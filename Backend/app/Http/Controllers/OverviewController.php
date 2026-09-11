<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\CashAdvance;
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

            $employee = Employee::where('status', '!=', 'Separated/Terminated')
                ->count();

            $employeesAddedAfterEndDate = Employee::whereDate('date_hired', '>', $end)->count();

            $date = Carbon::parse($start)->format('F d, Y') . ' - ' . Carbon::parse($end)->format('F d, Y');

            $totalPaid = Payroll::where('status', 'Paid')->sum('net_pay');
            $totalUnpaid = Payroll::where('status', '!=', 'Paid')->sum('net_pay');
            $totalPayroll = $totalPaid + $totalUnpaid;

            $paidPercentage = $totalPayroll > 0 ? round(($totalPaid / $totalPayroll) * 100, 2) : 0123;

            $ca = CashAdvance::where('status', 'Deducted/Paid')->whereBetween('requested_date', [$start, $end])->sum('amount');
            $currentSettledCA = CashAdvance::where('status', 'Deducted/Paid')
                ->whereHas('payroll', function ($query) use ($start, $end) {
                    $query->whereBetween('payout_date', [$start, $end]);
                })
                ->sum('amount');

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
                    'sub' => $employeesAddedAfterEndDate . ' Active Employees',
                    'sub_rate' => $employeesAddedAfterEndDate
                ],
                'totalPaid' => [
                    'label' => 'Total Salary Submitted / Paid',
                    'stamp' => 'PAID',
                    'count' => $totalPaid,
                    'date' => $date,
                    'sub' => $paidPercentage . "% Salary Processed",
                    'sub_rate' => $paidPercentage
                ],
                'totalCA' => [
                    'label' => 'Total C.A.',
                    'stamp' => 'C.A',
                    'count' => $ca,
                    'date' => $date,
                    'sub' => 'Cash Advances Released',
                    'sub_rate' => $currentSettledCA,
                ],
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
            return response_return('Connection Lost!', [], 500);
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

        try {
            $validation = $request->validate([
                'start_date' => ['nullable', 'date'],
                'end_date' => ['nullable', 'date'],
            ]);
        } catch (\Throwable $th) {
            return response_return($th->getMessage(), [], 500);
        }

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

            $attendanceCounts = DB::table('attendances')
                ->select(
                    DB::raw('DATE(created_at) as attendance_date'),
                    DB::raw("SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present_count"),
                    DB::raw("SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent_count"),
                    DB::raw("SUM(CASE WHEN status = 'Late' THEN 1 ELSE 0 END) as late_count")
                )
                ->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
                ->groupBy('attendance_date')
                ->get()
                ->keyBy('attendance_date');

            $presentData = [];
            $absentData = [];
            $lateData = [];

            foreach ($period as $date) {
                $dateKey = $date->toDateString();
                $record = $attendanceCounts->get($dateKey);

                $presentData[] = $record ? (int)$record->present_count : 0;
                $absentData[] = $record ? (int)$record->absent_count : 0;
                $lateData[] = $record ? (int)$record->late_count : 0;
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
                ]
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

            $endOfCurrentWeek = Carbon::now()->endOfWeek(Carbon::SATURDAY);
            $startOfFirstWeek = Carbon::now()->subWeeks(4)->startOfWeek(Carbon::SUNDAY);

            $currentStart = $startOfFirstWeek->copy();

            while ($currentStart <= $endOfCurrentWeek) {
                $weekStart = $currentStart->copy()->toDateString();
                $weekEnd = $currentStart->copy()->endOfWeek(Carbon::SATURDAY)->toDateString();

                $label = Carbon::parse($weekStart)->format('M d') . ' - ' . Carbon::parse($weekEnd)->format('M d');

                $totalNetPay = DB::table('payrolls')
                    ->whereBetween('created_at', [$weekStart . ' 00:00:00', $weekEnd . ' 23:59:59'])
                    ->sum('net_pay') ?? 0;

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
