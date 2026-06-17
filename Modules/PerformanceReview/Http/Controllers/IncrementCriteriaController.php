<?php

namespace Modules\PerformanceReview\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\PerformanceReview\Entities\IncrementCriteria;
use Yajra\DataTables\Facades\DataTables;
use Exception;

class IncrementCriteriaController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'increment-criteria');
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = IncrementCriteria::query();

            return DataTables::of($data)
                ->addIndexColumn()
                // ->addColumn('increment_percent', fn($row) => $row->increment_percent . '%')
                ->addColumn('action', function ($row) {
                    return createActionButton(route('incrementcriteria.edit', $row->id), 'Edit', 'btn-warning edit-button', 'fa fa-edit') .
                        createActionButton(route('incrementcriteria.destroy', $row->id), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable');
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('performancereview::incrementcriteria.index');
    }

    public function create()
    {
        return response()->json([
            'success' => true,
            'html' => view('performancereview::incrementcriteria.create')->render()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'min_score' => 'required|numeric|min:0',
            'max_score' => 'required|numeric|gt:min_score',
            'basic_percent' => 'required|numeric|min:0|max:100',
            'housing_percent' => 'required|numeric|min:0|max:100',
            'transport_percent' => 'required|numeric|min:0|max:100',
            'other_percent' => 'required|numeric|min:0|max:100',
            'incentive_percent' => 'required|numeric|min:0|max:100',
        ]);

        $exists = IncrementCriteria::where('label', $request->label)
            ->where('min_score', $request->min_score)
            ->where('max_score', $request->max_score)
            ->exists();

        if ($exists) {
            return response()->json(getErrorResponse("This combination of label and score range already exists."));
        }

        $increment = $request->basic_percent + $request->housing_percent + $request->transport_percent + $request->other_percent + $request->incentive_percent;

        if ($increment > 100) {
            return response()->json(getErrorResponse('Total increment percentage cannot exceed 100%'));
        }

        $data = $request->all();
        $data['increment_percent'] = $increment;

        try {
            IncrementCriteria::create($data);
            return response()->json(getSuccessResponse("Increment criteria created."));
        } catch (Exception $e) {
            return response()->json(getErrorResponse($e->getMessage()));
        }
    }

    public function edit($id)
    {
        $criterion = IncrementCriteria::findOrFail($id);

        return response()->json([
            'success' => true,
            'html' => view('performancereview::incrementcriteria.edit', compact('criterion'))->render()
        ]);
    }

    public function update(Request $request, $id)
    {
        $criterion = IncrementCriteria::findOrFail($id);

        $request->validate([
            'label' => 'required|string|max:255',
            'min_score' => 'required|numeric|min:0',
            'max_score' => 'required|numeric|gt:min_score',
            'basic_percent' => 'required|numeric|min:0|max:100',
            'housing_percent' => 'required|numeric|min:0|max:100',
            'transport_percent' => 'required|numeric|min:0|max:100',
            'other_percent' => 'required|numeric|min:0|max:100',
            'incentive_percent' => 'required|numeric|min:0|max:100',
        ]);

        $exists = IncrementCriteria::where('label', $request->label)
            ->where('min_score', $request->min_score)
            ->where('max_score', $request->max_score)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json(getErrorResponse("This combination of label and score range already exists."));
        }

        $increment = $request->basic_percent + $request->housing_percent + $request->transport_percent + $request->other_percent + $request->incentive_percent;

        if ($increment > 100) {
            return response()->json(getErrorResponse('Total increment percentage cannot exceed 100%'));
        }

        $data = $request->all();
        $data['increment_percent'] = $increment;

        try {
            $criterion->update($data);
            return response()->json(getSuccessResponse("Increment criteria updated."));
        } catch (Exception $e) {
            return response()->json(getErrorResponse($e->getMessage()));
        }
    }

    public function destroy($id)
    {
        try {
            $criterion = IncrementCriteria::findOrFail($id);
            $criterion->delete();

            return response()->json(getSuccessResponse("Increment criteria deleted."));
        } catch (Exception $e) {
            return response()->json(getErrorResponse($e->getMessage()));
        }
    }
}
