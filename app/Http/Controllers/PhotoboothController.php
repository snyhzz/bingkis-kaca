<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Frame;
use App\Models\Photo;
use App\Models\PhotoStrip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class PhotoboothController extends Controller
{
    public function index()
    {
        $categories = Category::where('is_active', true)
            ->with(['activeFrames' => function ($query) {
                $query->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        return view('photobooth', compact('categories'));
    }

    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|string', // base64 image data
        ]);

        try {
            // Decode base64 image
            $imageData = $request->input('photo');
            
            // Remove data:image/png;base64, prefix if exists
            if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
                $type = strtolower($type[1]); // jpg, png, gif
            } else {
                return response()->json(['error' => 'Invalid image format'], 400);
            }

            // Decode base64
            $imageData = base64_decode($imageData);

            if ($imageData === false) {
                return response()->json(['error' => 'Base64 decode failed'], 400);
            }

            // Generate unique filename
            $filename = 'photo_' . time() . '_' . Str::random(10) . '.' . $type;

            // Save to storage
            Storage::disk('public')->put('photos/' . $filename, $imageData);

            // Save to database
            $photo = Photo::create([
                'user_id' => auth()->id(),
                'filename' => $filename,
                'original_filename' => $filename,
                'status' => 'pending',
                'ip_address' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'filename' => $filename,
                'url' => Storage::url('photos/' . $filename),
                'photo_id' => $photo->id,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function composeStrip(Request $request)
    {
        $request->validate([
            'photos' => 'required|array|min:1|max:4',
            'photos.*' => 'required|string', // base64 or filename
            'frame_id' => 'nullable|exists:frames,id',
            'photo_count' => 'required|integer|min:2|max:4',
        ]);

        try {
            $photos = $request->input('photos');
            $frameId = $request->input('frame_id');
            $photoCount = $request->input('photo_count');

            // PERBAIKAN: Jika hanya 1 foto (sudah final canvas dari frontend), langsung simpan
            if (count($photos) === 1 && strpos($photos[0], 'data:image') === 0) {
                // Ini adalah final canvas yang sudah termasuk frame dari frontend
                $imageData = $photos[0];
                
                // Remove data:image/png;base64, prefix if exists
                if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
                    $imageData = substr($imageData, strpos($imageData, ',') + 1);
                    $type = strtolower($type[1]);
                } else {
                    return response()->json(['error' => 'Invalid image format'], 400);
                }
                
                // Decode base64
                $imageData = base64_decode($imageData);
                
                if ($imageData === false) {
                    return response()->json(['error' => 'Base64 decode failed'], 400);
                }

                // Generate unique filename for strip
                $stripFilename = 'strip_' . time() . '_' . Str::random(10) . '.png';
                $stripPath = 'strips/' . $stripFilename;

                // Save strip to storage
                Storage::disk('public')->put($stripPath, $imageData);

                // Save to database
                $photoStrip = PhotoStrip::create([
                    'user_id' => auth()->id(),
                    'frame_id' => $frameId,
                    'photo_data' => ['final_canvas'], // Marker bahwa ini final canvas
                    'final_image_path' => $stripPath,
                    'photo_count' => $photoCount,
                    'ip_address' => $request->ip(),
                    'is_saved' => false, // Default belum disimpan
                ]);

                return response()->json([
                    'success' => true,
                    'strip_url' => Storage::url($stripPath),
                    'strip_id' => $photoStrip->id,
                    'download_url' => route('photobooth.download', $photoStrip->id),
                    'is_authenticated' => auth()->check(),
                ]);
            }

            // FALLBACK: Proses multiple photos seperti biasa (jika diperlukan)
            // Load frame if provided
            $frame = null;
            $frameImage = null;
            if ($frameId) {
                $frame = Frame::findOrFail($frameId);
                $frameImage = Image::read(storage_path('app/public/frames/' . $frame->filename));
                
                // Increment frame usage count
                $frame->incrementUsage();
            }

            // Strip dimensions (portrait orientation for photobooth)
            $photoWidth = 800;
            $photoHeight = 600;
            $stripWidth = $photoWidth;
            $stripHeight = $photoHeight * $photoCount;

            // Create blank canvas
            $strip = Image::create($stripWidth, $stripHeight)
                ->fill('ffffff');

            // Process each photo
            foreach ($photos as $index => $photoData) {
                // Decode base64 if needed
                if (preg_match('/^data:image\/(\w+);base64,/', $photoData)) {
                    $photoData = substr($photoData, strpos($photoData, ',') + 1);
                    $photoData = base64_decode($photoData);
                    $photoImage = Image::read($photoData);
                } else {
                    // Assume it's a filename
                    $photoPath = storage_path('app/public/photos/' . $photoData);
                    if (!file_exists($photoPath)) {
                        return response()->json(['error' => 'Photo file not found: ' . $photoData], 404);
                    }
                    $photoImage = Image::read($photoPath);
                }

                // Resize photo to fit
                $photoImage->cover($photoWidth, $photoHeight);

                // Calculate position (vertical stacking)
                $yPosition = $index * $photoHeight;

                // Place photo on strip
                $strip->place($photoImage, 'top-left', 0, $yPosition);

                // Overlay frame if exists
                if ($frameImage) {
                    $frameResized = clone $frameImage;
                    $frameResized->resize($photoWidth, $photoHeight);
                    $strip->place($frameResized, 'top-left', 0, $yPosition);
                }
            }

            // Generate unique filename for strip
            $stripFilename = 'strip_' . time() . '_' . Str::random(10) . '.png';
            $stripPath = 'strips/' . $stripFilename;

            // Save strip
            Storage::disk('public')->put($stripPath, $strip->toPng());

            // Save to database
            $photoStrip = PhotoStrip::create([
                'user_id' => auth()->id(),
                'frame_id' => $frameId,
                'photo_data' => $photos,
                'final_image_path' => $stripPath,
                'photo_count' => $photoCount,
                'ip_address' => $request->ip(),
                'is_saved' => false,
            ]);

            return response()->json([
                'success' => true,
                'strip_url' => Storage::url($stripPath),
                'strip_id' => $photoStrip->id,
                'download_url' => route('photobooth.download', $photoStrip->id),
                'is_authenticated' => auth()->check(),
            ]);

        } catch (\Exception $e) {
            \Log::error('Error composing strip: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Simpan photo strip ke profil user (set is_saved = true)
     */
    public function saveStrip(Request $request, $id)
    {
        // Pastikan user sudah login
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda harus login terlebih dahulu untuk menyimpan photo strip.'
            ], 401);
        }

        try {
            $strip = PhotoStrip::findOrFail($id);
            
            // Update user_id dan is_saved
            $strip->update([
                'user_id' => auth()->id(),
                'is_saved' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Photo strip berhasil disimpan ke profil Anda!',
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Photo strip tidak ditemukan.'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error saving strip: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function download($id)
    {
        try {
            $strip = PhotoStrip::findOrFail($id);

            // Check authorization (optional - allow public download or restrict to owner)
            // Uncomment jika ingin restrict hanya owner yang bisa download
            // if ($strip->user_id && $strip->user_id !== auth()->id()) {
            //     abort(403, 'Unauthorized access');
            // }

            $filePath = storage_path('app/public/' . $strip->final_image_path);

            if (!file_exists($filePath)) {
                abort(404, 'File not found');
            }

            $filename = 'bingkiskaca_' . $strip->id . '_' . time() . '.png';

            return response()->download($filePath, $filename, [
                'Content-Type' => 'image/png',
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Photo strip not found');
        } catch (\Exception $e) {
            \Log::error('Error downloading strip: ' . $e->getMessage());
            abort(500, 'Error downloading file');
        }
    }
}
