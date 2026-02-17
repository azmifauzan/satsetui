<?php

use App\Models\LlmModel;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($this->admin);
});

it('updates model configuration without overwriting existing base_url when not provided', function () {
    // Create a model with existing base_url
    $model = LlmModel::factory()->create([
        'provider' => 'gemini',
        'model_name' => 'gemini-1.5-pro',
        'base_url' => 'https://custom-api.example.com/v1',
        'base_credits' => 5,
        'is_active' => true,
    ]);

    // Update without providing base_url (empty string)
    $response = $this->put("/admin/models/{$model->id}", [
        'provider' => 'gemini',
        'model_name' => 'gemini-2.0-flash',
        'api_key' => '',  // Empty, should not update
        'base_url' => '',  // Empty, should not overwrite existing
        'base_credits' => 10,
        'is_active' => false,
    ]);

    $response->assertRedirect('/admin/models');

    // Refresh model from database
    $model->refresh();

    // Check that base_url is preserved
    expect($model->base_url)->toBe('https://custom-api.example.com/v1');
    
    // Check that other fields are updated
    expect($model->model_name)->toBe('gemini-2.0-flash');
    expect($model->base_credits)->toBe(10);
    expect($model->is_active)->toBeFalse();
});

it('updates base_url when provided', function () {
    $model = LlmModel::factory()->create([
        'provider' => 'openai',
        'model_name' => 'gpt-4',
        'base_url' => 'https://old-api.example.com/v1',
        'base_credits' => 5,
    ]);

    $newBaseUrl = 'https://new-api.example.com/v2';

    $response = $this->put("/admin/models/{$model->id}", [
        'provider' => 'openai',
        'model_name' => 'gpt-4-turbo',
        'api_key' => '',
        'base_url' => $newBaseUrl,
        'base_credits' => 8,
        'is_active' => true,
    ]);

    $response->assertRedirect('/admin/models');

    $model->refresh();

    expect($model->base_url)->toBe($newBaseUrl);
    expect($model->model_name)->toBe('gpt-4-turbo');
});

it('does not update api_key when empty string provided', function () {
    $originalApiKey = 'original-secret-key';
    
    $model = LlmModel::factory()->create([
        'provider' => 'gemini',
        'model_name' => 'gemini-pro',
        'api_key' => $originalApiKey,
        'base_credits' => 5,
    ]);

    $response = $this->put("/admin/models/{$model->id}", [
        'provider' => 'gemini',
        'model_name' => 'gemini-pro-updated',
        'api_key' => '',  // Empty, should not update
        'base_url' => '',
        'base_credits' => 7,
        'is_active' => true,
    ]);

    $response->assertRedirect('/admin/models');

    $model->refresh();

    // API key should remain unchanged
    expect($model->api_key)->toBe($originalApiKey);
    expect($model->model_name)->toBe('gemini-pro-updated');
});

it('updates api_key when provided', function () {
    $model = LlmModel::factory()->create([
        'provider' => 'openai',
        'model_name' => 'gpt-3.5-turbo',
        'api_key' => 'old-key',
        'base_credits' => 5,
    ]);

    $newApiKey = 'new-secret-key';

    $response = $this->put("/admin/models/{$model->id}", [
        'provider' => 'openai',
        'model_name' => 'gpt-3.5-turbo',
        'api_key' => $newApiKey,
        'base_url' => '',
        'base_credits' => 5,
        'is_active' => true,
    ]);

    $response->assertRedirect('/admin/models');

    $model->refresh();

    expect($model->api_key)->toBe($newApiKey);
});

it('requires valid provider', function () {
    $model = LlmModel::factory()->create();

    $response = $this->put("/admin/models/{$model->id}", [
        'provider' => 'invalid-provider',
        'model_name' => 'test-model',
        'api_key' => '',
        'base_url' => '',
        'base_credits' => 5,
        'is_active' => true,
    ]);

    $response->assertSessionHasErrors('provider');
});

it('requires valid base_credits minimum value', function () {
    $model = LlmModel::factory()->create();

    $response = $this->put("/admin/models/{$model->id}", [
        'provider' => 'gemini',
        'model_name' => 'test-model',
        'api_key' => '',
        'base_url' => '',
        'base_credits' => 0,  // Invalid, must be at least 1
        'is_active' => true,
    ]);

    $response->assertSessionHasErrors('base_credits');
});

it('requires authentication as admin', function () {
    auth()->logout();
    
    $model = LlmModel::factory()->create();

    $response = $this->put("/admin/models/{$model->id}", [
        'provider' => 'gemini',
        'model_name' => 'test-model',
        'api_key' => '',
        'base_url' => '',
        'base_credits' => 5,
        'is_active' => true,
    ]);

    $response->assertRedirect('/login');
});
