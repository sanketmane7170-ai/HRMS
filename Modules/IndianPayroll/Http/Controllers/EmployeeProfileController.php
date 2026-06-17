<?php

namespace Modules\IndianPayroll\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\IndianPayroll\Entities\BankDetail;
use Modules\IndianPayroll\Entities\EmployeeProfile;
use Modules\IndianPayroll\Entities\IpState;
use Yajra\DataTables\Facades\DataTables;

class EmployeeProfileController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'indian-payroll.employee-profiles');
    }

    public function index(Request $request)
    {
        canPerform('Manage Employee Statutory Profile');

        if ($request->ajax()) {
            $profiles = EmployeeProfile::with('user', 'state')->whereHas('user');

            return DataTables::of($profiles)
                ->addColumn('name', fn ($p) => $p->user->name ?? 'N/A')
                ->addColumn('state', fn ($p) => $p->state->name ?? '-')
                ->addColumn('action', fn ($p) => createActionButton(
                    route('backend.indian-payroll.employee-profiles.show', $p->user_id), '', 'btn-info', 'fa fa-eye'
                ).createActionButton(
                    route('backend.indian-payroll.employee-profiles.edit', $p->user_id), '', 'btn-warning edit-button', 'fa fa-edit'
                ))
                ->rawColumns(['action'])
                ->make(true);
        }

        $usersWithoutProfile = User::whereDoesntHave('indianPayrollProfile')->where('status', User::STATUS_ACTIVE)->orderBy('name')->get();

        return view('indianpayroll::employee_profile.index', compact('usersWithoutProfile'));
    }

    public function create(User $user)
    {
        canPerform('Manage Employee Statutory Profile');

        if (EmployeeProfile::where('user_id', $user->id)->exists()) {
            return redirect()->route('backend.indian-payroll.employee-profiles.edit', $user);
        }

        $states = IpState::where('is_active', true)->orderBy('name')->get();

        return view('indianpayroll::employee_profile.form', ['user' => $user, 'profile' => null, 'bankDetail' => null, 'states' => $states]);
    }

    public function store(Request $request, User $user)
    {
        canPerform('Manage Employee Statutory Profile');

        $data = $this->validatedProfileData($request, null);
        $bankData = $this->validatedBankData($request, null);

        DB::transaction(function () use ($user, $data, $bankData) {
            $data['user_id'] = $user->id;
            EmployeeProfile::create($data);

            if ($bankData) {
                $bankData['user_id'] = $user->id;
                BankDetail::create($bankData);
            }
        });

        return redirect()->route('backend.indian-payroll.employee-profiles.show', $user)
            ->with('success', createFlashMessage('Employee Statutory Profile', 'created'));
    }

    public function edit(User $user)
    {
        canPerform('Manage Employee Statutory Profile');

        $profile = EmployeeProfile::where('user_id', $user->id)->firstOrFail();
        $bankDetail = BankDetail::where('user_id', $user->id)->first();
        $states = IpState::where('is_active', true)->orderBy('name')->get();

        return view('indianpayroll::employee_profile.form', compact('user', 'profile', 'bankDetail', 'states'));
    }

    public function update(Request $request, User $user)
    {
        canPerform('Manage Employee Statutory Profile');

        $profile = EmployeeProfile::where('user_id', $user->id)->firstOrFail();

        $data = $this->validatedProfileData($request, $profile);
        $bankData = $this->validatedBankData($request, BankDetail::where('user_id', $user->id)->first());

        DB::transaction(function () use ($profile, $user, $data, $bankData) {
            $profile->update($data);

            if ($bankData) {
                BankDetail::updateOrCreate(['user_id' => $user->id], $bankData);
            }
        });

        return redirect()->route('backend.indian-payroll.employee-profiles.show', $user)
            ->with('success', createFlashMessage('Employee Statutory Profile', 'updated'));
    }

    public function show(User $user)
    {
        canPerform('Manage Employee Statutory Profile');

        $profile = EmployeeProfile::where('user_id', $user->id)->with('state')->firstOrFail();
        $bankDetail = BankDetail::where('user_id', $user->id)->first();

        return view('indianpayroll::employee_profile.show', compact('user', 'profile', 'bankDetail'));
    }

    public function destroy(User $user)
    {
        canPerform('Manage Employee Statutory Profile');

        EmployeeProfile::where('user_id', $user->id)->delete();
        BankDetail::where('user_id', $user->id)->delete();

        return redirect()->route('backend.indian-payroll.employee-profiles.index')
            ->with('success', createFlashMessage('Employee Statutory Profile', 'removed'));
    }

    private function validatedProfileData(Request $request, ?EmployeeProfile $existing): array
    {
        $panRule = 'nullable|string|size:10|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/';
        $panRule .= $existing ? '|unique:ip_employee_profiles,pan,'.$existing->id : '|unique:ip_employee_profiles,pan';

        $uanRule = 'nullable|string|size:12';
        $uanRule .= $existing ? '|unique:ip_employee_profiles,uan,'.$existing->id : '|unique:ip_employee_profiles,uan';

        $validated = $request->validate([
            'pan' => $panRule,
            'aadhaar' => 'nullable|string|size:12',
            'uan' => $uanRule,
            'pf_number' => 'nullable|string|max:50',
            'esi_number' => 'nullable|string|max:17',
            'pt_enrollment_number' => 'nullable|string|max:50',
            'state_id' => 'required|exists:ip_states,id',
            'pf_applicable' => 'boolean',
            'pf_voluntary_above_ceiling' => 'boolean',
            'esi_applicable' => 'boolean',
            'pt_applicable' => 'boolean',
            'lwf_applicable' => 'boolean',
            'date_of_joining' => 'required|date',
            'date_of_exit' => 'nullable|date|after_or_equal:date_of_joining',
            'exit_reason' => 'nullable|in:resignation,termination,retirement,death,disablement',
            'gender' => 'nullable|in:male,female,other',
            'employment_type' => 'required|in:permanent,contract,intern,consultant',
        ]);

        $validated['pf_applicable'] = $request->has('pf_applicable');
        $validated['pf_voluntary_above_ceiling'] = $request->has('pf_voluntary_above_ceiling');
        $validated['esi_applicable'] = $request->has('esi_applicable');
        $validated['pt_applicable'] = $request->has('pt_applicable');
        $validated['lwf_applicable'] = $request->has('lwf_applicable');

        return $validated;
    }

    private function validatedBankData(Request $request, ?BankDetail $existing): ?array
    {
        if (! $request->filled('bank_name')) {
            return null;
        }

        return $request->validate([
            'bank_name' => 'required|string|max:150',
            'account_number' => 'required|string|max:30',
            'ifsc' => 'required|string|size:11|regex:/^[A-Z]{4}0[A-Z0-9]{6}$/',
            'account_type' => 'required|in:savings,current',
            'account_holder_name' => 'required|string|max:150',
        ]);
    }
}
