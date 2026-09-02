<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'employee_id',
    'salary_type',
    'basic_salary',
    'effective_date',
    'is_active'
])]
class Salary extends Model
{
    protected $table = 'salaries'; 

    public function employee(){
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
