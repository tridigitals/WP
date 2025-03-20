<?php

namespace Database\Factories;

use App\Models\Meta;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MetaFactory extends Factory
{
    protected $model = Meta::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'metable_type' => Post::class,
            'metable_id' => Post::factory(),
            'meta_title' => $this->faker->sentence(6),
            'meta_description' => $this->faker->paragraph(2),
            'meta_keywords' => implode(', ', $this->faker->words(5)),
            'canonical_url' => $this->faker->url,
            'robots' => 'index,follow',
            'og_title' => null,
            'og_description' => null,
            'og_type' => 'article',
            'og_image' => $this->faker->imageUrl(1200, 630),
            'twitter_card' => 'summary_large_image',
            'twitter_title' => null,
            'twitter_description' => null,
            'twitter_image' => null,
            'schema_markup' => [
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'headline' => $this->faker->sentence,
                'description' => $this->faker->paragraph,
                'datePublished' => now()->toIso8601String(),
                'dateModified' => now()->toIso8601String()
            ],
            'custom_meta' => [],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Configure meta for a post.
     */
    public function forPost(Post $post = null): static
    {
        return $this->state(function (array $attributes) use ($post) {
            $post = $post ?? Post::factory()->create();
            
            return [
                'metable_type' => Post::class,
                'metable_id' => $post->id,
                'meta_title' => $post->title,
                'meta_description' => Str::limit(strip_tags($post->content), 160),
                'og_title' => $post->title,
                'og_description' => Str::limit(strip_tags($post->content), 160),
                'twitter_title' => $post->title,
                'twitter_description' => Str::limit(strip_tags($post->content), 160),
                'schema_markup' => [
                    '@context' => 'https://schema.org',
                    '@type' => 'Article',
                    'headline' => $post->title,
                    'description' => $post->excerpt,
                    'datePublished' => optional($post->published_at)->toIso8601String(),
                    'dateModified' => $post->updated_at->toIso8601String(),
                    'author' => [
                        '@type' => 'Person',
                        'name' => $post->user->name
                    ]
                ]
            ];
        });
    }

    /**
     * Configure meta for a category.
     */
    public function forCategory(Category $category = null): static
    {
        return $this->state(function (array $attributes) use ($category) {
            $category = $category ?? Category::factory()->create();
            
            return [
                'metable_type' => Category::class,
                'metable_id' => $category->id,
                'meta_title' => $category->name,
                'meta_description' => $category->description,
                'og_type' => 'website',
                'schema_markup' => [
                    '@context' => 'https://schema.org',
                    '@type' => 'CollectionPage',
                    'name' => $category->name,
                    'description' => $category->description
                ]
            ];
        });
    }

    /**
     * Configure meta for a tag.
     */
    public function forTag(Tag $tag = null): static
    {
        return $this->state(function (array $attributes) use ($tag) {
            $tag = $tag ?? Tag::factory()->create();
            
            return [
                'metable_type' => Tag::class,
                'metable_id' => $tag->id,
                'meta_title' => $tag->name,
                'meta_description' => "Posts tagged with {$tag->name}",
                'og_type' => 'website',
                'schema_markup' => [
                    '@context' => 'https://schema.org',
                    '@type' => 'CollectionPage',
                    'name' => $tag->name,
                    'description' => "Collection of posts tagged with {$tag->name}"
                ]
            ];
        });
    }

    /**
     * Indicate that the meta should be optimized for social sharing.
     */
    public function optimizedForSocial(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'og_title' => $attributes['meta_title'],
                'og_description' => $attributes['meta_description'],
                'twitter_title' => $attributes['meta_title'],
                'twitter_description' => $attributes['meta_description'],
                'twitter_image' => $attributes['og_image']
            ];
        });
    }

    /**
     * Add custom meta data.
     */
    public function withCustomMeta(array $customMeta): static
    {
        return $this->state(function (array $attributes) use ($customMeta) {
            return [
                'custom_meta' => array_merge($attributes['custom_meta'] ?? [], $customMeta)
            ];
        });
    }

    /**
     * Set no-index for robots.
     */
    public function noIndex(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'robots' => 'noindex,follow'
            ];
        });
    }
}