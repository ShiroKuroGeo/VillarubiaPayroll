<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_advances', function (Blueprint $table) {
            $table->decimal('balances', 10, 2);
            $table->decimal('installment_amount', 10, 2)
                ->nullable()
                ->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('cash_advances', function (Blueprint $table) {
            $table->dropColumn('balances');
            $table->dropColumn('installment_amount');
        });
    }
};
