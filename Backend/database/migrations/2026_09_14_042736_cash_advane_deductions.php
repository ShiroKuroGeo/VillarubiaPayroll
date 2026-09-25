<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_advance_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_advance_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payroll_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->unsignedInteger('remaining_installments_after')->nullable();
            $table->date('cutoff_start');
            $table->date('cutoff_end');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_advance_deductions');
    }
};
