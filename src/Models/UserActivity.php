<?php

namespace thbappy7706\ActivityTracker\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class UserActivity extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'route_name',
        'url',
        'last_activity',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'last_activity' => 'datetime',
    ];

    /**
     * Create a new instance of the model.
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('activity-tracker.table_name', 'user_activities');
    }

    /**
     * Get the user that owns the activity.
     */
    public function user(): BelongsTo
    {
        $userModel = config('auth.providers.users.model', 'App\Models\User');
        
        return $this->belongsTo($userModel);
    }

    /**
     * Scope a query to only include online activities.
     */
    public function scopeOnline(Builder $query): Builder
    {
        $threshold = now()->subMinutes(config('activity-tracker.online_threshold', 5));
        
        return $query->where('last_activity', '>=', $threshold);
    }

    /**
     * Scope a query to only include activities for a specific user.
     */
    public function scopeForUser(Builder $query, $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include activities for a specific session.
     */
    public function scopeForSession(Builder $query, string $sessionId): Builder
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Scope a query to only include activities within a time period.
     */
    public function scopeWithinPeriod(Builder $query, int $minutes): Builder
    {
        $threshold = now()->subMinutes($minutes);
        
        return $query->where('last_activity', '>=', $threshold);
    }

    /**
     * Scope a query to only include authenticated user activities.
     */
    public function scopeAuthenticated(Builder $query): Builder
    {
        return $query->whereNotNull('user_id');
    }

    /**
     * Scope a query to only include anonymous user activities.
     */
    public function scopeAnonymous(Builder $query): Builder
    {
        return $query->whereNull('user_id');
    }

    /**
     * Check if this activity is considered online.
     */
    public function isOnline(): bool
    {
        $threshold = now()->subMinutes(config('activity-tracker.online_threshold', 5));
        
        return $this->last_activity >= $threshold;
    }

    /**
     * Get the last activity time in human readable format.
     */
    public function getLastActivityHumanAttribute(): string
    {
        return $this->last_activity->diffForHumans();
    }

    /**
     * Get the route name or URL if route name is not available.
     */
    public function getDisplayRouteAttribute(): string
    {
        return $this->route_name ?: $this->url;
    }
}
