<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('status_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cse_id')->constrained('cse_details')->cascadeOnDelete();

            // Status columns (Strings, validated by Enum in App)
            $table->string('tech_sub_status')->nullable();
            $table->string('sample_status')->nullable();
            $table->string('layout_status')->nullable();
            $table->string('car_m_dwg_status')->nullable();
            $table->string('cop_dwg_status')->nullable();
            $table->string('landing_dwg_status')->nullable();

            // PDF Path columns
            $table->string('tech_sub_status_pdf')->nullable();
            $table->string('sample_status_pdf')->nullable();
            $table->string('layout_status_pdf')->nullable();
            $table->string('car_m_dwg_status_pdf')->nullable();
            $table->string('cop_dwg_status_pdf')->nullable();
            $table->string('landing_dwg_status_pdf')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_updates');
    }
};
