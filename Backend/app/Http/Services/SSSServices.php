<?php

namespace App\Http\Services;

use App\Models\SSSContribution;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SSSServices
{
    public function createSSSDeduction(Request $request)
    {
        try {
            $validation = $request->validate([
                'employee_id' => ['required', 'integer', 'exists:employees,id'],
                'amount' => ['required', 'integer'],
                'date' => ['nullable', 'date'],
                'status' => ['nullable']
            ]);
        } catch (\Throwable $th) {
            return response_return($th->getMessage(), [], 500);
        }
        try {
            $employeeSSS = SSSContribution::create([
                'employee_id' => $validation['employee_id'],
                'amount' => $validation['amount'],
                'date' => $validation['date'],
                'status' => $validation['status'] ?? 'Pending',
            ]);

            if (!$employeeSSS) return response_return('Failed to submit an SSS Contribution. Please try again.', [], 409);

            return response_return('Successfully created an SSS Contribution.', [], 200);
        } catch (\Throwable $th) {
            return response_return('Error occurred in updating the maintenance', [], 500);
        }
    }

    public function getSSSDeductionsRecords(Request $request)
    {
        try {
            $validation = $request->validate([
                'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
                'status' => ['nullable', Rule::in(['Pending', 'Posted'])],
                'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            ]);
        } catch (\Throwable $th) {
            return response_return('Error occurred in validating the request.', [], 422);
        }

        try {
            $query = SSSContribution::with('employee');

            if (!empty($validation['employee_id'])) {
                $query->where('employee_id', $validation['employee_id']);
            }

            if (!empty($validation['status'])) {
                $query->where('status', $validation['status']);
            }

            $payrolls = $query->whereNull('payroll_id')->orderByDesc('date')
                ->paginate($validation['per_page'] ?? 15);

            return response_return('Successfully retrieved SSS Deductions.', $payrolls->toArray(), 200);
        } catch (\Throwable $th) {
            return response_return($th->getMessage(), [], 500);
        }
    }

    public function getSSSDeductionsHistory(Request $request)
    {
        try {
            $validation = $request->validate([
                'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
                'status' => ['nullable', Rule::in(['Pending', 'Posted'])],
                'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            ]);
        } catch (\Throwable $th) {
            return response_return('Error occurred in validating the request.', [], 422);
        }

        try {
            $query = SSSContribution::with('employee');

            if (!empty($validation['employee_id'])) {
                $query->where('employee_id', $validation['employee_id']);
            }

            if (!empty($validation['status'])) {
                $query->where('status', $validation['status']);
            }

            $payrolls = $query->whereNotNull('payroll_id')->orderByDesc('date')
                ->paginate($validation['per_page'] ?? 15);

            return response_return('Successfully retrieved SSS Deductions.', $payrolls->toArray(), 200);
        } catch (\Throwable $th) {
            return response_return('Error occurred in retrieving SSS Deductions.', [], 500);
        }
    }

    public function removeSSSDeduction(Request $request)
    {
        try {
            $validation = $request->validate([
                'id' => ['required']
            ]);
        } catch (\Throwable $th) {
            return response_return($th->getMessage(), [], 409);
        }

        try {
            $isExisted = SSSContribution::where('id', $validation['id'])->first();

            if (!$isExisted) return response_return('There is no sss contribution select. Please try again.', [], 409);

            $isExisted->delete();

            return response_return('Successfully deleted.', [], 200);
        } catch (\Throwable $th) {
            return response_return('Something is wrong upon deletion.', [], 409);
        }
    }

    public function updateSSSDeduction(Request $request)
    {
        try {
            $validation = $request->validate([
                'id' => ['required', 'integer', 'exists:sss_contributions,id'],
                'employee_id' => ['required', 'integer', 'exists:employees,id'],
                'amount' => ['required', 'integer'],
                'date' => ['nullable', 'date'],
                'status' => ['nullable']
            ]);
        } catch (\Throwable $th) {
            return response_return($th->getMessage(), [], 500);
        }

        try {
            $employeeSSS = SSSContribution::where('id', $validation['id'])->first();

            if (!$employeeSSS) return response_return('Failed to find an SSS Contribution. Please try again.', [], 409);


            $employeeSSS->update([
                'employee_id' => $validation['employee_id'],
                'amount' => $validation['amount'],
                'date' => $validation['date'],
                'status' => $validation['status'] ?? 'Pending',
            ]);

            return response_return('Successfully updated an SSS Contribution.', [], 200);
        } catch (\Throwable $th) {
            return response_return('Error occurred in updating the maintenance', [], 500);
        }
    }
}
