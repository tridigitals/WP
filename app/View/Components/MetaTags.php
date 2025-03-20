<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\Meta;

class MetaTags extends Component
{
    /**
     * The meta information.
     */
    public $meta;

    /**
     * Create a new component instance.
     */
    public function __construct($meta = null)
    {
        $this->meta = $meta instanceof Meta ? $meta : null;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        $meta = $this->meta;
        $defaultMeta = config('cms.seo', []);

        return view('components.meta-tags', [
            'title' => $meta?->meta_title ?? $defaultMeta['meta_title'] ?? config('app.name'),
            'description' => $meta?->meta_description ?? $defaultMeta['meta_description'] ?? '',
            'keywords' => $meta?->meta_keywords ?? $defaultMeta['meta_keywords'] ?? '',
            'robots' => $meta?->robots ?? $defaultMeta['default_robots'] ?? 'index,follow',
            'canonical' => $meta?->canonical_url ?? request()->url(),
            'ogTitle' => $meta?->og_title ?? $meta?->meta_title ?? $defaultMeta['meta_title'] ?? config('app.name'),
            'ogDescription' => $meta?->og_description ?? $meta?->meta_description ?? $defaultMeta['meta_description'] ?? '',
            'ogImage' => $meta?->og_image ?? $defaultMeta['og_image'] ?? null,
            'ogType' => $meta?->og_type ?? 'website',
            'twitterCard' => $meta?->twitter_card ?? $defaultMeta['default_twitter_card'] ?? 'summary_large_image',
            'twitterTitle' => $meta?->twitter_title ?? $meta?->meta_title ?? $defaultMeta['meta_title'] ?? config('app.name'),
            'twitterDescription' => $meta?->twitter_description ?? $meta?->meta_description ?? $defaultMeta['meta_description'] ?? '',
            'twitterImage' => $meta?->twitter_image ?? $meta?->og_image ?? $defaultMeta['og_image'] ?? null,
            'schemaMarkup' => $meta?->schema_markup ?? null,
        ]);
    }
}