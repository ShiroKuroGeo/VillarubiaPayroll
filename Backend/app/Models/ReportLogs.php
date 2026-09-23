<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;


#[Fillable(['report_type', 'last_generated'])]
class ReportLogs extends Model
{
    protected $table = 'report_logs';
}
