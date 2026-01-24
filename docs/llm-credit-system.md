# Sistem LLM dan Kredit - SatsetUI

Dokumentasi lengkap untuk sistem Large Language Model (LLM) dan perhitungan kredit di SatsetUI.

**Tanggal Update:** 25 Januari 2026  
**Versi:** 2.1 (Updated untuk SatsetUI)

---

## 📋 Daftar Isi

1. [Ringkasan Sistem](#ringkasan-sistem)
2. [Model LLM yang Tersedia](#model-llm-yang-tersedia)
3. [Perhitungan Kredit dengan Margin](#perhitungan-kredit-dengan-margin)
4. [Per-Page Generation](#per-page-generation)
5. [History Recording & Credit Learning](#history-recording--credit-learning)
6. [Struktur Database](#struktur-database)
7. [Service Architecture](#service-architecture)
8. [API Integration](#api-integration)
9. [Admin Configuration](#admin-configuration)

---

## Ringkasan Sistem

SatsetUI menggunakan OpenAI-compatible API untuk mendukung multiple model providers dalam satu interface yang konsisten. Setiap model memiliki pricing yang berbeda berdasarkan token input/output, dan users dikenakan biaya dalam bentuk kredit.

### Fitur Utama

- ✅ **6 Model LLM** - Dari model gratis hingga premium
- ✅ **Per-Page Generation** - Setiap halaman di-generate secara terpisah
- ✅ **History Recording** - Semua prompt dan response dicatat
- ✅ **Credit Learning** - Estimasi kredit semakin akurat dari data historis
- ✅ **Dynamic Pricing** - Harga dihitung berdasarkan token usage aktual
- ✅ **Error Margin** - Default 10%, configurable di admin
- ✅ **Profit Margin** - Default 5%, configurable di admin
- ✅ **Credit System** - 1 kredit = Rp 1,000
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

## Perhitungan Kredit dengan Margin

### Formula

```
subtotal = modelCredits + extraPageCredits + extraComponentCredits
withErrorMargin = subtotal × (1 + errorMarginPercent)
totalCredits = CEIL(withErrorMargin × (1 + profitMarginPercent))
```

### Kuota Dasar

| Item | Kuota Dasar | Biaya Extra per Item |
|------|-------------|----------------------|
| Halaman | 5 halaman | +1 kredit per halaman |
| Komponen | 6 komponen | +0.5 kredit per komponen |

### Margin System

| Margin Type | Default | Range | Configurable |
|-------------|---------|-------|--------------|
| Error Margin | 10% | 0-50% | Ya (Admin) |
| Profit Margin | 5% | 0-50% | Ya (Admin) |

**Error Margin** - Meng-cover variasi token usage yang tidak dapat diprediksi dengan akurat.
**Profit Margin** - Untuk biaya operasional dan keuntungan platform.

### Contoh Perhitungan Lengkap

#### Skenario 1: Template Standar dengan Margin
```
Model: Gemini 2.5 Flash (3 kredit)
Halaman: 4 halaman (predefined)
Komponen: 5 komponen (predefined)

Kalkulasi:
- Model Cost: 3 kredit
- Extra Pages: MAX(0, 4-5) × 1 = 0 kredit
- Extra Components: MAX(0, 5-6) × 0.5 = 0 kredit
- Subtotal: 3 + 0 + 0 = 3 kredit

Dengan Margin:
- After Error Margin (10%): 3 × 1.10 = 3.3 kredit
- After Profit Margin (5%): 3.3 × 1.05 = 3.465 kredit
- Final (rounded up): 4 kredit
```

#### Skenario 2: Template Kompleks dengan Custom
```
Model: Claude Sonnet 4.5 (15 kredit)
Halaman: 6 predefined + 3 custom = 9 halaman total
Komponen: 6 predefined + 4 custom = 10 komponen total

Kalkulasi:
- Model Cost: 15 kredit
- Extra Pages: MAX(0, 9-5) × 1 = 4 kredit
- Extra Components: MAX(0, 10-6) × 0.5 = 2 kredit
- Subtotal: 15 + 4 + 2 = 21 kredit

Dengan Margin:
- After Error Margin (10%): 21 × 1.10 = 23.1 kredit
- After Profit Margin (5%): 23.1 × 1.05 = 24.255 kredit
- Final (rounded up): 25 kredit
```

### Credit Breakdown Display (Step 3)

Di Step 3 (LLM Model Selection), user dapat melihat breakdown kredit:

```
┌─────────────────────────────────────────────────┐
│  💎 Siap untuk Generate!                         │
├─────────────────────────────────────────────────┤
│  Biaya Model:              💎 15 kredit          │
│  Halaman Extra: (9 total, 4 extra)    +4 kredit  │
│  Komponen Extra: (10 total, 4 extra)  +2 kredit  │
├─────────────────────────────────────────────────┤
│  Subtotal:                 💎 21 kredit          │
│  Error Margin (10%):       +2.1 kredit          │
│  Profit Margin (5%):       +1.155 kredit        │
├─────────────────────────────────────────────────┤
│  Total Biaya:              💎 25 kredit          │
│  (rounded up from 24.255)                        │
└─────────────────────────────────────────────────┘
```

---

## Per-Page Generation

### Konsep

SatsetUI generate **per halaman** untuk hasil yang lebih baik:

1. **Better LLM Context** - Setiap halaman mendapat fokus penuh
2. **Progress Tracking** - User melihat progress real-time
3. **Error Recovery** - Satu halaman gagal tidak menggagalkan yang lain
4. **Credit Accuracy** - Token usage aktual tercatat per halaman

### Flow Per-Page Generation

```
┌─────────────────────────────────────────────────────┐
│ Generation Started                                   │
│ Total Pages: 5                                       │
└──────────────────────┬──────────────────────────────┘
                       │
         ┌─────────────┴─────────────┐
         │ Page 1: Login             │
         │ - Build MCP Prompt        │
         │ - Call LLM API            │
         │ - Record History          │
         │ - Store Code              │
         └─────────────┬─────────────┘
                       │ ✓ Complete (1/5)
         ┌─────────────┴─────────────┐
         │ Page 2: Dashboard         │
         │ - Build MCP Prompt        │
         │ - Call LLM API            │
         │ - Record History          │
         │ - Store Code              │
         └─────────────┬─────────────┘
                       │ ✓ Complete (2/5)
         ┌─────────────┴─────────────┐
         │ Page 3: Charts            │
         │ - Build MCP Prompt        │
         │ - Call LLM API            │
         │ ✗ Error! Retry...         │
         │ - Call LLM API (retry)    │
         │ - Record History          │
         │ - Store Code              │
         └─────────────┬─────────────┘
                       │ ✓ Complete (3/5)
                      ...
```

### Progress API Response

```json
{
  "generation_id": 123,
  "status": "processing",
  "total_pages": 5,
  "current_page_index": 3,
  "current_page_name": "Charts",
  "pages": [
    {"name": "login", "status": "completed", "processing_time_ms": 2500},
    {"name": "dashboard", "status": "completed", "processing_time_ms": 4200},
    {"name": "charts", "status": "processing", "processing_time_ms": null},
    {"name": "settings", "status": "pending", "processing_time_ms": null},
    {"name": "inventory", "status": "pending", "processing_time_ms": null}
  ]
}
```

---

## History Recording & Credit Learning

### Data yang Dicatat per Halaman

Setiap halaman yang di-generate akan mencatat:

| Field | Description |
|-------|-------------|
| `page_name` | Nama halaman (e.g., "login", "dashboard", "Inventory") |
| `page_type` | "predefined" atau "custom" |
| `mcp_prompt` | Full prompt yang dikirim ke LLM |
| `llm_response` | Full response dari LLM |
| `input_tokens` | Jumlah token input |
| `output_tokens` | Jumlah token output |
| `processing_time_ms` | Waktu proses dalam ms |
| `status` | completed/failed |
| `error_message` | Pesan error jika gagal |

### Credit Learning Algorithm

SatsetUI belajar dari data historis untuk estimasi kredit yang lebih akurat:

```
estimated_tokens = weighted_average(
  last_100_generations_of_same_page_type,
  weights = exponential_decay(newer = higher_weight)
)
```

**Contoh:**
- `login` page historically uses ~500 output tokens
- `dashboard` page historically uses ~2000 output tokens  
- `charts` page historically uses ~1500 output tokens
- `custom` pages use average of all custom pages

---

## Struktur Database

### Tabel: `llm_models`

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

### Tabel: `page_generations`

```sql
CREATE TABLE page_generations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    generation_id BIGINT NOT NULL,
    page_name VARCHAR(100) NOT NULL,
    page_type ENUM('predefined', 'custom') NOT NULL,
    mcp_prompt TEXT NOT NULL,
    llm_response TEXT,
    input_tokens INT DEFAULT 0,
    output_tokens INT DEFAULT 0,
    processing_time_ms INT DEFAULT 0,
    status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    error_message TEXT,
    created_at TIMESTAMP,
    completed_at TIMESTAMP,
    FOREIGN KEY (generation_id) REFERENCES generations(id) ON DELETE CASCADE
);
```

### Tabel: `admin_settings`

```sql
CREATE TABLE admin_settings (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    key VARCHAR(100) UNIQUE NOT NULL,
    value TEXT NOT NULL,
    type ENUM('string', 'integer', 'float', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## Service Architecture

### GenerationService

```php
class GenerationService
{
    public function startGeneration(
        array $blueprint, 
        User $user, 
        ?string $modelName = null,
        ?string $projectName = null
    ): array {
        // Calculate credits with margins
        // Create generation record
        // Start per-page generation loop
    }
    
    public function generatePage(
        Generation $generation, 
        string $pageName,
        bool $isCustom
    ): PageGeneration {
        // Build page-specific MCP prompt
        // Call LLM API
        // Record history
        // Update progress
    }
}
```

### CreditService

```php
class CreditService
{
    public function calculateCharge(
        int $modelCredits,
        int $totalPages,
        int $totalComponents
    ): CreditBreakdown {
        // Apply margins and calculate total
    }
    
    public function deductCredits(User $user, int $amount, string $reason): bool;
    public function refundCredits(User $user, int $amount, string $reason): bool;
}
```

---

## API Integration

### Endpoint Configuration

**Base URL:** `https://ai.sumopod.com/v1`  
**Format:** OpenAI-compatible API

### Environment Variables

```env
LLM_API_KEY=sk-your-api-key
LLM_BASE_URL=https://ai.sumopod.com/v1
```

---

## Admin Configuration

### Available Settings

| Setting Key | Type | Default | Description |
|-------------|------|---------|-------------|
| `error_margin_percent` | float | 10 | Error margin (0-50%) |
| `profit_margin_percent` | float | 5 | Profit margin (0-50%) |

### Admin UI Features

1. **Margin Configuration**
   - Adjust error margin (0-50%)
   - Adjust profit margin (0-50%)

2. **Generation History**
   - View all generations
   - View prompts and responses
   - Filter by user, date, status

3. **Usage Statistics**
   - Total generations
   - Credits consumed
   - Revenue tracking

---

## Changelog

### v2.1 (25 Januari 2026)
- ✅ Rebranding ke SatsetUI
- ✅ Automatic retry mechanism (3x)
- ✅ Previous page context for consistency
- ✅ Credit refund system

### v2.0 (30 Desember 2025)
- ✅ Per-page generation
- ✅ History recording
- ✅ Credit learning
- ✅ Configurable margins

### v1.0 (29 Desember 2025)
- Initial release
