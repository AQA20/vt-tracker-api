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
        Schema::table('status_updates', function (Blueprint $table) {
            $table->integer('tech_sub_rejection_count')->default(0);
            $table->integer('sample_rejection_count')->default(0);
            $table->integer('layout_rejection_count')->default(0);
            $table->integer('car_m_dwg_rejection_count')->default(0);
            $table->integer('cop_dwg_rejection_count')->default(0);
            $table->integer('landing_dwg_rejection_count')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('status_updates', function (Blueprint $table) {
            $table->dropColumn([
                'tech_sub_rejection_count',
                'sample_rejection_count',
                'layout_rejection_count',
                'car_m_dwg_rejection_count',
                'cop_dwg_rejection_count',
                'landing_dwg_rejection_count',
            ]);
        });
    }
};
