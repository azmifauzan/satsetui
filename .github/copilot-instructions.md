# GitHub Copilot Instructions for SatsetUI

## Project Philosophy

SatsetUI is a **wizard-driven frontend template generator**. It is fundamentally different from prompt-to-design systems.

> **"Sat-set"** - Bahasa slang Indonesia yang berarti cepat dan efisien. SatsetUI membuat pembuatan template UI jadi sat-set!

### Core Principles

1. **No Free-Form Prompts**: Users never start with natural language prompts. All configuration happens through structured wizard steps.

2. **Deterministic Output**: Same wizard selections MUST produce identical results every time. No randomness, no creative interpretation.

3. **LLM as Implementation Engine Only**: The LLM receives a fully-formed MCP (Model Context Prompt) that contains all decisions. The LLM implements, never decides.

4. **Blueprint-First Architecture**: 
   - Wizard UI → Blueprint JSON → MCP → LLM → Preview
   - Each step is explicit and traceable

5. **Per-Page Generation**: Each page is generated separately with its own MCP prompt, enabling better context, progress tracking, and error recovery.

## Technology Stack

### ALLOWED Technologies

- **Backend**: Laravel 12
- **Frontend**: Vue.js 3 with TypeScript
- **CSS Frameworks**: Tailwind CSS 4 (for SatsetUI UI)
- **Generated Templates**: Tailwind CSS, Bootstrap, Pure CSS
- **Charts**: Chart.js, Apache ECharts ONLY
- **Build Tool**: Vite
- **Testing**: Pest (PHP), Vitest (JS/TS)
- **SPA Adapter**: Inertia.js v2

### FORBIDDEN Technologies

- ❌ React, Angular, Svelte (Vue only for SatsetUI UI)
- ❌ Other CSS frameworks for SatsetUI (no Bulma, Material, etc.)
- ❌ Drag-and-drop builders
- ❌ Visual editors
- ❌ D3.js or other chart libraries
- ❌ Prompt-based generation UI

## Wizard Structure

The wizard has exactly **3 steps**. When generating code related to wizard logic, use this structure:

1. **Step 1: Framework, Category & Output Format**
   - Select CSS framework (Tailwind/Bootstrap/Pure CSS)
   - Select template category (admin-dashboard, landing-page, etc.)
   - Select output format (HTML+CSS, React, Vue, Angular, Svelte, Custom)

2. **Step 2: Visual Design & Content**
   - Choose pages (predefined + custom)
   - Configure layout & navigation
   - Set theme & visual identity (colors, mode)
   - Configure UI density & style
   - Select components (buttons, forms, charts, etc.)

3. **Step 3: LLM Model Selection**
   - Choose AI model for generation
   - View credit breakdown with margins
   - See estimated cost

### Auto-Selected Values (NOT in wizard)

The following are auto-applied with best defaults:
- `responsiveness`: "fully-responsive"
- `interaction`: "moderate"
- `codeStyle`: "documented"

See `/docs/product-instruction.md` for complete wizard specification.

## Platform Requirements (Non-Wizard)

### CRITICAL: Always Implement These Features

#### 1. Bilingual UI (Indonesian + English)
- **MANDATORY**: ALL user-facing strings MUST use the i18n system (`@/lib/i18n.ts`)
- **DEFAULT LANGUAGE**: Indonesian (`id`) - All pages must default to Indonesian language
- **NEVER hardcode** text directly in components
- **Usage**: `const { t } = useI18n()` then access via `t.value.wizard.title`
- **New translations**: Add to both `id` and `en` objects in `i18n.ts`
- **Validation**: Every PR must have translations for both languages
- **Components must**:
  - Import `useI18n` from `@/lib/i18n`
  - Use `t.value.section.key` for all user-facing text
  - Never use literal strings like "Save", "Cancel" - always use `t.value.common.save`
  - Set default language to `id` (Indonesian) in all new components

#### 2. Theme Support (Dark/Light)
- **MANDATORY**: ALL components MUST support dark mode
- **DEFAULT THEME**: Light mode - All pages must default to light theme
- **Tailwind classes**: ALWAYS provide dark variants (e.g., `bg-white dark:bg-slate-800`)
- **Usage**: `const { theme, toggleTheme, isDark } = useTheme()`
- **Persistence**: Theme preference saved to localStorage
- **System preference**: If no preference set, default to 'light' theme (NOT system preference)
- **Components must**:
  - Import `useTheme` from `@/lib/theme` if needed
  - Use Tailwind dark: variants for ALL styling
  - Never use inline styles that don't respect theme
  - Test in both light and dark modes
  - Initialize with light theme as default

#### 3. User Layout Integration
- **Wizard pages**: Must use `AppLayout.vue` wrapper
- **Authenticated pages**: Always wrap with `<AppLayout>`
- **Sidebar navigation**: Integrated in AppLayout
- **Language switcher**: Available in AppLayout header
- **Theme toggle**: Available in AppLayout header

### Other Platform Requirements

- **Membership**:
    - Free uses Gemini 2.5 Flash (no model choice)
    - Premium can choose admin-defined models
- **Billing**: 
    - Premium uses credits
    - Credit calculation includes error margin (10% default) and profit margin (5% default)
    - Margins are admin-configurable
- **Admin Panel**: 
    - Statistics and settings
    - Custom page statistics for promotion candidates
    - Margin configuration (error %, profit %)
    - Generation history with prompts/responses

## Code Generation Rules

### When Generating Laravel Code

- **Controllers**: Keep thin. Delegate to Services.
- **Services**: Business logic lives here. Key services:
  - `McpPromptBuilder`: Core prompt generation (per-page)
  - `GenerationService`: Orchestrates per-page generation
  - `CreditService`: Credit management
  - `CostTrackingService`: LLM cost tracking
  - `AdminStatisticsService`: Admin dashboard stats
- **Validation**: Use Form Requests for wizard input validation.
- **Routes**: RESTful structure. Use route model binding where appropriate.
- **Blueprint Schema**: Must match `/app/Blueprints/template-blueprint.schema.json` exactly.

### When Generating Vue Code

- **Composition API**: Use `<script setup>` with TypeScript.
- **State Management**: Wizard state lives in `/resources/js/wizard/wizardState.ts`.
- **Components**: Small, focused, reusable.
- **Props/Emits**: Strongly typed using TypeScript interfaces.
- **Reactive State**: Use `ref` and `computed` appropriately.

### When Generating MCP Prompts

The MCP prompt structure is critical. **Prompts are generated PER PAGE**:

```
SYSTEM ROLE
│
├── PROJECT CONTEXT (from Blueprint)
│   ├── Framework choice
│   ├── Template category
│   ├── Output format
│   └── Full page list (for navigation)
│
├── CONSTRAINTS
│   ├── Technology boundaries
│   ├── Auto-selected code style (documented)
│   └── Auto-selected responsiveness (fully-responsive)
│
├── REQUIREMENTS
│   ├── Layout specification
│   ├── Theme configuration
│   ├── Component list
│   └── Auto-selected interaction level (moderate)
│
├── PAGE-SPECIFIC REQUIREMENTS
│   ├── Current page name and purpose
│   ├── Page-specific functionality
│   └── Components needed for this page
│
└── OUTPUT FORMAT
    ├── Single file output
    ├── Naming conventions
    └── Expected structure
```

Never generate MCPs that:
- Ask the LLM to "decide" or "choose"
- Include vague requirements
- Allow creative freedom
- Mix concerns
- Generate multiple pages at once (always per-page)

### When Generating Tests

- **Unit Tests**: Test pure functions and service logic.
- **Feature Tests**: Test wizard flow, blueprint generation, MCP assembly.
- **No Integration Tests**: Don't test LLM output quality (not deterministic).
- **MANDATORY**: After completing any code implementation, ALWAYS:
  1. Create corresponding test cases (Unit or Feature tests)
  2. Run the tests using `php artisan test` (for PHP) or `npm run test` (for JS/TS)
  3. Ensure all tests pass before considering the task complete
  4. Fix any failing tests immediately
- **Test Coverage Requirements**:
  - Every new Controller method must have a Feature test
  - Every new Service method must have a Unit test
  - Every new Vue component with business logic must have a Vitest test
  - Tests must cover both success and error scenarios

## File Organization

```
app/
├── Blueprints/           # Schema definitions
├── Http/
│   ├── Controllers/      # Wizard controllers
│   │   ├── Admin/        # Admin panel controllers
│   │   └── Auth/         # Authentication controllers
│   └── Requests/         # Wizard validation
├── Services/
│   ├── McpPromptBuilder.php         # Per-page MCP generation
│   ├── GenerationService.php        # Generation orchestration
│   ├── CreditService.php            # Credit management
│   ├── CreditEstimationService.php  # Token estimation
│   ├── CostTrackingService.php      # Cost tracking
│   ├── AdminStatisticsService.php   # Admin stats
│   ├── GeminiService.php            # Gemini API
│   └── OpenAICompatibleService.php  # OpenAI API
└── Models/
    ├── User.php
    ├── Generation.php
    ├── PageGeneration.php
    ├── LlmModel.php
    ├── AdminSetting.php
    ├── CreditTransaction.php
    ├── CreditEstimation.php
    ├── CustomPageStatistic.php
    └── GenerationCost.php

resources/js/
├── pages/                # Inertia pages
│   ├── Home.vue
│   ├── Auth/
│   ├── Dashboard/
│   ├── Wizard/
│   ├── Generation/
│   ├── Templates/
│   └── Admin/
├── wizard/               # Wizard-specific components
│   ├── steps/
│   │   ├── Step1FrameworkCategoryOutput.vue
│   │   ├── Step2VisualDesignContent.vue
│   │   └── Step3LlmModel.vue
│   ├── wizardState.ts    # Central state management
│   └── types.ts          # TypeScript interfaces
├── components/           # Shared components
├── layouts/              # Layout wrappers
└── lib/                  # Utilities (i18n, theme)

docs/
├── product-instruction.md  # 3-step wizard spec
├── architecture.md         # Per-page generation
├── mvp-plan.md
├── llm-credit-system.md    # Credit calculation
└── admin-panel-architecture.md
```

## Behavior Guidelines for Copilot

### DO

- ✅ Reference wizard step numbers explicitly (e.g., "Step 2: Visual Design & Content")
- ✅ Validate against blueprint schema
- ✅ Generate deterministic MCP prompts (per page)
- ✅ Add reasoning comments in McpPromptBuilder
- ✅ Create strongly-typed TypeScript interfaces
- ✅ Follow Laravel best practices (Service layer, Form Requests)
- ✅ Use Tailwind/Bootstrap utility classes appropriately
- ✅ **ALWAYS implement bilingual support** using `useI18n()` - no hardcoded strings
- ✅ **ALWAYS add dark mode support** using Tailwind `dark:` variants
- ✅ **ALWAYS wrap authenticated pages** with `AppLayout.vue`
- ✅ **ALWAYS generate per-page** - never all pages in one MCP
- ✅ **ALWAYS record history** - prompts and responses stored

### DO NOT

- ❌ Suggest free-form prompt input fields
- ❌ Recommend AI-powered design suggestions
- ❌ Add "smart defaults" that change based on context
- ❌ Propose drag-and-drop UI builders
- ❌ **NEVER hardcode user-facing text** - always use i18n
- ❌ **NEVER omit dark mode variants** - all styling must support dark theme
- ❌ **NEVER create authenticated pages without AppLayout** wrapper
- ❌ Mix presentation and business logic
- ❌ Use inline styles instead of CSS framework utilities
- ❌ Generate code that requires manual LLM review
- ❌ **NEVER generate multiple pages in one LLM call**

## Example Scenarios

### Scenario 1: User asks to "add a new wizard step"

**Correct Response**:
- Explain that wizard is fixed at 3 steps
- Suggest adding the feature to existing step (Step 2 for content, Step 1 for format)
- If absolutely needed, update all documentation:
  1. Update `/docs/product-instruction.md`
  2. Update `/app/Blueprints/template-blueprint.schema.json`
  3. Update `/resources/js/wizard/wizardState.ts`
  4. Create/update Vue component in `steps/`
  5. Update McpPromptBuilder
  6. Update tests

### Scenario 2: User asks to "generate all pages at once"

**Correct Response**:
- Explain that per-page generation is a core architecture decision
- Benefits: better context, progress tracking, error recovery
- All prompts and responses are recorded for credit learning
- Cannot change to batch generation

### Scenario 3: User asks about credit calculation

**Correct Response**:
- Explain the formula with margins:
  ```
  subtotal = model + extraPages + extraComponents
  total = CEIL(subtotal × (1 + errorMargin) × (1 + profitMargin))
  ```
- Error margin: 10% default (admin configurable)
- Profit margin: 5% default (admin configurable)
- All calculations done in `CreditService.php`

## Testing Strategy

### Must Test

- Wizard state transitions (3 steps)
- Blueprint validation
- Per-page MCP prompt assembly
- Credit calculation with margins
- History recording

### Don't Test

- LLM output quality (external system)
- Subjective design quality
- Cross-browser rendering (preview is informational only)

## Performance Considerations

- Wizard state should be client-side until final submission
- Blueprint generation is synchronous (fast)
- MCP generation is synchronous (fast, per page)
- LLM call is async (slow, ~30s per page) - handle with progress tracking
- Cache generated templates per Blueprint hash

## Security Notes

- Sanitize all wizard inputs (even though structured)
- Validate Blueprint against JSON schema server-side
- Rate-limit LLM API calls
- Never expose LLM API keys client-side
- User-generated templates should be sandboxed for preview
- Custom page names are normalized before storage

## References

- Full wizard specification: `/docs/product-instruction.md`
- Architecture overview: `/docs/architecture.md`
- Blueprint schema: `/app/Blueprints/template-blueprint.schema.json`
- MVP roadmap: `/docs/mvp-plan.md`
- Credit system: `/docs/llm-credit-system.md`
- Admin panel: `/docs/admin-panel-architecture.md`

---

## Laravel Boost Guidelines

### Foundational Context
This application is a Laravel application with the following key packages:

- php - 8.4
- inertiajs/inertia-laravel - v2
- laravel/framework - v12
- laravel/pint - v1
- pestphp/pest - v4
- @inertiajs/vue3 - v2
- tailwindcss - v4
- vue - v3

### Conventions
- Follow existing code conventions. Check sibling files for correct structure.
- Use descriptive names for variables and methods.
- Check for existing components to reuse before writing new ones.

### Application Structure
- Stick to existing directory structure.
- Do not change dependencies without approval.

### PHP Rules
- Use PHP 8 constructor property promotion.
- Always use explicit return type declarations.
- Use curly braces for control structures, even for single lines.

### Test Rules
- Every change must be tested. Write or update tests.
- Run minimum tests needed with `php artisan test --filter=`.
- Use Pest for all PHP tests.

### Inertia Rules
- Use `Inertia::render()` for routing.
- Use `useForm` for forms with proper error handling.
- Components live in `resources/js/pages`.

### Wayfinder Rules
- Use generated TypeScript functions for routes.
- Prefer named imports for tree-shaking.

---

## Sat-set! 🚀

SatsetUI is about speed and efficiency. Every feature should help users generate templates quickly and reliably.
