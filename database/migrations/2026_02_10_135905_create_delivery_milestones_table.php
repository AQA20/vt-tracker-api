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
        Schema::create('delivery_milestones', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('delivery_group_id');
            $table->string('milestone_code'); // 1a, 1b, 1c, 2, 2a, 2c, 2f, 3, 3a, 3b, 3s
            $table->string('milestone_name');
            $table->integer('planned_leadtime_days')->nullable();
            $table->date('planned_completion_date')->nullable();
            $table->date('actual_completion_date')->nullable();
            $table->integer('difference_days')->nullable(); // auto-calculated
            $table->timestamps();

            $table->foreign('delivery_group_id')
                ->references('id')
                ->on('delivery_groups')
                ->cascadeOnDelete();

            $table->unique(['delivery_group_id', 'milestone_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_milestones');
    }
};
