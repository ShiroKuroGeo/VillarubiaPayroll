<?php

namespace App\Http\Services;

use App\Models\Payroll;
use App\Models\Salary;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Models\Employee;
use App\Models\CashAdvance;
use App\Models\SSSContribution;
use App\Models\Deduction;
use App\Models\Maintenance;
use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\DB;

class PayrollServices
{


    public function generatePayroll(): array
    {
        $today = Carbon::now();

        if (!$today->isSaturday()) {
            throw new \Exception(
                'Payroll can only be generated on Saturday.',
                422
            );
        }

        $cutoffEnd = $today->copy()->startOfDay();

        $cutoffStart = $cutoffEnd
            ->copy()
            ->subDays(6)
            ->startOfDay();

        $payoutDate = $cutoffEnd->copy();

        $checkAttendance = Attendance::whereBetween('date', [$cutoffStart, $cutoffEnd])->exists();

        if (!$checkAttendance) {
            throw new \Exception(
                'There is no attendance imported. Please import the attendance in biometric first.',
                422
            );
        };
        $employees = Employee::whereNotIn(
            'status',
            [
                'Separated',
                'Terminated',
                'Separated-Terminated',
            ]
        )
            ->whereNull('deleted_at')
            ->get();

        $gracePeriodMinutes = (float) (
            Maintenance::where('name', 'Grace Period')->value('value') ?? 0
        );

        $lateDeductionPerMinute = (float) (
            Maintenance::where('name', 'Late Deduction (Per Minute)')->value('value') ?? 0
        );

        $sundayPremiumRate = (float) (
            Maintenance::where('name', 'Overtime Multiplier')->value('value') ?? 1.30
        );

        $workStartTime = Maintenance::where('name', 'Work Start Time')->value('value') ?? '08:00:00';

        $result = [
            'cutoff_start' => $cutoffStart->toDateString(),
            'cutoff_end' => $cutoffEnd->toDateString(),
            'generated' => [],
            'skipped' => [],
            'failed' => [],
        ];

        foreach ($employees as $employee) {

            try {
                $exists = Payroll::where('employee_id', $employee->id)
                    ->where('cutoff_start', $cutoffStart->toDateString())
                    ->where('cutoff_end', $cutoffEnd->toDateString())
                    ->exists();

                if ($exists) {
                    $result['skipped'][] = [
                        'employee_id' => $employee->id,
                        'reason' => 'Payroll already exists for this cutoff.',
                    ];
                    continue;
                }

                $salary = Salary::where('employee_id', $employee->id)
                    ->where('is_active', true)
                    ->first();
                if (!$salary) {
                    $result['skipped'][] = [
                        'employee_id' => $employee->id,
                        'reason' => 'No active salary found.',
                    ];
                    continue;
                }

                $attendances = Attendance::where('employee_id', $employee->id)
                    ->whereBetween(
                        'date',
                        [
                            $cutoffStart->toDateString(),
                            $cutoffEnd->toDateString(),
                        ]
                    )
                    ->orderBy('date', 'asc')
                    ->get();

                $grossPay = $this->calculateGrossPay(
                    $salary,
                    $attendances,
                    $sundayPremiumRate
                );

                DB::beginTransaction();

                $payroll = Payroll::create([
                    'employee_id' => $employee->id,
                    'cutoff_start' => $cutoffStart->toDateString(),
                    'cutoff_end' => $cutoffEnd->toDateString(),
                    'payout_date' => $payoutDate->toDateString(),
                    'gross_pay' => round($grossPay, 2),
                    'total_deductions' => 0,
                    'net_pay' => round($grossPay, 2),
                    'status' => 'Draft',

                ]);

                $cashAdvances = CashAdvance::where('employee_id', $employee->id)
                    ->where('status', 'Approved')
                    ->whereNull('payroll_id')
                    ->get();

                $caTotal = round($cashAdvances->sum('amount'), 2);

                $sssEntries = SssContribution::where('employee_id', $employee->id)
                    ->where('status', 'Pending')
                    ->whereNull('payroll_id')
                    ->get();

                $sssTotal = round($sssEntries->sum('amount'), 2);

                $lateDeduction = $this->calculateLateDeduction(
                    $attendances,
                    $workStartTime,
                    $gracePeriodMinutes,
                    $lateDeductionPerMinute
                );

                Deduction::create([
                    'employee_id' => $employee->id,
                    'payroll_id' => $payroll->id,
                    'sss_deduction' => $sssTotal,
                    'ca_deduction' => $caTotal,
                    'other_deduction' => [
                        'late_deduction' => $lateDeduction,
                    ],
                    'remarks' => 'Auto-generated on ' . $today->toDateString(),
                ]);

                $totalDeductions = round(
                    $sssTotal + $caTotal + $lateDeduction,
                    2
                );

                $netPay = round(max($grossPay - $totalDeductions, 0), 2);

                $payroll->update([
                    'total_deductions' => $totalDeductions,
                    'net_pay' => $netPay,
                ]);


                if ($cashAdvances->isNotEmpty()) {
                    CashAdvance::whereIn('id', $cashAdvances->pluck('id'))
                        ->update([
                            'payroll_id' => $payroll->id,
                            'status' => 'Deducted/Paid',
                        ]);
                }

                foreach ($sssEntries as $sssEntry) {
                    $sssEntry->update([
                        'payroll_id' => $payroll->id,
                        'status' => 'Posted',
                        'date' => $cutoffEnd,
                    ]);
                    $newEntry = $sssEntry->replicate();
                    $newEntry->payroll_id = null;
                    $newEntry->status = 'Pending';
                    $newEntry->date = null;
                    $newEntry->save();
                }

                SSSContribution::where('status', 'Posted')->whereNull('payroll_id')->delete();

                DB::commit();

                $result['generated'][] = [
                    'employee_id' => $employee->id,
                    'payroll_id' => $payroll->id,
                    'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                    'gross_pay' => $payroll->gross_pay,
                    'deductions' => $totalDeductions,
                    'net_pay' => $netPay,
                ];
            } catch (\Throwable $th) {

                DB::rollBack();

                logger()->error(
                    'PAYROLL GENERATION ERROR',
                    [

                        'employee_id' =>
                        $employee->id,

                        'message' =>
                        $th->getMessage(),

                        'file' =>
                        $th->getFile(),

                        'line' =>
                        $th->getLine(),

                    ]
                );

                $result['failed'][] = [

                    'employee_id' =>
                    $employee->id,
                    'reason' =>
                    $th->getMessage(),
                ];
            }
        }

        return $result;
    }

    private function calculateGrossPay(Salary $salary, $attendances, $sundayPremiumRate): float
    {

        $gross = 0;

        $paidStatuses = [
            'Present',
            'Late',
            'Half Day',
        ];

        foreach ($attendances as $attendance) {


            if (!in_array(
                $attendance->status,
                $paidStatuses
            )) {
                continue;
            }

            $attendanceDate = Carbon::parse(
                $attendance->date
            );

            $isSunday = $attendanceDate->isSunday();
            if ($salary->salary_type === 'Daily') {
                $dayRate = (float)
                $salary->basic_salary;

                if ($attendance->status === 'Half Day') {
                    $dayRate = $dayRate / 2;
                }

                if ($isSunday) {
                    $dayRate = $dayRate * $sundayPremiumRate;
                }

                $gross += $dayRate;

                $overtimeHours = (float) ($attendance->overtime_hours ?? 0);

                if ($overtimeHours > 0) {
                    $maintenance = Maintenance::where('name', 'Overtime Premium Rate')->first();
                    $hourlyRate = (float) $maintenance->value;

                    if ($isSunday) {

                        $hourlyRate = $hourlyRate * $sundayPremiumRate;
                    }

                    $gross += $overtimeHours * $hourlyRate;
                }
            }

            /*
        |--------------------------------------------------------------------------
        | HOURLY SALARY
        |--------------------------------------------------------------------------
        */ elseif (
                $salary->salary_type === 'Hourly'
            ) {

                $hourlyRate =
                    (float)
                    $salary->basic_salary;

                /*
            |--------------------------------------------------------------------------
            | Sunday +30%
            |--------------------------------------------------------------------------
            */

                if ($isSunday) {

                    $hourlyRate =
                        $hourlyRate
                        * $sundayPremiumRate;
                }

                /*
            |--------------------------------------------------------------------------
            | Worked hours
            |--------------------------------------------------------------------------
            */

                $hoursWorked =
                    (float) (
                        $attendance->hours_worked
                        ?? 0
                    );

                /*
            |--------------------------------------------------------------------------
            | Overtime hours
            |--------------------------------------------------------------------------
            */

                $overtimeHours =
                    (float) (
                        $attendance->overtime_hours
                        ?? 0
                    );

                /*
            |--------------------------------------------------------------------------
            | Prevent overtime from being paid twice
            |--------------------------------------------------------------------------
            |
            | Example:
            |
            | hours_worked = 10
            | overtime_hours = 2
            |
            | Regular hours = 8
            |
            */

                $regularHours =
                    max(
                        $hoursWorked
                            - $overtimeHours,
                        0
                    );

                /*
            |--------------------------------------------------------------------------
            | Add regular pay
            |--------------------------------------------------------------------------
            */

                $gross +=
                    $regularHours
                    * $hourlyRate;


                $gross +=
                    $overtimeHours
                    * $hourlyRate;
            }
        }

        return round(
            $gross,
            2
        );
    }

    private function calculateLateDeduction($attendances, string $workStartTime, float $gracePeriodMinutes, float $perMinuteRate): float
    {

        $totalDeduction = 0;

        foreach ($attendances as $attendance) {

            if ($attendance->status !== 'Late') {
                continue;
            }

            if (!$attendance->time_in) {
                continue;
            }

            $attendanceDate = Carbon::parse($attendance->date)->toDateString();

            $scheduledStart = Carbon::parse($attendanceDate . ' ' . $workStartTime);
            $graceEnd = $scheduledStart->copy()->addMinutes($gracePeriodMinutes);

            $timeIn = Carbon::parse($attendanceDate . ' ' . $attendance->time_in);

            if ($timeIn->lessThanOrEqualTo($graceEnd)) {
                continue;
            }

            $lateMinutes = $graceEnd->diffInMinutes($timeIn);

            $totalDeduction += $lateMinutes * $perMinuteRate;
        }

        return round($totalDeduction, 2);
    }

    public function updateStatus(Request $request)
    {
        try {
            $validation = $request->validate([
                'payroll_id' => ['required', 'integer', 'exists:payrolls,id'],
                'payment_method' => ['nullable', Rule::in(['Bank Transfer', 'Cash', 'Check', 'GCash'])],
                'status' => ['nullable', Rule::in(['Draft', 'Pending', 'Paid', 'On Hold'])],
                'payment_date' => ['nullable', 'date'],
            ]);
        } catch (\Throwable $th) {
            return response_return($th->getMessage(), [], 422);
        }

        try {
            $checkPayroll = Payroll::where('id', $validation['payroll_id'])->first();

            if (!$checkPayroll) {
                return response_return('Payroll record was not found. Please try again.', [], 409);
            }

            if ($validation['status'] === 'Draft') {
                $updatePayroll = $checkPayroll->update([
                    'status' => $validation['status'] ?? 'Paid',
                    'payment_method' => $validation['payment_method'] ?? 'Cash',
                    'payment_date' => null,
                ]);
            } else {
                $updatePayroll = $checkPayroll->update([
                    'status' => $validation['status'] ?? 'Paid',
                    'payment_method' => $validation['payment_method'] ?? 'Cash',
                    'payment_date' => $validation['payment_date'] ?? $checkPayroll->payment_date,
                ]);
            }

            if (!$updatePayroll) {
                return response_return('Cannot save payroll information at this moment.', [], 409);
            }

            return response_return('Successfully updated payroll status.', $checkPayroll->toArray(), 200);
        } catch (\Throwable $th) {
            return response_return('Error occurred in updating payroll status.', [], 500);
        }
    }

    public function getPayrolls(Request $request)
    {
        try {
            $validation = $request->validate([
                'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
                'status' => ['nullable', Rule::in(['Draft', 'Pending', 'Paid', 'On Hold'])],
                'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            ]);
        } catch (\Throwable $th) {
            return response_return('Error occurred in validating the request.', [], 422);
        }

        try {

            $today = Carbon::now();

            $cutoffStart = Carbon::now()->startOfWeek(Carbon::SUNDAY)->toDateString();
            // $today->copy()->startOfDay();
            $cutoffEnd = Carbon::now()->endOfWeek(Carbon::SATURDAY)->toDateString();
            // $cutoffEnd
            //     ->copy()
            //     ->subDays(6)
            //     ->startOfDay();

            $query = Payroll::with(['employee', 'employee.activeSalary', 'deductions'])->where('cutoff_start', $cutoffStart)->where('cutoff_end', $cutoffEnd);

            if (!empty($validation['employee_id'])) {
                $query->where('employee_id', $validation['employee_id']);
            }

            if (!empty($validation['status'])) {
                $query->where('status', $validation['status']);
            }

            $payrolls = $query->orderByDesc('cutoff_start')
                ->paginate($validation['per_page'] ?? 100);

            $pageItems = $payrolls->getCollection();

            $employeeIds = $pageItems->pluck('employee_id')->unique()->values();
            $minCutoffStart = $pageItems->min('cutoff_start');
            $maxCutoffEnd = $pageItems->max('cutoff_end');

            $attendancesByEmployee = collect();

            if ($employeeIds->isNotEmpty() && $minCutoffStart && $maxCutoffEnd) {
                $attendancesByEmployee = Attendance::whereIn('employee_id', $employeeIds)
                    ->whereBetween('date', [$minCutoffStart, $maxCutoffEnd])
                    ->orderBy('date')
                    ->get()
                    ->groupBy('employee_id');
            }

            $sundayPremiumRate = (float) (
                Maintenance::where('name', 'Overtime Multiplier')->value('value') ?? 1.30
            );

            $payrolls->through(function ($payroll) use ($attendancesByEmployee, $sundayPremiumRate) {

                $employee = $payroll->employee;
                $salary = $employee?->activeSalary;

                $initials = strtoupper(
                    substr($employee->first_name ?? '', 0, 1)
                        . substr($employee->last_name ?? '', 0, 1)
                ) ?: '—';

                $earningsBreakdown = ['sunday_premium' => 0.0, 'overtime_pay' => 0.0];

                if ($salary) {

                    $attendances = ($attendancesByEmployee->get($employee->id) ?? collect())
                        ->filter(
                            fn($a) =>
                            $a->date >= $payroll->cutoff_start
                                && $a->date <= $payroll->cutoff_end
                        );

                    $earningsBreakdown = $this->computeEarningsBreakdown(
                        $salary,
                        $attendances,
                        $sundayPremiumRate
                    );
                }

                $deduction = $payroll->deductions instanceof \Illuminate\Support\Collection
                    ? $payroll->deductions->first()
                    : $payroll->deductions;

                return [
                    "id" => $payroll->id,
                    "employeeId" => $employee->id,
                    "employeeName" => $employee->last_name . ', ' . $employee->first_name,
                    "initials" => $initials,
                    "department" => $employee->location,
                    "basicSalary" => (float) ($salary->basic_salary ?? 0),
                    "salaryType" => $salary->salary_type ?? null,
                    "totalAttendance" => $earningsBreakdown['total_attendance'],
                    "deductions" => (float) $payroll->total_deductions,
                    "status" => $payroll->status,
                    "image" => $employee->image,
                    "paid" => $payroll->status === 'Paid',
                    "paidDate" => $payroll->payment_date,
                    "paymentMethod" => $payroll->payment_method,
                    'reference' => 'VIP-' . now()->year . str_pad($payroll->id, 4, '0', STR_PAD_LEFT),

                    // 'earnings' => [
                    //     "sundayPremium" => $earningsBreakdown['sunday_premium'],
                    //     "overtime" => $earningsBreakdown['overtime_pay'],
                    // ],

                    // 'deductions' => [
                    //     'sss' => (float) ($deduction->sss_deduction ?? 0),
                    //     'cashAdvance' => (float) ($deduction->ca_deduction ?? 0),
                    //     'late' => (float) ($deduction->other_deduction['late_deduction'] ?? 0),
                    // ],

                ];
            });

            return response_return('Successfully retrieved payrolls.', $payrolls->toArray(), 200);
        } catch (\Throwable $th) {

            logger()->error('GET PAYROLLS ERROR', [
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
            ]);

            return response_return($th->getMessage(), [], 500);
        }
    }

    private function computeEarningsBreakdown(Salary $salary, $attendances, float $sundayPremiumRate): array
    {
        $sundayPremium = 0;
        $overtimePay = 0;
        $totalAttendance = 0;

        $paidStatuses = ['Present', 'Late', 'Half Day'];

        foreach ($attendances as $attendance) {

            if (!in_array($attendance->status, $paidStatuses)) {
                continue;
            }

            $isSunday = Carbon::parse($attendance->date)->isSunday();
            $overtimeHours = (float) ($attendance->overtime_hours ?? 0);

            $totalAttendance += $attendance->status === 'Half Day' ? 0.5 : 1;

            if ($salary->salary_type === 'Daily') {

                $baseDayRate = (float) $salary->basic_salary;

                if ($attendance->status === 'Half Day') {
                    $baseDayRate = $baseDayRate / 2;
                }

                if ($isSunday) {
                    $sundayPremium += $baseDayRate * ($sundayPremiumRate - 1);
                }

                if ($overtimeHours > 0) {
                    $maintenance = Maintenance::where('name', 'Overtime Premium Rate')->first();
                    $hourlyRate = (float) $maintenance->value;
                    $overtimePay += $overtimeHours * $hourlyRate;
                }
            } elseif ($salary->salary_type === 'Hourly') {
                $maintenance = Maintenance::where('name', 'Overtime Premium Rate')->first();
                $hourlyRate = (float) $maintenance->value;


                $baseHourlyRate = (float) $salary->basic_salary;
                $hoursWorked = (float) ($attendance->hours_worked ?? 0);
                $regularHours = max($hoursWorked - $overtimeHours, 0);

                if ($isSunday) {
                    $sundayPremium += $regularHours * $baseHourlyRate * ($sundayPremiumRate - 1);
                }

                $overtimePay += $overtimeHours * $baseHourlyRate;
            }
        }

        return [
            'sunday_premium' => round($sundayPremium, 2),
            'overtime_pay' => round($overtimePay, 2),
            'total_attendance' => $totalAttendance,
        ];
    }

    public function getPayroll(Request $request)
    {
        try {
            $validation = $request->validate([
                'payroll_id' => ['required', 'integer', 'exists:payrolls,id'],
            ]);
        } catch (\Throwable $th) {
            return response_return('Error occurred in validating the request.', [], 422);
        }

        try {
            $payroll = Payroll::with('employee')->where('id', $validation['payroll_id'])->first();

            if (!$payroll) {
                return response_return('Payroll record was not found. Please try again.', [], 409);
            }

            return response_return('Successfully retrieved payroll.', $payroll->toArray(), 200);
        } catch (\Throwable $th) {
            return response_return('Error occurred in retrieving payroll.', [], 500);
        }
    }

    public function ensureActivePayrollThisMonth()
    {
        $today = Carbon::now()->toDateString();

        $hasActivePayroll = Payroll::where('status', '!=', 'Draft')
            ->whereDate('cutoff_start', '<=', $today)
            ->whereDate('cutoff_end', '>=', $today)
            ->exists();

        if (!$hasActivePayroll) {
            throw new \Exception('No active payroll found for this week. Please wait until the admin will generate.', 409);
        }

        return null;
    }
}
