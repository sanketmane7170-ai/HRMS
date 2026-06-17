<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GeneralRequestController extends Controller
{
    public function general_request(){
        view()->share('activeLink', 'general request');
        $view = 'backend.general.general_request';
        return view($view);

    }

    public function apparel_request()
    {
        $page = 'apparel';
        view()->share('activeLink', "apparel-$page");
        return view('backend.general.apparel_index', compact('page'));
    }

    public function show_general_request()
    {
        $page = 'general request';
        view()->share('activeLink', "$page");
        return view('backend.general.index', compact('page'));
    }

}
