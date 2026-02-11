<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    // use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::updateOrCreate(
            ['email' => 'admin@vttracker.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]
        );

        // $this->call([
        //     ProjectSeeder::class,
        // ]);

        $this->call([
            TemplateSeeder::class,
            ProjectSeeder::class,
            DeliveryCatalogSeeder::class,
            DeliveryDataSeeder::class,
            DeliveryGroupItemSeeder::class,
        ]);
    }
}
