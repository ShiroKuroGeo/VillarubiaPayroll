<?php

namespace App\Http\Services;

use App\Models\BackupLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class BackupServices
{
    // How stale a backup can get before the dashboard flags it.
    const STALE_AFTER_HOURS = 48;

    /**
     * Called by the backup script itself after each run — NOT meant to be
     * hit from the frontend UI. Lock this route down hard (e.g. a shared
     * script secret/token, or restrict to localhost) since anyone who can
     * call this can spoof "backup succeeded" logs.
     */
    public function logBackup(Request $request)
    {
        try {
            $validation = $request->validate([
                'status' => ['required', Rule::in(['Success', 'Failed'])],
                'destination' => ['nullable', 'string', 'max:255'],
                'file_size_bytes' => ['nullable', 'integer', 'min:0'],
                'notes' => ['nullable', 'string'],
            ]);
        } catch (\Throwable $th) {
            return response_return('Error occurred in validating backup log information.', [], 422);
        }

        try {
            $createLog = BackupLog::create([
                'ran_at' => now(),
                'status' => $validation['status'],
                'destination' => $validation['destination'] ?? null,
                'file_size_bytes' => $validation['file_size_bytes'] ?? null,
                'notes' => $validation['notes'] ?? null,
            ]);

            if (!$createLog) {
                return response_return('Cannot save backup log at this moment.', [], 409);
            }

            return response_return('Backup log recorded.', $createLog->toArray(), 201);
        } catch (\Throwable $th) {
            return response_return('Error occurred in recording backup log.', [], 500);
        }
    }

    /**
     * Drives the informational (non-blocking) dashboard banner.
     * This never restricts anything in the app — it only reports status.
     */
    public function getBackupStatus()
    {
        try {
            $latest = BackupLog::orderByDesc('ran_at')->first();

            if (!$latest) {
                return response_return('Backup status retrieved.', [
                    'is_up_to_date' => false,
                    'last_backup' => null,
                    'message' => 'No backup has ever run.',
                ], 200);
            }

            $hoursSince = Carbon::parse($latest->ran_at)->diffInHours(now());
            $isStale = $hoursSince > self::STALE_AFTER_HOURS;
            $isUpToDate = $latest->status === 'Success' && !$isStale;

            $message = match (true) {
                $latest->status === 'Failed' => 'The last backup attempt failed.',
                $isStale => "Last backup was {$hoursSince} hours ago — overdue.",
                default => 'Backups are up to date.',
            };

            return response_return('Backup status retrieved.', [
                'is_up_to_date' => $isUpToDate,
                'last_backup' => $latest->toArray(),
                'hours_since_last_backup' => $hoursSince,
                'message' => $message,
            ], 200);
        } catch (\Throwable $th) {
            return response_return('Error occurred in retrieving backup status.', [], 500);
        }
    }

    public function getBackupLogs(Request $request)
    {
        try {
            $validation = $request->validate([
                'status' => ['nullable', Rule::in(['Success', 'Failed'])],
                'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            ]);
        } catch (\Throwable $th) {
            return response_return('Error occurred in validating the request.', [], 422);
        }

        try {
            $query = BackupLog::query();

            if (!empty($validation['status'])) {
                $query->where('status', $validation['status']);
            }

            $logs = $query->orderByDesc('ran_at')
                ->paginate($validation['per_page'] ?? 15);

            return response_return('Successfully retrieved backup logs.', $logs->toArray(), 200);
        } catch (\Throwable $th) {
            return response_return('Error occurred in retrieving backup logs.', [], 500);
        }
    }
}