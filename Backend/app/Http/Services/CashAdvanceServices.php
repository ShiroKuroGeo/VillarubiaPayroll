<?php

namespace App\Http\Services;

use App\Models\CashAdvance;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CashAdvanceServices
{
    /**
     * Employee (or whoever submits on their behalf) requests a cash advance.
     * Always starts as Pending — only an admin can move it forward.
     */
    public function requestCashAdvance(Request $request)
    {
        try {
            $validation = $request->validate([
                'employee_id' => ['required', 'integer', 'exists:employees,id'],
                'amount' => ['required', 'numeric', 'min:1'],
                'requested_date' => ['required', 'date'],
                'reason' => ['nullable', 'string'],
            ]);
        } catch (\Throwable $th) {
            return response_return('Error occurred in validating cash advance information.', [], 422);
        }

        try {
            $createCashAdvance = CashAdvance::create([
                'employee_id' => $validation['employee_id'],
                'amount' => $validation['amount'],
                'requested_date' => $validation['requested_date'],
                'reason' => $validation['reason'] ?? null,
                'status' => 'Pending',
            ]);

            if (!$createCashAdvance) {
                return response_return('Cannot save cash advance request at this moment.', [], 409);
            }

            return response_return('Successfully submitted cash advance request.', $createCashAdvance->toArray(), 201);
        } catch (\Throwable $th) {
            return response_return('Error occurred in submitting cash advance request.', [], 500);
        }
    }

    /**
     * Admin action: approve or reject a pending request.
     * Kept separate from a generic "update" so this can be locked down to
     * admin-only routes without also exposing amount/date edits.
     */
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
            return response_return('Error occurred in validating the request.', [], 422);
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
}
