<?php

namespace App\Http\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Maintenance;
use App\Models\BiometricLog;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\IOFactory;

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
                'id' => ['required', 'integer', 'exists:attendances,id'],
                'date' => ['sometimes', 'date'],
                'timeIn' => ['nullable', 'date_format:H:i'],
                'timeOut' => ['nullable', 'date_format:H:i', 'after:time_in'],
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
            $attedance = Attendance::with(['employee'])->orderByDesc('date')->get();

            $data = $attedance->map(function ($attendance) {
                return [
                    "id" => $attendance->id,
                    "employeeId" => $attendance->employee->id,
                    "employeeName" => $attendance->employee->last_name . ', ' . $attendance->employee->first_name,
                    "initials" => 'AA',
                    "phoneNumber" => $attendance->employee->phone_number,
                    "date" => $attendance->date,
                    "timeIn" => $attendance->time_in,
                    "timeOut" => $attendance->time_out,
                    "overtimeHours" => $attendance->overtime_hours,
                    "status" => $attendance->status,
                    "notes" => $attendance->remarks,
                    "image" => $attendance->employee->image
                ];
            });

            return response_return('Successfully retrieved salary history.', $data->toArray(), 200);
        } catch (\Throwable $th) {
            return response_return('Error occurred in retrieving salary history.', [], 500);
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:xlsx,xls,csv',
            ],
        ]);

        $file = $request->file('file');

        $spreadsheet = IOFactory::load(
            $file->getPathname()
        );

        $worksheet = $spreadsheet->getActiveSheet();

        $rows = $worksheet->toArray();

        $imported = 0;
        $duplicates = 0;
        $employeesNotFound = [];

        $affectedAttendances = [];

        DB::beginTransaction();

        $workStartTimeSetting = Maintenance::where(
            'name',
            'Work Start Time'
        )->first();

        $workEndTimeSetting = Maintenance::where(
            'name',
            'Work End Time'
        )->first();

        if (!$workStartTimeSetting || !$workEndTimeSetting) {

            return response()->json([
                'message' => 'Work Start Time or Work End Time setting was not found.'
            ], 422);
        }

        $workStartTime = $workStartTimeSetting->value;
        $workEndTime = $workEndTimeSetting->value;

        try {

            foreach ($rows as $index => $row) {

                if ($index === 0) {
                    continue;
                }

                $biometricUserId = trim(
                    (string) ($row[0] ?? '')
                );

                $scanDateTime = trim(
                    (string) ($row[1] ?? '')
                );

                if (
                    empty($biometricUserId) ||
                    empty($scanDateTime)
                ) {
                    continue;
                }

                try {
                    $scanTime = Carbon::createFromFormat(
                        'd/m/Y H:i',
                        $scanDateTime
                    );
                } catch (\Exception $e) {

                    continue;
                }
                $employee = Employee::where(
                    'biometric_user_id',
                    $biometricUserId
                )->first();

                if (!$employee) {

                    $employeesNotFound[] =
                        $biometricUserId;

                    continue;
                }

                $log = BiometricLog::firstOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'scan_time' => $scanTime,
                    ],
                    [
                        'biometric_user_id' =>
                        $biometricUserId,
                    ]
                );

                if ($log->wasRecentlyCreated) {

                    $imported++;
                } else {

                    $duplicates++;
                }

                $date = $scanTime->format('Y-m-d');

                $key =
                    $employee->id .
                    '_' .
                    $date;

                $affectedAttendances[$key] = [
                    'employee_id' => $employee->id,
                    'date' => $date,
                ];
            }

            foreach ($affectedAttendances as $attendanceData) {
                $this->processAttendance(
                    $attendanceData['employee_id'],
                    $attendanceData['date'],
                    $workStartTime,
                    $workEndTime
                );
            }

            DB::commit();

            return response_return('Biometric logs imported and attendance processed successfully.', [
                'imported' =>
                $imported,
                'duplicates' =>
                $duplicates,
                'employees_not_found' =>
                array_values(
                    array_unique(
                        $employeesNotFound
                    )
                ),
                'attendances_processed' =>
                count(
                    $affectedAttendances
                ),
            ], 200);
        } catch (\Exception $e) {

            DB::rollBack();
            return response_return('Failed to import biometric logs.', [], 500);
        }
    }

    private function processAttendance($employeeId, $date, $workStartTime, $workEndTime)
    {
        $logs = BiometricLog::where(
            'employee_id',
            $employeeId
        )
            ->whereDate(
                'scan_time',
                $date
            )
            ->orderBy(
                'scan_time',
                'asc'
            )
            ->get();

        if ($logs->isEmpty()) {
            return;
        }

        $scheduledStartTime = Carbon::parse(
            $date . ' ' . $workStartTime
        );

        $scheduledEndTime = Carbon::parse(
            $date . ' ' . $workEndTime
        );

        $timeIn = Carbon::parse(
            $logs->first()->scan_time
        );

        $timeOut = $logs->count() >= 2
            ? Carbon::parse(
                $logs->last()->scan_time
            )
            : null;

        $lunchOut = null;
        $lunchIn = null;

        if ($logs->count() >= 4) {

            $lunchOut = Carbon::parse(
                $logs[1]->scan_time
            );

            $lunchIn = Carbon::parse(
                $logs[2]->scan_time
            );
        }

        /*
     * Determine status
     */

        $status = 'Present';

        if (
            $timeIn->greaterThan(
                $scheduledStartTime
            )
        ) {
            $status = 'Late';
        }

        /*
     * Calculate total worked minutes
     */

        $workedMinutes = 0;

        if ($timeOut) {

            $totalMinutes = $timeIn
                ->diffInMinutes($timeOut);

            $lunchMinutes = 0;

            if ($lunchOut && $lunchIn) {

                $lunchMinutes = $lunchOut
                    ->diffInMinutes($lunchIn);
            }

            $workedMinutes =
                $totalMinutes - $lunchMinutes;
        }

        $hoursWorked = round(
            $workedMinutes / 60,
            2
        );

        /*
     * Calculate overtime
     */

        $overtimeHours = 0;

        if (
            $timeOut &&
            $timeOut->greaterThan(
                $scheduledEndTime
            )
        ) {

            $overtimeHours = round(
                $scheduledEndTime
                    ->diffInMinutes($timeOut)
                    / 60,
                2
            );
        }

        Attendance::updateOrCreate(
            [
                'employee_id' => $employeeId,
                'date' => $date,
            ],
            [
                'time_in' => $timeIn->format('H:i:s'),

                'time_out' => $timeOut
                    ? $timeOut->format('H:i:s')
                    : null,

                'hours_worked' => $hoursWorked,

                'overtime_hours' => $overtimeHours,

                'status' => $status,

                'remarks' =>
                'Generated from biometric logs',
            ]
        );
    }
}
