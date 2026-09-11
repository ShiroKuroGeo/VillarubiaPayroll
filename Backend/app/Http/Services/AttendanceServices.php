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
                'id' => [
                    'required',
                    'integer',
                    'exists:attendances,id',
                ],

                'date' => [
                    'sometimes',
                    'date',
                ],

                'timeIn' => [
                    'nullable',
                    'date_format:H:i',
                ],

                'timeOut' => [
                    'nullable',
                    'date_format:H:i',
                ],

                'status' => [
                    'sometimes',
                    'in:Present,Leave,Half Day,Absent,Late',
                ],

                'remarks' => [
                    'nullable',
                    'string',
                ],
            ]);
        } catch (\Throwable $th) {

            return response_return(
                $th->getMessage(),
                [],
                422
            );
        }


        try {

            $checkAttendance = Attendance::find(
                $validation['id']
            );


            if (!$checkAttendance) {

                return response_return(
                    'Attendance record was not found. Please try again.',
                    [],
                    409
                );
            }


            if (
                !empty($validation['date']) &&
                $validation['date'] !== $checkAttendance->date
            ) {

                $checkDuplicate = Attendance::where(
                    'employee_id',
                    $checkAttendance->employee_id
                )
                    ->where(
                        'date',
                        $validation['date']
                    )
                    ->where(
                        'id',
                        '!=',
                        $checkAttendance->id
                    )
                    ->exists();


                if ($checkDuplicate) {

                    return response_return(
                        'Attendance for this employee on this date already exists.',
                        [],
                        409
                    );
                }
            }


            $timeIn = array_key_exists(
                'timeIn',
                $validation
            )
                ? $validation['timeIn']
                : $checkAttendance->time_in;

            $timeOut = array_key_exists(
                'timeOut',
                $validation
            )
                ? $validation['timeOut']
                : $checkAttendance->time_out;

            if (
                !empty($timeIn) &&
                !empty($timeOut)
            ) {

                $parsedIn = Carbon::createFromFormat(
                    'H:i',
                    substr($timeIn, 0, 5)
                );


                $parsedOut = Carbon::createFromFormat(
                    'H:i',
                    substr($timeOut, 0, 5)
                );


                if (
                    $parsedOut->lessThanOrEqualTo(
                        $parsedIn
                    )
                ) {

                    return response_return(
                        'Time Out must be later than Time In.',
                        [],
                        422
                    );
                }
            }

            $hoursWorked = 0;

            $overtimeHours = 0;


            if (
                !empty($timeIn) &&
                !empty($timeOut)
            ) {

                $parsedIn = Carbon::createFromFormat(
                    'H:i',
                    substr($timeIn, 0, 5)
                );


                $parsedOut = Carbon::createFromFormat(
                    'H:i',
                    substr($timeOut, 0, 5)
                );


                $totalMinutes =
                    $parsedIn->diffInMinutes(
                        $parsedOut
                    );


                $totalHours =
                    $totalMinutes / 60;

                $hoursWorked = round(
                    min(
                        $totalHours,
                        8
                    ),
                    2
                );

                $overtimeHours = round(
                    max(
                        $totalHours - 8,
                        0
                    ),
                    2
                );
            }

            $lateHours = 0;

            $undertimeHours = 0;

            $workStartTimeSetting = Maintenance::where(
                'name',
                'Work Start Time'
            )->first();

            $workEndTimeSetting = Maintenance::where(
                'name',
                'Work End Time'
            )->first();

            if ($workStartTimeSetting && $workEndTimeSetting) {

                if (!empty($timeIn)) {

                    $scheduledStartTime = Carbon::createFromFormat(
                        'H:i',
                        substr($workStartTimeSetting->value, 0, 5)
                    );

                    $parsedIn = Carbon::createFromFormat(
                        'H:i',
                        substr($timeIn, 0, 5)
                    );

                    if ($parsedIn->greaterThan($scheduledStartTime)) {

                        $lateMinutes =
                            $scheduledStartTime->diffInMinutes(
                                $parsedIn
                            );

                        $lateHours = round(
                            $lateMinutes / 60,
                            2
                        );
                    }
                }

                if (!empty($timeOut)) {

                    $scheduledEndTime = Carbon::createFromFormat(
                        'H:i',
                        substr($workEndTimeSetting->value, 0, 5)
                    );

                    $parsedOut = Carbon::createFromFormat(
                        'H:i',
                        substr($timeOut, 0, 5)
                    );

                    if ($parsedOut->lessThan($scheduledEndTime)) {

                        $undertimeMinutes =
                            $parsedOut->diffInMinutes(
                                $scheduledEndTime
                            );

                        $undertimeHours = round(
                            $undertimeMinutes / 60,
                            2
                        );
                    }
                }
            }

            $status = array_key_exists(
                'status',
                $validation
            )
                ? $validation['status']
                : $checkAttendance->status;

            $updateAttendance = $checkAttendance->update([
                'date' => $validation['date'] ?? $checkAttendance->date,
                'time_in' => $timeIn,
                'time_out' => $timeOut,
                'hours_worked' => $hoursWorked,
                'overtime_hours' => $overtimeHours,
                'late_hours' => $lateHours,
                'undertime_hours' => $undertimeHours,
                'status' => $status,
                'remarks' => array_key_exists('remarks', $validation)
                    ? $validation['remarks'] : $checkAttendance->remarks,
            ]);

            if (!$updateAttendance) {
                return response_return(
                    'Cannot save attendance information at this moment.',
                    [],
                    409
                );
            }

            $checkAttendance->refresh();

            return response_return(
                'Successfully updated attendance.',
                $checkAttendance->toArray(),
                200
            );
        } catch (\Throwable $th) {

            logger()->error(
                'UPDATE ATTENDANCE ERROR',
                [
                    'message' => $th->getMessage(),
                    'line' => $th->getLine(),
                    'file' => $th->getFile(),
                ]
            );


            return response_return(
                'Error occurred in updating attendance.',
                [],
                500
            );
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

        $spreadsheet = IOFactory::load($file->getPathname());

        $worksheet = $spreadsheet->getSheet(2);

        $rows = $worksheet->toArray(null, true, true, false);

        $imported = 0;
        $duplicates = 0;
        $employeesNotFound = [];
        $affectedAttendances = [];

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
                'message' =>
                'Work Start Time or Work End Time setting was not found.'
            ], 422);
        }

        $workStartTime = $workStartTimeSetting->value;
        $workEndTime = $workEndTimeSetting->value;

        DB::beginTransaction();

        try {
            $punches = $this->parseDeliAttendanceReport($rows);

            $groupedPunches = collect($punches)
                ->groupBy(function ($punch) {

                    return $punch['biometric_user_id']
                        . '|'
                        . Carbon::parse(
                            $punch['scan_time']
                        )->format('Y-m-d');
                });

            foreach ($groupedPunches as $key => $dailyPunches) {

                $firstPunch = $dailyPunches->first();

                $biometricUserId = trim(
                    (string) $firstPunch['biometric_user_id']
                );

                $date = Carbon::parse(
                    $firstPunch['scan_time']
                )->format('Y-m-d');

                $employee = Employee::where(
                    'biometric_user_id',
                    $biometricUserId
                )->first();

                if (!$employee) {

                    $employeesNotFound[] = $biometricUserId;

                    continue;
                }

                BiometricLog::where(
                    'employee_id',
                    $employee->id
                )
                    ->whereDate(
                        'scan_time',
                        $date
                    )
                    ->delete();

                foreach ($dailyPunches as $punch) {

                    $scanTime = Carbon::parse(
                        $punch['scan_time']
                    );

                    BiometricLog::create([
                        'employee_id' =>
                        $employee->id,

                        'biometric_user_id' =>
                        $biometricUserId,

                        'scan_time' =>
                        $scanTime,
                    ]);

                    $imported++;
                }

                $affectedAttendances[$employee->id . '_' . $date] = [
                    'employee_id' => $employee->id,
                    'date' => $date,
                ];
            }

            $attendanceDates = collect($punches)
                ->map(function ($punch) {
                    return Carbon::parse(
                        $punch['scan_time']
                    )->format('Y-m-d');
                })
                ->unique()
                ->values();

            $employees = Employee::where(
                'status',
                '!=',
                'Separated/Terminated'
            )->get();

            foreach ($attendanceDates as $attendanceDate) {

                foreach ($employees as $employee) {

                    $this->processAttendance(
                        $employee->id,
                        $attendanceDate,
                        $workStartTime,
                        $workEndTime
                    );
                }
            }

            // foreach ($affectedAttendances as $attendanceData) {
            //     $this->processAttendance(
            //         $attendanceData['employee_id'],
            //         $attendanceData['date'],
            //         $workStartTime,
            //         $workEndTime
            //     );
            // }

            DB::commit();

            return response_return(
                'Biometric logs imported and attendance processed successfully.',
                [
                    'imported' => $imported,
                    'duplicates' => $duplicates,
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
                ],
                200
            );
        } catch (\Throwable $e) {

            DB::rollBack();

            return response_return(
                'Failed to import biometric logs: '
                    . $e->getMessage(),
                [],
                500
            );
        }
    }

    private function parseDeliAttendanceReport(array $rows): array
    {
        $punches = [];

        $reportStartDate = null;

        foreach ($rows as $row) {

            foreach ($row as $cell) {

                if (
                    is_string($cell) &&
                    preg_match(
                        '/Attendance date:(\d{4}-\d{2}-\d{2})/i',
                        trim($cell),
                        $matches
                    )
                ) {

                    $reportStartDate = Carbon::parse(
                        $matches[1]
                    );

                    break 2;
                }
            }
        }

        if (!$reportStartDate) {
            return [];
        }

        $blockSize = 15;

        $totalColumns = 0;

        foreach ($rows as $row) {

            $totalColumns = max(
                $totalColumns,
                count($row)
            );
        }

        $timeCardRow = null;

        foreach ($rows as $rowIndex => $row) {

            foreach ($row as $cell) {

                if (
                    is_string($cell) &&
                    trim($cell) === 'Time Card'
                ) {

                    $timeCardRow = $rowIndex;

                    break 2;
                }
            }
        }

        if ($timeCardRow === null) {
            return [];
        }

        $headerRow = $timeCardRow + 2;

        $dataStartRow = $timeCardRow + 3;

        for (
            $blockStart = 0;
            $blockStart < $totalColumns;
            $blockStart += $blockSize
        ) {

            $blockEnd = $blockStart + $blockSize - 1;

            $biometricUserId = null;

            for (
                $r = 0;
                $r < $dataStartRow;
                $r++
            ) {

                for (
                    $c = $blockStart;
                    $c <= $blockEnd;
                    $c++
                ) {

                    $cell =
                        $rows[$r][$c]
                        ?? null;

                    if (
                        is_string($cell) &&
                        trim($cell) === 'User ID'
                    ) {

                        for (
                            $nextCol = $c + 1;
                            $nextCol <= $blockEnd;
                            $nextCol++
                        ) {

                            $value =
                                $rows[$r][$nextCol]
                                ?? null;

                            if (
                                $value !== null &&
                                trim((string) $value) !== ''
                            ) {

                                $biometricUserId =
                                    trim((string) $value);

                                break 3;
                            }
                        }
                    }
                }
            }

            if (!$biometricUserId) {
                continue;
            }

            $columnTypes = [];

            for (
                $c = $blockStart + 1;
                $c <= $blockEnd;
                $c++
            ) {

                $header =
                    strtolower(
                        trim(
                            (string) (
                                $rows[$headerRow][$c]
                                ?? ''
                            )
                        )
                    );

                if ($header === 'in') {

                    $columnTypes[$c] = 'in';
                } elseif ($header === 'out') {

                    $columnTypes[$c] = 'out';
                }
            }

            logger()->info('COLUMN TYPES DEBUG', [
                'employee_id' => $biometricUserId,
                'block_start' => $blockStart,
                'block_end' => $blockEnd,
                'column_types' => $columnTypes,
            ]);

            for (
                $r = $dataStartRow;
                $r < count($rows);
                $r++
            ) {

                $dayLabel =
                    $rows[$r][$blockStart]
                    ?? null;

                if (
                    empty(trim((string) $dayLabel))
                ) {
                    continue;
                }

                if (
                    !preg_match(
                        '/^(\d{1,2})/',
                        trim((string) $dayLabel),
                        $matches
                    )
                ) {
                    continue;
                }

                $day = (int) $matches[1];


                for (
                    $c = $blockStart + 1;
                    $c <= $blockEnd;
                    $c++
                ) {

                    $type =
                        $columnTypes[$c]
                        ?? null;

                    if (!$type) {
                        continue;
                    }

                    $cell =
                        $rows[$r][$c]
                        ?? null;

                    $time =
                        $this->parseExcelTimeCell(
                            $cell
                        );

                    if (!$time) {
                        continue;
                    }

                    $scanTime = Carbon::create(
                        $reportStartDate->year,
                        $reportStartDate->month,
                        $day,
                        $time['hour'],
                        $time['minute'],
                        0
                    );

                    $punches[] = [

                        'biometric_user_id' =>
                        $biometricUserId,

                        'scan_time' =>
                        $scanTime,

                        'type' =>
                        $type,
                    ];
                }
            }
        }

        $uniquePunches = [];

        foreach ($punches as $punch) {

            $key =
                $punch['biometric_user_id']
                . '_'
                . $punch['scan_time']
                ->format('Y-m-d H:i:s')
                . '_'
                . $punch['type'];

            $uniquePunches[$key] =
                $punch;
        }


        return array_values(
            $uniquePunches
        );
    }

    private function parseExcelTimeCell($cell): ?array
    {
        if (is_string($cell) && preg_match('/^(\d{1,2}):(\d{2})$/', trim($cell), $m)) {
            return ['hour' => (int) $m[1], 'minute' => (int) $m[2]];
        }

        if (is_numeric($cell)) {
            $totalMinutes = (int) round(((float) $cell) * 24 * 60);
            return [
                'hour' => intdiv($totalMinutes, 60) % 24,
                'minute' => $totalMinutes % 60,
            ];
        }

        return null;
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


        /*
|--------------------------------------------------------------------------
| No biometric logs = Absent
|--------------------------------------------------------------------------
*/

        if ($logs->isEmpty()) {

            Attendance::updateOrCreate(
                [
                    'employee_id' => $employeeId,
                    'date' => $date,
                ],
                [
                    'time_in' => null,

                    'time_out' => null,

                    'hours_worked' => 0,

                    'overtime_hours' => 0,

                    'late_hours' => 0,

                    'undertime_hours' => 0,

                    'status' => 'Absent',

                    'remarks' => 'No biometric logs found',
                ]
            );

            return;
        }


        /*
|--------------------------------------------------------------------------
| Get first IN
|--------------------------------------------------------------------------
*/

        $timeInLog = $logs
            ->where('type', 'in')
            ->sortBy('scan_time')
            ->first();


        /*
|--------------------------------------------------------------------------
| Get last OUT
|--------------------------------------------------------------------------
*/

        $timeOutLog = $logs
            ->where('type', 'out')
            ->sortByDesc('scan_time')
            ->first();


        /*
|--------------------------------------------------------------------------
| Fallback for old logs without type
|--------------------------------------------------------------------------
*/

        $hasTypedLogs =
            $timeInLog !== null ||
            $timeOutLog !== null;


        if (!$hasTypedLogs) {

            $timeInLog = $logs->first();

            $timeOutLog =
                $logs->count() >= 2
                ? $logs->last()
                : null;
        }


        $timeIn = $timeInLog
            ? Carbon::parse(
                $timeInLog->scan_time
            )
            : null;


        $timeOut = $timeOutLog
            ? Carbon::parse(
                $timeOutLog->scan_time
            )
            : null;


        $scheduledStartTime = Carbon::parse(
            $date . ' ' . $workStartTime
        );


        $scheduledEndTime = Carbon::parse(
            $date . ' ' . $workEndTime
        );


        $status = 'Present';

        $remarks = 'Generated from biometric logs';


        /*
|--------------------------------------------------------------------------
| Missing punches
|--------------------------------------------------------------------------
*/

        if ($timeIn && !$timeOut) {

            $remarks =
                'Generated from biometric logs - Missing Time Out';
        } elseif (!$timeIn && $timeOut) {

            $remarks =
                'Generated from biometric logs - Missing Time In';
        }


        /*
|--------------------------------------------------------------------------
| Late
|--------------------------------------------------------------------------
*/

        $lateHours = 0;

        if (
            $timeIn &&
            $timeIn->greaterThan(
                $scheduledStartTime
            )
        ) {

            $status = 'Late';

            $lateMinutes =
                $scheduledStartTime->diffInMinutes(
                    $timeIn
                );

            $lateHours = round(
                $lateMinutes / 60,
                2
            );

            $remarks .=
                ' - Late by ' .
                $lateMinutes .
                ' minute(s)';
        }


        /*
|--------------------------------------------------------------------------
| Worked hours
|--------------------------------------------------------------------------
*/

        $workedMinutes = 0;

        if ($timeIn && $timeOut) {

            $workedMinutes =
                $timeIn->diffInMinutes(
                    $timeOut
                );
        }


        $hoursWorked = round(
            $workedMinutes / 60,
            2
        );


        /*
|--------------------------------------------------------------------------
| Expected working time
|--------------------------------------------------------------------------
*/

        $expectedMinutes =
            $scheduledStartTime->diffInMinutes(
                $scheduledEndTime
            );


        /*
|--------------------------------------------------------------------------
| Half Day
|--------------------------------------------------------------------------
*/

        if (
            $timeIn &&
            $timeOut &&
            $workedMinutes > 0 &&
            $workedMinutes <= (
                $expectedMinutes / 2
            )
        ) {

            $status = 'Half Day';

            $remarks =
                'Generated from biometric logs - Half Day';
        }

        $undertimeHours = 0;

        if (
            $timeIn &&
            $timeOut &&
            $timeOut->lessThan(
                $scheduledEndTime
            ) &&
            $status !== 'Half Day'
        ) {

            $undertimeMinutes =
                $timeOut->diffInMinutes(
                    $scheduledEndTime
                );

            $undertimeHours = round(
                $undertimeMinutes / 60,
                2
            );

            $remarks .=
                ' - Early Leave';
        }


        $overtimeHours = 0;

        if (
            $timeOut &&
            $timeOut->greaterThan(
                $scheduledEndTime
            )
        ) {

            $overtimeHours = round(
                $scheduledEndTime
                    ->diffInMinutes(
                        $timeOut
                    ) / 60,
                2
            );

            $remarks .=
                ' - Overtime: ' .
                $overtimeHours .
                ' hour(s)';
        }

        Attendance::updateOrCreate(
            [
                'employee_id' => $employeeId,
                'date' => $date,
            ],
            [
                'time_in' => $timeIn
                    ? $timeIn->format('H:i:s')
                    : null,

                'time_out' => $timeOut
                    ? $timeOut->format('H:i:s')
                    : null,

                'hours_worked' => $hoursWorked,

                'overtime_hours' => $overtimeHours,

                'late_hours' => $lateHours,

                'undertime_hours' => $undertimeHours,

                'status' => $status,

                'remarks' => $remarks,
            ]
        );
    }
}
