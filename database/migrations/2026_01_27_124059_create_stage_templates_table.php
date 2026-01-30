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
        Schema::create('stage_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('unit_type');
            $table->integer('stage_number');
            $table->string('title');
            $table->text('description');
            $table->integer('order_index');
            // timestamps not strictly needed for templates but good practice
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stage_templates');
    }
};
