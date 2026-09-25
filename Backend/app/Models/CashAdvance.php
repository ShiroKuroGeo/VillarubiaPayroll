<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'employee_id',
    'payroll_id',
    'amount',
    'balance',
    'custom_amount',
    'target_cutoff_start',
    'payment_type',
    'installment_amount',
    'installment_count',
    'requested_date',
    'reason',
    'status'
])]
class CashAdvance extends Model
{
    protected $table = 'cash_advances';

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function deductions()
    {
        return $this->hasMany(CashAdvanceDeduction::class);
    }

    public function payrolls()
    {
        return $this->hasManyThrough(
            Payroll::class,
            CashAdvanceDeduction::class,
            'cash_advance_id',
            'id',
            'id',
            'payroll_id'
        );
    }
}
