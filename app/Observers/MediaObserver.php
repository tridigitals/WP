<?php

namespace App\Observers;

use App\Models\Media;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Image;

class MediaObserver
{
    /**
     * Handle the Media "creating" event.
     */
    public function creating(Media $media): void
    {
        // Set default disk if not specified
        if (!$media->disk) {
            $media->disk = config('cms.media.disk', 'public');
        }
    }

    /**
     * Handle the Media "created" event.
     */
    public function created(Media $media): void
    {
        // Generate responsive images if it's an image
        if ($media->isImage()) {
            $this->generateResponsiveImages($media);
        }

        // Clear caches
        $this->clearCaches();
    }

    /**
     * Handle the Media "updating" event.
     */
    public function updating(Media $media): void
    {
        // If file is being replaced, delete old files
        if ($media->isDirty('path')) {
            $this->deleteFiles($media->getOriginal('path'), $media->getOriginal('responsive_images'));
        }
    }

    /**
     * Handle the Media "updated" event.
     */
    public function updated(Media $media): void
    {
        // Regenerate responsive images if file changed and is image
        if ($media->wasChanged('path') && $media->isImage()) {
            $this->generateResponsiveImages($media);
        }

        // Clear caches
        $this->clearCaches();
    }

    /**
     * Handle the Media "deleting" event.
     */
    public function deleting(Media $media): void
    {
        // Delete all associated files
        $this->deleteFiles($media->path, $media->responsive_images);
    }

    /**
     * Handle the Media "deleted" event.
     */
    public function deleted(Media $media): void
    {
        // Clear caches
        $this->clearCaches();
    }

    /**
     * Handle the Media "restored" event.
     */
    public function restored(Media $media): void
    {
        // Clear caches
        $this->clearCaches();
    }

    /**
     * Handle the Media "force deleted" event.
     */
    public function forceDeleted(Media $media): void
    {
        // Delete all associated files
        $this->deleteFiles($media->path, $media->responsive_images);

        // Clear caches
        $this->clearCaches();
    }

    /**
     * Generate responsive images for the media.
     */
    private function generateResponsiveImages(Media $media): void
    {
        if (!config('cms.media.image_sizes')) {
            return;
        }

        try {
            $image = Image::make(Storage::disk($media->disk)->path($media->path));
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

            // Update media record with responsive image paths
            $media->responsive_images = $responsiveImages;
            $media->saveQuietly();

            // Optimize images if enabled
            if (config('cms.media.optimize_images', true)) {
                $this->optimizeImages($media);
            }

        } catch (\Exception $e) {
            \Log::error("Error generating responsive images for media {$media->id}: " . $e->getMessage());
        }
    }

    /**
     * Delete files associated with the media.
     */
    private function deleteFiles(?string $path, ?array $responsiveImages): void
    {
        if ($path) {
            Storage::disk(config('cms.media.disk'))->delete($path);
        }

        if ($responsiveImages) {
            foreach ($responsiveImages as $path) {
                Storage::disk(config('cms.media.disk'))->delete($path);
            }
        }
    }

    /**
     * Optimize images using available optimization libraries.
     */
    private function optimizeImages(Media $media): void
    {
        // Implement image optimization using libraries like jpegoptim, optipng, etc.
        // This is a placeholder for actual implementation
        try {
            // Optimize original image
            if ($media->mime_type === 'image/jpeg') {
                exec("jpegoptim --strip-all --max=85 " . Storage::disk($media->disk)->path($media->path));
            } elseif ($media->mime_type === 'image/png') {
                exec("optipng -o2 " . Storage::disk($media->disk)->path($media->path));
            }

            // Optimize responsive images
            if ($media->responsive_images) {
                foreach ($media->responsive_images as $path) {
                    if ($media->mime_type === 'image/jpeg') {
                        exec("jpegoptim --strip-all --max=85 " . Storage::disk($media->disk)->path($path));
                    } elseif ($media->mime_type === 'image/png') {
                        exec("optipng -o2 " . Storage::disk($media->disk)->path($path));
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::warning("Image optimization failed for media {$media->id}: " . $e->getMessage());
        }
    }

    /**
     * Clear relevant caches.
     */
    private function clearCaches(): void
    {
        // Clear media cache
        Cache::tags(['media'])->flush();

        // Clear media library cache
        Cache::forget('media_library');

        // Clear image sizes cache
        Cache::forget('media_sizes');
    }
}