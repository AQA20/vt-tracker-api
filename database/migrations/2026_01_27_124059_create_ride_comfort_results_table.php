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
        Schema::create('ride_comfort_results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('unit_id')->constrained()->cascadeOnDelete();
            $table->decimal('vibration_value', 8, 2);
            $table->decimal('noise_db', 8, 2);
            $table->decimal('jerk_value', 8, 2);
            $table->boolean('passed');
            $table->timestamp('measured_at')->useCurrent();
            $table->string('device_used')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ride_comfort_results');
    }
};
