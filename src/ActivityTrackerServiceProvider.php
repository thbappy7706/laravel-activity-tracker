<?php

namespace thbappy7706\ActivityTracker;

use Illuminate\Support\ServiceProvider;
use thbappy7706\ActivityTracker\Console\Commands\CleanupActivityCommand;
use thbappy7706\ActivityTracker\Middleware\TrackUserActivity;

class ActivityTrackerServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/activity-tracker.php', 'activity-tracker');
        
        $this->app->singleton(ActivityTracker::class, function ($app) {
            return new ActivityTracker();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerPublishing();
        $this->registerMiddleware();
        $this->registerCommands();
    }

    /**
     * Register the package's publishable resources.
     */
    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            // Publish config
            $this->publishes([
                __DIR__.'/../config/activity-tracker.php' => config_path('activity-tracker.php'),
            ], 'activity-tracker-config');

            // Publish migration
            $this->publishes([
                __DIR__.'/../database/migrations/create_user_activities_table.php.stub' => 
                database_path('migrations/'.date('Y_m_d_His', time()).'_create_user_activities_table.php'),
            ], 'activity-tracker-migrations');
        }
    }

    /**
     * Register the package middleware.
     */
    protected function registerMiddleware(): void
    {
        $this->app['router']->aliasMiddleware('track.activity', TrackUserActivity::class);

        // Auto-register middleware for web routes if enabled
        if (config('activity-tracker.auto_track', true)) {
            $this->app['router']->pushMiddlewareToGroup('web', TrackUserActivity::class);
        }
    }

    /**
     * Register the package's commands.
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CleanupActivityCommand::class,
            ]);
        }
    }
}
