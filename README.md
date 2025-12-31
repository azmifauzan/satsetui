# Template Generator

Platform wizard-driven untuk menghasilkan template frontend yang konsisten, dapat diprediksi, dan siap produksi. Sistem berbasis konfigurasi terstruktur, bukan prompt bebas.

## 🌟 Fitur Utama

- **Wizard Terstruktur 3 Langkah**: Konfigurasi melalui langkah yang jelas dan terstruktur
  - Step 1: Framework, Kategori & Output Format
  - Step 2: Desain Visual & Konten
  - Step 3: Model LLM
- **Hasil Deterministik**: Pilihan yang sama menghasilkan output yang sama, setiap saat
- **Multi Framework**: Dukungan untuk Tailwind CSS, Bootstrap, dan Pure CSS
- **Multi Output Format**: HTML+CSS, React, Vue, Angular, Svelte, atau Custom
- **Kode Profesional**: Output bersih dan terstruktur mengikuti best practices
- **Sistem Kredit**: Manajemen kredit untuk generasi template dengan berbagai model LLM
- **Generasi Per Halaman**: Setiap halaman digenerate secara terpisah untuk kontrol lebih baik
- **Bilingual**: Dukungan Bahasa Indonesia dan English
- **Dark Mode**: Tema terang dan gelap

## 🎯 Kategori Template

1. **Admin Dashboard** - Internal tools, data-heavy, CRUD operations
2. **Company Profile** - Public-facing, company content showcase
3. **Landing Page** - Marketing-focused, conversion-optimized
4. **SaaS Application** - User accounts, full features, pricing
5. **Blog / Content** - Articles, reading experience, categories
6. **E-Commerce** - Product catalogs, shopping cart, checkout
7. **Custom** - Kategori kustom dengan deskripsi sendiri

## 🚀 Quick Start

### Prerequisites

- PHP 8.2+
- PostgreSQL/MySQL
- Node.js 18+
- Composer

### Installation

1. Clone repository
```bash
git clone https://github.com/yourusername/template-aspri.git
cd template-aspri
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
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

5. Run migrations
```bash
php artisan migrate
```

6. Start development servers
```bash
# Terminal 1 - Laravel
php artisan serve

# Terminal 2 - Vite
npm run dev

# Terminal 3 - Queue Worker (untuk generasi template)
php artisan queue:work
```

7. Seed data awal (opsional)
```bash
php artisan db:seed --class=LlmModelSeeder
```

8. Buka aplikasi di browser
```
http://127.0.0.1:8000
```

## 💎 Sistem Kredit

Aplikasi menggunakan sistem kredit untuk generasi template:

- **Free User**: Mendapat kredit gratis, hanya bisa menggunakan Gemini 2.5 Flash
- **Premium User**: Dapat memilih berbagai model LLM:
  - Gemini 2.5 Flash
  - Gemini 2.5 Pro
  - GPT-4
  - GPT-4 Turbo
  - Claude Sonnet
  - Dan lainnya

### Perhitungan Kredit

Kredit dihitung berdasarkan:
- Model yang dipilih
- Jumlah halaman template
- Jumlah komponen
- Margin error (10% default)
- Margin profit (5% default)

## 🎨 Cara Menggunakan Generator

### 1. Login atau Register
Buat akun baru atau login dengan akun existing

### 2. Akses Dashboard
Setelah login, Anda akan diarahkan ke dashboard yang menampilkan:
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
- Pilih halaman yang dibutuhkan
- Konfigurasi layout & navigasi
- Atur tema (warna, mode dark/light)
- Pilih komponen (forms, buttons, charts, dll)

#### Step 3: Model LLM
- Pilih model AI untuk generasi (free user hanya Gemini Flash)
- Lihat estimasi biaya kredit
- Mulai generasi

### 4. Monitor Progres
- Lihat progres generasi per halaman
- Tunggu hingga semua halaman selesai
- Download hasil template

### 5. Kelola Template
- Lihat riwayat template di halaman Templates
- Download ulang template yang sudah dibuat
- Lihat detail setiap generasi

## 🐳 Docker Deployment

### Build Docker Image

```bash
docker build -t template-generator .
```

### Run dengan Docker Compose

```bash
docker-compose up -d
```

Aplikasi akan berjalan di `http://localhost:8000`

## 📁 Struktur Project

```
template-aspri/
├── app/
│   ├── Blueprints/              # JSON schema untuk blueprint template
│   ├── Http/
│   │   ├── Controllers/         # Controllers (Wizard, Generation, Admin)
│   │   │   └── Auth/           # Authentication controllers
│   │   └── Requests/           # Form requests & validasi
│   ├── Jobs/                   # Background jobs
│   │   └── ProcessTemplateGeneration.php  # Job generasi template
│   ├── Models/                 # Eloquent models
│   │   ├── User.php
│   │   ├── Generation.php      # Model untuk generasi template
│   │   ├── PageGeneration.php  # Model untuk generasi per halaman
│   │   ├── LlmModel.php        # Model untuk LLM yang tersedia
│   │   └── ...
│   ├── Notifications/          # Email & notifikasi
│   └── Services/               # Business logic
│       ├── McpPromptBuilder.php      # Build MCP prompt per halaman
│       ├── GenerationService.php     # Orchestrasi generasi
│       ├── GeminiService.php         # Integrasi Gemini API
│       ├── OpenAICompatibleService.php  # Integrasi OpenAI-compatible API
│       ├── CreditService.php         # Manajemen kredit
│       └── CostTrackingService.php   # Tracking biaya generasi
├── resources/
│   ├── js/
│   │   ├── pages/              # Inertia pages
│   │   │   ├── Auth/           # Login & Register
│   │   │   ├── Dashboard/      # User dashboard
│   │   │   ├── Wizard/         # Template wizard
│   │   │   ├── Generation/     # Halaman monitor generasi
│   │   │   ├── Templates/      # Halaman template
│   │   │   └── Welcome.vue     # Landing page
│   │   ├── layouts/            # Layout components
│   │   │   └── AppLayout.vue   # Layout utama dengan sidebar
│   │   └── lib/                # Utilities (i18n, theme)
│   └── css/
│       └── app.css             # Tailwind CSS
├── routes/
│   └── web.php                 # Application routes
├── database/
│   ├── migrations/             # Database migrations
│   └── seeders/
│       └── LlmModelSeeder.php  # Seed data model LLM
└── docs/                       # Documentation lengkap
    ├── product-instruction.md  # Spesifikasi wizard 3 langkah
    ├── architecture.md         # Arsitektur per-page generation
    ├── llm-credit-system.md    # Sistem kredit
    └── ...
```

## 🛠 Teknologi yang Digunakan

### Backend
- **Laravel 12** - PHP Framework
- **PostgreSQL** - Database
- **Inertia.js** - Modern monolith SPA adapter
- **Queue Jobs** - Background processing untuk generasi template

### Frontend
- **Vue.js 3** - Frontend framework dengan Composition API
- **TypeScript** - Type safety
- **Tailwind CSS 4** - Utility-first CSS framework
- **Vite** - Build tool

### AI/LLM Integration
- **Google Gemini API** - Gemini 2.5 Flash & Pro
- **OpenAI Compatible API** - GPT-4, GPT-4 Turbo, Claude, dll

## 🔧 Konfigurasi Environment

Tambahkan ke file `.env`:

```env
# Database
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=template_aspri
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Queue (untuk background jobs)
QUEUE_CONNECTION=database

# Gemini API
GEMINI_API_KEY=your_gemini_api_key
GEMINI_API_URL=https://generativelanguage.googleapis.com/v1beta/models

# OpenAI Compatible API (optional)
OPENAI_API_KEY=your_openai_api_key
OPENAI_API_URL=https://api.openai.com/v1

# Kredit Sistem
DEFAULT_FREE_CREDITS=100
CREDIT_ERROR_MARGIN=10
CREDIT_PROFIT_MARGIN=5
```

## 🔐 Authentication

Aplikasi menggunakan Laravel authentication dengan fitur:

- ✅ User registration dengan validasi lengkap
- ✅ Login dengan remember me
- ✅ Rate limiting (5 attempts)
- ✅ Session management
- ✅ Protected routes

## 🌍 Bilingual Support

Aplikasi mendukung dua bahasa:
- 🇮🇩 Bahasa Indonesia (Default)
- 🇬🇧 English

Toggle bahasa tersedia di navbar.

## 🎨 Theming

- **Light Mode** (Default)
- **Dark Mode** 

Theme preference tersimpan di localStorage.

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=AuthenticationTest

# Run with coverage
php artisan test --coverage
```

## 📝 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 👥 Credits

Built with:
- [Laravel](https://laravel.com) - PHP Framework
- [Vue.js](https://vuejs.org) - Frontend Framework
- [Inertia.js](https://inertiajs.com) - SPA Framework
- [Tailwind CSS](https://tailwindcss.com) - CSS Framework
- [Vite](https://vitejs.dev) - Build Tool

## 📧 Contact

For support or questions, please open an issue on GitHub.
