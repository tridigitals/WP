<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Menu extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'location',
        'description'
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function ($menu) {
            if (empty($menu->slug)) {
                $menu->slug = Str::slug($menu->name);
            }
        });
    }

    /**
     * Get the menu items for this menu.
     */
    public function items()
    {
        return $this->hasMany(MenuItem::class)->orderBy('order');
    }

    /**
     * Get only the top level menu items.
     */
    public function topLevelItems()
    {
        return $this->hasMany(MenuItem::class)
            ->whereNull('parent_id')
            ->orderBy('order');
    }

    /**
     * Get the menu structure as a nested array.
     */
    public function getStructureAttribute()
    {
        return $this->buildMenuTree($this->items);
    }

    /**
     * Build a nested menu tree from a flat collection of items.
     */
    protected function buildMenuTree($items, $parentId = null)
    {
        $tree = [];

        foreach ($items as $item) {
            if ($item->parent_id === $parentId) {
                $children = $this->buildMenuTree($items, $item->id);
                if ($children) {
                    $item->children = $children;
                }
                $tree[] = $item;
            }
        }

        return $tree;
    }
}
