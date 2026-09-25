<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashAdvanceDeduction extends Model
{
    protected $fillable = [
        'cash_advance_id',
        'payroll_id',
        'employee_id',
        'amount',
        'remaining_installments_after',
        'cutoff_start',
        'cutoff_end',
    ];

    public function cashAdvance()
    {
        return $this->belongsTo(CashAdvance::class);
    }

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
