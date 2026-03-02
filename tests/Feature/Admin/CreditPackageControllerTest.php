<?php

use App\Models\CreditPackage;
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

test('admin can view credit packages index', function () {
    CreditPackage::factory()->count(3)->create();

    $this->actingAs($this->admin)
        ->get(route('admin.credit-packages.index'))
        ->assertSuccessful();
});

test('non-admin cannot view credit packages index', function () {
    $this->actingAs($this->user)
        ->get(route('admin.credit-packages.index'))
        ->assertForbidden();
});

test('guest cannot view credit packages index', function () {
    $this->get(route('admin.credit-packages.index'))
        ->assertRedirect();
});

// ── Create ─────────────────────────────────────────────────────

test('admin can view create package form', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.credit-packages.create'))
        ->assertSuccessful();
});

test('admin can store a new credit package', function () {
    $data = [
        'name' => 'Paket Starter',
        'description' => 'Paket kredit untuk pemula',
        'credits' => 100,
        'price' => 50000,
        'is_active' => true,
        'sort_order' => 1,
    ];

    $this->actingAs($this->admin)
        ->post(route('admin.credit-packages.store'), $data)
        ->assertRedirect(route('admin.credit-packages.index'));

    $this->assertDatabaseHas('credit_packages', [
        'name' => 'Paket Starter',
        'credits' => 100,
        'price' => 50000,
    ]);
});

test('store requires name and credits and price', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.credit-packages.store'), [])
        ->assertSessionHasErrors(['name', 'credits', 'price']);
});

test('store validates minimum values', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.credit-packages.store'), [
            'name' => 'Test',
            'credits' => 0,
            'price' => 500,
        ])
        ->assertSessionHasErrors(['credits', 'price']);
});

test('non-admin cannot store a credit package', function () {
    $this->actingAs($this->user)
        ->post(route('admin.credit-packages.store'), [
            'name' => 'Paket',
            'credits' => 100,
            'price' => 50000,
        ])
        ->assertForbidden();
});

// ── Edit / Update ──────────────────────────────────────────────

test('admin can view edit form', function () {
    $package = CreditPackage::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.credit-packages.edit', $package))
        ->assertSuccessful();
});

test('admin can update a credit package', function () {
    $package = CreditPackage::factory()->create(['name' => 'Old Name']);

    $this->actingAs($this->admin)
        ->put(route('admin.credit-packages.update', $package), [
            'name' => 'New Name',
            'credits' => 200,
            'price' => 100000,
            'is_active' => false,
            'sort_order' => 5,
        ])
        ->assertRedirect(route('admin.credit-packages.index'));

    $package->refresh();
    expect($package->name)->toBe('New Name');
    expect($package->credits)->toBe(200);
    expect($package->price)->toBe(100000);
    expect($package->is_active)->toBeFalse();
});

test('non-admin cannot update a credit package', function () {
    $package = CreditPackage::factory()->create();

    $this->actingAs($this->user)
        ->put(route('admin.credit-packages.update', $package), [
            'name' => 'Hacked',
            'credits' => 999,
            'price' => 1000,
        ])
        ->assertForbidden();
});

// ── Delete / Restore ───────────────────────────────────────────

test('admin can soft-delete a credit package', function () {
    $package = CreditPackage::factory()->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.credit-packages.destroy', $package))
        ->assertRedirect(route('admin.credit-packages.index'));

    $this->assertSoftDeleted('credit_packages', ['id' => $package->id]);
});

test('admin can restore a soft-deleted package', function () {
    $package = CreditPackage::factory()->create();
    $package->delete();

    $this->actingAs($this->admin)
        ->post(route('admin.credit-packages.restore', $package->id))
        ->assertRedirect(route('admin.credit-packages.index'));

    expect($package->fresh()->deleted_at)->toBeNull();
});

// ── Toggle Active ──────────────────────────────────────────────

test('admin can toggle active status', function () {
    $package = CreditPackage::factory()->create(['is_active' => true]);

    $this->actingAs($this->admin)
        ->post(route('admin.credit-packages.toggle-active', $package))
        ->assertRedirect();

    expect($package->fresh()->is_active)->toBeFalse();

    $this->actingAs($this->admin)
        ->post(route('admin.credit-packages.toggle-active', $package))
        ->assertRedirect();

    expect($package->fresh()->is_active)->toBeTrue();
});
