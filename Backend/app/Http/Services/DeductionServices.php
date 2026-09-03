<?php

namespace App\Http\Services;

use App\Models\Deduction;
use App\Models\Payroll;
use App\Models\CashAdvance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeductionServices
{
    /**
     * Creates the deduction record for a payroll run.
     * ca_deduction is NOT taken from the request — it's computed by summing
     * every Approved, unattached cash advance for this employee, which then
     * each get attached to this payroll and flipped to Deducted/Paid.
     */
    public function createDeduction(Request $request)
    {
        try {
            $validation = $request->validate([
                'payroll_id' => ['required', 'integer', 'exists:payrolls,id'],
                'employee_id' => ['required', 'integer', 'exists:employees,id'],
                'sss_deduction' => ['required', 'numeric', 'min:0'],
                'other_deduction' => ['nullable', 'array'],
                'remarks' => ['required', 'string'],
            ]);
        } catch (\Throwable $th) {
            return response_return('Error occurred in validating deduction information.', [], 422);
        }

        try {
            $checkPayroll = Payroll::where('id', $validation['payroll_id'])
                ->where('employee_id', $validation['employee_id'])
                ->first();

            if (!$checkPayroll) {
                return response_return('Payroll record was not found for this employee.', [], 409);
            }

            $alreadyExists = Deduction::where('payroll_id', $validation['payroll_id'])->exists();

            if ($alreadyExists) {
                return response_return('A deduction record already exists for this payroll.', [], 409);
            }

            $result = DB::transaction(function () use ($validation, $checkPayroll) {
                // Pull every Approved cash advance for this employee that
                // hasn't already been attached to a payroll.
                $approvedAdvances = CashAdvance::where('employee_id', $validation['employee_id'])
                    ->where('status', 'Approved')
                    ->whereNull('payroll_id')
                    ->get();

                $caDeduction = 0;

                foreach ($approvedAdvances as $advance) {
                    $advance->update([
                        'payroll_id' => $checkPayroll->id,
                        'status' => 'Deducted/Paid',
                    ]);
                    $caDeduction += $advance->amount;
                }

                $createDeduction = Deduction::create([
                    'employee_id' => $validation['employee_id'],
                    'payroll_id' => $checkPayroll->id,
                    'sss_deduction' => $validation['sss_deduction'],
                    'ca_deduction' => $caDeduction,
                    'other_deduction' => $validation['other_deduction'] ?? null,
                    'remarks' => $validation['remarks'],
                ]);

                $otherTotal = 0;
                if (!empty($validation['other_deduction'])) {
                    $otherTotal = array_sum(array_map('floatval', $validation['other_deduction']));
                }

                $totalDeductions = $validation['sss_deduction'] + $caDeduction + $otherTotal;
                $netPay = $checkPayroll->gross_pay - $totalDeductions;

                $checkPayroll->update([
                    'total_deductions' => round($totalDeductions, 2),
                    'net_pay' => round($netPay, 2),
                ]);

                return $createDeduction;
            });

            return response_return('Successfully created deduction record.', $result->toArray(), 201);
        } catch (\Throwable $th) {
            return response_return('Error occurred in creating deduction record.', [], 500);
        }
    }

    public function getDeductions(Request $request)
    {
        try {
            $validation = $request->validate([
                'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
                'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            ]);
        } catch (\Throwable $th) {
            return response_return('Error occurred in validating the request.', [], 422);
        }

        try {
            $query = Deduction::with(['employee', 'payroll']);

            if (!empty($validation['employee_id'])) {
                $query->where('employee_id', $validation['employee_id']);
            }

            $deductions = $query->orderByDesc('created_at')
                ->paginate($validation['per_page'] ?? 15);

            return response_return('Successfully retrieved deductions.', $deductions->toArray(), 200);
        } catch (\Throwable $th) {
            return response_return('Error occurred in retrieving deductions.', [], 500);
        }
    }

    public function getDeduction(Request $request)
    {
        try {
            $validation = $request->validate([
                'payroll_id' => ['required', 'integer', 'exists:payrolls,id'],
            ]);
        } catch (\Throwable $th) {
            return response_return('Error occurred in validating the request.', [], 422);
        }

        try {
            $deduction = Deduction::with(['employee', 'payroll'])
                ->where('payroll_id', $validation['payroll_id'])
                ->first();

            if (!$deduction) {
                return response_return('Deduction record was not found for this payroll.', [], 409);
            }

            return response_return('Successfully retrieved deduction.', $deduction->toArray(), 200);
        } catch (\Throwable $th) {
            return response_return('Error occurred in retrieving deduction.', [], 500);
        }
    }
}
