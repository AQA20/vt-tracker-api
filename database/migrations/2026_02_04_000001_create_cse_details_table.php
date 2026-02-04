<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cse_details', function (Blueprint $table) {
            $table->id();
            $table->integer('equip_n')->unique()->comment('Unique linkage key for import/export');
            $table->string('asset_name')->nullable();
            $table->string('unit_id')->nullable();
            $table->string('material_code')->nullable();
            $table->integer('so_no')->nullable()->index();
            $table->integer('network_no')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cse_details');
    }
};
