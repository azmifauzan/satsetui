<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

test('new users can register', function () {
    Event::fake([Registered::class]);

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'terms' => true,
    ]);

    $response->assertRedirect('/login');
    $response->assertSessionHas('status', 'Silakan cek email Anda untuk verifikasi akun.');

    $user = User::where('email', 'test@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->name)->toBe('Test User');
    expect($user->email_verified_at)->toBeNull();

    Event::assertDispatched(Registered::class);
});

test('user must verify email before accessing dashboard', function () {
    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertRedirect(route('verification.notice'));
});

test('verified user can access dashboard', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertOk();
});

test('user can verify email with valid link', function () {
    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);

    $verificationUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $response = $this->actingAs($user)->get($verificationUrl);

    expect($user->fresh()->email_verified_at)->not->toBeNull();
    $response->assertRedirect('/dashboard?verified=1');
});

test('user cannot register with invalid terms', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'terms' => false,
    ]);

    $response->assertSessionHasErrors(['terms']);
});

test('verification notification can be resent', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);

    $response = $this->actingAs($user)->post(route('verification.send'));

    $response->assertRedirect();
    $response->assertSessionHas('status', 'Link verifikasi telah dikirim ke email Anda!');
});
