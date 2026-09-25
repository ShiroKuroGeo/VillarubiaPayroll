<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'employee_id',
    'payroll_id',
    'sss_deduction',
    'ca_deduction',
    'other_deduction',
    'remarks'
])]
class Deduction extends Model
{
    protected $table = 'deductions';

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    public function cashAdvanceBreakdown()
    {
        return CashAdvanceDeduction::where('payroll_id', $this->payroll_id)->get();
    }

    protected $casts = [
        'other_deduction' => 'array',
    ];
}
