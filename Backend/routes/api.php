<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\CashAdvanceController;
use App\Http\Controllers\DeductionController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [UserController::class, 'login']);
Route::post('/create', [UserController::class, 'createUser']);
            // Route::post('create', 'createUser');
Route::post('/request/cash_advance', [CashAdvanceController::class, 'requestCashAdvance']);

Route::middleware('auth:sanctum')->group(function () {
    Route::controller(AttendanceController::class)
        ->prefix('attendance')
        ->group(function () {
            Route::post('create', 'createAttendance');
            Route::post('update', 'updateAttendance');
            Route::post('view', 'getAttendance');
            Route::get('list', 'getAttendances');
        });

    Route::controller(BackupController::class)
        ->prefix('backup')
        ->group(function () {
            Route::post('create', 'logBackup');
            Route::get('latest', 'getBackupStatus');
            Route::get('list', 'getBackupLogs');
        });

    Route::controller(CashAdvanceController::class)
        ->prefix('cash_advance')
        ->group(function () {
            Route::post('create', 'logBackup');
            Route::post('review', 'reviewCashAdvance');
            Route::post('list', 'getCashAdvances');
        });

    Route::controller(DeductionController::class)
        ->prefix('deduction')
        ->group(function () {
            Route::post('create', 'createDeduction');
            Route::post('list', 'getDeductions');
            Route::post('review', 'getDeduction');
        });

    Route::controller(EmployeeController::class)
        ->prefix('employee')
        ->group(function () {
            Route::post('create', 'createEmployee');
            Route::post('change_profile', 'updateImage');
            Route::post('update', 'updateEmployee');
            Route::post('remove', 'removeEmployee');
            Route::post('restore', 'restoreEmployee');
            Route::post('list', 'getEmployees');
            Route::post('review_employee', 'getEmployee');
            Route::get('list_job_types', 'getJobTypes');
        });

    Route::controller(MaintenanceController::class)
        ->prefix('maintenance')
        ->group(function () {
            Route::post('create', 'createMaintenance');
            Route::post('update', 'updateMaintenance');
            Route::post('remove', 'removeMaintenance');
        });

    Route::controller(PayrollController::class)
        ->prefix('payroll')
        ->group(function () {
            Route::post('generate', 'generatePayroll');
            Route::post('update', 'updateStatus');
            Route::post('list', 'getPayrolls');
            Route::post('review_payroll', 'getPayroll');
        });

    Route::controller(SalaryController::class)
        ->prefix('salary')
        ->group(function () {
            Route::post('create', 'createSalary');
            Route::post('update', 'updateSalary');
            Route::post('list', 'getSalaries');
            Route::post('review', 'getActiveSalary');
        });

    Route::controller(UserController::class)
        ->prefix('user')
        ->group(function () {
            Route::post('create', 'createUser');
            Route::post('update', 'updateUser');
            Route::post('logout', 'logout');
            Route::get('isAuthenticated', 'isAuthenticated');
        });

});
