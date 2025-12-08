<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Cute',
                'slug' => 'cute',
                'description' => 'Adorable and playful frames perfect for fun moments',
                'is_active' => true,
            ],
            [
                'name' => 'Classic',
                'slug' => 'classic',
                'description' => 'Timeless and elegant frames for any occasion',
                'is_active' => true,
            ],
            [
                'name' => 'Wedding',
                'slug' => 'wedding',
                'description' => 'Elegant frames perfect for wedding celebrations',
                'is_active' => true,
            ],
            [
                'name' => 'Birthday',
                'slug' => 'birthday',
                'description' => 'Fun and colorful frames for birthday parties',
                'is_active' => true,
            ],
            [
                'name' => 'Graduation',
                'slug' => 'graduation',
                'description' => 'Frames to celebrate academic achievements',
                'is_active' => true,
            ],
            [
                'name' => 'Holiday',
                'slug' => 'holiday',
                'description' => 'Festive frames for special holidays and celebrations',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        $this->command->info('✅ Categories seeded successfully!');
    }
}
