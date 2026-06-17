<?php

namespace Modules\IndianPayroll\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryComponent extends Model
{
    use SoftDeletes;

    protected $table = 'ip_salary_components';

    public const TYPE_EARNING = 'earning';
    public const TYPE_DEDUCTION = 'deduction';
    public const TYPE_EMPLOYER_CONTRIBUTION = 'employer_contribution';

    // Well-known statutory component codes the calculation engine writes by convention.
    public const CODE_BASIC = 'BASIC';
    public const CODE_HRA = 'HRA';
    public const CODE_CONVEYANCE = 'CONVEYANCE';
    public const CODE_SPECIAL_ALLOWANCE = 'SPECIAL_ALLOWANCE';
    public const CODE_LTA = 'LTA';
    public const CODE_MEDICAL_ALLOWANCE = 'MEDICAL_ALLOWANCE';
    public const CODE_EPF_EMPLOYEE = 'EPF_EMPLOYEE';
    public const CODE_EPF_EMPLOYER = 'EPF_EMPLOYER';
    public const CODE_EPS_EMPLOYER = 'EPS_EMPLOYER';
    public const CODE_ESI_EMPLOYEE = 'ESI_EMPLOYEE';
    public const CODE_ESI_EMPLOYER = 'ESI_EMPLOYER';
    public const CODE_PROFESSIONAL_TAX = 'PROFESSIONAL_TAX';
    public const CODE_LWF_EMPLOYEE = 'LWF_EMPLOYEE';
    public const CODE_LWF_EMPLOYER = 'LWF_EMPLOYER';
    public const CODE_TDS = 'TDS';
    public const CODE_GRATUITY_PROVISION = 'GRATUITY_PROVISION';
    public const CODE_LOSS_OF_PAY = 'LOSS_OF_PAY';

    // Additional CTC-structurable allowances and employer contributions.
    public const CODE_TELEPHONE_INTERNET_ALLOWANCE = 'TELEPHONE_INTERNET_ALLOWANCE';
    public const CODE_MEAL_ALLOWANCE = 'MEAL_ALLOWANCE';
    public const CODE_SHIFT_ALLOWANCE = 'SHIFT_ALLOWANCE';
    public const CODE_EDUCATION_ALLOWANCE = 'EDUCATION_ALLOWANCE';
    public const CODE_UNIFORM_ALLOWANCE = 'UNIFORM_ALLOWANCE';
    public const CODE_CITY_COMPENSATORY_ALLOWANCE = 'CITY_COMPENSATORY_ALLOWANCE';
    public const CODE_EMPLOYER_NPS = 'EMPLOYER_NPS';
    public const CODE_SUPERANNUATION = 'SUPERANNUATION';

    // One-off variable pay — never part of a recurring CTC structure, only ever
    // added to an individual payslip for a single run via the manual-component flow.
    public const CODE_PERFORMANCE_BONUS = 'PERFORMANCE_BONUS';
    public const CODE_SALES_INCENTIVE = 'SALES_INCENTIVE';
    public const CODE_RETENTION_BONUS = 'RETENTION_BONUS';
    public const CODE_JOINING_BONUS = 'JOINING_BONUS';
    public const CODE_REFERRAL_BONUS = 'REFERRAL_BONUS';
    public const CODE_ANNUAL_BONUS = 'ANNUAL_BONUS';

    protected $fillable = [
        'code', 'name', 'type', 'is_taxable', 'is_statutory', 'is_part_of_ctc',
        'considered_for_pf_wage', 'is_active', 'display_order',
    ];

    protected $casts = [
        'is_taxable' => 'boolean',
        'is_statutory' => 'boolean',
        'is_part_of_ctc' => 'boolean',
        'considered_for_pf_wage' => 'boolean',
        'is_active' => 'boolean',
    ];
}
