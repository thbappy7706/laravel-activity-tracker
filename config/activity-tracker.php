<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Auto Track Activity
    |--------------------------------------------------------------------------
    |
    | When enabled, user activity will be automatically tracked on all web routes.
    | Set to false if you want to manually apply the middleware to specific routes.
    |
    */
    'auto_track' => env('ACTIVITY_TRACKER_AUTO_TRACK', true),

    /*
    |--------------------------------------------------------------------------
    | Online Threshold (minutes)
    |--------------------------------------------------------------------------
    |
    | Users are considered "online" if their last activity was within this timeframe.
    | This affects the isOnline() method and online scopes.
    |
    */
    'online_threshold' => env('ACTIVITY_TRACKER_ONLINE_THRESHOLD', 5),

    /*
    |--------------------------------------------------------------------------
    | Cleanup Old Records
    |--------------------------------------------------------------------------
    |
    | Automatically cleanup activity records older than specified days.
    | Set to null to disable cleanup. You can run 'php artisan activity:cleanup'
    | manually or schedule it to run automatically.
    |
    */
    'cleanup_after_days' => env('ACTIVITY_TRACKER_CLEANUP_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Track Anonymous Users
    |--------------------------------------------------------------------------
    |
    | Track activity for non-authenticated users using session ID.
    | This can be useful for analytics but may increase database size.
    |
    */
    'track_anonymous' => env('ACTIVITY_TRACKER_TRACK_ANONYMOUS', false),

    /*
    |--------------------------------------------------------------------------
    | Ignored Routes
    |--------------------------------------------------------------------------
    |
    | Routes that should not trigger activity tracking. Supports wildcards.
    | Add patterns for routes you don't want to track (e.g., API endpoints,
    | admin panels, debugging tools).
    |
    */
    'ignored_routes' => [
        'api/*',
        'horizon/*',
        'telescope/*',
        '_debugbar/*',
        'livewire/*',
        'broadcasting/*',
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Table
    |--------------------------------------------------------------------------
    |
    | The table name for storing user activities. Change this if you need
    | to use a different table name to avoid conflicts.
    |
    */
    'table_name' => env('ACTIVITY_TRACKER_TABLE_NAME', 'user_activities'),

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Configure caching behavior to optimize performance. The cache prevents
    | excessive database writes by temporarily storing recent activity updates.
    |
    */
    'cache' => [
        'enabled' => env('ACTIVITY_TRACKER_CACHE_ENABLED', true),
        'ttl' => env('ACTIVITY_TRACKER_CACHE_TTL', 60), // seconds
        'prefix' => env('ACTIVITY_TRACKER_CACHE_PREFIX', 'activity_tracker'),
    ],

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | The user model class. This is automatically detected from the auth
    | configuration, but you can override it here if needed.
    |
    */
    'user_model' => env('ACTIVITY_TRACKER_USER_MODEL', null),
];
