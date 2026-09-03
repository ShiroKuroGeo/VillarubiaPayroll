<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

#[Fillable(['label', 'value', 'status'])]
class Maintenance extends Model
{
    protected $table = 'system_maintenances';
    use HasFactory, Notifiable;
}
