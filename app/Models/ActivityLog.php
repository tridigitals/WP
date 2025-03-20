<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;

class ActivityLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'activity_log';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'changes' => 'array',
        'data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that performed the activity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the model that was targeted by the activity.
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope query to only include logs of a specific type.
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope query to only include logs for a specific model.
     */
    public function scopeForModel(Builder $query, Model $model): Builder
    {
        return $query->where('model_type', get_class($model))
                    ->where('model_id', $model->getKey());
    }

    /**
     * Scope query to only include logs by a specific user.
     */
    public function scopeByUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    /**
     * Scope query to only include recent activity.
     */
    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Get the changes in a human-readable format.
     */
    public function getChangesDescriptionAttribute(): string
    {
        if (!$this->changes) {
            return 'No changes';
        }

        $old = $this->changes['old'] ?? [];
        $new = $this->changes['new'] ?? [];
        
        $changes = [];
        foreach ($new as $key => $value) {
            $oldValue = $old[$key] ?? null;
            if ($oldValue !== $value) {
                $changes[] = sprintf(
                    '%s changed from "%s" to "%s"',
                    ucfirst($key),
                    $oldValue,
                    $value
                );
            }
        }

        return implode(', ', $changes);
    }

    /**
     * Get activity description.
     */
    public function getDescriptionAttribute(): string
    {
        $description = match($this->type) {
            'admin_login' => 'Admin login',
            'settings_updated' => 'Settings updated',
            'maintenance_mode' => $this->data['status'] === 'enabled' 
                ? 'Maintenance mode enabled' 
                : 'Maintenance mode disabled',
            'cache_cleared' => 'Cache cleared' . ($this->data['type'] !== 'all' 
                ? ' (' . $this->data['type'] . ')' 
                : ''),
            'bulk_action' => ucfirst($this->data['action']) . ' performed on ' . 
                str_plural(class_basename($this->data['model'])),
            default => ucfirst($this->action ?? $this->type)
        };

        return $description;
    }

    /**
     * Get activity icon.
     */
    public function getIconAttribute(): string
    {
        return match($this->type) {
            'admin_login' => 'fas fa-sign-in-alt',
            'settings_updated' => 'fas fa-cog',
            'maintenance_mode' => 'fas fa-tools',
            'cache_cleared' => 'fas fa-broom',
            'bulk_action' => 'fas fa-tasks',
            'created' => 'fas fa-plus',
            'updated' => 'fas fa-edit',
            'deleted' => 'fas fa-trash',
            'restored' => 'fas fa-undo',
            default => 'fas fa-info-circle'
        };
    }
}