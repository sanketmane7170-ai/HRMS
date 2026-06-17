<?php

namespace Modules\IndianPayroll\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\IndianPayroll\Entities\SalaryComponent;

class SalaryComponentSeeder extends Seeder
{
    public function run(): void
    {
        $components = [
            // Earnings (part of CTC, structured via templates)
            ['code' => 'BASIC', 'name' => 'Basic Salary', 'type' => 'earning', 'is_taxable' => true, 'is_statutory' => false, 'considered_for_pf_wage' => true, 'order' => 10],
            ['code' => 'HRA', 'name' => 'House Rent Allowance', 'type' => 'earning', 'is_taxable' => true, 'is_statutory' => false, 'considered_for_pf_wage' => false, 'order' => 20],
            ['code' => 'CONVEYANCE', 'name' => 'Conveyance Allowance', 'type' => 'earning', 'is_taxable' => true, 'is_statutory' => false, 'considered_for_pf_wage' => false, 'order' => 30],
            ['code' => 'LTA', 'name' => 'Leave Travel Allowance', 'type' => 'earning', 'is_taxable' => true, 'is_statutory' => false, 'considered_for_pf_wage' => false, 'order' => 40],
            ['code' => 'MEDICAL_ALLOWANCE', 'name' => 'Medical Allowance', 'type' => 'earning', 'is_taxable' => true, 'is_statutory' => false, 'considered_for_pf_wage' => false, 'order' => 50],
            ['code' => 'SPECIAL_ALLOWANCE', 'name' => 'Special Allowance', 'type' => 'earning', 'is_taxable' => true, 'is_statutory' => false, 'considered_for_pf_wage' => false, 'order' => 60],

            // Employee-side statutory deductions (engine-computed, not manually entered)
            ['code' => 'EPF_EMPLOYEE', 'name' => 'Employee Provident Fund (Employee Share)', 'type' => 'deduction', 'is_taxable' => false, 'is_statutory' => true, 'considered_for_pf_wage' => false, 'order' => 110],
            ['code' => 'ESI_EMPLOYEE', 'name' => 'ESI (Employee Share)', 'type' => 'deduction', 'is_taxable' => false, 'is_statutory' => true, 'considered_for_pf_wage' => false, 'order' => 120],
            ['code' => 'PROFESSIONAL_TAX', 'name' => 'Professional Tax', 'type' => 'deduction', 'is_taxable' => false, 'is_statutory' => true, 'considered_for_pf_wage' => false, 'order' => 130],
            ['code' => 'LWF_EMPLOYEE', 'name' => 'Labour Welfare Fund (Employee Share)', 'type' => 'deduction', 'is_taxable' => false, 'is_statutory' => true, 'considered_for_pf_wage' => false, 'order' => 140],
            ['code' => 'TDS', 'name' => 'Income Tax (TDS)', 'type' => 'deduction', 'is_taxable' => false, 'is_statutory' => true, 'considered_for_pf_wage' => false, 'order' => 150],
            ['code' => 'LOSS_OF_PAY', 'name' => 'Loss of Pay', 'type' => 'deduction', 'is_taxable' => false, 'is_statutory' => true, 'considered_for_pf_wage' => false, 'order' => 160],

            // Employer-side contributions (shown on payslip for transparency, not deducted from net pay)
            ['code' => 'EPF_EMPLOYER', 'name' => 'Employer Provident Fund Contribution', 'type' => 'employer_contribution', 'is_taxable' => false, 'is_statutory' => true, 'considered_for_pf_wage' => false, 'order' => 210],
            ['code' => 'EPS_EMPLOYER', 'name' => 'Employer Pension Scheme Contribution', 'type' => 'employer_contribution', 'is_taxable' => false, 'is_statutory' => true, 'considered_for_pf_wage' => false, 'order' => 220],
            ['code' => 'ESI_EMPLOYER', 'name' => 'ESI (Employer Share)', 'type' => 'employer_contribution', 'is_taxable' => false, 'is_statutory' => true, 'considered_for_pf_wage' => false, 'order' => 230],
            ['code' => 'LWF_EMPLOYER', 'name' => 'Labour Welfare Fund (Employer Share)', 'type' => 'employer_contribution', 'is_taxable' => false, 'is_statutory' => true, 'considered_for_pf_wage' => false, 'order' => 240],
            ['code' => 'GRATUITY_PROVISION', 'name' => 'Gratuity Provision', 'type' => 'employer_contribution', 'is_taxable' => false, 'is_statutory' => true, 'considered_for_pf_wage' => false, 'order' => 250],
            ['code' => 'EPF_ADMIN_CHARGES', 'name' => 'EPF Administrative Charges (A/c 2)', 'type' => 'employer_contribution', 'is_taxable' => false, 'is_statutory' => true, 'considered_for_pf_wage' => false, 'order' => 225],
            ['code' => 'EDLI_CHARGES', 'name' => 'EDLI Charges (A/c 21)', 'type' => 'employer_contribution', 'is_taxable' => false, 'is_statutory' => true, 'considered_for_pf_wage' => false, 'order' => 226],

            // Additional CTC-structurable allowances — usable in salary templates exactly
            // like Conveyance/Special Allowance above.
            ['code' => 'TELEPHONE_INTERNET_ALLOWANCE', 'name' => 'Telephone/Internet Allowance', 'type' => 'earning', 'is_taxable' => true, 'is_statutory' => false, 'considered_for_pf_wage' => false, 'order' => 70],
            ['code' => 'MEAL_ALLOWANCE', 'name' => 'Meal Allowance', 'type' => 'earning', 'is_taxable' => true, 'is_statutory' => false, 'considered_for_pf_wage' => false, 'order' => 80],
            ['code' => 'SHIFT_ALLOWANCE', 'name' => 'Shift Allowance', 'type' => 'earning', 'is_taxable' => true, 'is_statutory' => false, 'considered_for_pf_wage' => false, 'order' => 90],
            ['code' => 'EDUCATION_ALLOWANCE', 'name' => 'Education Allowance', 'type' => 'earning', 'is_taxable' => true, 'is_statutory' => false, 'considered_for_pf_wage' => false, 'order' => 100],
            ['code' => 'UNIFORM_ALLOWANCE', 'name' => 'Uniform Allowance', 'type' => 'earning', 'is_taxable' => true, 'is_statutory' => false, 'considered_for_pf_wage' => false, 'order' => 101],
            ['code' => 'CITY_COMPENSATORY_ALLOWANCE', 'name' => 'City Compensatory Allowance', 'type' => 'earning', 'is_taxable' => true, 'is_statutory' => false, 'considered_for_pf_wage' => false, 'order' => 102],

            // Additional CTC-structurable employer contributions (resolved by the salary
            // structure resolver like any other component — no dedicated statutory calculator).
            ['code' => 'EMPLOYER_NPS', 'name' => 'Employer NPS Contribution', 'type' => 'employer_contribution', 'is_taxable' => false, 'is_statutory' => false, 'considered_for_pf_wage' => false, 'order' => 260],
            ['code' => 'SUPERANNUATION', 'name' => 'Superannuation', 'type' => 'employer_contribution', 'is_taxable' => false, 'is_statutory' => false, 'considered_for_pf_wage' => false, 'order' => 270],

            // One-off variable pay — NOT part of CTC, only ever added manually to a single
            // payslip for one payroll run (see PayslipController's manual-component flow).
            ['code' => 'PERFORMANCE_BONUS', 'name' => 'Performance Bonus', 'type' => 'earning', 'is_taxable' => true, 'is_statutory' => false, 'considered_for_pf_wage' => false, 'order' => 300, 'is_part_of_ctc' => false],
            ['code' => 'SALES_INCENTIVE', 'name' => 'Sales Incentive', 'type' => 'earning', 'is_taxable' => true, 'is_statutory' => false, 'considered_for_pf_wage' => false, 'order' => 301, 'is_part_of_ctc' => false],
            ['code' => 'RETENTION_BONUS', 'name' => 'Retention Bonus', 'type' => 'earning', 'is_taxable' => true, 'is_statutory' => false, 'considered_for_pf_wage' => false, 'order' => 302, 'is_part_of_ctc' => false],
            ['code' => 'JOINING_BONUS', 'name' => 'Joining Bonus', 'type' => 'earning', 'is_taxable' => true, 'is_statutory' => false, 'considered_for_pf_wage' => false, 'order' => 303, 'is_part_of_ctc' => false],
            ['code' => 'REFERRAL_BONUS', 'name' => 'Referral Bonus', 'type' => 'earning', 'is_taxable' => true, 'is_statutory' => false, 'considered_for_pf_wage' => false, 'order' => 304, 'is_part_of_ctc' => false],
            ['code' => 'ANNUAL_BONUS', 'name' => 'Annual Bonus', 'type' => 'earning', 'is_taxable' => true, 'is_statutory' => false, 'considered_for_pf_wage' => false, 'order' => 305, 'is_part_of_ctc' => false],

            // Loan/advance EMI recovered from net pay — engine-driven from the
            // employee's active loans, never part of CTC. Non-statutory so it
            // lands in "other deductions".
            ['code' => 'LOAN_RECOVERY', 'name' => 'Loan / Advance Recovery', 'type' => 'deduction', 'is_taxable' => false, 'is_statutory' => false, 'considered_for_pf_wage' => false, 'order' => 170, 'is_part_of_ctc' => false],

            // Reimbursement paid through payroll. Taxable flag varies per claim, so
            // the catalog row is marked non-taxable and the taxable portion is
            // tracked on the reimbursement record itself.
            ['code' => 'REIMBURSEMENT', 'name' => 'Reimbursement', 'type' => 'earning', 'is_taxable' => false, 'is_statutory' => false, 'considered_for_pf_wage' => false, 'order' => 95, 'is_part_of_ctc' => false],

            // Overtime / comp-off payout — attendance-driven extra pay entered per
            // month, taxable, not part of CTC.
            ['code' => 'OVERTIME_ALLOWANCE', 'name' => 'Overtime / Comp-off', 'type' => 'earning', 'is_taxable' => true, 'is_statutory' => false, 'considered_for_pf_wage' => false, 'order' => 96, 'is_part_of_ctc' => false],

            // Mid-service leave encashment paid through payroll (taxable for
            // non-government employees).
            ['code' => 'LEAVE_ENCASHMENT', 'name' => 'Leave Encashment', 'type' => 'earning', 'is_taxable' => true, 'is_statutory' => false, 'considered_for_pf_wage' => false, 'order' => 97, 'is_part_of_ctc' => false],
        ];

        foreach ($components as $c) {
            SalaryComponent::updateOrCreate(
                ['code' => $c['code']],
                [
                    'name' => $c['name'],
                    'type' => $c['type'],
                    'is_taxable' => $c['is_taxable'],
                    'is_statutory' => $c['is_statutory'],
                    'is_part_of_ctc' => $c['is_part_of_ctc'] ?? true,
                    'considered_for_pf_wage' => $c['considered_for_pf_wage'],
                    'is_active' => true,
                    'display_order' => $c['order'],
                ]
            );
        }
    }
}
