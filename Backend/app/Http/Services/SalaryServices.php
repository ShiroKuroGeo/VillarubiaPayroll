<?php

namespace App\Http\Services;

use App\Models\Employee;
use App\Models\Salary;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SalaryServices
{
    public function createSalary(Request $request)
    {
        try {
            $validation = $request->validate([
                'employee_id' => ['required', 'integer', 'exists:employees,id'],
                'salary_type' => ['required', Rule::in(['Monthly', 'Semi-Monthly', 'Weekly', 'Daily', 'Hourly', 'Piece-Rate'])],
                'basic_salary' => ['required', 'numeric', 'min:0'],
                'effective_date' => ['required', 'date'],
            ]);
        } catch (\Throwable $th) {
            return response_return($th->getMessage(), [], 422);
        }

        try {
            Salary::where('employee_id', $validation['employee_id'])
                ->where('is_active', true)
                ->update(['is_active' => false]);

            $createSalary = Salary::create([
                'employee_id' => $validation['employee_id'],
                'salary_type' => $validation['salary_type'],
                'basic_salary' => $validation['basic_salary'],
                'effective_date' => $validation['effective_date'],
                'is_active' => true,
            ]);

            if (!$createSalary) {
                return response_return('Cannot save salary information at this moment.', [], 409);
            }

            return response_return('Successfully created salary.', $createSalary->toArray(), 201);
        } catch (\Throwable $th) {
            return response_return('Error occurred in creating salary.', [], 500);
        }
    }

    public function updateSalary(Request $request)
    {
        try {
            $validation = $request->validate([
                'salary_id' => ['required', 'integer', 'exists:salaries,id'],
                'salary_type' => ['sometimes', Rule::in(['Monthly', 'Semi-Monthly', 'Weekly', 'Daily', 'Hourly', 'Piece-Rate'])],
                'basic_salary' => ['sometimes', 'numeric', 'min:0'],
                'effective_date' => ['sometimes', 'date'],
            ]);
        } catch (\Throwable $th) {
            return response_return('Error occurred in validating salary information.', [], 422);
        }

        try {
            $checkSalary = Salary::where('id', $validation['salary_id'])->first();

            if (!$checkSalary) {
                return response_return('Salary record was not found. Please try again.', [], 409);
            }

            if (!$checkSalary->is_active) {
                return response_return('Only the active salary record can be edited. Create a new salary entry instead.', [], 409);
            }

            $updateSalary = $checkSalary->update(
                collect($validation)->except(['salary_id'])->toArray()
            );

            if (!$updateSalary) {
                return response_return('Cannot save salary information at this moment.', [], 409);
            }

            return response_return('Successfully updated salary.', $checkSalary->toArray(), 200);
        } catch (\Throwable $th) {
            return response_return('Error occurred in updating salary.', [], 500);
        }
    }

    // public function getSalaries(Request $request)
    // {
    //     try {
    //         $employeeSalary = Employee::with('activeSalary', 'job')
    //             ->orderByDesc('date_hired')
    //             ->get();

    //         $data = $employeeSalary->map(function($employeeSalary){
    //             return [
    //                 'id' => $employeeSalary->activeSalary->id,
    //                 'employeeId' => $employeeSalary->id,
    //                 'employeeName' => $employeeSalary->last_name . ', '. $employeeSalary->first_name,
    //                 'location' => $employeeSalary->location,
    //                 'phoneNumber' => $employeeSalary->job->label,
    //                 'basicSalary' => $employeeSalary->activeSalary->basic_salary,
    //                 'salaryType' => $employeeSalary->activeSalary->salary_type,
    //                 'effectiveDate' => $employeeSalary->activeSalary->effective_date,
    //                 'status' => $employeeSalary->activeSalary->is_active,
    //                 'image' => $employeeSalary->image,
    //             ];
    //         });

    //         return response_return('Successfully retrieved salary history.', $data->toArray(), 200);
    //     } catch (\Throwable $th) {
    //         return response_return($th->getMessage(), [], 500);
    //     }
    // }

    public function getSalaries(Request $request)
    {
        try {
            $employees = Employee::with('activeSalary', 'job')
                ->whereHas('activeSalary')
                ->orderByDesc('date_hired')
                ->get();

            $data = $employees->map(function ($employee) {
                $salary = $employee->activeSalary;

                return [
                    'id'            => $salary?->id,
                    'employeeId'    => $employee->id,
                    'employeeName'  => $employee->last_name . ', ' . $employee->first_name,
                    'location'      => $employee->location,
                    'phoneNumber'   => $employee->job->label ?? null,
                    'basicSalary'   => $salary?->basic_salary,
                    'salaryType'    => $salary?->salary_type,
                    'effectiveDate' => $salary?->effective_date,
                    'status'        => $salary?->is_active,
                    'image'         => $employee->image,
                    'hasSalary'     => $salary !== null,
                ];
            });

            return response_return('Successfully retrieved salary history.', $data->toArray(), 200);
        } catch (\Throwable $th) {
            return response_return($th->getMessage(), [], 500);
        }
    }

    public function getActiveSalary(Request $request)
    {
        try {
            $validation = $request->validate([
                'employee_id' => ['required', 'integer', 'exists:employees,id'],
            ]);
        } catch (\Throwable $th) {
            return response_return('Error occurred in validating the request.', [], 422);
        }

        try {
            $salary = Salary::where('employee_id', $validation['employee_id'])
                ->where('is_active', true)
                ->first();

            if (!$salary) {
                return response_return('No active salary found for this employee.', [], 409);
            }

            return response_return('Successfully retrieved active salary.', $salary->toArray(), 200);
        } catch (\Throwable $th) {
            return response_return('Error occurred in retrieving active salary.', [], 500);
        }
    }
}
