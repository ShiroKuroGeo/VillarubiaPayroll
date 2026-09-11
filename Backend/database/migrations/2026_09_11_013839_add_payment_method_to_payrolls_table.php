<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->enum('payment_method', [
                'Bank Transfer',
                'Cash',
                'Check',
                'GCash',
            ])
                ->nullable()
                ->after('payment_date');
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });
    }
};
