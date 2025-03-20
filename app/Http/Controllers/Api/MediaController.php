<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\MediaRequest;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Image;

class MediaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Media::query()
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('original_name', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%")
                        ->orWhere('alt_text', 'like', "%{$search}%")
                        ->orWhere('caption', 'like', "%{$search}%");
                });
            })
            ->when($request->type, function ($query, $type) {
                $query->where('mime_type', 'like', $type . '%');
            })
            ->when($request->sort, function ($query, $sort) {
                [$field, $direction] = explode(',', $sort);
                $query->orderBy($field, $direction ?? 'desc');
            }, function ($query) {
                $query->latest();
            });

        $media = $query->paginate($request->input('per_page', 15));

        return response()->json($media);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MediaRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            if ($request->hasFile('file')) {
                $media = Media::handleUpload($request->file('file'), [
                    'title' => $request->input('title'),
                    'alt_text' => $request->input('alt_text'),
                    'caption' => $request->input('caption'),
                    'description' => $request->input('description'),
                    'meta_data' => $request->input('meta_data', [])
                ]);

                // Generate responsive images if it's an image
                if ($media->isImage()) {
                    $media->generateResponsiveImages();
                }
            } else {
                // Handle external URL
                $media = Media::create([
                    'user_id' => auth()->id(),
                    'file_name' => basename($request->url),
                    'original_name' => basename($request->url),
                    'mime_type' => $this->getMimeTypeFromUrl($request->url),
                    'extension' => pathinfo($request->url, PATHINFO_EXTENSION),
                    'path' => $request->url,
                    'disk' => 'external',
                    'title' => $request->input('title'),
                    'alt_text' => $request->input('alt_text'),
                    'caption' => $request->input('caption'),
                    'description' => $request->input('description'),
                    'meta_data' => $request->input('meta_data', []),
                    'status' => 'ready'
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Media uploaded successfully',
                'media' => $media
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error uploading media',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Media $media): JsonResponse
    {
        return response()->json([
            'media' => $media,
            'urls' => [
                'original' => $media->url(),
                'responsive' => $media->isImage() ? collect($media->responsive_images ?? [])->map(function ($path, $size) use ($media) {
                    return $media->responsiveUrl($size);
                }) : null
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MediaRequest $request, Media $media): JsonResponse
    {
        try {
            DB::beginTransaction();

            $media->update([
                'title' => $request->input('title'),
                'alt_text' => $request->input('alt_text'),
                'caption' => $request->input('caption'),
                'description' => $request->input('description'),
                'meta_data' => array_merge($media->meta_data ?? [], $request->input('meta_data', []))
            ]);

            // Handle file replacement if provided
            if ($request->hasFile('file')) {
                // Delete old files
                Storage::disk($media->disk)->delete($media->path);
                if ($media->responsive_images) {
                    foreach ($media->responsive_images as $path) {
                        Storage::disk($media->disk)->delete($path);
                    }
                }

                // Upload new file
                $file = $request->file('file');
                $fileName = $media->generateFileName($file);
                $path = $file->storeAs('media', $fileName, $media->disk);

                $media->update([
                    'file_name' => $fileName,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'extension' => $file->getClientOriginalExtension(),
                    'size' => $file->getSize(),
                    'path' => $path
                ]);

                // Regenerate responsive images if it's an image
                if ($media->isImage()) {
                    $media->generateResponsiveImages();
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Media updated successfully',
                'media' => $media
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error updating media',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Media $media): JsonResponse
    {
        try {
            $media->delete(); // This will handle file deletion through the model's delete method

            return response()->json([
                'message' => 'Media deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting media',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete media files.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:media,id']
        ]);

        try {
            DB::beginTransaction();

            Media::whereIn('id', $request->ids)->get()->each->delete();

            DB::commit();

            return response()->json([
                'message' => 'Media files deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error deleting media files',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get mime type from URL.
     */
    protected function getMimeTypeFromUrl(string $url): string
    {
        $extension = strtolower(pathinfo($url, PATHINFO_EXTENSION));
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            // Add more mime types as needed
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }
}
