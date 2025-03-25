<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Support\Str;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $categories = Category::all();
        $tags = Tag::all();

        // Create 50 posts
        for ($i = 0; $i < 50; $i++) {
            $title = fake()->sentence();
            $isPublished = fake()->boolean(80); // 80% chance of being published

            $post = Post::create([
                'title' => $title,
                'slug' => Str::slug($title),
                'content' => $this->generateContent(),
                'excerpt' => fake()->paragraph(),
                'status' => $isPublished ? 'published' : 'draft',
                'author_id' => $users->random()->id,
                'published_at' => $isPublished ? fake()->dateTimeBetween('-1 year', 'now') : null,
            ]);

            // Assign 1-3 categories
            $post->categories()->attach(
                $categories->random(rand(1, 3))->pluck('id')->toArray()
            );

            // Assign 2-5 relevant tags
            $post->tags()->attach(
                $tags->random(rand(2, 5))->pluck('id')->toArray()
            );
        }
    }

    /**
     * Generate realistic blog post content with headings, paragraphs, and lists.
     */
    private function generateContent(): string
    {
        $content = [];

        // Introduction
        $content[] = fake()->paragraph(3);
        $content[] = '';

        // Main content sections
        for ($i = 0; $i < rand(2, 4); $i++) {
            $content[] = '## ' . fake()->sentence();
            $content[] = '';
            $content[] = fake()->paragraph(4);
            $content[] = '';

            // 50% chance to add a list
            if (fake()->boolean()) {
                $content[] = $this->generateList();
                $content[] = '';
            }

            $content[] = fake()->paragraph(3);
            $content[] = '';
        }

        // Conclusion
        $content[] = '## Conclusion';
        $content[] = '';
        $content[] = fake()->paragraph(2);

        return implode("\n", $content);
    }

    /**
     * Generate a random markdown list.
     */
    private function generateList(): string
    {
        $list = [];
        $items = rand(3, 6);

        for ($i = 0; $i < $items; $i++) {
            $list[] = '* ' . fake()->sentence();
        }

        return implode("\n", $list);
    }
}
