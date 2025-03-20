<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SecurityAlert;

class SecuritySubscriber
{
    /**
     * Handle failed login attempts.
     */
    public function handleFailedLogin($event): void
    {
        $key = 'login_attempts:' . $event->credentials['email'];
        $attempts = Cache::get($key, 0) + 1;
        
        Cache::put($key, $attempts, now()->addHours(1));

        if ($attempts >= 5) {
            Log::warning('Multiple failed login attempts detected', [
                'email' => $event->credentials['email'],
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            $this->notifyAdmins('Multiple failed login attempts detected', [
                'email' => $event->credentials['email'],
                'attempts' => $attempts
            ]);
        }
    }

    /**
     * Handle successful logins.
     */
    public function handleSuccessfulLogin($event): void
    {
        $user = $event->user;
        $previousLogin = $user->meta_data['last_login_at'] ?? null;
        
        $user->update([
            'meta_data' => array_merge($user->meta_data ?? [], [
                'last_login_at' => now()->toDateTimeString(),
                'last_login_ip' => request()->ip(),
                'previous_login_at' => $previousLogin
            ])
        ]);

        // Clear failed login attempts
        Cache::forget('login_attempts:' . $user->email);
    }

    /**
     * Handle permission changes.
     */
    public function handlePermissionChange($event): void
    {
        Log::info('User permissions modified', [
            'user_id' => $event->user->id,
            'changed_by' => auth()->id(),
            'changes' => $event->changes
        ]);

        $this->notifyAdmins('User permissions modified', [
            'user' => $event->user->name,
            'changes' => $event->changes
        ]);
    }

    /**
     * Handle suspicious activities.
     */
    public function handleSuspiciousActivity($event): void
    {
        Log::warning('Suspicious activity detected', [
            'type' => $event->type,
            'user_id' => $event->userId ?? null,
            'ip' => request()->ip(),
            'details' => $event->details
        ]);

        $this->notifyAdmins('Suspicious activity detected', [
            'type' => $event->type,
            'details' => $event->details
        ]);

        if ($event->shouldBlockIp) {
            Cache::tags('security')->put(
                'blocked_ip:' . request()->ip(),
                true,
                now()->addHours(24)
            );
        }
    }

    /**
     * Handle maintenance mode changes.
     */
    public function handleMaintenanceMode($event): void
    {
        Log::info('Maintenance mode ' . ($event->enabled ? 'enabled' : 'disabled'), [
            'user_id' => auth()->id(),
            'ip' => request()->ip()
        ]);

        $this->notifyAdmins('Maintenance mode changed', [
            'status' => $event->enabled ? 'enabled' : 'disabled',
            'by' => auth()->user()->name
        ]);
    }

    /**
     * Notify admin users about security events.
     */
    protected function notifyAdmins(string $message, array $data = []): void
    {
        $admins = \App\Models\User::where('is_admin', true)->get();
        
        Notification::send($admins, new SecurityAlert($message, $data));
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events): array
    {
        return [
            'Illuminate\Auth\Events\Failed' => 'handleFailedLogin',
            'Illuminate\Auth\Events\Login' => 'handleSuccessfulLogin',
            'App\Events\PermissionChanged' => 'handlePermissionChange',
            'App\Events\SuspiciousActivity' => 'handleSuspiciousActivity',
            'App\Events\MaintenanceModeChanged' => 'handleMaintenanceMode',
        ];
    }
}