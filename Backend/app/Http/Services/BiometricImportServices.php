<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BiometricAttendanceImportService
{
    protected int $firstDataSheetIndex = 2;

    protected int $blockWidth = 7;

    public function import(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheetCount = $spreadsheet->getSheetCount();

        $result = [
            'imported' => 0,
            'updated' => 0,
            'skipped_employees' => [],
            'errors' => [],
        ];

        for ($i = $this->firstDataSheetIndex; $i < $sheetCount; $i++) {
            $sheet = $spreadsheet->getSheet($i);

            try {
                $blocks = $this->locateEmployeeBlocks($sheet);

                if (empty($blocks)) {
                    $result['errors'][] = "No employee blocks found on sheet '{$sheet->getTitle()}'.";
                    continue;
                }

                foreach ($blocks as $block) {
                    $this->importBlock($sheet, $block, $result);
                }
            } catch (\Throwable $e) {
                Log::error('Attendance import: sheet failed', [
                    'sheet' => $sheet->getTitle(),
                    'message' => $e->getMessage(),
                    'line' => $e->getLine(),
                ]);
                $result['errors'][] = "Sheet '{$sheet->getTitle()}': {$e->getMessage()}";
            }
        }

        return $result;
    }

    /**
     * Scan the header rows for "User ID" labels; each marks one employee
     * block. Returns [['start' => colIndex, 'end' => colIndex], ...].
     */
    protected function locateEmployeeBlocks(Worksheet $sheet): array
    {
        $headerScanRows = 6;
        $highestColIndex = Coordinate::columnIndexFromString($sheet->getHighestColumn());

        $startColumns = [];

        for ($row = 1; $row <= $headerScanRows; $row++) {
            for ($col = 1; $col <= $highestColIndex; $col++) {
                $value = trim((string) $sheet->getCell(Coordinate::stringFromColumnIndex($col) . ($row))->getValue());

                if ($value !== '' && stripos($value, 'user') !== false && stripos($value, 'id') !== false) {
                    $startColumns[] = $col;
                }
            }
        }

        $startColumns = array_values(array_unique($startColumns));
        sort($startColumns);

        $blocks = [];
        foreach ($startColumns as $index => $startCol) {
            $nextStart = $startColumns[$index + 1] ?? ($highestColIndex + 1);
            $blocks[] = [
                'start' => $startCol,
                'end' => $nextStart - 1,
            ];
        }

        return $blocks;
    }

    protected function importBlock(Worksheet $sheet, array $block, array &$result): void
    {
        $startCol = $block['start'];
        $endCol = $block['end'];

        $biometricUserId = $this->extractLabelValue($sheet, $startCol, $endCol, 1, 6, 'User ID');

        if (!$biometricUserId) {
            $result['errors'][] = "Could not find a User ID for the block at column {$startCol} on sheet '{$sheet->getTitle()}'.";
            return;
        }

        $employee = Employee::where('biometric_user_id', $biometricUserId)->first();

        if (!$employee) {
            $result['skipped_employees'][] = [
                'biometric_user_id' => $biometricUserId,
                'sheet' => $sheet->getTitle(),
                'reason' => 'No employee found with this biometric_user_id.',
            ];
            return;
        }

        [$periodYear, $periodMonth] = $this->extractCutoffYearMonth($sheet, $startCol, $endCol);

        $timeCardRow = $this->findRowContaining($sheet, $startCol, $endCol, 'Time Card');

        if (!$timeCardRow) {
            $result['errors'][] = "Could not find the 'Time Card' section for User ID {$biometricUserId} on sheet '{$sheet->getTitle()}'.";
            return;
        }

        // Row layout: [Time Card title] [sub-header: Before Noon / After Noon / Overtime] [data...]
        $dataStartRow = $timeCardRow + 2;
        $highestRow = $sheet->getHighestRow();

        $columns = [
            'date' => $startCol,
            'before_in' => $startCol + 1,
            'before_out' => $startCol + 2,
            'after_in' => $startCol + 3,
            'after_out' => $startCol + 4,
            'ot_in' => $startCol + 5,
            'ot_out' => $startCol + 6,
        ];

        $blankStreak = 0;

        for ($row = $dataStartRow; $row <= $highestRow; $row++) {
            $rawDate = trim((string) $sheet->getCell(Coordinate::stringFromColumnIndex($columns['date']) . ($row))->getValue());

            if ($rawDate === '') {
                $blankStreak++;
                // Stop once we've clearly run past the data rows for this block.
                if ($blankStreak >= 3) {
                    break;
                }
                continue;
            }
            $blankStreak = 0;

            $date = $this->parseRowDate($rawDate, $periodYear, $periodMonth);

            if (!$date) {
                continue;
            }

            $timeIn = $this->parseTimeCell($sheet, $columns['before_in'], $row);
            $timeOut = $this->parseTimeCell($sheet, $columns['after_out'], $row)
                ?? $this->parseTimeCell($sheet, $columns['before_out'], $row);
            $otIn = $this->parseTimeCell($sheet, $columns['ot_in'], $row);
            $otOut = $this->parseTimeCell($sheet, $columns['ot_out'], $row);

            $hoursWorked = ($timeIn && $timeOut)
                ? round(abs(Carbon::parse($timeOut)->diffInMinutes(Carbon::parse($timeIn))) / 60, 2)
                : 0.00;

            $overtimeHours = ($otIn && $otOut)
                ? round(abs(Carbon::parse($otOut)->diffInMinutes(Carbon::parse($otIn))) / 60, 2)
                : 0.00;

            $status = match (true) {
                $timeIn && $timeOut => 'Present',
                $timeIn || $timeOut => 'Half Day',
                default => 'Absent',
            };

            $attendance = Attendance::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'date' => $date->toDateString(),
                ],
                [
                    'time_in' => $timeIn,
                    'time_out' => $timeOut,
                    'hours_worked' => $hoursWorked,
                    'overtime_hours' => $overtimeHours,
                    'status' => $status,
                    'remarks' => 'Imported from biometric export on ' . now()->toDateString(),
                ]
            );

            $attendance->wasRecentlyCreated
                ? $result['imported']++
                : $result['updated']++;
        }
    }

    /**
     * Find a cell in the given column/row window whose value equals or
     * contains $label, and return the value of the cell immediately to
     * its right (or below, if nothing found to the right).
     */
    protected function extractLabelValue(
        Worksheet $sheet,
        int $startCol,
        int $endCol,
        int $startRow,
        int $endRow,
        string $label
    ): ?string {
        for ($row = $startRow; $row <= $endRow; $row++) {
            for ($col = $startCol; $col <= $endCol; $col++) {
                $value = trim((string) $sheet->getCell(Coordinate::stringFromColumnIndex($col) . ($row))->getValue());

                if ($value !== '' && stripos($value, $label) !== false) {
                    // Try the cell to the right first.
                    for ($c = $col + 1; $c <= $endCol; $c++) {
                        $candidate = trim((string) $sheet->getCell(Coordinate::stringFromColumnIndex($c) . ($row))->getValue());
                        if ($candidate !== '') {
                            return $candidate;
                        }
                    }
                    // Fall back to the cell directly below.
                    $below = trim((string) $sheet->getCell(Coordinate::stringFromColumnIndex($col) . ($row + 1))->getValue());
                    if ($below !== '') {
                        return $below;
                    }
                }
            }
        }

        return null;
    }

    protected function findRowContaining(Worksheet $sheet, int $startCol, int $endCol, string $needle): ?int
    {
        $highestRow = min($sheet->getHighestRow(), 10);

        for ($row = 1; $row <= $highestRow; $row++) {
            for ($col = $startCol; $col <= $endCol; $col++) {
                $value = trim((string) $sheet->getCell(Coordinate::stringFromColumnIndex($col) . ($row))->getValue());
                if (stripos($value, $needle) !== false) {
                    return $row;
                }
            }
        }

        return null;
    }

    protected function extractCutoffYearMonth(Worksheet $sheet, int $startCol, int $endCol): array
    {
        $raw = $this->extractLabelValue($sheet, $startCol, $endCol, 1, 6, 'Date');

        if ($raw && preg_match('/(\d{4})-(\d{2})-(\d{2})/', $raw, $m)) {
            return [(int) $m[1], (int) $m[2]];
        }

        $now = Carbon::now();
        return [$now->year, $now->month];
    }

    protected function parseRowDate(string $rawDate, int $periodYear, int $periodMonth): ?Carbon
    {
        if (!preg_match('/^(\d{1,2})/', $rawDate, $m)) {
            return null;
        }

        $day = (int) $m[1];

        if ($day < 1 || $day > 31) {
            return null;
        }

        try {
            return Carbon::createFromDate($periodYear, $periodMonth, $day)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Reads a time cell such as "8:00" or "17:00:00pm" and normalizes it
     * to H:i:s. Returns null for blank/unparseable cells.
     */
    protected function parseTimeCell(Worksheet $sheet, int $col, int $row): ?string
    {
        $raw = trim((string) $sheet->getCell(Coordinate::stringFromColumnIndex($col) . ($row))->getFormattedValue());

        if ($raw === '') {
            return null;
        }

        // Normalize odd exports like "17:00:00pm" (24h value with a
        // trailing am/pm label) before handing off to Carbon.
        $clean = preg_replace('/\s*(am|pm)\s*$/i', '', $raw);
        $clean = trim($clean);

        try {
            return Carbon::parse($clean)->format('H:i:s');
        } catch (\Throwable) {
            return null;
        }
    }
}
