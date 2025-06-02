<?php

namespace thbappy7706\ActivityTracker\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void track(\Illuminate\Http\Request $request)
 * @method static bool isUserOnline($userId)
 * @method static string|null getLastSeen($userId)
 * @method static \Illuminate\Support\Collection getOnlineUsers()
 * @method static int getOnlineCount()
 * @method static \thbappy7706\ActivityTracker\Models\UserActivity|null getUserActivity($userId)
 * @method static \Illuminate\Support\Collection getUserActivities($userId, int $limit = 10)
 * @method static int cleanup()
 * @method static int getOnlineCountByPeriod(int $minutes)
 * @method static bool isSessionActive(string $sessionId)
 *
 * @see \thbappy7706\ActivityTracker\ActivityTracker
 */
class ActivityTracker extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return \thbappy7706\ActivityTracker\ActivityTracker::class;
    }
}
