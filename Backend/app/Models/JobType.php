<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

#[Fillable(['label', 'value', 'status'])]
class JobType extends Model
{
    protected $table = 'job_types';
    use HasFactory, Notifiable;

    public function employee(){
        return $this->hasMany(Employee::class, 'job_id');
    }
}
