<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

/**
 * Builds a single .xlsx file containing one payslip per employee,
 * stacked vertically and separated by a dashed line, each with a
 * signature line at the bottom.
 *
 * Usage:
 *   $export = new PayslipExport($payrollData, 'September 2026');
 *   return $export->download('payslips.xlsx');
 *   // or: $export->save(storage_path('app/payslips.xlsx'));
 */
class PayslipExport
{
    protected array $data;
    protected string $payPeriod;
    protected string $companyName;

    // Layout constants
    protected int $colStart = 1; // A
    protected int $colEnd = 6;   // F
    protected int $currentRow = 1;

    public function __construct(array $data, string $payPeriod = '', string $companyName = 'Your Company Name')
    {
        $this->data = $data;
        $this->payPeriod = $payPeriod;
        $this->companyName = $companyName;
    }
    public function build(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Payslips');

        /*
    |--------------------------------------------------------------------------
    | Column widths
    |--------------------------------------------------------------------------
    */

        $sheet->getColumnDimension('A')->setWidth(22);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(18);


        /*
    |--------------------------------------------------------------------------
    | Print Setup
    |--------------------------------------------------------------------------
    */

        $pageSetup = $sheet->getPageSetup();

        // A4 paper
        $pageSetup->setPaperSize(
            \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4
        );

        // Portrait orientation
        $pageSetup->setOrientation(
            \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT
        );

        // Fit A-F into ONE page width
        $pageSetup->setFitToWidth(1);

        // Allow multiple pages vertically
        $pageSetup->setFitToHeight(0);

        // Enable fit to page
        $sheet->setShowGridlines(false);

        $sheet->getPageSetup()->setHorizontalCentered(false);


        /*
    |--------------------------------------------------------------------------
    | Print Margins
    |--------------------------------------------------------------------------
    */

        $margins = $sheet->getPageMargins();

        $margins->setTop(0.30);
        $margins->setBottom(0.30);
        $margins->setLeft(0.25);
        $margins->setRight(0.25);

        $margins->setHeader(0);
        $margins->setFooter(0);


        /*
    |--------------------------------------------------------------------------
    | Build Payslips
    |--------------------------------------------------------------------------
    */

        $this->currentRow = 1;

        foreach ($this->data as $index => $payslip) {

            $this->writePayslipBlock(
                $sheet,
                $payslip
            );

            $isLast = $index === array_key_last(
                $this->data
            );

            if (!$isLast) {

                $this->writeDashedSeparator(
                    $sheet
                );
            }
        }


        /*
    |--------------------------------------------------------------------------
    | Dynamic Print Area
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    | This makes Excel include columns A through F
    | and all generated rows.
    |
    */

        $lastRow = $this->currentRow;

        $sheet->getPageSetup()->setPrintArea(
            "A1:F{$lastRow}"
        );


        return $spreadsheet;
    }

    // public function build(): Spreadsheet
    // {
    //     $spreadsheet = new Spreadsheet();
    //     $sheet = $spreadsheet->getActiveSheet();
    //     $sheet->setTitle('Payslips');

    //     // Column widths
    //     $sheet->getColumnDimension('A')->setWidth(22);
    //     $sheet->getColumnDimension('B')->setWidth(18);
    //     $sheet->getColumnDimension('C')->setWidth(18);
    //     $sheet->getColumnDimension('D')->setWidth(18);
    //     $sheet->getColumnDimension('E')->setWidth(18);
    //     $sheet->getColumnDimension('F')->setWidth(18);

    //     $this->currentRow = 1;

    //     foreach ($this->data as $index => $payslip) {
    //         $this->writePayslipBlock($sheet, $payslip);

    //         $isLast = $index === array_key_last($this->data);
    //         if (!$isLast) {
    //             $this->writeDashedSeparator($sheet);
    //         }
    //     }

    //     return $spreadsheet;
    // }

    protected function writePayslipBlock($sheet, array $p): void
    {
        $startRow = $this->currentRow;

        // --- Company header ---
        $this->mergeAndSet($sheet, "A{$this->currentRow}", $this->companyName, 14, true);
        $this->currentRow++;

        $this->mergeAndSet($sheet, "A{$this->currentRow}", 'PAYSLIP', 12, true, Alignment::HORIZONTAL_LEFT);
        $sheet->setCellValue("D{$this->currentRow}", 'Pay Period:');
        $sheet->setCellValue("E{$this->currentRow}", $p['period'] ?? ($this->payPeriod ?: '-'));
        $sheet->getStyle("D{$this->currentRow}")->getFont()->setBold(true);
        $this->currentRow++;

        $this->currentRow++; // spacer

        // --- Employee info ---
        $sheet->setCellValue("A{$this->currentRow}", 'Employee Name:');
        $sheet->setCellValue("B{$this->currentRow}", $p['employeeName'] ?? '');
        $sheet->setCellValue("D{$this->currentRow}", 'Reference:');
        $sheet->setCellValue("E{$this->currentRow}", $p['reference'] ?? '');
        $this->boldCells($sheet, ["A{$this->currentRow}", "D{$this->currentRow}"]);
        $this->currentRow++;

        $sheet->setCellValue("A{$this->currentRow}", 'Address:');
        $sheet->setCellValue("B{$this->currentRow}", $p['address'] ?? '');
        $sheet->setCellValue("D{$this->currentRow}", 'Salary Type:');
        $sheet->setCellValue("E{$this->currentRow}", $p['salaryType'] ?? '');
        $this->boldCells($sheet, ["A{$this->currentRow}", "D{$this->currentRow}"]);
        $this->currentRow++;

        $sheet->setCellValue("A{$this->currentRow}", 'Payment Method:');
        $sheet->setCellValue("B{$this->currentRow}", $p['paymentMethod'] ?? '');
        $sheet->setCellValue("D{$this->currentRow}", 'Status:');
        $sheet->setCellValue("E{$this->currentRow}", $p['status'] ?? '');
        $this->boldCells($sheet, ["A{$this->currentRow}", "D{$this->currentRow}"]);
        $this->currentRow++;

        $this->currentRow++; // spacer

        // --- Earnings / Deductions side-by-side headers ---
        $earnRow = $this->currentRow;
        $this->headerCell($sheet, "A{$earnRow}", 'EARNINGS');
        $sheet->mergeCells("A{$earnRow}:B{$earnRow}");
        $this->headerCell($sheet, "D{$earnRow}", 'DEDUCTIONS');
        $sheet->mergeCells("D{$earnRow}:E{$earnRow}");
        $this->currentRow++;

        $earnings = $p['earnings'] ?? [];
        $deductions = $p['deductions'] ?? [];

        $earningRows = [
            'Basic Salary' => $earnings['basicSalary'] ?? 0,
            'Sunday Premium' => $earnings['sundayPremium'] ?? 0,
            'Overtime' => $earnings['overtime'] ?? 0,
            'Total Attendance (days)' => $earnings['totalAttendance'] ?? 0,
        ];

        $deductionRows = [
            'SSS' => $deductions['sss'] ?? 0,
            'Cash Advance' => $deductions['cashAdvance'] ?? 0,
            'Late' => $deductions['late'] ?? 0,
        ];

        $maxRows = max(count($earningRows), count($deductionRows));
        $earningKeys = array_keys($earningRows);
        $deductionKeys = array_keys($deductionRows);

        for ($i = 0; $i < $maxRows; $i++) {
            $row = $this->currentRow;
            if (isset($earningKeys[$i])) {
                $label = $earningKeys[$i];
                $sheet->setCellValue("A{$row}", $label);
                $sheet->setCellValue("B{$row}", $earningRows[$label]);
                if ($label !== 'Total Attendance (days)') {
                    $sheet->getStyle("B{$row}")->getNumberFormat()
                        ->setFormatCode('#,##0.00');
                }
            }
            if (isset($deductionKeys[$i])) {
                $label = $deductionKeys[$i];
                $sheet->setCellValue("D{$row}", $label);
                $sheet->setCellValue("E{$row}", $deductionRows[$label]);
                $sheet->getStyle("E{$row}")->getNumberFormat()
                    ->setFormatCode('#,##0.00');
            }
            $this->currentRow++;
        }

        // --- Totals ---
        $sheet->setCellValue("A{$this->currentRow}", 'GROSS PAY');
        $sheet->setCellValue("B{$this->currentRow}", $p['grossPay'] ?? 0);
        $sheet->setCellValue("D{$this->currentRow}", 'TOTAL DEDUCTIONS');
        $sheet->setCellValue("E{$this->currentRow}", $p['totalDeductions'] ?? 0);
        $this->boldCells($sheet, ["A{$this->currentRow}", "D{$this->currentRow}"]);
        $sheet->getStyle("B{$this->currentRow}")->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle("E{$this->currentRow}")->getNumberFormat()->setFormatCode('#,##0.00');
        $this->currentRow++;

        $this->currentRow++; // spacer

        // --- Net pay (highlighted) ---
        $netRow = $this->currentRow;
        $sheet->setCellValue("A{$netRow}", 'NET PAY');
        $sheet->mergeCells("A{$netRow}:B{$netRow}");
        $sheet->setCellValue("C{$netRow}", $p['netPay'] ?? 0);
        $sheet->getStyle("C{$netRow}")->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle("A{$netRow}:C{$netRow}")->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle("A{$netRow}:C{$netRow}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFF2CC');
        $this->currentRow++;

        $this->currentRow++; // spacer
        $this->currentRow++; // spacer

        // --- Signature line ---
        $sigRow = $this->currentRow;
        $sheet->setCellValue("A{$sigRow}", '_____________________________');
        $this->currentRow++;
        $sheet->setCellValue("A{$this->currentRow}", 'Employee Signature over Printed Name');
        $sheet->getStyle("A{$this->currentRow}")->getFont()->setItalic(true)->setSize(9);
        $this->currentRow++;

        // Outer border around the whole payslip block
        $endRow = $this->currentRow - 1;
        $range = "A{$startRow}:F{$endRow}";
        $sheet->getStyle($range)->getBorders()->getOutline()
            ->setBorderStyle(Border::BORDER_MEDIUM);

        $this->currentRow++; // spacer before separator
    }

    protected function writeDashedSeparator($sheet): void
    {
        $row = $this->currentRow;
        $range = "A{$row}:F{$row}";
        $sheet->getStyle($range)->getBorders()->getBottom()
            ->setBorderStyle(Border::BORDER_DASHED);
        $sheet->getRowDimension($row)->setRowHeight(6);
        $this->currentRow++;
        $this->currentRow++; // spacer after separator
    }

    protected function mergeAndSet($sheet, string $cell, string $value, int $size = 11, bool $bold = false, string $align = Alignment::HORIZONTAL_LEFT): void
    {
        $sheet->setCellValue($cell, $value);
        $sheet->getStyle($cell)->getFont()->setSize($size)->setBold($bold);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal($align);
    }

    protected function headerCell($sheet, string $cell, string $value): void
    {
        $sheet->setCellValue($cell, $value);
        $sheet->getStyle($cell)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($cell)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('4472C4');
    }

    protected function boldCells($sheet, array $cells): void
    {
        foreach ($cells as $cell) {
            $sheet->getStyle($cell)->getFont()->setBold(true);
        }
    }

    /**
     * Stream the file directly as a download response.
     */
    public function download(string $filename = 'payslips.xlsx')
    {
        $spreadsheet = $this->build();
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Save the file to a path on disk instead of streaming it.
     */
    public function save(string $path): void
    {
        $spreadsheet = $this->build();
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);
    }
}
