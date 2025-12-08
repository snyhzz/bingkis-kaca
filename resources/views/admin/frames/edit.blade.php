@extends('admin.layouts.app')

@section('title', 'Edit Frame')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>
            <i class="bi bi-pencil me-2"></i> Edit Frame
            @if($frame->is_default)
                <span class="badge bg-warning text-dark ms-2">
                    <i class="bi bi-star-fill"></i> Default Frame
                </span>
            @endif
        </h1>
        <p class="text-muted">Update frame details</p>
    </div>
    <a href="{{ route('admin.frames.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        {{-- Default Frame Notice --}}
        @if($frame->is_default)
        <div class="alert alert-warning border-0 shadow-sm mb-4">
            <div class="d-flex align-items-start">
                <i class="bi bi-shield-lock-fill fs-3 me-3"></i>
                <div>
                    <h5 class="alert-heading mb-2">Protected Default Frame</h5>
                    <p class="mb-1">This is a system default frame with limited edit capabilities:</p>
                    <ul class="mb-0 small">
                        <li><strong>Can Edit:</strong> Name, Description, Active Status</li>
                        <li><strong>Cannot Edit:</strong> Image, Color Code, Photo Count, Category</li>
                    </ul>
                    <p class="mt-2 mb-0 small text-muted">
                        <i class="bi bi-info-circle"></i> 
                        Default frames ensure consistent quality and cannot be deleted.
                    </p>
                </div>
            </div>
        </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="{{ route('admin.frames.update', $frame) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')
                    
                    {{-- Frame Name (Always Editable) --}}
                    <div class="mb-4">
                        <label for="name" class="form-label fw-semibold">
                            Frame Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control form-control-lg @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $frame->name) }}" 
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Description (Always Editable) --}}
                    <div class="mb-4">
                        <label for="description" class="form-label fw-semibold">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" 
                                  name="description" 
                                  rows="3"
                                  placeholder="Brief description of this frame...">{{ old('description', $frame->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Category (Only for Custom Frames) --}}
                    @unless($frame->is_default)
                    <div class="mb-4">
                        <label for="category_id" class="form-label fw-semibold">Category</label>
                        <select class="form-select @error('category_id') is-invalid @enderror" 
                                id="category_id" name="category_id">
                            <option value="">None</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" 
                                    {{ old('category_id', $frame->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    @endunless

                    {{-- Current Image & Upload (Only for Custom Frames) --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Current Image</label>
                        @if($frame->image_path && Storage::disk('public')->exists($frame->image_path))
                            <div class="mb-3">
                                <img src="{{ Storage::url($frame->image_path) }}" 
                                     alt="{{ $frame->name }}" 
                                     class="img-thumbnail" 
                                     style="max-height: 300px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                No image found for this frame.
                            </div>
                        @endif
                        
                        @if($frame->is_default)
                            {{-- Default frames cannot change image --}}
                            <div class="alert alert-info small mb-0">
                                <i class="bi bi-lock-fill me-2"></i>
                                Image cannot be changed for default frames.
                            </div>
                        @else
                            {{-- Custom frames can change image --}}
                            <label for="image_path" class="form-label fw-semibold mt-3">
                                Update Image (Optional)
                            </label>
                            <input type="file" 
                                   class="form-control @error('image_path') is-invalid @enderror" 
                                   id="image_path" 
                                   name="image_path" 
                                   accept="image/png,image/jpeg,image/jpg">
                            @error('image_path')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                Max size: 5MB | Format: PNG, JPG, JPEG | Leave empty to keep current image
                            </small>
                        @endif
                    </div>

                    {{-- Color Code (Only for Custom Frames) --}}
                    @unless($frame->is_default)
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Color Theme <span class="text-danger">*</span></label>
                        <div class="row g-3">
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="color_code" id="color_brown" value="brown" 
                                       {{ old('color_code', $frame->color_code) == 'brown' ? 'checked' : '' }} required>
                                <label class="btn btn-outline-secondary w-100" for="color_brown">
                                    <div class="color-preview mb-1" style="background: linear-gradient(135deg, #522504, #9D6B46);"></div>
                                    Brown
                                </label>
                            </div>
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="color_code" id="color_cream" value="cream" 
                                       {{ old('color_code', $frame->color_code) == 'cream' ? 'checked' : '' }}>
                                <label class="btn btn-outline-secondary w-100" for="color_cream">
                                    <div class="color-preview mb-1" style="background: linear-gradient(135deg, #F5E6D3, #CBA991);"></div>
                                    Cream
                                </label>
                            </div>
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="color_code" id="color_white" value="white" 
                                       {{ old('color_code', $frame->color_code) == 'white' ? 'checked' : '' }}>
                                <label class="btn btn-outline-secondary w-100" for="color_white">
                                    <div class="color-preview mb-1" style="background: white; border: 2px solid #dee2e6;"></div>
                                    White
                                </label>
                            </div>
                        </div>
                        @error('color_code')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    @else
                        {{-- Show current color for default frames (read-only) --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Color Theme (Read-only)</label>
                            <div class="alert alert-secondary mb-0">
                                <span class="badge" style="background: {{ $frame->color_code == 'brown' ? '#6B4423' : ($frame->color_code == 'cream' ? '#CBA991' : '#fff') }}; color: {{ $frame->color_code == 'white' ? '#000' : '#fff' }};">
                                    {{ ucfirst($frame->color_code) }}
                                </span>
                            </div>
                        </div>
                    @endunless

                    {{-- Photo Count (Only for Custom Frames) --}}
                    @unless($frame->is_default)
                    <div class="mb-4">
                        <label for="photo_count" class="form-label fw-semibold">
                            Photo Count <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('photo_count') is-invalid @enderror" 
                                id="photo_count" name="photo_count" required>
                            <option value="2" {{ old('photo_count', $frame->photo_count) == 2 ? 'selected' : '' }}>2 Photos</option>
                            <option value="3" {{ old('photo_count', $frame->photo_count) == 3 ? 'selected' : '' }}>3 Photos</option>
                            <option value="4" {{ old('photo_count', $frame->photo_count) == 4 ? 'selected' : '' }}>4 Photos</option>
                        </select>
                        @error('photo_count')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    @else
                        {{-- Show current photo count for default frames (read-only) --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Photo Count (Read-only)</label>
                            <div class="alert alert-secondary mb-0">
                                <span class="badge bg-primary">{{ $frame->photo_count }} Photos</span>
                            </div>
                        </div>
                    @endunless

                    {{-- Active Status (Always Editable) --}}
                    <div class="mb-4">
                        <div class="form-check form-switch form-check-lg">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="is_active" 
                                   name="is_active" 
                                   value="1"
                                   {{ old('is_active', $frame->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="is_active">
                                <i class="bi bi-eye{{ old('is_active', $frame->is_active) ? '' : '-slash' }} me-2"></i>
                                Active (Available in Photobooth)
                            </label>
                        </div>
                        <small class="text-muted">
                            When inactive, this frame won't appear in the photobooth frame selector
                        </small>
                    </div>

                    {{-- Frame Metadata Display --}}
                    <div class="mb-4">
                        <hr>
                        <h6 class="text-muted mb-3">Frame Information</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <small class="text-muted d-block">Frame Type</small>
                                <strong>{{ $frame->is_default ? 'Default Frame' : 'Custom Frame' }}</strong>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Usage Count</small>
                                <strong>{{ $frame->usage_count }} photo strips</strong>
                            </div>
                            @if($frame->uploadedBy)
                            <div class="col-md-6">
                                <small class="text-muted d-block">Uploaded By</small>
                                <strong>{{ $frame->uploadedBy->name }}</strong>
                            </div>
                            @endif
                            <div class="col-md-6">
                                <small class="text-muted d-block">Last Updated</small>
                                <strong>{{ $frame->updated_at->format('d M Y, H:i') }}</strong>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- Action Buttons --}}
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-save me-2"></i>
                            Update Frame
                        </button>
                        <a href="{{ route('admin.frames.index') }}" class="btn btn-outline-secondary btn-lg">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.color-preview {
    height: 50px;
    border-radius: 8px;
}

.btn-check:checked + .btn-outline-secondary {
    background-color: #522504;
    border-color: #522504;
    color: white;
}

.form-check-lg .form-check-input {
    width: 3rem;
    height: 1.5rem;
}
</style>
@endpush

@push('scripts')
<script>
// Preview image before upload (for custom frames only)
document.getElementById('image_path')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            // You can add image preview logic here if needed
            console.log('New image selected:', file.name);
        };
        reader.readAsDataURL(file);
    }
});

// Toggle active status visual feedback
document.getElementById('is_active')?.addEventListener('change', function() {
    const icon = this.parentElement.querySelector('i');
    if (this.checked) {
        icon.className = 'bi bi-eye me-2';
    } else {
        icon.className = 'bi bi-eye-slash me-2';
    }
});
</script>
@endpush
