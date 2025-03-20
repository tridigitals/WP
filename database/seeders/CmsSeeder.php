<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Media;
use App\Models\Meta;

class CmsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create demo user
        $user = User::firstOrCreate(
            ['email' => 'demo@example.com'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Create categories
        $categories = [
            ['name' => 'News', 'description' => 'Latest news and updates'],
            ['name' => 'Tutorials', 'description' => 'How-to guides and tutorials'],
            ['name' => 'Technology', 'description' => 'Technology related articles'],
            ['name' => 'Design', 'description' => 'Design tips and inspiration'],
            ['name' => 'Development', 'description' => 'Development related content'],
        ];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category['name'],
                'slug' => \Str::slug($category['name']),
                'description' => $category['description'],
                'user_id' => $user->id,
            ]);
        }

        // Create tags
        $tags = [
            'Laravel', 'PHP', 'JavaScript', 'CSS', 'HTML', 
            'Vue.js', 'React', 'Design', 'UI/UX', 'Database',
            'Security', 'Performance', 'Testing', 'DevOps', 'API'
        ];

        foreach ($tags as $tagName) {
            Tag::create([
                'name' => $tagName,
                'slug' => \Str::slug($tagName),
                'user_id' => $user->id,
            ]);
        }

        // Create sample posts
        $posts = [
            [
                'title' => 'Getting Started with Our CMS',
                'content' => $this->getStarterPostContent(),
                'type' => 'post',
                'status' => 'published',
                'category_id' => 1,
                'tags' => ['Laravel', 'PHP', 'Tutorial'],
            ],
            [
                'title' => 'Best Practices for Content Management',
                'content' => $this->getBestPracticesContent(),
                'type' => 'post',
                'status' => 'published',
                'category_id' => 2,
                'tags' => ['CMS', 'Content', 'Tips'],
            ],
            [
                'title' => 'About Us',
                'content' => $this->getAboutPageContent(),
                'type' => 'page',
                'status' => 'published',
                'category_id' => null,
                'tags' => [],
            ],
            [
                'title' => 'Contact Us',
                'content' => $this->getContactPageContent(),
                'type' => 'page',
                'status' => 'published',
                'category_id' => null,
                'tags' => [],
            ],
            [
                'title' => 'Draft Post Example',
                'content' => 'This is a draft post example.',
                'type' => 'post',
                'status' => 'draft',
                'category_id' => 3,
                'tags' => ['Example'],
            ],
        ];

        foreach ($posts as $postData) {
            $post = Post::create([
                'title' => $postData['title'],
                'slug' => \Str::slug($postData['title']),
                'content' => $postData['content'],
                'type' => $postData['type'],
                'status' => $postData['status'],
                'category_id' => $postData['category_id'],
                'user_id' => $user->id,
                'published_at' => $postData['status'] === 'published' ? now() : null,
            ]);

            // Attach tags
            foreach ($postData['tags'] as $tagName) {
                $tag = Tag::firstWhere('name', $tagName);
                if ($tag) {
                    $post->tags()->attach($tag->id);
                }
            }

            // Create meta information
            Meta::create([
                'metable_type' => Post::class,
                'metable_id' => $post->id,
                'meta_title' => $postData['title'],
                'meta_description' => \Str::limit(strip_tags($postData['content']), 160),
                'og_title' => $postData['title'],
                'og_description' => \Str::limit(strip_tags($postData['content']), 160),
                'twitter_card' => 'summary_large_image',
            ]);
        }

        // Create sample media items
        $mediaItems = [
            [
                'title' => 'Sample Image 1',
                'file_name' => 'sample1.jpg',
                'mime_type' => 'image/jpeg',
                'size' => 1024 * 100, // 100KB
            ],
            [
                'title' => 'Sample Document',
                'file_name' => 'document.pdf',
                'mime_type' => 'application/pdf',
                'size' => 1024 * 500, // 500KB
            ],
        ];

        foreach ($mediaItems as $item) {
            Media::create(array_merge($item, [
                'user_id' => $user->id,
                'original_name' => $item['file_name'],
                'path' => 'media/' . $item['file_name'],
                'extension' => pathinfo($item['file_name'], PATHINFO_EXTENSION),
                'disk' => 'public',
            ]));
        }
    }

    /**
     * Get starter post content.
     */
    protected function getStarterPostContent(): string
    {
        return <<<'EOT'
Welcome to our CMS! This guide will help you get started with the basic features and functionality.

## Key Features

1. **Content Management**
   - Create and manage posts
   - Organize content with categories and tags
   - Schedule posts for future publication

2. **Media Management**
   - Upload and organize media files
   - Automatic image optimization
   - Responsive image generation

3. **SEO Tools**
   - Meta information management
   - Schema.org markup
   - Social media previews

## Getting Started

1. Log in to the admin panel
2. Create your first category
3. Add some tags
4. Create your first post
5. Upload media files

## Need Help?

Check out our documentation or contact support if you need assistance.
EOT;
    }

    /**
     * Get best practices content.
     */
    protected function getBestPracticesContent(): string
    {
        return <<<'EOT'
Follow these best practices to make the most of your content management system.

## Content Organization

1. **Use Categories Wisely**
   - Create a clear hierarchy
   - Keep categories broad and meaningful
   - Use subcategories for specific topics

2. **Effective Tagging**
   - Use relevant tags
   - Be consistent with tag naming
   - Don't over-tag content

3. **Media Management**
   - Optimize images before upload
   - Use descriptive file names
   - Add alt text for accessibility

## SEO Guidelines

1. **Meta Information**
   - Write compelling meta titles
   - Create informative meta descriptions
   - Use relevant keywords

2. **Content Structure**
   - Use proper heading hierarchy
   - Include internal links
   - Optimize images with alt text

## Regular Maintenance

1. **Content Audit**
   - Review and update old content
   - Remove outdated information
   - Check for broken links

2. **Performance Optimization**
   - Monitor site speed
   - Optimize database regularly
   - Clear cache when needed
EOT;
    }

    /**
     * Get about page content.
     */
    protected function getAboutPageContent(): string
    {
        return <<<'EOT'
Welcome to our website! We are dedicated to providing high-quality content and services to our visitors.

## Our Mission

To deliver valuable information and resources that help our users achieve their goals.

## Our Team

We are a group of passionate individuals working together to create the best possible experience for our users.

## Contact Us

Have questions? Feel free to reach out to us through our contact page.
EOT;
    }

    /**
     * Get contact page content.
     */
    protected function getContactPageContent(): string
    {
        return <<<'EOT'
Get in touch with us! We'd love to hear from you.

## Contact Information

**Email:** contact@example.com
**Phone:** (555) 123-4567
**Address:** 123 Main Street, City, Country

## Office Hours

Monday - Friday: 9:00 AM - 5:00 PM
Saturday: 10:00 AM - 2:00 PM
Sunday: Closed

## Send Us a Message

Use the contact form below to send us a message, and we'll get back to you as soon as possible.
EOT;
    }
}