<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('status_approvals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('status_update_id');
            $table->string('approval_code'); // A or B
            $table->text('pdf_path')->nullable();
            $table->text('comment')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->foreign('status_update_id')
                ->references('id')
                ->on('status_updates')
                ->cascadeOnDelete();

            // One approval per code per status
            $table->unique(['status_update_id', 'approval_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_approvals');
    }
};
