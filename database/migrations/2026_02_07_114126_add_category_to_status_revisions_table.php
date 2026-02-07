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
        Schema::table('status_revisions', function (Blueprint $table) {
            $table->string('category')->default('submitted')->after('status_update_id');
            $table->dropUnique(['status_update_id', 'revision_number']);
            $table->unique(['status_update_id', 'category', 'revision_number']);
        });

        if (config('database.default') !== 'sqlite') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE status_revisions ADD CONSTRAINT status_revisions_category_check CHECK (category IN ('submitted', 'rejected'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('status_revisions', function (Blueprint $table) {
            if (config('database.default') !== 'sqlite') {
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE status_revisions DROP CONSTRAINT status_revisions_category_check');
            }
            $table->dropUnique(['status_update_id', 'category', 'revision_number']);
            $table->dropColumn('category');
            $table->unique(['status_update_id', 'revision_number']);
        });
    }
};
