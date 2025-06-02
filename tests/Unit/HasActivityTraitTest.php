<?php

namespace thbappy7706\ActivityTracker\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use thbappy7706\ActivityTracker\Models\UserActivity;
use thbappy7706\ActivityTracker\Tests\TestCase;
use thbappy7706\ActivityTracker\Tests\User;

class HasActivityTraitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_activities_relationship()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        UserActivity::create([
            'user_id' => $user->id,
            'session_id' => 'test-session',
            'ip_address' => '127.0.0.1',
            'last_activity' => now(),
        ]);

        $this->assertCount(1, $user->activities);
        $this->assertInstanceOf(UserActivity::class, $user->activities->first());
    }

    /** @test */
    public function it_has_latest_activity_relationship()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create older activity
        UserActivity::create([
            'user_id' => $user->id,
            'session_id' => 'old-session',
            'ip_address' => '127.0.0.1',
            'last_activity' => now()->subHours(2),
        ]);

        // Create newer activity
        $latestActivity = UserActivity::create([
            'user_id' => $user->id,
            'session_id' => 'new-session',
            'ip_address' => '127.0.0.1',
            'last_activity' => now(),
        ]);

        $this->assertEquals($latestActivity->id, $user->latestActivity->id);
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
        $this->assertFalse($user->isOnline());

        // Create recent activity
        UserActivity::create([
            'user_id' => $user->id,
            'session_id' => 'test-session',
            'ip_address' => '127.0.0.1',
            'last_activity' => now(),
        ]);

        // Refresh the user model
        $user = $user->fresh();

        // User should be online now
        $this->assertTrue($user->isOnline());
    }

    /** @test */
    public function it_can_get_last_seen_attribute()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // No activity yet
        $this->assertNull($user->last_seen);

        // Create activity
        UserActivity::create([
            'user_id' => $user->id,
            'session_id' => 'test-session',
            'ip_address' => '127.0.0.1',
            'last_activity' => now()->subMinutes(10),
        ]);

        // Refresh the user model
        $user = $user->fresh();

        $this->assertNotNull($user->last_seen);
        $this->assertStringContains('10 minutes ago', $user->last_seen);
    }

    /** @test */
    public function it_can_scope_online_users()
    {
        $onlineUser = User::create([
            'name' => 'Online User',
            'email' => 'online@example.com',
            'password' => bcrypt('password'),
        ]);

        $offlineUser = User::create([
            'name' => 'Offline User',
            'email' => 'offline@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create recent activity for online user
        UserActivity::create([
            'user_id' => $onlineUser->id,
            'session_id' => 'online-session',
            'ip_address' => '127.0.0.1',
            'last_activity' => now(),
        ]);

        // Create old activity for offline user
        UserActivity::create([
            'user_id' => $offlineUser->id,
            'session_id' => 'offline-session',
            'ip_address' => '127.0.0.1',
            'last_activity' => now()->subHours(1),
        ]);

        $onlineUsers = User::online()->get();

        $this->assertCount(1, $onlineUsers);
        $this->assertEquals($onlineUser->id, $onlineUsers->first()->id);
    }

    /** @test */
    public function it_can_scope_users_active_within_period()
    {
        $recentUser = User::create([
            'name' => 'Recent User',
            'email' => 'recent@example.com',
            'password' => bcrypt('password'),
        ]);

        $oldUser = User::create([
            'name' => 'Old User',
            'email' => 'old@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create activity within 30 minutes
        UserActivity::create([
            'user_id' => $recentUser->id,
            'session_id' => 'recent-session',
            'ip_address' => '127.0.0.1',
            'last_activity' => now()->subMinutes(20),
        ]);

        // Create activity older than 30 minutes
        UserActivity::create([
            'user_id' => $oldUser->id,
            'session_id' => 'old-session',
            'ip_address' => '127.0.0.1',
            'last_activity' => now()->subMinutes(40),
        ]);

        $activeUsers = User::activeWithin(30)->get();

        $this->assertCount(1, $activeUsers);
        $this->assertEquals($recentUser->id, $activeUsers->first()->id);
    }

    /** @test */
    public function it_can_check_if_user_was_online_within_period()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create activity 10 minutes ago
        UserActivity::create([
            'user_id' => $user->id,
            'session_id' => 'test-session',
            'ip_address' => '127.0.0.1',
            'last_activity' => now()->subMinutes(10),
        ]);

        $this->assertTrue($user->wasOnlineWithin(15));
        $this->assertFalse($user->wasOnlineWithin(5));
    }
}
