<?php

namespace Modules\PerformanceReview\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\PerformanceReview\Entities\ReviewDuration;
use Illuminate\Contracts\Support\Renderable;
use Yajra\DataTables\Facades\DataTables;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReviewDurationController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'review.duration');
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = ReviewDuration::query();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = createActionButton(route('reviewduration.edit', $row->id), 'Edit', 'btn-warning edit-button', 'fa fa-edit');
                    $btn .= createActionButton(route('reviewduration.destroy', $row->id), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable');
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('performancereview::durations.index');
    }

    public function create()
    {
        return response()->json([
            'success' => true,
            'html' => view('performancereview::durations.create')->render()
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'label' => [
                'required',
                'string',
                'max:100',
                Rule::unique('review_durations')->where(function ($query) use ($request) {
                    return $query->where('months', $request->months);
                })
            ],
            'months' => 'required|numeric|min:1|max:24',
        ]);

        DB::beginTransaction();
        try {
            ReviewDuration::create($data);
            DB::commit();
            return response()->json(getSuccessResponse("Review duration created."));
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(getErrorResponse($e->getMessage()));
        }
    }

    public function edit($id)
    {
        $duration = ReviewDuration::findOrFail($id);

        return response()->json([
            'success' => true,
            'html' => view('performancereview::durations.edit', compact('duration'))->render()
        ]);
    }

    public function update(Request $request, $id)
    {
        $duration = ReviewDuration::findOrFail($id);

        $data = $request->validate([
            'label' => [
                'required',
                'string',
                'max:100',
                Rule::unique('review_durations')->where(function ($query) use ($request) {
                    return $query->where('months', $request->months);
                })->ignore($duration->id)
            ],
            'months' => 'required|numeric|min:1|max:24',
        ]);


        $duration->update($data);

        return response()->json(getSuccessResponse("Review duration updated."));
    }

    public function destroy($id)
    {
        $duration = ReviewDuration::findOrFail($id);
        $duration->delete();

        return response()->json(getSuccessResponse("Review duration deleted."));
    }
}
