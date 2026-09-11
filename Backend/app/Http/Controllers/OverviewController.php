<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\CashAdvance;
use App\Models\Employee;
use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
}
