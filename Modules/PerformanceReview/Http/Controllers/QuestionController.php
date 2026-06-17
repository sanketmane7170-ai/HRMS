<?php

namespace Modules\PerformanceReview\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\PerformanceReview\Entities\Question;
use Modules\PerformanceReview\Entities\QuestionSet;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class QuestionController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'questions');
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Question::with('questionSet');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('question_set', function ($row) {
                    return $row->questionSet->name ?? '-';
                })
                ->addColumn('action', function ($row) {
                    $btn = createActionButton(route('question.edit', $row->id), 'Edit', 'btn-warning edit-button', 'fa fa-edit');
                    $btn .= createActionButton(route('question.destroy', $row->id), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable');
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('performancereview::questions.index');
    }

    public function create()
    {
        $questionSets = QuestionSet::pluck('name', 'id');
        return response()->json([
            'success' => true,
            'html' => view('performancereview::questions.create', compact('questionSets'))->render()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'question_text' => [
                'required',
                'string',
                Rule::unique('questions')->where(function ($query) use ($request) {
                    return $query->where('question_set_id', $request->question_set_id);
                })
            ],
            'question_set_id' => 'required|exists:question_sets,id',
            'max_score' => 'required|integer|min:1',
            'options' => 'required|array|min:2',
            'correct_option' => 'required|numeric'
        ]);

        DB::beginTransaction();
        try {
            $question = Question::create([
                'question_set_id' => $request->question_set_id,
                'question_text' => $request->question_text,
                'max_score' => $request->max_score,
            ]);

            foreach ($request->options as $index => $opt) {
                $question->options()->create([
                    'option_text' => $opt['text'],
                    'is_correct' => $request->correct_option == $index,
                ]);
            }

            DB::commit();
            return redirect()->route('question.index')->with('success', 'Question created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }


    public function edit($id)
    {
        $question = Question::findOrFail($id);
        $questionSets = QuestionSet::pluck('name', 'id');

        return response()->json([
            'success' => true,
            'html' => view('performancereview::questions.edit', compact('question', 'questionSets'))->render()
        ]);
    }

    public function update(Request $request, $id)
    {
        $question = Question::with('options')->findOrFail($id);
        try {
            $res = $request->validate([
                'question_text' => [
                    'required',
                    'string',
                    Rule::unique('questions')->where(function ($query) use ($request) {
                        return $query->where('question_set_id', $request->question_set_id);
                    })->ignore($id)
                ],
                'question_set_id' => 'required|exists:question_sets,id',
                'max_score' => 'required|integer|min:1',
                'options' => 'required|array|min:2',
                'options.*.text' => 'required|string',
                'correct_option' => 'required|numeric'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed:', $e->errors());
            return redirect()->back()->withErrors($e->errors())->withInput();
        }


        DB::beginTransaction();
        try {
            // Update question
            $question->update([
                'question_set_id' => $request->question_set_id,
                'question_text' => $request->question_text,
                'max_score' => $request->max_score,
            ]);

            // Delete old options
            $question->options()->delete();

            // Insert new options
            foreach ($request->options as $index => $opt) {
                $question->options()->create([
                    'option_text' => $opt['text'],
                    'is_correct' => $request->correct_option == $index,
                ]);
            }

            DB::commit();
            return redirect()->route('question.index')->with('success', 'Question updated successfully!');
        } catch (\Exception $e) {

            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $question = Question::findOrFail($id);
        $question->delete();

        return response()->json(getSuccessResponse("Question deleted."));
    }

    public function samplequesexportexcel()
    {
        return Excel::download(new \App\Exports\QuestionSampleExport, 'question_sample.xlsx');
    }

    public function importQuesFromExcel(Request $request)
    {
        $request->validate([
            'import_file' => 'required|mimes:xlsx,xls',
        ]);
        $response = getErrorResponse();
        try {

            Excel::import(new \App\Imports\QuestionImport, $request->file('import_file'));
            $response = getSuccessResponse(createFlashMessage('File', 'imported'));
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return response()->json($response);
    }
}
