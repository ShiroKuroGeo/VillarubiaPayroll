<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'employee_id',
    'date',
    'time_in',
    'time_out',
    'hours_worked',
    'overtime_hours',
    'status',
    'late_hours',
    'undertime_hours',
    'remarks',
])]
class Attendance extends Model
{

    protected $table = 'attendances';

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
