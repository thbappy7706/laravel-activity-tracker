<?php

namespace thbappy7706\ActivityTracker\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use thbappy7706\ActivityTracker\ActivityTracker;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    /**
     * The activity tracker instance.
     */
    protected ActivityTracker $activityTracker;

    /**
     * Create a new middleware instance.
     */
    public function __construct(ActivityTracker $activityTracker)
    {
        $this->activityTracker = $activityTracker;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Skip tracking for ignored routes
        if ($this->shouldIgnoreRoute($request)) {
            return $response;
        }

        // Skip if not tracking anonymous users and user is not authenticated
        if (!config('activity-tracker.track_anonymous', false) && !Auth::check()) {
            return $response;
        }

        // Skip for non-successful responses (optional)
        if (!$this->isSuccessfulResponse($response)) {
            return $response;
        }

        $this->activityTracker->track($request);

        return $response;
    }

    /**
     * Determine if the route should be ignored.
     */
    protected function shouldIgnoreRoute(Request $request): bool
    {
        $ignoredRoutes = config('activity-tracker.ignored_routes', []);
        
        foreach ($ignoredRoutes as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the response is successful.
     */
    protected function isSuccessfulResponse(Response $response): bool
    {
        return $response->getStatusCode() >= 200 && $response->getStatusCode() < 300;
    }
}
