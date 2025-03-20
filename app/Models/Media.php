<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Image;

class Media extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'file_name',
        'original_name',
        'mime_type',
        'extension',
        'size',
        'path',
        'disk',
        'title',
        'alt_text',
        'caption',
        'description',
        'meta_data',
        'responsive_images',
        'status'
    ];

    protected $casts = [
        'meta_data' => 'array',
        'responsive_images' => 'array',
        'size' => 'integer'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // File handling
    public static function handleUpload(UploadedFile $file, array $attributes = []): self
    {
        $instance = new static();
        
        $fileName = $instance->generateFileName($file);
        $path = $file->storeAs('media', $fileName, 'public');

        return $instance->create(array_merge([
            'user_id' => auth()->id(),
            'file_name' => $fileName,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'extension' => $file->getClientOriginalExtension(),
            'size' => $file->getSize(),
            'path' => $path,
            'disk' => 'public',
            'status' => 'ready'
        ], $attributes));
    }

    protected function generateFileName(UploadedFile $file): string
    {
        return Str::random(40) . '.' . $file->getClientOriginalExtension();
    }

    // Image handling
    public function generateResponsiveImages(): void
    {
        if (!$this->isImage()) {
            return;
        }

        $sizes = [
            'thumb' => [150, 150],
            'small' => [300, 300],
            'medium' => [600, 600],
            'large' => [1200, 1200]
        ];

        $responsiveImages = [];
        foreach ($sizes as $size => $dimensions) {
            $responsiveImages[$size] = $this->createResizedImage($dimensions[0], $dimensions[1]);
        }

        $this->update(['responsive_images' => $responsiveImages]);
    }

    protected function createResizedImage(int $width, int $height): string
    {
        $image = Image::make(Storage::disk($this->disk)->path($this->path));
        $fileName = Str::beforeLast($this->file_name, '.') . "-{$width}x{$height}." . $this->extension;
        $resizedPath = "media/responsive/{$fileName}";

        $image->fit($width, $height, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        Storage::disk($this->disk)->put($resizedPath, $image->stream());

        return $resizedPath;
    }

    // Helpers
    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function responsiveUrl(string $size): ?string
    {
        if (!isset($this->responsive_images[$size])) {
            return null;
        }

        return Storage::disk($this->disk)->url($this->responsive_images[$size]);
    }

    public function isImage(): bool
    {
        return Str::startsWith($this->mime_type, 'image/');
    }

    public function delete(): ?bool
    {
        if (parent::delete()) {
            Storage::disk($this->disk)->delete($this->path);
            
            if ($this->responsive_images) {
                foreach ($this->responsive_images as $path) {
                    Storage::disk($this->disk)->delete($path);
                }
            }

            return true;
        }

        return false;
    }

    public function toArray()
    {
        return array_merge(parent::toArray(), [
            'url' => $this->url(),
            'responsive_urls' => collect($this->responsive_images ?? [])->mapWithKeys(function ($path, $size) {
                return [$size => Storage::disk($this->disk)->url($path)];
            })->all()
        ]);
    }
}
