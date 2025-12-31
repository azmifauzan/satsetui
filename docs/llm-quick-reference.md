# Quick Reference - Sistem LLM

Panduan singkat untuk developer yang bekerja dengan sistem LLM dan kredit.

## 🎯 Model LLM

| Model | Kredit | Tier |
|-------|--------|------|
| `gemini-2.5-flash` | 3 | FREE ✅ |
| `gpt-5.1-codex-mini` | 2 | Premium |
| `claude-haiku-4-5` | 6 | Premium |
| `gpt-5.1-codex` | 10 | Premium |
| `gemini-3-pro-preview` | 12 | Premium |
| `claude-sonnet-4-5` | 15 | Premium |

## 💰 Kredit System

```php
// Default credits saat registrasi
25 kredit (gratis)

// Nilai kredit
1 kredit = Rp 1,000

// Pembulatan
SELALU dibulatkan ke atas (CEIL)
```

## 🔧 Usage Examples

### Get Available Models

```php
// Untuk user yang sedang login
$models = auth()->user()->getAvailableModels();

// Atau via service
$llmService = app(\App\Services\OpenAICompatibleService::class);
$models = $llmService->getAvailableModels($user->hasPremiumAccess());
```

### Start Generation dengan Model

```php
use App\Services\GenerationService;

$service = app(GenerationService::class);

$result = $service->startGeneration(
    blueprint: $blueprint,
    user: $user,
    modelName: 'claude-haiku-4-5', // Optional, auto-select jika null
    projectName: 'Project Name'
);

// Response
[
    'success' => true,
    'generation_id' => 123,
    'model' => 'claude-haiku-4-5',
    'credits_charged' => 6
]
```

### Check Credits

```php
$user = auth()->user();

// Apakah user punya akses premium?
$isPremium = $user->hasPremiumAccess(); // credits > 0

// Cek cukup kredit untuk model tertentu
$model = \App\Models\LlmModel::where('name', 'claude-sonnet-4-5')->first();

if ($user->credits < $model->estimated_credits_per_generation) {
    return response()->json([
        'error' => 'Insufficient credits',
        'required' => $model->estimated_credits_per_generation,
        'available' => $user->credits
    ], 402);
}
```

### Calculate Actual Credits

```php
use App\Services\OpenAICompatibleService;

$llmService = app(OpenAICompatibleService::class);

// Setelah generation selesai, calculate actual cost
$actualCredits = $llmService->calculateActualCredits(
    modelName: 'claude-haiku-4-5',
    inputTokens: 12500,
    outputTokens: 52300
);

// Bandingkan dengan estimasi
$difference = $actualCredits - $generation->credits_used;
```

## 📊 Database Queries

### Get All Active Models

```php
use App\Models\LlmModel;

$models = LlmModel::active()->ordered()->get();
```

### Get Free Models Only

```php
$freeModels = LlmModel::active()->free()->get();
```

### Get Premium Models Only

```php
$premiumModels = LlmModel::active()->premium()->get();
```

### Get Model by Name

```php
$model = LlmModel::where('name', 'claude-haiku-4-5')->first();
```

## 🔐 API Configuration

```env
# .env file
LLM_API_KEY=your-api-key-here
LLM_BASE_URL=https://ai.sumopod.com/v1
```

## 🧪 Testing

```bash
# Run semua tests
php artisan test

# Run OpenAICompatibleService tests only
php artisan test --filter=OpenAICompatibleServiceTest

# Run dengan coverage
php artisan test --coverage
```

## 🗄️ Seeders

```bash
# Seed LLM models
php artisan db:seed --class=LlmModelSeeder

# Seed semua (includes LlmModelSeeder)
php artisan db:seed
```

## 🚨 Common Issues

### "Insufficient Credits"

```php
// Solution: Check user credits first
if (!$model->is_free && $user->credits < $model->estimated_credits_per_generation) {
    // Show error or redirect to purchase page
}
```

### "Model not found or inactive"

```php
// Solution: Validate model exists and is active
$model = LlmModel::where('name', $modelName)
    ->active()
    ->first();

if (!$model) {
    throw new \Exception('Model not available');
}
```

### API Error 401

```php
// Check API key in config
config('services.llm.api_key'); // Should return the key

// If null, check .env file
```

## 📝 Important Constants

```php
// In OpenAICompatibleService
USD_TO_IDR = 18000
MARGIN = 0.05 (5%)
CREDIT_VALUE = 1000 (Rp)

// In LlmModelSeeder
ESTIMATED_INPUT_TOKENS = 10000
ESTIMATED_OUTPUT_TOKENS = 50000
```

## 🔄 Credit Flow

```
1. User registers
   → Auto +25 credits

2. Start generation
   → Check sufficient credits
   → Deduct estimated credits upfront
   → Start LLM generation

3. Generation success
   → Credits already deducted
   → Save result

4. Generation failure
   → Refund credits to user
   → Log error
```

## 📚 Documentation

Dokumentasi lengkap: [docs/llm-credit-system.md](./llm-credit-system.md)

## 🎯 Next Steps (TODO)

- [ ] Frontend: Add model selector in wizard
- [ ] Controller: Add `/api/models` endpoint
- [ ] Admin Panel: Model management UI
- [ ] Payment: Credit purchase system
- [ ] Analytics: Track model usage & costs
- [ ] Notification: Low credit warnings

---

**Last Updated:** 30 Desember 2025
