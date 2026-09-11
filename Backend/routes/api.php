<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\CashAdvanceController;
use App\Http\Controllers\DeductionController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\PayslipController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\SSSController;
use App\Http\Controllers\OverviewController;
use App\Http\Controllers\UserController;
use App\Http\Services\SSSServices;
use Illuminate\Support\Facades\Route;

Route::post('/login', [UserController::class, 'login']);
Route::post('/create', [UserController::class, 'createUser']);
Route::post('/request/cash_advance', [CashAdvanceController::class, 'requestCashAdvance']);

Route::middleware('auth:sanctum')->group(function () {
    Route::controller(PayslipController::class)
        ->prefix('payslip')
        ->group(function () {
            Route::get('paySlip', 'getPayslip');
            Route::get('export', 'exportPayslips');
        });

    Route::controller(AttendanceController::class)
        ->prefix('attendance')
        ->group(function () {
            Route::post('create', 'createAttendance');
            Route::post('update', 'updateAttendance');
            Route::post('view', 'getAttendance');
            Route::get('list', 'getAttendances');
            Route::post('import', 'import');
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
            Route::post('create', 'requestCashAdvance');
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
            Route::get('all', 'getAllEmployees');
        });

    Route::controller(MaintenanceController::class)
        ->prefix('maintenance')
        ->group(function () {
            Route::post('create', 'createMaintenance');
            Route::post('update', 'updateMaintenance');
            Route::post('remove', 'removeMaintenance');
            Route::get('maintenance_list', 'getMaintenances');
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
            Route::get('list', 'getSalaries');
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

    Route::controller(SSSController::class)
        ->prefix('sss')
        ->group(function () {
            Route::post('create', 'createSSSDeduction');
            Route::post('getRecords', 'getSSSDeductionsRecords');
            Route::post('getHistory', 'getSSSDeductionsHistory');
            Route::post('removeSSS', 'removeSSSDeduction');
            Route::post('updateSSS', 'updateSSSDeduction');
        });

    Route::controller(OverviewController::class)
        ->prefix('overview')
        ->group(function(){
            Route::post('card_overview', 'cardOverview');
            Route::post('weekly_attendance', 'weeklyAttendance');
            Route::get('five_paid', 'fiveWeeksSalaryPaid');
        });
});
