<?php

namespace thbappy7706\ActivityTracker\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;
use thbappy7706\ActivityTracker\Models\UserActivity;
use thbappy7706\ActivityTracker\Facades\ActivityTracker;

trait HasActivity
{
    /**
     * Get all activities for the user.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(UserActivity::class);
    }

    /**
     * Get the latest activity for the user.
     */
    public function latestActivity(): HasOne
    {
        return $this->hasOne(UserActivity::class)->latestOfMany();
    }

    /**
     * Check if the user is currently online.
     */
    public function isOnline(): bool
    {
        return ActivityTracker::isUserOnline($this->getKey());
    }

    /**
     * Get the last seen time in human readable format.
     */
    public function getLastSeenAttribute(): ?string
    {
        return ActivityTracker::getLastSeen($this->getKey());
    }

    /**
     * Get the last activity timestamp.
     */
    public function getLastActivityAttribute(): ?string
    {
        $activity = $this->latestActivity;
        
        return $activity?->last_activity;
    }

    /**
     * Scope a query to only include online users.
     */
    public function scopeOnline(Builder $query): Builder
    {
        $threshold = now()->subMinutes(config('activity-tracker.online_threshold', 5));
        
        return $query->whereHas('activities', function ($q) use ($threshold) {
            $q->where('last_activity', '>=', $threshold);
        });
    }

    /**
     * Scope a query to only include users active within a specific period.
     */
    public function scopeActiveWithin(Builder $query, int $minutes): Builder
    {
        $threshold = now()->subMinutes($minutes);
        
        return $query->whereHas('activities', function ($q) use ($threshold) {
            $q->where('last_activity', '>=', $threshold);
        });
    }

    /**
     * Scope a query to only include users with recent activity.
     */
    public function scopeWithRecentActivity(Builder $query): Builder
    {
        return $query->whereHas('activities');
    }

    /**
     * Get the user's current IP address from latest activity.
     */
    public function getCurrentIpAttribute(): ?string
    {
        return $this->latestActivity?->ip_address;
    }

    /**
     * Get the user's current user agent from latest activity.
     */
    public function getCurrentUserAgentAttribute(): ?string
    {
        return $this->latestActivity?->user_agent;
    }

    /**
     * Get the user's current route from latest activity.
     */
    public function getCurrentRouteAttribute(): ?string
    {
        return $this->latestActivity?->route_name;
    }

    /**
     * Get the user's current URL from latest activity.
     */
    public function getCurrentUrlAttribute(): ?string
    {
        return $this->latestActivity?->url;
    }

    /**
     * Check if user was online within specific minutes.
     */
    public function wasOnlineWithin(int $minutes): bool
    {
        $threshold = now()->subMinutes($minutes);
        
        return $this->activities()
            ->where('last_activity', '>=', $threshold)
            ->exists();
    }
}
