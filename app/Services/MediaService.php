<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Image;

class MediaService
{
    /**
     * Upload and create a new media record.
     */
    public function upload(UploadedFile $file, array $attributes = []): Media
    {
        // Validate file type
        $this->validateFileType($file);

        // Generate unique filename
        $fileName = $this->generateUniqueFileName($file);
        
        // Store the file
        $path = $file->storeAs(
            config('cms.media.path', 'media'),
            $fileName,
            config('cms.media.disk', 'public')
        );

        // Create media record
        $media = Media::create(array_merge([
            'user_id' => auth()->id(),
            'file_name' => $fileName,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'extension' => $file->getClientOriginalExtension(),
            'size' => $file->getSize(),
            'path' => $path,
            'disk' => config('cms.media.disk', 'public'),
            'status' => 'ready'
        ], $attributes));

        // Process image if necessary
        if ($this->isImage($file)) {
            $this->processImage($media);
        }

        return $media;
    }

    /**
     * Process an image file.
     */
    protected function processImage(Media $media): void
    {
        $image = Image::make(Storage::disk($media->disk)->path($media->path));
        $meta = [
            'width' => $image->width(),
            'height' => $image->height(),
            'ratio' => $image->width() / $image->height()
        ];

        // Update media metadata
        $media->update(['meta_data' => array_merge($media->meta_data ?? [], $meta)]);

        // Generate responsive versions if enabled
        if (config('cms.media.image_sizes')) {
            $this->generateResponsiveImages($media, $image);
        }

        // Optimize original image if enabled
        if (config('cms.media.optimize_images', true)) {
            $this->optimizeImage($media);
        }
    }

    /**
     * Generate responsive image versions.
     */
    protected function generateResponsiveImages(Media $media, $image): void
    {
        $responsiveImages = [];

        foreach (config('cms.media.image_sizes') as $size => [$width, $height]) {
            $fileName = pathinfo($media->file_name, PATHINFO_FILENAME) . 
                       "-{$width}x{$height}." . 
                       $media->extension;
            
            $resizedPath = "media/responsive/{$fileName}";

            // Create resized image
            $resized = clone $image;
            $resized->fit($width, $height, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            // Save resized image
            Storage::disk($media->disk)->put(
                $resizedPath,
                $resized->encode($media->extension, 90)
            );

            $responsiveImages[$size] = $resizedPath;
        }

        $media->update(['responsive_images' => $responsiveImages]);
    }

    /**
     * Optimize an image file.
     */
    protected function optimizeImage(Media $media): void
    {
        $path = Storage::disk($media->disk)->path($media->path);

        if ($media->mime_type === 'image/jpeg') {
            exec("jpegoptim --strip-all --max=85 {$path}");
        } elseif ($media->mime_type === 'image/png') {
            exec("optipng -o2 {$path}");
        }

        // Update file size after optimization
        $media->update(['size' => filesize($path)]);
    }

    /**
     * Validate file type.
     */
    protected function validateFileType(UploadedFile $file): void
    {
        $allowedTypes = collect(config('cms.media.allowed_file_types', []))
            ->flatten()
            ->map(fn($ext) => '.' . $ext)
            ->all();

        if (!in_array('.' . $file->getClientOriginalExtension(), $allowedTypes)) {
            throw new \Exception('File type not allowed');
        }
    }

    /**
     * Generate a unique filename.
     */
    protected function generateUniqueFileName(UploadedFile $file): string
    {
        return Str::random(40) . '.' . $file->getClientOriginalExtension();
    }

    /**
     * Check if file is an image.
     */
    protected function isImage(UploadedFile $file): bool
    {
        return Str::startsWith($file->getMimeType(), 'image/');
    }

    /**
     * Delete a media file and its derivatives.
     */
    public function delete(Media $media): bool
    {
        // Delete original file
        Storage::disk($media->disk)->delete($media->path);

        // Delete responsive images if they exist
        if ($media->responsive_images) {
            foreach ($media->responsive_images as $path) {
                Storage::disk($media->disk)->delete($path);
            }
        }

        return $media->delete();
    }

    /**
     * Get human readable file size.
     */
    public function getHumanFileSize(int $bytes, int $decimals = 2): string
    {
        $size = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . ' ' . $size[$factor];
    }
}