<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashAdvanceDeduction extends Model
{
    protected $fillable = [
        'cash_advance_id',
        'payroll_id',
        'amount_deducted',
        'balance_after',
    ];

    public function cashAdvance()
    { 
        return $this->belongsTo(CashAdvance::class);
    }

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }
}
