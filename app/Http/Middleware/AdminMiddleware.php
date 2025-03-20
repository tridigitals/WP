<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is logged in
        if (!Auth::check()) {
            return $this->handleUnauthorized($request);
        }

        $user = Auth::user();

        // Check if user is an admin
        if (!$this->isAdmin($user)) {
            return $this->handleUnauthorized($request);
        }

        // Add admin flag to view
        view()->share('isAdmin', true);

        // Add user to view
        view()->share('adminUser', $user);

        return $next($request);
    }

    /**
     * Check if the user is an admin.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    protected function isAdmin($user): bool
    {
        // Check for admin role/permission
        // This can be expanded based on your user/role system
        return in_array($user->email, config('cms.admin_emails', [])) ||
               $user->hasRole('admin') ||
               $user->is_admin;
    }

    /**
     * Handle unauthorized access attempt.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    protected function handleUnauthorized(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        return redirect()
            ->route('login')
            ->with('error', 'You do not have permission to access the admin area.');
    }
}