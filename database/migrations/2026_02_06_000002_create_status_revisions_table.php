<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('status_revisions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('status_update_id');
            $table->smallInteger('revision_number'); // 0–9
            $table->text('pdf_path')->nullable();
            $table->timestamp('revision_date')->nullable();
            $table->timestamps();

            $table->foreign('status_update_id')
                ->references('id')
                ->on('status_updates')
                ->cascadeOnDelete();

            $table->unique(['status_update_id', 'revision_number']);
        });

        // PostgreSQL check constraint for revision number range
        if (config('database.default') !== 'sqlite') {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE status_revisions ADD CONSTRAINT status_revisions_revision_number_check CHECK (revision_number BETWEEN 0 AND 9)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('status_revisions');
    }
};
