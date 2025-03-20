<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PostFactory extends Factory
{
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence;
        $content = collect([
            '## ' . $this->faker->sentence,
            $this->faker->paragraph(3),
            '### ' . $this->faker->sentence,
            $this->faker->paragraph(2),
            '- ' . $this->faker->sentence,
            '- ' . $this->faker->sentence,
            '- ' . $this->faker->sentence,
            $this->faker->paragraph(3),
            '> ' . $this->faker->sentence,
            $this->faker->paragraph(2),
        ])->implode("\n\n");

        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'excerpt' => $this->faker->paragraph,
            'content' => $content,
            'type' => 'post',
            'status' => 'draft',
            'featured' => $this->faker->boolean(20),
            'comment_enabled' => true,
            'published_at' => null,
            'created_at' => $this->faker->dateTimeThisYear(),
            'updated_at' => $this->faker->dateTimeThisYear(),
            'meta_data' => [
                'reading_time' => $this->faker->numberBetween(3, 15),
                'word_count' => $this->faker->numberBetween(500, 2000),
                'views' => $this->faker->numberBetween(0, 1000),
                'likes' => $this->faker->numberBetween(0, 100),
            ]
        ];
    }

    /**
     * Indicate that the post is published.
     */
    public function published(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'published',
                'published_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            ];
        });
    }

    /**
     * Indicate that the post is scheduled.
     */
    public function scheduled(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'scheduled',
                'published_at' => $this->faker->dateTimeBetween('now', '+1 month'),
            ];
        });
    }

    /**
     * Indicate that the post is a page.
     */
    public function page(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'page',
                'category_id' => null,
            ];
        });
    }

    /**
     * Indicate that the post is featured.
     */
    public function featured(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'featured' => true,
            ];
        });
    }

    /**
     * Indicate that comments are disabled.
     */
    public function commentsDisabled(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'comment_enabled' => false,
            ];
        });
    }

    /**
     * Add meta data to the post.
     */
    public function withMeta(array $meta = []): static
    {
        return $this->afterCreating(function (Post $post) use ($meta) {
            $post->meta()->create(array_merge([
                'meta_title' => $post->title,
                'meta_description' => Str::limit(strip_tags($post->content), 160),
                'meta_keywords' => implode(', ', $this->faker->words(5)),
                'og_title' => $post->title,
                'og_description' => Str::limit(strip_tags($post->content), 160),
                'twitter_card' => 'summary_large_image',
                'schema_markup' => [
                    '@context' => 'https://schema.org',
                    '@type' => 'Article',
                    'headline' => $post->title,
                    'description' => $post->excerpt,
                    'datePublished' => optional($post->published_at)->toIso8601String(),
                    'dateModified' => $post->updated_at->toIso8601String(),
                ]
            ], $meta));
        });
    }

    /**
     * Add featured image to the post.
     */
    public function withFeaturedImage(): static
    {
        return $this->afterCreating(function (Post $post) {
            $media = \App\Models\Media::factory()->image()->create([
                'user_id' => $post->user_id
            ]);
            
            $post->update([
                'meta_data' => array_merge($post->meta_data ?? [], [
                    'featured_image_id' => $media->id
                ])
            ]);
        });
    }

    /**
     * Add tags to the post.
     */
    public function withTags(int $count = 3): static
    {
        return $this->afterCreating(function (Post $post) use ($count) {
            $post->tags()->attach(
                \App\Models\Tag::factory()->count($count)->create()
            );
        });
    }

    /**
     * Create a post with common attributes.
     */
    public function common(): static
    {
        return $this->published()
            ->withMeta()
            ->withFeaturedImage()
            ->withTags();
    }
}