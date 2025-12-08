<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Frame;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class FrameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin user
        $admin = User::where('is_admin', true)->first();
        
        // Get categories
        $cuteCategory = Category::where('slug', 'cute')->first();
        $classicCategory = Category::where('slug', 'classic')->first();
        $weddingCategory = Category::where('slug', 'wedding')->first();
        $birthdayCategory = Category::where('slug', 'birthday')->first();

        // Create sample frames with new structure
        $frames = [
            // Brown frames
            [
                'name' => 'Brown Classic 4 Photos',
                'description' => 'Classic brown frame perfect for any occasion',
                'category_id' => $classicCategory?->id,
                'image_path' => 'frames/frame_brown_4_classic.png',
                'color_code' => 'brown',
                'photo_count' => 4,
                'is_active' => true,
                'uploaded_by' => $admin?->id,
                'usage_count' => 0,
            ],
            [
                'name' => 'Brown Hearts 4 Photos',
                'description' => 'Cute brown frame with hearts border',
                'category_id' => $cuteCategory?->id,
                'image_path' => 'frames/frame_brown_4_hearts.png',
                'color_code' => 'brown',
                'photo_count' => 4,
                'is_active' => true,
                'uploaded_by' => $admin?->id,
                'usage_count' => 0,
            ],
            [
                'name' => 'Brown Wedding 3 Photos',
                'description' => 'Elegant brown frame for wedding moments',
                'category_id' => $weddingCategory?->id,
                'image_path' => 'frames/frame_brown_3_wedding.png',
                'color_code' => 'brown',
                'photo_count' => 3,
                'is_active' => true,
                'uploaded_by' => $admin?->id,
                'usage_count' => 0,
            ],

            // Cream frames
            [
                'name' => 'Cream Elegant 4 Photos',
                'description' => 'Soft cream frame with elegant design',
                'category_id' => $weddingCategory?->id,
                'image_path' => 'frames/frame_cream_4_elegant.png',
                'color_code' => 'cream',
                'photo_count' => 4,
                'is_active' => true,
                'uploaded_by' => $admin?->id,
                'usage_count' => 0,
            ],
            [
                'name' => 'Cream Birthday 4 Photos',
                'description' => 'Fun cream frame for birthday celebrations',
                'category_id' => $birthdayCategory?->id,
                'image_path' => 'frames/frame_cream_4_birthday.png',
                'color_code' => 'cream',
                'photo_count' => 4,
                'is_active' => true,
                'uploaded_by' => $admin?->id,
                'usage_count' => 0,
            ],
            [
                'name' => 'Cream Simple 2 Photos',
                'description' => 'Minimalist cream frame for 2 photos',
                'category_id' => $classicCategory?->id,
                'image_path' => 'frames/frame_cream_2_simple.png',
                'color_code' => 'cream',
                'photo_count' => 2,
                'is_active' => true,
                'uploaded_by' => $admin?->id,
                'usage_count' => 0,
            ],

            // White frames
            [
                'name' => 'White Clean 4 Photos',
                'description' => 'Clean white frame with modern look',
                'category_id' => $classicCategory?->id,
                'image_path' => 'frames/frame_white_4_clean.png',
                'color_code' => 'white',
                'photo_count' => 4,
                'is_active' => true,
                'uploaded_by' => $admin?->id,
                'usage_count' => 0,
            ],
            [
                'name' => 'White Minimalist 3 Photos',
                'description' => 'Simple white frame for 3 photos',
                'category_id' => $classicCategory?->id,
                'image_path' => 'frames/frame_white_3_minimal.png',
                'color_code' => 'white',
                'photo_count' => 3,
                'is_active' => true,
                'uploaded_by' => $admin?->id,
                'usage_count' => 0,
            ],
        ];

        foreach ($frames as $frameData) {
            Frame::create($frameData);
        }

        // Create placeholder PNG files
        $this->createPlaceholderFrames($frames);

        $this->command->info('✅ Frames seeded successfully!');
        $this->command->warn('⚠️  Placeholder frames created. Replace with real images via admin panel.');
    }

    /**
     * Create placeholder frame files
     */
    private function createPlaceholderFrames(array $frames)
    {
        $storagePath = storage_path('app/public/frames');
        
        // Ensure directory exists
        if (!File::exists($storagePath)) {
            File::makeDirectory($storagePath, 0755, true);
        }

        foreach ($frames as $frame) {
            $filename = basename($frame['image_path']);
            $filePath = $storagePath . '/' . $filename;
            
            // Only create if doesn't exist
            if (!File::exists($filePath)) {
                // Create frame based on photo count and color
                $this->createFramePNG(
                    $filePath, 
                    $frame['color_code'],
                    $frame['photo_count']
                );
            }
        }
    }

    /**
     * Create a frame PNG with color and layout
     */
    private function createFramePNG($filePath, $colorCode, $photoCount)
    {
        // Frame dimensions (4R photo size: 1200x1800px portrait)
        $width = 1200;
        $height = 1800;
        
        // Create image
        $image = imagecreatetruecolor($width, $height);
        
        // Enable alpha blending
        imagealphablending($image, false);
        imagesavealpha($image, true);
        
        // Create transparent background
        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefill($image, 0, 0, $transparent);
        
        // Get color based on color code
        $frameColor = $this->getFrameColor($image, $colorCode);
        
        // Border width
        $borderWidth = 40;
        $innerBorderWidth = 10;
        
        // Outer border
        imagefilledrectangle($image, 0, 0, $width, $borderWidth, $frameColor);
        imagefilledrectangle($image, 0, $height - $borderWidth, $width, $height, $frameColor);
        imagefilledrectangle($image, 0, 0, $borderWidth, $height, $frameColor);
        imagefilledrectangle($image, $width - $borderWidth, 0, $width, $height, $frameColor);
        
        // Calculate photo positions based on count
        $photoHeight = ($height - ($borderWidth * 2) - ($innerBorderWidth * ($photoCount - 1))) / $photoCount;
        
        for ($i = 0; $i < $photoCount; $i++) {
            $y = $borderWidth + ($photoHeight * $i) + ($innerBorderWidth * $i);
            
            // Draw divider between photos
            if ($i > 0) {
                imagefilledrectangle(
                    $image, 
                    $borderWidth, 
                    $y - $innerBorderWidth, 
                    $width - $borderWidth, 
                    $y, 
                    $frameColor
                );
            }
        }
        
        // Save as PNG
        imagepng($image, $filePath);
        imagedestroy($image);
    }

    /**
     * Get frame color based on color code
     */
    private function getFrameColor($image, $colorCode)
    {
        switch ($colorCode) {
            case 'brown':
                return imagecolorallocate($image, 82, 37, 4); // #522504
            case 'cream':
                return imagecolorallocate($image, 203, 169, 145); // #CBA991
            case 'white':
                return imagecolorallocate($image, 255, 255, 255); // #FFFFFF
            default:
                return imagecolorallocate($image, 82, 37, 4);
        }
    }
}
