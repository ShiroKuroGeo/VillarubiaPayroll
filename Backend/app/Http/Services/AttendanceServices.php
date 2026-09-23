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
                'time_in' => $segments['before_noon_in'],
                'time_out' => $segments['after_noon_out'],
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
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
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

        $subHeaderRow = $timeCardRow + 2;
        $dataStartRow = $timeCardRow + 3;

        foreach ($blockStarts as $index => $blockStart) {

            $blockEnd = isset($blockStarts[$index + 1])
                ? $blockStarts[$index + 1] - 1
                : $totalColumns - 1;

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

            for ($r = $dataStartRow; $r < count($rows); $r++) {

                $dayLabel = $rows[$r][$blockStart] ?? null;

                if (empty(trim((string) $dayLabel))) {
                    continue;
                }

                if (!preg_match('/^(\d{1,2})/', trim((string) $dayLabel), $dayMatch)) {
                    continue;
                }

                $day = (int) $dayMatch[1];

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

    private function applyAttendanceRecord(int $employeeId, array $record, string $workStartTime, string $workEndTime): void
    {
        $date = $record['date'];
        $bnIn = $record['before_noon_in'];
        $bnOut = $record['before_noon_out'];
        $anIn = $record['after_noon_in'];
        $anOut = $record['after_noon_out'];
        $otIn = $record['overtime_in'];
        $otOut = $record['overtime_out'];

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

        $scheduledEnd = Carbon::parse($date . ' ' . $workEndTime);

        $scheduledEndNoLunch = $scheduledEnd->copy()->subHour();

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
}
