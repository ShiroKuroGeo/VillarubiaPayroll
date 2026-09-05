<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SSSContribution extends Model
{
    protected $fillable = [
        'employee_id',
        'payroll_id',
        'amount',
        'date',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date'   => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }
}
