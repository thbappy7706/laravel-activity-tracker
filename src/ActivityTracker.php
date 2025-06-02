<?php

namespace thbappy7706\ActivityTracker;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use thbappy7706\ActivityTracker\Models\UserActivity;

class ActivityTracker
{
    /**
     * Track user activity from the given request.
     */
    public function track(Request $request): void
    {
        $userId = Auth::id();
        $sessionId = $request->session()->getId();

        // Use cache to prevent excessive database writes
        $cacheKey = $this->getCacheKey($userId, $sessionId);
        
        if (Cache::has($cacheKey)) {
            return;
        }

        $this->updateOrCreateActivity($request, $userId, $sessionId);

        // Cache for 1 minute to reduce database hits
        Cache::put($cacheKey, true, 60);
    }

    /**
     * Update or create activity record.
     */
    protected function updateOrCreateActivity(Request $request, $userId, $sessionId): void
    {
        $data = [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'route_name' => $request->route()?->getName(),
            'url' => $request->url(),
            'last_activity' => now(),
        ];

        if ($userId) {
            UserActivity::updateOrCreate(
                ['user_id' => $userId],
                array_merge($data, ['session_id' => $sessionId])
            );
        } else {
            UserActivity::updateOrCreate(
                ['session_id' => $sessionId],
                $data
            );
        }
    }

    /**
     * Generate cache key for activity tracking.
     */
    protected function getCacheKey($userId, $sessionId): string
    {
        return 'activity_tracker:' . ($userId ?: 'session_' . $sessionId);
    }

    /**
     * Check if a user is currently online.
     */
    public function isUserOnline($userId): bool
    {
        $threshold = now()->subMinutes(config('activity-tracker.online_threshold', 5));
        
        return UserActivity::where('user_id', $userId)
            ->where('last_activity', '>=', $threshold)
            ->exists();
    }

    /**
     * Get the last seen time for a user in human readable format.
     */
    public function getLastSeen($userId): ?string
    {
        $activity = UserActivity::where('user_id', $userId)
            ->latest('last_activity')
            ->first();

        return $activity?->last_activity?->diffForHumans();
    }

    /**
     * Get all currently online users.
     */
    public function getOnlineUsers(): Collection
    {
        return UserActivity::online()
            ->whereNotNull('user_id')
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter(); // Remove null users
    }

    /**
     * Get the count of online users.
     */
    public function getOnlineCount(): int
    {
        return UserActivity::online()
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count();
    }

    /**
     * Get the latest activity record for a user.
     */
    public function getUserActivity($userId): ?UserActivity
    {
        return UserActivity::where('user_id', $userId)
            ->latest('last_activity')
            ->first();
    }

    /**
     * Get all activities for a user.
     */
    public function getUserActivities($userId, int $limit = 10): Collection
    {
        return UserActivity::where('user_id', $userId)
            ->latest('last_activity')
            ->limit($limit)
            ->get();
    }

    /**
     * Clean up old activity records.
     */
    public function cleanup(): int
    {
        $days = config('activity-tracker.cleanup_after_days');
        
        if (!$days) {
            return 0;
        }

        $threshold = now()->subDays($days);
        
        return UserActivity::where('last_activity', '<', $threshold)->delete();
    }

    /**
     * Get online users count by time period.
     */
    public function getOnlineCountByPeriod(int $minutes): int
    {
        $threshold = now()->subMinutes($minutes);
        
        return UserActivity::where('last_activity', '>=', $threshold)
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count();
    }

    /**
     * Check if a session is active.
     */
    public function isSessionActive(string $sessionId): bool
    {
        $threshold = now()->subMinutes(config('activity-tracker.online_threshold', 5));
        
        return UserActivity::where('session_id', $sessionId)
            ->where('last_activity', '>=', $threshold)
            ->exists();
    }
}
