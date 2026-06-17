<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CommonController extends Controller
{

    /**
     * Check User Redirection base on his role
     *
     */
    public function checkRedirection()
    {

        return redirect()->route('backend.dashboard');
    }

    public function translateText(Request $request)
    {
        $text = $request->input('text');
        $targetLanguage = $request->input('targetLanguage', 'hi');

        // Properly build the URL
        $response = Http::timeout(60)->get('https://translate.googleapis.com/translate_a/single', [
            'client' => 'gtx',
            'sl' => 'en',  // Source language
            'tl' => $targetLanguage,  // Target language
            'dt' => 't',
            'q' => $text,
        ]);

        if ($response->successful()) {
            $json = $response->json();

            if (isset($json[0]) && is_array($json[0])) {
                $translatedText = collect($json[0])->pluck(0)->join('');
                return response()->json(['translatedText' => $translatedText]);
            }
        }

        return response()->json(['error' => 'Translation failed'], 500);
    }
}
