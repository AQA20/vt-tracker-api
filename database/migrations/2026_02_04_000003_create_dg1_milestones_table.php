<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dg1_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cse_id')->constrained('cse_details')->cascadeOnDelete();

            // Date columns
            $table->date('ms2')->nullable()->comment('FL Send order to SL');
            $table->date('ms2a')->nullable()->comment('Order Check & Listing Release');
            $table->date('ms2c')->nullable()->comment('Listing Completion');
            $table->date('ms2z')->nullable()->comment('Engineering Completion');
            $table->date('ms3')->nullable()->comment('NRP');
            $table->date('ms3a_exw')->nullable()->comment('Material in DC');
            $table->date('ms3b')->nullable()->comment('Actual Shipping Date');
            $table->date('ms3s_ksa_port')->nullable()->comment('Delivery to Dammam Port');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dg1_milestones');
    }
};
