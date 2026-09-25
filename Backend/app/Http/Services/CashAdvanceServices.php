<?php

namespace App\Http\Services;

use App\Models\CashAdvance;
use App\Models\CashAdvanceDeduction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class CashAdvanceServices
{
    public function requestCashAdvance(Request $request)
    {
        try {
            $validation = $request->validate([
                'employee_id' => ['required', 'integer', 'exists:employees,id'],
                'amount' => ['nullable', 'numeric', 'min:1'],
                'balance' => ['nullable', 'numeric', 'min:1'],
                'custom_amount' => ['nullable', 'numeric', 'min:1'],
                'installment_amount' => ['nullable', 'numeric', 'min:1'],
                'installment_count' => ['nullable', 'numeric', 'min:1'],
                'requested_date' => ['nullable', 'date'],
                'reason' => ['nullable', 'string'],
            ]);
        } catch (\Throwable $th) {
            return response_return('Error occurred in validating cash advance information.', [], 422);
        }

        try {
            if($request->payment_type == 'Custom'){
                if (!Carbon::parse($request->target_cutoff_start)->isSaturday()) {
                    return response_return('Target cutoff date is not a saturday. ', [], 422);
                }
            }

            $createCashAdvance = CashAdvance::create([
                'employee_id' => $validation['employee_id'],
                'amount' => $validation['amount'],
                'balance' => $validation['balance'],
                'custom_amount' => $validation['custom_amount'],
                'target_cutoff_start' => $request->target_cutoff_start,
                'payment_type' => $request->payment_type,
                'installment_amount' => $validation['installment_amount'],
                'installment_count' => $validation['installment_count'],
                'requested_date' => $validation['requested_date'],
                'reason' => $validation['reason'] ?? null,
                'status' => 'Pending',
            ]);

            if (!$createCashAdvance) {
                return response_return('Cannot save cash advance request at this moment.', [], 409);
            }

            return response_return('Successfully submitted cash advance request.', $createCashAdvance->toArray(), 201);
        } catch (\Throwable $th) {
            return response_return($th->getMessage(), [], 500);
        }
    }

    public function reviewCashAdvance(Request $request)
    {
        try {
            $validation = $request->validate([
                'cash_advance_id' => ['required', 'integer', 'exists:cash_advances,id'],
                'status' => ['required', Rule::in(['Approved', 'Rejected'])],
            ]);
        } catch (\Throwable $th) {
            return response_return('Error occurred in validating the request.', [], 422);
        }

        try {
            $checkCashAdvance = CashAdvance::where('id', $validation['cash_advance_id'])->first();

            if (!$checkCashAdvance) {
                return response_return('Cash advance request was not found. Please try again.', [], 409);
            }

            if ($checkCashAdvance->status !== 'Pending') {
                return response_return('Only pending requests can be approved or rejected.', [], 409);
            }

            $updateCashAdvance = $checkCashAdvance->update([
                'status' => $validation['status'],
            ]);

            if (!$updateCashAdvance) {
                return response_return('Cannot save cash advance information at this moment.', [], 409);
            }

            $message = $validation['status'] === 'Approved'
                ? 'Successfully approved cash advance request.'
                : 'Successfully rejected cash advance request.';

            return response_return($message, $checkCashAdvance->toArray(), 200);
        } catch (\Throwable $th) {
            return response_return('Error occurred in reviewing cash advance request.', [], 500);
        }
    }

    public function attachToPayroll(int $cashAdvanceId, int $payrollId)
    {
        $cashAdvance = CashAdvance::where('id', $cashAdvanceId)
            ->where('status', 'Approved')
            ->whereNull('payroll_id')
            ->first();

        if (!$cashAdvance) {
            return false;
        }

        return $cashAdvance->update([
            'payroll_id' => $payrollId,
            'status' => 'Deducted/Paid',
        ]);
    }

    public function getCashAdvances(Request $request)
    {
        try {
            $validation = $request->validate([
                'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
                'status' => ['nullable', Rule::in(['Approved', 'Pending', 'Deducted/Paid', 'Rejected'])],
                'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            ]);
        } catch (\Throwable $th) {
            return response_return($th->getMessage(), [], 422);
        }

        try {
            $query = CashAdvance::with('employee');

            if (!empty($validation['employee_id'])) {
                $query->where('employee_id', $validation['employee_id']);
            }

            if (!empty($validation['status'])) {
                $query->where('status', $validation['status']);
            }

            $cashAdvances = $query->orderByDesc('requested_date')
                ->paginate($validation['per_page'] ?? 15);

            return response_return('Successfully retrieved cash advances.', $cashAdvances->toArray(), 200);
        } catch (\Throwable $th) {
            return response_return('Error occurred in retrieving cash advances.', [], 500);
        }
    }

    public function getCashAdvance(Request $request)
    {
        try {
            $validation = $request->validate([
                'cash_advance_id' => ['required', 'integer', 'exists:cash_advances,id'],
            ]);
        } catch (\Throwable $th) {
            return response_return('Error occurred in validating the request.', [], 422);
        }

        try {
            $cashAdvance = CashAdvance::with('employee')->where('id', $validation['cash_advance_id'])->first();

            if (!$cashAdvance) {
                return response_return('Cash advance request was not found. Please try again.', [], 409);
            }

            return response_return('Successfully retrieved cash advance.', $cashAdvance->toArray(), 200);
        } catch (\Throwable $th) {
            return response_return('Error occurred in retrieving cash advance.', [], 500);
        }
    }

    public function nextDeduction(Request $request)
    {
        try {
            $validation = $request->validate([
                'cash_advance_id' => ['required', 'integer', 'exists:cash_advances,id'],
                'amount_deducted' => ['nullable', 'integer'],
            ]);
        } catch (\Throwable $th) {
            return response_return('Error occurred in validating the request.', [], 422);
        }

        try {

            $balance = CashAdvance::where('id', $validation['cash_advance_id'])->first();

            if (!$balance) {
                return response_return('Cash advance record was not found.', [], 409);
            }

            $message = '';

            if ($validation['amount_deducted'] > $balance->balance) {
                $validation['amount_deducted'] = $balance->balance;
                $message = 'Amount to be deduction is higher than the balance. Automatically change to how much balance.';
            }

            $updateCashAdvance = $balance->update([
                'installment_amount' => $validation['amount_deducted'],
                'balance' => $balance->balance - $validation['amount_deducted'],
            ]);

            if (!$updateCashAdvance) {
                return response_return('Updated cash advances for deductions is not recorded. Please try again.', [], 409);
            }
            return response_return($message ?? 'Updated cash advances for deductions is successfully updated.', [], 200);
        } catch (\Throwable $th) {
            return response_return('Error occurred in retrieving cash advance.', [], 500);
        }
    }
}
