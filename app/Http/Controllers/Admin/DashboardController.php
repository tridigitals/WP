<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Media;
use App\Models\User;
use App\Models\ActivityLog;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        $stats = $this->getQuickStats();
        $recentActivity = $this->getRecentActivity();
        $systemStatus = $this->getSystemStatus();
        $draftPosts = $this->getDraftPosts();

        return view('admin.dashboard', compact(
            'stats',
            'recentActivity',
            'systemStatus',
            'draftPosts'
        ));
    }

    /**
     * Get quick statistics for the dashboard.
     */
    public function getQuickStats()
    {
        return Cache::remember('dashboard.quick_stats', 300, function () {
            return [
                'posts' => [
                    'total' => Post::count(),
                    'published' => Post::where('status', 'published')->count(),
                    'drafts' => Post::where('status', 'draft')->count(),
                    'scheduled' => Post::where('status', 'scheduled')->count(),
                ],
                'categories' => [
                    'total' => Category::count(),
                    'active' => Category::has('posts')->count(),
                ],
                'tags' => [
                    'total' => Tag::count(),
                    'popular' => Tag::orderBy('count', 'desc')->take(5)->get(),
                ],
                'media' => [
                    'total' => Media::count(),
                    'size' => Media::sum('size'),
                    'types' => Media::selectRaw('mime_type, count(*) as count')
                        ->groupBy('mime_type')
                        ->get()
                        ->pluck('count', 'mime_type'),
                ],
                'users' => [
                    'total' => User::count(),
                    'active' => User::whereNotNull('last_login_at')->count(),
                ],
            ];
        });
    }

    /**
     * Get recent activity for the dashboard.
     */
    protected function getRecentActivity()
    {
        return ActivityLog::with('user')
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($log) {
                return [
                    'description' => $log->description,
                    'user' => $log->user?->name ?? 'System',
                    'time' => $log->created_at->diffForHumans(),
                    'icon' => $log->icon,
                    'type' => $log->type,
                ];
            });
    }

    /**
     * Get system status information.
     */
    protected function getSystemStatus()
    {
        return Cache::remember('dashboard.system_status', 300, function () {
            return [
                'cache' => [
                    'driver' => config('cache.default'),
                    'status' => Cache::driver()->getStore()->connection()->ping() ? 'Connected' : 'Error',
                ],
                'storage' => [
                    'disk' => config('filesystems.default'),
                    'free_space' => disk_free_space(storage_path()),
                    'total_space' => disk_total_space(storage_path()),
                ],
                'queue' => [
                    'driver' => config('queue.default'),
                    'jobs' => \DB::table('jobs')->count(),
                ],
                'maintenance_mode' => app()->isDownForMaintenance(),
                'last_cache_clear' => Cache::get('last_cache_clear'),
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
            ];
        });
    }

    /**
     * Get recent draft posts.
     */
    protected function getDraftPosts()
    {
        return Post::with(['user', 'category'])
            ->where('status', 'draft')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($post) {
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'author' => $post->user->name,
                    'category' => $post->category?->name,
                    'updated_at' => $post->updated_at->diffForHumans(),
                    'edit_url' => route('admin.posts.edit', $post),
                ];
            });
    }

    /**
     * Clear various cache types.
     */
    public function clearCache(Request $request)
    {
        $type = $request->input('type', 'all');

        switch ($type) {
            case 'view':
                Artisan::call('view:clear');
                break;
            case 'route':
                Artisan::call('route:clear');
                break;
            case 'config':
                Artisan::call('config:clear');
                break;
            default:
                Artisan::call('cache:clear');
                Artisan::call('view:clear');
                Artisan::call('route:clear');
                Artisan::call('config:clear');
        }

        Cache::put('last_cache_clear', now());

        return response()->json([
            'message' => 'Cache cleared successfully.',
            'type' => $type
        ]);
    }

    /**
     * Run media optimization.
     */
    public function optimizeMedia()
    {
        // Queue media optimization job
        \App\Jobs\OptimizeMedia::dispatch();

        return response()->json([
            'message' => 'Media optimization started.',
            'job_id' => 'optimize-media-' . time()
        ]);
    }
}