<?php

namespace App\Http\Controllers\Backend;

use App\Exports\FeatureExport;
use App\Http\Controllers\Controller;
use App\Imports\FeatureImport;
use App\Models\Feature;
use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class KnowledgeHubController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'knowledgehub');
    }

    /**
     * Display a listing of the feature.
     */
    // public function index(Request $request)
    // {
    //     $videos = [
    //         ['title' => 'Do you know about video 1', 'url' => 'https://youtu.be/lhk2VLnl2CA'],
    //         ['title' => 'Do you know about video 2', 'url' => 'https://youtu.be/lhk2VLnl2CA'],
    //         ['title' => 'Do you know about video 1', 'url' => 'https://youtu.be/lhk2VLnl2CA'],
    //         ['title' => 'Do you know about video 2', 'url' => 'https://youtu.be/lhk2VLnl2CA'],
    //         ['title' => 'Do you know about video 1', 'url' => 'https://youtu.be/lhk2VLnl2CA'],
    //         ['title' => 'Do you know about video 2', 'url' => 'https://youtu.be/lhk2VLnl2CA'],
    //         ['title' => 'Do you know about video 1', 'url' => 'https://youtu.be/lhk2VLnl2CA'],
    //         ['title' => 'Do you know about video 2', 'url' => 'https://youtu.be/lhk2VLnl2CA'],
    //         ['title' => 'Do you know about video 1', 'url' => 'https://youtu.be/lhk2VLnl2CA'],
    //         ['title' => 'Do you know about video 2', 'url' => 'https://youtu.be/lhk2VLnl2CA'],
    //         ['title' => 'Do you know about video 1', 'url' => 'https://youtu.be/lhk2VLnl2CA'],
    //         ['title' => 'Do you know about video 2', 'url' => 'https://youtu.be/lhk2VLnl2CA'],
    //         ['title' => 'Do you know about video 1', 'url' => 'https://youtu.be/lhk2VLnl2CA'],
    //         ['title' => 'Do you know about video 2', 'url' => 'https://youtu.be/lhk2VLnl2CA'],
    //         ['title' => 'Do you know about video 1', 'url' => 'https://youtu.be/lhk2VLnl2CA'],
    //         ['title' => 'Do you know about video 2', 'url' => 'https://youtu.be/lhk2VLnl2CA'],
    //         ['title' => 'Do you know about video 1', 'url' => 'https://youtu.be/lhk2VLnl2CA'],
    //         ['title' => 'Do you know about video 2', 'url' => 'https://youtu.be/lhk2VLnl2CA'],
    //         ['title' => 'Do you know about video 1', 'url' => 'https://youtu.be/lhk2VLnl2CA'],
    //         ['title' => 'Do you know about video 2', 'url' => 'https://youtu.be/lhk2VLnl2CA'],
    //         ['title' => 'Do you know about video 1', 'url' => 'https://youtu.be/lhk2VLnl2CA'],
    //         ['title' => 'Do you know about video 2', 'url' => 'https://youtu.be/lhk2VLnl2CA'],
    //         ['title' => 'Do you know about video 1', 'url' => 'https://youtu.be/lhk2VLnl2CA'],
    //         ['title' => 'Do you know about video 2', 'url' => 'https://youtu.be/lhk2VLnl2CA'],
    //         ['title' => 'Do you know about video 1', 'url' => 'https://youtu.be/lhk2VLnl2CA'],
    //         ['title' => 'Do you know about video 2', 'url' => 'https://youtu.be/lhk2VLnl2CA'],
    //         ['title' => 'Do you know about video 1', 'url' => 'https://youtu.be/lhk2VLnl2CA'],
    //         ['title' => 'Do you know about video 2', 'url' => 'https://youtu.be/lhk2VLnl2CA'],
    //         ['title' => 'Do you know about video 1', 'url' => 'https://youtu.be/lhk2VLnl2CA'],
    //         ['title' => 'Do you know about video 2', 'url' => 'https://youtu.be/lhk2VLnl2CA'],
    //         ['title' => 'Do you know about video 1', 'url' => 'https://youtu.be/lhk2VLnl2CA'],
    //         ['title' => 'Do you know about video 2', 'url' => 'https://youtu.be/lhk2VLnl2CA'],
    //     ];

        
    //     return view('backend.knowledgehub.index',compact('videos'));
    // }
    public function index(Request $request)
    {
        $search = $request->input('search');
    
        // Static array of videos
        $videos = collect([
            ['title' => 'How to Create New Users.', 'url' => 'https://youtu.be/jIBWeDvcS9o'],
            ['title' => 'How to Use the Announcement Feature.', 'url' => 'https://youtu.be/A6Bjzjs0ZZ0'],
            ['title' => 'How to Use the Report Section Module.', 'url' => 'https://youtu.be/yejnrKX_LaE'],
            ['title' => 'How to Use Auto Overtime Calculation.', 'url' => 'https://youtu.be/OvlcXp0JJoI'],
            ['title' => 'How to Use Attendance & Holiday Tracking.', 'url' => 'https://youtu.be/l_Vu7hMxZz4'],
            ['title' => 'How to Use Role Manager Permissions.', 'url' => 'https://youtu.be/ExkSWvGOuLE'],
        ]);
    
        // Filter by title if search is present
        if ($search) {
            $videos = $videos->filter(function ($video) use ($search) {
                return str_contains(strtolower($video['title']), strtolower($search));
            });
        }
    
        // Manually paginate the filtered results
        $perPage = 9;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $pagedVideos = new LengthAwarePaginator(
            $videos->forPage($currentPage, $perPage)->values(),
            $videos->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    
        return view('backend.knowledgehub.index', [
            'videos' => $pagedVideos,
            'search' => $search,
        ]);
    }
}
