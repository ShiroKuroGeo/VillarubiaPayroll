<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;


#[Fillable([
    'employee_id',
    'payroll_id',
    'amount',
    'requested_date',
    'reason',
    'status'
])]
class CashAdvance extends Model
{
    protected $table = 'cash_advances';

    public function employee(){
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function payroll(){
        return $this->belongsTo(Payroll::class, 'payroll_id');
    }
}
