<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'employee_id',
    'cutoff_start',
    'cutoff_end',
    'payout_date',
    'gross_pay',
    'total_deductions',
    'net_pay',
    'status',
    'payment_date'
])]
class Payroll extends Model
{
    protected $table = 'payrolls';

    public function employee(){
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function cashadvances(){
        return $this->hasMany(CashAdvance::class);
    }

    public function deductions(){
        return $this->hasMany(Deduction::class);
    }
}