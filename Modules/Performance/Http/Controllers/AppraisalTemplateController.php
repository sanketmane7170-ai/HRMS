<?php
namespace Modules\Performance\Http\Controllers;

use App\Models\Department;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Performance\Entities\AppraisalTemplate;
use Modules\Performance\Entities\AppraisalTemplateCriteria;
use Yajra\DataTables\Facades\DataTables;

class AppraisalTemplateController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'appraisal_template');
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = AppraisalTemplate::with('criteria')->latest()->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('period_type', fn($row) =>
                    ucfirst(str_replace('_', ' ', $row->period_type))
                )
                ->addColumn('criteria_count', fn($row) =>
                    $row->criteria->count()
                )
                ->addColumn('status', fn($row) =>
                    $row->is_active
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-danger">Inactive</span>'
                )
                ->addColumn('branch', fn($row) =>
                    optional($row->branch)->name ?? '-'
                )
                ->addColumn('department', fn($row) =>
                    optional($row->department)->name ?? '-'
                )
                ->addColumn('action', function ($row) {
                    $btn  = createActionButton(route('performance.template.show', $row->id), 'View', 'btn-success edit-button', 'fa fa-eye');
                    $btn .= createActionButton(route('performance.template.edit', $row->id), 'Edit', 'btn-warning edit-button', 'fa fa-edit');
                    $btn .= createActionButton(route('performance.template.destroy', $row->id), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable');
                    return $btn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }
        $departments = Division::select('id', 'name')->get();
        $branches    = Department::select('id', 'name')->get();

        return view('performance::template.index', compact('branches', 'departments'));
    }

    public function show($id)
    {
        $template = AppraisalTemplate::with('criteria')->findOrFail($id);

        return response()->json([
            'success' => true,
            'html'    => view('performance::template.show', compact('template'))->render(),
        ]);
    }

    public function create()
    {
        $departments = Division::select('id', 'name')->get();
        $branches    = Department::select('id', 'name')->get();

        return response()->json([
            'success' => true,
            'html'    => view('performance::template.create', compact('branches', 'departments'))->render(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                   => 'required|string|max:255',
            'period_type'            => 'required|in:daily,weekly,monthly,quarterly,half_yearly,yearly',
            'criteria'               => 'required|array',
            'criteria.*.name'        => 'required|string|max:255',
            'criteria.*.weight'      => 'required|numeric|min:1|max:100',
            'criteria.*.max_score'   => 'required|numeric|min:1',
            'criteria.*.description' => 'nullable|string|max:1000',
            'criteria.*.comments'    => 'nullable|string|max:1000',
            'branch_id'              => 'nullable|exists:departments,id',
            'department_id'          => 'nullable|exists:divisions,id',
        ]);

        DB::beginTransaction();
        try {
            $template = AppraisalTemplate::create([
                'name'          => $data['name'],
                'period_type'   => $data['period_type'],
                'branch_id'     => $data['branch_id'] ?? 0,
                'department_id' => $data['department_id'] ?? 0,
                'is_active'     => 1,
            ]);

            foreach ($data['criteria'] as $c) {
                AppraisalTemplateCriteria::create([
                    'template_id'   => $template->id,
                    'criteria_name' => $c['name'],
                    'description'   => $c['description'] ?? null,
                    'weight'        => $c['weight'],
                    'max_score'     => $c['max_score'],
                    'comments'      => $c['comments'] ?? null,
                ]);
            }

            DB::commit();
            return response()->json(getSuccessResponse('Template & criteria created successfully.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(getErrorResponse($e->getMessage()));
        }
    }

    public function edit($id)
    {
        $template    = AppraisalTemplate::with('criteria')->findOrFail($id);
        $departments = Division::select('id', 'name')->get();
        $branches    = Department::select('id', 'name')->get();

        return response()->json([
            'success' => true,
            'html'    => view('performance::template.edit', compact('template', 'departments', 'branches'))->render(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $template = AppraisalTemplate::with('criteria')->findOrFail($id);

        $data = $request->validate([
            'name'                   => 'required|string|max:255',
            'period_type'            => 'required|in:daily,weekly,monthly,quarterly,half_yearly,yearly',
            'criteria'               => 'required|array',
            'criteria.*.name'        => 'required|string|max:255',
            'criteria.*.weight'      => 'required|numeric|min:1|max:100',
            'criteria.*.max_score'   => 'required|numeric|min:1',
            'criteria.*.description' => 'nullable|string|max:1000',
            'criteria.*.comments'    => 'nullable|string|max:1000',
            'branch_id'              => 'nullable|exists:departments,id',
            'department_id'          => 'nullable|exists:divisions,id',
        ]);

        DB::beginTransaction();
        try {
            $template->update([
                'name'          => $data['name'],
                'period_type'   => $data['period_type'],
                'branch_id'     => $data['branch_id'] ?? 0,
                'department_id' => $data['department_id'] ?? 0,
            ]);

            // Delete old criteria and insert new ones
            $template->criteria()->delete();

            foreach ($data['criteria'] as $c) {
                AppraisalTemplateCriteria::create([
                    'template_id'   => $template->id,
                    'criteria_name' => $c['name'],
                    'description'   => $c['description'] ?? null,
                    'weight'        => $c['weight'],
                    'max_score'     => $c['max_score'],
                    'comments'      => $c['comments'] ?? null,
                ]);
            }

            DB::commit();
            return response()->json(getSuccessResponse('Template & criteria updated successfully.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(getErrorResponse($e->getMessage()));
        }
    }

    public function destroy($id)
    {
        $template = AppraisalTemplate::findOrFail($id);
        $template->criteria()->delete(); // delete criteria first
        $template->delete();

        return response()->json(getSuccessResponse('Template & criteria deleted successfully.'));
    }
}
