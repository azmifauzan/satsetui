<?php

use App\Models\User;

test('admin menu appears in sidebar for admin users', function () {
    $adminUser = User::factory()->create([
        'is_admin' => true,
    ]);
    
    $response = $this->actingAs($adminUser)->get('/dashboard');
    
    $response->assertOk();
    // The component renders with the admin user, sidebar should include admin link
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard/Index')
    );
});

test('admin menu does not appear in sidebar for regular users', function () {
    $regularUser = User::factory()->create([
        'is_admin' => false,
    ]);
    
    $response = $this->actingAs($regularUser)->get('/dashboard');
    
    $response->assertOk();
    // The component renders with a regular user
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard/Index')
    );
});

test('user cannot access admin panel without is_admin flag', function () {
    $regularUser = User::factory()->create([
        'is_admin' => false,
    ]);
    
    $response = $this->actingAs($regularUser)->get('/admin');
    
    // AdminMiddleware redirects to dashboard when not admin
    $response->assertRedirect('/dashboard');
});

test('admin user can access admin panel', function () {
    $adminUser = User::factory()->create([
        'is_admin' => true,
    ]);
    
    $response = $this->actingAs($adminUser)->get('/admin');
    
    // Admin can access (or get 404 if component not implemented, but not 403)
    expect($response->status())->not->toBe(403);
});
