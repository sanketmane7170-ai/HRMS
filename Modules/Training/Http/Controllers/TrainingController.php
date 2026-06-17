<?php

namespace Modules\Training\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Modules\Training\Entities\Training;
use Modules\Training\Entities\TrainingQuestion;
use Modules\Training\Entities\TrainingVideo;
use Modules\Training\Entities\UserTrainingAttempt;
use Yajra\DataTables\Facades\DataTables;

class TrainingController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            if (auth()->user()->hasRole(User::ROLE_ADMIN) || auth()->user()->hasRole(User::ROLE_SUPER_ADMIN)) {
                $data = Training::with('department')->get();
            } else {
                $userDepartmentId = auth()->user()->department_id;
                $data = Training::where('department_id', $userDepartmentId)->get();
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('department', function ($row) {
                    return $row->department->name;
                })
                ->addColumn('action', function ($row) {
                    $btn = '';
                    $btn = createActionButton(route('backend.training.show', $row), 'View', 'btn-success', 'fa fa-eye');
                    if (auth()->user()->hasRole(User::ROLE_ADMIN) || auth()->user()->hasRole(User::ROLE_SUPER_ADMIN)) {
                        $btn .= createActionButton(route('backend.training.questions', $row->id), 'Questions', 'btn-info edit-button', 'fa fa-question');
                        $btn .= createActionButton(route('backend.training.user.scores', $row->id), 'User Scores', 'btn-secondary edit-button', 'fa fa-users');
                        $btn .= createActionButton(route('backend.training.edit', $row), 'Edit', 'btn-warning edit-button', 'fa fa-edit');
                        $btn .= createActionButton(route('backend.training.destroy', $row), 'Delete', 'btn-danger action-button', 'fa fa-trash', 'datatable');
                    }
                    if (!auth()->user()->hasRole(User::ROLE_ADMIN) || auth()->user()->hasRole(User::ROLE_SUPER_ADMIN)) {
                        $btn .= createActionButton(route('backend.training.qa', $row), 'Certificate', 'btn-info edit-button', 'fa fa-certificate');
                    }
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('training::training.index');
    }

    public function create()
    {
        $departments = Department::all();
        $html = view('training::training.create', compact('departments'))->render();
        return response()->json(['success' => true, 'html' => $html]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            // 'videos.*' => 'nullable|file|mimes:mp4,mov,avi,wmv|max:200000',
             'videos.*' => 'required|file|max:20480', // 20MB per file
        ]);

        $response = getErrorResponse();
        try {
            $training = Training::create([
                'department_id' => $request->department_id,
                'title' => $request->title,
                'description' => $request->description,
            ]);

            if ($request->hasFile('videos')) {
                foreach ($request->file('videos') as $video) {
                    $path = $video->store('training_videos', 'public');
                    $training->videos()->create(['video_path' => $path]);
                }
            }

            $response = getSuccessResponse(createFlashMessage('Training', 'created'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    public function show(Training $training)
    {
        // dd($training->creator);
        return view('training::training.show', compact('training'));
    }

    public function edit(Training $training)
    {
        $departments = Department::all();
        $training->load('videos');
        $html = view('training::training.edit', compact('training', 'departments'))->render();

        return response()->json(['success' => true, 'html' => $html]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            // 'videos.*' => 'nullable|file|mimes:mp4,mov,avi,wmv|max:200000',
             'videos.*' => 'required|file|max:20480', // 20MB per file
        ]);
        $response = getErrorResponse();
        try {
            $training = Training::findOrFail($id);
            $training->update($request->only(['department_id', 'title', 'description']));

            if ($request->hasFile('videos')) {
                foreach ($request->file('videos') as $video) {
                    $path = $video->store('training_videos', 'public');

                    $training->videos()->create(['video_path' => $path]);
                }
            }
            $response = getSuccessResponse(createFlashMessage('Training', 'updated'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }

    public function destroy(Training $training)
    {
        $training->delete();

        $response = getSuccessResponse(createFlashMessage('Training Request', 'deleted'));

        return response()->json($response);
    }
    public function manageQuestions(Training $training)
    {

        $training->load('questions.answers');

        $html = view('training::training.questions', compact('training'))->render();
        return response()->json(['success' => true, 'html' => $html]);
    }

    public function storeQuestion(Request $request, Training $training)
    {
        $request->validate([
            'question' => 'required|string',
            'duration' => 'required',
            'answers.*.option_label' => 'required|in:a,b,c,d',
            'answers.*.option_text' => 'required|string',
            'correct_option' => 'required|in:a,b,c,d',
        ]);
        $response = getErrorResponse();
        try {
            $question = $training->questions()->create(
                [
                    'question' => $request->question,
                    'duration' => $request->duration
                ]);

            foreach ($request->answers as $answer) {
                $question->answers()->create([
                    'option_label' => $answer['option_label'],
                    'option_text' => $answer['option_text'],
                    'is_correct' => $answer['option_label'] == $request->correct_option
                ]);
            }
            $response = getSuccessResponse(createFlashMessage('Question', 'added'));
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
        // return back()->with('success', 'Question added successfully.');
    }
    // public function showQuestions(Training $training)
    // {
    //     $training->load('questions.answers');

    //     $html = view('training::training.certificate', compact('training'))->render();
    //     return response()->json(['success' => true, 'html' => $html]);
    //     // return view('training::training.certificate', compact('training'));
    // }
    public function questionList(Training $training)
    {
        $training->load('questions');

        $userAnswers = UserTrainingAttempt::where('user_id', auth()->id())
            ->where('training_id', $training->id)
            ->get()
            ->keyBy('question_id');

        return view('training::training.question_list', compact('training', 'userAnswers'));
    }

    public function showQuestions(Training $training)
    {
        $training->load('questions.answers');

        $userId = auth()->id();

        // Fetch previous attempts if any
        $attempts = UserTrainingAttempt::where('user_id', $userId)
            ->where('training_id', $training->id)
            ->get()
            ->keyBy('question_id');

        $score = session('score') ?? $attempts->where('is_correct', true)->count();
        $correctCount = $attempts->where('is_correct', true)->count();
        $totalQuestions = $attempts->count();
        $score = $totalQuestions > 0 ? round(($correctCount / $totalQuestions) * 100) : 0;

        $readonly = $attempts->isNotEmpty();

         // ✅ Total duration in seconds (assuming duration is in seconds)
    $totalDuration = $training->questions->sum('duration');

    // ✅ Optionally format duration (e.g., into mm:ss)
    $formattedDuration = gmdate("i:s", $totalDuration);


        $html = view('training::training.certificate', compact('training', 'attempts', 'readonly', 'score','totalDuration', 'formattedDuration'))->render();
        return response()->json(['success' => true, 'html' => $html]);
    }

    // public function storeQAAnswers(Request $request, Training $training)
    // {
    //     $answers = $request->input('answers', []);
    //     $userId = auth()->id();

    //     foreach ($answers as $questionId => $selectedOption) {
    //         $question = TrainingQuestion::with('answers')->find($questionId);

    //         $correctAnswer = $question->answers->firstWhere('is_correct', true);

    //         UserTrainingAttempt::create([
    //             'user_id' => $userId,
    //             'training_id' => $training->id,
    //             'question_id' => $questionId,
    //             'selected_option' => $selectedOption,
    //             'is_correct' => $selectedOption === $correctAnswer->option_label,
    //         ]);
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Your answers were submitted successfully.',
    //     ]);
    // }
    public function storeQAAnswers(Request $request, Training $training)
    {
        $answers = $request->input('answers', []);
        $userId = auth()->id();
        $score = 0;
        foreach ($answers as $questionId => $selectedOption) {
            $question = TrainingQuestion::with('answers')->find($questionId);
            $correctAnswer = $question->answers->firstWhere('is_correct', true);

            $isCorrect = $selectedOption === $correctAnswer->option_label;
            if ($isCorrect) {
                $score++;
            }

            UserTrainingAttempt::updateOrCreate(
                [
                    'user_id' => $userId,
                    'training_id' => $training->id,
                    'question_id' => $questionId,
                ],
                [
                    'selected_option' => $selectedOption,
                    'is_correct' => $isCorrect,
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Your answers were submitted successfully.',
        ]);

        // return redirect()
        //     ->route('backend.training.qa', $training->id)
        //     ->with('score', $score)
        //     ->with('success', 'You have already attempted this test.');
    }

    public function userScores(Training $training)
    {
        // Group by user and count correct answers
        $attempts = UserTrainingAttempt::where('training_id', $training->id)
            ->get()
            ->groupBy('user_id');

        // Build scores list
        $userScores = [];

        foreach ($attempts as $userId => $userAttempts) {
            $user = User::find($userId);
            $correctCount = $userAttempts->where('is_correct', true)->count();
            $totalQuestions = $userAttempts->count();
            $correctCount = $totalQuestions > 0 ? round(($correctCount / $totalQuestions) * 100) : 0;

            $userScores[] = [
                'user' => $user,
                'score' => $correctCount,
                'total' => $totalQuestions,
            ];
        }
        $html = view('training::training.user_scores', compact('training', 'userScores'))->render();
        return response()->json(['success' => true, 'html' => $html]);
    }

    public function generate(Training $training)
    {
        $userId = auth()->user()->id;

        // Calculate user score
        $totalQuestions = $training->questions()->count();
        $correctAnswers = UserTrainingAttempt::where('user_id', $userId)
            ->where('training_id', $training->id)
            ->where('is_correct', 1)
            ->count();

        $score = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100) : 0;

        return view('training::training.certificateprint', compact('training', 'score'));
    }
    public function questionsdestroy(Training $training, TrainingQuestion $question)
    {
        $question->answers()->delete(); // Delete related answers first (if cascade is not set)
        $question->delete();

        return response()->json(['success' => true, 'message' => 'Question deleted successfully.']);
    }
}
