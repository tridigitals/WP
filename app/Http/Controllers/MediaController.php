<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class MediaController extends Controller
{
    /**
     * Store a newly created media in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpeg,png,jpg,gif|max:2048',
            'alt_text' => 'nullable|string|max:255',
            'caption' => 'nullable|string'
        ]);

        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        
        // Store original file
        $filePath = $file->storeAs('uploads', $fileName, 'public');
        
        // Generate and store thumbnail
        $image = Image::make($file);
        $image->fit(300, 300, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        
        // Create thumbnails directory if it doesn't exist
        $thumbnailPath = 'uploads/thumbnails';
        if (!Storage::disk('public')->exists($thumbnailPath)) {
            Storage::disk('public')->makeDirectory($thumbnailPath);
        }
        
        // Save thumbnail
        $thumbnailName = 'thumb_' . $fileName;
        $thumbnailFullPath = $thumbnailPath . '/' . $thumbnailName;
        Storage::disk('public')->put($thumbnailFullPath, (string) $image->encode());

        // Create media record
        $media = Media::create([
            'name' => $fileName,
            'file_path' => $filePath,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'alt_text' => $request->alt_text,
            'caption' => $request->caption,
            'width' => $image->width(),
            'height' => $image->height(),
            'uploaded_by' => auth()->id()
        ]);

        return response()->json([
            'message' => 'Media uploaded successfully',
            'media' => $media
        ]);
    }
}