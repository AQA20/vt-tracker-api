<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('status_updates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('unit_id');
            $table->string('category'); // StatusCategory
            $table->string('status')->nullable();   // Status
            $table->timestamps();

            $table->foreign('unit_id')
                ->references('id')
                ->on('units')
                ->cascadeOnDelete();

            // Enforce one status per category per unit
            $table->unique(['unit_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_updates');
    }
};
