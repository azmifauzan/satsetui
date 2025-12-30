# Architecture Documentation

## System Overview

This is a **wizard-driven frontend template generator** built with Laravel and Vue.js. The system translates structured user inputs into deterministic code generation instructions.

### Core Architectural Principle

**Separation of Decision and Implementation**

- **Human (via Wizard)**: Makes all design decisions
- **System (Blueprint + MCP)**: Translates decisions into instructions
- **LLM**: Implements instructions with zero creative freedom

No AI decision-making. No prompt interpretation. Pure translation.

This repository also includes platform capabilities around generation:
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
- **PHP Version**: 8.2+
- **Database**: MySQL/PostgreSQL (for storing Blueprints, Projects, Generation History)
- **Validation**: Form Requests, JSON Schema validation
- **Testing**: Pest

**Key Packages** (current repo):
- `inertiajs/inertia-laravel`: Frontend-backend bridge

### Frontend (Vue.js)

- **Framework**: Vue 3 with Composition API
- **Language**: TypeScript
- **Build Tool**: Vite
- **State Management**: Reactive state (wizardState.ts)
- **Routing**: Handled by Inertia.js (Laravel-driven)
- **UI Framework**: Tailwind CSS for the generator UI; generated templates support Tailwind CSS, Bootstrap, or Pure CSS

### External Services

- **LLM API**: OpenAI-compatible API via Sumopod (https://ai.sumopod.com/v1)
- **Models**: 6 LLM models from Gemini, Claude, and GPT families
  - Free tier: Gemini 2.5 Flash (3 credits/generation)
  - Premium tier: 5 additional models (2-15 credits/generation)
- **Credits**: 1 credit = Rp 1,000
- **Documentation**: See [docs/llm-credit-system.md](./llm-credit-system.md)
- **Storage**: Local filesystem or S3 for generated templates

### Platform Concerns (Non-Template)

- **Internationalization (i18n)**: Indonesian + English for the generator UI
- **Billing**: Premium credits + cost accounting per generation with margins
- **Admin**: Configuration (available models, error margin, profit margin) + statistics + custom page tracking

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
│  Service: ModelSelector + BillingCalculator                  │
│  ├─ Determine membership tier (free/premium)                 │
│  ├─ Free: force Gemini 2.5 Flash                            │
│  ├─ Premium: allow admin-defined model choices               │
│  ├─ Calculate base cost (model + extras)                     │
│  ├─ Apply error margin (default 10%, admin configurable)     │
│  ├─ Apply profit margin (default 5%, admin configurable)     │
│  ├─ Validate premium credit balance                          │
│  └─ Reserve/charge credits atomically                        │
└──────────────────────┬───────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│ 6. LLM API CALL (External Service - Per Page)               │
│                                                              │
│  Service: LlmService::generatePage()                        │
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
│  Service: GenerationHistoryService::recordPage()            │
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
│  Service: TemplateProcessor::processPage()                  │
│  ├─ Extract file from LLM response                          │
│  ├─ Store in filesystem (storage/templates/{gen_id}/{page}) │
│  ├─ Update Generation model with file reference             │
│  └─ Continue to next page or finalize                       │
└──────────────────────┬───────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│ 9. PREVIEW RENDERER (Vue.js Frontend)                       │
│                                                              │
│  Component: TemplatePreview.vue                             │
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
- `BlueprintController`: CRUD for blueprints
- `GenerationController`: Generate templates, progress tracking
- `WizardController`: Serve wizard UI (Inertia)
- `AdminController`: Statistics and configuration

**Form Requests** (Validation)
- `StoreBlueprintRequest`: Validates wizard inputs against rules
- `GenerateTemplateRequest`: Validates generation parameters

**API Resources** (Response formatting)
- `BlueprintResource`: Format blueprint JSON for frontend
- `GenerationResource`: Format generation metadata
- `PageGenerationResource`: Format per-page generation history

#### 2. Service Layer (Business Logic)

**McpPromptBuilder.php** (Core Service)
```php
class McpPromptBuilder
{
    /**
     * Build deterministic MCP prompt for a single page
     * Auto-applies best defaults: interaction=moderate, 
     * responsiveness=fully-responsive, codeStyle=documented
     */
    public function buildForPage(array $blueprint, string $pageName): string
    {
        // Assembles deterministic MCP prompt for ONE page
        // No randomness, no decisions
        // Pure translation of blueprint → instructions
    }
    
    /**
     * Legacy method - builds prompt for all pages (deprecated)
     */
    public function buildFromBlueprint(array $blueprint): string
    {
        // For backward compatibility
    }
}
```

**BlueprintValidator.php**
```php
class BlueprintValidator
{
    public function validate(array $data): array
    {
        // JSON Schema validation
        // Cross-field dependency checks
        // Returns validated blueprint or throws
    }
}
```

**GenerationService.php**
```php
class GenerationService
{
    public function generateTemplate(Blueprint $blueprint): Generation
    {
        // Orchestrates per-page generation
        // Manages progress tracking
        // Handles errors gracefully
    }
    
    public function generatePage(Generation $generation, string $pageName): PageGeneration
    {
        // Generates single page
        // Records history
        // Updates progress
    }
}
```

**GenerationHistoryService.php**
```php
class GenerationHistoryService
{
    public function recordPage(Generation $generation, PageGenerationData $data): PageGeneration
    {
        // Records prompt, response, tokens, time
        // Updates credit estimation data
    }
    
    public function getEstimatedTokensForPage(string $pageType): int
    {
        // Returns estimated tokens based on historical data
        // Uses weighted moving average
    }
}
```

**CustomPageStatisticsService.php**
```php
class CustomPageStatisticsService
{
    public function recordCustomPage(string $pageName, string $category): void
    {
        // Normalizes and records custom page usage
    }
    
    public function getPopularCustomPages(int $limit = 20): array
    {
        // Returns most used custom pages
    }
    
    public function getCandidatesForPromotion(int $threshold = 100): array
    {
        // Returns custom pages ready to become predefined
    }
}
```

**BillingCalculator.php** (platform)
```php
class BillingCalculator
{
    public function calculateCharge(
        int $modelCredits,
        int $extraPageCredits,
        int $extraComponentCredits
    ): CreditBreakdown {
        // subtotal = model + pages + components
        // withError = subtotal × (1 + errorMargin)
        // total = CEIL(withError × (1 + profitMargin))
    }
    
    public function getErrorMargin(): float
    {
        // Returns admin-configured error margin (default 0.10)
    }
    
    public function getProfitMargin(): float
    {
        // Returns admin-configured profit margin (default 0.05)
    }
}
```

**AdminSettingsService.php** (platform)
```php
class AdminSettingsService
{
    public function getPremiumModels(): array {}
    public function getErrorMarginPercent(): float {}
    public function getProfitMarginPercent(): float {}
    public function setErrorMarginPercent(float $percent): void {}
    public function setProfitMarginPercent(float $percent): void {}
}
```

#### 3. Data Layer

**Models**
- `Blueprint`: Stores wizard selections (JSON column)
- `Generation`: Stores main generation metadata
- `PageGeneration`: Stores per-page generation history
- `CustomPageStatistic`: Tracks custom page usage
- `User`: Standard Laravel user (auth, projects, credits)
- `LlmModel`: Available LLM models and pricing
- `AdminSetting`: Admin-configurable settings

**Relationships**
- User → hasMany(Blueprint)
- Blueprint → hasOne(Generation)
- Generation → hasMany(PageGeneration)
- Generation → belongsTo(LlmModel)

**Generation Schema** (Database)
```json
{
  "id": "bigint",
  "user_id": "bigint",
  "project_id": "bigint",
  "model_used": "string",
  "credits_used": "integer",
  "status": "enum(pending,processing,completed,failed)",
  "mcp_prompt": "text (deprecated - see page_generations)",
  "progress_data": "json",
  "current_page_index": "integer",
  "total_pages": "integer",
  "current_status": "string",
  "error_message": "text",
  "processing_time": "integer",
  "started_at": "timestamp",
  "completed_at": "timestamp"
}
```

**PageGeneration Schema** (Database)
```json
{
  "id": "bigint",
  "generation_id": "bigint",
  "page_name": "string",
  "page_type": "enum(predefined,custom)",
  "mcp_prompt": "text",
  "llm_response": "text",
  "input_tokens": "integer",
  "output_tokens": "integer",
  "processing_time_ms": "integer",
  "status": "enum(pending,processing,completed,failed)",
  "error_message": "text",
  "created_at": "timestamp",
  "completed_at": "timestamp"
}
```

**CustomPageStatistic Schema** (Database)
```json
{
  "id": "bigint",
  "page_name_normalized": "string",
  "original_names": "json",
  "category": "string",
  "usage_count": "integer",
  "first_used_at": "timestamp",
  "last_used_at": "timestamp"
}
```

**AdminSetting Schema** (Database)
```json
{
  "id": "bigint",
  "key": "string (unique)",
  "value": "text",
  "type": "enum(string,integer,float,boolean,json)",
  "description": "text"
}
```

---

### Frontend (Vue.js)

#### 1. Wizard Components

**Directory Structure**
```
resources/js/wizard/
├── WizardLayout.vue             # Main wizard orchestrator
├── wizardState.ts               # Reactive state management (3 steps)
├── types.ts                     # TypeScript interfaces
├── steps/
│   ├── Step1FrameworkCategoryOutput.vue
│   ├── Step2VisualDesignContent.vue
│   └── Step3LlmModel.vue
└── components/
    ├── WizardNavigation.vue     # Back/Next buttons
    └── WizardSummary.vue        # Review before submit
```

**State Management Pattern**
```typescript
// wizardState.ts
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
    customNavItems: [],
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

export const isStepValid = computed(() => {
  // Validation logic per step (3 steps)
});

export const blueprintJSON = computed(() => {
  // Serialize state to blueprint format
});
```

#### 2. Preview Components

**TemplatePreview.vue**
- Fetches generated files from API
- Shows generation progress (per-page)
- Renders in sandboxed iframe
- Provides code view with syntax highlighting
- Download as ZIP functionality

**GenerationProgress.vue**
- Shows current page being generated
- Displays progress bar (x/total pages)
- Real-time status updates via polling/websocket

**CodeViewer.vue**
- Syntax-highlighted code display
- File tree navigation
- Copy-to-clipboard per file

#### 3. Admin Components

**CustomPageStatistics.vue**
- Table of popular custom pages
- Filter by category
- Promotion candidates highlight

**MarginSettings.vue**
- Error margin input (0-50%)
- Profit margin input (0-50%)
- Save with validation

---

## Blueprint to MCP Translation (Per Page)

### Blueprint JSON Structure (3-Step Wizard)
```json
{
  "framework": "tailwind",
  "category": "admin-dashboard",
  "outputFormat": "vue",
  "pages": ["dashboard", "users", "charts"],
  "customPages": [
    {"id": "inventory", "name": "Inventory", "description": "Stock management"}
  ],
  "layout": {
    "navigation": "sidebar",
    "sidebarDefaultState": "expanded",
    "breadcrumbs": true,
    "footer": "minimal"
  },
  "theme": {
    "primary": "#10B981",
    "secondary": "#3B82F6",
    "mode": "dark",
    "background": "solid"
  },
  "ui": {
    "density": "comfortable",
    "borderRadius": "rounded"
  },
  "components": ["buttons", "forms", "modals", "cards", "charts"],
  "chartLibrary": "chartjs",
  "interaction": "moderate",
  "responsiveness": "fully-responsive",
  "codeStyle": "documented",
  "llmModel": "gemini-2.5-flash",
  "modelCredits": 0
}
```

### MCP Prompt Output (Per Page Example - Dashboard)

```
You are an expert Vue.js developer specializing in Tailwind CSS.

PROJECT CONTEXT:
- Framework: Tailwind CSS (utility-first, responsive design)
- Template Category: Admin Dashboard
- Output Format: Vue.js 3 with Composition API
- All Pages: Dashboard, Users, Charts, Inventory (custom)

CONSTRAINTS (MUST FOLLOW):
- Use ONLY Tailwind CSS utility classes (no custom CSS)
- Use ONLY Chart.js for data visualizations
- Dark mode implementation required (CSS variables)
- No external icon libraries (use Heroicons via CDN)
- No backend logic (frontend templates only)
- All imports must be valid (no placeholders)

LAYOUT REQUIREMENTS:
- Navigation: Collapsible sidebar (expanded by default)
- Breadcrumbs: Enabled on all pages
- Footer: Minimal (copyright only)
- Sidebar items: Dashboard, Users, Charts, Inventory

THEME SPECIFICATION:
- Primary color: #10B981 (green-500)
- Secondary color: #3B82F6 (blue-500)
- Mode: Dark (default), with light mode toggle
- Background: Solid color (no gradients)

UI DENSITY:
- Spacing: Comfortable (Tailwind default scale)
- Border radius: Rounded (rounded-lg for cards, rounded-md for buttons)

COMPONENT REQUIREMENTS:
- Buttons: Primary (filled), Secondary (outline), Destructive (red)
- Forms: Text input, Select dropdown, Checkbox, Textarea
- Modals: Center-screen overlay with backdrop
- Cards: Header, body, footer sections
- Charts: Line chart, Bar chart (Chart.js integration)

INTERACTION LEVEL: Moderate
- Hover states: bg/text color shifts, opacity changes
- Transitions: 150ms ease-in-out for interactive elements

RESPONSIVENESS: Fully responsive
- Mobile (<640px): Hamburger menu, stacked layout
- Tablet (640-1024px): Collapsible sidebar, responsive grid
- Desktop (>1024px): Expanded sidebar, multi-column layout

CODE STYLE: Documented
- Clear comments explaining code sections
- JSDoc comments for functions
- TypeScript interfaces with descriptions

=== GENERATE THIS PAGE: Dashboard ===

PAGE REQUIREMENTS:
- 4 metric cards (users, revenue, orders, growth)
- Line chart (last 7 days trend)
- Recent activity table (5 rows)
- Include breadcrumbs
- Use consistent spacing (p-6 for page content)
- Implement dark mode using Tailwind dark: classes

OUTPUT FORMAT:
Generate a single Vue 3 component file.
Start with: // src/pages/Dashboard.vue
Use <script setup lang="ts"> syntax.
Include all imports.
Implement full functionality (no TODO comments).
```

**Key Properties of MCP**:
1. **Deterministic**: Same blueprint + page = same MCP, character-for-character
2. **Exhaustive**: No missing requirements (LLM has no questions)
3. **Constrained**: Explicit technology boundaries (no improvisation)
4. **Actionable**: Direct instructions, not descriptions
5. **Page-Focused**: Single page per generation call

---

## Credit Estimation Learning

### How It Works

1. **Initial Estimation**
   - Based on model pricing × estimated tokens
   - Default estimates per page type (dashboard=2000 tokens, login=500 tokens, etc.)

2. **Actual Recording**
   - Every page generation records actual input/output tokens
   - Stored in `page_generations` table

3. **Learning Algorithm**
   ```
   estimated_tokens = weighted_average(
     last_100_generations_of_same_page_type,
     weights = [0.1, 0.15, 0.2, ..., 0.3] // newer = higher weight
   )
   ```

4. **Application**
   - When calculating credits, use learned estimates
   - Show comparison: "Estimated: 12 credits (based on 87 similar generations)"

### Database Structure

```sql
CREATE TABLE credit_estimations (
    id BIGINT PRIMARY KEY,
    page_type VARCHAR(50),          -- 'login', 'dashboard', 'custom', etc.
    category VARCHAR(50),           -- 'admin-dashboard', etc.
    model_id VARCHAR(100),          -- 'gemini-2.5-flash', etc.
    avg_input_tokens INT,
    avg_output_tokens INT,
    sample_count INT,
    last_updated_at TIMESTAMP
);
```

---

## Security Considerations

### Input Validation

- **Client-Side**: UX-focused validation (instant feedback)
- **Server-Side**: Authoritative validation (Form Requests + JSON Schema)
- **Blueprint Schema**: Strict types, enums, required fields
- **Custom Page Names**: Sanitized and normalized

### LLM API Security

- **API Keys**: Stored in `.env`, never exposed client-side
- **Rate Limiting**: Laravel middleware (e.g., 10 generations/hour per user)
- **Cost Control**: Maximum token limits, request timeouts

### Preview Rendering

- **Sandboxing**: Generated code runs in iframe with restricted permissions
- **CSP Headers**: Prevent inline script execution
- **No Eval**: Never execute user-provided code directly

### Data Privacy

- **Blueprint Storage**: User-owned, not shared by default
- **Generated Templates**: Stored per user, not publicly accessible
- **LLM Requests**: No PII in MCP prompts (only structured data)
- **History**: Prompt/response history is user-owned

---

## Error Handling

### Per-Page Error Recovery

- **Single Page Failure**: Mark page as failed, continue with others
- **Retry Logic**: Automatic retry once with exponential backoff
- **Partial Success**: User can download successful pages, retry failed ones

### Wizard Submission Errors

- **Validation Failure**: Return 422 with specific field errors
- **Schema Mismatch**: Return 400 with schema violation details

### LLM API Errors

- **Timeout**: Retry once, then mark page as failed
- **Rate Limit**: Queue request for later, notify user
- **Invalid Response**: Log error, return "generation failed" message

---

## Development Workflow

### Local Development Setup

```bash
# Backend
composer install
php artisan migrate
php artisan serve

# Frontend
npm install
npm run dev

# Testing
php artisan test
npm run test
```

### Directory Structure

```
app/
├── Blueprints/            # Schema definitions
├── Http/
│   ├── Controllers/       # Thin orchestration
│   └── Requests/          # Validation logic
├── Services/
│   ├── McpPromptBuilder.php
│   ├── GenerationService.php
│   ├── GenerationHistoryService.php
│   ├── CustomPageStatisticsService.php
│   └── BillingCalculator.php
└── Models/
    ├── Generation.php
    ├── PageGeneration.php
    ├── CustomPageStatistic.php
    └── AdminSetting.php

resources/js/
├── wizard/                # 3-step wizard
│   ├── steps/
│   │   ├── Step1FrameworkCategoryOutput.vue
│   │   ├── Step2VisualDesignContent.vue
│   │   └── Step3LlmModel.vue
│   └── wizardState.ts
├── preview/               # Preview rendering
└── lib/                   # Shared utilities

tests/
├── Feature/               # End-to-end flows
└── Unit/                  # Pure functions, services
```

---

## Conclusion

This architecture ensures **deterministic, reproducible, and maintainable** template generation by:

1. **Explicit Decision Capture**: Wizard UI with 3 focused steps
2. **Zero-Ambiguity Translation**: Blueprint → MCP with no interpretation
3. **Per-Page Generation**: Better context, progress tracking, error recovery
4. **History Recording**: All prompts and responses stored for learning
5. **Credit Learning**: Estimates improve over time with real usage data
6. **Custom Page Tracking**: Popular custom pages become predefined options

The system is **not** an AI design tool. It is a **configuration-to-code translator** that happens to use an LLM for implementation efficiency.

Wizard decides. Blueprint stores. MCP instructs. LLM implements (per page). History records. Preview displays.

Simple. Deterministic. Scalable.
