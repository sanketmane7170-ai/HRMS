<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Backend\AccountController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\DepartmentController;
use App\Http\Controllers\Backend\DesignationController;
use App\Http\Controllers\Backend\DivisionController;
use App\Http\Controllers\Backend\FeatureController;
use App\Http\Controllers\Backend\GeneralRequestController;
use App\Http\Controllers\Backend\LeaveApprovalSettingController;
use App\Http\Controllers\Backend\NotificationController;
use App\Http\Controllers\Backend\ReportsController;
use App\Http\Controllers\Backend\RoleController;
use App\Http\Controllers\Backend\SettingController;
use App\Http\Controllers\Backend\UserController;
use App\Http\Controllers\Backend\UserDependentController;
use App\Http\Controllers\Backend\UserDocumentController;
use App\Http\Controllers\Backend\UserImportExportController;
use App\Http\Controllers\Backend\UserPromotionController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\Employee\DependentController as EmployeeDependentController;
use App\Http\Controllers\Employee\DocumentController;
use App\Http\Controllers\Employee\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Modules\PerformanceReview\Http\Controllers\EmployeeKpiResponseController;
use Modules\PerformanceReview\Http\Controllers\ReviewResponseController;
use Modules\Performance\Http\Controllers\PerformanceAppraisalController;
use Modules\Training\Http\Controllers\TrainingController;
use App\Http\Controllers\Backend\AirTicketRequestController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Auth::routes(['register' => false]);
Route::view('/', 'auth.login')->middleware('guest');
Route::middleware('guest')->group(function () {
    // show login page (GET /login) - name it 'login' so route('login') works
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');

    // credentials submit (POST /login)
    Route::post('login', [LoginController::class, 'otplogin'])->name('login.perform');

    // OTP verification
    Route::post('login/verify-otp', [LoginController::class, 'verifyOtp'])->name('login.verifyOtp');

    // Resend OTP
    Route::post('login/resend-otp', [LoginController::class, 'resendOtp'])->name('login.resendOtp');
});
Route::get('gk/logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);

Route::middleware(["auth", "subscription"])->group(function () {
    Route::get('check-redirection', [CommonController::class, 'checkRedirection'])->name('home');

    Route::post('/send-message', [ChatController::class, 'sendMessage']);
    Route::get('/messages/{userId}', [ChatController::class, 'fetchMessages']);

    Route::as('backend.')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('teams', [UserController::class, 'showRoleBasedHierarchy'])->name('teams');

        Route::post('dashboard/user_checkin', [DashboardController::class, 'user_checkin'])->name('dashboard.user_checkin');

        Route::get('reports', [ReportsController::class, 'index'])->name('reports');
        Route::get('reports/leaves_report', [ReportsController::class, 'leaves_report'])->name('reports.leaves_report');
        Route::post('reports/leaves_report', [ReportsController::class, 'leaves_report'])->name('reports.leaves_report_search');
        Route::get('reports/leaves_report_export', [ReportsController::class, 'leaves_report_export'])->name('reports.leaves_report_export');
        Route::get('reports/attendance_report', [ReportsController::class, 'attendance_report'])->name('reports.attendance_report');
        Route::post('reports/attendance_report', [ReportsController::class, 'attendance_report'])->name('reports.attendance_report_search');
        Route::get('reports/attendance_report_export', [ReportsController::class, 'attendance_report_export'])->name('reports.attendance_report_export');
        Route::get('reports/late_comers_report', [ReportsController::class, 'late_comers_report'])->name('reports.late_comers_report');
        Route::post('reports/late_comers_report', [ReportsController::class, 'late_comers_report'])->name('reports.late_comers_report_search');
        Route::get('reports/late_comers_report_export', [ReportsController::class, 'late_comers_report_export'])->name('reports.late_comers_report_export');
        Route::get('reports/early_comers_report', [ReportsController::class, 'early_comers_report'])->name('reports.early_comers_report');
        Route::post('reports/early_comers_report', [ReportsController::class, 'early_comers_report'])->name('reports.early_comers_report_search');
        Route::get('reports/early_outs_report', [ReportsController::class, 'early_outs_report'])->name('reports.early_outs_report');
        Route::post('reports/early_outs_report', [ReportsController::class, 'early_outs_report'])->name('reports.early_outs_report_search');

        Route::get('reports/increment_report', [ReportsController::class, 'increment_report'])->name('reports.increment_report');
        Route::post('reports/increment_report', [ReportsController::class, 'increment_report'])->name('reports.increment_report_search');

        Route::get('reports/expense_report', [ReportsController::class, 'expense_report'])->name('reports.expense_report');
        Route::post('reports/expense_report', [ReportsController::class, 'expense_report'])->name('reports.expense_report_search');

        Route::get('reports/gratuity-report', [ReportsController::class, 'gratuity_report'])->name('reports.gratuity_report');
        Route::post('reports/gratuity-report', [ReportsController::class, 'gratuity_report'])->name('reports.gratuity_report_search');

        Route::get('reports/accruals-report', [ReportsController::class, 'accruals_report'])->name('reports.accruals_report');
        Route::post('reports/accruals-report', [ReportsController::class, 'accruals_report'])->name('reports.accruals_report_search');

        Route::get('reports/branch-budget-report', [ReportsController::class, 'branch_budget_report'])->name('reports.branch_budget_report');
        Route::post('reports/branch-budget-report', [ReportsController::class, 'branch_budget_report'])->name('reports.branch_budget_report_search');
        Route::get('reports/generateBranchBudgetReport', [ReportsController::class, 'generateBranchBudgetReport'])->name('reports.generateBranchBudgetReport');

        Route::get('reports/air-ticket-report', [ReportsController::class, 'air_ticket_report'])->name('reports.air_ticket_report');
        // Route::get('reports/air-ticket/export', [ReportsController::class, 'air_ticket_report_export'])->name('reports.air_ticket_report_export');
        Route::get('reports/air-ticket/export/{type}', [ReportsController::class, 'air_ticket_report_export'])
            ->name('reports.air_ticket_report_export');

        Route::get('employee-export-pdf', [UserImportExportController::class, 'exportToPdf'])->name('users.export.pdf');
        Route::get('employee-export-excel', [UserImportExportController::class, 'exportToExcel'])->name('users.export.excel');
        Route::get('users/export/master', [UserImportExportController::class, 'exportMasterSheet'])->name('users.export.master');
        Route::get('sample-export-excel', [UserImportExportController::class, 'exportSampleToExcel'])->name('sample.export.excel');

        Route::get('editEmpExportSampleToExcel', [UserImportExportController::class, 'editEmpExportSampleToExcel'])->name('editEmpExportSampleToExcel');
        Route::post('updateEmpToExcel', [UserImportExportController::class, 'updateEmpToExcel'])->name('updateEmpToExcel');

        Route::get('samplemedicalpremium-export-excel', [UserImportExportController::class, 'exportSampleMedicalpremiumToExcel'])->name('samplemedicalpremium.export.excel');
        Route::post('employee-import-excel', [UserImportExportController::class, 'importFromExcel'])->name('users.import.excel');
        Route::post('employee-importmedicalpremium-excel', [UserImportExportController::class, 'importimportmedicalpremiumFromExcel'])->name('users.importmedicalpremium.excel');
        Route::post('employee/{user}/change-status/{status}', [UserController::class, 'updateUserStatus'])->name('users.update-status')
            ->where('status', 'in-active|active|banned');
        Route::post('employee/{user}/send-welcome-notification', [UserController::class, 'sendWelcomeNotification'])->name('users.send-welcome-notification');
        Route::post('employee/send-welcome-notification-to-all', [UserController::class, 'sendWelcomeNotificationToAll'])->name('users.send-welcome-notification-to-all');
        Route::get('employes/{user}/{type?}', [UserController::class, 'show'])->name('users.show')
            ->where('type', 'asset|dependent|document|offboarding');
        Route::get('employes/leave/calculate/{user}', [UserController::class, 'leaveCalculate'])->name('users.leave-calculate');
        Route::post('employes/final/settlement/{user}', [UserController::class, 'postFinalSettlement'])->name('users.finalsettlement');
        Route::post('getSettlementLeavePolicy', [UserController::class, 'getSettlementLeavePolicy'])->name('getSettlementLeavePolicy');
        Route::post('storeAbsentDays', [UserController::class, 'storeAbsentDays'])->name('storeAbsentDays');
        Route::post('storeOffBoarding', [UserController::class, 'storeOffBoarding'])->name('storeOffBoarding');
        Route::post('getoffBoarding', [UserController::class, 'getoffBoarding'])->name('getoffBoarding');
        Route::post('addMonthDay', [UserController::class, 'addMonthDay'])->name('addMonthDay');
        Route::post('removeMonthDay', [UserController::class, 'removeMonthDay'])->name('removeMonthDay');
        Route::get('rehire/{id}', [UserController::class, 'rehire'])->name('rehire');
        Route::resource('employees', UserController::class, [
            'names'      => 'users',
            'parameters' => [
                'employees' => 'user',
            ],
        ])->except('show');
        Route::match(['get', 'post'], 'assignBranch/{id}', [UserController::class, 'assignBranch'])->name('assignBranch');
        Route::get('getProbationEndDate', [UserController::class, 'getProbationEndDate'])->name('getProbationEndDate');
        Route::middleware('auth',"subscription")->prefix('analytic')->as('analytic.')->group(function () {
            Route::get('probation-ending-list',  [\App\Http\Controllers\ProbationController::class, 'index'])->name('probation.upcoming.list');
            Route::post('probation/upload', [\App\Http\Controllers\ProbationController::class, 'processProbationUpload'])->name('probation.upload'); // Note: user's route name differs from my previous 'upload-docx'
            Route::post('probation/save', [\App\Http\Controllers\ProbationController::class, 'save'])->name('probation.save');
            Route::post('probation/template-store', [\App\Http\Controllers\ProbationController::class, 'templateStore'])->name('probation.template.store');
            Route::get('probation/download/{letter}', [\App\Http\Controllers\ProbationController::class, 'download'])->name('probation.download');
            Route::get('probation/send-email/{letter}', [\App\Http\Controllers\ProbationController::class, 'sendEmail'])->name('probation.send.email'); // Note: user's route uses GET for email?
        });

        Route::get('getDivisions', [UserController::class, 'getDivisions'])->name('getDivisions');

        Route::get('employees/{id}/take-photo', [UserController::class, 'takePhoto'])->name('takePhoto');
        Route::post('employees/{id}/submitPhoto', [UserController::class, 'submitPhoto'])->name('submitPhoto');
        Route::get('employees/assign/working/days', [UserController::class, 'getWorkingDayPage'])->name('working_day_page');

        Route::resource('roles', RoleController::class)->except('show');

        Route::get('user-dependent/{user}/create', [UserDependentController::class, 'create'])->name('user-dependent.create');
        Route::post('user-dependent/{user}/store', [UserDependentController::class, 'store'])->name('user-dependent.store');
        Route::resource('user-dependent', UserDependentController::class)->only('edit', 'update', 'destroy', 'show');
        Route::delete('user-dependent-document/{id}/{user_id}', [UserDependentController::class, 'dependent_document_delete'])->name('user-dependent.dependent_document_delete');
        Route::get('user-dependent-document/{userDependentDocument}/{user_id}/download', [UserDependentController::class, 'download'])->name('user-dependent.download');

        Route::resource('user-document', UserDocumentController::class)->only('destroy');
        Route::get('user-document/{userDocument}/download', [UserDocumentController::class, 'download'])->name('user-document.download');
        Route::post('user-document/{user}/store', [UserDocumentController::class, 'store'])->name('user-document.store');
        Route::get('user-document/{user}/store', [UserDocumentController::class, 'create'])->name('user-document.create');
        Route::get('user-document/{userdocument}', [UserDocumentController::class, 'edit'])->name('user-document.edit');
        Route::post('user-document/{userdocument}', [UserDocumentController::class, 'update'])->name('user-document.update');
        Route::post('user-document/{userdocument}/status', [UserDocumentController::class, 'changeStatus'])->name('user-document.status');

        Route::get('departments/{department}/createallowances', [DepartmentController::class, 'createallowances'])->name('departments.createallowances');
        Route::get('departments/{department}/allowanceslist', [DepartmentController::class, 'allowanceslist'])->name('departments.allowanceslist');
        Route::post('departments/{department}/storeallowances', [DepartmentController::class, 'storeallowances'])->name('departments.storeallowances');
        Route::get('departments/{department}/allowances/{allowance}/edit', [DepartmentController::class, 'editallowances'])
            ->name('departments.editallowances');
        Route::put('departments/{department}/allowances/{allowance}', [DepartmentController::class, 'updateallowances'])
            ->name('departments.updateallowances');
        Route::delete('departments/{department}/allowances/{allowance}', [DepartmentController::class, 'deleteallowances'])
            ->name('departments.deleteallowances');

        Route::post('departments/{department}/store-user', [DepartmentController::class, 'addUser'])->name('departments.user.add');
        Route::get('departments/{department}/add-user', [DepartmentController::class, 'addUserForm'])->name('departments.user.add.form');
        Route::get('departments/export', [DepartmentController::class, 'export'])->name('departments.export');
        Route::post('departments/import', [DepartmentController::class, 'import'])->name('departments.import');
        Route::resource('departments', DepartmentController::class)->except('show');

        Route::resource('divisions', DivisionController::class)->except('show');
        Route::resource('user-promotions', UserPromotionController::class)->except('show');
        Route::get('users/{user}/designation', [UserPromotionController::class, 'getUserDesignation'])
            ->name('users.designation');

        // Route::resource('promotions', UserPromotionController::class);

        // Route::get('features', [FeatureController::class, 'index'])->name('features');
        Route::get('features/export', [FeatureController::class, 'export'])->name('features.export');
        Route::post('features/import', [FeatureController::class, 'import'])->name('features.import');
        Route::resource('features', FeatureController::class)->except('show');

        Route::get('designations/export', [DesignationController::class, 'export'])->name('designations.export');
        Route::get('designations/sample-export', [DesignationController::class, 'exportSampleToExcel'])->name('designations.sample.export');
        Route::post('designations/import', [DesignationController::class, 'import'])->name('designations.import');
        Route::resource('designations', DesignationController::class)->except('show');

        //// Backend Setting Routes
        Route::prefix('settings')->as('settings.')->group(function () {
            Route::get('general', [SettingController::class, 'general'])->name('general');
            Route::post('general', [SettingController::class, 'saveGeneralSettings'])->name('general.post');
            //// Smtp setting
            Route::get('test-smtp', [SettingController::class, 'testSmtp'])->name('test.smtp');
            Route::get('smtp', [SettingController::class, 'smtp'])->name('smtp');
            Route::post('smtp', [SettingController::class, 'saveSmtpSettings'])->name('smtp.post');
            Route::get('clear-cache', [SettingController::class, 'clearCache'])->name('cache.clear');
            //// Advance Setting
            Route::get('system-setting', [SettingController::class, 'systemInfo'])->name('system.info');
            Route::get('advance', [SettingController::class, 'advance'])->name('advance');
            Route::post('advance', [SettingController::class, 'saveAdvanceSettings'])->name('advance.post');

            // Portal Management Setting
            Route::get('portals-setting', [SettingController::class, 'portalsInfo'])->name('portals.info');
            Route::get('portal/info/create', [SettingController::class, 'portalInfoCreate'])->name('portals.info.create');
            Route::post('portal/info/store', [SettingController::class, 'portalInfoStore'])->name('portals.info.store');
            Route::get('portal/info/edit/{portaldetail}', [SettingController::class, 'portalInfoEdit'])->name('portals.info.edit');
            Route::post('portal/info/update/{portaldetail}', [SettingController::class, 'portalInfoUpdate'])->name('portals.info.update');
        });

        //// Backend Acccount Routes
        Route::get('account', [AccountController::class, 'index'])->name('account');
        Route::post('account', [AccountController::class, 'updateAccount'])->name('update-account');
        Route::get('change-password', [AccountController::class, 'password'])->name('change-password');
        Route::post('change-password', [AccountController::class, 'updatePassword'])->name('update-password');

        //// Employee Module Routes

        Route::as('employee.')->group(function () {
            Route::get('profile', [ProfileController::class, 'my'])->name('profile');
            Route::get('edit-social-details', [ProfileController::class, 'editSocialDetailForm'])->name('social.details.edit');
            Route::get('edit-profile-details', [ProfileController::class, 'editProfileDetailForm'])->name('profile.details.edit');
            Route::post('update-social-details', [ProfileController::class, 'updateSocialDetails'])->name('social.details.update');
            Route::post('update-profile-details', [ProfileController::class, 'updateProfileDetails'])->name('profile.details.update');
            Route::resource('my-dependents', EmployeeDependentController::class, ['names' => 'dependents'])->except('destroy');
            Route::resource('documents', DocumentController::class);
            Route::get('hierarchy', [UserController::class, 'showEmployeeHierarchy'])->name('hierarchy');
            Route::get('hierarchy1', [UserController::class, 'showEmployeeHierarchy1'])->name('hierarchy1');
            Route::get('traininglist', [TrainingController::class, 'index'])->name('traininglist');
            Route::get('performancelist', [PerformanceAppraisalController::class, 'performancelist'])->name('performancelist');
            Route::get('reviewresponse', [ReviewResponseController::class, 'index'])->name('reviewresponse');
            Route::get('/reviewresponse/{id}', [ReviewResponseController::class, 'respondView'])->name('reviewresponse.view');
            Route::post('/reviewresponse/{id}', [ReviewResponseController::class, 'respondSubmit'])->name('reviewresponse.submit');
            Route::post('reviewresponse/{review}/user/{user}/accept', [ReviewResponseController::class, 'accept'])->name('reviewresponse.accept');
            Route::post('reviewresponse/{review}/user/{user}/decline', [ReviewResponseController::class, 'decline'])->name('reviewresponse.decline');

            Route::get('kpiresponse', [EmployeeKpiResponseController::class, 'index'])->name('kpiresponse');
            Route::get('kpishow/{id}', [EmployeeKpiResponseController::class, 'show'])->name('kpi.show');
            Route::post('kpishow/{id}', [EmployeeKpiResponseController::class, 'submit'])->name('kpi.submit');

        });

        // Notification Read Route
        Route::post('/mark-as-read/{notificationId}', [NotificationController::class, 'markAsRead'])->name('markAsRead');
        Route::delete('/readall-notifications', [NotificationController::class, 'readAllNotifications'])->name('readnotifications');

        //Basic Salary Export Import
        Route::get('employee-bs-export-excel', [UserImportExportController::class, 'exportBSSampleToExcel'])->name('users.bsexport.excel');
        Route::post('employee-bs-import-excel', [UserImportExportController::class, 'importBSFromExcel'])->name('users.bsimport.excel');
        Route::get('settlement/list/export', [UserImportExportController::class, 'exportSettlementList'])->name('settlement.export');

        Route::post('workingday-excel-import/{month}/{year}', [UserImportExportController::class, 'importWorkingDayFromExcel'])->name('workingday.import.excel');
        Route::get('workingday-excel-export/{month}/{year}', [UserImportExportController::class, 'exportWorkingDayToExcel'])->name('workingday.export.excel');

        Route::get('allowance-export-excel', [UserImportExportController::class, 'exportAllowanceSampleToExcel'])->name('users.allowance.export.excel');
        Route::post('allowance-import-excel', [UserImportExportController::class, 'importAllowanceFromExcel'])->name('users.allowance.import.excel');

        Route::get('deduction-export-excel', [UserImportExportController::class, 'exportDeductionSampleToExcel'])->name('users.deduction.export.excel');
        Route::post('deduction-import-excel', [UserImportExportController::class, 'importDeductionFromExcel'])->name('users.deduction.import.excel');

        // General Request Route
        Route::get('general-request', [GeneralRequestController::class, 'general_request'])->name('general_request');
        Route::get('show-apparel-request', [GeneralRequestController::class, 'apparel_request'])->name('show_apparel_request');
        Route::get('show-general-request', [GeneralRequestController::class, 'show_general_request'])->name('show_general_request');

        Route::get('/leave-approval', [LeaveApprovalSettingController::class, 'index'])->name('leave-approval.index');
        Route::post('/leave-approval', [LeaveApprovalSettingController::class, 'store'])->name('leave-approval.store');

        Route::get('reports/vacation_leave_report', [ReportsController::class, 'vacation_leave_report'])->name('reports.vacation_leave_report');
        Route::post('reports/vacation_leave_report', [ReportsController::class, 'vacation_leave_report'])->name('reports.vacation_leave_search_report');

        Route::get('reports/vacation-leave-report/export/{type}', [ReportsController::class, 'vacation_leave_report_export'])
            ->name('reports.vacation_leave_report_export');

        Route::get('reports/initial-leave-balance', [ReportsController::class, 'initialBalanceReport'])
            ->name('reports.initial_leave_balance');

        Route::get('reports/initial-leave-balance/data', [ReportsController::class, 'getInitialBalanceReportData'])
            ->name('reports.initial_leave_balance.data');

        Route::get('reports/initial-leave-balance/export', [ReportsController::class, 'exportInitialBalance'])->name('reports.initial_leave_balance.export');
        Route::post('reports/initial-leave-balance/import', [ReportsController::class, 'importInitialBalance'])->name('reports.initial_leave_balance.import');

        // air ticket request
        Route::get('get-air-ticket-request', [AirTicketRequestController::class, 'getAirTicketRequest'])->name('air-ticket.request');
        Route::get('air-ticket/requestDetails/{id}', [AirTicketRequestController::class, 'requestDetails'])->name('air-ticket.requestDetails');
        Route::post('air-ticket/requestApprove/{id}', [AirTicketRequestController::class, 'requestApprove'])->name('air-ticket.requestApprove');
        Route::delete('air-ticket/requestDelete/{id}', [AirTicketRequestController::class, 'requestDelete'])->name('air-ticket.requestDelete');
        Route::get('air-ticket/policyDetails/{userId}', [AirTicketRequestController::class, 'policyDetails'])->name('air-ticket.policyDetails');
        

    });
});

/////

Route::view('test', 'backend.users.test');
