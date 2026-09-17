<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_advances', function (Blueprint $table) {
            $table->decimal('installment_amount', 10, 2)->nullable()->after('amount');
            $table->decimal('installment_count', 10, 2)->nullable()->after('installment_amount');
        });
    }

    public function down(): void
    {
        Schema::table('cash_advances', function (Blueprint $table) {
            $table->dropColumn(['installment_amount', 'installment_count']);
        });
    }

};