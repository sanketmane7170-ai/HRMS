<?php

use Modules\Announcement\Entities\Announcement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;



if (!function_exists('getActiveAccouncements')) {
    function getActiveAccouncements()
    {
        // $userId = Auth::id();

        // DB::enableQueryLog(); // Enable query log

        $query = Announcement::with('type')
            ->where('start_at', '<=', now()->toDateTimeString())
            ->where('end_at', '>=', now()->toDateTimeString())
            ->where(function ($q) {
                $q->where('user_id', Auth::id())->orwhereNull('user_id');
            })
            ->where(function ($q) {
                $q->where('department_id', Auth::user()->department_id)->orwhereNull('department_id');
            })
            ->orderBy('start_at');

        $data =  $query->get();
        // dd($data);
        // dd(DB::getQueryLog()); // Show results of log
        return $data;
    }
}
