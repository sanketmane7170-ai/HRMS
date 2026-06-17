<?php

namespace Modules\AgenticAI\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AgenticAIController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('agenticai::index', ['activeLink' => 'agentic-ai']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('agenticai::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('agenticai::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('agenticai::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Handle file upload from Chat.
     */
    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240|mimes:pdf,doc,docx,txt,jpg,jpeg,png,csv,xlsx',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $user = auth()->user();
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            //Sanket v2.0 - include user_id in path for file ownership tracking
            $filename = $user->id . '_' . time() . '_' . \Illuminate\Support\Str::slug($originalName) . '.' . $extension;
            $path = $file->storeAs('chat_uploads/' . $user->id, $filename, 'public');
            
            return response()->json([
                'status' => 'success',
                'url' => asset('storage/' . $path),
                'filename' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'path' => $path
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'No file uploaded'], 400);
    }
}
