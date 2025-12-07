@extends('admin.layouts.app')

@section('title', 'Photo Strips')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="bi bi-images me-2"></i> Photo Strips</h1>
        <p class="text-muted">Manage all photo strips</p>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.photo-strips.index') }}">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Search by user name..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="photo_count" class="form-select">
                        <option value="">All Photos</option>
                        <option value="2" {{ request('photo_count') == '2' ? 'selected' : '' }}>2 Photos</option>
                        <option value="3" {{ request('photo_count') == '3' ? 'selected' : '' }}>3 Photos</option>
                        <option value="4" {{ request('photo_count') == '4' ? 'selected' : '' }}>4 Photos</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="saved" class="form-select">
                        <option value="">All Status</option>
                        <option value="yes" {{ request('saved') === 'yes' ? 'selected' : '' }}>Saved</option>
                        <option value="no" {{ request('saved') === 'no' ? 'selected' : '' }}>Not Saved</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control" 
                           placeholder="From" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Photo Strips Grid -->
<div class="row g-4">
    @forelse($photoStrips as $strip)
    <div class="col-md-4">
        <div class="card h-100">
            <div class="position-relative">
                @if($strip->final_image_path && Storage::disk('public')->exists($strip->final_image_path))
                    <img src="{{ Storage::url($strip->final_image_path) }}" 
                         class="card-img-top" 
                         alt="Photo Strip #{{ $strip->id }}"
                         style="height: 300px; object-fit: cover;">
                @else
                    <div class="bg-light d-flex align-items-center justify-content-center" 
                         style="height: 300px;">
                        <i class="bi bi-image text-muted" style="font-size: 4rem;"></i>
                    </div>
                @endif
                
                <div class="position-absolute top-0 end-0 m-2">
                    @if($strip->is_saved)
                        <span class="badge bg-success"><i class="bi bi-bookmark-fill"></i> Saved</span>
                    @else
                        <span class="badge bg-secondary"><i class="bi bi-bookmark"></i> Not Saved</span>
                    @endif
                </div>
            </div>
            
            <div class="card-body">
                <h6 class="card-title">Strip #{{ $strip->id }}</h6>
                <p class="card-text text-muted mb-2">
                    <i class="bi bi-person"></i>
                    @if($strip->user)
                        <a href="{{ route('admin.users.show', $strip->user) }}">
                            {{ $strip->user->name }}
                        </a>
                    @else
                        Guest
                    @endif
                </p>
                <p class="card-text">
                    <span class="badge bg-info">{{ $strip->photo_count }} photos</span>
                    <span class="badge bg-secondary">{{ $strip->created_at->diffForHumans() }}</span>
                </p>
            </div>
            
            <div class="card-footer bg-white border-top-0">
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.photo-strips.show', $strip) }}" 
                       class="btn btn-sm btn-outline-primary flex-fill">
                        <i class="bi bi-eye"></i> View
                    </a>
                    <form action="{{ route('admin.photo-strips.destroy', $strip) }}" 
                          method="POST" 
                          onsubmit="return confirm('Are you sure?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-images text-muted" style="font-size: 4rem;"></i>
                <h4 class="mt-3">No photo strips found</h4>
                <p class="text-muted">Photo strips will appear here when users create them.</p>
            </div>
        </div>
    </div>
    @endforelse
</div>

<!-- Pagination -->
@if($photoStrips->hasPages())
<div class="mt-4">
    {{ $photoStrips->links() }}
</div>
@endif
@endsection
