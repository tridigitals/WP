<?php

namespace Tests\Feature\Admin;

use Tests\Feature\BaseTestCase;
use App\Models\Media;

class MediaTest extends BaseTestCase
{
    /**
     * Test admin can view media list.
     */
    public function test_admin_can_view_media_list(): void
    {
        $this->createMediaItems(3);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.media.index'));

        $response->assertStatus(200);
        $response->assertViewHas('media');
    }

    /**
     * Test admin can upload image.
     */
    public function test_admin_can_upload_image(): void
    {
        $file = $this->createTestImage('test.jpg', 1200, 800);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.media.store'), [
                'file' => $file,
                'title' => 'Test Image',
                'alt_text' => 'Test alt text'
            ]);

        $response->assertStatus(201);
        
        $media = Media::first();
        
        // Check original file exists
        $this->assertFileExistsInStorage($media->path);

        // Check responsive images were generated
        $this->assertNotNull($media->responsive_images);
        foreach ($media->responsive_images as $size => $path) {
            $this->assertFileExistsInStorage($path);
        }

        $this->assertEquals('Test Image', $media->title);
        $this->assertEquals('Test alt text', $media->alt_text);
    }

    /**
     * Test admin can upload document.
     */
    public function test_admin_can_upload_document(): void
    {
        $file = $this->createTestDocument('document.pdf', 500);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.media.store'), [
                'file' => $file,
                'title' => 'Test Document'
            ]);

        $response->assertStatus(201);
        
        $media = Media::first();
        $this->assertFileExistsInStorage($media->path);
        $this->assertEquals('application/pdf', $media->mime_type);
    }

    /**
     * Test media file size validation.
     */
    public function test_media_file_size_validation(): void
    {
        $file = $this->createTestDocument('large.pdf', 51200); // 50MB+

        $response = $this->actingAs($this->admin)
            ->post(route('admin.media.store'), [
                'file' => $file
            ]);

        $this->assertResponseHasError($response, 'file');
    }

    /**
     * Test media file type validation.
     */
    public function test_media_file_type_validation(): void
    {
        $file = UploadedFile::fake()->create('test.exe', 100);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.media.store'), [
                'file' => $file
            ]);

        $this->assertResponseHasError($response, 'file');
    }

    /**
     * Test admin can update media info.
     */
    public function test_admin_can_update_media_info(): void
    {
        $media = Media::factory()->create();

        $response = $this->actingAs($this->admin)
            ->put(route('admin.media.update', $media), [
                'title' => 'Updated Title',
                'alt_text' => 'Updated alt text',
                'caption' => 'Updated caption'
            ]);

        $response->assertStatus(200);
        $this->assertModelWasUpdated(Media::class, [
            'id' => $media->id,
            'title' => 'Updated Title',
            'alt_text' => 'Updated alt text',
            'caption' => 'Updated caption'
        ]);
    }

    /**
     * Test admin can delete media.
     */
    public function test_admin_can_delete_media(): void
    {
        $file = $this->createTestImage();
        
        $response = $this->actingAs($this->admin)
            ->post(route('admin.media.store'), [
                'file' => $file
            ]);

        $media = Media::first();
        $originalPath = $media->path;
        $responsiveImages = $media->responsive_images;

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.media.destroy', $media));

        $response->assertStatus(200);

        // Check that files are deleted
        $this->assertFileNotExistsInStorage($originalPath);
        foreach ($responsiveImages as $path) {
            $this->assertFileNotExistsInStorage($path);
        }

        $this->assertModelWasDeleted(Media::class, $media->id);
    }

    /**
     * Test admin can bulk delete media.
     */
    public function test_admin_can_bulk_delete_media(): void
    {
        $this->createMediaItems(3);
        $media = Media::all();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.media.bulk-delete'), [
                'ids' => $media->pluck('id')->toArray()
            ]);

        $response->assertStatus(200);
        
        foreach ($media as $item) {
            $this->assertModelWasDeleted(Media::class, $item->id);
        }
    }

    /**
     * Test admin can regenerate image sizes.
     */
    public function test_admin_can_regenerate_image_sizes(): void
    {
        $file = $this->createTestImage();
        
        $response = $this->actingAs($this->admin)
            ->post(route('admin.media.store'), [
                'file' => $file
            ]);

        $media = Media::first();
        
        // Delete responsive images
        foreach ($media->responsive_images as $path) {
            Storage::disk($this->testDisk)->delete($path);
        }

        $response = $this->actingAs($this->admin)
            ->post(route('admin.media.regenerate', $media));

        $response->assertStatus(200);
        
        $media->refresh();
        foreach ($media->responsive_images as $path) {
            $this->assertFileExistsInStorage($path);
        }
    }

    /**
     * Test media browser returns filtered results.
     */
    public function test_media_browser_returns_filtered_results(): void
    {
        Media::factory()->create(['mime_type' => 'image/jpeg']);
        Media::factory()->create(['mime_type' => 'application/pdf']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.media.browser', ['type' => 'image']));

        $response->assertStatus(200);
        $response->assertViewHas('media', function ($media) {
            return $media->count() === 1 && 
                   $media->first()->mime_type === 'image/jpeg';
        });
    }

    /**
     * Test image optimization.
     */
    public function test_image_optimization(): void
    {
        $file = $this->createTestImage();
        
        $response = $this->actingAs($this->admin)
            ->post(route('admin.media.store'), [
                'file' => $file,
                'optimize' => true
            ]);

        $response->assertStatus(201);
        
        $media = Media::first();
        $this->assertArrayHasKey('optimized', $media->meta_data);
        $this->assertTrue($media->meta_data['optimized']);
    }
}