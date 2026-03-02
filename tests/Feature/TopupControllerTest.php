<?php

use App\Models\CreditPackage;
use App\Models\TopupTransaction;
use App\Models\User;
use App\Services\MayarService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'credits' => 50,
        'email_verified_at' => now(),
    ]);
});

// ── Index Page ─────────────────────────────────────────────────

test('authenticated user can view topup page', function () {
    CreditPackage::factory()->count(3)->create();

    $this->actingAs($this->user)
        ->get(route('topup.index'))
        ->assertSuccessful();
});

test('topup page only shows active packages', function () {
    CreditPackage::factory()->create(['name' => 'Active Package', 'is_active' => true]);
    CreditPackage::factory()->create(['name' => 'Inactive Package', 'is_active' => false]);

    $response = $this->actingAs($this->user)
        ->get(route('topup.index'))
        ->assertSuccessful();

    $packages = $response->original->getData()['page']['props']['packages'];

    expect($packages)->toHaveCount(1);
    expect($packages[0]['name'])->toBe('Active Package');
});

test('guest cannot access topup page', function () {
    $this->get(route('topup.index'))
        ->assertRedirect();
});

// ── Initiate Topup ─────────────────────────────────────────────

test('user can initiate a topup', function () {
    $package = CreditPackage::factory()->create([
        'credits' => 200,
        'price' => 100000,
    ]);

    $this->mock(MayarService::class, function ($mock) {
        $mock->shouldReceive('createPaymentLink')
            ->once()
            ->andReturn([
                'id' => 'mayar-payment-id-123',
                'link' => 'https://checkout.mayar.id/pay/123',
            ]);
    });

    $response = $this->actingAs($this->user)
        ->post(route('topup.initiate'), [
            'credit_package_id' => $package->id,
        ]);

    $response->assertRedirect('https://checkout.mayar.id/pay/123');

    $this->assertDatabaseHas('topup_transactions', [
        'user_id' => $this->user->id,
        'credit_package_id' => $package->id,
        'amount' => 100000,
        'credits_added' => 200,
        'status' => TopupTransaction::STATUS_PENDING,
        'mayar_transaction_id' => 'mayar-payment-id-123',
    ]);
});

test('initiate topup requires valid credit package id', function () {
    $this->actingAs($this->user)
        ->post(route('topup.initiate'), [
            'credit_package_id' => 99999,
        ])
        ->assertSessionHasErrors('credit_package_id');
});

test('initiate topup cleans up on payment gateway failure', function () {
    $package = CreditPackage::factory()->create();

    $this->mock(MayarService::class, function ($mock) {
        $mock->shouldReceive('createPaymentLink')
            ->once()
            ->andThrow(new RuntimeException('Gateway error'));
    });

    $this->actingAs($this->user)
        ->post(route('topup.initiate'), [
            'credit_package_id' => $package->id,
        ])
        ->assertSessionHasErrors('payment');

    // Transaction record should have been cleaned up
    $this->assertDatabaseCount('topup_transactions', 0);
});

test('initiate topup rejects inactive packages', function () {
    $package = CreditPackage::factory()->inactive()->create();

    $this->actingAs($this->user)
        ->post(route('topup.initiate'), [
            'credit_package_id' => $package->id,
        ]);

    // Should get 404 since findOrFail with active() scope won't find it
    // The validation passes (exists in DB) but controller uses active()->findOrFail() which throws 404
    $this->assertDatabaseCount('topup_transactions', 0);
});

// ── Callback Page ──────────────────────────────────────────────

test('user can view callback page for own transaction', function () {
    $transaction = TopupTransaction::factory()->create([
        'user_id' => $this->user->id,
        'status' => TopupTransaction::STATUS_SUCCESS,
    ]);

    $this->actingAs($this->user)
        ->get(route('topup.callback', $transaction))
        ->assertSuccessful();
});

test('user cannot view callback page for another users transaction', function () {
    $otherUser = User::factory()->create();
    $transaction = TopupTransaction::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    $this->actingAs($this->user)
        ->get(route('topup.callback', $transaction))
        ->assertForbidden();
});
