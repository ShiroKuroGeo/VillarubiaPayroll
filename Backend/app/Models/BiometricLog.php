<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BiometricLog extends Model
{
    protected $fillable = [
        'employee_id',
        'biometric_user_id',
        'scan_time',
    ];

    protected $casts = [
        'scan_time' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(
            Employee::class
        );
    }
}
