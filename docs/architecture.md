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
- Premium credit billing with configurable markup
- Admin panel for statistics and configuration

---

## Technology Stack

### Backend (Laravel)

- **Framework**: Laravel 12.x
- **PHP Version**: 8.2+
- **Database**: MySQL/PostgreSQL (for storing Blueprints, Projects)
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
- **UI Framework**: Tailwind CSS for the generator UI; generated templates support Tailwind CSS or Bootstrap

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
- **Billing**: Premium credits + cost accounting per generation
- **Admin**: Configuration (available models, markup) + statistics

---

## Data Flow Architecture

### Complete Request Flow

```
┌─────────────────────────────────────────────────────────────┐
│ 1. USER INTERACTION (Vue.js Frontend)                       │
│                                                              │
│  Wizard UI (11 steps)                                       │
│  - Step components capture structured inputs                │
│  - wizardState.ts holds reactive state                      │
│  - Validation happens client-side (UX)                      │
│  - Submit triggers Inertia.js POST                          │
└──────────────────────┬───────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│ 2. LARAVEL BACKEND (Validation & Persistence)               │
│                                                              │
│  Route: POST /api/blueprint                                 │
│  Controller: BlueprintController@store                      │
│  ├─ Form Request validates structure                        │
│  ├─ JSON Schema validator confirms blueprint format         │
│  ├─ Store Blueprint model (database)                        │
│  └─ Return Blueprint ID + validated data                    │
└──────────────────────┬───────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│ 3. MCP PROMPT BUILDER (Core Logic)                          │
│                                                              │
│  Service: McpPromptBuilder::buildFromBlueprint()            │
│  ├─ Load Blueprint JSON                                     │
│  ├─ Assemble prompt sections:                               │
│  │   ├─ ROLE: Define LLM expertise                          │
│  │   ├─ CONTEXT: Framework, category, pages                 │
│  │   ├─ CONSTRAINTS: Technology limits, no decisions        │
│  │   ├─ REQUIREMENTS: Layout, theme, components             │
│  │   ├─ CODE STYLE: Based on Step 10 preferences            │
│  │   └─ OUTPUT FORMAT: File structure, naming conventions   │
│  └─ Return deterministic MCP string                         │
└──────────────────────┬───────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│ 4. MODEL SELECTION + BILLING (Platform Logic)               │
│                                                              │
│  Service: ModelSelector + BillingCalculator                  │
│  ├─ Determine membership tier (free/premium)                 │
│  ├─ Free: force Gemini Flash                                 │
│  ├─ Premium: allow admin-defined model choices               │
│  ├─ Estimate base cost (tokens * admin price)                │
│  ├─ Apply markup percentage (admin setting)                  │
│  ├─ Validate premium credit balance                          │
│  └─ Reserve/charge credits atomically                        │
└──────────────────────┬───────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│ 5. LLM API CALL (External Service)                          │
│                                                              │
│  Service: LlmService::generate()                            │
│  ├─ Send MCP prompt to configured LLM                       │
│  ├─ Parse response (code blocks, file structure)            │
│  ├─ Validate output format                                  │
│  └─ Return structured code result                           │
└──────────────────────┬───────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│ 6. CODE STORAGE & PROCESSING                                │
│                                                              │
│  Service: TemplateProcessor::process()                      │
│  ├─ Extract individual files from LLM response              │
│  ├─ Store in filesystem (storage/templates/{blueprint_id})  │
│  ├─ Update Blueprint model with file references             │
│  └─ Generate preview metadata                               │
└──────────────────────┬───────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│ 7. PREVIEW RENDERER (Vue.js Frontend)                       │
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
- `TemplateController`: Generate, preview, download templates
- `WizardController`: Serve wizard UI (Inertia)

**Form Requests** (Validation)
- `StoreBlueprintRequest`: Validates wizard inputs against rules
- `GenerateTemplateRequest`: Validates generation parameters

**API Resources** (Response formatting)
- `BlueprintResource`: Format blueprint JSON for frontend
- `TemplateResource`: Format generated template metadata

#### 2. Service Layer (Business Logic)

**McpPromptBuilder.php** (Core Service)
```php
class McpPromptBuilder
{
    public function buildFromBlueprint(array $blueprint): string
    {
        // Assembles deterministic MCP prompt
        // No randomness, no decisions
        // Pure translation of blueprint → instructions
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

**LlmService.php**
```php
class LlmService
{
  public function generate(string $mcpPrompt): LlmResponse
    {
        // HTTP call to LLM API
        // Retry logic, error handling
        // Response parsing
    }
}

**ModelSelector.php** (platform)
```php
class ModelSelector
{
  public function selectForUser(User $user, ?string $requestedModel): SelectedModel
  {
    // Free: force Gemini Flash
    // Premium: validate requested model is in admin-configured allow-list
    // Return provider+model identifier
  }
}
```

**BillingCalculator.php** (platform)
```php
class BillingCalculator
{
  public function calculateCharge(SelectedModel $model, Usage $usage, Markup $markup): Money
  {
    // base = tokensIn/out * pricePerToken
    // charged = base + (base * markupPercent)
    // charged amount reduces premium credits
  }
}
```

**AdminSettingsService.php** (platform)
```php
class AdminSettingsService
{
  public function getPremiumModels(): array {}
  public function getMarkupPercent(): float {}
}
```
```

**TemplateProcessor.php**
```php
class TemplateProcessor
{
    public function process(LlmResponse $response, Blueprint $blueprint): Template
    {
        // Extract files from LLM response
        // Validate file structure
        // Store to filesystem
        // Return Template model
    }
}
```

#### 3. Data Layer

**Models**
- `Blueprint`: Stores wizard selections (JSON column)
- `Template`: Stores generated template metadata
- `User`: Standard Laravel user (auth, projects)

**Relationships**
- User → hasMany(Blueprint)
- Blueprint → hasOne(Template)

**Blueprint Schema** (Database JSON column structure)
```json
{
  "id": "uuid",
  "user_id": "int",
  "name": "string",
  "data": {
    "framework": "string",
    "category": "string",
    "pages": ["array"],
    "layout": {"object"},
    "theme": {"object"},
    "ui": {"object"},
    "components": ["array"],
    "interaction": "string",
    "responsiveness": "string",
    "codeStyle": "string",
    "outputIntent": "string"
  },
  "created_at": "timestamp",
  "updated_at": "timestamp"
}
```

---

### Frontend (Vue.js)

#### 1. Wizard Components

**Directory Structure**
```
resources/js/wizard/
├── WizardContainer.vue          # Main wizard orchestrator
├── wizardState.ts               # Reactive state management
├── types.ts                     # TypeScript interfaces
├── steps/
│   ├── Step1Framework.vue
│   ├── Step2Category.vue
│   ├── Step3Pages.vue
│   ├── Step4Layout.vue
│   ├── Step5Theme.vue
│   ├── Step6Density.vue
│   ├── Step7Components.vue
│   ├── Step8Interaction.vue
│   ├── Step9Responsiveness.vue
│   ├── Step10CodeStyle.vue
│   └── Step11Output.vue
└── components/
    ├── WizardProgress.vue       # Step indicator
    ├── WizardNavigation.vue     # Back/Next buttons
    └── WizardSummary.vue        # Review before submit
```

**State Management Pattern**
```typescript
// wizardState.ts
import { reactive, computed } from 'vue';

export const wizardState = reactive({
  currentStep: 1,
  framework: 'tailwind',
  category: 'admin-dashboard',
  pages: ['dashboard'],
  layout: {
    navigation: 'sidebar',
    sidebarDefaultState: 'expanded',
    breadcrumbs: true,
    footer: 'minimal'
  },
  theme: {
    primary: '#3B82F6',
    secondary: '#6366F1',
    mode: 'light',
    background: 'solid'
  },
  // ... all 11 steps
});

#### Internationalization (Generator UI)

The generator UI is bilingual:
- All user-facing strings in the wizard and admin/billing UI must use translation keys.
- Language preference should be stored per user.

#### Dark/Light Theme (Generator UI)

The generator UI supports dark/light theme toggling independent of template generation.

export const isStepValid = computed(() => {
  // Validation logic per step
});

export const blueprintJSON = computed(() => {
  // Serialize state to blueprint format
});
```

#### 2. Preview Components

**TemplatePreview.vue**
- Fetches generated files from API
- Renders in sandboxed iframe
- Provides code view with syntax highlighting
- Download as ZIP functionality

**CodeViewer.vue**
- Syntax-highlighted code display
- File tree navigation
- Copy-to-clipboard per file

#### 3. Shared Components

**UI Elements** (styled with Tailwind)
- `Button.vue`: Reusable button component
- `Input.vue`: Form input wrapper
- `Select.vue`: Dropdown selector
- `ColorPicker.vue`: Color selection (Step 5)
- `Card.vue`: Content container

---

## Blueprint to MCP Translation

### Blueprint JSON Structure

```json
{
  "framework": "tailwind",
  "category": "admin-dashboard",
  "pages": ["dashboard", "users", "charts"],
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
  "codeStyle": "minimal",
  "outputIntent": "production"
}
```

### MCP Prompt Output

```
You are an expert Vue.js developer specializing in Tailwind CSS.

PROJECT CONTEXT:
- Framework: Tailwind CSS (utility-first, responsive design)
- Template Category: Admin Dashboard
- Target Pages: Dashboard, User Management, Charts/Analytics

CONSTRAINTS:
- Use ONLY Tailwind CSS utility classes (no custom CSS)
- Use ONLY Chart.js for data visualizations
- Dark mode implementation required (CSS variables)
- No external icon libraries (use Heroicons via CDN)

LAYOUT REQUIREMENTS:
- Navigation: Collapsible sidebar (expanded by default)
- Breadcrumbs: Enabled on all pages
- Footer: Minimal (copyright only)
- Sidebar items: Dashboard, Users, Charts, Settings

THEME SPECIFICATION:
- Primary color: #10B981 (green-500)
- Secondary color: #3B82F6 (blue-500)
- Mode: Dark (default), with light mode toggle
- Background: Solid color (no gradients)

UI DENSITY:
- Spacing: Comfortable (Tailwind default scale)
- Border radius: Rounded (rounded-lg for cards, rounded-md for buttons)
- Font size: Base (text-base for body, text-sm for secondary)

COMPONENT REQUIREMENTS:
- Buttons: Primary (filled), Secondary (outline), Destructive (red)
- Forms: Text input, Select dropdown, Checkbox, Textarea
- Modals: Center-screen overlay with backdrop
- Cards: Header, body, footer sections
- Charts: Line chart, Bar chart (Chart.js integration)

INTERACTION LEVEL: Moderate
- Hover states: bg/text color shifts, opacity changes
- Transitions: 150ms ease-in-out for interactive elements
- No complex animations, no parallax, no loading skeletons

RESPONSIVENESS: Fully responsive
- Mobile (<640px): Hamburger menu, stacked layout
- Tablet (640-1024px): Collapsible sidebar, responsive grid
- Desktop (>1024px): Expanded sidebar, multi-column layout

CODE STYLE: Minimal
- Concise variable names
- No comments unless complex logic
- Use Vue Composition API (<script setup>)
- TypeScript interfaces for props

OUTPUT FORMAT:
Generate the following file structure:

src/
├── pages/
│   ├── Dashboard.vue
│   ├── Users.vue
│   └── Charts.vue
├── components/
│   ├── Sidebar.vue
│   ├── Topbar.vue
│   ├── Button.vue
│   ├── Card.vue
│   └── Modal.vue
├── composables/
│   └── useTheme.ts
└── types/
    └── index.ts

For each file:
1. Start with filename comment: // src/pages/Dashboard.vue
2. Implement full functionality (no TODO comments)
3. Include all imports
4. Export as default

Dashboard page must include:
- 4 metric cards (users, revenue, orders, growth)
- Line chart (last 7 days trend)
- Recent activity table (5 rows)

Users page must include:
- Data table with columns: Avatar, Name, Email, Role, Status, Actions
- Search filter
- Add user button (opens modal)
- Edit/Delete actions

Charts page must include:
- Line chart (revenue over time)
- Bar chart (products by category)
- Doughnut chart (traffic sources)

All pages must:
- Include breadcrumbs
- Use consistent spacing (p-6 for page content)
- Implement dark mode using Tailwind dark: classes

Begin output with Dashboard.vue:
```

**Key Properties of MCP**:
1. **Deterministic**: Same blueprint = same MCP, character-for-character
2. **Exhaustive**: No missing requirements (LLM has no questions)
3. **Constrained**: Explicit technology boundaries (no improvisation)
4. **Actionable**: Direct instructions, not descriptions

---

## Why This Architecture Works

### 1. Separation of Concerns

| Layer | Responsibility | Decision Authority |
|-------|----------------|-------------------|
| Wizard UI | Capture user choices | User |
| Blueprint | Store structured data | None (data only) |
| MCP Builder | Translate to instructions | None (logic only) |
| LLM | Implement code | None (follows instructions) |

No layer makes subjective decisions. The wizard is the only source of creative input.

### 2. Determinism Guarantees

**Wizard State** → Immutable after submission
**Blueprint JSON** → Validated against schema
**MCP Prompt** → Pure function of Blueprint (no external state)
**LLM Output** → Seeded if API supports (for reproducibility)

Test: `blueprint_hash(A) === blueprint_hash(B)` implies `output(A) === output(B)`

### 3. Testability

**Unit Tests**:
- Each wizard step validation
- Blueprint schema validation
- MCP builder prompt assembly

**Feature Tests**:
- Complete wizard submission flow
- Blueprint → MCP conversion
- LLM response parsing

**Integration Tests** (limited):
- LLM API connectivity (not output quality)

**Not Tested**:
- Subjective design quality (not measurable)
- LLM output correctness (external dependency)

### 4. Scalability

**Horizontal Scaling**:
- Stateless API (Laravel)
- Blueprint generation is fast (no LLM call)
- LLM calls can be queued (async jobs)
- Preview rendering is client-side

**Performance Bottlenecks**:
- LLM API latency (5-30 seconds per generation)
- Filesystem I/O for large templates

**Mitigation**:
- Cache generated templates by Blueprint hash
- Lazy-load preview (only when user views)
- Rate-limit generation requests per user

### 5. Extensibility

**Adding a New Wizard Step**:
1. Update `/docs/product-instruction.md`
2. Add field to `template-blueprint.schema.json`
3. Create Vue component in `resources/js/wizard/steps/`
4. Update `wizardState.ts`
5. Modify `McpPromptBuilder.php` to use new field
6. Add validation rules
7. Update tests

**Adding a New Framework** (e.g., Bulma):
1. Add to Step 1 options
2. Update Blueprint schema enum
3. Add Bulma-specific MCP templates in McpPromptBuilder
4. Update documentation

**Adding a New LLM Provider**:
1. Create adapter class (implements `LlmInterface`)
2. Add to `config/services.php`
3. No changes to MCP builder (provider-agnostic)

---

## Security Considerations

### Input Validation

- **Client-Side**: UX-focused validation (instant feedback)
- **Server-Side**: Authoritative validation (Form Requests + JSON Schema)
- **Blueprint Schema**: Strict types, enums, required fields

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

---

## Error Handling

### Wizard Submission Errors

- **Validation Failure**: Return 422 with specific field errors
- **Schema Mismatch**: Return 400 with schema violation details

### LLM API Errors

- **Timeout**: Retry once, then fail gracefully with error message
- **Rate Limit**: Queue request for later, notify user
- **Invalid Response**: Log error, return "generation failed" message

### Preview Rendering Errors

- **Malformed Code**: Show syntax error overlay, allow code download anyway
- **Missing Files**: Display warning, show available files

### General Error Strategy

- **User-Facing**: Clear, actionable error messages (no stack traces)
- **Logging**: All errors logged to `storage/logs/laravel.log`
- **Monitoring**: (Future) Integration with Sentry/Bugsnag

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

### Directory Structure for New Features

```
app/
├── Services/              # Business logic (add new services here)
├── Blueprints/            # Schema definitions
└── Http/
    ├── Controllers/       # Thin orchestration
    └── Requests/          # Validation logic

resources/js/
├── wizard/                # Wizard-specific components
│   ├── steps/             # One component per wizard step
│   └── wizardState.ts     # Central state (update for new steps)
├── preview/               # Preview rendering
└── lib/                   # Shared utilities

tests/
├── Feature/               # End-to-end flows
└── Unit/                  # Pure functions, services
```

---

## Deployment Considerations

### Production Environment

- **Web Server**: Nginx + PHP-FPM
- **Queue Worker**: Laravel Horizon (for async LLM calls)
- **Database**: PostgreSQL (JSONB support for blueprints)
- **Storage**: S3 or similar (generated templates)
- **CDN**: CloudFront for static assets

### Environment Variables

```env
# LLM API
LLM_PROVIDER=openai          # openai | anthropic
OPENAI_API_KEY=sk-...
ANTHROPIC_API_KEY=sk-...

# Rate Limiting
GENERATION_RATE_LIMIT=10     # per hour per user

# Storage
FILESYSTEM_DISK=s3           # local | s3
AWS_BUCKET=templates-bucket
```

### Performance Optimization

- **Blueprint Caching**: Cache identical blueprints by hash
- **MCP Caching**: Cache prompts per blueprint (rebuild if schema changes)
- **Static Assets**: Vite build optimization
- **Database Indexing**: Index `blueprints.user_id`, `templates.blueprint_id`

---

## Conclusion

This architecture ensures **deterministic, reproducible, and maintainable** template generation by:

1. **Explicit Decision Capture**: Wizard UI with 11 structured steps
2. **Zero-Ambiguity Translation**: Blueprint → MCP with no interpretation
3. **LLM as Tool**: Implementation only, no creative authority
4. **Separation of Concerns**: Each layer has single, clear responsibility
5. **Testability**: Pure functions, validatable schemas, mockable LLM

The system is **not** an AI design tool. It is a **configuration-to-code translator** that happens to use an LLM for implementation efficiency.

Wizard decides. Blueprint stores. MCP instructs. LLM implements. Preview displays.

Simple. Deterministic. Scalable.
