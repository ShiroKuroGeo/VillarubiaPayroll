<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        /*
         * Drop existing status check constraint
         */
        DB::statement("
            ALTER TABLE attendances
            DROP CONSTRAINT IF EXISTS attendances_status_check
        ");

        /*
         * Add new constraint including Late
         */
        DB::statement("
            ALTER TABLE attendances
            ADD CONSTRAINT attendances_status_check
            CHECK (
                status IN (
                    'Present',
                    'Leave',
                    'Half Day',
                    'Absent',
                    'Late'
                )
            )
        ");
    }

    public function down()
    {
        DB::statement("
            ALTER TABLE attendances
            DROP CONSTRAINT IF EXISTS attendances_status_check
        ");

        DB::statement("
            ALTER TABLE attendances
            ADD CONSTRAINT attendances_status_check
            CHECK (
                status IN (
                    'Present',
                    'Leave',
                    'Half Day',
                    'Absent'
                )
            )
        ");
    }
};
