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
            $table->string('kone_equipment_no')->nullable()->after('equipment_number');
            $table->string('sl_reference_no')->nullable()->after('kone_equipment_no');
            $table->string('fl_unit_name')->nullable()->after('sl_reference_no');
            $table->text('unit_description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn(['kone_equipment_no', 'sl_reference_no', 'fl_unit_name', 'unit_description']);
        });
    }
};
