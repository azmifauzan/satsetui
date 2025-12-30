# Sistem LLM dan Kredit

Dokumentasi lengkap untuk sistem Large Language Model (LLM) dan perhitungan kredit di Template Generator.

**Tanggal Update:** 30 Desember 2025  
**Versi:** 1.0

---

## 📋 Daftar Isi

1. [Ringkasan Sistem](#ringkasan-sistem)
2. [Model LLM yang Tersedia](#model-llm-yang-tersedia)
3. [Perhitungan Kredit](#perhitungan-kredit)
4. [Struktur Database](#struktur-database)
5. [Service Architecture](#service-architecture)
6. [API Integration](#api-integration)
7. [User Credits & Premium](#user-credits--premium)
8. [Implementasi Teknis](#implementasi-teknis)

---

## Ringkasan Sistem

Sistem LLM menggunakan OpenAI-compatible API untuk mendukung multiple model providers dalam satu interface yang konsisten. Setiap model memiliki pricing yang berbeda berdasarkan token input/output, dan users dikenakan biaya dalam bentuk kredit.

### Fitur Utama

- ✅ **6 Model LLM** - Dari model gratis hingga premium
- ✅ **Dynamic Pricing** - Harga dihitung berdasarkan token usage aktual
- ✅ **Credit System** - 1 kredit = Rp 1,000
- ✅ **Margin System** - 5% markup untuk biaya operasional
- ✅ **Free Tier** - Gemini 2.5 Flash tersedia gratis
- ✅ **25 Kredit Awal** - Diberikan saat registrasi

---

## Model LLM yang Tersedia

### Tabel Model

| No | Model Name | Display Name | Input Price | Output Price | Kredit/Gen | Tier |
|----|------------|--------------|-------------|--------------|------------|------|
| 1 | `gemini-2.5-flash` | Gemini 2.5 Flash | $0.30/1M | $2.50/1M | **3** | FREE ✅ |
| 2 | `gpt-5.1-codex-mini` | GPT-5.1 Codex Mini | $0.25/1M | $2.00/1M | **2** | Premium |
| 3 | `claude-haiku-4-5` | Claude Haiku 4.5 | $1.00/1M | $5.00/1M | **6** | Premium |
| 4 | `gpt-5.1-codex` | GPT-5.1 Codex | $1.25/1M | $10.00/1M | **10** | Premium |
| 5 | `gemini-3-pro-preview` | Gemini 3 Pro Preview | $2.00/1M | $12.00/1M | **12** | Premium |
| 6 | `claude-sonnet-4-5` | Claude Sonnet 4.5 | $3.00/1M | $15.00/1M | **15** | Premium |

### Deskripsi Model

#### 1. Gemini 2.5 Flash (FREE)
- **Use Case:** Template sederhana, prototyping cepat
- **Speed:** Sangat cepat
- **Quality:** Good
- **Cocok untuk:** User baru, testing, template basic

#### 2. GPT-5.1 Codex Mini
- **Use Case:** Generasi kode ringan dengan kualitas baik
- **Speed:** Cepat
- **Quality:** Very Good
- **Cocok untuk:** Template standar, landing pages

#### 3. Claude Haiku 4.5
- **Use Case:** Balance antara speed dan quality
- **Speed:** Cepat
- **Quality:** Excellent
- **Cocok untuk:** Dashboard sederhana, admin panels

#### 4. GPT-5.1 Codex
- **Use Case:** Generasi kode berkualitas tinggi
- **Speed:** Moderate
- **Quality:** Excellent
- **Cocok untuk:** Complex templates, multi-page apps

#### 5. Gemini 3 Pro Preview
- **Use Case:** Model premium Google dengan fitur advanced
- **Speed:** Moderate
- **Quality:** Outstanding
- **Cocok untuk:** Enterprise templates, complex business logic

#### 6. Claude Sonnet 4.5
- **Use Case:** Model terbaik untuk output premium
- **Speed:** Moderate to Slow
- **Quality:** Outstanding
- **Cocok untuk:** Production-ready apps, critical projects

---

## Perhitungan Kredit

### Formula Dasar

```
Kredit = CEIL(
    ((input_tokens / 1,000,000) × input_price_per_million) +
    ((output_tokens / 1,000,000) × output_price_per_million)
) × USD_TO_IDR × (1 + MARGIN) / CREDIT_VALUE
)
```

### Konstanta

| Variabel | Nilai | Keterangan |
|----------|-------|------------|
| `USD_TO_IDR` | 18,000 | Kurs USD ke Rupiah |
| `MARGIN` | 0.05 (5%) | Markup untuk sistem |
| `CREDIT_VALUE` | 1,000 | 1 kredit = Rp 1,000 |
| `ESTIMATED_INPUT` | 10,000 | Rata-rata token input |
| `ESTIMATED_OUTPUT` | 50,000 | Rata-rata token output |

### Contoh Perhitungan Detail

#### Example 1: Gemini 2.5 Flash (FREE)

```
Input Price:  $0.30 per 1M tokens
Output Price: $2.50 per 1M tokens

Kalkulasi:
- Input Cost  = (10,000 / 1,000,000) × $0.30 = $0.003
- Output Cost = (50,000 / 1,000,000) × $2.50 = $0.125
- Total USD   = $0.003 + $0.125 = $0.128

Konversi:
- Total IDR before margin = $0.128 × 18,000 = Rp 2,304
- Total IDR with margin   = Rp 2,304 × 1.05 = Rp 2,419.20
- Credits = CEIL(Rp 2,419.20 / 1,000) = 3 kredit
```

#### Example 2: Claude Sonnet 4.5 (Premium)

```
Input Price:  $3.00 per 1M tokens
Output Price: $15.00 per 1M tokens

Kalkulasi:
- Input Cost  = (10,000 / 1,000,000) × $3.00 = $0.03
- Output Cost = (50,000 / 1,000,000) × $15.00 = $0.75
- Total USD   = $0.03 + $0.75 = $0.78

Konversi:
- Total IDR before margin = $0.78 × 18,000 = Rp 14,040
- Total IDR with margin   = Rp 14,040 × 1.05 = Rp 14,742
- Credits = CEIL(Rp 14,742 / 1,000) = 15 kredit
```

### Pembulatan (Rounding)

⚠️ **PENTING:** Semua perhitungan kredit **SELALU DIBULATKAN KE ATAS** menggunakan fungsi `CEIL()`.

**Alasan:**
1. Mencegah kerugian pada transaksi kecil
2. Mempermudah estimasi user
3. Standar industri untuk micro-transactions
4. Margin keamanan untuk fluktuasi kurs

**Contoh:**
- Rp 2,419.20 → **3** kredit (bukan 2)
- Rp 1,940.00 → **2** kredit (bukan 1)
- Rp 5,670.50 → **6** kredit (bukan 5)

---

## Struktur Database

### Tabel: `llm_models`

Menyimpan informasi semua model LLM yang tersedia.

```sql
CREATE TABLE llm_models (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) UNIQUE NOT NULL,
    display_name VARCHAR(255) NOT NULL,
    description TEXT,
    input_price_per_million DECIMAL(10,7) NOT NULL,
    output_price_per_million DECIMAL(10,7) NOT NULL,
    estimated_credits_per_generation INT NOT NULL,
    is_free BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Kolom Penting:**

- `name` - Identifier unik untuk API (e.g., "claude-haiku-4-5")
- `display_name` - Nama yang ditampilkan ke user
- `input_price_per_million` - Harga USD per 1 juta token input
- `output_price_per_million` - Harga USD per 1 juta token output
- `estimated_credits_per_generation` - Pre-calculated credits (rounded up)
- `is_free` - TRUE untuk model gratis (free tier)
- `is_active` - FALSE untuk disable model temporarily
- `sort_order` - Urutan tampilan di UI

### Tabel: `users` (Updated Fields)

```sql
ALTER TABLE users ADD COLUMN credits INT DEFAULT 25;
ALTER TABLE users ADD COLUMN preferred_model VARCHAR(255) NULL;
```

**Default Credits:** 25 kredit diberikan saat registrasi baru.

### Tabel: `generations` (Updated Fields)

```sql
ALTER TABLE generations MODIFY COLUMN model_used VARCHAR(255);
ALTER TABLE generations ADD COLUMN credits_used INT DEFAULT 0;
```

---

## Service Architecture

### 1. OpenAICompatibleService

**Location:** `app/Services/OpenAICompatibleService.php`

**Responsibilities:**
- Komunikasi dengan LLM API
- Generate template code
- Calculate actual credits based on token usage
- Get available models for users

**Key Methods:**

```php
// Generate template
public function generateTemplate(string $prompt, string $modelName): array

// Get models for user (filtered by premium status)
public function getAvailableModels(bool $isPremium): array

// Get model details
public function getModel(string $modelName): ?LlmModel

// Calculate actual credits after generation
public function calculateActualCredits(
    string $modelName, 
    int $inputTokens, 
    int $outputTokens
): float
```

### 2. GenerationService

**Location:** `app/Services/GenerationService.php`

**Changes:**
- Constructor now uses `OpenAICompatibleService` instead of `GeminiService`
- `startGeneration()` accepts optional `$modelName` parameter
- Credits deducted upfront based on estimated amount
- Model validation before generation starts

**Updated Signature:**

```php
public function startGeneration(
    array $blueprint, 
    User $user, 
    ?string $modelName = null,  // NEW: optional model selection
    ?string $projectName = null
): array
```

**Auto-Selection Logic:**

```php
if (!$modelName) {
    $isPremium = $user->credits > 0;
    $modelName = $isPremium ? 'claude-haiku-4-5' : 'gemini-2.5-flash';
}
```

### 3. LlmModel (New Model)

**Location:** `app/Models/LlmModel.php`

**Scopes:**
- `active()` - Only active models
- `ordered()` - Sorted by sort_order
- `free()` - Only free tier models
- `premium()` - Only premium models

---

## API Integration

### Endpoint Configuration

**Base URL:** `https://ai.sumopod.com/v1`  
**API Key:** `sk-Cx00n-G2-g8__tXS44WljA`  
**Format:** OpenAI-compatible API

### Configuration File

**Location:** `config/services.php`

```php
'llm' => [
    'api_key' => env('LLM_API_KEY', 'sk-Cx00n-G2-g8__tXS44WljA'),
    'base_url' => env('LLM_BASE_URL', 'https://ai.sumopod.com/v1'),
],
```

### Environment Variables

Add to `.env`:

```env
LLM_API_KEY=sk-Cx00n-G2-g8__tXS44WljA
LLM_BASE_URL=https://ai.sumopod.com/v1
```

### Request Format

```bash
curl https://ai.sumopod.com/v1/chat/completions \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer sk-Cx00n-G2-g8__tXS44WljA" \
  -d '{
    "model": "claude-haiku-4-5",
    "messages": [
      {
        "role": "user",
        "content": "Your MCP prompt here..."
      }
    ],
    "max_tokens": 60000,
    "temperature": 0.7
  }'
```

### Response Format

```json
{
  "choices": [
    {
      "message": {
        "content": "Generated template code..."
      }
    }
  ],
  "usage": {
    "prompt_tokens": 10234,
    "completion_tokens": 45678,
    "total_tokens": 55912
  }
}
```

---

## User Credits & Premium

### Free Tier

**Kondisi:**
- Default saat registrasi
- Mendapat 25 kredit awal (gratis)
- Hanya bisa menggunakan model `gemini-2.5-flash`

**Batasan:**
- 1 model tersedia
- Tidak bisa pilih model lain
- Tidak ada refund jika gagal

### Premium Tier

**Kondisi:**
- User dengan `credits > 0`
- Akses ke semua 6 model
- Bisa pilih model sesuai kebutuhan

**Benefits:**
- Model selection flexibility
- Better quality output
- Priority support (future)
- Refund on generation failure

### Credit Flow

```
1. USER REGISTRATION
   ↓
   + 25 kredit otomatis

2. START GENERATION
   ↓
   - Deduct estimated credits upfront
   - Lock credits untuk transaksi ini
   
3. GENERATION SUCCESS
   ↓
   - Credits sudah terpakai
   - Save actual token usage
   - (Optional) Calculate actual cost for reporting
   
4. GENERATION FAILURE
   ↓
   - Refund estimated credits
   - User tidak dikenakan biaya
```

### Purchase Credits (Future Implementation)

```
Pricing Tiers (Recommendation):
- 100 kredit  = Rp 100,000 (Rp 1,000/kredit)
- 500 kredit  = Rp 450,000 (Rp 900/kredit) - 10% bonus
- 1000 kredit = Rp 850,000 (Rp 850/kredit) - 15% bonus
- 5000 kredit = Rp 4,000,000 (Rp 800/kredit) - 20% bonus
```

---

## Implementasi Teknis

### File Structure

```
app/
├── Models/
│   ├── LlmModel.php              ← NEW: Model untuk LLM data
│   └── User.php                  ← UPDATED: Added methods
├── Services/
│   ├── OpenAICompatibleService.php  ← NEW: LLM communication
│   ├── GenerationService.php        ← UPDATED: Uses new service
│   ├── McpPromptBuilder.php         ← Unchanged
│   └── GeminiService.php            ← DEPRECATED
│
database/
├── migrations/
│   ├── 0001_01_01_000000_create_users_table.php      ← UPDATED: credits default 25
│   └── 2025_12_30_101141_create_llm_models_table.php ← NEW
└── seeders/
    ├── LlmModelSeeder.php    ← NEW: Seed model data
    ├── UserSeeder.php        ← UPDATED: 25 credits
    └── DatabaseSeeder.php    ← UPDATED: Include LlmModelSeeder

config/
└── services.php              ← UPDATED: Added 'llm' config

tests/
└── Unit/Services/
    └── OpenAICompatibleServiceTest.php  ← NEW: 7 test cases
```

### Running Migrations & Seeds

```bash
# Run migrations
php artisan migrate

# Seed LLM models
php artisan db:seed --class=LlmModelSeeder

# Or seed everything
php artisan db:seed
```

### Usage Examples

#### Get Available Models for User

```php
// In controller
$user = auth()->user();
$models = $user->getAvailableModels();

// Returns array of models user can use
// Free user: only gemini-2.5-flash
// Premium user: all 6 models
```

#### Start Generation with Model Selection

```php
use App\Services\GenerationService;

$generationService = app(GenerationService::class);

$result = $generationService->startGeneration(
    blueprint: $request->validated(),
    user: auth()->user(),
    modelName: 'claude-haiku-4-5', // Optional
    projectName: 'My New Project'
);

if ($result['success']) {
    return response()->json([
        'generation_id' => $result['generation_id'],
        'model' => $result['model'],
        'credits_charged' => $result['credits_charged'],
    ]);
}
```

#### Check Credits Before Generation

```php
$user = auth()->user();
$model = LlmModel::where('name', 'claude-sonnet-4-5')->first();

if (!$model->is_free && $user->credits < $model->estimated_credits_per_generation) {
    return response()->json([
        'error' => 'Insufficient credits',
        'required' => $model->estimated_credits_per_generation,
        'available' => $user->credits,
    ], 402); // Payment Required
}
```

#### Calculate Actual Cost After Generation

```php
use App\Services\OpenAICompatibleService;

$llmService = app(OpenAICompatibleService::class);

// After generation completes
$actualCredits = $llmService->calculateActualCredits(
    modelName: 'claude-haiku-4-5',
    inputTokens: 12450,
    outputTokens: 48920
);

// Log for analytics
Log::info('Generation cost analysis', [
    'estimated' => $generation->credits_used,
    'actual' => $actualCredits,
    'difference' => $actualCredits - $generation->credits_used,
]);
```

### Testing

Run all tests:
```bash
php artisan test
```

Run specific service tests:
```bash
php artisan test --filter=OpenAICompatibleServiceTest
```

**Test Coverage:**
- ✅ Generate template with valid response
- ✅ Handle API errors gracefully
- ✅ Filter models for free users
- ✅ Return all models for premium users
- ✅ Get model by name
- ✅ Calculate actual credits accurately
- ✅ Verify ceiling function for rounding

---

## Admin Configuration (Future)

### Settings to Add in Admin Panel

1. **USD to IDR Exchange Rate**
   - Current: 18,000
   - Allow admin to update
   - Show last update timestamp

2. **System Margin**
   - Current: 5%
   - Configurable range: 3% - 15%
   - Apply globally to all models

3. **Model Management**
   - Enable/disable models
   - Update pricing from API provider
   - Change sort order
   - Mark as featured

4. **Credit Pricing**
   - Base credit value (currently Rp 1,000)
   - Purchase tier pricing
   - Promotional pricing

---

## Migration Notes

### From Old System (Gemini Only)

**Breaking Changes:**
1. `GeminiService` replaced with `OpenAICompatibleService`
2. `model_used` field now stores model name (string) not tier
3. Credits calculated per generation, not per page
4. Default credits reduced from 100 to 25

**Migration Path:**
1. Existing users keep their current credits
2. Old `model_used` values ('gemini-pro', 'gemini-flash') remain valid
3. New generations use new model names
4. No data loss, backward compatible

---

## Troubleshooting

### Issue: "Insufficient Credits"

**Cause:** User doesn't have enough credits for selected model  
**Solution:** 
- Downgrade to cheaper model
- Purchase more credits
- Use free tier model

### Issue: "Model not found or inactive"

**Cause:** Model name invalid or disabled by admin  
**Solution:**
- Check available models: `$user->getAvailableModels()`
- Use auto-selection (pass `null` for modelName)

### Issue: API Error 401 Unauthorized

**Cause:** Invalid or expired API key  
**Solution:**
- Check `LLM_API_KEY` in `.env`
- Verify key with provider
- Regenerate if needed

### Issue: Credits Not Refunded on Failure

**Cause:** Exception thrown before refund logic  
**Solution:**
- Check logs: `storage/logs/laravel.log`
- Manual refund if needed
- Fix bug in GenerationService

---

## Changelog

### Version 1.0 (30 December 2025)

**Added:**
- 6 LLM models with dynamic pricing
- Credit calculation with 5% margin
- OpenAI-compatible API integration
- Free tier with Gemini 2.5 Flash
- Premium tier with model selection
- Automatic credit rounding (ceiling)
- 25 initial credits for new users
- LlmModel database table and seeder
- Comprehensive unit tests

**Changed:**
- Replaced GeminiService with OpenAICompatibleService
- Updated GenerationService for new credit system
- Default user credits: 100 → 25
- Credit calculation: per-page → per-generation

**Deprecated:**
- GeminiService (kept for backward compatibility)
- Old model tier system ('free'/'premium')

**Security:**
- API key stored in config with env fallback
- Input validation for model selection
- Rate limiting (to be implemented)

---

## Kontak & Support

**Technical Issues:**  
- GitHub Issues: (project repository)
- Email: (technical support email)

**Business/Pricing:**  
- Email: (sales/business email)

**Documentation Updates:**  
Silakan submit PR untuk perbaikan atau update dokumentasi ini.

---

**Last Updated:** 30 Desember 2025  
**Next Review:** 30 Januari 2026
