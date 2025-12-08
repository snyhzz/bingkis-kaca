<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            DefaultFrameSeeder::class, // NEW: Seed default frames
            FrameSeeder::class, // Optional: Keep for custom frames examples
        ]);
    }
}
