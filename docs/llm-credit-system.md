# Sistem LLM dan Kredit - SatsetUI

Dokumentasi lengkap untuk sistem Large Language Model (LLM) dan perhitungan kredit di SatsetUI.

**Tanggal Update:** 25 Februari 2026  
**Versi:** 2.3 (Updated untuk 2-model system)

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

SatsetUI menggunakan OpenAI-compatible API sebagai gateway utama untuk mendukung multiple LLM providers dalam satu interface yang konsisten. Sistem menggunakan **2 tipe model** yang admin-configurable: Satset (cepat) dan Expert (premium).

### Fitur Utama

- ✅ **2 Tipe Model LLM** - Satset (cepat, 6 kredit) dan Expert (premium, 15 kredit)
- ✅ **Admin-Configurable Models** - Provider, model name, API key, base URL (encrypted)
- ✅ **Per-Page Generation** - Setiap halaman di-generate secara terpisah
- ✅ **SSE Streaming** - Real-time progress via Server-Sent Events
- ✅ **Refinement Chat** - Edit hasil via conversational refinement
- ✅ **History Recording** - Semua prompt dan response dicatat
- ✅ **Credit Learning** - Estimasi kredit semakin akurat dari data historis (min 5 samples)
- ✅ **Dynamic Pricing** - Harga dihitung berdasarkan token usage aktual
- ✅ **Error Margin** - Default 10%, configurable di admin
- ✅ **Profit Margin** - Default 5%, configurable di admin
- ✅ **Auto-Refund** - Kredit dikembalikan otomatis jika generasi gagal setelah 3x retry
- ✅ **Cost Tracking** - Biaya aktual LLM (USD + IDR) per halaman
- ✅ **100 Kredit Awal** - Diberikan saat registrasi

---

## Model LLM yang Tersedia

### 2-Model System

SatsetUI menggunakan sistem 2 tipe model yang disederhanakan:

| Tipe | Default Model | Default Provider | Base Credits | Deskripsi |
|------|---------------|------------------|-------------|-----------|
| **Satset** | `gemini-2.0-flash-exp` | Gemini | **6** | Cepat, cocok untuk prototyping |
| **Expert** | `gemini-2.5-pro-preview` | Gemini | **15** | Kualitas premium |

> **Note:** Admin dapat mengubah model name, provider (gemini/openai), API key, dan base URL melalui Admin Panel > LLM Models. API keys dan base URLs disimpan terenkripsi di database.

### Deskripsi Model

#### 1. Satset (Default: Gemini 2.0 Flash Exp)
- **Use Case:** Template standar, prototyping cepat, iterasi desain
- **Speed:** Sangat cepat
- **Quality:** Good — cukup untuk kebanyakan template
- **Credits:** 6 per generasi
- **Cocok untuk:** Landing pages, admin dashboards sederhana, blog templates

#### 2. Expert (Default: Gemini 2.5 Pro Preview)
- **Use Case:** Template kompleks, kualitas production-ready
- **Speed:** Moderate
- **Quality:** Outstanding — output premium dengan detail tinggi
- **Credits:** 15 per generasi
- **Cocok untuk:** E-commerce, SaaS apps, complex dashboards, enterprise templates

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

#### Skenario 1: Template Standar dengan Satset
```
Model: Satset (6 kredit)
Halaman: 4 halaman (predefined, di bawah kuota 5)

Kalkulasi:
- Model Cost: 6 kredit
- Extra Pages: MAX(0, 4-5) × 1 = 0 kredit
- Subtotal: 6 + 0 = 6 kredit

Dengan Margin:
- After Error Margin (10%): 6 × 1.10 = 6.6 kredit
- After Profit Margin (5%): 6.6 × 1.05 = 6.93 kredit
- Final (rounded up): 7 kredit
```

#### Skenario 2: Template Kompleks dengan Expert
```
Model: Expert (15 kredit)
Halaman: 6 predefined + 3 custom = 9 halaman total

Kalkulasi:
- Model Cost: 15 kredit
- Extra Pages: MAX(0, 9-5) × 1 = 4 kredit
- Subtotal: 15 + 4 = 19 kredit

Dengan Margin:
- After Error Margin (10%): 19 × 1.10 = 20.9 kredit
- After Profit Margin (5%): 20.9 × 1.05 = 21.945 kredit
- Final (rounded up): 22 kredit
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
    model_type ENUM('satset', 'expert') NOT NULL UNIQUE,
    provider ENUM('gemini', 'openai') NOT NULL DEFAULT 'gemini',
    model_name VARCHAR(255) NOT NULL,
    api_key TEXT NULL,           -- encrypted
    base_url TEXT NULL,          -- encrypted
    base_credits INT NOT NULL DEFAULT 1,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_model_type_active (model_type, is_active)
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
    file_path VARCHAR(255) NULL,
    file_type VARCHAR(50) DEFAULT 'html',
    raw_prompt TEXT NULL,
    raw_response TEXT NULL,
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

### v2.3 (25 Februari 2026)
- ✅ Live Preview workspace (WorkspaceService, PreviewController)
- ✅ Multi-file generation output (GenerationFile model)
- ✅ ScaffoldGeneratorService for framework project scaffolding
- ✅ PreviewSession model for preview lifecycle tracking
- ✅ Updated documentation to match current codebase state

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
