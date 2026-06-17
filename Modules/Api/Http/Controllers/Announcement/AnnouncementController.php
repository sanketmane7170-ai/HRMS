<?php

namespace Modules\Api\Http\Controllers\Announcement;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Announcement\Entities\Announcement;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $results = Announcement::with(['type'  => function ($query) {
            $query->select('id', 'name', 'color'); //timestamps excluded
        }])
        ->where('start_at', '<=', now()->toDateTimeString())
        ->where('end_at', '>=', now()->toDateTimeString())
        ->where(function ($q) {
            $q->where('user_id', Auth::id())->orwhereNull('user_id');
        })
        ->where(function ($q) {
            $q->where('department_id', Auth::user()->department_id)->orwhereNull('department_id');
        })
        ->orderBy('start_at')->get();

        $results->makeHidden(['created_at','updated_at','announcement_type_id']);
        $results->transform(function ($item) {
            $html = $item->body;
            $item->body = preg_replace_callback(
            '/<img[^>]+src="([^">]+)"/i',
                function ($matches) {
                    $src = $matches[1];
                    if (!preg_match('/^https?:\/\//', $src)) {
                        $src = url($src);
                    }
                    return str_replace($matches[1], $src, $matches[0]);
                }, $html);
            $item->body = str_replace(["\r", "\n"], '', $item->body);
            return $item;
        });
        return response()->json([
            'success' => true,
            'message' => __trans('active_announcements_fetched_successfully'),
            'data' => $results,
        ], 200, [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Display a listing of the birthday wishes this month.
     * @return Renderable
     */
    public function upcomingbirthday($department = null){

        $defaultImage = asset('assets/backend/img/profiles/avatar-01.jpg');

        $query = User::select('id','name','profile_image')->where('status',User::STATUS_ACTIVE)->withWhereHas('profile', function ($query) {
            return $query->select('date_of_birth', 'user_id')->birthdayThisMonth();
        })
            ->when($department, function ($query) use ($department) {
                return $query->where('department_id', $department);
            })
            ->orderByRaw('(SELECT DATE_FORMAT(date_of_birth,"%M %d %Y") FROM user_profiles WHERE users.id = user_profiles.user_id)')->take(5)->get();
        
            foreach($query as $data){
                if($data->profile_image){
                    $data->profile_image = asset($data->profile_image);
                } else {
                    $data->profile_image = $defaultImage;
                }
           }
        return response()->success(__trans('upcoming_birthday_wishes_fetched_successfully'), $query);
    }

    /**
     * Display a listing of the users anniversary this month.
     * @return Renderable
     */
    public function upcominganniversary($department = null){

        $defaultImage = asset('assets/backend/img/profiles/avatar-01.jpg');

        $query = User::select('id','name','profile_image')->where('status',User::STATUS_ACTIVE)->withWhereHas('workDetail', function ($query) {
            return $query->select('joining_date', 'user_id')->anniversaryThisMonth();
        })
            ->when($department, function ($query) use ($department) {
                return $query->where('department_id', $department);
            })
            ->orderByRaw('(SELECT DATE_FORMAT(joining_date,"%d") FROM user_work_details WHERE users.id = user_work_details.user_id)')->take(5)->get();

        foreach($query as $data){
            if($data->profile_image){
                $data->profile_image = asset($data->profile_image);
            } else {
                $data->profile_image = $defaultImage;
            }
        }   
        return response()->success(__trans('upcoming_users_anniversary_fetched_successfully'), $query);
    }
}
