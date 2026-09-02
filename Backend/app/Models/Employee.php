<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['job_id', 'image', 'first_name', 'last_name', 'phone_number', 'location', 'email', 'status', 'date_hired',])]
class Employee extends Model
{
    protected $table = 'employees';

    public function job()
    {
        return $this->belongsTo(JobType::class, 'job_id');
    }

    public function attendances(){
        return $this->hasMany(Attendance::class);
    }

    public function salaries(){
        return $this->hasMany(Salary::class);
    }

    public function payrolls(){
        return $this->hasMany(Payroll::class);
    }

    public function cashAdvances(){
        return $this->hasMany(CashAdvance::class);
    }

    public function deductions(){
        return $this->hasMany(Deduction::class);
    }
}
