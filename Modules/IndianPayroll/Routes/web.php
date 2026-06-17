<?php

use Illuminate\Support\Facades\Route;
use Modules\IndianPayroll\Http\Controllers\DashboardController;
use Modules\IndianPayroll\Http\Controllers\EmployeeProfileController;
use Modules\IndianPayroll\Http\Controllers\SalaryComponentController;
use Modules\IndianPayroll\Http\Controllers\SalaryStructureTemplateController;
use Modules\IndianPayroll\Http\Controllers\EmployeeSalaryStructureController;
use Modules\IndianPayroll\Http\Controllers\StatutorySettingController;
use Modules\IndianPayroll\Http\Controllers\IncomeTaxSlabController;
use Modules\IndianPayroll\Http\Controllers\ProfessionalTaxSlabController;
use Modules\IndianPayroll\Http\Controllers\LwfRuleController;
use Modules\IndianPayroll\Http\Controllers\PayrollRunController;
use Modules\IndianPayroll\Http\Controllers\PayslipController;
use Modules\IndianPayroll\Http\Controllers\TaxDeclarationController;
use Modules\IndianPayroll\Http\Controllers\Form16Controller;
use Modules\IndianPayroll\Http\Controllers\SettlementController;
use Modules\IndianPayroll\Http\Controllers\ComplianceReportController;
use Modules\IndianPayroll\Http\Controllers\EmployeeLoanController;
use Modules\IndianPayroll\Http\Controllers\ReimbursementController;
use Modules\IndianPayroll\Http\Controllers\PayrollOutputController;
use Modules\IndianPayroll\Http\Controllers\OvertimeController;
use Modules\IndianPayroll\Http\Controllers\LeaveEncashmentController;
use Modules\IndianPayroll\Http\Controllers\StatutoryBonusController;
use Modules\IndianPayroll\Http\Controllers\Employee\MyTaxDeclarationController;
use Modules\IndianPayroll\Http\Controllers\Employee\MyPayslipController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'subscription'])->group(function () {
    Route::as('backend.')->group(function () {

        Route::prefix('indian-payroll')->as('indian-payroll.')->group(function () {

            Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

            /*
            |----------------------------------------------------------------
            | Employee Statutory Profile (PAN/Aadhaar/UAN/Bank/State)
            |----------------------------------------------------------------
            */
            Route::prefix('employee-profiles')->as('employee-profiles.')->group(function () {
                Route::get('/', [EmployeeProfileController::class, 'index'])->name('index');
                Route::get('create/{user}', [EmployeeProfileController::class, 'create'])->name('create');
                Route::post('store/{user}', [EmployeeProfileController::class, 'store'])->name('store');
                Route::get('edit/{user}', [EmployeeProfileController::class, 'edit'])->name('edit');
                Route::put('update/{user}', [EmployeeProfileController::class, 'update'])->name('update');
                Route::get('show/{user}', [EmployeeProfileController::class, 'show'])->name('show');
                Route::delete('destroy/{user}', [EmployeeProfileController::class, 'destroy'])->name('destroy');
            });

            /*
            |----------------------------------------------------------------
            | Salary Component Catalog
            |----------------------------------------------------------------
            */
            Route::prefix('salary-components')->as('salary-components.')->group(function () {
                Route::get('/', [SalaryComponentController::class, 'index'])->name('index');
                Route::get('create', [SalaryComponentController::class, 'create'])->name('create');
                Route::post('store', [SalaryComponentController::class, 'store'])->name('store');
                Route::get('edit/{salaryComponent}', [SalaryComponentController::class, 'edit'])->name('edit');
                Route::put('update/{salaryComponent}', [SalaryComponentController::class, 'update'])->name('update');
                Route::delete('destroy/{salaryComponent}', [SalaryComponentController::class, 'destroy'])->name('destroy');
            });

            /*
            |----------------------------------------------------------------
            | CTC Structure Templates
            |----------------------------------------------------------------
            */
            Route::prefix('salary-templates')->as('salary-templates.')->group(function () {
                Route::get('/', [SalaryStructureTemplateController::class, 'index'])->name('index');
                Route::get('create', [SalaryStructureTemplateController::class, 'create'])->name('create');
                Route::post('store', [SalaryStructureTemplateController::class, 'store'])->name('store');
                Route::get('edit/{template}', [SalaryStructureTemplateController::class, 'edit'])->name('edit');
                Route::put('update/{template}', [SalaryStructureTemplateController::class, 'update'])->name('update');
                Route::delete('destroy/{template}', [SalaryStructureTemplateController::class, 'destroy'])->name('destroy');
                Route::post('{template}/components', [SalaryStructureTemplateController::class, 'addComponent'])->name('components.add');
                Route::delete('{template}/components/{component}', [SalaryStructureTemplateController::class, 'removeComponent'])->name('components.remove');
            });

            /*
            |----------------------------------------------------------------
            | Employee Salary Structure Assignment (CTC breakup per employee)
            |----------------------------------------------------------------
            */
            Route::prefix('employee-salary-structures')->as('employee-salary-structures.')->group(function () {
                Route::get('/', [EmployeeSalaryStructureController::class, 'index'])->name('index');
                Route::get('assign/{user}', [EmployeeSalaryStructureController::class, 'create'])->name('create');
                Route::post('assign/{user}', [EmployeeSalaryStructureController::class, 'store'])->name('store');
                Route::get('show/{user}', [EmployeeSalaryStructureController::class, 'show'])->name('show');
                Route::get('revise/{user}', [EmployeeSalaryStructureController::class, 'revise'])->name('revise');
            });

            /*
            |----------------------------------------------------------------
            | Statutory Configuration (PF / ESI / Gratuity)
            |----------------------------------------------------------------
            */
            Route::prefix('statutory-settings')->as('statutory-settings.')->group(function () {
                Route::get('/', [StatutorySettingController::class, 'index'])->name('index');
                Route::post('pf', [StatutorySettingController::class, 'storePf'])->name('pf.store');
                Route::post('esi', [StatutorySettingController::class, 'storeEsi'])->name('esi.store');
                Route::post('gratuity', [StatutorySettingController::class, 'storeGratuity'])->name('gratuity.store');
            });

            /*
            |----------------------------------------------------------------
            | Income Tax Slabs (Old & New regime, financial-year wise)
            |----------------------------------------------------------------
            */
            Route::prefix('tax-slabs')->as('tax-slabs.')->group(function () {
                Route::get('/', [IncomeTaxSlabController::class, 'index'])->name('index');
                Route::post('store', [IncomeTaxSlabController::class, 'store'])->name('store');
                Route::delete('destroy/{slab}', [IncomeTaxSlabController::class, 'destroy'])->name('destroy');
            });

            /*
            |----------------------------------------------------------------
            | Professional Tax Slabs (state-wise)
            |----------------------------------------------------------------
            */
            Route::prefix('professional-tax')->as('professional-tax.')->group(function () {
                Route::get('/', [ProfessionalTaxSlabController::class, 'index'])->name('index');
                Route::post('store', [ProfessionalTaxSlabController::class, 'store'])->name('store');
                Route::delete('destroy/{slab}', [ProfessionalTaxSlabController::class, 'destroy'])->name('destroy');
            });

            /*
            |----------------------------------------------------------------
            | Labour Welfare Fund Rules (state-wise)
            |----------------------------------------------------------------
            */
            Route::prefix('lwf-rules')->as('lwf-rules.')->group(function () {
                Route::get('/', [LwfRuleController::class, 'index'])->name('index');
                Route::post('store', [LwfRuleController::class, 'store'])->name('store');
                Route::delete('destroy/{rule}', [LwfRuleController::class, 'destroy'])->name('destroy');
            });

            /*
            |----------------------------------------------------------------
            | Payroll Run (draft -> computed -> approved -> locked)
            |----------------------------------------------------------------
            */
            Route::prefix('payroll-runs')->as('payroll-runs.')->group(function () {
                Route::get('/', [PayrollRunController::class, 'index'])->name('index');
                Route::post('store', [PayrollRunController::class, 'store'])->name('store');
                Route::get('{run}', [PayrollRunController::class, 'show'])->name('show');
                Route::post('{run}/compute', [PayrollRunController::class, 'compute'])->name('compute');
                Route::post('{run}/approve', [PayrollRunController::class, 'approve'])->name('approve');
                Route::post('{run}/lock', [PayrollRunController::class, 'lock'])->name('lock');
                Route::delete('{run}', [PayrollRunController::class, 'destroy'])->name('destroy');

                // Run outputs: bank file, accounting JV, management/finance registers.
                Route::get('{run}/bank-file', [PayrollOutputController::class, 'bankTransferFile'])->name('bank-file');
                Route::get('{run}/journal-voucher', [PayrollOutputController::class, 'journalVoucher'])->name('journal-voucher');
                Route::get('{run}/salary-register', [PayrollOutputController::class, 'salaryRegister'])->name('salary-register');
                Route::get('{run}/payroll-summary', [PayrollOutputController::class, 'payrollSummary'])->name('payroll-summary');
                Route::get('{run}/department-cost', [PayrollOutputController::class, 'departmentCost'])->name('department-cost');
                Route::get('{run}/salary-variance', [PayrollOutputController::class, 'salaryVariance'])->name('salary-variance');
                Route::get('{run}/pf-ecr', [PayrollOutputController::class, 'pfEcr'])->name('pf-ecr');
            });

            /*
            |----------------------------------------------------------------
            | Payslips
            |----------------------------------------------------------------
            */
            Route::prefix('payslips')->as('payslips.')->group(function () {
                Route::get('{payslip}', [PayslipController::class, 'show'])->name('show');
                Route::get('{payslip}/edit', [PayslipController::class, 'edit'])->name('edit');
                Route::put('{payslip}', [PayslipController::class, 'update'])->name('update');
                Route::delete('{payslip}/deductions/{component}', [PayslipController::class, 'destroyManualDeduction'])->name('deductions.destroy');
                Route::get('{payslip}/download', [PayslipController::class, 'download'])->name('download');
            });

            /*
            |----------------------------------------------------------------
            | Tax Declarations - HR verification queue
            |----------------------------------------------------------------
            */
            Route::prefix('tax-declarations')->as('tax-declarations.')->group(function () {
                Route::get('/', [TaxDeclarationController::class, 'index'])->name('index');
                Route::get('{declaration}', [TaxDeclarationController::class, 'show'])->name('show');
                Route::get('investment/{investmentDeclaration}/proof', [TaxDeclarationController::class, 'downloadProof'])->name('investment.proof');
                Route::post('investment/{investmentDeclaration}/verify', [TaxDeclarationController::class, 'verify'])->name('verify');
            });

            /*
            |----------------------------------------------------------------
            | Form 16 / Form 24Q
            |----------------------------------------------------------------
            */
            Route::prefix('forms')->as('forms.')->group(function () {
                Route::get('form16/{user}/{financialYear}', [Form16Controller::class, 'download'])->name('form16');
                Route::get('form24q/{financialYear}/{quarter}', [Form16Controller::class, 'form24q'])->name('form24q');
            });

            /*
            |----------------------------------------------------------------
            | Full & Final Settlement
            |----------------------------------------------------------------
            */
            Route::prefix('settlements')->as('settlements.')->group(function () {
                Route::get('/', [SettlementController::class, 'index'])->name('index');
                Route::get('create/{user}', [SettlementController::class, 'create'])->name('create');
                Route::post('store/{user}', [SettlementController::class, 'store'])->name('store');
                Route::get('{settlement}', [SettlementController::class, 'show'])->name('show');
                Route::post('{settlement}/approve', [SettlementController::class, 'approve'])->name('approve');
                Route::get('{settlement}/download', [SettlementController::class, 'download'])->name('download');
            });

            /*
            |----------------------------------------------------------------
            | Loans & Advances (recovered via payroll EMIs)
            |----------------------------------------------------------------
            */
            Route::prefix('loans')->as('loans.')->group(function () {
                Route::get('/', [EmployeeLoanController::class, 'index'])->name('index');
                Route::get('create', [EmployeeLoanController::class, 'create'])->name('create');
                Route::post('store', [EmployeeLoanController::class, 'store'])->name('store');
                Route::get('{loan}', [EmployeeLoanController::class, 'show'])->name('show');
                Route::post('{loan}/cancel', [EmployeeLoanController::class, 'cancel'])->name('cancel');
            });

            /*
            |----------------------------------------------------------------
            | Reimbursements (paid through payroll)
            |----------------------------------------------------------------
            */
            Route::prefix('reimbursements')->as('reimbursements.')->group(function () {
                Route::get('/', [ReimbursementController::class, 'index'])->name('index');
                Route::get('create', [ReimbursementController::class, 'create'])->name('create');
                Route::post('store', [ReimbursementController::class, 'store'])->name('store');
                Route::post('{reimbursement}/approve', [ReimbursementController::class, 'approve'])->name('approve');
                Route::post('{reimbursement}/reject', [ReimbursementController::class, 'reject'])->name('reject');
                Route::get('{reimbursement}/proof', [ReimbursementController::class, 'downloadProof'])->name('proof');
            });

            /*
            |----------------------------------------------------------------
            | Overtime & Comp-off (paid through payroll)
            |----------------------------------------------------------------
            */
            Route::prefix('overtime')->as('overtime.')->group(function () {
                Route::get('/', [OvertimeController::class, 'index'])->name('index');
                Route::get('create', [OvertimeController::class, 'create'])->name('create');
                Route::post('store', [OvertimeController::class, 'store'])->name('store');
                Route::post('{overtime}/approve', [OvertimeController::class, 'approve'])->name('approve');
                Route::post('{overtime}/reject', [OvertimeController::class, 'reject'])->name('reject');
            });

            /*
            |----------------------------------------------------------------
            | Mid-service Leave Encashment (paid through payroll)
            |----------------------------------------------------------------
            */
            Route::prefix('leave-encashment')->as('leave-encashment.')->group(function () {
                Route::get('/', [LeaveEncashmentController::class, 'index'])->name('index');
                Route::get('create', [LeaveEncashmentController::class, 'create'])->name('create');
                Route::post('store', [LeaveEncashmentController::class, 'store'])->name('store');
                Route::post('{encashment}/approve', [LeaveEncashmentController::class, 'approve'])->name('approve');
                Route::post('{encashment}/reject', [LeaveEncashmentController::class, 'reject'])->name('reject');
            });

            /*
            |----------------------------------------------------------------
            | Statutory Bonus (Payment of Bonus Act)
            |----------------------------------------------------------------
            */
            Route::prefix('statutory-bonus')->as('statutory-bonus.')->group(function () {
                Route::get('/', [StatutoryBonusController::class, 'index'])->name('index');
                Route::post('generate', [StatutoryBonusController::class, 'generate'])->name('generate');
                Route::post('{bonus}/approve', [StatutoryBonusController::class, 'approve'])->name('approve');
            });

            /*
            |----------------------------------------------------------------
            | Statutory Compliance Reports
            |----------------------------------------------------------------
            */
            Route::prefix('reports')->as('reports.')->group(function () {
                Route::get('pf/{run}', [ComplianceReportController::class, 'pfRegister'])->name('pf');
                Route::get('esi/{run}', [ComplianceReportController::class, 'esiRegister'])->name('esi');
                Route::get('pt/{run}', [ComplianceReportController::class, 'ptRegister'])->name('pt');
                Route::get('lwf/{run}', [ComplianceReportController::class, 'lwfRegister'])->name('lwf');
                Route::get('gratuity', [ComplianceReportController::class, 'gratuityRegister'])->name('gratuity');
            });
        });

        /*
        |--------------------------------------------------------------------
        | Employee Self-Service (My Salary / My Tax Declaration)
        |--------------------------------------------------------------------
        */
        Route::prefix('my-indian-payroll')->as('my-indian-payroll.')->group(function () {
            Route::get('payslips', [MyPayslipController::class, 'index'])->name('payslips.index');
            Route::get('payslips/{payslip}/download', [MyPayslipController::class, 'download'])->name('payslips.download');

            Route::get('tax-declaration', [MyTaxDeclarationController::class, 'index'])->name('tax-declaration.index');
            Route::post('tax-declaration/regime', [MyTaxDeclarationController::class, 'chooseRegime'])->name('tax-declaration.regime');
            Route::post('tax-declaration/investment', [MyTaxDeclarationController::class, 'storeInvestment'])->name('tax-declaration.investment.store');
            Route::post('tax-declaration/hra', [MyTaxDeclarationController::class, 'storeHra'])->name('tax-declaration.hra.store');
        });
    });
});
