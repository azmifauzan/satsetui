<?php

use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create([
        'phone' => '081234567890',
    ]);
});

test('profile edit page renders for authenticated user', function () {
    $this->actingAs($this->user)
        ->get('/profile')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Profile/Edit')
            ->has('user')
            ->where('user.name', $this->user->name)
            ->where('user.email', $this->user->email)
            ->where('user.phone', '081234567890')
        );
});

test('profile edit requires authentication', function () {
    $this->get('/profile')
        ->assertRedirect('/login');
});

test('user can update profile name', function () {
    $this->actingAs($this->user)
        ->put('/profile', [
            'name' => 'Updated Name',
            'email' => $this->user->email,
            'phone' => $this->user->phone,
        ])
        ->assertRedirect();

    $this->user->refresh();
    expect($this->user->name)->toBe('Updated Name');
});

test('user can update profile phone', function () {
    $this->actingAs($this->user)
        ->put('/profile', [
            'name' => $this->user->name,
            'email' => $this->user->email,
            'phone' => '089876543210',
        ])
        ->assertRedirect();

    $this->user->refresh();
    expect($this->user->phone)->toBe('089876543210');
});

test('user can clear phone number', function () {
    $this->actingAs($this->user)
        ->put('/profile', [
            'name' => $this->user->name,
            'email' => $this->user->email,
            'phone' => '',
        ])
        ->assertRedirect();

    $this->user->refresh();
    expect($this->user->phone)->toBeNull();
});

test('phone number must be at least 10 characters', function () {
    $this->actingAs($this->user)
        ->put('/profile', [
            'name' => $this->user->name,
            'email' => $this->user->email,
            'phone' => '08123',
        ])
        ->assertSessionHasErrors('phone');
});

test('phone number only allows valid characters', function () {
    $this->actingAs($this->user)
        ->put('/profile', [
            'name' => $this->user->name,
            'email' => $this->user->email,
            'phone' => 'abc123invalid',
        ])
        ->assertSessionHasErrors('phone');
});

test('email must be unique', function () {
    $other = User::factory()->create();

    $this->actingAs($this->user)
        ->put('/profile', [
            'name' => $this->user->name,
            'email' => $other->email,
            'phone' => $this->user->phone,
        ])
        ->assertSessionHasErrors('email');
});

test('user can keep same email without unique error', function () {
    $this->actingAs($this->user)
        ->put('/profile', [
            'name' => 'Same Email Test',
            'email' => $this->user->email,
            'phone' => $this->user->phone,
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();
});

test('name is required for profile update', function () {
    $this->actingAs($this->user)
        ->put('/profile', [
            'name' => '',
            'email' => $this->user->email,
        ])
        ->assertSessionHasErrors('name');
});

test('topup page shows userPhone prop', function () {
    $this->actingAs($this->user)
        ->get('/credits/topup')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Credits/Topup')
            ->where('userPhone', '081234567890')
        );
});

test('topup page shows null userPhone when no phone set', function () {
    $userNoPhone = User::factory()->create(['phone' => null]);

    $this->actingAs($userNoPhone)
        ->get('/credits/topup')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Credits/Topup')
            ->where('userPhone', null)
        );
});
