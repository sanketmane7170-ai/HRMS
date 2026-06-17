<?php

use App\Http\Controllers\Ajax\Select2Controller;
use Illuminate\Support\Facades\Route;

Route::as('ajax.')->prefix('ajax')->group(function () {
    Route::as('select2.')->prefix('select2')->group(function () {
        Route::get('users', [Select2Controller::class, 'getUsers'])->name('fetch.users');
        Route::get('userswithall', [Select2Controller::class, 'getUsersWithAll'])->name('fetch.userswithall');
        Route::get('userswithselect', [Select2Controller::class, 'getUsersWithSelect'])->name('fetch.userswithselect');
        Route::get('userswithselectbymonth', [Select2Controller::class, 'getUsersWithSelectByMonth'])->name('fetch.userswithselectbymonth');
        Route::get('permissions', [Select2Controller::class, 'getPermissions'])->name('fetch.permissions');
        Route::get('roles', [Select2Controller::class, 'getRoles'])->name('fetch.roles');
        Route::get('departments', [Select2Controller::class, 'getDepartments'])->name('fetch.departments');
        Route::get('departmentswithall', [Select2Controller::class, 'getDepartmentsWithAll'])->name('fetch.departmentswithall');
        Route::get('departmentswithselect', [Select2Controller::class, 'getDepartmentsWithSelect'])->name('fetch.departmentswithselect');
        Route::get('designation', [Select2Controller::class, 'getDesignations'])->name('fetch.designations');
        Route::get('fetch-users/{roleId}', [Select2Controller::class, 'fetchUsersByRoleId'])->name('fetch.users.by.role');
        Route::get('companydocument', [Select2Controller::class, 'getCompanyDocument'])->name('fetch.companydocument');
        Route::get('currency', [Select2Controller::class, 'getCurrency'])->name('fetch.currency');
        Route::get('usersbygrade', [Select2Controller::class, 'getEmployeesByGrade'])->name('fetch.usersbygrade');

        Route::get('divisions', [Select2Controller::class, 'getDivisions'])->name('fetch.divisions');

    });
    
    Route::get('designation-grade', [Select2Controller::class, 'getDesignationGrade'])->name('get.designation.grade');
});
