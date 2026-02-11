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
            // Drop the unique constraint on unit_id
            $table->dropUnique(['unit_id']);

            // Make unit_id nullable
            $table->uuid('unit_id')->nullable()->change();

            // Add delivery_milestone_id column
            $table->uuid('delivery_milestone_id')->nullable()->after('unit_id');

            // Add foreign key constraint
            $table->foreign('delivery_milestone_id')
                ->references('id')
                ->on('delivery_milestones')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supply_chain_references', function (Blueprint $table) {
            // Drop foreign key and column
            $table->dropForeign(['delivery_milestone_id']);
            $table->dropColumn('delivery_milestone_id');

            // Make unit_id non-nullable again
            $table->uuid('unit_id')->nullable(false)->change();

            // Re-add unique constraint
            $table->unique('unit_id');
        });
    }
};
