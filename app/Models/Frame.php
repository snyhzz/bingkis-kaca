<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Frame extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category_id',
        'image_path',
        'color_code',
        'photo_count',
        'is_active',
        'is_default', // NEW
        'uploaded_by',
        'usage_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean', // NEW
        'photo_count' => 'integer',
        'usage_count' => 'integer',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeCustom($query)
    {
        return $query->where('is_default', false);
    }

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function photoStrips()
    {
        return $this->hasMany(PhotoStrip::class, 'frame_id');
    }

    // Accessors
    public function getImageUrlAttribute()
    {
        if ($this->image_path) {
            return Storage::url($this->image_path);
        }
        return null;
    }

    // Check if frame can be deleted
    public function canBeDeleted(): bool
    {
        // Default frames cannot be deleted
        if ($this->is_default) {
            return false;
        }

        // Custom frames can be deleted if not in use
        return $this->photoStrips()->count() === 0;
    }

    // Check if frame can be edited
    public function canBeEdited(): bool
    {
        // Default frames can only toggle active status and update description
        // Custom frames can be fully edited
        return true;
    }
}
