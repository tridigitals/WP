<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'file_path',
        'mime_type',
        'file_size',
        'alt_text',
        'caption',
        'width',
        'height',
        'uploaded_by'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<string>
     */
    protected $appends = [
        'url',
        'thumbnail_url',
        'is_image'
    ];

    /**
     * Get the user who uploaded the media.
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the full URL to the media file.
     */
    public function getUrlAttribute()
    {
        return Storage::url($this->file_path);
    }

    /**
     * Get the thumbnail URL for the media file.
     */
    public function getThumbnailUrlAttribute()
    {
        if (!$this->is_image) {
            return null;
        }

        $path = pathinfo($this->file_path);
        $thumbnailPath = $path['dirname'] . '/thumbnails/' . $path['basename'];
        
        return Storage::exists($thumbnailPath)
            ? Storage::url($thumbnailPath)
            : $this->url;
    }

    /**
     * Check if the file is an image.
     */
    public function getIsImageAttribute()
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Get human-readable file size.
     */
    public function getHumanFileSizeAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get image dimensions as string.
     */
    public function getDimensionsAttribute()
    {
        if (!$this->is_image) {
            return null;
        }

        return "{$this->width}x{$this->height}";
    }

    /**
     * Delete the media file from storage when the model is deleted.
     */
    protected static function booted()
    {
        static::deleting(function ($media) {
            Storage::delete($media->file_path);
            
            // Delete thumbnail if exists
            if ($media->is_image) {
                $path = pathinfo($media->file_path);
                $thumbnailPath = $path['dirname'] . '/thumbnails/' . $path['basename'];
                if (Storage::exists($thumbnailPath)) {
                    Storage::delete($thumbnailPath);
                }
            }
        });
    }
}
