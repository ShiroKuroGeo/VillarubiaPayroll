<?php

namespace App\Http\Controllers;

use App\Models\ReportLogs;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportLogController extends Controller
{

    public function checkStatus()
    {
        try {
            $today = Carbon::today();

            $isSaturday = $today->isSaturday();
            $lastLog = ReportLogs::where('report_type', 'saturday_report')->first();
            $alreadyGenerated = $lastLog && Carbon::parse($lastLog->last_generated)->isToday();

            return response()->json([
                'isSaturday' => $isSaturday,
                'alreadyGenerated' => $alreadyGenerated,
                'canGenerate' => $isSaturday && !$alreadyGenerated,
            ]);
        } catch (\Throwable $th) {
            return response_return('Error occurred in get last generated payroll.', [], 500);
        }
    }

    public function generate(string $reportLogs)
    {
        try {
            ReportLogs::create([
                "report_logs" => $reportLogs,
                "last_generated" => Carbon::today()->toDateString(),
            ]);
        } catch (\Throwable $th) {
            return response_return('Error occurred in generating a report.', [], 500);
        }
    }
}
