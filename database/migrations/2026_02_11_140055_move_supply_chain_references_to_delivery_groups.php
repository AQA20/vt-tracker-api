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
        Schema::table('supply_chain_references', function (Blueprint $table) {
            $table->foreignUuid('delivery_group_id')->after('id')->nullable()->constrained()->onDelete('cascade');
            $table->dropForeign(['delivery_milestone_id']);
            $table->dropColumn('delivery_milestone_id');
            // Fully cleanup legacy unit_id if it exists
            if (Schema::hasColumn('supply_chain_references', 'unit_id')) {
                // Drop foreign key first for SQLite/proper cleanup
                $table->dropForeign(['unit_id']);
                $table->dropColumn('unit_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supply_chain_references', function (Blueprint $table) {
            $table->foreignId('delivery_milestone_id')->after('id')->nullable()->constrained()->onDelete('cascade');
            $table->dropForeign(['delivery_group_id']);
            $table->dropColumn('delivery_group_id');
        });
    }
};
