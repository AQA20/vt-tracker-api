<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        // 1. Create the custom ENUM type if it doesn't exist
        DB::statement("DO $$ BEGIN
            CREATE TYPE unit_category AS ENUM ('elevator', 'escalator', 'travelator', 'dumbwaiter');
        EXCEPTION
            WHEN duplicate_object THEN null;
        END $$;");

        // 2. Convert units.category to the new type
        DB::statement('ALTER TABLE units ALTER COLUMN category DROP DEFAULT');
        DB::statement('ALTER TABLE units ALTER COLUMN category TYPE unit_category USING category::unit_category');
        DB::statement("ALTER TABLE units ALTER COLUMN category SET DEFAULT 'elevator'::unit_category");

        // 3. Convert stage_templates.category to the new type
        DB::statement('ALTER TABLE stage_templates ALTER COLUMN category DROP DEFAULT');
        DB::statement('ALTER TABLE stage_templates ALTER COLUMN category TYPE unit_category USING category::unit_category');
        DB::statement("ALTER TABLE stage_templates ALTER COLUMN category SET DEFAULT 'elevator'::unit_category");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        // 1. Revert stage_templates.category to string
        DB::statement('ALTER TABLE stage_templates ALTER COLUMN category TYPE VARCHAR(255) USING category::VARCHAR');
        DB::statement("ALTER TABLE stage_templates ALTER COLUMN category SET DEFAULT 'elevator'");

        // 2. Revert units.category to string
        DB::statement('ALTER TABLE units ALTER COLUMN category TYPE VARCHAR(255) USING category::VARCHAR');
        DB::statement("ALTER TABLE units ALTER COLUMN category SET DEFAULT 'elevator'");

        // 3. Drop the custom ENUM type
        DB::statement('DROP TYPE unit_category');
    }
};
