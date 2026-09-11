<?php

namespace App\Http\Services;

use App\Models\Deduction;
use App\Models\Payroll;
use App\Models\CashAdvance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeductionServices
{
    public function createDeduction(Request $request)
    {
        try {
            $validation = $request->validate([
                'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
                'sss_deduction' => ['nullable', 'numeric', 'min:0'],
                'other_deduction' => ['nullable', 'array'],
                'remarks' => ['nullable', 'string'],
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
            $query = Deduction::with(['employee', 'payroll', 'employee.job']);

            if (!empty($validation['employee_id'])) {
                $query->where('employee_id', $validation['employee_id']);
            }

            $deductions = $query->orderByDesc('created_at')
                ->paginate($validation['per_page'] ?? 15);

            $data = $deductions->map(function ($deduction) {
                $initials = strtoupper(
                    substr($deduction->employee->first_name ?? '', 0, 1)
                        . substr($deduction->employee->last_name ?? '', 0, 1)
                ) ?: '—';
                return [
                    'id'   => $deduction->id,
                    'employeeId' => $deduction->employee->id,
                    'employeeName'   => $deduction->employee->last_name . ', ' . $deduction->employee->first_name,
                    'initials'   => $initials,
                    'department'   => $deduction->employee->job->label,
                    'status'   => $deduction->employee->status,
                    'image'   => $deduction->employee->image,
                    'sss'   => $deduction->sss_deduction,
                    'ca'   => $deduction->ca_deduction,
                    'payroll_date' => $deduction->payroll->payout_date,
                    'otherDeductions' => collect(
                        $deduction->other_deduction ?? []
                    )
                        ->map(function ($amount, $key) {
                            return [
                                'key' => $key,
                                'label' => ucwords(
                                    str_replace('_', ' ', $key)
                                ),
                                'amount' => (float) $amount,
                            ];
                        })
                        ->values()
                        ->toArray(),
                ];
            });

            return response_return('Successfully retrieved deductions.', $data->toArray(), 200);
        } catch (\Throwable $th) {
            return response_return('Error occurred in retrieving deductions.', [], 500);
        }
    }

    public function getDeduction(Request $request)
    {
        try {
            $validation = $request->validate([
                'deduction_id' => ['required', 'integer', 'exists:payrolls,id'],
            ]);
        } catch (\Throwable $th) {
            return response_return('Error occurred in validating the request.', [], 422);
        }

        try {
            $deduction = Deduction::with(['employee', 'payroll'])
                ->where('id', $validation['deduction_id'])
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
