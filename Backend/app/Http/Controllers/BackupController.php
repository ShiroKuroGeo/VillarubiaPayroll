<?php

namespace App\Http\Controllers;

use App\Http\Services\BackupServices;
use Illuminate\Http\Request;

class BackupController extends Controller
{
    private $backupServices;

    public function __construct(BackupServices $backupServices)
    {
        $this->backupServices = $backupServices;
    }

    public function logBackup(Request $request){
        return $this->backupServices->logBackup($request);
    }

    public function getBackupStatus(Request $request){
        return $this->backupServices->getBackupStatus($request);
    }

    public function getBackupLogs(Request $request){
        return $this->backupServices->getBackupLogs($request);
    }
}
