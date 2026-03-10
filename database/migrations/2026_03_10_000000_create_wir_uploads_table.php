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
        Schema::create('wir_uploads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('unit_id')->index();
            $table->enum('progress_group', ['installation', 'commissioning']);
            $table->string('file_path');
            $table->string('file_name');
            $table->integer('file_size');
            $table->uuid('uploaded_by');
            $table->timestamps();

            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['unit_id', 'progress_group']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wir_uploads');
    }
};
