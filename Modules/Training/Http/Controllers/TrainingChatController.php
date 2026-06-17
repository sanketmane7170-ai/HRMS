<?php

namespace Modules\Training\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Training\Entities\Training;
use Modules\Training\Entities\TrainingChat;

class TrainingChatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function store(Request $request, Training $training)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);
        try {
            TrainingChat::create([
                'training_id' => $training->id,
                'user_id' => auth()->id(),
                'message' => $request->message,
            ]);
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went to wrong.');
        }

        return redirect()->back()->with('success', 'Message posted successfully.');
    }

    public function reply(Request $request, TrainingChat $chat): RedirectResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);
        try {
            TrainingChat::create([
                'training_id' => $chat->training_id,
                'user_id' => auth()->id(),
                'message' => $request->message,
                'parent_id' => $chat->id
            ]);
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Something went to wrong.');
        }
        return redirect()->back()->with('success', 'Reply posted.');
    }
}
