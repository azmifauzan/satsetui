# GitHub Copilot Instructions for Template Generator

## Project Philosophy

This repository implements a **wizard-driven frontend template generator**. It is fundamentally different from prompt-to-design systems.

### Core Principles

1. **No Free-Form Prompts**: Users never start with natural language prompts. All configuration happens through structured wizard steps.

2. **Deterministic Output**: Same wizard selections MUST produce identical results every time. No randomness, no creative interpretation.

3. **LLM as Implementation Engine Only**: The LLM receives a fully-formed MCP (Model Context Prompt) that contains all decisions. The LLM implements, never decides.

4. **Blueprint-First Architecture**: 
   - Wizard UI → Blueprint JSON → MCP → LLM → Preview
   - Each step is explicit and traceable

## Technology Constraints

### ALLOWED Technologies

- **Backend**: Laravel (current version in composer.json)
- **Frontend**: Vue.js with TypeScript
- **CSS Frameworks**: Tailwind CSS, Bootstrap ONLY
- **Charts**: Chart.js, Apache ECharts ONLY
- **Build Tool**: Vite
- **Testing**: Pest (PHP), Vitest (JS/TS)

### FORBIDDEN Technologies

- ❌ React, Angular, Svelte (Vue only)
- ❌ Other CSS frameworks (no Bulma, Material, etc.)
- ❌ Drag-and-drop builders
- ❌ Visual editors
- ❌ D3.js or other chart libraries
- ❌ Prompt-based generation UI

## Wizard Structure

The wizard has exactly 5 steps. When generating code related to wizard logic, use this structure:

1. **Framework & Category** (combined) - Select CSS framework (Tailwind/Bootstrap) and template category
2. **Pages & Layout** (combined) - Choose pages and configure navigation/layout
3. **Theme & Styling** (combined) - Visual identity, UI density, and component preferences
4. **Responsiveness & Interactions** (combined) - Responsive breakpoints and interaction level
5. **Code Preferences & Output** (combined) - Code style, naming conventions, and output format

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
    - Free uses Gemini Flash (no model choice)
    - Premium can choose admin-defined models
- **Billing**: Premium uses credits; premium generation cost includes admin-configurable markup percentage.
- **Admin Panel**: Statistics and settings (premium models allow-list, markup percentage).

## Code Generation Rules

### When Generating Laravel Code

- **Controllers**: Keep thin. Delegate to Services.
- **Services**: Business logic lives here. McpPromptBuilder is the core service.
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

The MCP prompt structure is critical:

```
SYSTEM ROLE
│
├── PROJECT CONTEXT (from Blueprint)
│   ├── Framework choice
│   ├── Template category
│   └── Selected pages
│
├── CONSTRAINTS
│   ├── Technology boundaries
│   ├── Code style preferences
│   └── Output format rules
│
├── REQUIREMENTS
│   ├── Layout specification
│   ├── Theme configuration
│   ├── Component list
│   └── Responsiveness rules
│
└── OUTPUT FORMAT
    ├── File structure
    ├── Naming conventions
    └── Expected deliverables
```

Never generate MCPs that:
- Ask the LLM to "decide" or "choose"
- Include vague requirements
- Allow creative freedom
- Mix concerns

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
│   ├── Requests/         # Wizard validation
│   └── Resources/        # API responses
├── Services/             # McpPromptBuilder, BlueprintValidator, etc.
└── Models/               # User, Project, Blueprint (if persisted)

resources/js/
├── wizard/               # Wizard-specific Vue components
│   ├── steps/            # Individual step components
│   ├── wizardState.ts    # Central state management
│   └── types.ts          # TypeScript interfaces
├── preview/              # Preview rendering components
└── lib/                  # Shared utilities

docs/
├── product-instruction.md
├── architecture.md
└── mvp-plan.md
```

## Behavior Guidelines for Copilot

### DO

- ✅ Reference wizard step numbers explicitly (e.g., "Step 2: Pages & Layout")
- ✅ Validate against blueprint schema
- ✅ Generate deterministic MCP prompts
- ✅ Add reasoning comments in McpPromptBuilder
- ✅ Create strongly-typed TypeScript interfaces
- ✅ Follow Laravel best practices (Service layer, Form Requests)
- ✅ Use Tailwind/Bootstrap utility classes appropriately
- ✅ **ALWAYS implement bilingual support** using `useI18n()` - no hardcoded strings
- ✅ **ALWAYS add dark mode support** using Tailwind `dark:` variants
- ✅ **ALWAYS wrap authenticated pages** with `AppLayout.vue`

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

## Example Scenarios

### Scenario 1: User asks to "add a new wizard step"

**Correct Response**:
1. Update `/docs/product-instruction.md` with step definition
2. Update `/app/Blueprints/template-blueprint.schema.json`
3. Update `/resources/js/wizard/wizardState.ts`
4. Create Vue component in `/resources/js/wizard/steps/`
5. Update McpPromptBuilder to handle new blueprint field
6. Add validation in Form Request
7. Update tests

### Scenario 2: User asks to "improve the design"

**Correct Response**:
- Ask for specificity: Which wizard step? Which component?
- Reference existing wizard options (theme, density, interaction level)
- Suggest changes within existing wizard structure
- Never add free-form customization

### Scenario 3: User asks to "make the output more creative"

**Incorrect Response**: Add randomness or LLM-based variation

**Correct Response**:
- Explain that deterministic output is a core requirement
- Suggest adding more wizard options for controlled variation
- Reference Output Intent step (Step 5) for style preferences

## Testing Strategy

### Must Test

- Wizard state transitions
- Blueprint validation
- MCP prompt assembly
- Each wizard step independently

### Don't Test

- LLM output quality (external system)
- Subjective design quality
- Cross-browser rendering (preview is informational only)

## Performance Considerations

- Wizard state should be client-side until final submission
- Blueprint generation is synchronous (fast)
- MCP generation is synchronous (fast)
- LLM call is async (slow) - handle with proper loading states
- Cache generated templates per Blueprint hash

## Security Notes

- Sanitize all wizard inputs (even though structured)
- Validate Blueprint against JSON schema server-side
- Rate-limit LLM API calls
- Never expose LLM API keys client-side
- User-generated templates should be sandboxed for preview

## References

- Full wizard specification: `/docs/product-instruction.md`
- Architecture overview: `/docs/architecture.md`
- Blueprint schema: `/app/Blueprints/template-blueprint.schema.json`
- MVP roadmap: `/docs/mvp-plan.md`
