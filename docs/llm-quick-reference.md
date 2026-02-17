# Quick Reference - Sistem LLM SatsetUI

Panduan singkat untuk developer yang bekerja dengan sistem LLM dan kredit di SatsetUI.

**Last Updated:** 16 Februari 2026

## 🎯 Model LLM (2-Model System)

| Tipe | Default Model | Provider | Kredit |
|------|---------------|----------|--------|
| **Satset** | `gemini-2.0-flash-exp` | Gemini | 6 |
| **Expert** | `gemini-2.5-pro-preview` | Gemini | 15 |

> Admin dapat mengubah model name, provider, API key, dan base URL via Admin Panel.

## 💰 Kredit System

```php
// Default credits saat registrasi
100 kredit (gratis)

// Nilai kredit
1 kredit = Rp 100 (configurable via AdminSetting)

// Pembulatan
SELALU dibulatkan ke atas (CEIL)

// Formula
subtotal = base_credits + extra_page_credits
total = CEIL(subtotal × (1 + error_margin) × (1 + profit_margin))
```

## 🔧 Usage Examples

### Get Available Models

```php
use App\Models\LlmModel;

// Get all active models
$models = LlmModel::active()->ordered()->get();

// Get by type
$satset = LlmModel::where('model_type', 'satset')->active()->first();
$expert = LlmModel::where('model_type', 'expert')->active()->first();
```

### Start Generation

```php
use App\Services\GenerationService;

$service = app(GenerationService::class);

$result = $service->startGeneration(
    blueprint: $blueprint,
    user: $user,
    modelName: 'satset', // or 'expert'
    projectName: 'My Template'
);

// Response
[
    'success' => true,
    'generation_id' => 123,
    'model' => 'gemini-2.0-flash-exp',
    'credits_charged' => 7
]
```

### Generate Next Page (with retry & context)

```php
$result = $service->generateNextPage($generation, retryCount: 0);
// Includes context from last 2 pages for consistency
// Auto-retries up to 3x with exponential backoff
```

### Refine Generation

```php
$result = $service->refineGeneration($generation, 'Make the sidebar darker');
// Stores RefinementMessage records for conversation history
```

### Credit Operations

```php
use App\Services\CreditService;

$creditService = app(CreditService::class);

// Charge credits
$creditService->charge($user, $amount, $generation, 'Template generation');

// Refund credits
$creditService->refund($user, $amount, $generation, 'Generation failed');

// Admin adjustment
$creditService->adminAdjustment($user, $amount, $admin, 'Bonus credits');

// Get statistics
$stats = $creditService->getStatistics($user);
```

### Cost Tracking

```php
use App\Services\CostTrackingService;

$costService = app(CostTrackingService::class);

// Record actual LLM costs
$costService->recordCost($generation, $pageGeneration, [
    'input_tokens' => 12500,
    'output_tokens' => 52300,
    'model_name' => 'gemini-2.0-flash-exp',
    'provider' => 'gemini',
]);
```

## 📊 Database Queries

```php
use App\Models\LlmModel;

// Active models
$models = LlmModel::active()->ordered()->get();

// By type
$model = LlmModel::where('model_type', 'satset')->first();
$model = LlmModel::where('model_type', 'expert')->first();

// Display name (computed)
$model->display_name; // "Satset — Cepat & Efisien"
$model->description;  // auto-generated from model_type
```

## 🔐 API Configuration

```env
# .env - Primary LLM gateway
LLM_API_KEY=your-api-key-here
LLM_BASE_URL=https://ai.sumopod.com/v1

# Alternative: per-model config via Admin Panel > LLM Models
# API keys stored encrypted in database
```

## 🧪 Testing

```bash
# Run all tests
php artisan test --compact

# Specific test files
php artisan test --compact --filter=OpenAICompatibleService
php artisan test --compact --filter=McpPromptBuilder
php artisan test --compact --filter=GenerationController
php artisan test --compact --filter=CreditEstimationService
```

## 🗄️ Seeders

```bash
# Seed everything (admin user, models, settings)
php artisan db:seed

# Individual seeders
php artisan db:seed --class=AdminUserSeeder     # admin@templategen.com / admin123
php artisan db:seed --class=LlmModelSeeder      # 2 model types
php artisan db:seed --class=AdminSettingSeeder   # platform settings
```

## 🔄 Credit Flow

```
1. User registers
   → Auto +100 credits

2. Start generation (POST /generation/generate)
   → Validate blueprint
   → Calculate credits with margins
   → Check sufficient credits
   → Charge credits upfront
   → Create Generation + first PageGeneration

3. Per-page generation loop
   → Build MCP prompt (with context from last 2 pages)
   → Call LLM API via OpenAICompatibleService
   → Record cost in generation_costs
   → Record history in page_generations
   → On error: retry up to 3x with backoff

4. Generation complete
   → Update Generation status
   → Send database notification

5. Generation failure (after 3 retries)
   → Refund credits via CreditService
   → Record in generation_failures
   → Record refund in credit_transactions
```

## 🚨 Common Issues

### "Insufficient Credits"
```php
// Check user credits vs model base_credits
$model = LlmModel::where('model_type', $type)->active()->first();
if ($user->credits < $model->base_credits) {
    // Show error or redirect to topup
}
```

### API Timeout (524)
```
// Handled automatically by retry mechanism
// 3 retries with exponential backoff: 1s, 2s, 4s
// Credits only refunded after all retries fail
```

### SSE Stream Connection
```javascript
// Frontend: use EventSource for real-time progress
const es = new EventSource(`/generation/${id}/stream`);
es.onmessage = (e) => { /* update progress UI */ };
```

## ✅ Feature Status

| Feature | Status |
|---------|--------|
| 2-model system (satset/expert) | ✅ Done |
| Per-page generation | ✅ Done |
| SSE streaming | ✅ Done |
| Refinement chat | ✅ Done |
| Credit system with margins | ✅ Done |
| Credit learning (5+ samples) | ✅ Done |
| Auto-refund on failure | ✅ Done |
| Cost tracking (USD + IDR) | ✅ Done |
| Retry mechanism (3x + backoff) | ✅ Done |
| Background queue generation | ✅ Done |
| Admin model management | ✅ Done |
| Payment/topup flow | ❌ Not implemented |
| Rate limiting for generation | ❌ Not implemented |

## 📚 Full Documentation

- [docs/llm-credit-system.md](./llm-credit-system.md) — Complete credit system documentation
- [docs/credit-refund-and-cost-tracking.md](./credit-refund-and-cost-tracking.md) — Refund & cost tracking details
