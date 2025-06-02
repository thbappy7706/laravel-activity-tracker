<?php

namespace thbappy7706\ActivityTracker\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use thbappy7706\ActivityTracker\ActivityTracker;
use thbappy7706\ActivityTracker\Models\UserActivity;
use thbappy7706\ActivityTracker\Tests\TestCase;
use thbappy7706\ActivityTracker\Tests\User;

class ActivityTrackerTest extends TestCase
{
    use RefreshDatabase;

    protected ActivityTracker $tracker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tracker = new ActivityTracker();
    }

    /** @test */
    public function it_can_track_authenticated_user_activity()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Auth::login($user);

        $request = Request::create('/test', 'GET');
        $request->setSession(app('session.store'));

        $this->tracker->track($request);

        $this->assertDatabaseHas('user_activities', [
            'user_id' => $user->id,
            'url' => 'http://localhost/test',
        ]);
    }

    /** @test */
    public function it_can_check_if_user_is_online()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // User is not online initially
        $this->assertFalse($this->tracker->isUserOnline($user->id));

        // Create recent activity
        UserActivity::create([
            'user_id' => $user->id,
            'session_id' => 'test-session',
            'ip_address' => '127.0.0.1',
            'last_activity' => now(),
        ]);

        // User should be online now
        $this->assertTrue($this->tracker->isUserOnline($user->id));
    }

    /** @test */
    public function it_can_get_last_seen_time()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // No activity yet
        $this->assertNull($this->tracker->getLastSeen($user->id));

        // Create activity
        UserActivity::create([
            'user_id' => $user->id,
            'session_id' => 'test-session',
            'ip_address' => '127.0.0.1',
            'last_activity' => now()->subMinutes(10),
        ]);

        $lastSeen = $this->tracker->getLastSeen($user->id);
        $this->assertNotNull($lastSeen);
        $this->assertStringContains('10 minutes ago', $lastSeen);
    }

    /** @test */
    public function it_can_get_online_users_count()
    {
        $user1 = User::create([
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'password' => bcrypt('password'),
        ]);

        $user2 = User::create([
            'name' => 'User 2',
            'email' => 'user2@example.com',
            'password' => bcrypt('password'),
        ]);

        // No online users initially
        $this->assertEquals(0, $this->tracker->getOnlineCount());

        // Add online activity for user1
        UserActivity::create([
            'user_id' => $user1->id,
            'session_id' => 'session-1',
            'ip_address' => '127.0.0.1',
            'last_activity' => now(),
        ]);

        $this->assertEquals(1, $this->tracker->getOnlineCount());

        // Add online activity for user2
        UserActivity::create([
            'user_id' => $user2->id,
            'session_id' => 'session-2',
            'ip_address' => '127.0.0.1',
            'last_activity' => now(),
        ]);

        $this->assertEquals(2, $this->tracker->getOnlineCount());
    }

    /** @test */
    public function it_can_cleanup_old_activities()
    {
        config(['activity-tracker.cleanup_after_days' => 7]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create old activity
        UserActivity::create([
            'user_id' => $user->id,
            'session_id' => 'old-session',
            'ip_address' => '127.0.0.1',
            'last_activity' => now()->subDays(10),
        ]);

        // Create recent activity
        UserActivity::create([
            'user_id' => $user->id,
            'session_id' => 'recent-session',
            'ip_address' => '127.0.0.1',
            'last_activity' => now(),
        ]);

        $this->assertEquals(2, UserActivity::count());

        $deleted = $this->tracker->cleanup();

        $this->assertEquals(1, $deleted);
        $this->assertEquals(1, UserActivity::count());
    }

    /** @test */
    public function it_respects_online_threshold_configuration()
    {
        config(['activity-tracker.online_threshold' => 10]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Activity within threshold
        UserActivity::create([
            'user_id' => $user->id,
            'session_id' => 'session-1',
            'ip_address' => '127.0.0.1',
            'last_activity' => now()->subMinutes(5),
        ]);

        $this->assertTrue($this->tracker->isUserOnline($user->id));

        // Activity outside threshold
        UserActivity::updateOrCreate(
            ['user_id' => $user->id],
            ['last_activity' => now()->subMinutes(15)]
        );

        $this->assertFalse($this->tracker->isUserOnline($user->id));
    }
}
