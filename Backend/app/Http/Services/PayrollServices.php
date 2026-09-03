<?php

namespace App\Http\Services;

use App\Models\Payroll;
use App\Models\Salary;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class PayrollServices
{
    const SUNDAY_PREMIUM_RATE = 1;

    public function generatePayroll(Request $request)
    {
        try {
            $validation = $request->validate([
                'employee_id' => ['required', 'integer', 'exists:employees,id'],
                'cutoff_start' => ['required', 'date'],
                'cutoff_end' => ['required', 'date', 'after_or_equal:cutoff_start'],
                'payout_date' => ['required', 'date', 'after_or_equal:cutoff_end'],
            ]);
        } catch (\Throwable $th) {
            return response_return('Error occurred in validating payroll information.', [], 422);
        }

        try {
            $checkExisting = Payroll::where('employee_id', $validation['employee_id'])
                ->where('cutoff_start', $validation['cutoff_start'])
                ->where('cutoff_end', $validation['cutoff_end'])
                ->exists();

            if ($checkExisting) {
                return response_return('Payroll for this employee and cutoff already exists.', [], 409);
            }

            $salary = Salary::where('employee_id', $validation['employee_id'])
                ->where('is_active', true)
                ->first();

            if (!$salary) {
                return response_return('This employee has no active salary rate set.', [], 409);
            }

            $attendances = Attendance::where('employee_id', $validation['employee_id'])
                ->whereBetween('date', [$validation['cutoff_start'], $validation['cutoff_end']])
                ->get();

            $grossPay = $this->calculateGrossPay($salary, $attendances);

            $createPayroll = Payroll::create([
                'employee_id' => $validation['employee_id'],
                'cutoff_start' => $validation['cutoff_start'],
                'cutoff_end' => $validation['cutoff_end'],
                'payout_date' => $validation['payout_date'],
                'gross_pay' => round($grossPay, 2),
                'total_deductions' => 0,
                'net_pay' => round($grossPay, 2),
                'status' => 'Draft',
            ]);

            if (!$createPayroll) {
                return response_return('Cannot save payroll information at this moment.', [], 409);
            }

            return response_return('Successfully generated payroll.', $createPayroll->toArray(), 201);
        } catch (\Throwable $th) {
            return response_return('Error occurred in generating payroll.', [], 500);
        }
    }

    private function calculateGrossPay(Salary $salary, $attendances): float
    {
        $gross = 0;

        foreach ($attendances as $attendance) {
            $isSunday = Carbon::parse($attendance->date)->isSunday();
            $isPaidDay = in_array($attendance->status, ['Present', 'Half Day']);

            if (!$isPaidDay) {
                continue;
            }

            if ($salary->salary_type === 'Daily') {
                $dayRate = $salary->basic_salary;

                if ($attendance->status === 'Half Day') {
                    $dayRate = $dayRate / 2;
                }

                if ($isSunday) {
                    $dayRate *= self::SUNDAY_PREMIUM_RATE;
                }

                $gross += $dayRate;

                $hourlyEquivalent = $salary->basic_salary / 8;
                $gross += $attendance->overtime_hours * $hourlyEquivalent * ($isSunday ? self::SUNDAY_PREMIUM_RATE : 1);
            } elseif ($salary->salary_type === 'Hourly') {
                $rate = $isSunday ? $salary->basic_salary * self::SUNDAY_PREMIUM_RATE : $salary->basic_salary;
                $gross += $attendance->hours_worked * $rate;
                $gross += $attendance->overtime_hours * $rate;
            }
        }

        return $gross;
    }

    public function updateStatus(Request $request)
    {
        try {
            $validation = $request->validate([
                'payroll_id' => ['required', 'integer', 'exists:payrolls,id'],
                'status' => ['required', Rule::in(['Draft', 'Pending', 'Paid', 'On Hold'])],
                'payment_date' => ['nullable', 'date'],
            ]);
        } catch (\Throwable $th) {
            return response_return('Error occurred in validating the request.', [], 422);
        }

        try {
            $checkPayroll = Payroll::where('id', $validation['payroll_id'])->first();

            if (!$checkPayroll) {
                return response_return('Payroll record was not found. Please try again.', [], 409);
            }

            if ($validation['status'] === 'Paid' && empty($validation['payment_date'])) {
                return response_return('A payment_date is required when marking payroll as Paid.', [], 422);
            }

            $updatePayroll = $checkPayroll->update([
                'status' => $validation['status'],
                'payment_date' => $validation['payment_date'] ?? $checkPayroll->payment_date,
            ]);

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
            $query = Payroll::with('employee');

            if (!empty($validation['employee_id'])) {
                $query->where('employee_id', $validation['employee_id']);
            }

            if (!empty($validation['status'])) {
                $query->where('status', $validation['status']);
            }

            $payrolls = $query->orderByDesc('cutoff_start')
                ->paginate($validation['per_page'] ?? 15);

            return response_return('Successfully retrieved payrolls.', $payrolls->toArray(), 200);
        } catch (\Throwable $th) {
            return response_return('Error occurred in retrieving payrolls.', [], 500);
        }
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
}
