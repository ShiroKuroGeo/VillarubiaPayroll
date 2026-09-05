<?php

namespace App\Http\Services;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Employee;
use App\Models\JobType;

class EmployeeServices
{
    public function createEmployee(Request $request)
    {
        try {
            $validation = $request->validate([
                'job_id' => ['required', 'integer', 'exists:job_types,id'],
                'image' => ['nullable', 'image', 'max:2048'],
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'phone_number' => ['nullable', 'string', 'min:10', 'max:15'],
                'location' => ['nullable', 'string'],
                'email' => ['required', 'email', 'unique:employees,email'],
                'status' => ['nullable', Rule::in(['Full Time', 'Part Time', 'Suspended', 'Separated/Terminated', 'Probationary'])],
                'date_hired' => ['required', 'date'],
            ]);
        } catch (\Throwable $th) {
            return response_return($th->getMessage(), [], 422);
        }

        try {
            if ($request->hasFile('image')) {
                $validation['image'] = $request->file('image')->store('employees', 'public');
            }

            $createEmployee = Employee::create($validation);

            if (!$createEmployee) {
                return response_return('Cannot save employee information at this moment.', [], 409);
            }

            return response_return('Successfully created employee.', [
                'name' => $validation['last_name'] . ', ' . $validation['first_name'],
                'employee_id' => $createEmployee->id
            ], 201);
        } catch (\Throwable $th) {
            return response_return($th->getMessage(), [], 500);
        }
    }

    public function updateImage(Request $request)
    {
        try {

            $validation = $request->validate([
                'employee_id' => ['required', 'integer', 'exists:employees,id'],
                'image' => ['nullable', 'image', 'max:2048'],
            ]);

            $checkEmployee = Employee::where('id', $validation['employee_id'])->first();

            if (!$checkEmployee) {
                return response_return('Employee selected was not found. Please try again.', [], 409);
            }

            $newImagePath = $request->file('image')->store('employees', 'public');

            $checkEmployee->update([
                'image' => $newImagePath,
            ]);

            return response_return('Successfully created employee.', [
                'name' => $checkEmployee->last_name . ', ' . $checkEmployee->first_name,
                'employee_id' => $checkEmployee->id
            ], 201);
        } catch (\Throwable $th) {
            return response_return('Error occurred in creating an employee.', [], 500);
        }
    }

    public function updateEmployee(Request $request)
    {
        try {
            $validation = $request->validate([
                'employee_id' => ['required', 'integer', 'exists:employees,id'],
                'job_id' => ['sometimes', 'integer', 'exists:job_types,id'],
                'first_name' => ['sometimes', 'string', 'max:255'],
                'last_name' => ['sometimes', 'string', 'max:255'],
                'phone_number' => ['nullable', 'string', 'max:20'],
                'location' => ['nullable', 'string'],
                'email' => ['sometimes', 'email', Rule::unique('employees', 'email')->ignore($request->employee_id)],
                'status' => ['sometimes', Rule::in(['Full Time', 'Part Time', 'Suspended', 'Separated/Terminated', 'Probationary'])],
                'date_hired' => ['sometimes', 'date'],
            ]);
        } catch (\Throwable $th) {
            return response_return($th->getMessage(), [], 422);
        }

        try {
            $checkEmployee = Employee::where('id', $validation['employee_id'])->first();

            if (!$checkEmployee) {
                return response_return('Employee selected was not found. Please try again.', [], 409);
            }

            $updateEmployee = $checkEmployee->update(
                collect($validation)->except(['employee_id'])->toArray()
            );

            if (!$updateEmployee) {
                return response_return('Cannot save employee information at this moment.', [], 409);
            }

            return response_return('Successfully updated employee.', [
                'name' => $checkEmployee->last_name . ', ' . $checkEmployee->first_name,
                'employee_id' => $checkEmployee->id
            ], 200);
        } catch (\Throwable $th) {
            return response_return('Error occurred in updating employee.', [], 500);
        }
    }

    public function removeEmployee(Request $request)
    {
        try {
            $validation = $request->validate([
                'employee_id' => ['required', 'integer', 'exists:employees,id'],
            ]);
        } catch (\Throwable $th) {
            return response_return('Error occurred in validating employee information.', [], 422);
        }

        try {
            $checkEmployee = Employee::where('id', $validation['employee_id'])->first();

            if (!$checkEmployee) {
                return response_return('Employee selected was not found. Please try again.', [], 409);
            }

            $removeEmployee = $checkEmployee->delete();

            if (!$removeEmployee) {
                return response_return('Cannot remove employee at this moment.', [], 409);
            }

            return response_return('Successfully removed employee.', [
                'name' => $checkEmployee->last_name . ', ' . $checkEmployee->first_name,
                'employee_id' => $checkEmployee->id
            ], 200);
        } catch (\Throwable $th) {
            return response_return('Error occurred in removing employee.', [], 500);
        }
    }

    public function restoreEmployee(Request $request)
    {
        try {
            $validation = $request->validate([
                'employee_id' => ['required', 'integer'],
            ]);
        } catch (\Throwable $th) {
            return response_return('Error occurred in validating employee information.', [], 422);
        }

        try {
            $checkEmployee = Employee::withTrashed()->where('id', $validation['employee_id'])->first();

            if (!$checkEmployee) {
                return response_return('Employee selected was not found. Please try again.', [], 409);
            }

            if (!$checkEmployee->trashed()) {
                return response_return('Employee is not currently removed.', [], 409);
            }

            $checkEmployee->restore();

            return response_return('Successfully restored employee.', [
                'name' => $checkEmployee->last_name . ', ' . $checkEmployee->first_name,
                'employee_id' => $checkEmployee->id
            ], 200);
        } catch (\Throwable $th) {
            return response_return('Error occurred in restoring employee.', [], 500);
        }
    }

    public function getEmployees(Request $request)
    {
        try {
            $validation = $request->validate([
                'status' => ['nullable', Rule::in(['Full Time', 'Part Time', 'Suspended', 'Separated/Terminated', 'Probationary'])],
                'job_id' => ['nullable', 'integer', 'exists:job_types,id'],
                'search' => ['nullable', 'string', 'max:255'],
                'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            ]);
        } catch (\Throwable $th) {
            return response_return($th->getMessage(), [], 422);
        }

        try {
            $query = Employee::with('job');

            if (!empty($validation['status'])) {
                $query->where('status', $validation['status']);
            }

            if (!empty($validation['job_id'])) {
                $query->where('job_id', $validation['job_id']);
            }

            if (!empty($validation['search'])) {
                $search = $validation['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $employees = $query->orderBy('last_name')
                ->paginate($validation['per_page'] ?? 15);

            return response_return('Successfully retrieved employees.', $employees->toArray(), 200);
        } catch (\Throwable $th) {
            return response_return('Error occurred in retrieving employees.', [], 500);
        }
    }

    public function getEmployee(Request $request)
    {
        try {
            $validation = $request->validate([
                'employee_id' => ['required', 'integer', 'exists:employees,id'],
            ]);
        } catch (\Throwable $th) {
            return response_return('Error occurred in validating the request.', [], 422);
        }

        try {
            $checkEmployee = Employee::with('job')->where('id', $validation['employee_id'])->first();

            if (!$checkEmployee) {
                return response_return('Employee selected was not found. Please try again.', [], 409);
            }

            return response_return('Successfully retrieved employee.', $checkEmployee->toArray(), 200);
        } catch (\Throwable $th) {
            return response_return('Error occurred in retrieving employee.', [], 500);
        }
    }

    public function getJobTypes()
    {
        try {
            $jobTypes = JobType::where('status', 'Active')->orderByDesc('id')->get(['label', 'id']);

            return response_return('Successfully retrieved job types', $jobTypes->toArray(), 200);
        } catch (\Throwable $th) {
            return response_return('Error occurred in retrieving job types.', [], 500);
        }
    }

    public function getAllEmployees()
    {
        $employees = Employee::select(['id', 'first_name', 'last_name'])->get();

        $data = $employees->map(function ($employee) {
            return [
                'id'   => $employee->id,
                'name' => $employee->last_name . ', ' . $employee->first_name,
            ];
        });

        return response_return('Successfully retrieved employees.', $data->toArray(), 200);
    }
}
