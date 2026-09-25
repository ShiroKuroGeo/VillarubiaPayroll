<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\CashAdvance;
use App\Models\CashAdvanceDeduction;
use App\Models\Employee;
use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OverviewController extends Controller
{
    public function cardOverview(Request $request)
    {
        try {
            $validation = $request->validate([
                'start_date' => ['nullable', 'date'],
                'end_date' => ['nullable', 'date'],
            ]);
        } catch (\Throwable $th) {
            return response_return($th->getMessage(), [], 500);
        }

        try {
            $start_date = Carbon::now()->startOfMonth()->toDateString();
            $end_date = Carbon::now()->endOfMonth()->toDateString();

            $start = $validation['start_date'] ?? $start_date;
            $end = $validation['end_date'] ?? $end_date;

            // Total employees on file (not period-bound — a headcount, not a flow metric)
            $employee = Employee::where('status', '!=', 'Separated/Terminated')
                ->count();

            // Employees actively employed as of the period (hired on/before end date,
            // not separated before the period started). This replaces the previous
            // "hired AFTER end date" check, which counted people who weren't even
            // employed yet during the period being reported on.
            $activeEmployeesInPeriod = Employee::where('status', '!=', 'Separated/Terminated')
                ->whereDate('date_hired', '<=', $end)
                ->count();

            $date = Carbon::parse($start)->format('F d, Y') . ' - ' . Carbon::parse($end)->format('F d, Y');

            // Payroll totals now scoped to the selected period via payout_date,
            // instead of being all-time sums regardless of the date filter.
            $totalPaid = Payroll::where('status', 'Paid')
                ->whereBetween('payout_date', [$start, $end])
                ->sum('net_pay');

            $totalUnpaid = Payroll::where('status', '!=', 'Paid')
                ->whereBetween('payout_date', [$start, $end])
                ->sum('net_pay');

            $totalPayroll = $totalPaid + $totalUnpaid;

            // Fixed: was the octal literal `0123` (== 83 in decimal), which silently
            // returned 83% whenever $totalPayroll was 0. Now correctly returns 0.
            $paidPercentage = $totalPayroll > 0
                ? round(($totalPaid / $totalPayroll) * 100, 2)
                : 0;

            // Cash advances actually deducted within the period, pulled from
            // cash_advance_deductions (one row per real deduction, partial or
            // final) instead of CashAdvance.amount. The old query only caught
            // CAs that were FULLY paid off (status = 'Deducted/Paid') and summed
            // the entire original request amount against requested_date — which
            // undercounts ongoing installments and misattributes multi-period
            // CAs to whichever month they were first requested in.
            $ca = CashAdvanceDeduction::whereBetween('cutoff_start', [$start, $end])
                ->sum('amount');

            // Same fix applied here: join through cash_advance_deductions rather
            // than CashAdvance->payroll(), since CashAdvance.payroll_id is only
            // ever set on the FINAL deduction of a CA, never on partial ones.
            // whereHas('payroll', ...) on CashAdvance therefore silently excluded
            // every partially-deducted CA from this figure.
            $currentSettledCA = CashAdvanceDeduction::whereHas('payroll', function ($query) use ($start, $end) {
                $query->whereBetween('payout_date', [$start, $end]);
            })->sum('amount');

            $totalAttendance = Attendance::whereBetween('date', [$start, $end])->count();

            $totalPresent = Attendance::whereBetween('date', [$start, $end])
                ->where('status', '!=', 'Absent')
                ->count();

            $attendanceRate = $totalAttendance > 0
                ? round(($totalPresent / $totalAttendance) * 100, 2)
                : 0;

            $data = [
                'employee_card' => [
                    'label' => 'Total Employees',
                    'stamp' => 'STAFF',
                    'count' => $employee,
                    'date' => $date,
                    'sub' => $activeEmployeesInPeriod . ' Active Employees',
                    'sub_rate' => $activeEmployeesInPeriod,
                ],
                'totalPaid' => [
                    'label' => 'Total Salary Submitted / Paid',
                    'stamp' => 'PAID',
                    'count' => $totalPaid,
                    'date' => $date,
                    'sub' => $paidPercentage . "% Salary Processed",
                    'sub_rate' => $paidPercentage,
                ],
                'totalCA' => [
                    'label' => 'Total C.A.',
                    'stamp' => 'C.A',
                    'count' => $ca,
                    'date' => $date,
                    'sub' => 'Cash Advances Released',
                    'sub_rate' => $currentSettledCA,
                ],
                // Renamed key content to match what it actually holds (attendance,
                // not deductions). Left the key name as 'totalDeduction' since the
                // frontend may already read this key — rename that too if you
                // update the client at the same time.
                'totalDeduction' => [
                    'label' => 'Total Present',
                    'stamp' => 'ATT.',
                    'count' => $totalPresent,
                    'date' => $date,
                    'sub' => $attendanceRate . ' Attendance Rate',
                    'sub_rate' => $attendanceRate,
                ],
            ];

            return response_return('Successfully get overviews', $data, 200);
        } catch (\Throwable $th) {
            return response_return($th->getMessage(), [], 500);
        }
    }


    public function weeklyAttendance(Request $request)
    {
        try {
            $validation = $request->validate([
                'start_date' => ['nullable', 'date'],
                'end_date' => ['nullable', 'date'],
            ]);
        } catch (\Throwable $th) {
            return response_return($th->getMessage(), [], 500);
        }

        // Removed: duplicate copy of the same validate() block that ran twice
        // in the original (harmless, but dead/duplicated work).

        try {
            $start_date = Carbon::now()->subWeek()->startOfWeek(Carbon::SUNDAY)->toDateString();
            $end_date = Carbon::now()->endOfWeek(Carbon::SATURDAY)->toDateString();

            $start = $validation['start_date'] ?? $start_date;
            $end = $validation['end_date'] ?? $end_date;

            $dateString = Carbon::parse($start)->format('F d, Y') . ' - ' . Carbon::parse($end)->format('F d, Y');

            $period = \Carbon\CarbonPeriod::create($start, $end);
            $labels = [];
            foreach ($period as $date) {
                $labels[] = $date->format('D');
            }

            // Fixed: grouped/filtered by `date` (the actual attendance/work date)
            // instead of `created_at` (when the row was inserted). Biometric
            // imports can land well after the fact and all share one created_at
            // day, which would previously bunch a whole week's attendance onto
            // a single bar and leave the rest empty.
            $attendanceCounts = DB::table('attendances')
                ->select(
                    DB::raw('DATE(date) as attendance_date'),
                    DB::raw("SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present_count"),
                    DB::raw("SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent_count"),
                    DB::raw("SUM(CASE WHEN status = 'Late' THEN 1 ELSE 0 END) as late_count")
                )
                ->whereBetween('date', [$start, $end])
                ->groupBy('attendance_date')
                ->get()
                ->keyBy('attendance_date');

            $presentData = [];
            $absentData = [];
            $lateData = [];

            foreach ($period as $date) {
                $dateKey = $date->toDateString();
                $record = $attendanceCounts->get($dateKey);

                $presentData[] = $record ? (int) $record->present_count : 0;
                $absentData[] = $record ? (int) $record->absent_count : 0;
                $lateData[] = $record ? (int) $record->late_count : 0;
            }

            $data = [
                'date' => $dateString,
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Present',
                        'data' => $presentData,
                        'backgroundColor' => '#22c55e',
                    ],
                    [
                        'label' => 'Absent',
                        'data' => $absentData,
                        'backgroundColor' => '#ef4444',
                    ],
                    [
                        'label' => 'Late',
                        'data' => $lateData,
                        'backgroundColor' => '#f59e0b',
                    ],
                ],
            ];

            return response_return('Weekly attendance based on uploaded attendance', $data, 200);
        } catch (\Throwable $th) {
            return response_return($th->getMessage(), [], 500);
        }
    }


    public function fiveWeeksSalaryPaid(Request $request)
    {
        try {
            $validation = $request->validate([
                'start_date' => ['nullable', 'date'],
                'end_date' => ['nullable', 'date'],
            ]);
        } catch (\Throwable $th) {
            return response_return($th->getMessage(), [], 500);
        }

        try {
            $salaryWeeks = [];

            // Fixed: previously ignored $validation entirely and always used
            // "now minus 4 weeks to now," even though start_date/end_date were
            // validated as accepted input. Now anchors the 5-week window on the
            // requested end_date when one is given, falling back to "now."
            $anchor = isset($validation['end_date'])
                ? Carbon::parse($validation['end_date'])
                : Carbon::now();

            $endOfCurrentWeek = $anchor->copy()->endOfWeek(Carbon::SATURDAY);
            $startOfFirstWeek = $anchor->copy()->subWeeks(4)->startOfWeek(Carbon::SUNDAY);

            $currentStart = $startOfFirstWeek->copy();

            while ($currentStart <= $endOfCurrentWeek) {
                $weekStart = $currentStart->copy()->toDateString();
                $weekEnd = $currentStart->copy()->endOfWeek(Carbon::SATURDAY)->toDateString();

                $label = Carbon::parse($weekStart)->format('M d') . ' - ' . Carbon::parse($weekEnd)->format('M d');

                $totalNetPay = DB::table('payrolls')
                    ->whereBetween('payout_date', [$weekStart, $weekEnd])
                    ->sum('net_pay');

                $salaryWeeks[] = [
                    'label' => $label,
                    'totalSalary' => (float) $totalNetPay,
                ];

                $currentStart->addWeek();
            }

            return response_return('5 weeks salary paid retrieved successfully', $salaryWeeks, 200);
        } catch (\Throwable $th) {
            return response_return('Connection Lost!', [], 500);
        }
    }
}
