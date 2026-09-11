<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payroll;
use App\Models\Salary;
use App\Models\Attendance;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Models\Employee;
use App\Models\CashAdvance;
use App\Models\SSSContribution;
use App\Models\Deduction;
use App\Models\Maintenance;
use App\Exports\PayslipExport;
use Illuminate\Support\Facades\DB;

class PayslipController extends Controller
{
    public function getPayslip()
    {
        try {

            /*
        |--------------------------------------------------------------------------
        | Get Payrolls
        |--------------------------------------------------------------------------
        */

            $payrolls = Payroll::with([
                'employee',
                'employee.activeSalary',
                'deductions'
            ])
                ->orderByDesc('cutoff_start')
                ->get();

            return $this->buildPayslipResponse($payrolls);
        } catch (\Throwable $th) {

            logger()->error(
                'GET PAYSLIPS ERROR',
                [
                    'message' => $th->getMessage(),
                    'file' => $th->getFile(),
                    'line' => $th->getLine(),
                ]
            );

            return response_return(
                $th->getMessage(),
                [],
                500
            );
        }
    }

    /**
     * GET /api/payroll/payslips/export
     *
     * Streams a single .xlsx file with one payslip per payroll record
     * for the CURRENT weekly cutoff (Sunday start, Saturday end).
     * No query params needed — the week is computed here.
     */
    public function exportPayslips(Request $request)
    {
        try {

            $cutoffStart = Carbon::now()->startOfWeek(Carbon::SUNDAY)->toDateString();
            $cutoffEnd = Carbon::now()->endOfWeek(Carbon::SATURDAY)->toDateString();

            $payrolls = Payroll::with([
                'employee',
                'employee.activeSalary',
                'deductions'
            ])
                ->where('cutoff_start', $cutoffStart)
                ->where('cutoff_end', $cutoffEnd)
                ->orderByDesc('cutoff_start')
                ->get();

            if ($payrolls->isEmpty()) {
                return response_return(
                    'No payroll records found for this week\'s cutoff (Cutoff start' . $cutoffStart . ' to cutoff end' . $cutoffEnd . ').',
                    [],
                    401
                );
            }

            $payslips = $this->transformPayrolls($payrolls)->toArray();

            $company = Maintenance::where('name', 'Company Name')->first();

            $export = new PayslipExport($payslips, '', $company->value ?? 'Villarubia Company');

            return $export->download('payslips-' . now()->format('Ymd-His') . '.xlsx');
        } catch (\Throwable $th) {

            logger()->error(
                'EXPORT PAYSLIPS ERROR',
                [
                    'message' => $th->getMessage(),
                    'file' => $th->getFile(),
                    'line' => $th->getLine(),
                ]
            );

            return response_return(
                $th->getMessage(),
                [],
                500
            );
        }
    }

    /**
     * Shared response builder used by getPayslip().
     */
    private function buildPayslipResponse($payrolls)
    {
        if ($payrolls->isEmpty()) {
            return response_return(
                'No payroll records found.',
                [],
                200
            );
        }

        $payslips = $this->transformPayrolls($payrolls);

        return response_return(
            '',
            $payslips->toArray(),
            200
        );
    }

    /**
     * Turns a collection of Payroll models into the flat payslip array
     * shape used by both the JSON endpoint and the Excel export.
     */
    private function transformPayrolls($payrolls)
    {
        /*
        |--------------------------------------------------------------------------
        | Get Employee IDs
        |--------------------------------------------------------------------------
        */

        $employeeIds = $payrolls
            ->pluck('employee_id')
            ->unique()
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Get Cutoff Range
        |--------------------------------------------------------------------------
        */

        $minCutoffStart = $payrolls->min('cutoff_start');
        $maxCutoffEnd = $payrolls->max('cutoff_end');

        /*
        |--------------------------------------------------------------------------
        | Get Attendance
        |--------------------------------------------------------------------------
        */

        $attendancesByEmployee = collect();

        if (
            $employeeIds->isNotEmpty()
            && $minCutoffStart
            && $maxCutoffEnd
        ) {
            $attendancesByEmployee = Attendance::whereIn('employee_id', $employeeIds)
                ->whereBetween('date', [$minCutoffStart, $maxCutoffEnd])
                ->orderBy('date')
                ->get()
                ->groupBy('employee_id');
        }

        /*
        |--------------------------------------------------------------------------
        | Sunday Premium Rate
        |--------------------------------------------------------------------------
        */

        $sundayPremiumRate = (float) (
            Maintenance::where('name', 'Overtime Multiplier')->value('value')
            ?? 1.30
        );

        /*
        |--------------------------------------------------------------------------
        | Transform Payroll Data
        |--------------------------------------------------------------------------
        */

        return $payrolls->map(
            fn($payroll) => $this->transformSinglePayroll(
                $payroll,
                $attendancesByEmployee,
                $sundayPremiumRate
            )
        );
    }

    /**
     * Transforms one Payroll model into the payslip array shape.
     * (Logic unchanged from the original getPayslip() closure — just
     * extracted so exportPayslips() can reuse it.)
     */
    private function transformSinglePayroll($payroll, $attendancesByEmployee, $sundayPremiumRate)
    {
        $employee = $payroll->employee;
        $salary = $employee?->activeSalary;

        $initials = strtoupper(
            substr($employee->first_name ?? '', 0, 1)
                . substr($employee->last_name ?? '', 0, 1)
        ) ?: '—';

        $earningsBreakdown = [
            'sunday_premium' => 0.00,
            'overtime_pay' => 0.00,
            'total_attendance' => 0,
        ];

        if ($salary && $employee) {

            $employeeAttendances = $attendancesByEmployee
                ->get($employee->id, collect())
                ->filter(function ($attendance) use ($payroll) {
                    return $attendance->date >= $payroll->cutoff_start
                        && $attendance->date <= $payroll->cutoff_end;
                });

            $earningsBreakdown = $this->computeEarningsBreakdown(
                $salary,
                $employeeAttendances,
                $sundayPremiumRate
            );
        }

        $deduction = null;

        if ($payroll->deductions instanceof \Illuminate\Support\Collection) {
            $deduction = $payroll->deductions->first();
        } else {
            $deduction = $payroll->deductions;
        }

        // Human-readable pay period, e.g. "Sep 6 - 12, 2026",
        // built straight from this payroll's own cutoff dates.
        $period = $this->formatPeriod($payroll->cutoff_start, $payroll->cutoff_end);

        return [

            'id' => $payroll->id,

            'employeeId' => $employee?->id,

            'employeeName' => $employee
                ? $employee->last_name . ', ' . $employee->first_name
                : 'Unknown Employee',

            'initials' => $initials,

            'address' => $employee?->location,

            'image' => $employee?->image,

            'salaryType' => $salary?->salary_type,

            'grossPay' => (float) $payroll->gross_pay,

            'period' => $period,

            'cutoffStart' => $payroll->cutoff_start,

            'cutoffEnd' => $payroll->cutoff_end,

            'earnings' => [
                'basicSalary' => (float) ($salary?->basic_salary ?? 0),
                'sundayPremium' => (float) $earningsBreakdown['sunday_premium'],
                'overtime' => (float) $earningsBreakdown['overtime_pay'],
                'totalAttendance' => $earningsBreakdown['total_attendance'],
            ],

            'deductions' => [
                'sss' => (float) ($deduction?->sss_deduction ?? 0),
                'cashAdvance' => (float) ($deduction?->ca_deduction ?? 0),
                'late' => (float) ($deduction?->other_deduction['late_deduction'] ?? 0),
            ],

            'totalDeductions' => (float) $payroll->total_deductions,

            'netPay' => (float) $payroll->net_pay,

            'status' => $payroll->status,

            'paid' => $payroll->status === 'Paid',

            'paidDate' => $payroll->payment_date,

            'paymentMethod' => $payroll->payment_method,

            'reference' => 'VIP-' . now()->year . str_pad($payroll->id, 4, '0', STR_PAD_LEFT),

        ];
    }

    /**
     * Formats two dates into a readable range, e.g. "Sep 6 - 12, 2026".
     * Falls back to '-' if either date is missing.
     */
    private function formatPeriod($start, $end): string
    {
        if (!$start || !$end) {
            return '-';
        }

        $startDate = Carbon::parse($start);
        $endDate = Carbon::parse($end);

        if ($startDate->isSameMonth($endDate) && $startDate->isSameYear($endDate)) {
            return $startDate->format('M j') . ' - ' . $endDate->format('j, Y');
        }

        return $startDate->format('M j') . ' - ' . $endDate->format('M j, Y');
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
}
