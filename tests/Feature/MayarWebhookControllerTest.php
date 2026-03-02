<?php

use App\Models\CreditPackage;
use App\Models\TopupTransaction;
use App\Models\User;
use App\Services\MayarService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'credits' => 10,
        'email_verified_at' => now(),
    ]);

    $this->package = CreditPackage::factory()->create([
        'credits' => 100,
        'price' => 50000,
    ]);

    $this->transaction = TopupTransaction::factory()->create([
        'user_id' => $this->user->id,
        'credit_package_id' => $this->package->id,
        'credits_added' => 100,
        'amount' => 50000,
        'status' => TopupTransaction::STATUS_PENDING,
        'mayar_transaction_id' => 'mayar-txn-123',
    ]);
});

// ── Signature Verification ─────────────────────────────────────

test('webhook rejects invalid signature', function () {
    $payload = json_encode(['id' => 'mayar-txn-123', 'status' => 'PAID']);

    $this->postJson('/webhooks/mayar', json_decode($payload, true), [
        'X-Mayar-Signature' => 'invalid-signature',
        'Content-Type' => 'application/json',
    ])->assertStatus(401);
});

// ── Successful Payment ─────────────────────────────────────────

test('webhook processes successful payment and adds credits', function () {
    $payload = json_encode([
        'id' => 'mayar-txn-123',
        'status' => 'PAID',
        'event' => 'payment.success',
        'metadata' => ['reference_id' => (string) $this->transaction->id],
    ]);

    // Mock the signature verification to pass
    $this->mock(MayarService::class, function ($mock) {
        $mock->shouldReceive('verifyWebhookSignature')->once()->andReturn(true);
    });

    $this->postJson('/webhooks/mayar', json_decode($payload, true), [
        'X-Mayar-Signature' => 'valid-sig',
    ])->assertStatus(200);

    // Verify transaction was marked as successful
    $this->transaction->refresh();
    expect($this->transaction->status)->toBe(TopupTransaction::STATUS_SUCCESS);
    expect($this->transaction->paid_at)->not->toBeNull();
    expect($this->transaction->mayar_payload)->toBeArray();

    // Verify user received credits
    $this->user->refresh();
    expect($this->user->credits)->toBe(110); // 10 initial + 100 from topup
});

// ── Idempotency ────────────────────────────────────────────────

test('webhook handles idempotent calls gracefully', function () {
    // Mark transaction as already processed
    $this->transaction->update([
        'status' => TopupTransaction::STATUS_SUCCESS,
        'paid_at' => now(),
    ]);

    $payload = json_encode([
        'id' => 'mayar-txn-123',
        'status' => 'PAID',
        'event' => 'payment.success',
    ]);

    $this->mock(MayarService::class, function ($mock) {
        $mock->shouldReceive('verifyWebhookSignature')->once()->andReturn(true);
    });

    $this->postJson('/webhooks/mayar', json_decode($payload, true), [
        'X-Mayar-Signature' => 'valid-sig',
    ])->assertStatus(200);

    // Credits should NOT be added again
    $this->user->refresh();
    expect($this->user->credits)->toBe(10); // Unchanged
});

// ── Transaction Not Found ──────────────────────────────────────

test('webhook returns 200 for unknown transactions', function () {
    $payload = json_encode([
        'id' => 'unknown-txn-999',
        'status' => 'PAID',
        'event' => 'payment.success',
    ]);

    $this->mock(MayarService::class, function ($mock) {
        $mock->shouldReceive('verifyWebhookSignature')->once()->andReturn(true);
    });

    $this->postJson('/webhooks/mayar', json_decode($payload, true), [
        'X-Mayar-Signature' => 'valid-sig',
    ])->assertStatus(200);
});

// ── Non-Payment Events ─────────────────────────────────────────

test('webhook ignores non-payment events', function () {
    $payload = json_encode([
        'id' => 'mayar-txn-123',
        'status' => 'PENDING',
        'event' => 'payment.created',
    ]);

    $this->mock(MayarService::class, function ($mock) {
        $mock->shouldReceive('verifyWebhookSignature')->once()->andReturn(true);
    });

    $this->postJson('/webhooks/mayar', json_decode($payload, true), [
        'X-Mayar-Signature' => 'valid-sig',
    ])->assertStatus(200);

    // Transaction should remain pending
    $this->transaction->refresh();
    expect($this->transaction->status)->toBe(TopupTransaction::STATUS_PENDING);

    // Credits should NOT be added
    $this->user->refresh();
    expect($this->user->credits)->toBe(10);
});

// ── Reference ID Lookup ────────────────────────────────────────

test('webhook finds transaction by reference ID when mayar ID not found', function () {
    // Remove the mayar_transaction_id so it falls back to reference_id lookup
    $this->transaction->update(['mayar_transaction_id' => null]);

    $payload = json_encode([
        'id' => 'some-other-id',
        'status' => 'PAID',
        'event' => 'payment.success',
        'metadata' => ['reference_id' => (string) $this->transaction->id],
    ]);

    $this->mock(MayarService::class, function ($mock) {
        $mock->shouldReceive('verifyWebhookSignature')->once()->andReturn(true);
    });

    $this->postJson('/webhooks/mayar', json_decode($payload, true), [
        'X-Mayar-Signature' => 'valid-sig',
    ])->assertStatus(200);

    $this->transaction->refresh();
    expect($this->transaction->status)->toBe(TopupTransaction::STATUS_SUCCESS);

    $this->user->refresh();
    expect($this->user->credits)->toBe(110);
});
