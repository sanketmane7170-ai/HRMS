<?php

namespace Modules\IndianPayroll\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\IndianPayroll\Entities\EmployeeProfile;
use Modules\IndianPayroll\Entities\EmployeeSalaryStructure;
use Modules\IndianPayroll\Entities\SalaryComponent;
use Modules\IndianPayroll\Entities\StatutoryBonus;
use Modules\IndianPayroll\Services\Tax\FinancialYearHelper;

class StatutoryBonusController extends Controller
{
    public function __construct()
    {
        view()->share('activeLink', 'indian-payroll.statutory-bonus');
    }

    public function index(Request $request)
    {
        canPerform('Manage Indian Payroll');

        $financialYear = $request->get('financial_year', FinancialYearHelper::forDate(now()));

        $bonuses = StatutoryBonus::with('user')
            ->where('financial_year', $financialYear)
            ->orderByDesc('bonus_amount')
            ->paginate(25);

        return view('indianpayroll::statutory_bonus.index', compact('bonuses', 'financialYear'));
    }

    /**
     * Compute Payment of Bonus Act bonus for every eligible employee for a
     * financial year. Eligible = basic+DA at or below the ₹21,000 ceiling; the
     * chosen percentage (8.33%–20%) is applied to min(wage, ₹7,000) for each
     * eligible month worked in the year.
     */
    public function generate(Request $request)
    {
        canPerform('Manage Indian Payroll');

        $data = $request->validate([
            'financial_year' => 'required|string',
            'percentage' => 'required|numeric|min:8.33|max:20',
        ]);

        $fy = $data['financial_year'];
        $pct = (float) $data['percentage'];
        $fyStart = FinancialYearHelper::startDate($fy);
        $fyEnd = FinancialYearHelper::endDate($fy);

        $profiles = EmployeeProfile::with('user')->get();
        $generated = 0;

        foreach ($profiles as $profile) {
            $structure = EmployeeSalaryStructure::where('user_id', $profile->user_id)
                ->where('effective_from', '<=', $fyEnd)
                ->orderByDesc('effective_from')
                ->with('components.component')
                ->first();

            if (! $structure) {
                continue;
            }

            $basicDa = (float) $structure->componentAmount(SalaryComponent::CODE_BASIC);
            if ($basicDa <= 0 || $basicDa > StatutoryBonus::ELIGIBILITY_WAGE_CEILING) {
                continue; // not eligible (no basic, or above the wage ceiling)
            }

            $months = $this->monthsEligibleInYear($profile->date_of_joining, $profile->date_of_exit, $fyStart, $fyEnd);
            if ($months <= 0) {
                continue;
            }

            $bonusBase = min($basicDa, StatutoryBonus::CALCULATION_WAGE_CAP);
            $bonus = round($bonusBase * ($pct / 100) * $months, 2);

            StatutoryBonus::updateOrCreate(
                ['user_id' => $profile->user_id, 'financial_year' => $fy],
                [
                    'monthly_wage' => $basicDa,
                    'bonus_wage_base' => $bonusBase,
                    'percentage' => $pct,
                    'months_eligible' => $months,
                    'bonus_amount' => $bonus,
                    'status' => StatutoryBonus::STATUS_DRAFT,
                ]
            );
            $generated++;
        }

        return redirect()->route('backend.indian-payroll.statutory-bonus.index', ['financial_year' => $fy])
            ->with('success', "Statutory bonus generated for {$generated} eligible employee(s) at {$pct}%.");
    }

    public function approve(StatutoryBonus $bonus)
    {
        canPerform('Manage Indian Payroll');

        $bonus->update(['status' => StatutoryBonus::STATUS_APPROVED]);

        return back()->with('success', 'Bonus approved. Pay it via an Annual Bonus line on the employee\'s payslip.');
    }

    /** Whole months in the financial year during which the employee was on rolls. */
    private function monthsEligibleInYear(?Carbon $joining, ?Carbon $exit, Carbon $fyStart, Carbon $fyEnd): int
    {
        $start = $joining && $joining->gt($fyStart) ? $joining->copy() : $fyStart->copy();
        $end = $exit && $exit->lt($fyEnd) ? $exit->copy() : $fyEnd->copy();

        if ($start->gt($end)) {
            return 0;
        }

        return min(12, $start->diffInMonths($end) + 1);
    }
}
