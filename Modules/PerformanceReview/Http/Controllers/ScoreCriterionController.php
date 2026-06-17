<?php

namespace Modules\PerformanceReview\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Exception;
use Modules\PerformanceReview\Entities\ScoreCriterion;

class ScoreCriterionController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'score.criteria');
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = ScoreCriterion::query();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = createActionButton(route('scorecriterion.edit', $row->id), 'Edit', 'btn-warning edit-button', 'fa fa-edit');
                    $btn .= createActionButton(route('scorecriterion.destroy', $row->id), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable');
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('performancereview::scorecriterion.index');
    }

    public function create()
    {
        return response()->json([
            'success' => true,
            'html' => view('performancereview::scorecriterion.create')->render()
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request) {
                    $exists = \Modules\PerformanceReview\Entities\ScoreCriterion::where('title', $value)
                        ->where('min_score', $request->min_score)
                        ->where('max_score', $request->max_score)
                        ->exists();

                    if ($exists) {
                        $fail('This combination of title, min score, and max score already exists.');
                    }
                }
            ],
            'min_score' => 'required|numeric|min:0',
            'max_score' => 'required|numeric|gte:min_score',
            'description' => 'nullable|string|max:1000',
        ]);


        DB::beginTransaction();
        try {
            ScoreCriterion::create($data);
            DB::commit();
            return response()->json(getSuccessResponse("Score criterion created."));
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(getErrorResponse($e->getMessage()));
        }
    }

    public function edit($id)
    {
        $criterion = ScoreCriterion::findOrFail($id);

        return response()->json([
            'success' => true,
            'html' => view('performancereview::scorecriterion.edit', compact('criterion'))->render()
        ]);
    }

    public function update(Request $request, $id)
    {
        $criterion = ScoreCriterion::findOrFail($id);

        $data = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request, $criterion) {
                    $exists = \Modules\PerformanceReview\Entities\ScoreCriterion::where('title', $value)
                        ->where('min_score', $request->min_score)
                        ->where('max_score', $request->max_score)
                        ->where('id', '!=', $criterion->id)
                        ->exists();

                    if ($exists) {
                        $fail('This combination of title, min score, and max score already exists.');
                    }
                }
            ],
            'min_score' => 'required|numeric|min:0',
            'max_score' => 'required|numeric|gte:min_score',
            'description' => 'nullable|string|max:1000',
        ]);


        $criterion->update($data);

        return response()->json(getSuccessResponse("Score criterion updated."));
    }

    public function destroy($id)
    {
        $criterion = ScoreCriterion::findOrFail($id);
        $criterion->delete();

        return response()->json(getSuccessResponse("Score criterion deleted."));
    }
}
