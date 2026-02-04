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
        Schema::table('dg1_milestones', function (Blueprint $table) {
            $table->integer('ms2_3s')->nullable()->after('ms3s_ksa_port')->comment('Leadtime MS2-3s');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dg1_milestones', function (Blueprint $table) {
            $table->dropColumn('ms2_3s');
        });
    }
};
