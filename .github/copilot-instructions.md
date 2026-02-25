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

- **Credits & Models**:
    - All users start with 100 credits at registration
    - 2 model types: **Satset** (6 credits, fast) and **Expert** (15 credits, premium)
    - Model name, provider, API key, base URL admin-configurable per model type
    - Credit calculation includes error margin (10% default) and profit margin (5% default)
    - Margins are admin-configurable
- **Admin Panel**: 
    - Statistics and settings
    - Custom page statistics for promotion candidates
    - Satset/Expert model configuration (provider, model name, API key, base URL, credits)
    - Margin configuration (error %, profit %)
    - Generation history with prompts/responses
- **Live Preview**:
    - Server-side workspace with Vite dev server for JS framework output
    - Static HTML preview via iframe for HTML+CSS output
    - Device switcher (desktop/tablet/mobile)
    - WorkspaceService manages lifecycle, PreviewController handles endpoints

## Code Generation Rules

### When Generating Laravel Code

- **Controllers**: Keep thin. Delegate to Services.
- **Services**: Business logic lives here. Key services:
  - `McpPromptBuilder`: Core prompt generation (per-page)
  - `GenerationService`: Orchestrates per-page generation with retry & context
  - `CreditService`: Credit management (charge, refund, admin adjustment)
  - `CreditEstimationService`: Token estimation with historical learning
  - `CostTrackingService`: LLM cost tracking (USD + IDR)
  - `AdminStatisticsService`: Admin dashboard stats
  - `WorkspaceService`: Live preview workspace lifecycle
  - `ScaffoldGeneratorService`: Deterministic framework scaffolding
  - `OpenAICompatibleService`: Primary LLM API gateway
  - `TelegramService`: Telegram bot messaging
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
│   │   │   ├── DashboardController.php
│   │   │   ├── UserManagementController.php
│   │   │   ├── LlmModelController.php
│   │   │   ├── SettingsController.php
│   │   │   └── GenerationHistoryController.php
│   │   └── Auth/         # Authentication controllers
│   ├── Middleware/
│   │   ├── AdminMiddleware.php
│   │   └── HandleInertiaRequests.php
│   └── Requests/         # Wizard validation
├── Jobs/
│   └── ProcessTemplateGeneration.php  # Background generation (30min timeout)
├── Services/
│   ├── McpPromptBuilder.php         # Per-page MCP generation
│   ├── GenerationService.php        # Generation orchestration
│   ├── CreditService.php            # Credit management
│   ├── CreditEstimationService.php  # Token estimation
│   ├── CostTrackingService.php      # Cost tracking
│   ├── AdminStatisticsService.php   # Admin stats
│   ├── GeminiService.php            # Gemini API (legacy)
│   ├── OpenAICompatibleService.php  # OpenAI-compatible API
│   ├── WorkspaceService.php         # Live preview workspace management
│   ├── ScaffoldGeneratorService.php # Framework project scaffolding
│   └── TelegramService.php         # Telegram bot messaging
└── Models/
    ├── User.php
    ├── Generation.php
    ├── PageGeneration.php
    ├── GenerationFile.php     # Multi-file generation output
    ├── GenerationCost.php
    ├── GenerationFailure.php
    ├── LlmModel.php           # 2 types: satset, expert
    ├── AdminSetting.php
    ├── CreditTransaction.php
    ├── CreditEstimation.php
    ├── CustomPageStatistic.php
    ├── PreviewSession.php     # Live preview lifecycle
    ├── RefinementMessage.php  # Chat refinement messages
    ├── Project.php
    └── Template.php

resources/js/
├── pages/                # Inertia pages
│   ├── Home.vue
│   ├── Auth/             # Login, Register, VerifyEmail
│   ├── Dashboard/
│   ├── Wizard/
│   ├── Generation/
│   ├── Templates/
│   └── Admin/            # Dashboard, Users, Models, Generations, Settings
├── wizard/               # Wizard-specific components
│   ├── steps/
│   │   ├── Step1FrameworkCategoryOutput.vue
│   │   ├── Step2VisualDesignContent.vue
│   │   └── Step3LlmModel.vue
│   ├── wizardState.ts    # Central state management
│   └── types.ts          # TypeScript interfaces
├── components/           # Shared components
│   ├── admin/            # StatCard
│   ├── dashboard/        # Card, StatCard
│   ├── generation/       # LivePreview, FileTree
│   └── landing/          # Navbar, Hero, Features, HowItWorks, FAQ, CTA, Footer
├── layouts/
│   ├── AppLayout.vue     # Authenticated layout with sidebar
│   └── AdminLayout.vue   # Admin panel layout
└── lib/
    ├── i18n.ts           # Internationalization
    ├── i18n/             # Translation files (en/, id/)
    ├── theme.ts          # Theme management
    └── utils.ts          # Utility functions

docs/
├── product-instruction.md  # 3-step wizard spec
├── architecture.md         # Per-page generation + live preview
├── mvp-plan.md
├── llm-credit-system.md    # Credit calculation
├── admin-panel-architecture.md
├── plan-js-framework-output-live-preview.md  # JS framework & live preview
├── credit-refund-and-cost-tracking.md
├── llm-quick-reference.md
├── CHANGELOG.md
├── ADMIN-IMPLEMENTATION.md
├── EMAIL-TELEGRAM-SETUP.md
└── TROUBLESHOOTING-419.md
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

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context
This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4.11
- inertiajs/inertia-laravel (INERTIA) - v2
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- laravel/wayfinder (WAYFINDER) - v0
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- @inertiajs/vue3 (INERTIA) - v2
- tailwindcss (TAILWINDCSS) - v4
- vue (VUE) - v3
- @laravel/vite-plugin-wayfinder (WAYFINDER) - v0
- eslint (ESLINT) - v9
- prettier (PRETTIER) - v3

## Conventions
- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts
- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture
- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling
- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies
- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files
- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

## Laravel Boost
- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan
- Use the `list-artisan-commands` tool when you need to call an Artisan command to double-check the available parameters.

## URLs
- Whenever you share a project URL with the user, you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain/IP, and port.

## Tinker / Debugging
- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool
- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)
- Boost comes with a powerful `search-docs` tool you should use before any other approaches when dealing with Laravel or Laravel ecosystem packages. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The `search-docs` tool is perfect for all Laravel-related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic-based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries; package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax
- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'.
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit".
3. Quoted Phrases (Exact Position) - query="infinite scroll" - words must be adjacent and in that order.
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit".
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms.

=== php rules ===

## PHP

- Always use curly braces for control structures, even if it has one line.

### Constructors
- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters unless the constructor is private.

### Type Declarations
- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments
- Prefer PHPDoc blocks over inline comments. Never use comments within the code itself unless there is something very complex going on.

## PHPDoc Blocks
- Add useful array shape type definitions for arrays when appropriate.

## Enums
- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

=== tests rules ===

## Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== inertia-laravel/core rules ===

## Inertia

- Inertia.js components should be placed in the `resources/js/Pages` directory unless specified differently in the JS bundler (`vite.config.js`).
- Use `Inertia::render()` for server-side routing instead of traditional Blade views.
- Use the `search-docs` tool for accurate guidance on all things Inertia.

<code-snippet name="Inertia Render Example" lang="php">
// routes/web.php example
Route::get('/users', function () {
    return Inertia::render('Users/Index', [
        'users' => User::all()
    ]);
});
</code-snippet>

=== inertia-laravel/v2 rules ===

## Inertia v2

- Make use of all Inertia features from v1 and v2. Check the documentation before making any changes to ensure we are taking the correct approach.

### Inertia v2 New Features
- Deferred props.
- Infinite scrolling using merging props and `WhenVisible`.
- Lazy loading data on scroll.
- Polling.
- Prefetching.

### Deferred Props & Empty States
- When using deferred props on the frontend, you should add a nice empty state with pulsing/animated skeleton.

### Inertia Form General Guidance
- Build forms using the `useForm` helper. Use the code examples and the `search-docs` tool with a query of `useForm helper` for guidance.

=== laravel/core rules ===

## Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database
- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries.
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation
- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources
- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation
- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues
- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization
- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation
- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration
- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing
- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error
- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

## Laravel 12

- Use the `search-docs` tool to get version-specific documentation.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

### Laravel 12 Structure
- In Laravel 12, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app\Console\Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

### Database
- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models
- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== wayfinder/core rules ===

## Laravel Wayfinder

Wayfinder generates TypeScript functions and types for Laravel controllers and routes which you can import into your client-side code. It provides type safety and automatic synchronization between backend routes and frontend code.

### Development Guidelines
- Always use the `search-docs` tool to check Wayfinder correct usage before implementing any features.
- Always prefer named imports for tree-shaking (e.g., `import { show } from '@/actions/...'`).
- Avoid default controller imports (prevents tree-shaking).
- Run `php artisan wayfinder:generate` after route changes if Vite plugin isn't installed.

### Feature Overview
- Form Support: Use `.form()` with `--with-form` flag for HTML form attributes — `<form {...store.form()}>` → `action="/posts" method="post"`.
- HTTP Methods: Call `.get()`, `.post()`, `.patch()`, `.put()`, `.delete()` for specific methods — `show.head(1)` → `{ url: "/posts/1", method: "head" }`.
- Invokable Controllers: Import and invoke directly as functions. For example, `import StorePost from '@/actions/.../StorePostController'; StorePost()`.
- Named Routes: Import from `@/routes/` for non-controller routes. For example, `import { show } from '@/routes/post'; show(1)` for route name `post.show`.
- Parameter Binding: Detects route keys (e.g., `{post:slug}`) and accepts matching object properties — `show("my-post")` or `show({ slug: "my-post" })`.
- Query Merging: Use `mergeQuery` to merge with `window.location.search`, set values to `null` to remove — `show(1, { mergeQuery: { page: 2, sort: null } })`.
- Query Parameters: Pass `{ query: {...} }` in options to append params — `show(1, { query: { page: 1 } })` → `"/posts/1?page=1"`.
- Route Objects: Functions return `{ url, method }` shaped objects — `show(1)` → `{ url: "/posts/1", method: "get" }`.
- URL Extraction: Use `.url()` to get URL string — `show.url(1)` → `"/posts/1"`.

### Example Usage

<code-snippet name="Wayfinder Basic Usage" lang="typescript">
    // Import controller methods (tree-shakable)...
    import { show, store, update } from '@/actions/App/Http/Controllers/PostController'

    // Get route object with URL and method...
    show(1) // { url: "/posts/1", method: "get" }

    // Get just the URL...
    show.url(1) // "/posts/1"

    // Use specific HTTP methods...
    show.get(1) // { url: "/posts/1", method: "get" }
    show.head(1) // { url: "/posts/1", method: "head" }

    // Import named routes...
    import { show as postShow } from '@/routes/post' // For route name 'post.show'
    postShow(1) // { url: "/posts/1", method: "get" }
</code-snippet>

### Wayfinder + Inertia
If your application uses the `useForm` component from Inertia, you can directly submit to the Wayfinder generated functions.

<code-snippet name="Wayfinder useForm Example" lang="typescript">
    import { store } from "@/actions/App/Http/Controllers/ExampleController";

    const form = useForm({
        name: "My Big Post",
    });

    form.submit(store());
</code-snippet>

=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.

=== pest/core rules ===

## Pest
### Testing
- If you need to verify a feature is working, write or update a Unit / Feature test.

### Pest Tests
- All tests must be written using Pest. Use `php artisan make:test --pest {name}`.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files - these are core to the application.
- Tests should test all of the happy paths, failure paths, and weird paths.
- Tests live in the `tests/Feature` and `tests/Unit` directories.
- Pest tests look and behave like this:
<code-snippet name="Basic Pest Test Example" lang="php">
it('is true', function () {
    expect(true)->toBeTrue();
});
</code-snippet>

### Running Tests
- Run the minimal number of tests using an appropriate filter before finalizing code edits.
- To run all tests: `php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).
- When the tests relating to your changes are passing, ask the user if they would like to run the entire test suite to ensure everything is still passing.

### Pest Assertions
- When asserting status codes on a response, use the specific method like `assertForbidden` and `assertNotFound` instead of using `assertStatus(403)` or similar, e.g.:
<code-snippet name="Pest Example Asserting postJson Response" lang="php">
it('returns all', function () {
    $response = $this->postJson('/api/docs', []);

    $response->assertSuccessful();
});
</code-snippet>

### Mocking
- Mocking can be very helpful when appropriate.
- When mocking, you can use the `Pest\Laravel\mock` Pest function, but always import it via `use function Pest\Laravel\mock;` before using it. Alternatively, you can use `$this->mock()` if existing tests do.
- You can also create partial mocks using the same import or self method.

### Datasets
- Use datasets in Pest to simplify tests that have a lot of duplicated data. This is often the case when testing validation rules, so consider this solution when writing tests for validation rules.

<code-snippet name="Pest Dataset Example" lang="php">
it('has emails', function (string $email) {
    expect($email)->not->toBeEmpty();
})->with([
    'james' => 'james@laravel.com',
    'taylor' => 'taylor@laravel.com',
]);
</code-snippet>

=== pest/v4 rules ===

## Pest 4

- Pest 4 is a huge upgrade to Pest and offers: browser testing, smoke testing, visual regression testing, test sharding, and faster type coverage.
- Browser testing is incredibly powerful and useful for this project.
- Browser tests should live in `tests/Browser/`.
- Use the `search-docs` tool for detailed guidance on utilizing these features.

### Browser Testing
- You can use Laravel features like `Event::fake()`, `assertAuthenticated()`, and model factories within Pest 4 browser tests, as well as `RefreshDatabase` (when needed) to ensure a clean state for each test.
- Interact with the page (click, type, scroll, select, submit, drag-and-drop, touch gestures, etc.) when appropriate to complete the test.
- If requested, test on multiple browsers (Chrome, Firefox, Safari).
- If requested, test on different devices and viewports (like iPhone 14 Pro, tablets, or custom breakpoints).
- Switch color schemes (light/dark mode) when appropriate.
- Take screenshots or pause tests for debugging when appropriate.

### Example Tests

<code-snippet name="Pest Browser Test Example" lang="php">
it('may reset the password', function () {
    Notification::fake();

    $this->actingAs(User::factory()->create());

    $page = visit('/sign-in'); // Visit on a real browser...

    $page->assertSee('Sign In')
        ->assertNoJavascriptErrors() // or ->assertNoConsoleLogs()
        ->click('Forgot Password?')
        ->fill('email', 'nuno@laravel.com')
        ->click('Send Reset Link')
        ->assertSee('We have emailed your password reset link!')

    Notification::assertSent(ResetPassword::class);
});
</code-snippet>

<code-snippet name="Pest Smoke Testing Example" lang="php">
$pages = visit(['/', '/about', '/contact']);

$pages->assertNoJavascriptErrors()->assertNoConsoleLogs();
</code-snippet>

=== inertia-vue/core rules ===

## Inertia + Vue

- Vue components must have a single root element.
- Use `router.visit()` or `<Link>` for navigation instead of traditional links.

<code-snippet name="Inertia Client Navigation" lang="vue">

    import { Link } from '@inertiajs/vue3'
    <Link href="/">Home</Link>

</code-snippet>

=== inertia-vue/v2/forms rules ===

## Inertia v2 + Vue Forms

<code-snippet name="Inertia Vue useForm example" lang="vue">

<script setup>
    import { useForm } from '@inertiajs/vue3'

    const form = useForm({
        email: null,
        password: null,
        remember: false,
    })
</script>

<template>
    <form @submit.prevent="form.post('/login')">
        <!-- email -->
        <input type="text" v-model="form.email">
        <div v-if="form.errors.email">{{ form.errors.email }}</div>
        <!-- password -->
        <input type="password" v-model="form.password">
        <div v-if="form.errors.password">{{ form.errors.password }}</div>
        <!-- remember me -->
        <input type="checkbox" v-model="form.remember"> Remember Me
        <!-- submit -->
        <button type="submit" :disabled="form.processing">Login</button>
    </form>
</template>

</code-snippet>

=== tailwindcss/core rules ===

## Tailwind CSS

- Use Tailwind CSS classes to style HTML; check and use existing Tailwind conventions within the project before writing your own.
- Offer to extract repeated patterns into components that match the project's conventions (i.e. Blade, JSX, Vue, etc.).
- Think through class placement, order, priority, and defaults. Remove redundant classes, add classes to parent or child carefully to limit repetition, and group elements logically.
- You can use the `search-docs` tool to get exact examples from the official documentation when needed.

### Spacing
- When listing items, use gap utilities for spacing; don't use margins.

<code-snippet name="Valid Flex Gap Spacing Example" lang="html">
    <div class="flex gap-8">
        <div>Superior</div>
        <div>Michigan</div>
        <div>Erie</div>
    </div>
</code-snippet>

### Dark Mode
- If existing pages and components support dark mode, new pages and components must support dark mode in a similar way, typically using `dark:`.

=== tailwindcss/v4 rules ===

## Tailwind CSS 4

- Always use Tailwind CSS v4; do not use the deprecated utilities.
- `corePlugins` is not supported in Tailwind v4.
- In Tailwind v4, configuration is CSS-first using the `@theme` directive — no separate `tailwind.config.js` file is needed.

<code-snippet name="Extending Theme in CSS" lang="css">
@theme {
  --color-brand: oklch(0.72 0.11 178);
}
</code-snippet>

- In Tailwind v4, you import Tailwind using a regular CSS `@import` statement, not using the `@tailwind` directives used in v3:

<code-snippet name="Tailwind v4 Import Tailwind Diff" lang="diff">
   - @tailwind base;
   - @tailwind components;
   - @tailwind utilities;
   + @import "tailwindcss";
</code-snippet>

### Replaced Utilities
- Tailwind v4 removed deprecated utilities. Do not use the deprecated option; use the replacement.
- Opacity values are still numeric.

| Deprecated |	Replacement |
|------------+--------------|
| bg-opacity-* | bg-black/* |
| text-opacity-* | text-black/* |
| border-opacity-* | border-black/* |
| divide-opacity-* | divide-black/* |
| ring-opacity-* | ring-black/* |
| placeholder-opacity-* | placeholder-black/* |
| flex-shrink-* | shrink-* |
| flex-grow-* | grow-* |
| overflow-ellipsis | text-ellipsis |
| decoration-slice | box-decoration-slice |
| decoration-clone | box-decoration-clone |
</laravel-boost-guidelines>
