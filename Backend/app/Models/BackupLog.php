<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'ran_at',
    'status',
    'destination',
    'file_size_bytes',
    'notes'
])]
class BackupLog extends Model
{
    protected $table = 'backup_logs';
}
