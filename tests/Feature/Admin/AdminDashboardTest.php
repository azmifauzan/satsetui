<?php

use App\Models\User;
use App\Models\Generation;
use App\Models\LlmModel;

test('admin can access dashboard', function () {
    $admin = User::factory()->create([
        'is_admin' => true,
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)->get('/admin');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Index')
        ->has('statistics')
        ->has('generationTrend')
        ->has('creditUsageTrend')
    );
});

test('non-admin cannot access dashboard', function () {
    $user = User::factory()->create([
        'is_admin' => false,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->get('/admin');

    $response->assertStatus(403);
});

test('guest cannot access dashboard', function () {
    $response = $this->get('/admin');

    $response->assertRedirect('/login');
});

test('admin dashboard shows correct statistics', function () {
    $admin = User::factory()->create([
        'is_admin' => true,
        'is_active' => true,
    ]);

    // Create test data
    User::factory()->count(10)->create();
    User::factory()->count(5)->create(['is_premium' => true]);

    $response = $this->actingAs($admin)->get('/admin');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->where('statistics.users.total', 16) // 10 + 5 + 1 admin
        ->where('statistics.users.premium', 5)
    );
});
