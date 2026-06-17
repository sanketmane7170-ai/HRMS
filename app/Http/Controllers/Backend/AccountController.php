<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AccountController extends Controller
{
    /**
     * Display  User Profile
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page = 'account-details';
        $title = __trans('my_account');
        view()->share('activeLink', $title);

        return view('backend.account.index', compact('page', 'title'));
    }

    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function updateAccount(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|unique:users,email,' . auth()->id(),
            'name' => 'required',
            'phone' => 'nullable|min:10',
        ]);
        $response = getErrorResponse();
        try {
            if ($request->hasFile('profile_image')) {
                $data['profile_image'] = $this->upload($request->profile_image, '/uploads/profile', auth()->user()->profile_image);
            }
            auth()->user()->update($data);
            $response = [
                'success' => true,
                'message' => createFlashMessage('Account', 'update')
            ];
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }


    /**
     * Display  User Profile
     *
     * @return \Illuminate\Http\Response
     */
    public function password()
    {
        $page = 'change-password';
        $title = 'Change Password';
        view()->share('activeLink', $title);
        return view('backend.account.index', compact('page', 'title'));
    }

    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8',
            'confirm_password' => 'required|same:new_password'
        ]);
        $user = auth()->user();
        $response = getErrorResponse();
        try {
            if (!Hash::check($request->current_password, $user->password)) {
                $response['message'] = 'Your current password  didn\'t matched with password in our records';
            } else {
                $user->password  = Hash::make($request->new_password);
                if ($user->save()) {
                    $response = [
                        'success' => true,
                        'message' => createFlashMessage('Password', 'changed')
                    ];
                } else {
                }
            }
        } catch (Exception $e) {
            $response['error'] = $e->getMessage();
        }

        return response()->json($response);
    }
}
