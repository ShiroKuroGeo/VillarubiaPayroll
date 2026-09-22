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

    private function computeAttendanceMetrics(
        ?Carbon $bnIn,
        ?Carbon $bnOut,
        ?Carbon $anIn,
        ?Carbon $anOut,
        ?Carbon $otIn,
        ?Carbon $otOut,
        Carbon $scheduledStart,
        Carbon $scheduledEnd,
        Carbon $scheduledEndNoLunch
    ): array {
        if (!$bnIn && !$anIn) {
            return [
                'time_in' => null,
                'time_out' => null,
                'hours_worked' => 0,
                'overtime_hours' => 0,
                'status' => 'Absent',
                'remarks' => 'No biometric logs found',
            ];
        }

        $timeIn = $bnIn ?? $anIn;
        $timeOut = $otOut ?? $anOut ?? $bnOut;

        $remarks = [];
        $status = 'Present';

        if ($bnIn && $bnOut && !$anIn && !$anOut) {
            $status = 'Half Day';
            $remarks[] = 'Only morning session recorded';
        } else {
            if (!$bnOut) {
                $remarks[] = 'Missing before-noon time-out';
            }

            if ($bnIn && !$anIn) {
                $remarks[] = 'Missing after-noon time-in';
            }

            if (!$anOut && !$otOut) {
                $remarks[] = 'Missing time-out';
            }
        }

        if ($status === 'Present' && $timeIn && $timeIn->gt($scheduledStart)) {
            $status = 'Late';
            $remarks[] = 'Late by ' . $scheduledStart->diffInMinutes($timeIn) . ' minute(s)';
        }

        $hoursWorked = 0;
        $overtimeHours = 0;

        if ($timeIn && $timeOut && $timeOut->gt($timeIn)) {

            $totalMinutes = $timeIn->diffInMinutes($timeOut);

            $hasLunchBreak = $bnOut && $anIn && $anIn->gt($bnOut);

            if ($hasLunchBreak) {
                $totalMinutes -= $bnOut->diffInMinutes($anIn);
                $remarks[] = 'Straight time';
            } else {
                $remarks[] = 'Normal time';
            }

            $totalMinutes = max($totalMinutes, 0);
            $hoursWorked = round($totalMinutes / 60, 2);

            if ($status !== 'Half Day') {

                $standardMinutes = 8 * 60;

                if ($totalMinutes < $standardMinutes) {
                    $shortHours = round(($standardMinutes - $totalMinutes) / 60, 2);
                    $remarks[] = 'Undertime by ' . $shortHours . ' hour(s)';
                }

                $overtimeCutoff = $hasLunchBreak ? $scheduledEnd : $scheduledEndNoLunch;

                if ($timeOut->gt($overtimeCutoff)) {
                    $overtimeHours = intdiv($overtimeCutoff->diffInMinutes($timeOut), 60);

                    if ($overtimeHours > 0) {
                        $remarks[] = $overtimeHours . ' hour(s) overtime';
                    }
                }
            }
        }
        return [
            'time_in' => $timeIn?->format('H:i:s'),
            'time_out' => $timeOut?->format('H:i:s'),
            'hours_worked' => $hoursWorked,
            'overtime_hours' => $overtimeHours,
            'status' => $status,
            'remarks' => $remarks ? implode(' - ', $remarks) : 'Generated from biometric logs',
        ];
    }

    public function updateAttendance(Request $request)
    {
        try {
            $validation = $request->validate([
                'id' => ['required', 'integer', 'exists:attendances,id'],
                'date' => ['sometimes', 'date'],

                'before_noon_in' => ['nullable', 'date_format:H:i,H:i:s'],
                'before_noon_out' => ['nullable', 'date_format:H:i,H:i:s'],
                'after_noon_in' => ['nullable', 'date_format:H:i,H:i:s'],
                'after_noon_out' => ['nullable', 'date_format:H:i,H:i:s'],
                'overtime_in' => ['nullable', 'date_format:H:i,H:i:s'],
                'overtime_out' => ['nullable', 'date_format:H:i,H:i:s'],

                'status' => ['sometimes', 'in:Present,Leave,Half Day,Absent,Late'],
                'remarks' => ['nullable', 'string'],
            ]);
        } catch (\Throwable $th) {
            return response_return($th->getMessage(), [], 422);
        }

        try {
            $checkAttendance = Attendance::find($validation['id']);

            if (!$checkAttendance) {
                return response_return('Attendance record was not found. Please try again.', [], 409);
            }

            $date = $validation['date'] ?? $checkAttendance->date;

            if (!empty($validation['date']) && $validation['date'] !== $checkAttendance->date) {

                $checkDuplicate = Attendance::where('employee_id', $checkAttendance->employee_id)
                    ->where('date', $validation['date'])
                    ->where('id', '!=', $checkAttendance->id)
                    ->exists();

                if ($checkDuplicate) {
                    return response_return('Attendance for this employee on this date already exists.', [], 409);
                }
            }

            $segments = [
                'before_noon_in' => $checkAttendance->before_noon_in,
                'before_noon_out' => $checkAttendance->before_noon_out,
                'after_noon_in' => $checkAttendance->after_noon_in,
                'after_noon_out' => $checkAttendance->after_noon_out,
                'overtime_in' => $checkAttendance->overtime_in,
                'overtime_out' => $checkAttendance->overtime_out,
            ];

            foreach (array_keys($segments) as $key) {
                if (array_key_exists($key, $validation)) {
                    $segments[$key] = $validation[$key];
                }
            }

            $toCarbon = fn(?string $value) => $value
                ? Carbon::parse($date . ' ' . substr($value, 0, 5))
                : null;

            $bnIn = $toCarbon($segments['before_noon_in']);
            $bnOut = $toCarbon($segments['before_noon_out']);
            $anIn = $toCarbon($segments['after_noon_in']);
            $anOut = $toCarbon($segments['after_noon_out']);
            $otIn = $toCarbon($segments['overtime_in']);
            $otOut = $toCarbon($segments['overtime_out']);

            if ($bnIn && $bnOut && $bnOut->lessThanOrEqualTo($bnIn)) {
                return response_return('Before-noon Time Out must be later than Time In.', [], 422);
            }

            if ($anIn && $anOut && $anOut->lessThanOrEqualTo($anIn)) {
                return response_return('After-noon Time Out must be later than Time In.', [], 422);
            }

            if ($otIn && $otOut && $otOut->lessThanOrEqualTo($otIn)) {
                return response_return('Overtime Time Out must be later than Time In.', [], 422);
            }

            $workStartTimeSetting = Maintenance::where('name', 'Work Start Time')->first();
            $workEndTimeSetting = Maintenance::where('name', 'Work End Time')->first();

            if (!$workStartTimeSetting || !$workEndTimeSetting) {
                return response_return('Work Start Time or Work End Time setting was not found.', [], 422);
            }

            $scheduledStart = Carbon::parse($date . ' ' . $workStartTimeSetting->value);
            $scheduledEnd = Carbon::parse($date . ' ' . $workEndTimeSetting->value);
            $scheduledEndNoLunch = $scheduledEnd->copy()->subHour();

            $metrics = $this->computeAttendanceMetrics(
                $bnIn,
                $bnOut,
                $anIn,
                $anOut,
                $otIn,
                $otOut,
                $scheduledStart,
                $scheduledEnd,
                $scheduledEndNoLunch
            );

            // A status the admin picks by hand overrides the computed one.
            // Absent/Leave also clears the derived numbers so they don't
            // contradict a status that says the person didn't work that day.
            if (array_key_exists('status', $validation) && $validation['status'] !== $metrics['status']) {
                $metrics['status'] = $validation['status'];

                if (in_array($validation['status'], ['Absent', 'Leave'], true)) {
                    $metrics['hours_worked'] = 0;
                    $metrics['overtime_hours'] = 0;
                    $metrics['time_in'] = null;
                    $metrics['time_out'] = null;
                }
            }

            $updateAttendance = $checkAttendance->update([
                'date' => $date,

                'time_in' => $metrics['time_in'],
                'time_out' => $metrics['time_out'],

                'before_noon_in' => $segments['before_noon_in'],
                'before_noon_out' => $segments['before_noon_out'],
                'after_noon_in' => $segments['after_noon_in'],
                'after_noon_out' => $segments['after_noon_out'],
                'overtime_in' => $segments['overtime_in'],
                'overtime_out' => $segments['overtime_out'],

                'hours_worked' => $metrics['hours_worked'],
                'overtime_hours' => $metrics['overtime_hours'],
                'status' => $metrics['status'],
                'remarks' => array_key_exists('remarks', $validation)
                    ? $validation['remarks']
                    : $metrics['remarks'],
            ]);

            if (!$updateAttendance) {
                return response_return('Cannot save attendance information at this moment.', [], 409);
            }

            $checkAttendance->refresh();

            return response_return('Successfully updated attendance.', $checkAttendance->toArray(), 200);
        } catch (\Throwable $th) {

            logger()->error('UPDATE ATTENDANCE ERROR', [
                'message' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
            ]);

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
                    "amTimeIn" => $attendance->before_noon_in,
                    "amTimeOut" => $attendance->before_noon_out,
                    "pmTimeIn" => $attendance->after_noon_in,
                    "pmTimeOut" => $attendance->after_noon_out,
                    "overIn" => $attendance->overtime_in,
                    "overOut" => $attendance->overtime_out,
                    "hoursWorked" => $attendance->hours_worked,
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

        $allSheets = $spreadsheet->getAllSheets();

        $dataSheets = array_slice($allSheets, 2);

        $dailyRecords = [];

        foreach ($dataSheets as $sheet) {

            $rows = $sheet->toArray(null, true, true, false);

            $sheetRecords = $this->parseNoonAttendanceReport($rows);

            if (!empty($sheetRecords)) {
                $dailyRecords = array_merge($dailyRecords, $sheetRecords);
            }
        }

        if (empty($dailyRecords)) {
            return response()->json([
                'message' => 'No recognizable attendance data was found in the uploaded file.',
            ], 422);
        }

        $workStartTimeSetting = Maintenance::where('name', 'Work Start Time')->first();
        $workEndTimeSetting = Maintenance::where('name', 'Work End Time')->first();

        if (!$workStartTimeSetting || !$workEndTimeSetting) {
            return response()->json([
                'message' => 'Work Start Time or Work End Time setting was not found.',
            ], 422);
        }

        $workStartTime = $workStartTimeSetting->value;
        $workEndTime = $workEndTimeSetting->value;

        $imported = 0;
        $employeesNotFound = [];

        DB::beginTransaction();

        try {
            foreach ($dailyRecords as $record) {

                $biometricUserId = trim((string) $record['biometric_user_id']);

                $employee = Employee::where('biometric_user_id', $biometricUserId)->first();

                if (!$employee) {
                    $employeesNotFound[] = $biometricUserId;
                    continue;
                }

                $this->applyAttendanceRecord($employee->id, $record, $workStartTime, $workEndTime);

                $imported++;
            }

            DB::commit();

            return response_return(
                'Attendance imported successfully.',
                [
                    'days_imported' => $imported,
                    'employees_not_found' => array_values(array_unique($employeesNotFound)),
                ],
                200
            );
        } catch (\Throwable $e) {

            DB::rollBack();

            return response_return(
                'Failed to import attendance: ' . $e->getMessage(),
                [],
                500
            );
        }
    }

    private function parseNoonAttendanceReport(array $rows): array
    {
        $records = [];

        $rangeStart = null;
        $rangeEnd = null;

        foreach ($rows as $row) {
            foreach ($row as $cell) {
                if (
                    is_string($cell) &&
                    preg_match(
                        '/Attendance date:\s*(\d{4}-\d{2}-\d{2})\s*~\s*(\d{4}-\d{2}-\d{2})/i',
                        trim($cell),
                        $matches
                    )
                ) {
                    $rangeStart = Carbon::parse($matches[1]);
                    $rangeEnd = Carbon::parse($matches[2]);
                    break 2;
                }
            }
        }

        if (!$rangeStart || !$rangeEnd) {
            return [];
        }

        // 2. Find the row holding "Time Card" - one occurrence per employee block.
        $timeCardRow = null;
        $blockStarts = [];

        foreach ($rows as $rowIndex => $row) {
            foreach ($row as $colIndex => $cell) {
                if (is_string($cell) && trim($cell) === 'Time Card') {
                    $timeCardRow = $rowIndex;
                    $blockStarts[] = $colIndex;
                }
            }

            if ($timeCardRow !== null) {
                break;
            }
        }

        if ($timeCardRow === null || empty($blockStarts)) {
            return [];
        }

        sort($blockStarts);

        $totalColumns = 0;

        foreach ($rows as $row) {
            $totalColumns = max($totalColumns, count($row));
        }

        $subHeaderRow = $timeCardRow + 2; // row containing "In" / "Out" labels
        $dataStartRow = $timeCardRow + 3; // first row of actual day data

        foreach ($blockStarts as $index => $blockStart) {

            $blockEnd = isset($blockStarts[$index + 1])
                ? $blockStarts[$index + 1] - 1
                : $totalColumns - 1;

            // --- locate the User ID for this block ---
            $biometricUserId = null;

            for ($r = 0; $r < $timeCardRow && $biometricUserId === null; $r++) {
                for ($c = $blockStart; $c <= $blockEnd; $c++) {

                    $cell = $rows[$r][$c] ?? null;

                    if (is_string($cell) && trim($cell) === 'User ID') {

                        for ($nextCol = $c + 1; $nextCol <= $blockEnd; $nextCol++) {

                            $value = $rows[$r][$nextCol] ?? null;

                            if ($value !== null && trim((string) $value) !== '') {
                                $biometricUserId = trim((string) $value);
                                break;
                            }
                        }

                        break;
                    }
                }
            }

            if (!$biometricUserId) {
                continue;
            }

            // --- map this block's columns to before/after-noon/overtime in & out ---
            $segmentNames = ['before_noon', 'after_noon', 'overtime'];
            $columnSegments = [];
            $pairIndex = -1;
            $expectingOut = false;

            for ($c = $blockStart + 1; $c <= $blockEnd; $c++) {

                $header = strtolower(trim((string) ($rows[$subHeaderRow][$c] ?? '')));

                if ($header === 'in') {
                    $pairIndex++;
                    $expectingOut = true;
                    $columnSegments[$c] = ($segmentNames[$pairIndex] ?? ('extra_' . $pairIndex)) . '_in';
                } elseif ($header === 'out' && $expectingOut) {
                    $columnSegments[$c] = ($segmentNames[$pairIndex] ?? ('extra_' . $pairIndex)) . '_out';
                    $expectingOut = false;
                }
            }

            if (empty($columnSegments)) {
                continue;
            }

            // --- walk the daily rows for this block ---
            for ($r = $dataStartRow; $r < count($rows); $r++) {

                $dayLabel = $rows[$r][$blockStart] ?? null;

                if (empty(trim((string) $dayLabel))) {
                    continue;
                }

                if (!preg_match('/^(\d{1,2})/', trim((string) $dayLabel), $dayMatch)) {
                    continue;
                }

                $day = (int) $dayMatch[1];

                // Handle a report that spans a month boundary: try the start
                // month first, and fall back to the end month if the resulting
                // date falls outside the reported range.
                $date = $this->resolveDayInRange($day, $rangeStart, $rangeEnd);

                $dateKey = $date->format('Y-m-d');
                $recordKey = $biometricUserId . '|' . $dateKey;

                if (!isset($records[$recordKey])) {
                    $records[$recordKey] = [
                        'biometric_user_id' => $biometricUserId,
                        'date' => $dateKey,
                        'before_noon_in' => null,
                        'before_noon_out' => null,
                        'after_noon_in' => null,
                        'after_noon_out' => null,
                        'overtime_in' => null,
                        'overtime_out' => null,
                    ];
                }

                foreach ($columnSegments as $col => $segment) {

                    $cell = $rows[$r][$col] ?? null;

                    $time = $this->parseExcelTimeCell($cell);

                    if (!$time) {
                        continue;
                    }

                    $records[$recordKey][$segment] = Carbon::create(
                        $date->year,
                        $date->month,
                        $date->day,
                        $time['hour'],
                        $time['minute'],
                        0
                    );
                }
            }
        }

        return array_values($records);
    }

    private function applyAttendanceRecord(
        int $employeeId,
        array $record,
        string $workStartTime,
        string $workEndTime
    ): void {
        $date = $record['date'];

        $bnIn = $record['before_noon_in'];
        $bnOut = $record['before_noon_out'];
        $anIn = $record['after_noon_in'];
        $anOut = $record['after_noon_out'];
        $otIn = $record['overtime_in'];
        $otOut = $record['overtime_out'];

        // No check-in at all for the day.
        if (!$bnIn && !$anIn) {
            Attendance::updateOrCreate(
                ['employee_id' => $employeeId, 'date' => $date],
                [
                    'time_in' => null,
                    'time_out' => null,
                    'before_noon_in' => null,
                    'before_noon_out' => null,
                    'after_noon_in' => null,
                    'after_noon_out' => null,
                    'overtime_in' => null,
                    'overtime_out' => null,
                    'hours_worked' => 0,
                    'overtime_hours' => 0,
                    'status' => 'Absent',
                    'remarks' => 'No biometric logs found',
                ]
            );

            return;
        }

        $scheduledStart = Carbon::parse($date . ' ' . $workStartTime);

        // "With lunch" stop time, exactly as configured (e.g. 5:00 PM).
        $scheduledEnd = Carbon::parse($date . ' ' . $workEndTime);

        // "No lunch" stop time - one hour earlier (e.g. 4:00 PM), since the
        // standard schedule assumes a 1-hour lunch is normally part of the span.
        $scheduledEndNoLunch = $scheduledEnd->copy()->subHour();

        $timeIn = $bnIn ?? $anIn;
        $timeOut = $otOut ?? $anOut ?? $bnOut;

        $remarks = [];
        $status = 'Present';

        if ($bnIn && $bnOut && !$anIn && !$anOut) {
            // Morning session complete, no afternoon session recorded at all.
            $status = 'Half Day';
            $remarks[] = 'Only morning session recorded';
        } else {
            if (!$bnOut) {
                $remarks[] = 'Missing before-noon time-out';
            }

            if ($bnIn && !$anIn) {
                $remarks[] = 'Missing after-noon time-in';
            }

            if (!$anOut && !$otOut) {
                $remarks[] = 'Missing time-out';
            }
        }

        // Lateness, based on whichever "in" punch happened first.
        if ($status === 'Present' && $timeIn && $timeIn->gt($scheduledStart)) {
            $status = 'Late';
            $remarks[] = 'Late by ' . $scheduledStart->diffInMinutes($timeIn) . ' minute(s)';
        }

        $hoursWorked = 0;
        $overtimeHours = 0;

        if ($timeIn && $timeOut && $timeOut->gt($timeIn)) {

            $totalMinutes = $timeIn->diffInMinutes($timeOut);

            $hasLunchBreak = $bnOut && $anIn && $anIn->gt($bnOut);

            if ($hasLunchBreak) {
                // Split shift: subtract the actual recorded lunch gap.
                $totalMinutes -= $bnOut->diffInMinutes($anIn);
                $remarks[] = 'Straight time';
            } else {
                // Continuous shift: no lunch punched, count the full span.
                $remarks[] = 'Normal time';
            }

            $totalMinutes = max($totalMinutes, 0);
            $hoursWorked = round($totalMinutes / 60, 2);

            if ($status !== 'Half Day') {

                // Undertime: still based on hours actually worked vs 8 hours.
                $standardMinutes = 8 * 60;

                if ($totalMinutes < $standardMinutes) {
                    $shortHours = round(($standardMinutes - $totalMinutes) / 60, 2);
                    $remarks[] = 'Undertime by ' . $shortHours . ' hour(s)';
                }

                // Overtime: based on the actual clock-out time versus the
                // fixed schedule cutoff - not on hours worked, and not on
                // what time this person happened to clock in.
                $overtimeCutoff = $hasLunchBreak ? $scheduledEnd : $scheduledEndNoLunch;

                if ($timeOut->gt($overtimeCutoff)) {
                    $overtimeHours = intdiv($overtimeCutoff->diffInMinutes($timeOut), 60);

                    if ($overtimeHours > 0) {
                        $remarks[] = $overtimeHours . ' hour(s) overtime';
                    }
                }
            }
        }

        Attendance::updateOrCreate(
            ['employee_id' => $employeeId, 'date' => $date],
            [
                'time_in' => $timeIn?->format('H:i:s'),
                'time_out' => $timeOut?->format('H:i:s'),

                'before_noon_in' => $bnIn?->format('H:i:s'),
                'before_noon_out' => $bnOut?->format('H:i:s'),
                'after_noon_in' => $anIn?->format('H:i:s'),
                'after_noon_out' => $anOut?->format('H:i:s'),
                'overtime_in' => $otIn?->format('H:i:s'),
                'overtime_out' => $otOut?->format('H:i:s'),

                'hours_worked' => $hoursWorked,
                'overtime_hours' => $overtimeHours,
                'status' => $status,
                'remarks' => $remarks ? implode(' - ', $remarks) : 'Generated from biometric logs',
            ]
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

    private function resolveDayInRange(int $day, Carbon $rangeStart, Carbon $rangeEnd): Carbon
    {
        $startMonthDate = Carbon::create($rangeStart->year, $rangeStart->month, 1)
            ->day(min($day, Carbon::create($rangeStart->year, $rangeStart->month, 1)->daysInMonth));

        if ($startMonthDate->between($rangeStart, $rangeEnd)) {
            return $startMonthDate;
        }

        return Carbon::create($rangeEnd->year, $rangeEnd->month, 1)
            ->day(min($day, Carbon::create($rangeEnd->year, $rangeEnd->month, 1)->daysInMonth));
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

    // private function parseExcelTimeCell($cell): ?array
    // {
    //     if (is_string($cell) && preg_match('/^(\d{1,2}):(\d{2})$/', trim($cell), $m)) {
    //         return ['hour' => (int) $m[1], 'minute' => (int) $m[2]];
    //     }

    //     if (is_numeric($cell)) {
    //         $totalMinutes = (int) round(((float) $cell) * 24 * 60);
    //         return [
    //             'hour' => intdiv($totalMinutes, 60) % 24,
    //             'minute' => $totalMinutes % 60,
    //         ];
    //     }

    //     return null;
    // }

    //     private function processAttendance($employeeId, $date, $workStartTime, $workEndTime)
    //     {

    //         $logs = BiometricLog::where(
    //             'employee_id',
    //             $employeeId
    //         )
    //             ->whereDate(
    //                 'scan_time',
    //                 $date
    //             )
    //             ->orderBy(
    //                 'scan_time',
    //                 'asc'
    //             )
    //             ->get();


    //         /*
    // |--------------------------------------------------------------------------
    // | No biometric logs = Absent
    // |--------------------------------------------------------------------------
    // */

    //         if ($logs->isEmpty()) {

    //             Attendance::updateOrCreate(
    //                 [
    //                     'employee_id' => $employeeId,
    //                     'date' => $date,
    //                 ],
    //                 [
    //                     'time_in' => null,

    //                     'time_out' => null,

    //                     'hours_worked' => 0,

    //                     'overtime_hours' => 0,

    //                     'late_hours' => 0,

    //                     'undertime_hours' => 0,

    //                     'status' => 'Absent',

    //                     'remarks' => 'No biometric logs found',
    //                 ]
    //             );

    //             return;
    //         }


    //         /*
    // |--------------------------------------------------------------------------
    // | Get first IN
    // |--------------------------------------------------------------------------
    // */

    //         $timeInLog = $logs
    //             ->where('type', 'in')
    //             ->sortBy('scan_time')
    //             ->first();


    //         /*
    // |--------------------------------------------------------------------------
    // | Get last OUT
    // |--------------------------------------------------------------------------
    // */

    //         $timeOutLog = $logs
    //             ->where('type', 'out')
    //             ->sortByDesc('scan_time')
    //             ->first();


    //         /*
    // |--------------------------------------------------------------------------
    // | Fallback for old logs without type
    // |--------------------------------------------------------------------------
    // */

    //         $hasTypedLogs =
    //             $timeInLog !== null ||
    //             $timeOutLog !== null;


    //         if (!$hasTypedLogs) {

    //             $timeInLog = $logs->first();

    //             $timeOutLog =
    //                 $logs->count() >= 2
    //                 ? $logs->last()
    //                 : null;
    //         }


    //         $timeIn = $timeInLog
    //             ? Carbon::parse(
    //                 $timeInLog->scan_time
    //             )
    //             : null;


    //         $timeOut = $timeOutLog
    //             ? Carbon::parse(
    //                 $timeOutLog->scan_time
    //             )
    //             : null;


    //         $scheduledStartTime = Carbon::parse(
    //             $date . ' ' . $workStartTime
    //         );


    //         $scheduledEndTime = Carbon::parse(
    //             $date . ' ' . $workEndTime
    //         );


    //         $status = 'Present';

    //         $remarks = 'Generated from biometric logs';

    //         if ($timeIn && !$timeOut) {

    //             $remarks =
    //                 'Generated from biometric logs - Missing Time Out';
    //         } elseif (!$timeIn && $timeOut) {

    //             $remarks =
    //                 'Generated from biometric logs - Missing Time In';
    //         }

    //         $lateHours = 0;

    //         if (
    //             $timeIn &&
    //             $timeIn->greaterThan(
    //                 $scheduledStartTime
    //             )
    //         ) {

    //             $status = 'Late';

    //             $lateMinutes =
    //                 $scheduledStartTime->diffInMinutes(
    //                     $timeIn
    //                 );

    //             $lateHours = round(
    //                 $lateMinutes / 60,
    //                 2
    //             );

    //             $remarks .=
    //                 ' - Late by ' .
    //                 $lateMinutes .
    //                 ' minute(s)';
    //         }

    //         $workedMinutes = 0;

    //         if ($timeIn && $timeOut) {
    //             $workedMinutes = $timeIn->diffInMinutes($timeOut);
    //         }

    //         $hoursWorked = round($workedMinutes / 60, 2);

    //         $halfDayThresholdMinutes = 6 * 60;

    //         if (
    //             $timeIn && $timeOut && $workedMinutes > 0 && $workedMinutes < $halfDayThresholdMinutes
    //         ) {
    //             $status = 'Half Day';
    //             $remarks = 'Generated from biometric logs - Half Day';
    //         }

    //         $undertimeHours = 0;

    //         if (
    //             $timeIn &&
    //             $timeOut &&
    //             $timeOut->lessThan(
    //                 $scheduledEndTime
    //             ) &&
    //             $status !== 'Half Day'
    //         ) {

    //             $undertimeMinutes =
    //                 $timeOut->diffInMinutes(
    //                     $scheduledEndTime
    //                 );

    //             $undertimeHours = round(
    //                 $undertimeMinutes / 60,
    //                 2
    //             );

    //             $remarks .=
    //                 ' - Early Leave';
    //         }

    //         $overtimeHours = 0;

    //         if (
    //             $timeOut &&
    //             $timeOut->greaterThan(
    //                 $scheduledEndTime
    //             )
    //         ) {

    //             $overtimeHours = round(
    //                 $scheduledEndTime
    //                     ->diffInMinutes(
    //                         $timeOut
    //                     ) / 60,
    //                 2
    //             );

    //             $remarks .=
    //                 ' - Overtime: ' .
    //                 $overtimeHours .
    //                 ' hour(s)';
    //         }

    //         Attendance::updateOrCreate(
    //             [
    //                 'employee_id' => $employeeId,
    //                 'date' => $date,
    //             ],
    //             [
    //                 'time_in' => $timeIn
    //                     ? $timeIn->format('H:i:s')
    //                     : null,

    //                 'time_out' => $timeOut
    //                     ? $timeOut->format('H:i:s')
    //                     : null,

    //                 'hours_worked' => $hoursWorked,

    //                 'overtime_hours' => $overtimeHours,

    //                 'late_hours' => $lateHours,

    //                 'undertime_hours' => $undertimeHours,

    //                 'status' => $status,

    //                 'remarks' => $remarks,
    //             ]
    //         );
    //     }
}
