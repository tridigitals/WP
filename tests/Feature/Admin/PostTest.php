<?php

namespace Tests\Feature\Admin;

use Tests\Feature\BaseTestCase;
use App\Models\Post;
use App\Models\Tag;
use Carbon\Carbon;

class PostTest extends BaseTestCase
{
    /**
     * Test admin can view posts list.
     */
    public function test_admin_can_view_posts_list(): void
    {
        $posts = Post::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.posts.index'));

        $response->assertStatus(200);
        $response->assertViewHas('posts');
    }

    /**
     * Test admin can create post.
     */
    public function test_admin_can_create_post(): void
    {
        $tags = Tag::factory()->count(2)->create();
        $postData = array_merge(
            $this->getValidModelData('post'),
            ['tags' => $tags->pluck('id')->toArray()]
        );

        $response = $this->actingAs($this->admin)
            ->post(route('admin.posts.store'), $postData);

        $response->assertStatus(302);
        $this->assertModelWasCreated(Post::class, [
            'title' => $postData['title']
        ]);

        $post = Post::where('title', $postData['title'])->first();
        $this->assertCount(2, $post->tags);
        $this->assertHasMeta($post);
    }

    /**
     * Test admin can update post.
     */
    public function test_admin_can_update_post(): void
    {
        $post = $this->createPost();
        
        $updatedData = array_merge(
            $this->getValidModelData('post'),
            ['title' => 'Updated Post Title']
        );

        $response = $this->actingAs($this->admin)
            ->put(route('admin.posts.update', $post), $updatedData);

        $response->assertStatus(302);
        $this->assertModelWasUpdated(Post::class, [
            'id' => $post->id,
            'title' => 'Updated Post Title'
        ]);
    }

    /**
     * Test admin can publish post.
     */
    public function test_admin_can_publish_post(): void
    {
        $post = $this->createPostWithStatus('draft');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.posts.publish', $post));

        $response->assertStatus(200);
        $this->assertModelWasUpdated(Post::class, [
            'id' => $post->id,
            'status' => 'published'
        ]);
        $this->assertNotNull($post->fresh()->published_at);
    }

    /**
     * Test admin can schedule post.
     */
    public function test_admin_can_schedule_post(): void
    {
        $post = $this->createPostWithStatus('draft');
        $publishDate = now()->addDays(5);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.posts.schedule', $post), [
                'published_at' => $publishDate
            ]);

        $response->assertStatus(200);
        $this->assertModelWasUpdated(Post::class, [
            'id' => $post->id,
            'status' => 'scheduled'
        ]);
        $this->assertEquals(
            $publishDate->format('Y-m-d H:i'),
            $post->fresh()->published_at->format('Y-m-d H:i')
        );
    }

    /**
     * Test admin can delete post.
     */
    public function test_admin_can_delete_post(): void
    {
        $post = $this->createPost();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.posts.destroy', $post));

        $response->assertStatus(302);
        $this->assertModelWasDeleted(Post::class, $post->id);
    }

    /**
     * Test post validation.
     */
    public function test_post_validation(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.posts.store'), []);

        $this->assertResponseHasError($response, ['title', 'content']);
    }

    /**
     * Test post slug uniqueness.
     */
    public function test_post_slug_must_be_unique(): void
    {
        $existingPost = $this->createPost(['slug' => 'test-post']);
        
        $response = $this->actingAs($this->admin)
            ->post(route('admin.posts.store'), [
                'title' => 'Test Post',
                'slug' => 'test-post',
                'content' => 'Test content',
                'category_id' => $existingPost->category_id,
                'status' => 'draft'
            ]);

        $this->assertResponseHasError($response, 'slug');
    }

    /**
     * Test post relationships.
     */
    public function test_post_relationships(): void
    {
        $post = $this->createPost();
        $this->assertPostHasRelationships($post);
    }

    /**
     * Test bulk post actions.
     */
    public function test_bulk_post_actions(): void
    {
        $posts = collect([
            $this->createPostWithStatus('draft'),
            $this->createPostWithStatus('draft'),
            $this->createPostWithStatus('draft')
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.posts.bulk-action'), [
                'action' => 'publish',
                'posts' => $posts->pluck('id')->toArray()
            ]);

        $response->assertStatus(200);
        
        foreach ($posts as $post) {
            $this->assertModelWasUpdated(Post::class, [
                'id' => $post->id,
                'status' => 'published'
            ]);
        }
    }

    /**
     * Test post featured image handling.
     */
    public function test_post_featured_image(): void
    {
        $post = Post::factory()->create();
        $image = $this->createTestImage();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.posts.featured-image', $post), [
                'image' => $image
            ]);

        $response->assertStatus(200);
        $this->assertNotNull($post->fresh()->meta_data['featured_image_id']);
        $this->assertFileExistsInStorage("media/{$image->hashName()}");
    }

    /**
     * Test post meta information.
     */
    public function test_post_meta_handling(): void
    {
        $post = $this->createPost();
        
        $metaData = [
            'meta_title' => 'Test Meta Title',
            'meta_description' => 'Test meta description',
            'og_title' => 'Test OG Title',
            'twitter_card' => 'summary_large_image'
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.posts.meta.update', $post), $metaData);

        $response->assertStatus(200);
        $this->assertEquals($metaData['meta_title'], $post->fresh()->meta->meta_title);
        $this->assertEquals($metaData['og_title'], $post->fresh()->meta->og_title);
    }
}