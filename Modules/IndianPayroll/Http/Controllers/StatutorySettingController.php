<?php

namespace Modules\IndianPayroll\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\IndianPayroll\Entities\EsiSetting;
use Modules\IndianPayroll\Entities\GratuitySetting;
use Modules\IndianPayroll\Entities\PfSetting;

class StatutorySettingController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'indian-payroll.statutory-settings');
    }

    public function index()
    {
        canPerform('Manage Statutory Settings');

        $pfSettings = PfSetting::orderByDesc('effective_from')->get();
        $esiSettings = EsiSetting::orderByDesc('effective_from')->get();
        $gratuitySettings = GratuitySetting::orderByDesc('effective_from')->get();

        return view('indianpayroll::statutory_settings.index', compact('pfSettings', 'esiSettings', 'gratuitySettings'));
    }

    public function storePf(Request $request)
    {
        canPerform('Manage Statutory Settings');

        $data = $request->validate([
            'effective_from' => 'required|date',
            'employee_rate' => 'required|numeric|min:0|max:100',
            'employer_rate' => 'required|numeric|min:0|max:100',
            'eps_rate' => 'required|numeric|min:0|max:100',
            'wage_ceiling' => 'required|numeric|min:0',
            'eps_wage_ceiling' => 'required|numeric|min:0',
            'admin_charges_rate' => 'required|numeric|min:0|max:100',
        ]);

        $data['is_active'] = true;
        PfSetting::create($data);

        return redirect()->route('backend.indian-payroll.statutory-settings.index')
            ->with('success', createFlashMessage('PF Settings', 'added'));
    }

    public function storeEsi(Request $request)
    {
        canPerform('Manage Statutory Settings');

        $data = $request->validate([
            'effective_from' => 'required|date',
            'employee_rate' => 'required|numeric|min:0|max:100',
            'employer_rate' => 'required|numeric|min:0|max:100',
            'wage_threshold' => 'required|numeric|min:0',
            'wage_threshold_disabled' => 'required|numeric|min:0',
        ]);

        $data['is_active'] = true;
        EsiSetting::create($data);

        return redirect()->route('backend.indian-payroll.statutory-settings.index')
            ->with('success', createFlashMessage('ESI Settings', 'added'));
    }

    public function storeGratuity(Request $request)
    {
        canPerform('Manage Statutory Settings');

        $data = $request->validate([
            'effective_from' => 'required|date',
            'exemption_ceiling' => 'required|numeric|min:0',
            'days_per_year_first_slab' => 'required|integer|min:1',
            'divisor_days_per_month' => 'required|integer|min:1',
            'minimum_vesting_years' => 'required|integer|min:0',
        ]);

        $data['is_active'] = true;
        GratuitySetting::create($data);

        return redirect()->route('backend.indian-payroll.statutory-settings.index')
            ->with('success', createFlashMessage('Gratuity Settings', 'added'));
    }
}
