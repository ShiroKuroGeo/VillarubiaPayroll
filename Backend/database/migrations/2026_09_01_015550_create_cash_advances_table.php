<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cash_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payroll_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->date('requested_date');
            $table->decimal('installment_amount', 10, 2)->nullable();
            $table->unsignedInteger('installment_count')->nullable();
            $table->decimal('balance', 10, 2)->nullable();
            $table->decimal('custom_amount', 10, 2)->nullable();
            $table->date('target_cutoff_start')->nullable();
            $table->enum('payment_type', ['Installment', 'Custom'])->default('Installment');
            $table->text('reason')->nullable();
            $table->enum('status', ['Approved', 'Pending', 'Deducted/Paid', 'Rejected'])->default('Pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_advances');
    }
};
