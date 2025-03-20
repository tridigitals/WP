<?php

namespace Tests\Feature\Admin;

use Tests\Feature\BaseTestCase;
use App\Models\Post;
use App\Models\Category;
use App\Models\Meta;

class MetaTest extends BaseTestCase
{
    /**
     * Test admin can view meta list.
     */
    public function test_admin_can_view_meta_list(): void
    {
        Meta::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.seo.index'));

        $response->assertStatus(200);
        $response->assertViewHas('meta');
    }

    /**
     * Test admin can create meta for post.
     */
    public function test_admin_can_create_meta_for_post(): void
    {
        $post = $this->createPost();
        
        $metaData = [
            'meta_title' => 'Test Meta Title',
            'meta_description' => 'This is a test meta description that should be optimized for search engines.',
            'meta_keywords' => 'test, meta, keywords',
            'og_title' => 'Test Open Graph Title',
            'og_description' => 'Test Open Graph description for social sharing',
            'og_image' => 'https://example.com/image.jpg',
            'twitter_card' => 'summary_large_image',
            'canonical_url' => 'https://example.com/test-post',
            'robots' => 'index,follow',
            'schema_markup' => [
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'headline' => 'Test Article',
                'description' => 'Test description'
            ]
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.seo.store'), [
                'metable_type' => Post::class,
                'metable_id' => $post->id,
                ...$metaData
            ]);

        $response->assertStatus(201);
        $this->assertHasMeta($post->fresh());
        $this->assertEquals('Test Meta Title', $post->fresh()->meta->meta_title);
    }

    /**
     * Test meta title length validation.
     */
    public function test_meta_title_length_validation(): void
    {
        $post = $this->createPost();
        
        $response = $this->actingAs($this->admin)
            ->post(route('admin.seo.store'), [
                'metable_type' => Post::class,
                'metable_id' => $post->id,
                'meta_title' => str_repeat('a', 70) // Too long
            ]);

        $this->assertResponseHasError($response, 'meta_title');
    }

    /**
     * Test meta description length validation.
     */
    public function test_meta_description_length_validation(): void
    {
        $post = $this->createPost();
        
        $response = $this->actingAs($this->admin)
            ->post(route('admin.seo.store'), [
                'metable_type' => Post::class,
                'metable_id' => $post->id,
                'meta_description' => str_repeat('a', 170) // Too long
            ]);

        $this->assertResponseHasError($response, 'meta_description');
    }

    /**
     * Test admin can analyze SEO.
     */
    public function test_admin_can_analyze_seo(): void
    {
        $post = $this->createPost();
        Meta::factory()->create([
            'metable_type' => Post::class,
            'metable_id' => $post->id,
            'meta_title' => 'Short Title',
            'meta_description' => 'This is a short description'
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.seo.analyze', ['type' => 'post', 'id' => $post->id]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'analysis' => [
                'title',
                'description',
                'keywords',
                'og_tags',
                'twitter_tags',
                'schema'
            ],
            'score'
        ]);
    }

    /**
     * Test admin can generate meta tags.
     */
    public function test_admin_can_generate_meta_tags(): void
    {
        $post = $this->createPost();
        Meta::factory()->create([
            'metable_type' => Post::class,
            'metable_id' => $post->id,
            'meta_title' => 'Test Title',
            'meta_description' => 'Test Description',
            'og_title' => 'OG Title',
            'twitter_card' => 'summary'
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.seo.meta-tags', ['type' => 'post', 'id' => $post->id]));

        $response->assertStatus(200);
        $response->assertSee('<title>Test Title</title>', false);
        $response->assertSee('<meta name="description" content="Test Description">', false);
        $response->assertSee('<meta property="og:title" content="OG Title">', false);
        $response->assertSee('<meta name="twitter:card" content="summary">', false);
    }

    /**
     * Test admin can bulk update meta.
     */
    public function test_admin_can_bulk_update_meta(): void
    {
        $posts = collect([
            $this->createPost(),
            $this->createPost(),
            $this->createPost()
        ]);

        $metaData = $posts->map(function ($post) {
            return [
                'id' => $post->id,
                'meta_title' => "Title for post {$post->id}",
                'meta_description' => "Description for post {$post->id}"
            ];
        })->toArray();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.seo.bulk-update'), [
                'meta' => $metaData
            ]);

        $response->assertStatus(200);

        foreach ($posts as $post) {
            $this->assertModelWasUpdated(Meta::class, [
                'metable_type' => Post::class,
                'metable_id' => $post->id,
                'meta_title' => "Title for post {$post->id}"
            ]);
        }
    }

    /**
     * Test schema markup validation.
     */
    public function test_schema_markup_validation(): void
    {
        $post = $this->createPost();
        
        $response = $this->actingAs($this->admin)
            ->post(route('admin.seo.store'), [
                'metable_type' => Post::class,
                'metable_id' => $post->id,
                'schema_markup' => [
                    // Missing required @context and @type
                    'headline' => 'Test Article'
                ]
            ]);

        $this->assertResponseHasError($response, 'schema_markup');
    }

    /**
     * Test meta generation for models.
     */
    public function test_can_generate_meta_for_model(): void
    {
        $post = $this->createPost([
            'title' => 'Test Post Title',
            'content' => 'This is the content of the test post which should be used for meta description.'
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.seo.generate'), [
                'model_type' => Post::class,
                'model_id' => $post->id
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'meta' => [
                'meta_title',
                'meta_description',
                'og_title',
                'og_description',
                'schema_markup'
            ],
            'preview'
        ]);

        $this->assertStringContainsString('Test Post Title', $response->json('meta.meta_title'));
    }
}