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
        Schema::table('units', function (Blueprint $table) {
            $table->integer('installation_progress')->default(0);
            $table->integer('commissioning_progress')->default(0);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->integer('installation_progress')->default(0);
            $table->integer('commissioning_progress')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn(['installation_progress', 'commissioning_progress']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['installation_progress', 'commissioning_progress']);
        });
    }
};
