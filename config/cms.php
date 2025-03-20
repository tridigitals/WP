<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CMS General Settings
    |--------------------------------------------------------------------------
    */
    'name' => env('CMS_NAME', 'Laravel CMS'),
    'version' => '1.0.0',

    /*
    |--------------------------------------------------------------------------
    | Media Settings
    |--------------------------------------------------------------------------
    */
    'media' => [
        'disk' => env('CMS_MEDIA_DISK', 'public'),
        'path' => env('CMS_MEDIA_PATH', 'media'),
        'max_file_size' => env('CMS_MEDIA_MAX_FILE_SIZE', 50 * 1024), // 50MB
        'allowed_file_types' => [
            'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
            'document' => ['pdf', 'doc', 'docx', 'txt', 'rtf'],
            'audio' => ['mp3', 'wav', 'ogg'],
            'video' => ['mp4', 'webm', 'avi']
        ],
        'image_sizes' => [
            'thumbnail' => [150, 150],
            'small' => [300, 300],
            'medium' => [600, 600],
            'large' => [1200, 1200]
        ],
        'optimize_images' => env('CMS_OPTIMIZE_IMAGES', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => env('CMS_CACHE_ENABLED', true),
        'ttl' => [
            'posts' => env('CMS_CACHE_TTL_POSTS', 3600),
            'categories' => env('CMS_CACHE_TTL_CATEGORIES', 7200),
            'tags' => env('CMS_CACHE_TTL_TAGS', 7200),
            'media' => env('CMS_CACHE_TTL_MEDIA', 86400),
            'meta' => env('CMS_CACHE_TTL_META', 3600),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SEO Settings
    |--------------------------------------------------------------------------
    */
    'seo' => [
        'meta_title_max_length' => 60,
        'meta_description_max_length' => 160,
        'meta_keywords_max_count' => 10,
        'enable_schema_org' => true,
        'enable_open_graph' => true,
        'enable_twitter_cards' => true,
        'default_twitter_card' => 'summary_large_image',
        'default_robots' => 'index,follow',
    ],

    /*
    |--------------------------------------------------------------------------
    | Post Settings
    |--------------------------------------------------------------------------
    */
    'posts' => [
        'types' => [
            'post' => 'Blog Post',
            'page' => 'Page',
            'custom' => 'Custom Content'
        ],
        'statuses' => [
            'draft' => 'Draft',
            'published' => 'Published',
            'scheduled' => 'Scheduled'
        ],
        'visibility' => [
            'public' => 'Public',
            'private' => 'Private',
            'password_protected' => 'Password Protected'
        ],
        'excerpt_length' => 160,
        'pagination' => 15,
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    */
    'security' => [
        'enable_csrf' => true,
        'allowed_html_tags' => [
            'p', 'br', 'strong', 'em', 'u', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'ul', 'ol', 'li', 'blockquote', 'img', 'a', 'table', 'tr', 'td', 'th',
            'tbody', 'thead', 'div', 'span', 'pre', 'code'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | API Settings
    |--------------------------------------------------------------------------
    */
    'api' => [
        'throttle' => [
            'enabled' => true,
            'max_attempts' => 60,
            'decay_minutes' => 1,
        ],
        'pagination' => [
            'per_page' => 15,
            'max_per_page' => 100,
        ],
    ],
];