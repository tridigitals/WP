<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MenuItem extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'menu_id',
        'title',
        'type',
        'url',
        'linkable_id',
        'linkable_type',
        'target',
        'order',
        'parent_id',
        'attributes'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'attributes' => 'array'
    ];

    /**
     * Get the menu this item belongs to.
     */
    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * Get the parent menu item.
     */
    public function parent()
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /**
     * Get the child menu items.
     */
    public function children()
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('order');
    }

    /**
     * Get all descendants of this menu item.
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get the linked item (Post, Page, Category, etc.).
     */
    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the URL for this menu item.
     */
    public function getUrlAttribute()
    {
        if ($this->type === 'custom') {
            return $this->attributes['url'];
        }

        return $this->linkable?->url ?? '#';
    }

    /**
     * Get all available menu item types.
     */
    public static function getAvailableTypes(): array
    {
        return [
            'custom' => 'Custom URL',
            'post' => 'Post',
            'page' => 'Page',
            'category' => 'Category',
            'tag' => 'Tag'
        ];
    }

    /**
     * Check if the menu item has children.
     */
    public function hasChildren(): bool
    {
        return $this->children()->count() > 0;
    }

    /**
     * Get the full path of ancestors.
     */
    public function getPathAttribute(): string
    {
        $path = collect([$this]);
        $item = $this;

        while ($item->parent) {
            $path->prepend($item->parent);
            $item = $item->parent;
        }

        return $path->pluck('title')->implode(' > ');
    }

    /**
     * Generate HTML attributes for the menu item.
     */
    public function getHtmlAttributesAttribute(): string
    {
        $attrs = array_merge([
            'class' => 'menu-item',
            'id' => 'menu-item-' . $this->id
        ], $this->attributes['attributes'] ?? []);

        if ($this->hasChildren()) {
            $attrs['class'] .= ' has-children';
        }

        return collect($attrs)->map(function ($value, $key) {
            return sprintf('%s="%s"', $key, htmlspecialchars($value));
        })->implode(' ');
    }
}
