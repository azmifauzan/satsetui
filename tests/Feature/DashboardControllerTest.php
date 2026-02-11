<?php

use App\Models\Generation;
use App\Models\User;
use Carbon\Carbon;

test('dashboard displays correct template statistics', function () {
    $user = User::factory()->create(['credits' => 100]);
    
    // Create completed generations for the user
    Generation::factory()->count(3)->create([
        'user_id' => $user->id,
        'status' => 'completed',
        'completed_at' => Carbon::now()->subDays(5),
    ]);
    
    // Create generations from this month
    Generation::factory()->count(2)->create([
        'user_id' => $user->id,
        'status' => 'completed',
        'completed_at' => Carbon::now()->subDays(2),
    ]);
    
    // Create an older generation (last month)
    Generation::factory()->create([
        'user_id' => $user->id,
        'status' => 'completed',
        'completed_at' => Carbon::now()->subMonth(),
    ]);
    
    // Create a pending generation (should not be counted)
    Generation::factory()->create([
        'user_id' => $user->id,
        'status' => 'processing',
    ]);
    
    $response = $this->actingAs($user)->get('/dashboard');
    
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard/Index')
        ->has('stats', fn ($stats) => $stats
            ->where('total_templates', 6) // 3 + 2 + 1 completed
            ->where('templates_this_month', 2) // Only 2 from this month
            ->where('credits_remaining', 100)
            ->whereType('last_generated', 'string') // Should be a human-readable time string
        )
    );
});

test('dashboard shows null for last_generated when no templates exist', function () {
    $user = User::factory()->create(['credits' => 50]);
    
    $response = $this->actingAs($user)->get('/dashboard');
    
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard/Index')
        ->has('stats', fn ($stats) => $stats
            ->where('total_templates', 0)
            ->where('templates_this_month', 0)
            ->where('credits_remaining', 50)
            ->where('last_generated', null)
        )
    );
});

test('dashboard only shows current user templates', function () {
    $user1 = User::factory()->create(['credits' => 100]);
    $user2 = User::factory()->create(['credits' => 50]);
    
    // Create templates for user1
    Generation::factory()->count(3)->create([
        'user_id' => $user1->id,
        'status' => 'completed',
        'completed_at' => Carbon::now(),
    ]);
    
    // Create templates for user2
    Generation::factory()->count(5)->create([
        'user_id' => $user2->id,
        'status' => 'completed',
        'completed_at' => Carbon::now(),
    ]);
    
    // User1 should only see their own templates
    $response = $this->actingAs($user1)->get('/dashboard');
    
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('stats', fn ($stats) => $stats
            ->where('total_templates', 3)
            ->where('templates_this_month', 3)
        )
    );
    
    // User2 should only see their own templates
    $response2 = $this->actingAs($user2)->get('/dashboard');
    
    $response2->assertOk();
    $response2->assertInertia(fn ($page) => $page
        ->has('stats', fn ($stats) => $stats
            ->where('total_templates', 5)
            ->where('templates_this_month', 5)
        )
    );
});

test('dashboard requires authentication', function () {
    $response = $this->get('/dashboard');
    
    $response->assertRedirect('/login');
});
