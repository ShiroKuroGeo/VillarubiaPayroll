<?php

namespace App\Http\Services;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class AttendanceServices
{
    public function createAttendance(Request $request)
    {
        try {
            $validation = $request->validate([
                'employee_id' => ['required', 'integer', 'exists:employees,id'],
                'date' => ['required', 'date'],
                'time_in' => ['nullable', 'date_format:H:i'],
                'time_out' => ['nullable', 'date_format:H:i', 'after:time_in'],
                'status' => ['nullable', Rule::in(['Present', 'Leave', 'Half Day', 'Absent'])],
                'remarks' => ['nullable', 'string'],
            ]);
        } catch (\Throwable $th) {
            return response_return('Error occurred in validating attendance information.', [], 422);
        }

        try {
            $checkExisting = Attendance::where('employee_id', $validation['employee_id'])
                ->where('date', $validation['date'])
                ->exists();

            if ($checkExisting) {
                return response_return('Attendance for this employee on this date already exists.', [], 409);
            }

            $hoursWorked = 0;
            $overtimeHours = 0;

            if (!empty($validation['time_in']) && !empty($validation['time_out'])) {
                $timeIn = Carbon::createFromFormat('H:i', $validation['time_in']);
                $timeOut = Carbon::createFromFormat('H:i', $validation['time_out']);

                $totalHours = $timeIn->diffInMinutes($timeOut) / 60;

                $hoursWorked = min($totalHours, 8);
                $overtimeHours = max($totalHours - 8, 0);
            }

            $createAttendance = Attendance::create([
                'employee_id' => $validation['employee_id'],
                'date' => $validation['date'],
                'time_in' => $validation['time_in'] ?? null,
                'time_out' => $validation['time_out'] ?? null,
                'hours_worked' => round($hoursWorked, 2),
                'overtime_hours' => round($overtimeHours, 2),
                'status' => $validation['status'] ?? 'Present',
                'remarks' => $validation['remarks'] ?? null,
            ]);

            if (!$createAttendance) {
                return response_return('Cannot save attendance information at this moment.', [], 409);
            }

            return response_return('Successfully created attendance.', $createAttendance->toArray(), 201);
        } catch (\Throwable $th) {
            return response_return('Error occurred in creating an attendance.', [], 500);
        }
    }

    public function updateAttendance(Request $request)
    {
        try {
            $validation = $request->validate([
                'attendance_id' => ['required', 'integer', 'exists:attendances,id'],
                'date' => ['sometimes', 'date'],
                'time_in' => ['nullable', 'date_format:H:i'],
                'time_out' => ['nullable', 'date_format:H:i', 'after:time_in'],
                'status' => ['sometimes', Rule::in(['Present', 'Leave', 'Half Day', 'Absent'])],
                'remarks' => ['nullable', 'string'],
            ]);
        } catch (\Throwable $th) {
            return response_return('Error occurred in validating attendance information.', [], 422);
        }

        try {
            $checkAttendance = Attendance::where('id', $validation['attendance_id'])->first();

            if (!$checkAttendance) {
                return response_return('Attendance record was not found. Please try again.', [], 409);
            }

            if (!empty($validation['date']) && $validation['date'] !== $checkAttendance->date) {
                $checkDuplicate = Attendance::where('employee_id', $checkAttendance->employee_id)
                    ->where('date', $validation['date'])
                    ->where('id', '!=', $checkAttendance->id)
                    ->exists();

                if ($checkDuplicate) {
                    return response_return('Attendance for this employee on this date already exists.', [], 409);
                }
            }

            $timeIn = array_key_exists('time_in', $validation) ? $validation['time_in'] : $checkAttendance->time_in;
            $timeOut = array_key_exists('time_out', $validation) ? $validation['time_out'] : $checkAttendance->time_out;

            $hoursWorked = $checkAttendance->hours_worked;
            $overtimeHours = $checkAttendance->overtime_hours;

            if (!empty($timeIn) && !empty($timeOut)) {
                $parsedIn = Carbon::createFromFormat('H:i', substr($timeIn, 0, 5));
                $parsedOut = Carbon::createFromFormat('H:i', substr($timeOut, 0, 5));

                $totalHours = $parsedIn->diffInMinutes($parsedOut) / 60;

                $hoursWorked = round(min($totalHours, 8), 2);
                $overtimeHours = round(max($totalHours - 8, 0), 2);
            } elseif (empty($timeIn) || empty($timeOut)) {
                $hoursWorked = 0;
                $overtimeHours = 0;
            }

            $updateAttendance = $checkAttendance->update([
                'date' => $validation['date'] ?? $checkAttendance->date,
                'time_in' => $timeIn,
                'time_out' => $timeOut,
                'hours_worked' => $hoursWorked,
                'overtime_hours' => $overtimeHours,
                'status' => $validation['status'] ?? $checkAttendance->status,
                'remarks' => array_key_exists('remarks', $validation) ? $validation['remarks'] : $checkAttendance->remarks,
            ]);

            if (!$updateAttendance) {
                return response_return('Cannot save attendance information at this moment.', [], 409);
            }

            return response_return('Successfully updated attendance.', $checkAttendance->toArray(), 200);
        } catch (\Throwable $th) {
            return response_return('Error occurred in updating attendance.', [], 500);
        }
    }

    public function getAttendance(Request $request)
    {
        try {
            $validation = $request->validate([
                'employee_id' => ['required', 'integer', 'exists:employees,id'],
            ]);
        } catch (\Throwable $th) {
            return response_return('Error occurred in validating the request.', [], 422);
        }

        try {
            $attedance = Attendance::where('employee_id', $validation['employee_id'])
                ->orderByDesc('date')
                ->get();

            return response_return('Successfully retrieved salary history.', $attedance->toArray(), 200);
        } catch (\Throwable $th) {
            return response_return('Error occurred in retrieving salary history.', [], 500);
        }
    }

    public function getAttendances()
    {
        try {
            $attedance = Attendance::orderByDesc('date')->get();

            return response_return('Successfully retrieved salary history.', $attedance->toArray(), 200);
        } catch (\Throwable $th) {
            return response_return('Error occurred in retrieving salary history.', [], 500);
        }
    }
}
