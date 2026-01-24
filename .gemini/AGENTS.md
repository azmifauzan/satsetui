# Antigravity Instructions for SatsetUI

## Project Overview

**SatsetUI** is a wizard-driven frontend template generator built with Laravel and Vue.js. The name comes from Indonesian slang "sat-set" meaning quick and efficient - SatsetUI makes UI template generation as quick as saying "sat-set"!

### Core Philosophy

1. **No Free-Form Prompts**: Users configure templates through a structured 3-step wizard, not natural language descriptions
2. **Deterministic Output**: Same wizard selections MUST produce identical results every time
3. **LLM as Implementation Engine**: The LLM receives fully-formed prompts and implements, never decides
4. **Per-Page Generation**: Each page is generated separately for better context and error recovery

---

## Technology Stack

### Backend
- **Framework**: Laravel 12
- **Language**: PHP 8.2+
- **Database**: PostgreSQL/MySQL
- **SPA Adapter**: Inertia.js v2
- **Testing**: Pest

### Frontend
- **Framework**: Vue.js 3 with Composition API
- **Language**: TypeScript
- **CSS**: Tailwind CSS 4
- **Build Tool**: Vite
- **Testing**: Vitest

### AI Integration
- **API**: OpenAI-compatible via Sumopod
- **Models**: Gemini, GPT, Claude families
- **Free Tier**: Gemini 2.5 Flash

---

## Project Structure

```
satsetui/
├── app/
│   ├── Http/Controllers/      # Request handlers
│   │   ├── Admin/             # Admin panel
│   │   └── Auth/              # Authentication
│   ├── Models/                # Eloquent models
│   └── Services/              # Business logic
│       ├── McpPromptBuilder.php     # MCP prompt generation
│       ├── GenerationService.php    # Template generation
│       ├── CreditService.php        # Credit management
│       └── ...
├── resources/js/
│   ├── pages/                 # Inertia pages
│   │   ├── Admin/             # Admin pages
│   │   ├── Auth/              # Login/Register
│   │   ├── Dashboard/         # User dashboard
│   │   ├── Generation/        # Generation progress
│   │   ├── Wizard/            # Template wizard
│   │   └── Home.vue           # Landing page
│   ├── wizard/                # Wizard components
│   │   ├── steps/             # Step 1, 2, 3
│   │   ├── wizardState.ts     # State management
│   │   └── types.ts           # TypeScript types
│   ├── layouts/               # Layout wrappers
│   └── lib/                   # Utilities (i18n, theme)
├── routes/web.php             # Application routes
├── database/migrations/       # Database schema
└── docs/                      # Documentation
```

---

## Key Features to Understand

### 1. 3-Step Wizard

The wizard is fixed at exactly 3 steps:

1. **Step 1: Framework, Category & Output Format**
   - CSS Framework: Tailwind CSS, Bootstrap, Pure CSS
   - Category: Admin Dashboard, Landing Page, SaaS, Blog, E-Commerce, Custom
   - Output: HTML+CSS, React, Vue, Angular, Svelte, Custom

2. **Step 2: Visual Design & Content**
   - Pages selection (predefined + custom)
   - Layout & Navigation
   - Theme & Colors
   - UI Density & Style
   - Components

3. **Step 3: LLM Model Selection**
   - Model choice (free users: Gemini Flash only)
   - Credit breakdown with margins
   - Generation start

### 2. Per-Page Generation

Templates are generated page by page:
- Each page gets its own focused MCP prompt
- Progress tracking shows X/Y pages
- Single page failure doesn't stop others
- All prompts/responses are recorded

### 3. Credit System

```
Total = CEIL((Model + ExtraPages + ExtraComponents) × (1 + ErrorMargin) × (1 + ProfitMargin))
```

- Error Margin: 10% default (admin configurable)
- Profit Margin: 5% default (admin configurable)
- Base quota: 5 pages, 6 components
- Extra: +1 credit/page, +0.5 credit/component

### 4. Bilingual UI

- **MANDATORY**: All text uses i18n system
- Default: Indonesian (id)
- Also: English (en)
- Usage: `const { t } = useI18n()` → `t.value.section.key`

### 5. Theme Support

- **MANDATORY**: All components support dark mode
- Default: Light theme
- Usage: Tailwind `dark:` variants everywhere
- Never use inline styles that ignore theme

---

## When Working on This Project

### DO

✅ Use the i18n system for ALL user-facing text
✅ Add `dark:` variants for ALL Tailwind styling
✅ Wrap authenticated pages with `AppLayout.vue`
✅ Generate templates per-page, never all at once
✅ Record all LLM prompts and responses
✅ Follow existing code conventions
✅ Write tests for new functionality
✅ Use TypeScript for Vue components
✅ Use Services for business logic, keep Controllers thin

### DO NOT

❌ Hardcode user-facing text strings
❌ Create components without dark mode support
❌ Suggest free-form prompt input fields
❌ Generate multiple pages in one LLM call
❌ Use inline styles that bypass theme
❌ Add new dependencies without approval
❌ Skip tests for new features

---

## Key Services

### McpPromptBuilder (app/Services/McpPromptBuilder.php)

Builds deterministic MCP prompts per page. Largest service (~52KB).

```php
// Key methods
public function buildForPage(array $blueprint, string $pageName): string;
public function buildForPageWithContext(array $blueprint, string $pageName, array $context): string;
```

### GenerationService (app/Services/GenerationService.php)

Orchestrates the generation process.

```php
public function startGeneration(array $blueprint, User $user, ?string $modelName = null): array;
public function generateNextPage(Generation $generation, int $retryCount = 0): array;
```

### CreditService (app/Services/CreditService.php)

Manages user credits.

```php
public function calculateCharge(int $modelCredits, int $totalPages, int $totalComponents): array;
public function deductCredits(User $user, int $amount, string $reason): bool;
public function refundCredits(User $user, int $amount, string $reason): bool;
```

---

## Key Models

| Model | Purpose |
|-------|---------|
| `User` | User accounts with credits, premium status, admin flag |
| `Generation` | Template generation record with blueprint and status |
| `PageGeneration` | Per-page generation history and tokens |
| `LlmModel` | Available LLM models and pricing |
| `AdminSetting` | Admin-configurable settings |
| `CreditTransaction` | Credit movement audit trail |

---

## Routes Overview

### Public
- `GET /` → Home.vue (Landing page)

### Guest
- `GET /login` → Login
- `GET /register` → Register

### Authenticated
- `GET /dashboard` → User dashboard
- `GET /wizard` → Template wizard
- `POST /generation/generate` → Start generation
- `GET /generation/{id}` → View generation
- `GET /templates` → User templates

### Admin (`/admin/*`)
- Dashboard, Users, LLM Models, Settings, Generations

---

## Documentation

- `/docs/product-instruction.md` - Complete wizard specification
- `/docs/architecture.md` - System architecture
- `/docs/llm-credit-system.md` - Credit calculation details
- `/docs/admin-panel-architecture.md` - Admin panel structure
- `/docs/mvp-plan.md` - Development roadmap

---

## Development Commands

```bash
# Setup
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed --class=LlmModelSeeder

# Development
php artisan serve      # Laravel server
npm run dev            # Vite dev server
php artisan queue:work # Queue worker

# Testing
php artisan test                    # All PHP tests
php artisan test --filter=TestName  # Specific test
npm run test                        # Frontend tests
```

---

## Common Tasks

### Adding a New Page to Wizard Step 2

1. Add page to predefined options in `Step2VisualDesignContent.vue`
2. Add translations in `lib/i18n.ts`
3. Update McpPromptBuilder for page-specific requirements
4. Add tests

### Adding a New Admin Setting

1. Add migration to create setting in `admin_settings` table
2. Update `AdminSetting` model if needed
3. Add to Settings page UI
4. Add translations

### Adding a New LLM Model

1. Use admin panel at `/admin/models`
2. Or add via seeder `LlmModelSeeder.php`

---

## Sat-set! 🚀

Remember: SatsetUI is about speed and efficiency. Every feature should help users generate templates quickly and reliably. The wizard-first approach ensures deterministic, reproducible results every time.
