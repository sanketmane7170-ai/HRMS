<?php

use App\Http\Controllers\Backend\UserController;
use Illuminate\Support\Facades\Route;
use Modules\Payroll\Http\Controllers\AdvanceRequestController;
use Modules\Payroll\Http\Controllers\EmployeeTaxController;
use Modules\Payroll\Http\Controllers\EmployeeTaxUserController;
use Modules\Payroll\Http\Controllers\UserPaySlipController;
use Modules\Payroll\Http\Controllers\UserSalaryController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(["auth","subscription"])->group(function () {
    Route::as('backend.')->group(function () {
        /*
        |--------------------------------------------------------------------------
        | Admin Side Routes
        |--------------------------------------------------------------------------
    */
        Route::prefix('payroll')->as('payroll.')->group(function () {

            Route::get('user-salaries', [UserSalaryController::class, 'index'])->name('user-salaries.index');
            Route::resource('user.user-salaries', UserSalaryController::class)->except('delete', 'index');

            /* Allowance */
            Route::get('user-salaries/allowance/{user}', [UserSalaryController::class, 'createallowance'])->name('user.user-salaries.createallowance');
            Route::post('user-salaries/allowance/{user}', [UserSalaryController::class, 'storeallowance'])->name('user.user-salaries.storeallowance');
            Route::get('user-salaries/{user}/{allowance}/edit', [UserSalaryController::class, 'editallowance'])->name('user.user-salaries.editallowance');
            Route::put('user-salaries/{user}/{allowance}', [UserSalaryController::class, 'updateallowance'])->name('user.user-salaries.updateallowance');
            Route::delete('user-salaries/delete/{allowance}', [UserSalaryController::class, 'destroyallowance'])->name('user.user-salaries.destroyallowance');

            /* Overtime */
            Route::get('user-salaries/overtime/{user}', [UserSalaryController::class, 'createovertime'])->name('user.user-salaries.createovertime');
            Route::post('user-salaries/overtime/{user}', [UserSalaryController::class, 'storeovertime'])->name('user.user-salaries.storeovertime');
            Route::get('user-salaries/overtime/{user}/{overtime}/list', [UserSalaryController::class, 'userovertimelist'])->name('user.user-salaries.userovertimelist');
            Route::get('user-salaries/overtime/{user}/{overtime}/edit', [UserSalaryController::class, 'editovertime'])->name('user.user-salaries.editovertime');
            Route::put('user-salaries/overtime/{user}/{overtime}', [UserSalaryController::class, 'updateovertime'])->name('user.user-salaries.updateovertime');
            Route::delete('user-salaries/overtime/delete/{overtime}', [UserSalaryController::class, 'destroyovertime'])->name('user.user-salaries.destroyovertime');

            /* Deduction */
            Route::get('user-salaries/deduction/{user}', [UserSalaryController::class, 'creatededuction'])->name('user.user-salaries.creatededuction');
            Route::post('user-salaries/deduction/{user}', [UserSalaryController::class, 'storededuction'])->name('user.user-salaries.storededuction');
            Route::get('user-salaries/deduction/{user}/{deduction}/list', [UserSalaryController::class, 'userdeductionlist'])->name('user.user-salaries.userdeductionlist');
            Route::get('user-salaries/deduction/{user}/{deduction}/edit', [UserSalaryController::class, 'editdeduction'])->name('user.user-salaries.editdeduction');
            Route::put('user-salaries/deduction/{user}/{deduction}', [UserSalaryController::class, 'updatededuction'])->name('user.user-salaries.updatededuction');
            Route::delete('user-salaries/deduction/delete/{deduction}', [UserSalaryController::class, 'destroydeduction'])->name('user.user-salaries.destroydeduction');
            /*Taxes*/
            Route::resource('employee-taxes', EmployeeTaxController::class);
            Route::get('employee-taxes/edit/{id}', [EmployeeTaxController::class, 'edit'])->name('taxes.edit');
            Route::resource('employeeTaxUsers', EmployeeTaxUserController::class);
            Route::get('employeeTaxUsers/edit/{id}', [EmployeeTaxUserController::class, 'edit'])->name('taxmapping.edit');
            /*advanced salary*/
            Route::resource('advance-request', AdvanceRequestController::class);
            Route::get('advance-request/{userid}/request', [AdvanceRequestController::class, 'createRequest'])->name('advance.createRequest');
            Route::get('advance-request/{userid}/editRequest/{id}', [AdvanceRequestController::class, 'editRequest'])->name('advance.editRequest');
            Route::post('advance-request/{userid}/updateAdvanceRequest/{id}', [AdvanceRequestController::class, 'updateAdvanceRequest'])->name('advance.updateAdvanceRequest');
            Route::get('advance-request/{id}/approveRequest', [AdvanceRequestController::class, 'approveRequest'])->name('advance.advancepay-approval');
            Route::post('advance-request/{id}/updateApproval', [AdvanceRequestController::class, 'updateApproval'])->name('advance.approvepay-advancepay');
            Route::post('advance-request/{id}/processUpdate', [AdvanceRequestController::class, 'updateLoan'])->name('advance.processUpdate-advancepay');
            Route::get('advance-request/{id}/updateRequest', [AdvanceRequestController::class, 'updateRequest'])->name('advance.advancepay-update');
            Route::get('advance-request/{id}/detailsRequest', [AdvanceRequestController::class, 'detailsRequest'])->name('advance.advancepay-details');
            Route::delete('advance-request/{id}/deleteRequest', [AdvanceRequestController::class, 'deleteRequest'])->name('advance.deleteRequest');
            Route::get('advance-request-report', [AdvanceRequestController::class, 'advanceRequestReport'])->name('advance.advanceRequestReport');
            Route::get('advance-request/report/pdfExport', [AdvanceRequestController::class, 'pdfExport'])->name('advance.request.report.pdf');
            Route::get('advance-request/report/excelExport', [AdvanceRequestController::class, 'excelExport'])->name('advance.request.report.excel');

            Route::get('advance-request/{id}/updateInstallment', [AdvanceRequestController::class, 'updateInstallments'])->name('advance.advancepay-installment');
            // Route::put('advance-request/{id}', [AdvanceRequestController::class, 'update'])->name('advance.update');
            // Route::get('advance-request/{id}/show', [AdvanceRequestController::class,'show'])->name('advance.show');
            Route::get('user/{user}/user-salaries/hourlysalary/{userId}', [UserSalaryController::class, 'hourlySalary'])->name('user.user-salaries.hourlysalary');
            Route::get('/current-timezone', [UserSalaryController::class, 'getCurrentTimezone']);

            Route::as('employee.')->group(function () {});
            //Allowance & Deduction
            Route::get('set-allowance-deduction', [UserSalaryController::class, 'set_allowance_deducation'])->name('user.allowance_deduction');
            Route::get('set-allowance', [UserSalaryController::class, 'set_allowance'])->name('user.allowance');
            Route::get('update-allowance/{id}', [UserSalaryController::class, 'update_allowance'])->name('user.updateAllowance');
            Route::delete('delete-allowance/{id}', [UserSalaryController::class, 'delete_allowance'])->name('user.deleteAllowance');

            Route::get('set-deduction', [UserSalaryController::class, 'set_deducation'])->name('user.deduction');
            Route::get('update-deduction/{id}', [UserSalaryController::class, 'update_deducation'])->name('user.updateDeduction');
            Route::delete('delete-deduction/{id}', [UserSalaryController::class, 'delete_deducation'])->name('user.deleteDeduction');

            Route::post('save-allowance-deduction', [UserSalaryController::class, 'save_allowance_deducation'])->name('user.save.allowance_deduction');
            Route::post('update-allowance-deduction/{id}', [UserSalaryController::class, 'update_allowance_deducation'])->name('user.update.allowance_deduction');
            // bulk allowance & deduction
            Route::get('bulk-allowance', [UserSalaryController::class, 'add_bulk_allowance_deduction'])->name('user.bulk.allowance');
            Route::post('search-bulk-allowance', [UserSalaryController::class, 'add_bulk_allowance_deduction'])->name('user.search.bulk.allowance');
            Route::post('bulk-allowance-store', [UserSalaryController::class, 'store_bulk_allowance_deduction'])->name('user.bulk.allowance.store');
            /*EMI Allowance */
            Route::get('showEMIAllowance/{user}', [UserSalaryController::class, 'showEMIAllowance'])->name('showEMIAllowance');
            Route::get('createEMIAllowance/{user}/{monthyear}', [UserSalaryController::class, 'createEMIAllowance'])->name('createEMIAllowance');
            Route::post('storeEMIAllowance/{user}/{monthyear}', [UserSalaryController::class, 'storeEMIAllowance'])->name('storeEMIAllowance');
            Route::get('editEMIAllowance/{id}', [UserSalaryController::class, 'editEMIAllowance'])->name('editEMIAllowance');
            Route::post('updateEMIAllowance/{id}', [UserSalaryController::class, 'updateEMIAllowance'])->name('updateEMIAllowance');
            Route::delete('destroyEMIAllowance/{id}', [UserSalaryController::class, 'destroyEMIAllowance'])->name('destroyEMIAllowance');
            /*EMI Deduction */
            Route::get('showEMIDeduction/{user}', [UserSalaryController::class, 'showEMIDeduction'])->name('showEMIDeduction');
            Route::get('createEMIDeduction/{user}/{monthyear}', [UserSalaryController::class, 'createEMIDeduction'])->name('createEMIDeduction');
            Route::post('storeEMIDeduction/{user}/{monthyear}', [UserSalaryController::class, 'storeEMIDeduction'])->name('storeEMIDeduction');
            Route::get('editEMIDeduction/{id}', [UserSalaryController::class, 'editEMIDeduction'])->name('editEMIDeduction');
            Route::post('updateEMIDeduction/{id}', [UserSalaryController::class, 'updateEMIDeduction'])->name('updateEMIDeduction');
            Route::delete('destroyEMIDeduction/{id}', [UserSalaryController::class, 'destroyEMIDeduction'])->name('destroyEMIDeduction');
        });

        Route::prefix('payslip')->as('payslip.')->group(function () {
            Route::resource('user-payslip', UserPaySlipController::class);
            Route::post('payslip/user-payslip/delete', [UserPaySlipController::class, 'destroy'])
                ->name('user-payslip.delete.custom');
            Route::post('payslip/user-payslip/delete', [UserPaySlipController::class, 'destroy'])
                ->name('user-payslip.destroy');

            Route::get('user-payslip/{user}/{payslip}/edit', [UserPaySlipController::class, 'editPaySlip'])->name('user-payslip.editpayslip');

            Route::get('allowance/{allowance}/show', [UserPaySlipController::class, 'showallowance'])->name('allowance.showallowance');
            Route::get('allowance/{user}/{monthyear}/create', [UserPaySlipController::class, 'createallowance'])->name('allowance.createallowance');

            Route::get('overtime/{user}/show', [UserPaySlipController::class, 'showovertime'])->name('overtime.showovertime');
            Route::get('overtime/{user}/{monthyear}/create', [UserPaySlipController::class, 'createovertime'])->name('overtime.createovertime');

            Route::get('deduction/{user}/show', [UserPaySlipController::class, 'showdeduction'])->name('deduction.showdeduction');
            Route::get('deduction/{user}/{monthyear}/create', [UserPaySlipController::class, 'creatededuction'])->name('deduction.creatededuction');

            Route::get('expense/{user}/show', [UserPaySlipController::class, 'showexpense'])->name('expense.showexpense');

            Route::get('payslip/export/{month}/{year}/{company?}', [UserPaySlipController::class, 'payslipexport'])->name('export');
            Route::post('gratuity_report_download/reports', [UserPaySlipController::class, 'gratuity_report_download'])->name('gratuity_report_download');
            Route::post('medical_insurance_report_download/reports', [UserPaySlipController::class, 'medical_insurance_report_download'])->name('medical_insurance_report_download');
            Route::post('air_ticket_report_download/reports', [UserPaySlipController::class, 'air_ticket_report_download'])->name('air_ticket_report_download');
            Route::post('leave_salary_report_download/reports', [UserPaySlipController::class, 'leave_salary_report_download'])->name('leave_salary_report_download');

            Route::get('payslip/invoice/{user}/{payslip}', [UserPaySlipController::class, 'openinvoice'])->name('invoice');

            Route::get('generate/sif/{month}/{year}/{company?}', [UserPaySlipController::class, 'sifexport'])->name('sif.export');
            Route::get('/payslip/export/sif/xls/{month}/{year}/{company?}', [UserPaySlipController::class, 'sifexportxls'])->name('sif.export.xls');
            Route::get('/payslip/export/sif/sif/{month}/{year}/{company?}', [UserPaySlipController::class, 'sifexportsif'])->name('sif.export.sif');
            Route::post('/company-document/{id}/save_sif_settings', [UserPaySlipController::class, 'saveSifSettings'])->name('save_sif_settings');
            Route::get('/company-document/{id}/sif_settings', [UserPaySlipController::class, 'getSifSettings'])->name('sif_settings');

            Route::get('close/report/{month}/{year}/{company?}', [UserPaySlipController::class, 'close_report'])->name('close.report');
            Route::get('showSalaryTransaction', [UserPaySlipController::class, 'showSalaryTransaction'])->name('showSalaryTransaction');

            Route::get('get_payroll_details/{month?}/{company?}', [UserPaySlipController::class, 'get_payroll_details'])->name('get_payroll_details');

        });

        Route::prefix('settlement')->as('settlement.')->group(function () {
            Route::get('transactions', [UserController::class, 'getTransactionList'])->name('transaction');
        });

        /*
        |--------------------------------------------------------------------------
        | User Side Routes
        |--------------------------------------------------------------------------
    */
        Route::prefix('my-salary')->as('my-salary.')->group(function () {
            Route::get('/', [UserSalaryController::class, 'getUserSalary'])->name('getViewSalary');
            Route::get('allowance', [UserSalaryController::class, 'getUserSalaryAllowance'])->name('allowance');
            Route::get('overtime', [UserSalaryController::class, 'getUserSalaryOvertime'])->name('overtime');
            Route::get('deduction', [UserSalaryController::class, 'getUserSalaryDeduction'])->name('deduction');
            Route::get('payslip', [UserPaySlipController::class, 'getUserSalaryPayslip'])->name('getViewPayslip');
            Route::get('payslip/invoice/{payslip}', [UserPaySlipController::class, 'viewPayslipModal'])->name('viewPayslipModal');
        });
    });
});
