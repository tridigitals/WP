<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ActivityLogSubscriber
{
    /**
     * Model events to track.
     */
    protected array $trackedEvents = [
        'created', 'updated', 'deleted', 'restored', 'force_deleted'
    ];

    /**
     * Handle model events.
     */
    public function handleModelEvent($event, string $action): void
    {
        $model = $event->model ?? $event;
        $changes = $model->getDirty();
        $originalData = $model->getOriginal();

        DB::table('activity_log')->insert([
            'user_id' => auth()->id(),
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'action' => $action,
            'changes' => json_encode([
                'old' => array_intersect_key($originalData, $changes),
                'new' => $changes
            ]),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Handle admin login events.
     */
    public function handleAdminLogin($event): void
    {
        if ($event->user->is_admin) {
            $this->logActivity('admin_login', [
                'user_id' => $event->user->id,
                'email' => $event->user->email
            ]);
        }
    }

    /**
     * Handle settings changes.
     */
    public function handleSettingsChange($event): void
    {
        $this->logActivity('settings_updated', [
            'section' => $event->section,
            'changes' => $event->changes
        ]);
    }

    /**
     * Handle maintenance mode changes.
     */
    public function handleMaintenanceMode($event): void
    {
        $this->logActivity('maintenance_mode', [
            'status' => $event->enabled ? 'enabled' : 'disabled'
        ]);
    }

    /**
     * Handle cache clearing events.
     */
    public function handleCacheClear($event): void
    {
        $this->logActivity('cache_cleared', [
            'type' => $event->type ?? 'all'
        ]);
    }

    /**
     * Handle bulk actions.
     */
    public function handleBulkAction($event): void
    {
        $this->logActivity('bulk_action', [
            'action' => $event->action,
            'model' => $event->modelType,
            'count' => count($event->ids),
            'ids' => $event->ids
        ]);
    }

    /**
     * Log activity to database.
     */
    protected function logActivity(string $type, array $data = []): void
    {
        DB::table('activity_log')->insert([
            'user_id' => auth()->id(),
            'type' => $type,
            'data' => json_encode($data),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events): array
    {
        $subscribers = [
            'Illuminate\Auth\Events\Login' => 'handleAdminLogin',
            'App\Events\SettingsUpdated' => 'handleSettingsChange',
            'App\Events\MaintenanceModeChanged' => 'handleMaintenanceMode',
            'App\Events\CacheCleared' => 'handleCacheClear',
            'App\Events\BulkActionPerformed' => 'handleBulkAction',
        ];

        // Register model events
        foreach ($this->trackedEvents as $event) {
            $subscribers["eloquent.{$event}:*"] = function ($eventName, array $data) use ($event) {
                $this->handleModelEvent($data[0], $event);
            };
        }

        return $subscribers;
    }
}