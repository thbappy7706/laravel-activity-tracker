# Laravel Activity Tracker

[![Latest Version on Packagist](https://img.shields.io/packagist/v/thbappy7706/laravel-activity-tracker.svg?style=flat-square)](https://packagist.org/packages/thbappy7706/laravel-activity-tracker)
[![Total Downloads](https://img.shields.io/packagist/dt/thbappy7706/laravel-activity-tracker.svg?style=flat-square)](https://packagist.org/packages/thbappy7706/laravel-activity-tracker)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/thbappy7706/laravel-activity-tracker/run-tests?label=tests)](https://github.com/thbappy7706/laravel-activity-tracker/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/thbappy7706/laravel-activity-tracker/Check%20&%20fix%20styling?label=code%20style)](https://github.com/thbappy7706/laravel-activity-tracker/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)

A comprehensive Laravel package for tracking user activity, online status, and "last seen" functionality. Perfect for applications that need to display user presence, activity monitoring, or implement features like "who's online" lists.

## Features

- 🟢 **Real-time Online Status** - Track which users are currently online
- 👁️ **Last Seen Tracking** - Display when users were last active
- 🚀 **Automatic Activity Tracking** - Middleware-based tracking with minimal setup
- 🎯 **Flexible Configuration** - Customizable online thresholds and tracking options
- 🧹 **Automatic Cleanup** - Built-in command to clean old activity records
- 📊 **Anonymous User Support** - Optional tracking for non-authenticated users
- 🔧 **Route Filtering** - Exclude specific routes from tracking
- 💾 **Optimized Performance** - Caching to reduce database load
- 🧪 **Fully Tested** - Comprehensive test suite included

## Installation

You can install the package via composer:

```bash
composer require thbappy7706/laravel-activity-tracker
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag="activity-tracker-migrations"
php artisan migrate
```

Optionally, you can publish the config file:

```bash
php artisan vendor:publish --tag="activity-tracker-config"
```

## Quick Start

### 1. Add the trait to your User model

```php
use thbappy7706\ActivityTracker\Traits\HasActivity;

class User extends Authenticatable
{
    use HasActivity;
    
    // ... rest of your model
}
```

### 2. Use in your application

```php
use thbappy7706\ActivityTracker\Facades\ActivityTracker;

// Check if a user is online
if (ActivityTracker::isUserOnline($user->id)) {
    echo "User is online!";
}

// Get last seen time
echo "Last seen: " . ActivityTracker::getLastSeen($user->id);

// Get count of online users
echo "Online users: " . ActivityTracker::getOnlineCount();

// Using the trait methods
if ($user->isOnline()) {
    echo "User is online!";
}

echo "Last seen: " . $user->last_seen;
```

### 3. In Blade templates

```blade
@if($user->isOnline())
    <span class="text-green-500">● Online</span>
@else
    <span class="text-gray-500">Last seen: {{ $user->last_seen }}</span>
@endif

<!-- Show online users count -->
<p>{{ ActivityTracker::getOnlineCount() }} users online</p>
```

## Configuration

The package works out of the box with sensible defaults, but you can customize it by publishing the config file:

```php
return [
    // Automatically track activity on all web routes
    'auto_track' => true,
    
    // Users are "online" if active within 5 minutes
    'online_threshold' => 5,
    
    // Clean up records older than 30 days
    'cleanup_after_days' => 30,
    
    // Track anonymous users (optional)
    'track_anonymous' => false,
    
    // Routes to ignore
    'ignored_routes' => [
        'api/*',
        'horizon/*',
        'telescope/*',
    ],
    
    // Database table name
    'table_name' => 'user_activities',
];
```

## Advanced Usage

### Manual Tracking

If you disable `auto_track`, you can manually apply the middleware:

```php
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('track.activity');
```

### Cleanup Old Records

Add to your scheduler in `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('activity:cleanup')->daily();
}
```

### Get Online Users

```php
$onlineUsers = ActivityTracker::getOnlineUsers();

// Or using Eloquent scopes
$onlineUsers = User::online()->get();
```

### Custom Queries

```php
use thbappy7706\ActivityTracker\Models\UserActivity;

// Get recent activity for a user
$recentActivity = UserActivity::forUser($userId)
    ->latest('last_activity')
    ->take(10)
    ->get();

// Get all online activities
$onlineActivities = UserActivity::online()->get();
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Publishing to Packagist

To publish this package to Packagist:

1. **Create GitHub repository**: Push your code to GitHub at `https://github.com/thbappy7706/laravel-activity-tracker`

2. **Submit to Packagist**:
   - Go to [packagist.org](https://packagist.org)
   - Click "Submit"
   - Enter your GitHub repository URL: `https://github.com/thbappy7706/laravel-activity-tracker`
   - Packagist will automatically sync with your repository

4. **Set up auto-update**: Configure GitHub webhook in your repository settings to automatically update Packagist when you push new versions.

## Version Management

Use semantic versioning (SemVer) for releases:

```bash
# Tag a new version
git tag v1.0.0
git push origin v1.0.0
```

## Examples

### Real-time Online Users Component

```php
// In your controller
public function getOnlineUsers()
{
    return response()->json([
        'count' => ActivityTracker::getOnlineCount(),
        'users' => User::online()->with('latestActivity')->get()
    ]);
}
```

### Blade Component for User Status

```blade
{{-- resources/views/components/user-status.blade.php --}}
@props(['user'])

<div class="flex items-center space-x-2">
    @if($user->isOnline())
        <div class="w-3 h-3 bg-green-400 rounded-full"></div>
        <span class="text-sm text-green-600">Online</span>
    @else
        <div class="w-3 h-3 bg-gray-400 rounded-full"></div>
        <span class="text-sm text-gray-500">{{ $user->last_seen }}</span>
    @endif
</div>
```

### Dashboard Widget

```php
// Show activity statistics
$stats = [
    'online_now' => ActivityTracker::getOnlineCount(),
    'active_today' => ActivityTracker::getOnlineCountByPeriod(24 * 60),
    'active_this_week' => ActivityTracker::getOnlineCountByPeriod(7 * 24 * 60),
];
```

## Credits

- [Tanvir Hossen Bappy](https://github.com/thbappy7706)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
