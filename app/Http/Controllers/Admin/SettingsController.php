<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;

class SettingsController extends AdminController
{
    /**
     * Display the settings page.
     */
    public function index()
    {
        $settings = [
            'general' => $this->getGeneralSettings(),
            'media' => $this->getMediaSettings(),
            'seo' => $this->getSeoSettings(),
            'cache' => $this->getCacheSettings(),
            'security' => $this->getSecuritySettings(),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'site_name' => ['required', 'string', 'max:255'],
            'site_description' => ['nullable', 'string', 'max:500'],
            'posts_per_page' => ['required', 'integer', 'min:1', 'max:100'],
            'allow_comments' => ['boolean'],
            'media_max_size' => ['required', 'integer', 'min:1', 'max:50000'],
            'media_allowed_types' => ['required', 'array'],
            'optimize_images' => ['boolean'],
            'enable_meta' => ['boolean'],
            'default_meta_title' => ['nullable', 'string', 'max:60'],
            'default_meta_description' => ['nullable', 'string', 'max:160'],
            'cache_enabled' => ['boolean'],
            'cache_duration' => ['required', 'integer', 'min:1'],
            'maintenance_mode' => ['boolean'],
        ]);

        // Update settings in config and cache
        foreach ($validated as $key => $value) {
            $this->updateSetting($key, $value);
        }

        // Clear config cache if necessary
        if ($request->boolean('clear_config_cache')) {
            Artisan::call('config:clear');
        }

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Settings updated successfully');
    }

    /**
     * Display cache management page.
     */
    public function cache()
    {
        $cacheStats = [
            'enabled' => Config::get('cms.cache.enabled', true),
            'driver' => Config::get('cache.default'),
            'duration' => Config::get('cms.cache.ttl.posts', 3600),
            'size' => $this->getCacheSize(),
        ];

        return view('admin.settings.cache', compact('cacheStats'));
    }

    /**
     * Display maintenance mode management.
     */
    public function maintenance()
    {
        $maintenanceMode = [
            'enabled' => app()->isDownForMaintenance(),
            'message' => Config::get('cms.maintenance_message'),
            'allowed_ips' => Config::get('cms.maintenance_allowed_ips', []),
        ];

        return view('admin.settings.maintenance', compact('maintenanceMode'));
    }

    /**
     * Toggle maintenance mode.
     */
    public function toggleMaintenance(Request $request)
    {
        if (app()->isDownForMaintenance()) {
            Artisan::call('up');
            $message = 'Maintenance mode disabled';
        } else {
            Artisan::call('down', [
                '--message' => $request->input('message', 'Site under maintenance'),
                '--allow' => $request->input('allowed_ips', []),
            ]);
            $message = 'Maintenance mode enabled';
        }

        return redirect()
            ->route('admin.settings.maintenance')
            ->with('success', $message);
    }

    /**
     * Get general CMS settings.
     */
    protected function getGeneralSettings(): array
    {
        return [
            'site_name' => Config::get('cms.name'),
            'site_description' => Config::get('app.description'),
            'posts_per_page' => Config::get('cms.posts.pagination', 15),
            'allow_comments' => Config::get('cms.posts.allow_comments', true),
            'maintenance_mode' => app()->isDownForMaintenance(),
        ];
    }

    /**
     * Get media settings.
     */
    protected function getMediaSettings(): array
    {
        return [
            'max_file_size' => Config::get('cms.media.max_file_size', 5120),
            'allowed_file_types' => Config::get('cms.media.allowed_file_types', []),
            'optimize_images' => Config::get('cms.media.optimize_images', true),
            'storage_used' => $this->getStorageUsed(),
            'storage_limit' => Config::get('cms.media.storage_limit', '1 GB'),
        ];
    }

    /**
     * Get SEO settings.
     */
    protected function getSeoSettings(): array
    {
        return [
            'enable_meta' => Config::get('cms.seo.enable_meta', true),
            'default_meta_title' => Config::get('cms.seo.default_meta_title'),
            'default_meta_description' => Config::get('cms.seo.default_meta_description'),
            'enable_schema_org' => Config::get('cms.seo.enable_schema_org', true),
            'enable_twitter_cards' => Config::get('cms.seo.enable_twitter_cards', true),
        ];
    }

    /**
     * Get cache settings.
     */
    protected function getCacheSettings(): array
    {
        return [
            'enabled' => Config::get('cms.cache.enabled', true),
            'driver' => Config::get('cache.default'),
            'duration' => Config::get('cms.cache.ttl.posts', 3600),
        ];
    }

    /**
     * Get security settings.
     */
    protected function getSecuritySettings(): array
    {
        return [
            'enable_csrf' => Config::get('cms.security.enable_csrf', true),
            'allowed_html_tags' => Config::get('cms.security.allowed_html_tags', []),
        ];
    }

    /**
     * Update a setting value.
     */
    protected function updateSetting(string $key, $value): void
    {
        // Store in database or config file based on implementation
        Cache::forget("setting.{$key}");
        
        // Update config at runtime
        Config::set("cms.{$key}", $value);
    }

    /**
     * Get cache size in human readable format.
     */
    protected function getCacheSize(): string
    {
        $size = 0;
        $path = storage_path('framework/cache');

        foreach (glob("{$path}/*") as $file) {
            $size += is_file($file) ? filesize($file) : 0;
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        
        return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
    }
}