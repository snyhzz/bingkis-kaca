<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\PhotoStrip;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(): View
    {
        // Ambil user yang sedang login
        $user = Auth::user();
        
        // Ambil photo strips yang disimpan oleh user
        $photoStrips = PhotoStrip::where('user_id', $user->id)
            ->where('is_saved', true)  // Hanya yang disimpan
            ->with('frame')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('profile.index', compact('user', 'photoStrips'));
    }

    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function deleteStrip($id): RedirectResponse
    {
        $strip = PhotoStrip::where('user_id', Auth::id())
            ->where('id', $id)
            ->where('is_saved', true)  // Tambahkan filter ini untuk keamanan
            ->firstOrFail();

        $strip->delete();

        return Redirect::route('profile.index')->with('status', 'Photo strip berhasil dihapus!');
    }
}
