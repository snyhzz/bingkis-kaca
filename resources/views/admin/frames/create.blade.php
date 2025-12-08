@extends('admin.layouts.app')

@section('title', 'Add New Frame')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="bi bi-plus-circle me-2"></i> Add New Frame</h1>
        <p class="text-muted">Create a new photo booth frame</p>
    </div>
    <a href="{{ route('admin.frames.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

{{-- Display Errors --}}
@if ($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <h5 class="alert-heading"><i class="bi bi-exclamation-triangle me-2"></i> Validation Errors:</h5>
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="{{ route('admin.frames.store') }}" 
                      method="POST" 
                      enctype="multipart/form-data" 
                      id="frameForm">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="name" class="form-label fw-semibold">
                            Frame Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control form-control-lg @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}" 
                               placeholder="e.g., Brown Wedding Frame"
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label fw-semibold">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" 
                                  name="description" 
                                  rows="3"
                                  placeholder="Brief description of this frame...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="category_id" class="form-label fw-semibold">Category</label>
                            <select class="form-select @error('category_id') is-invalid @enderror" 
                                    id="category_id" name="category_id">
                                <option value="">None</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-4">
                            <label for="photo_count" class="form-label fw-semibold">
                                Photo Count <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('photo_count') is-invalid @enderror" 
                                    id="photo_count" name="photo_count" required>
                                <option value="">Select...</option>
                                <option value="2" {{ old('photo_count') == 2 ? 'selected' : '' }}>2 Photos</option>
                                <option value="3" {{ old('photo_count') == 3 ? 'selected' : '' }}>3 Photos</option>
                                <option value="4" {{ old('photo_count', 4) == 4 ? 'selected' : '' }}>4 Photos</option>
                            </select>
                            @error('photo_count')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Number of photos this frame can hold</small>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="color_code" class="form-label fw-semibold">
                            Color Theme <span class="text-danger">*</span>
                        </label>
                        <div class="row g-3">
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="color_code" id="color_brown" value="brown" {{ old('color_code', 'brown') == 'brown' ? 'checked' : '' }} required>
                                <label class="btn btn-outline-secondary w-100" for="color_brown">
                                    <div class="color-preview mb-1" style="background: linear-gradient(135deg, #522504, #9D6B46);"></div>
                                    Brown
                                </label>
                            </div>
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="color_code" id="color_cream" value="cream" {{ old('color_code') == 'cream' ? 'checked' : '' }}>
                                <label class="btn btn-outline-secondary w-100" for="color_cream">
                                    <div class="color-preview mb-1" style="background: linear-gradient(135deg, #F5E6D3, #CBA991);"></div>
                                    Cream
                                </label>
                            </div>
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="color_code" id="color_white" value="white" {{ old('color_code') == 'white' ? 'checked' : '' }}>
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

                    <div class="mb-4">
                        <label for="image_path" class="form-label fw-semibold">
                            Frame Image <span class="text-danger">*</span>
                        </label>
                        <input type="file" 
                               class="form-control @error('image_path') is-invalid @enderror" 
                               id="image_path" 
                               name="image_path" 
                               accept="image/png,image/jpeg,image/jpg" 
                               required
                               onchange="previewImage(this)">
                        @error('image_path')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Max size: 5MB | Format: PNG, JPG, JPEG</small>
                        
                        <div id="imagePreview" class="mt-3 d-none">
                            <p class="text-success"><i class="bi bi-check-circle me-1"></i> Image selected:</p>
                            <img id="preview" src="" alt="Preview" class="img-thumbnail" style="max-height: 300px;">
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="is_active" 
                                   name="is_active" 
                                   value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="is_active">
                                Active (Available in Photobooth)
                            </label>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                            <i class="bi bi-save me-2"></i>
                            <span id="btnText">Create Frame</span>
                            <span id="btnLoading" class="d-none">
                                <span class="spinner-border spinner-border-sm me-2"></span>
                                Creating...
                            </span>
                        </button>
                        <a href="{{ route('admin.frames.index') }}" class="btn btn-outline-secondary btn-lg">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Help Card -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm bg-light">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-info-circle me-2"></i> Frame Guidelines</h5>
                
                <h6 class="mt-3">Image Requirements:</h6>
                <ul class="small">
                    <li>Format: PNG (recommended) or JPG</li>
                    <li>Size: Max 5MB</li>
                    <li>Dimensions: 1200x1800px recommended</li>
                    <li>Use transparent background for photo areas</li>
                </ul>

                <h6 class="mt-3">Photo Count:</h6>
                <ul class="small">
                    <li><strong>2 Photos:</strong> 2 vertical slots</li>
                    <li><strong>3 Photos:</strong> 2 top + 1 bottom</li>
                    <li><strong>4 Photos:</strong> 2x2 grid</li>
                </ul>

                <div class="alert alert-warning small mt-3">
                    <i class="bi bi-lightbulb me-1"></i>
                    <strong>Tip:</strong> Create frames with transparent areas where photos will be placed.
                </div>
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
</style>
@endpush

@push('scripts')
<script>
function previewImage(input) {
    const preview = document.getElementById('preview');
    const previewContainer = document.getElementById('imagePreview');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            previewContainer.classList.remove('d-none');
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}

// Form submission handler
document.getElementById('frameForm').addEventListener('submit', function() {
    const submitBtn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');
    const btnLoading = document.getElementById('btnLoading');
    
    submitBtn.disabled = true;
    btnText.classList.add('d-none');
    btnLoading.classList.remove('d-none');
});
</script>
@endpush
