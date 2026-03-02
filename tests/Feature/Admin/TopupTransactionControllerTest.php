<?php

use App\Models\TopupTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create([
        'is_admin' => true,
        'email_verified_at' => now(),
    ]);

    $this->user = User::factory()->create([
        'is_admin' => false,
        'email_verified_at' => now(),
    ]);
});

// ── Index ──────────────────────────────────────────────────────

test('admin can view topup transactions index', function () {
    TopupTransaction::factory()->count(3)->create();

    $this->actingAs($this->admin)
        ->get(route('admin.topup-transactions.index'))
        ->assertSuccessful();
});

test('non-admin cannot view topup transactions index', function () {
    $this->actingAs($this->user)
        ->get(route('admin.topup-transactions.index'))
        ->assertForbidden();
});

// ── Filters ────────────────────────────────────────────────────

test('admin can filter transactions by status', function () {
    TopupTransaction::factory()->successful()->count(2)->create();
    TopupTransaction::factory()->create(['status' => TopupTransaction::STATUS_PENDING]);

    $this->actingAs($this->admin)
        ->get(route('admin.topup-transactions.index', ['status' => 'success']))
        ->assertSuccessful();
});

test('admin can filter transactions by search term', function () {
    $userJohn = User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
    TopupTransaction::factory()->create(['user_id' => $userJohn->id]);
    TopupTransaction::factory()->count(2)->create();

    $this->actingAs($this->admin)
        ->get(route('admin.topup-transactions.index', ['search' => 'John']))
        ->assertSuccessful();
});

test('admin can filter transactions by date range', function () {
    TopupTransaction::factory()->create(['created_at' => now()->subDays(10)]);
    TopupTransaction::factory()->create(['created_at' => now()]);

    $this->actingAs($this->admin)
        ->get(route('admin.topup-transactions.index', [
            'date_from' => now()->subDay()->toDateString(),
            'date_to' => now()->toDateString(),
        ]))
        ->assertSuccessful();
});

// ── Stats ──────────────────────────────────────────────────────

test('admin sees correct summary stats', function () {
    TopupTransaction::factory()->successful()->create([
        'amount' => 50000,
        'credits_added' => 100,
    ]);
    TopupTransaction::factory()->successful()->create([
        'amount' => 100000,
        'credits_added' => 250,
    ]);
    TopupTransaction::factory()->create([
        'status' => TopupTransaction::STATUS_PENDING,
    ]);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.topup-transactions.index'))
        ->assertSuccessful();

    $stats = $response->original->getData()['page']['props']['stats'];

    expect($stats['total_revenue'])->toBe(150000);
    expect($stats['total_credits_sold'])->toBe(350);
    expect($stats['total_transactions'])->toBe(2);
    expect($stats['pending_count'])->toBe(1);
});
