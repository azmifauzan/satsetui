# Template Generator

Platform wizard-driven untuk menghasilkan template frontend yang konsisten, dapat diprediksi, dan siap produksi. Tanpa coding manual, tanpa hasil yang acak.

## 🌟 Fitur Utama

- **Wizard Terstruktur**: Konfigurasi melalui 11 langkah jelas tanpa prompt bebas
- **Hasil Deterministik**: Pilihan yang sama menghasilkan output yang sama, setiap saat
- **Multi Framework**: Dukungan untuk Tailwind CSS dan Bootstrap
- **Kode Profesional**: Output bersih dan terstruktur mengikuti best practices
- **Highly Customizable**: Pilih tema, layout, komponen sesuai kebutuhan
- **Preview Instan**: Lihat hasil template secara real-time

## 🎯 Kategori Template

1. **Admin Dashboard** - Internal tools, data-heavy, CRUD operations
2. **Company Profile** - Public-facing, company content showcase
3. **Landing Page** - Marketing-focused, conversion-optimized
4. **SaaS Application** - User accounts, full features, pricing
5. **Blog / Content** - Articles, reading experience, categories
6. **Portfolio / Agency** - Showcase projects, case studies, gallery

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
```

7. Buka aplikasi di browser
```
http://127.0.0.1:8000
```

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
│   ├── Blueprints/              # Schema definitions
│   ├── Http/
│   │   ├── Controllers/         # Controllers
│   │   │   └── Auth/           # Authentication controllers
│   │   └── Requests/           # Form requests & validation
│   ├── Models/                 # Eloquent models
│   └── Services/               # Business logic
│       └── McpPromptBuilder.php
├── resources/
│   ├── js/
│   │   ├── pages/              # Inertia pages
│   │   │   ├── Auth/           # Login & Register
│   │   │   ├── Wizard/         # Template wizard
│   │   │   └── Home.vue        # Landing page
│   │   └── lib/                # Utilities
│   └── css/
│       └── app.css             # Tailwind CSS
├── routes/
│   └── web.php                 # Application routes
├── database/
│   └── migrations/             # Database migrations
└── docs/                       # Documentation
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
