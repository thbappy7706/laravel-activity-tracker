<?php

namespace thbappy7706\ActivityTracker\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use thbappy7706\ActivityTracker\Middleware\TrackUserActivity;
use thbappy7706\ActivityTracker\Models\UserActivity;
use thbappy7706\ActivityTracker\Tests\TestCase;
use thbappy7706\ActivityTracker\Tests\User;
use thbappy7706\ActivityTracker\ActivityTracker;

class MiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected TrackUserActivity $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new TrackUserActivity(new ActivityTracker());
    }

    /** @test */
    public function it_tracks_authenticated_user_activity()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Auth::login($user);

        $request = Request::create('/test', 'GET');
        $request->setSession(app('session.store'));

        $response = $this->middleware->handle($request, function ($req) {
            return new Response('OK');
        });

        $this->assertEquals('OK', $response->getContent());
        $this->assertDatabaseHas('user_activities', [
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function it_skips_tracking_for_ignored_routes()
    {
        config(['activity-tracker.ignored_routes' => ['api/*', 'admin/*']]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Auth::login($user);

        $request = Request::create('/api/users', 'GET');
        $request->setSession(app('session.store'));

        $this->middleware->handle($request, function ($req) {
            return new Response('OK');
        });

        $this->assertDatabaseMissing('user_activities', [
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function it_skips_tracking_anonymous_users_when_disabled()
    {
        config(['activity-tracker.track_anonymous' => false]);

        $request = Request::create('/test', 'GET');
        $request->setSession(app('session.store'));

        $this->middleware->handle($request, function ($req) {
            return new Response('OK');
        });

        $this->assertDatabaseMissing('user_activities', [
            'session_id' => $request->session()->getId(),
        ]);
    }

    /** @test */
    public function it_tracks_anonymous_users_when_enabled()
    {
        config(['activity-tracker.track_anonymous' => true]);

        $request = Request::create('/test', 'GET');
        $request->setSession(app('session.store'));

        $this->middleware->handle($request, function ($req) {
            return new Response('OK');
        });

        $this->assertDatabaseHas('user_activities', [
            'session_id' => $request->session()->getId(),
            'user_id' => null,
        ]);
    }

    /** @test */
    public function it_skips_tracking_for_unsuccessful_responses()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Auth::login($user);

        $request = Request::create('/test', 'GET');
        $request->setSession(app('session.store'));

        $this->middleware->handle($request, function ($req) {
            return new Response('Not Found', 404);
        });

        $this->assertDatabaseMissing('user_activities', [
            'user_id' => $user->id,
        ]);
    }
}
