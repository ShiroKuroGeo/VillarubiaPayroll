<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->decimal('late_hours', 5, 2)->default(0.00)->after('overtime_hours');
            $table->decimal('undertime_hours', 5, 2)->default(0.00)->after('late_hours');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['late_hours', 'undertime_hours']);
        });
    }
};
