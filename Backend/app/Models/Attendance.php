<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'employee_id',
    'date',
    'time_in',
    'time_out',
    'before_noon_in',
    'before_noon_out',
    'after_noon_in',
    'after_noon_out',
    'overtime_in',
    'overtime_out',
    'hours_worked',
    'overtime_hours',
    'status',
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
