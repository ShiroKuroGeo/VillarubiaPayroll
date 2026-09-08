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
        // Schema::create('maintenances', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('name');
        //     $table->text('description')->nullable();
        //     $table->string('value')->nullable();
        //     $table->string('tags')->nullable();
        //     $table->boolean('is_section')->default(false);
        //     $table->string('section_name')->nullable();
        //     $table->enum('status', ['Online', 'Offline', 'Busy'])->default('Online');
        //     $table->timestamps();
        // });

        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('value')->nullable();
            $table->string('tags')->nullable();
            $table->boolean('is_section')->default(false);
            $table->string('section_name')->nullable();
            $table->enum('input_type', ['text', 'number', 'time', 'pay-period'])->nullable();
            $table->enum('status', ['Online', 'Offline', 'Busy'])->default('Online');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance');
    }
};
