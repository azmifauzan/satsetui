# SatsetUI

Platform wizard-driven untuk menghasilkan template frontend yang konsisten, dapat diprediksi, dan siap produksi. Sistem berbasis konfigurasi terstruktur, bukan prompt bebas.

> **"Sat-set"** - Bahasa slang Indonesia yang berarti cepat dan efisien. SatsetUI membuat pembuatan template UI jadi sat-set!

## 🌟 Fitur Utama

- **Wizard Terstruktur 3 Langkah**: Konfigurasi melalui langkah yang jelas dan terstruktur
  - Step 1: Framework CSS, Kategori Template & Output Format
  - Step 2: Desain Visual & Konten (halaman, layout, tema, komponen)
  - Step 3: Pemilihan Model LLM & Estimasi Kredit
- **Hasil Deterministik**: Pilihan yang sama menghasilkan output yang sama, setiap saat
- **Multi CSS Framework**: Tailwind CSS, Bootstrap, Pure CSS
- **Multi Output Format**: HTML+CSS, React, Vue, Angular, Svelte, Custom
- **Generasi Per Halaman**: Setiap halaman digenerate terpisah dengan konteks halaman sebelumnya
- **SSE Streaming**: Real-time streaming progres generasi
- **Background Queue**: Generasi bisa dilanjutkan di background via queue worker
- **Refinement Chat**: Edit hasil generasi via chat setelah generate selesai
- **Sistem Kredit**: Kredit dengan margin error (10%) & profit (5%), auto-refund saat gagal
- **Credit Learning**: Estimasi kredit makin akurat berdasarkan data historis
- **Cost Tracking**: Pelacakan biaya LLM aktual (USD + IDR) per halaman
- **Retry Otomatis**: 3x retry dengan exponential backoff untuk error timeout
- **ZIP Download**: Download seluruh hasil generasi dalam format ZIP
- **Bilingual**: Bahasa Indonesia (default) & English
- **Dark/Light Mode**: Tema terang dan gelap dengan persistensi localStorage
- **Email Verification**: Verifikasi email wajib sebelum akses fitur
- **Notifikasi Telegram**: Notifikasi admin saat user baru mendaftar
- **Admin Panel Lengkap**: Dashboard, user management, model config, settings, generation history

## 🎯 Kategori Template

1. **Admin Dashboard** - Internal tools, data-heavy, CRUD operations
2. **Company Profile** - Public-facing, company content showcase
3. **Landing Page** - Marketing-focused, conversion-optimized
4. **SaaS Application** - User accounts, full features, pricing
5. **Blog / Content** - Articles, reading experience, categories
6. **E-Commerce** - Product catalogs, shopping cart, checkout
7. **Custom** - Kategori kustom dengan nama dan deskripsi sendiri

## 🚀 Quick Start

### Prerequisites

- PHP 8.2+ (recommended 8.4)
- PostgreSQL atau MySQL
- Node.js 18+
- Composer 2.x

### Installation

1. Clone repository
```bash
git clone https://github.com/yourusername/satsetui.git
cd satsetui
```

2. Install dependencies
```bash
composer install
npm install
```

3. Setup environment
```bash
cp .env.example .env
php artisan key:generate
```

4. Configure database di `.env`
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=satsetui
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

5. Run migrations & seed
```bash
php artisan migrate
php artisan db:seed
```

Seeder akan membuat:
- Admin user (`admin@templategen.com` / `admin123`)
- 2 model LLM (Satset & Expert)
- Admin settings default

6. Start development servers
```bash
# Terminal 1 - Laravel
php artisan serve

# Terminal 2 - Vite
npm run dev

# Terminal 3 - Queue Worker (untuk generasi template)
php artisan queue:work
```

7. Buka aplikasi di browser
```
http://127.0.0.1:8000
```

## 💎 Sistem Kredit

Aplikasi menggunakan sistem kredit untuk generasi template:

- **User Baru**: Mendapat **100 kredit** gratis saat registrasi
- **2 Tipe Model LLM**:
  - **Satset** (default: Gemini 2.0 Flash Exp) — 6 kredit/generasi. Cepat, cocok untuk prototyping
  - **Expert** (default: Gemini 2.5 Pro Preview) — 15 kredit/generasi. Kualitas premium

### Perhitungan Kredit

```
subtotal = base_credits + extra_page_credits
total = CEIL(subtotal × (1 + error_margin) × (1 + profit_margin))
```

- Base pages: 5 halaman termasuk dalam base cost
- Extra page: +1 kredit per halaman tambahan
- Error margin: 10% (admin-configurable)
- Profit margin: 5% (admin-configurable)
- **Auto-refund** jika generasi gagal setelah 3x retry

## 🎨 Cara Menggunakan Generator

### 1. Register & Verifikasi Email
Buat akun baru, verifikasi email, dan login. User baru mendapat 100 kredit gratis.

### 2. Akses Dashboard
Setelah login, dashboard menampilkan:
- Total template yang sudah dibuat
- Kredit tersisa
- Template bulan ini
- Akses cepat ke wizard

### 3. Mulai Wizard (3 Langkah)

#### Step 1: Framework, Kategori & Output Format
- Pilih CSS framework (Tailwind CSS, Bootstrap, atau Pure CSS)
- Pilih kategori template atau buat custom
- Pilih format output (HTML+CSS, React, Vue, Angular, Svelte, atau Custom)

#### Step 2: Desain Visual & Konten
- Pilih halaman yang dibutuhkan (+ custom pages)
- Isi info proyek (nama perusahaan/aplikasi untuk branding konsisten)
- Konfigurasi layout & navigasi (sidebar/top/hybrid)
- Atur tema (warna primer/sekunder, mode dark/light, background style)
- Pilih UI density & border style
- Pilih komponen (forms, buttons, charts, modals, dll)
- Pilih chart library (Chart.js atau Apache ECharts)

#### Step 3: Model LLM
- Pilih model AI (Satset atau Expert)
- Beri nama template
- Lihat estimasi biaya kredit dengan breakdown
- Mulai generasi

### 4. Monitor Progres
- Real-time streaming via SSE (Server-Sent Events)
- Lihat progres generasi per halaman
- Opsi lanjutkan di background jika tidak mau menunggu

### 5. Refinement & Download
- Chat refinement untuk edit hasil generasi
- Download semua file dalam format ZIP
- Lihat riwayat template di halaman Templates

## 🐳 Docker Deployment

### Build Docker Image

```bash
docker build -t satsetui .
```

### Run dengan Docker Compose

```bash
docker-compose up -d
```

Aplikasi akan berjalan di `http://localhost:8000`

## 📁 Struktur Project

```
satsetui/
├── app/
│   ├── Blueprints/              # JSON schema untuk blueprint template
│   │   └── template-blueprint.schema.json
│   ├── Channels/
│   │   └── TelegramChannel.php  # Custom notification channel
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/            # Login, Register, Verify Email
│   │   │   ├── Admin/           # Dashboard, Users, Models, Settings, Generations
│   │   │   ├── DashboardController.php
│   │   │   ├── GenerationController.php  # Generate, stream, refine, progress
│   │   │   ├── TemplateController.php
│   │   │   ├── LlmModelController.php
│   │   │   └── LanguageController.php
│   │   ├── Middleware/
│   │   │   ├── AdminMiddleware.php
│   │   │   └── HandleInertiaRequests.php
│   │   └── Requests/            # Form requests & validasi
│   ├── Jobs/
│   │   └── ProcessTemplateGeneration.php  # Background generation (30min timeout)
│   ├── Models/
│   │   ├── User.php             # credits, is_premium, is_admin, is_active
│   │   ├── Generation.php       # Template generation record
│   │   ├── PageGeneration.php   # Per-page generation record
│   │   ├── LlmModel.php         # 2 types: satset, expert
│   │   ├── AdminSetting.php     # Key-value settings with cache
│   │   ├── CreditTransaction.php # charge, refund, topup, bonus, adjustment
│   │   ├── CreditEstimation.php # Historical credit learning
│   │   ├── GenerationCost.php   # Actual LLM costs (USD + IDR)
│   │   ├── GenerationFailure.php # Failure tracking with stack traces
│   │   ├── CustomPageStatistic.php # Custom page usage tracking
│   │   ├── RefinementMessage.php # Chat refinement messages
│   │   └── Project.php          # User projects
│   ├── Notifications/
│   │   ├── TemplateGenerationCompleted.php  # Database notification
│   │   └── UserRegistered.php               # Telegram notification
│   ├── Policies/
│   │   └── GenerationPolicy.php  # User can only view own generations
│   └── Services/
│       ├── McpPromptBuilder.php       # Blueprint → MCP prompt (per-page)
│       ├── GenerationService.php      # Core orchestrator (retry, context, credits)
│       ├── OpenAICompatibleService.php # Primary LLM gateway (Gemini/OpenAI)
│       ├── GeminiService.php          # Legacy direct Gemini API
│       ├── CreditService.php          # Credit operations & transactions
│       ├── CreditEstimationService.php # Historical learning for estimates
│       ├── CostTrackingService.php    # LLM cost recording & analytics
│       ├── AdminStatisticsService.php # Admin dashboard statistics
│       └── TelegramService.php        # Telegram bot messaging
├── resources/js/
│   ├── pages/
│   │   ├── Home.vue              # Public landing page
│   │   ├── Auth/                 # Login, Register, VerifyEmail
│   │   ├── Dashboard/Index.vue   # User dashboard
│   │   ├── Wizard/Index.vue      # 3-step wizard
│   │   ├── Generation/Show.vue   # SSE streaming, refinement chat, ZIP download
│   │   ├── Templates/Index.vue   # Paginated template list
│   │   └── Admin/                # Dashboard, Users, Models, Settings, Generations
│   ├── wizard/
│   │   ├── wizardState.ts        # Central wizard state management
│   │   ├── types.ts              # TypeScript interfaces
│   │   └── steps/
│   │       ├── Step1FrameworkCategoryOutput.vue
│   │       ├── Step2VisualDesignContent.vue
│   │       └── Step3LlmModel.vue
│   ├── layouts/
│   │   ├── AppLayout.vue         # Authenticated layout with sidebar
│   │   └── AdminLayout.vue       # Admin layout
│   ├── components/
│   │   ├── admin/StatCard.vue
│   │   ├── dashboard/            # Card, StatCard
│   │   └── landing/              # Navbar, Hero, Features, HowItWorks, FAQ, CTA, Footer
│   └── lib/
│       ├── i18n.ts               # Bilingual (ID/EN) translations
│       └── theme.ts              # Dark/Light/System theme
├── routes/web.php                # All application routes
├── database/
│   ├── migrations/               # 17 migration files
│   ├── factories/UserFactory.php
│   └── seeders/                  # Admin, LlmModel, AdminSetting, User seeders
├── docs/                         # Dokumentasi lengkap
└── docker/                       # Docker & Nginx config
```

## 🛠 Teknologi yang Digunakan

### Backend
| Package | Versi |
|---------|-------|
| PHP | 8.4 |
| Laravel | 12.x |
| Inertia.js (Laravel) | 2.x |
| Laravel Wayfinder | 0.x |
| Pest | 4.x |
| Laravel Pint | 1.x |
| Laravel Sail | 1.x |

### Frontend
| Package | Versi |
|---------|-------|
| Vue.js 3 | 3.5.x |
| TypeScript | 5.x |
| Tailwind CSS | 4.x |
| Vite | 7.x |
| @inertiajs/vue3 | 2.x |
| JSZip | 3.x |
| @vueuse/core | 12.x |

### AI/LLM Integration
- **OpenAI-Compatible API** (via Sumopod) — gateway utama untuk semua LLM
- **Google Gemini Direct API** — legacy, masih tersedia sebagai fallback
- **2 Model Types**: Satset (Gemini Flash) & Expert (Gemini Pro) — admin-configurable

## 🔧 Konfigurasi Environment

```env
# Application
APP_NAME=SatsetUI
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=satsetui
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Queue (untuk background generation)
QUEUE_CONNECTION=database

# LLM API (primary - OpenAI-compatible via Sumopod)
LLM_API_KEY=your_llm_api_key
LLM_BASE_URL=https://ai.sumopod.com/v1

# Gemini API (legacy/fallback)
GEMINI_API_KEY=your_gemini_api_key
GEMINI_API_URL=https://generativelanguage.googleapis.com/v1beta/models

# Telegram Notifications (optional, configurable via admin panel)
TELEGRAM_BOT_TOKEN=your_bot_token
TELEGRAM_CHAT_ID=your_chat_id

# Mail (for email verification)
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
```

> **Note**: API keys dan base URL untuk LLM juga bisa dikonfigurasi via **Admin Panel > LLM Models** (disimpan terenkripsi di database).

## 🔐 Authentication

- ✅ User registration dengan validasi lengkap
- ✅ Email verification wajib (MustVerifyEmail)
- ✅ Login dengan rate limiting (5 attempts)
- ✅ Session management
- ✅ Protected routes dengan middleware `auth` + `verified`
- ✅ Admin middleware (`is_admin` flag)
- ✅ Generation policy (user hanya bisa akses generasi sendiri)

## 👨‍💼 Admin Panel

Admin panel tersedia di `/admin` (requires `is_admin = true`):

| Halaman | Fitur |
|---------|-------|
| **Dashboard** | 20+ stat cards: users, generations, credits, models, system health |
| **User Management** | CRUD, credit adjustment, premium/active toggle, suspend/delete |
| **LLM Models** | 2 fixed types (satset/expert), provider config, API key management |
| **Settings** | Grouped settings (billing, generation, email, notification), reset to default |
| **Generation History** | Filterable list, detail view, prompts/responses, refund/retry actions |

Default admin: `admin@templategen.com` / `admin123`

## 🌍 Bilingual Support

- 🇮🇩 Bahasa Indonesia (Default)
- 🇬🇧 English

Toggle bahasa tersedia di header layout. Preferensi disimpan per-user di database.

## 🎨 Theming

- **Light Mode** (Default)
- **Dark Mode**

Theme preference tersimpan di localStorage. Semua komponen menggunakan Tailwind `dark:` variants.

## 🧪 Testing

```bash
# Run all tests (13 test files: 9 Feature + 4 Unit)
php artisan test --compact

# Run specific test file
php artisan test --compact tests/Feature/GenerationControllerTest.php

# Run by name filter
php artisan test --compact --filter=McpPromptBuilder

# Run with coverage
php artisan test --coverage

# Run frontend tests
npm run test
```

### Test Coverage
| Kategori | Test Files |
|----------|-----------|
| Feature | Admin Dashboard, Admin Menu, Email Verification, Dashboard Controller, Generation Controller, Generation Flow, Refinement Message, Credit Estimation Service |
| Unit | MCP Prompt Builder, Generation Service Context, OpenAI Compatible Service |

## 📝 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 👥 Credits

Built with:
- [Laravel](https://laravel.com) - PHP Framework
- [Vue.js](https://vuejs.org) - Frontend Framework
- [Inertia.js](https://inertiajs.com) - SPA Adapter
- [Tailwind CSS](https://tailwindcss.com) - CSS Framework
- [Vite](https://vitejs.dev) - Build Tool
- [Pest](https://pestphp.com) - Testing Framework

## 📧 Contact

For support or questions, please open an issue on GitHub.
