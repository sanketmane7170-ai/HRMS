<?php

namespace Modules\PerformanceReview\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\PerformanceReview\Entities\QuestionSet;
use Illuminate\Contracts\Support\Renderable;
use Yajra\DataTables\Facades\DataTables;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class QuestionSetController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'question.set');
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = QuestionSet::query();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = createActionButton(route('questionset.edit', $row->id), 'Edit', 'btn-warning edit-button', 'fa fa-edit');
                    $btn .= createActionButton(route('questionset.destroy', $row->id), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable');
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('performancereview::questionsets.index');
    }

    public function create()
    {
        return response()->json([
            'success' => true,
            'html' => view('performancereview::questionsets.create')->render()
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:question_sets,name',
        ]);


        DB::beginTransaction();
        try {
            QuestionSet::create($data);
            DB::commit();
            return response()->json(getSuccessResponse("Question set created."));
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(getErrorResponse($e->getMessage()));
        }
    }

    public function edit($id)
    {
        $questionSet = QuestionSet::findOrFail($id);

        return response()->json([
            'success' => true,
            'html' => view('performancereview::questionsets.edit', compact('questionSet'))->render()
        ]);
    }

    public function update(Request $request, $id)
    {
        $questionSet = QuestionSet::findOrFail($id);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('question_sets', 'name')->ignore($questionSet->id),
            ],
        ]);


        $questionSet->update($data);

        return response()->json(getSuccessResponse("Question set updated."));
    }

    public function destroy($id)
    {
        $questionSet = QuestionSet::findOrFail($id);
        $questionSet->delete();

        return response()->json(getSuccessResponse("Question set deleted."));
    }
}
