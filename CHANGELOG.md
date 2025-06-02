# Changelog

All notable changes to `laravel-activity-tracker` will be documented in this file.

## 1.0.0 - 2024-01-XX

### Added
- Initial release
- User activity tracking with middleware
- Online status detection
- Last seen functionality
- Automatic cleanup of old records
- Support for anonymous user tracking
- Configurable online threshold
- Route filtering for ignored paths
- Comprehensive test suite
- Laravel 10 and 11 support
- PHP 8.1+ support

### Features
- `HasActivity` trait for User models
- `ActivityTracker` facade for easy access
- Artisan command for cleanup (`activity:cleanup`)
- Automatic service provider registration
- Optimized database queries with proper indexing
- Caching to reduce database load
- Flexible configuration options
