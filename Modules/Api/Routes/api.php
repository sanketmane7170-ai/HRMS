<?php

use Illuminate\Support\Facades\Route;
use Modules\Api\Http\Controllers\Attendance\CheckinController;
use Modules\Api\Http\Controllers\Auth\ForgetPasswordController;
use Modules\Api\Http\Controllers\Auth\LoginController;
use Modules\Api\Http\Controllers\Leave\MyLeaveController;
use Modules\Api\Http\Controllers\Leave\TypeController;
use Modules\Api\Http\Controllers\Announcement\AnnouncementController;
use Modules\Api\Http\Controllers\ServiceRequest\ServiceRequestController;
use Modules\Api\Http\Controllers\Profile\ProfileController;
use Modules\Api\Http\Controllers\Profile\DocumentUploadController;
use Modules\Api\Http\Controllers\PayRoll\PayRollController;
use Modules\Api\Http\Controllers\Attendance\BreakinController;
use Modules\Api\Http\Controllers\Attendance\VisitController;
use Modules\Api\Http\Controllers\Expense\ExpenseTypeController;
use Modules\Api\Http\Controllers\Expense\ExpenseController;
use Modules\Api\Http\Controllers\Warning\WarningController;
use App\Http\Controllers\Api\ApiController;
use Modules\Api\Http\Controllers\ApparelRequest\ApparelController;
use Modules\Api\Http\Controllers\ApparelRequest\ApparelRequestController;
use Modules\Api\Http\Controllers\GeneralRequest\GeneralRequestController;
use Modules\Api\Http\Controllers\GeneralRequest\GeneralRequestTypeController;
use Modules\Api\Http\Controllers\PayRoll\AdvanceRequestController;
use Modules\Api\Http\Controllers\BiometricAttendanceController;
use Modules\Api\Http\Middleware\Biometric\BiometricApiKeyMiddleware;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->middleware("subscription")->group(function () {
    Route::post('login', [LoginController::class, 'login']);
    Route::post('forget-password', [ForgetPasswordController::class, 'sendResetLinkEmail']);

    if (request()->getHost() === config('domain.specific_domain')) {
        /* Portal Information Module */
        Route::post('portal/info', [LoginController::class, 'getPortalInfo']);
    }
    Route::post('portal/info', [LoginController::class, 'getPortalInfo']);

    Route::post('CheckInWithLocation', [CheckinController::class, 'CheckInWithLocation']);
    Route::middleware(['auth:sanctum', 'throttle:1000,1',"subscription"])->group(function () {

        Route::post('apk_info', [ProfileController::class, 'apk_info']);

        Route::get('logout', [LoginController::class, 'logout']);

        //// attendance Module Routes
        Route::get('attendance/index', [CheckinController::class, 'index']);
        Route::get('attendance/checkins', [CheckinController::class, 'handleMultiCheckIns']);
        Route::post('attendance/checkins', [CheckinController::class, 'handleMultiCheckInswithlocation']);
        Route::get('attendance/list/{month?}/{year?}', [CheckinController::class, 'getAttendanceByID']);
        Route::get('attendance/breakins', [BreakinController::class, 'handleMultiBreakIns']);
        Route::get('attendance/visit/list', [VisitController::class, 'index']);
        Route::post('attendance/visit/start', [VisitController::class, 'handleMultiVisits']);
        Route::get('attendance/visit/end', [VisitController::class, 'handleMultiVisits']);
        Route::post('attendance/visit/end', [VisitController::class, 'handleMultiVisits']);
        //// leave Module Routes
        Route::get('leave/types', [TypeController::class, 'index']);
        Route::apiResource('leaves', MyLeaveController::class);

        // Get Announcements
        Route::get('announcements', [AnnouncementController::class, 'index']);
        Route::get('upcomingbirthday', [AnnouncementController::class, 'upcomingbirthday']);
        Route::get('upcominganniversary', [AnnouncementController::class, 'upcominganniversary']);

        // service requests Module Routes
        Route::apiResource('service-requests', ServiceRequestController::class);
        Route::get('service-requests/document/types', [ServiceRequestController::class, 'getDocumentTypes']);

        /*-------------------------------------------------------------
        | profile module
        |--------------------------------------------------------------
        | /profile GET URL for reterive user information
        | /profile/update-profile PATCH URL for update user information
        | /profile/update-password PATCH URL for update user password
        */
        Route::apiResource('profile', ProfileController::class);
        Route::get('user/department', [ProfileController::class, 'userdepartment'])->name('user.department');
        Route::post('profile/profile-update', [ProfileController::class, 'profileUpdate'])->name('profile.update.info');
        Route::post('profile/update-image', [ProfileController::class, 'updateprofileimage'])->name('profile.update.image');
        Route::post('profile/delete-image', [ProfileController::class, 'deleteprofileimage'])->name('profile.delete.image');
        Route::get('countrylist', [ProfileController::class, 'countrylist']);
        Route::post('user/location/update', [ProfileController::class, 'updateuserlocation'])->name('user.location.update');
        Route::post('user/location_update', [ProfileController::class, 'updateuserlocation'])->name('user.location_update');
        Route::post('firebase/token', [ProfileController::class, 'storeFToken']);

        // User Document upload module
        Route::apiResource('document-upload', DocumentUploadController::class);
        Route::get('document-upload/document/types', [DocumentUploadController::class, 'getDocumentTypes']);

        Route::apiResource('payroll', PayRollController::class);
        Route::get('payroll/payslip/{month}/{year}', [PayRollController::class, 'getUserPaySlip']);
        Route::get('newpayslip/{month}/{year}', [PayRollController::class, 'getUserNewPaySlip']);


        // User Assigned Shift 
        Route::get('user/shift/list', [ProfileController::class, 'UserAllottedShiftList']);

        Route::get('getwarninglist', [WarningController::class, 'getwarninglist']);
        Route::get('getUserwarninglist', [WarningController::class, 'getUserwarninglist']);
        Route::get('getUserCompanyPolicyList', [WarningController::class, 'getUserCompanyPolicyList']);
        Route::post('usercompanypolicyack', [WarningController::class, 'usercompanypolicyack']);

        Route::get('getUserAppreciationList', [WarningController::class, 'getUserAppreciationList']);
        Route::post('updateacknowledgement', [WarningController::class, 'updateacknowledgement']);


        Route::prefix('expense-types')->group(function () {
            Route::get('/', [ExpenseTypeController::class, 'index']); // Get all
            Route::get('/{id}', [ExpenseTypeController::class, 'show']); // Get a single by ID
            Route::post('/', [ExpenseTypeController::class, 'store']); // Create a new
            Route::put('/{id}', [ExpenseTypeController::class, 'update']); // Update an existing
            Route::delete('/{id}', [ExpenseTypeController::class, 'destroy']); // Delete
        });

        Route::prefix('expenses')->group(function () {
            Route::get('/', [ExpenseController::class, 'index']); // Get all
            Route::get('/{id}', [ExpenseController::class, 'show']); // Get a single by ID
            Route::post('/', [ExpenseController::class, 'store']); // Create a new
            Route::post('/{id}', [ExpenseController::class, 'update']); // Update an existing
            Route::delete('/{id}', [ExpenseController::class, 'destroy']); // Delete
            Route::delete('document_delete/{id}', [ExpenseController::class, 'document_delete']); // Delete
        });

         Route::get('getManagerRolePermissions', [ApiController::class, 'getManagerRolePermissions'])->name('getManagerRolePermissions');
        // notification 
        Route::get('getAllNotification', [ApiController::class, 'getAllNotification'])->name('getAllNotification');
        Route::get('getUnreadNotification', [ApiController::class, 'getUnreadNotification'])->name('getUnreadNotification');
        Route::post('markNotificationAsRead', [ApiController::class, 'markNotificationAsRead'])->name('markNotificationAsRead');

        Route::post('getEmployeeList', [ApiController::class, 'getEmployeeList'])->name('getEmployeeList');
        Route::post('getDepartmentList', [ApiController::class, 'getDepartmentList'])->name('getDepartmentList');
        Route::post('expiryDocumentList', [ApiController::class, 'expiryDocumentList'])->name('expiryDocumentList');
        // leave type
        Route::post('leaveTypeList', [ApiController::class, 'leaveTypeList'])->name('leaveTypeList');
        Route::post('userLeaveBalance', [ApiController::class, 'userLeaveBalance'])->name('userLeaveBalance');
        Route::post('addLeaveType', [ApiController::class, 'addLeaveType'])->name('addLeaveType');
        Route::post('updateLeaveType', [ApiController::class, 'updateLeaveType'])->name('updateLeaveType');
        Route::post('deleteLeaveType', [ApiController::class, 'deleteLeaveType'])->name('deleteLeaveType');

        // leave
        Route::post('leaveList', [ApiController::class, 'leaveList'])->name('leaveList');
        Route::post('leaveDetails', [ApiController::class, 'leaveDetails'])->name('leaveDetails');
        Route::post('addLeave', [ApiController::class, 'addLeave'])->name('addLeave');
        Route::post('updateLeave', [ApiController::class, 'updateLeave'])->name('updateLeave');
        Route::post('deleteLeave', [ApiController::class, 'deleteLeave'])->name('deleteLeave');
        Route::get('leaveExport/{leave_id}', [ApiController::class, 'leaveExport'])->name('leaveExport');
        Route::post('leaveListExport', [ApiController::class, 'leaveListExport'])->name('leaveListExport');
        Route::post('leaveApproveReject', [ApiController::class, 'leaveApproveReject'])->name('leaveApproveReject');
        // general request 
        Route::post('listofGeneralRequestType', [ApiController::class, 'listofGeneralRequestType'])->name('listofGeneralRequestType');
        Route::post('GeneralRequestTypeStore', [ApiController::class, 'GeneralRequestTypeStore'])->name('GeneralRequestTypeStore');
        Route::post('GeneralRequestTypeUpdate', [ApiController::class, 'GeneralRequestTypeUpdate'])->name('GeneralRequestTypeUpdate');
        Route::post('GeneralRequestTypeDelete', [ApiController::class, 'GeneralRequestTypeDelete'])->name('GeneralRequestTypeDelete');
        Route::post('GeneralRequestApproveReject', [ApiController::class, 'GeneralRequestApproveReject'])->name('GeneralRequestApproveReject');

        Route::post('listofGeneralRequest', [ApiController::class, 'listofGeneralRequest'])->name('listofGeneralRequest');
        Route::post('addGeneralRequest', [ApiController::class, 'addGeneralRequest'])->name('addGeneralRequest');
        Route::post('updateGeneralRequest', [ApiController::class, 'updateGeneralRequest'])->name('updateGeneralRequest');
        Route::post('deleteGeneralRequest', [ApiController::class, 'deleteGeneralRequest'])->name('deleteGeneralRequest');
        // Document Request
        Route::post('documentlRequestList', [ApiController::class, 'documentlRequestList'])->name('documentlRequestList');
        Route::post('documentlRequestDetails', [ApiController::class, 'documentlRequestDetails'])->name('documentlRequestDetails');
        Route::post('previewDocumentRequest', [ApiController::class, 'previewDocumentRequest'])->name('previewDocumentRequest');
        Route::post('DocumentRequestDownload', [ApiController::class, 'DocumentRequestDownload'])->name('DocumentRequestDownload');
        Route::post('generateDocumentRequest', [ApiController::class, 'generateDocumentRequest'])->name('generateDocumentRequest');
        Route::post('rejectDocumentRequest', [ApiController::class, 'rejectDocumentRequest'])->name('rejectDocumentRequest');

        // attendances
        Route::post('attendancesList', [ApiController::class, 'attendancesList'])->name('attendancesList');
        Route::post('attendancesDetails', [ApiController::class, 'attendancesDetails'])->name('attendancesDetails');
        Route::post('attendanceReport', [ApiController::class, 'attendanceReport'])->name('attendanceReport');
        Route::post('exportAttendanceReport', [ApiController::class, 'exportAttendanceReport'])->name('exportAttendanceReport');
        Route::post('exportPDFAttendanceReport', [ApiController::class, 'exportPDFAttendanceReport'])->name('exportPDFAttendanceReport');

        // advance or loan requrest 
        // Route::post('loanOrAdvanceSalaryRequest', [ApiController::class, 'loanOrAdvanceSalaryRequest'])->name('loanOrAdvanceSalaryRequest');
        // Route::post('uniformRequest', [ApiController::class, 'uniformRequest'])->name('uniformRequest');

        Route::get('/advance-requests', [AdvanceRequestController::class, 'index']);
        Route::post('/advance-requests', [AdvanceRequestController::class, 'store']);
        Route::get('/advance-requests/{id}', [AdvanceRequestController::class, 'show']);
        Route::put('/advance-requests/{id}', [AdvanceRequestController::class, 'update']);
        Route::delete('/advance-requests/{id}', [AdvanceRequestController::class, 'destroy']);

        Route::get('/apparels', [ApparelController::class, 'index']);

        Route::get('/apparel-requests', [ApparelRequestController::class, 'index']);
        Route::post('/apparel-requests', [ApparelRequestController::class, 'store']);
        Route::get('/apparel-requests/{id}', [ApparelRequestController::class, 'show']);
        Route::put('/apparel-requests/{id}', [ApparelRequestController::class, 'update']);
        Route::delete('/apparel-requests/{id}', [ApparelRequestController::class, 'destroy']);



        Route::get('/general-request-types', [GeneralRequestTypeController::class, 'index']);

        // General Request CRUD
        Route::get('/general-requests', [GeneralRequestController::class, 'index']);
        Route::post('/general-requests', [GeneralRequestController::class, 'store']);
        Route::get('/general-requests/{id}', [GeneralRequestController::class, 'show']);
        Route::put('/general-requests/{id}', [GeneralRequestController::class, 'update']);
        Route::delete('/general-requests/{id}', [GeneralRequestController::class, 'destroy']);
    });









    Route::prefix('{unique_code}/{user_id}')->middleware(['handle.portal.user'])->group(function () {

        Route::middleware(['auth:sanctum', 'throttle:1000,1',"subscription"])->group(function () {


            //// attendance Module Routes
            Route::get('attendance/index', [CheckinController::class, 'index']);
            Route::get('attendance/checkins', [CheckinController::class, 'handleMultiCheckIns']);
            Route::post('attendance/checkins', [CheckinController::class, 'handleMultiCheckInswithlocation']);
            Route::get('attendance/list/{month?}/{year?}', [CheckinController::class, 'getAttendanceByID']);
            Route::get('attendance/breakins', [BreakinController::class, 'handleMultiBreakIns']);
            Route::get('attendance/visit/list', [VisitController::class, 'index']);
            Route::post('attendance/visit/start', [VisitController::class, 'handleMultiVisits']);
            Route::get('attendance/visit/end', [VisitController::class, 'handleMultiVisits']);
            Route::post('attendance/visit/end', [VisitController::class, 'handleMultiVisits']);


            Route::apiResource('profile', ProfileController::class);

            Route::get('user/department', [ProfileController::class, 'userdepartment'])->name('user.department');
            Route::post('profile/profile-update', [ProfileController::class, 'profileUpdate'])->name('profile.update.info');
            Route::post('profile/update-image', [ProfileController::class, 'updateprofileimage'])->name('profile.update.image');
            Route::post('profile/delete-image', [ProfileController::class, 'deleteprofileimage'])->name('profile.delete.image');
            Route::get('countrylist', [ProfileController::class, 'countrylist']);
            Route::post('user/location/update', [ProfileController::class, 'updateuserlocation'])->name('user.location.update');
            Route::post('user/location_update', [ProfileController::class, 'updateuserlocation'])->name('user.location_update');
            Route::post('firebase/token', [ProfileController::class, 'storeFToken']);
            Route::get('user/shift/list', [ProfileController::class, 'UserAllottedShiftList']);

            Route::get('profile', [ProfileController::class, 'index'])->name('user.profile.get');
            Route::get('getManagerRolePermissions', [ApiController::class, 'getManagerRolePermissions'])->name('getManagerRolePermissions.v1');

            //sanket : Portal notification system - enables portal users to access notifications with pagination support
            Route::get('getAllNotification', [ApiController::class, 'getAllNotification'])->name('getAllNotification.v1');
            Route::get('getUnreadNotification', [ApiController::class, 'getUnreadNotification'])->name('getUnreadNotification.v1');
            Route::post('markNotificationAsRead', [ApiController::class, 'markNotificationAsRead'])->name('markNotificationAsRead.v1');

            //sanket : Portal employee management APIs - for portal admins to manage employee data within their portal context
            Route::post('getEmployeeList', [ApiController::class, 'getEmployeeList'])->name('getEmployeeList.v1');
            Route::post('getDepartmentList', [ApiController::class, 'getDepartmentList'])->name('getDepartmentList.v1');
            Route::post('expiryDocumentList', [ApiController::class, 'expiryDocumentList'])->name('expiryDocumentList.v1');

            //sanket : Portal leave management system - comprehensive leave handling within portal context
            //sanket : These routes handle leave types, balances, CRUD operations and approval workflows for portal users
            Route::post('leaveTypeList', [ApiController::class, 'leaveTypeList'])->name('leaveTypeList.v1');
            Route::post('userLeaveBalance', [ApiController::class, 'userLeaveBalance'])->name('userLeaveBalance.v1');
            Route::post('addLeaveType', [ApiController::class, 'addLeaveType'])->name('addLeaveType.v1');
            Route::post('updateLeaveType', [ApiController::class, 'updateLeaveType'])->name('updateLeaveType.v1');
            Route::post('deleteLeaveType', [ApiController::class, 'deleteLeaveType'])->name('deleteLeaveType.v1');

            Route::post('leaveList', [ApiController::class, 'leaveList'])->name('leaveList.v1');
            Route::post('leaveDetails', [ApiController::class, 'leaveDetails'])->name('leaveDetails.v1');
            Route::post('addLeave', [ApiController::class, 'addLeave'])->name('addLeave.v1');
            Route::post('updateLeave', [ApiController::class, 'updateLeave'])->name('updateLeave.v1');
            Route::post('deleteLeave', [ApiController::class, 'deleteLeave'])->name('deleteLeave.v1');
            Route::post('leaveApproveReject', [ApiController::class, 'leaveApproveReject'])->name('leaveApproveReject.v1');

            //sanket : Portal general request system - handles custom requests and approval workflows for portal users
            Route::post('listofGeneralRequestType', [ApiController::class, 'listofGeneralRequestType'])->name('listofGeneralRequestType.v1');
            Route::post('listofGeneralRequest', [ApiController::class, 'listofGeneralRequest'])->name('listofGeneralRequest.v1');
            Route::post('addGeneralRequest', [ApiController::class, 'addGeneralRequest'])->name('addGeneralRequest.v1');
            Route::post('updateGeneralRequest', [ApiController::class, 'updateGeneralRequest'])->name('updateGeneralRequest.v1');
            Route::post('deleteGeneralRequest', [ApiController::class, 'deleteGeneralRequest'])->name('deleteGeneralRequest.v1');
            Route::post('GeneralRequestApproveReject', [ApiController::class, 'GeneralRequestApproveReject'])->name('GeneralRequestApproveReject.v1');

            //sanket : Portal document request system - handles document generation, preview and management for portal users
            Route::post('documentlRequestList', [ApiController::class, 'documentlRequestList'])->name('documentlRequestList.v1');
            Route::post('documentlRequestDetails', [ApiController::class, 'documentlRequestDetails'])->name('documentlRequestDetails.v1');
            Route::post('previewDocumentRequest', [ApiController::class, 'previewDocumentRequest'])->name('previewDocumentRequest.v1');
            Route::post('generateDocumentRequest', [ApiController::class, 'generateDocumentRequest'])->name('generateDocumentRequest.v1');
            Route::post('rejectDocumentRequest', [ApiController::class, 'rejectDocumentRequest'])->name('rejectDocumentRequest.v1');

            //sanket : Portal attendance reporting system - provides attendance analytics and reports for portal managers
            Route::post('attendancesList', [ApiController::class, 'attendancesList'])->name('attendancesList.v1');
            Route::post('attendancesDetails', [ApiController::class, 'attendancesDetails'])->name('attendancesDetails.v1');
            Route::post('attendanceReport', [ApiController::class, 'attendanceReport'])->name('attendanceReport.v1');
        });
    });
});

// ==========================================
// Biometric Machine API (v2) - Machine to Server
// Protected by X-API-KEY header (BiometricApiKeyMiddleware)
// ==========================================
Route::prefix('v2/biometric')->middleware([\Modules\Api\Http\Middleware\Biometric\BiometricApiKeyMiddleware::class])->group(function () {
    Route::post('checkin', [\Modules\Api\Http\Controllers\BiometricAttendanceController::class, 'checkin']);
    Route::get('attendance/user/{user_id}', [\Modules\Api\Http\Controllers\BiometricAttendanceController::class, 'userReport']);
    Route::get('attendance/employee/{biometric_user_id}', [\Modules\Api\Http\Controllers\BiometricAttendanceController::class, 'employeeReport']);
    Route::get('attendance/date/{date}', [\Modules\Api\Http\Controllers\BiometricAttendanceController::class, 'dateReport']);
});

