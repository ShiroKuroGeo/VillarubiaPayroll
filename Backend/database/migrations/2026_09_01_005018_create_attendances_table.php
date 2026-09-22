<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->time('before_noon_in')->nullable();
            $table->time('before_noon_out')->nullable();
            $table->time('after_noon_in')->nullable();
            $table->time('after_noon_out')->nullable();
            $table->time('overtime_in')->nullable();
            $table->time('overtime_out')->nullable();
            $table->decimal('hours_worked', 5, 2)->default(0.00);
            $table->decimal('overtime_hours', 5, 2)->default(0.00);
            $table->enum('status', ['Present', 'Late', 'Half Day', 'Leave', 'Absent'])->default('Absent');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
