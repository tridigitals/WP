<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'failed' => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :minutes minutes.',

    // Custom CMS auth messages
    'login_success' => 'Welcome back! You have been successfully logged in.',
    'login_error' => 'Unable to log in. Please check your credentials.',
    'logout_success' => 'You have been successfully logged out.',
    'remember_me' => 'Remember me',
    'forgot_password' => 'Forgot your password?',
    'reset_password' => 'Reset Password',
    'send_reset_link' => 'Send Password Reset Link',
    'reset_link_sent' => 'We have emailed your password reset link.',
    'reset_success' => 'Your password has been reset successfully.',

    // Admin specific messages
    'admin_required' => 'Administrator privileges are required to access this area.',
    'access_denied' => 'You do not have permission to access this resource.',
    'account_inactive' => 'Your account is currently inactive. Please contact the administrator.',
    'account_locked' => 'Your account has been locked for security reasons. Please try again later.',
    
    // Security messages
    'suspicious_activity' => 'Suspicious activity detected. Your account has been temporarily locked.',
    'ip_blocked' => 'Access from your IP address has been temporarily blocked.',
    'session_expired' => 'Your session has expired. Please log in again.',
    'concurrent_login' => 'Your account is being used in another session.',
    
    // Additional messages
    'verify_email' => 'Please verify your email address.',
    'email_verified' => 'Your email address has been verified.',
    'verification_sent' => 'A fresh verification link has been sent to your email address.',
    'verification_required' => 'You must verify your email address to access this resource.',
    
    // Form labels
    'labels' => [
        'email' => 'Email Address',
        'password' => 'Password',
        'confirm_password' => 'Confirm Password',
        'new_password' => 'New Password',
        'current_password' => 'Current Password',
    ],

    // Form placeholders
    'placeholders' => [
        'email' => 'Enter your email address',
        'password' => 'Enter your password',
    ],

    // Error messages
    'errors' => [
        'invalid_token' => 'This password reset token is invalid.',
        'expired_token' => 'This password reset token has expired.',
        'password_mismatch' => 'The passwords do not match.',
        'invalid_credentials' => 'Invalid email or password.',
    ],

    // Success messages
    'success' => [
        'password_changed' => 'Your password has been changed successfully.',
        'profile_updated' => 'Your profile has been updated successfully.',
    ],
];