@extends('admin.layouts.app')

@section('title', 'Photo Strip #' . $photoStrip->id)

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="bi bi-image me-2"></i> Photo Strip #{{ $photoStrip->id }}</h1>
        <p class="text-muted">View photo strip details</p>
    </div>
    <a href="{{ route('admin.photo-strips.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

<div class="row g-4">
    <!-- Photo Strip Image -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-image me-2"></i> Photo Strip</h5>
            </div>
            <div class="card-body text-center">
                @if($photoStrip->final_image_path && Storage::disk('public')->exists($photoStrip->final_image_path))
                    <img src="{{ Storage::url($photoStrip->final_image_path) }}" 
                         class="img-fluid rounded shadow-sm" 
                         alt="Photo Strip #{{ $photoStrip->id }}"
                         style="max-height: 800px;">
                @else
                    <div class="bg-light d-flex align-items-center justify-content-center rounded" 
                         style="height: 600px;">
                        <div class="text-center">
                            <i class="bi bi-image text-muted" style="font-size: 5rem;"></i>
                            <p class="text-muted mt-3">Image not found</p>
                        </div>
                    </div>
                @endif
            </div>
            <div class="card-footer bg-white">
                <div class="d-flex gap-2 justify-content-center">
                    @if($photoStrip->final_image_path && Storage::disk('public')->exists($photoStrip->final_image_path))
                        <a href="{{ route('photobooth.download', $photoStrip->id) }}" 
                           class="btn btn-primary">
                            <i class="bi bi-download"></i> Download
                        </a>
                    @endif
                    <form action="{{ route('admin.photo-strips.destroy', $photoStrip) }}" 
                          method="POST" 
                          onsubmit="return confirm('Are you sure you want to delete this photo strip?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Details -->
    <div class="col-lg-4">
        <!-- User Info -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-person me-2"></i> User Information</h5>
            </div>
            <div class="card-body">
                @if($photoStrip->user)
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar-circle me-3">
                            {{ strtoupper(substr($photoStrip->user->name, 0, 1)) }}
                        </div>
                        <div>
                            <h6 class="mb-0">{{ $photoStrip->user->name }}</h6>
                            <small class="text-muted">{{ $photoStrip->user->email }}</small>
                        </div>
                    </div>
                    <a href="{{ route('admin.users.show', $photoStrip->user) }}" 
                       class="btn btn-outline-primary btn-sm w-100">
                        <i class="bi bi-eye"></i> View Profile
                    </a>
                @else
                    <p class="text-muted mb-0">
                        <i class="bi bi-person-x"></i> Guest User (Not logged in)
                    </p>
                @endif
            </div>
        </div>

        <!-- Strip Details -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i> Details</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td class="text-muted">ID:</td>
                        <td><strong>#{{ $photoStrip->id }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Photo Count:</td>
                        <td><span class="badge bg-info">{{ $photoStrip->photo_count }} photos</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status:</td>
                        <td>
                            @if($photoStrip->is_saved)
                                <span class="badge bg-success">
                                    <i class="bi bi-bookmark-fill"></i> Saved
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    <i class="bi bi-bookmark"></i> Not Saved
                                </span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Frame:</td>
                        <td>
                            @if($photoStrip->frame)
                                {{ $photoStrip->frame->name }}
                            @else
                                <span class="text-muted">None</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">IP Address:</td>
                        <td><code>{{ $photoStrip->ip_address }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Created:</td>
                        <td>{{ $photoStrip->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Updated:</td>
                        <td>{{ $photoStrip->updated_at->format('M d, Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.avatar-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.3rem;
}
</style>
@endpush
