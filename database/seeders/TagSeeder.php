<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tag;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            // Technology related tags
            ['name' => 'Laravel', 'slug' => 'laravel'],
            ['name' => 'React', 'slug' => 'react'],
            ['name' => 'JavaScript', 'slug' => 'javascript'],
            ['name' => 'PHP', 'slug' => 'php'],
            ['name' => 'DevOps', 'slug' => 'devops'],
            ['name' => 'Cloud Computing', 'slug' => 'cloud-computing'],
            ['name' => 'Cybersecurity', 'slug' => 'cybersecurity'],
            ['name' => 'Mobile Development', 'slug' => 'mobile-development'],

            // Business related tags
            ['name' => 'Startup', 'slug' => 'startup'],
            ['name' => 'Entrepreneurship', 'slug' => 'entrepreneurship'],
            ['name' => 'Digital Marketing', 'slug' => 'digital-marketing'],
            ['name' => 'E-commerce', 'slug' => 'e-commerce'],
            ['name' => 'Remote Work', 'slug' => 'remote-work'],
            ['name' => 'Leadership', 'slug' => 'leadership'],

            // Lifestyle related tags
            ['name' => 'Productivity', 'slug' => 'productivity'],
            ['name' => 'Self Improvement', 'slug' => 'self-improvement'],
            ['name' => 'Health', 'slug' => 'health'],
            ['name' => 'Fitness', 'slug' => 'fitness'],
            ['name' => 'Travel Tips', 'slug' => 'travel-tips'],
            ['name' => 'Food', 'slug' => 'food'],

            // General tags
            ['name' => 'Tutorial', 'slug' => 'tutorial'],
            ['name' => 'Guide', 'slug' => 'guide'],
            ['name' => 'Tips & Tricks', 'slug' => 'tips-and-tricks'],
            ['name' => 'Best Practices', 'slug' => 'best-practices'],
            ['name' => 'Review', 'slug' => 'review'],
            ['name' => 'Case Study', 'slug' => 'case-study'],
            ['name' => 'Trending', 'slug' => 'trending'],
            ['name' => 'Featured', 'slug' => 'featured']
        ];

        foreach ($tags as $tag) {
            Tag::create($tag);
        }
    }
}
