# Architecture Documentation - SatsetUI

## System Overview

SatsetUI is a **wizard-driven frontend template generator** built with Laravel and Vue.js. The system translates structured user inputs into deterministic code generation instructions.

> **"Sat-set"** - Bahasa slang Indonesia yang berarti cepat dan efisien. SatsetUI membuat pembuatan template UI jadi sat-set!

### Core Architectural Principle

**Separation of Decision and Implementation**

- **Human (via Wizard)**: Makes all design decisions
- **System (Blueprint + MCP)**: Translates decisions into instructions
- **LLM**: Implements instructions with zero creative freedom

No AI decision-making. No prompt interpretation. Pure translation.

### Platform Capabilities

- Bilingual UI (Bahasa Indonesia + English)
- Generator UI dark/light theme
- Free vs Premium membership tiers
- Premium credit billing with configurable margins
- Admin panel for statistics and configuration
- Per-page generation with history recording
- Custom page tracking for future wizard enhancements

---

## Technology Stack

### Backend (Laravel)

- **Framework**: Laravel 12.x
- **PHP Version**: 8.4
- **Database**: MySQL/PostgreSQL
- **Validation**: Form Requests, JSON Schema validation
- **Testing**: Pest
- **SPA Adapter**: Inertia.js

### Frontend (Vue.js)

- **Framework**: Vue 3 with Composition API
- **Language**: TypeScript
- **Build Tool**: Vite
- **State Management**: Reactive state (wizardState.ts)
- **Routing**: Handled by Inertia.js (Laravel-driven)
- **UI Framework**: Tailwind CSS 4

### External Services

- **LLM API**: OpenAI-compatible API via Sumopod (https://ai.sumopod.com/v1)
- **Models**: 2 model types (admin-configurable via Admin Panel)
  - **Satset** — fast generation, 6 credits (default: `gemini-2.0-flash-exp`)
  - **Expert** — premium quality, 15 credits (default: `gemini-2.5-pro-preview`)
- Model name, provider, API key, and base URL stored encrypted in database
- **Telegram**: Admin notifications (new user registration)
- **SMTP**: Email verification
- **Storage**: Local filesystem for generated templates

---

## Data Flow Architecture

### Complete Request Flow

```
┌─────────────────────────────────────────────────────────────┐
│ 1. USER INTERACTION (Vue.js Frontend)                       │
│                                                              │
│  Wizard UI (3 steps)                                        │
│  - Step 1: Framework, Category & Output Format              │
│  - Step 2: Visual Design & Content (Pages, Layout, Theme,   │
│            UI, Components)                                   │
│  - Step 3: LLM Model Selection                              │
│  - wizardState.ts holds reactive state                      │
│  - Validation happens client-side (UX)                      │
│  - Submit triggers POST via axios                           │
└──────────────────────┬───────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│ 2. LARAVEL BACKEND (Validation & Persistence)               │
│                                                              │
│  Route: POST /generation/generate                           │
│  Controller: GenerationController@generate                  │
│  ├─ Form Request validates structure                        │
│  ├─ JSON Schema validator confirms blueprint format         │
│  ├─ Record custom pages to statistics table                 │
│  ├─ Store Blueprint model (database)                        │
│  └─ Return Blueprint ID + validated data                    │
└──────────────────────┬───────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│ 3. PER-PAGE GENERATION LOOP                                 │
│                                                              │
│  For each page in blueprint.pages + blueprint.customPages:  │
│  ├─ Build page-specific MCP prompt                          │
│  ├─ Call LLM API                                            │
│  ├─ Record prompt + response to history                     │
│  ├─ Update generation progress                              │
│  └─ Continue to next page or handle error                   │
└──────────────────────┬───────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│ 4. MCP PROMPT BUILDER (Core Logic - Per Page)               │
│                                                              │
│  Service: McpPromptBuilder::buildForPage()                  │
│  ├─ Load Blueprint JSON                                     │
│  ├─ Assemble prompt sections:                               │
│  │   ├─ ROLE: Define LLM expertise                          │
│  │   ├─ CONTEXT: Framework, category, output format         │
│  │   ├─ CONSTRAINTS: Technology limits, no decisions        │
│  │   ├─ REQUIREMENTS: Layout, theme, components             │
│  │   ├─ PAGE-SPECIFIC: Current page requirements            │
│  │   └─ OUTPUT FORMAT: Single file structure                │
│  └─ Return deterministic MCP string for single page         │
└──────────────────────┬───────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│ 5. MODEL SELECTION + BILLING (Platform Logic)               │
│                                                              │
│  Service: CreditService + CreditEstimationService           │
│  ├─ User selects model type (satset or expert)              │
│  ├─ Look up model config from llm_models table              │
│  ├─ Calculate base cost (model credits + extras)            │
│  ├─ Apply error margin (default 10%, admin configurable)    │
│  ├─ Apply profit margin (default 5%, admin configurable)    │
│  ├─ Validate user credit balance                            │
│  └─ Reserve/charge credits atomically                       │
└──────────────────────┬───────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│ 6. LLM API CALL (External Service - Per Page)               │
│                                                              │
│  Service: OpenAICompatibleService::generatePage()           │
│  ├─ Send page-specific MCP prompt to configured LLM         │
│  ├─ Parse response (single page code)                       │
│  ├─ Record token usage (input + output)                     │
│  ├─ Validate output format                                  │
│  └─ Return structured code result                           │
└──────────────────────┬───────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│ 7. HISTORY RECORDING (Per Page)                             │
│                                                              │
│  Model: PageGeneration                                      │
│  ├─ Store MCP prompt sent                                   │
│  ├─ Store raw LLM response                                  │
│  ├─ Store token usage (input/output)                        │
│  ├─ Store processing time                                   │
│  ├─ Store success/failure status                            │
│  └─ Update credit estimation learning data                  │
└──────────────────────┬───────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│ 8. CODE STORAGE & PROCESSING                                │
│                                                              │
│  ├─ Extract file from LLM response                          │
│  ├─ Store in Generation model (generated_content JSON)      │
│  ├─ Update Generation status and progress                   │
│  └─ Continue to next page or finalize                       │
└──────────────────────┬───────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│ 9. PREVIEW RENDERER (Vue.js Frontend)                       │
│                                                              │
│  Component: Generation/Show.vue                             │
│  ├─ Fetch generated files via API                           │
│  ├─ Render in iframe (sandboxed)                            │
│  ├─ Syntax highlighting for code view                       │
│  └─ Download as ZIP option                                  │
└─────────────────────────────────────────────────────────────┘
```

---

## Component Architecture

### Backend (Laravel)

#### 1. HTTP Layer

**Controllers** (Thin, orchestration only)
- `DashboardController`: User dashboard
- `GenerationController`: Generate templates, progress tracking
- `TemplateController`: List user templates
- `LlmModelController`: Get available LLM models
- `Admin/DashboardController`: Admin statistics
- `Admin/UserManagementController`: User CRUD
- `Admin/LlmModelController`: LLM model CRUD
- `Admin/SettingsController`: Admin settings
- `Admin/GenerationHistoryController`: Generation history
- `PreviewController`: Live preview workspace management
- `LanguageController`: Language preference switching

**Form Requests** (Validation)
- Located in `app/Http/Requests`
- Validates wizard inputs against rules

#### 2. Service Layer (Business Logic)

**McpPromptBuilder.php** (Core Service - 52KB)
```php
class McpPromptBuilder
{
    /**
     * Build deterministic MCP prompt for a single page
     * Auto-applies best defaults: interaction=moderate, 
     * responsiveness=fully-responsive, codeStyle=documented
     */
    public function buildForPage(array $blueprint, string $pageName): string;
    
    /**
     * Build prompt with previous page context for consistency
     */
    public function buildForPageWithContext(
        array $blueprint, 
        string $pageName,
        array $previousPageContext
    ): string;
}
```

**GenerationService.php** (23KB)
```php
class GenerationService
{
    public function startGeneration(array $blueprint, User $user, ?string $modelName = null): array;
    public function generateNextPage(Generation $generation, int $retryCount = 0): array;
    public function continueGeneration(Generation $generation): void;
}
```

**CreditService.php**
```php
class CreditService
{
    public function deductCredits(User $user, int $amount, string $reason): bool;
    public function refundCredits(User $user, int $amount, string $reason): bool;
    public function calculateCharge(int $modelCredits, int $totalPages, int $totalComponents): array;
}
```

**CreditEstimationService.php**
```php
class CreditEstimationService
{
    public function getEstimatedTokensForPage(string $pageType, string $category, string $modelId): array;
    public function updateEstimation(PageGeneration $pageGeneration): void;
}
```

**CostTrackingService.php**
```php
class CostTrackingService
{
    public function trackCost(Generation $generation, array $response, float $actualCostUsd): void;
}
```

**AdminStatisticsService.php**
```php
class AdminStatisticsService
{
    public function getDashboardStats(): array;
    public function getUserStats(): array;
    public function getGenerationStats(): array;
    public function getCreditStats(): array;
}
```

**WorkspaceService.php**
```php
class WorkspaceService
{
    // Manage live preview workspaces (npm install, dev server lifecycle)
}
```

**ScaffoldGeneratorService.php**
```php
class ScaffoldGeneratorService
{
    // Generate deterministic project scaffold (package.json, vite config, router, etc.)
}
```

**TelegramService.php**
```php
class TelegramService
{
    // Telegram bot messaging for admin notifications
}
```

#### 3. Data Layer (Models)

| Model | Purpose |
|-------|---------|
| `User` | User accounts with credits and premium status |
| `Generation` | Main generation record with blueprint and status |
| `PageGeneration` | Per-page generation history |
| `LlmModel` | Available LLM models and pricing |
| `AdminSetting` | Admin-configurable settings |
| `CreditTransaction` | Credit movement audit trail |
| `CreditEstimation` | Token estimation learning data |
| `CustomPageStatistic` | Custom page usage tracking |
| `GenerationCost` | LLM cost tracking |
| `GenerationFailure` | Failure records for debugging |
| `GenerationFile` | Generated file storage (per page/scaffold) |
| `PreviewSession` | Live preview session lifecycle tracking |
| `RefinementMessage` | Chat refinement conversation messages |
| `Project` | User projects |
| `Template` | User template records |

**Relationships**
- User → hasMany(Generation)
- Generation → hasMany(PageGeneration)
- Generation → belongsTo(LlmModel)
- Generation → hasMany(GenerationCost)
- Generation → hasMany(GenerationFile)
- Generation → hasMany(RefinementMessage)
- Generation → hasOne(PreviewSession)

---

### Frontend (Vue.js)

#### Directory Structure

```
resources/js/
├── pages/
│   ├── Home.vue                 # Landing page
│   ├── Auth/
│   │   ├── Login.vue
│   │   ├── Register.vue
│   │   └── VerifyEmail.vue         # Email verification page
│   ├── Dashboard/
│   │   └── Index.vue            # User dashboard
│   ├── Wizard/
│   │   └── Index.vue            # Wizard container
│   ├── Generation/
│   │   └── Show.vue             # Generation progress & preview
│   ├── Templates/
│   │   └── Index.vue            # User templates list
│   └── Admin/
│       ├── Index.vue            # Admin dashboard
│       ├── Users/
│       │   ├── Show.vue
│       │   └── Edit.vue
│       ├── Generations/
│       │   ├── Index.vue
│       │   └── Show.vue
│       ├── Models/
│       │   ├── Create.vue
│       │   ├── Show.vue
│       │   └── Edit.vue
│       └── Settings/
│           └── Index.vue
├── wizard/
│   ├── wizardState.ts           # Reactive state management
│   ├── types.ts                 # TypeScript interfaces
│   └── steps/
│       ├── Step1FrameworkCategoryOutput.vue
│       ├── Step2VisualDesignContent.vue
│       └── Step3LlmModel.vue
├── components/
│   ├── generation/
│   │   ├── LivePreview.vue      # Live preview iframe
│   │   └── FileTree.vue         # File tree navigation
│   ├── landing/
│   │   ├── Navbar.vue
│   │   ├── HeroSection.vue
│   │   ├── FeaturesSection.vue
│   │   ├── HowItWorksSection.vue
│   │   ├── FaqSection.vue
│   │   ├── CtaSection.vue
│   │   └── Footer.vue
│   └── (other shared components)
├── layouts/
│   ├── AppLayout.vue            # Main layout with sidebar
│   ├── AdminLayout.vue          # Admin panel layout
│   └── GuestLayout.vue          # Auth pages layout
└── lib/
    ├── i18n.ts                  # Internationalization
    ├── i18n/
    │   ├── en/                  # English translations
    │   └── id/                  # Indonesian translations
    ├── theme.ts                 # Theme management
    └── utils.ts                 # Utility functions
```

#### State Management Pattern

```typescript
// wizard/wizardState.ts
import { reactive, computed } from 'vue';

export const wizardState = reactive({
  currentStep: 1,
  
  // Step 1: Framework, Category & Output
  framework: 'tailwind',
  category: 'admin-dashboard',
  customCategoryName: '',
  customCategoryDescription: '',
  outputFormat: 'vue',
  customOutputFormat: '',
  
  // Step 2: Visual Design & Content
  pages: ['login', 'dashboard'],
  customPages: [],
  layout: {
    navigation: 'sidebar',
    sidebarDefaultState: 'expanded',
    breadcrumbs: true,
    footer: 'minimal',
  },
  theme: {
    primary: '#3B82F6',
    secondary: '#6366F1',
    mode: 'light',
    background: 'solid',
  },
  ui: {
    density: 'comfortable',
    borderRadius: 'rounded',
  },
  components: ['buttons', 'forms', 'cards', 'alerts'],
  customComponents: [],
  chartLibrary: undefined,
  
  // Step 3: LLM Model
  llmModel: '',
  modelCredits: 0,
  calculatedCredits: 0,
  
  // Auto-selected (not in wizard UI)
  interaction: 'moderate',
  responsiveness: 'fully-responsive',
  codeStyle: 'documented',
});
```

---

## Database Schema

### Key Tables

```sql
-- Users with credits
CREATE TABLE users (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    password VARCHAR(255),
    credits INT DEFAULT 100,
    is_premium BOOLEAN DEFAULT FALSE,
    is_admin BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    language VARCHAR(5) DEFAULT 'id',
    suspended_at TIMESTAMP NULL
);

-- Main generation record
CREATE TABLE generations (
    id BIGINT PRIMARY KEY,
    user_id BIGINT,
    model_used VARCHAR(100),
    blueprint JSON,
    generated_content JSON,
    status ENUM('pending', 'processing', 'completed', 'failed'),
    credits_used INT,
    credit_breakdown JSON,
    error_margin_percent DECIMAL(5,2) DEFAULT 10.00,
    profit_margin_percent DECIMAL(5,2) DEFAULT 5.00,
    current_page_index INT DEFAULT 0,
    total_pages INT,
    current_status VARCHAR(255),
    error_message TEXT,
    processing_time INT,
    started_at TIMESTAMP,
    completed_at TIMESTAMP
);

-- Per-page generation history
CREATE TABLE page_generations (
    id BIGINT PRIMARY KEY,
    generation_id BIGINT,
    page_name VARCHAR(100),
    page_type ENUM('predefined', 'custom'),
    mcp_prompt TEXT,
    llm_response TEXT,
    raw_prompt TEXT,
    raw_response TEXT,
    input_tokens INT DEFAULT 0,
    output_tokens INT DEFAULT 0,
    processing_time_ms INT DEFAULT 0,
    status ENUM('pending', 'processing', 'completed', 'failed')
);

-- LLM models configuration (2 model types, admin-configurable)
CREATE TABLE llm_models (
    id BIGINT PRIMARY KEY,
    model_type ENUM('satset', 'expert') UNIQUE,
    provider ENUM('gemini', 'openai'),
    model_name VARCHAR(255),
    api_key TEXT,           -- encrypted
    base_url TEXT,          -- encrypted, nullable
    base_credits INT DEFAULT 1,
    is_active BOOLEAN DEFAULT TRUE,
    timestamps
);

-- Admin settings
CREATE TABLE admin_settings (
    id BIGINT PRIMARY KEY,
    key VARCHAR(100) UNIQUE,
    value TEXT,
    type ENUM('string', 'integer', 'float', 'boolean', 'json'),
    description TEXT
);
```

---

## Security Considerations

### Input Validation

- **Client-Side**: UX-focused validation (instant feedback)
- **Server-Side**: Authoritative validation (Form Requests)
- **Blueprint Schema**: Strict types, enums, required fields

### LLM API Security

- **API Keys**: Stored in `.env`, never exposed client-side
- **Rate Limiting**: Laravel middleware
- **Cost Control**: Maximum token limits, request timeouts

### Authentication & Authorization

- Laravel authentication with sessions
- Admin middleware for admin panel
- CSRF protection on all POST requests

---

## Error Handling

### Per-Page Error Recovery

- **Automatic Retry**: Up to 3 retries with exponential backoff
- **Single Page Failure**: Mark page as failed, continue with others
- **Credit Refund**: Automatic refund on complete failure

### Generation Flow

1. Start generation → Reserve credits
2. Generate each page → Track progress
3. On success → Finalize, keep credits
4. On failure → Refund credits, log error

---

## Development Commands

```bash
# Backend
composer install
php artisan migrate
php artisan db:seed --class=LlmModelSeeder
php artisan serve
php artisan queue:work

# Frontend
npm install
npm run dev

# Testing
php artisan test
npm run test
```

---

## Routes Overview

### Public Routes
- `GET /` - Landing page (Home.vue)

### Guest Routes
- `GET /login` - Login page
- `POST /login` - Login action
- `GET /register` - Register page
- `POST /register` - Register action

### Authenticated Routes
- `GET /dashboard` - User dashboard
- `GET /wizard` - Template wizard
- `POST /generation/generate` - Start generation
- `GET /generation/{id}` - View generation
- `GET /generation/{id}/progress` - Get progress
- `POST /generation/{id}/next` - Generate next page
- `POST /generation/{id}/retry-failed` - Retry failed pages
- `POST /generation/{id}/background` - Continue in background queue
- `GET /generation/{id}/stream` - SSE streaming progress
- `POST /generation/{id}/refine` - Refinement chat
- `PATCH /generation/{id}/name` - Update template name
- `GET /templates` - User templates

### Preview Routes
- `POST /generation/{id}/preview/setup` - Setup workspace
- `GET /generation/{id}/preview/status` - Get preview status
- `GET /generation/{id}/preview/logs` - Get preview logs
- `GET /generation/{id}/preview/proxy` - Proxy requests to dev server
- `POST /generation/{id}/preview/stop` - Stop preview
- `GET /generation/{id}/preview/static` - Serve static preview

### File Routes
- `GET /generation/{id}/files/{fileId}` - Download generation file

### Utility Routes
- `POST /language` - Language switching
- `GET /api/llm/models` - Get available LLM models

### Admin Routes (`/admin/*`)
- `GET /admin` - Admin dashboard
- `GET /admin/users` - User management
- `GET /admin/models` - LLM models
- `GET /admin/settings` - Settings
- `GET /admin/generations` - Generation history

---

## Sat-set! 🚀

SatsetUI is designed for speed and efficiency - making UI template generation as quick as saying "sat-set"!
