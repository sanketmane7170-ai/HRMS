<?php
namespace App\Http\Controllers\Employee;

use App\Enums\Gender;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class ProfileController extends Controller
{

    public function my(): View
    {
        view()->share('activeLink', 'profile');
        $user = User::with('profile', 'workDetail')->where('id', auth()->id())->first();

        return view('employee.profile.index', compact('user'));
    }

    /**
     * Return view to edit social details
     */
    public function editSocialDetailForm(): JsonResponse
    {
        $html = view('employee.profile.partials.edit-social-details')->render();

        return response()->json([
            'html'    => $html,
            'success' => true,
        ]);
    }

    /**
     * Update Social Details of the logged in user
     */
    public function updateSocialDetails(Request $request): JsonResponse
    {
        $request->validate([
            'linkedin_profile_url' => 'nullable|url',
            'skills'               => 'required',
            'hobbies'              => 'required',
        ]);
        $user                  = auth()->user();
        $profile               = $user->profile;
        $profile->linkedin_url = $request->linkedin_profile_url;
        $profile->hobbies      = $request->hobbies;
        $profile->skills       = $request->skills;
        $profile->save();
        $html = view('employee.profile.partials.social-details', compact('user'))->render();

        return response()->json([
            'success' => true,
            'message' => createFlashMessage('Social Detail', 'updated'),
            'html'    => $html,
        ]);
    }

    /**
     * Return view to edit profile details
     */
    public function editProfileDetailForm(): JsonResponse
    {
        $user = User::with('profile')->where('id', auth()->id())->first();
        $html = view('employee.profile.partials.edit-profile-details', compact('user'))->render();

        return response()->json([
            'html'    => $html,
            'success' => true,
        ]);
    }

    /**
     * Update Profile Details of the logged in user
     */
    public function updateProfileDetails(Request $request): JsonResponse
    {
        $user = auth()->user();
        $request->validate([
            'name'           => 'required',
            'gender'         => ['required', new Enum(Gender::class)],
            'email'          => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'employee_id'    => ['required', Rule::unique('users', 'employee_id')->ignore($user->id)],
            'work_phone'     => 'required',
            'personal_email' => ['required', 'email'],
            'personal_phone' => 'required',
            'visa_category'  => 'nullable|string',
            'date_of_birth'  => ['required', 'date'],
            'country_id'     => ['required', 'exists:countries,id'],
            'martial_status' => 'required',
            'address'        => 'required',
        ]);

        $user->name        = $request->name;
        $user->phone       = $request->work_phone;
        $user->email       = $request->email;
        $user->employee_id = $request->employee_id;
        $user->save();

        $user->profile()->update([
            'date_of_birth'  => $request->date_of_birth,
            'personal_email' => $request->personal_email,
            'personal_phone' => $request->personal_phone,
            'visa_category'  => $request->visa_category,
            'martial_status' => $request->martial_status,
            'country_id'     => $request->country_id,
            'gender'         => $request->gender,
            'address'        => $request->address,
        ]);
        $html = view('employee.profile.index', compact('user'))->render();

        return response()->json([
            'success' => true,
            'message' => createFlashMessage('Personal Detail', 'updated'),
            'html'    => $html,
        ]);
    }
}
