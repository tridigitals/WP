<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
        
        // Share common data with all admin views
        view()->share('adminMenu', $this->getAdminMenu());
    }

    /**
     * Get the admin navigation menu structure.
     */
    protected function getAdminMenu(): array
    {
        return [
            'dashboard' => [
                'icon' => 'fa-chart-bar',
                'title' => 'Dashboard',
                'route' => 'admin.dashboard'
            ],
            'content' => [
                'icon' => 'fa-edit',
                'title' => 'Content',
                'children' => [
                    'posts' => [
                        'icon' => 'fa-file-alt',
                        'title' => 'Posts',
                        'route' => 'admin.posts.index'
                    ],
                    'pages' => [
                        'icon' => 'fa-file',
                        'title' => 'Pages',
                        'route' => 'admin.pages.index'
                    ],
                    'categories' => [
                        'icon' => 'fa-folder',
                        'title' => 'Categories',
                        'route' => 'admin.categories.index'
                    ],
                    'tags' => [
                        'icon' => 'fa-tags',
                        'title' => 'Tags',
                        'route' => 'admin.tags.index'
                    ]
                ]
            ],
            'media' => [
                'icon' => 'fa-images',
                'title' => 'Media',
                'children' => [
                    'library' => [
                        'icon' => 'fa-image',
                        'title' => 'Library',
                        'route' => 'admin.media.index'
                    ],
                    'upload' => [
                        'icon' => 'fa-upload',
                        'title' => 'Upload',
                        'route' => 'admin.media.upload'
                    ]
                ]
            ],
            'seo' => [
                'icon' => 'fa-search',
                'title' => 'SEO',
                'route' => 'admin.seo.index'
            ],
            'settings' => [
                'icon' => 'fa-cog',
                'title' => 'Settings',
                'route' => 'admin.settings.index'
            ]
        ];
    }

    /**
     * Get system stats for dashboard.
     */
    protected function getSystemStats(): array
    {
        return [
            'total_posts' => \App\Models\Post::count(),
            'posts_increase' => $this->calculatePostsIncrease(),
            'total_media' => \App\Models\Media::count(),
            'storage_used' => $this->getStorageUsed(),
            'storage_limit' => $this->getStorageLimit(),
            'storage_percentage' => $this->calculateStoragePercentage(),
            'total_categories' => \App\Models\Category::count(),
            'total_tags' => \App\Models\Tag::count(),
        ];
    }

    /**
     * Calculate posts increase percentage.
     */
    protected function calculatePostsIncrease(): int
    {
        $lastMonth = \App\Models\Post::whereMonth('created_at', now()->subMonth())->count();
        $thisMonth = \App\Models\Post::whereMonth('created_at', now())->count();

        if ($lastMonth === 0) return 0;
        
        return round((($thisMonth - $lastMonth) / $lastMonth) * 100);
    }

    /**
     * Get storage used in human readable format.
     */
    protected function getStorageUsed(): string
    {
        $bytes = \App\Models\Media::sum('size');
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        return round($bytes / pow(1024, $pow), 2) . ' ' . $units[$pow];
    }

    /**
     * Get storage limit from configuration.
     */
    protected function getStorageLimit(): string
    {
        return config('cms.media.storage_limit', '1 GB');
    }

    /**
     * Calculate storage usage percentage.
     */
    protected function calculateStoragePercentage(): int
    {
        $used = \App\Models\Media::sum('size');
        $limit = $this->convertToBytes(config('cms.media.storage_limit', '1 GB'));

        return min(round(($used / $limit) * 100), 100);
    }

    /**
     * Convert storage size string to bytes.
     */
    protected function convertToBytes(string $from): int
    {
        $units = ['B' => 0, 'KB' => 1, 'MB' => 2, 'GB' => 3, 'TB' => 4];
        $number = (int) preg_replace('/[^0-9]/', '', $from);
        $unit = preg_replace('/[^A-Z]/', '', strtoupper($from));

        return $number * pow(1024, $units[$unit] ?? 0);
    }
}