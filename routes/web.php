<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\FrameController as AdminFrameController;
use App\Http\Controllers\Admin\PhotoController as AdminPhotoController;
use App\Http\Controllers\Admin\PhotoStripController as AdminPhotoStripController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PhotoboothController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/help', [HomeController::class, 'help'])->name('help');

/*
|--------------------------------------------------------------------------
| Photobooth Routes
|--------------------------------------------------------------------------
*/

Route::get('/photobooth', [PhotoboothController::class, 'index'])->name('photobooth');
Route::post('/photobooth/upload', [PhotoboothController::class, 'uploadPhoto'])->name('photobooth.upload');
Route::post('/photobooth/compose', [PhotoboothController::class, 'composeStrip'])->name('photobooth.compose');
Route::post('/photobooth/save/{id}', [PhotoboothController::class, 'saveStrip'])->name('photobooth.save');
Route::get('/photobooth/download/{id}', [PhotoboothController::class, 'download'])->name('photobooth.download');

/*
|--------------------------------------------------------------------------
| Profile Routes (Authenticated Users)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::delete('/profile/strips/{id}', [ProfileController::class, 'deleteStrip'])->name('profile.strips.delete');
});

/*
|--------------------------------------------------------------------------
| Admin Routes (Protected by auth + admin middleware)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    
    // Dashboard
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Users Management
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::post('/users/{user}/block', [AdminUserController::class, 'block'])->name('users.block');
    Route::post('/users/{user}/unblock', [AdminUserController::class, 'unblock'])->name('users.unblock');
    Route::post('/users/{user}/toggle-block', [AdminUserController::class, 'toggleBlock'])->name('users.toggle-block');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    // Categories Management
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::post('/categories/{category}/toggle', [CategoryController::class, 'toggle'])->name('categories.toggle');

    // Frames Management
    Route::resource('frames', AdminFrameController::class)->except(['show']);
    Route::post('/frames/{frame}/toggle', [AdminFrameController::class, 'toggle'])->name('frames.toggle');
    Route::post('/frames/restore-defaults', [AdminFrameController::class, 'restoreDefaults'])->name('frames.restore-defaults'); // NEW ROUTE
    Route::delete('/frames/{frame}/force', [AdminFrameController::class, 'forceDestroy'])->name('frames.force-destroy'); // Optional

    // Photos Moderation
    Route::get('/photos', [AdminPhotoController::class, 'index'])->name('photos.index');
    Route::post('/photos/{photo}/approve', [AdminPhotoController::class, 'approve'])->name('photos.approve');
    Route::post('/photos/{photo}/reject', [AdminPhotoController::class, 'reject'])->name('photos.reject');
    Route::delete('/photos/{photo}', [AdminPhotoController::class, 'destroy'])->name('photos.destroy');

    // Photo Strips Management
    Route::get('/photo-strips', [AdminPhotoStripController::class, 'index'])->name('photo-strips.index');
    Route::get('/photo-strips/{photoStrip}', [AdminPhotoStripController::class, 'show'])->name('photo-strips.show');
    Route::delete('/photo-strips/{photoStrip}', [AdminPhotoStripController::class, 'destroy'])->name('photo-strips.destroy');
});


// Add temporary debug route (remove after debugging)
Route::get('/debug/frame/{frame}', function (App\Models\Frame $frame) {
    return [
        'frame' => $frame,
        'is_default' => $frame->is_default,
        'photo_strips_count' => $frame->photoStrips()->count(),
        'can_delete' => !$frame->is_default && $frame->photoStrips()->count() === 0,
        'image_exists' => Storage::disk('public')->exists($frame->image_path),
        'image_path' => $frame->image_path,
        'full_path' => storage_path('app/public/' . $frame->image_path),
    ];
})->middleware(['auth', 'admin']);

/*
|--------------------------------------------------------------------------
| Auth Routes (Laravel Breeze)
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';
