<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        // Get the user before logging out
        $user = $request->user();

        // Log the user out
        Auth::logout();

        // Invalidate the session
        $request->session()->invalidate();

        // Regenerate CSRF token
        $request->session()->regenerateToken();

        // Fire logout event
        event(new \Illuminate\Auth\Events\Logout('web', $user));

        // Log activity if it was an admin
        if ($user && $user->is_admin) {
            activity()
                ->performedOn($user)
                ->event('logout')
                ->log('Admin logged out');
        }

        // Redirect to login page
        return redirect()->route('login')
            ->with('status', 'You have been logged out successfully.');
    }
}