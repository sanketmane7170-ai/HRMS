<?php

namespace Modules\IndianPayroll\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Modules\IndianPayroll\Entities\SalaryComponent;

class Payslip extends Model
{
    protected $table = 'ip_payslips';

    protected $fillable = [
        'run_id', 'user_id', 'gross_earnings', 'total_statutory_deductions',
        'total_other_deductions', 'total_employer_contributions', 'net_pay',
        'days_in_period', 'paid_days', 'loss_of_pay_days', 'tax_regime', 'status',
    ];

    protected $casts = [
        'gross_earnings' => 'decimal:2',
        'total_statutory_deductions' => 'decimal:2',
        'total_other_deductions' => 'decimal:2',
        'total_employer_contributions' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'paid_days' => 'decimal:2',
        'loss_of_pay_days' => 'decimal:2',
    ];

    public function run()
    {
        return $this->belongsTo(PayrollRun::class, 'run_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function components()
    {
        return $this->hasMany(PayslipComponent::class, 'payslip_id');
    }

    public function componentAmount(string $code): float
    {
        return (float) $this->components
            ->first(fn (PayslipComponent $c) => optional($c->component)->code === $code)?->amount ?? 0.0;
    }

    /**
     * Single source of truth for the four summary totals, derived entirely from the
     * current component rows. gross_earnings always means the actual payable gross
     * (engine-computed prorated earnings + any manual earning/bonus rows) — never the
     * pre-LOP contracted figure.
     */
    public function recalculateTotals(): void
    {
        $this->loadMissing('components.component');

        $gross = $this->components->where('type', SalaryComponent::TYPE_EARNING)->sum('amount');

        $statutoryDeductions = $this->components
            ->filter(fn (PayslipComponent $c) => $c->type === SalaryComponent::TYPE_DEDUCTION
                && ! $c->is_manual
                && optional($c->component)->is_statutory)
            ->sum('amount');

        $otherDeductions = $this->components
            ->filter(fn (PayslipComponent $c) => $c->type === SalaryComponent::TYPE_DEDUCTION
                && ($c->is_manual || ! optional($c->component)->is_statutory))
            ->sum('amount');

        $employerContributions = $this->components->where('type', SalaryComponent::TYPE_EMPLOYER_CONTRIBUTION)->sum('amount');

        $this->update([
            'gross_earnings' => round($gross, 2),
            'total_statutory_deductions' => round($statutoryDeductions, 2),
            'total_other_deductions' => round($otherDeductions, 2),
            'total_employer_contributions' => round($employerContributions, 2),
            'net_pay' => round(max(0, $gross - $statutoryDeductions - $otherDeductions), 2),
        ]);
    }
}
