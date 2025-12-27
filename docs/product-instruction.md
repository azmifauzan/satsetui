# Product Instruction: Wizard-Driven Frontend Template Generator

## Problem Statement

Frontend developers and product teams need to quickly scaffold consistent, production-ready UI templates. Current solutions fall into two extremes:

1. **Manual Coding**: Slow, inconsistent, requires design expertise
2. **AI Prompt-to-Design**: Unpredictable, non-deterministic, produces varying results

Both approaches fail to provide **repeatable, configurable, and deterministic** template generation.

## Solution: Wizard-First Configuration

This system takes a fundamentally different approach:

- **No Free-Text Prompts**: Users never describe what they want in natural language
- **Structured Decisions**: Every configuration choice is explicit and constrained
- **Deterministic Output**: Same selections = same result, always
- **LLM as Tool**: AI implements requirements, never interprets intent

### Why Avoid Prompt-to-Design?

| Problem | Our Solution |
|---------|-------------|
| "Make it modern" (subjective) | Step 6: UI Density (Compact/Comfortable/Spacious) |
| "Add some charts" (vague) | Step 7: Components → Charts (Chart.js / Apache ECharts) |
| "Should be responsive" (undefined) | Step 9: Responsiveness (Desktop-first/Mobile-first/Fully responsive) |
| "Use nice colors" (arbitrary) | Step 5: Theme (Primary/Secondary colors, Light/Dark mode) |

**Prompt-based**: "Create a modern admin dashboard with charts and a clean design"
→ Results vary, unpredictable, not reproducible

**Wizard-based**: Framework=Tailwind, Category=Admin Dashboard, Pages=[Dashboard,Charts], Layout=Sidebar+Topbar, Theme=Blue/Indigo/Dark, Density=Comfortable, Components=[Charts], Interaction=Moderate, Responsive=Fully, CodeStyle=Clean, Intent=MVP
→ Results identical every time

## Complete Wizard Specification

### Step 1: Framework Selection

**Purpose**: Choose the CSS framework foundation

**Options**:
- **Tailwind CSS**: Utility-first, highly customizable
- **Bootstrap**: Component-based, rapid prototyping

**Why this matters**: Dictates entire component structure, utility classes, and responsive patterns. This decision affects every subsequent step.

**Blueprint field**: `framework`

**Default**: Tailwind CSS

---

### Step 2: Template Category

**Purpose**: Define the primary use case and content structure

**Options**:
- **Admin Dashboard**: Internal tools, data-heavy, CRUD operations
- **Company Profile**: Public-facing, content showcase, about/services pages
- **Landing Page**: Marketing-focused, conversion-optimized, hero sections
- **SaaS Application**: User accounts, feature sections, pricing pages
- **Blog / Content Site**: Article listings, reading experience, categories

**Impact**:
- Determines default page recommendations
- Influences layout patterns
- Affects component priorities

**Blueprint field**: `category`

**Default**: Admin Dashboard

---

### Step 3: Page Selection

**Purpose**: Choose specific pages to include in the template

**Options** (multi-select):
- **Login**: Authentication form, forgot password link
- **Register**: User signup form, terms acceptance
- **Forgot Password**: Email input, reset instructions
- **Dashboard**: Overview/home page with widgets
- **User Management**: Table with CRUD operations
- **Settings**: Configuration forms, preferences
- **Charts / Analytics**: Data visualizations, metrics
- **Tables / Data List**: Sortable, filterable data tables
- **Profile**: User information, avatar, edit form
- **Public Pages**: About, Contact

**Dependencies**:
- Admin Dashboard category → Suggests Dashboard, User Management, Charts, Tables
- Landing Page category → Suggests Public Pages only
- SaaS Application → Suggests Register, Dashboard, Settings, Profile

**Blueprint field**: `pages` (array)

**Default**: ["Login", "Dashboard"]

---

### Step 4: Layout & Navigation

**Purpose**: Define structural navigation patterns

**Options**:

#### Navigation Style (single-select):
- **Sidebar**: Vertical menu, collapsible, ideal for many menu items
  - Substyle: Collapsed by default / Expanded by default
- **Top Navigation**: Horizontal menu bar, clean for few items
- **Hybrid**: Sidebar + Top bar (user menu, notifications in top)

#### Additional Layout Elements (toggles):
- **Breadcrumbs**: On / Off
- **Footer**: Minimal / Full (with links)

**Blueprint fields**:
- `layout.navigation`: "sidebar" | "topbar" | "hybrid"
- `layout.sidebarDefaultState`: "collapsed" | "expanded"
- `layout.breadcrumbs`: boolean
- `layout.footer`: "minimal" | "full"

**Default**: Sidebar (expanded), Breadcrumbs: On, Footer: Minimal

---

### Step 5: Theme & Visual Identity

**Purpose**: Define color scheme and mode preferences

**Options**:
- **Primary Color**: Color picker or preset (Blue, Green, Purple, Red, Orange, Pink)
- **Secondary Color**: Color picker or preset
- **Mode**: Light / Dark
- **Background Style**: Solid / Subtle gradient

**Blueprint fields**:
- `theme.primary`: hex code (e.g., "#3B82F6")
- `theme.secondary`: hex code
- `theme.mode`: "light" | "dark"
- `theme.background`: "solid" | "gradient"

**Default**: Primary=#3B82F6 (blue), Secondary=#6366F1 (indigo), Mode=Light, Background=Solid

**Implementation Note**: These values directly populate CSS custom properties or Tailwind config.

---

### Step 6: UI Density & Style

**Purpose**: Control spacing, sizing, and visual weight

**Options**:

#### Density (single-select):
- **Compact**: Tight spacing, small fonts, data-dense
  - Use case: Analytics, dashboards, tables
- **Comfortable**: Balanced spacing, readable
  - Use case: General applications
- **Spacious**: Generous whitespace, large touch targets
  - Use case: Public sites, accessibility focus

#### Border Radius Style (single-select):
- **Sharp**: 0-2px radius, modern/technical aesthetic
- **Rounded**: 4-8px radius, friendly/approachable

**Blueprint fields**:
- `ui.density`: "compact" | "comfortable" | "spacious"
- `ui.borderRadius`: "sharp" | "rounded"

**Default**: Comfortable, Rounded

**Implementation**: Maps to spacing scale and border-radius utility classes.

---

### Step 7: Components

**Purpose**: Select UI components to include in the template

**Options** (multi-select):
- **Buttons**: Primary, Secondary, Outline, Icon buttons
- **Forms**: Text inputs, Select, Checkbox, Radio, Textarea, File upload
- **Modals**: Dialog boxes, confirmation prompts
- **Dropdowns**: Menu dropdowns, select alternatives
- **Alerts / Toasts**: Notification messages (success, error, warning, info)
- **Cards**: Content containers with header/body/footer
- **Tabs**: Horizontal/vertical tab navigation
- **Charts**: Data visualizations (Chart.js or Apache ECharts only)

**Blueprint field**: `components` (array)

**Default**: ["Buttons", "Forms", "Cards", "Alerts"]

**Important**: When Charts is selected, the wizard must also capture which chart library to use: Chart.js or Apache ECharts.

**Blueprint fields (Charts sub-option)**:
- `chartLibrary`: `"chartjs" | "echarts"` (required when `components` includes `"charts"`)

---

### Step 8: Interaction Level

**Purpose**: Define animation and interaction richness

**Options**:
- **Static**: No animations, instant transitions, minimal interactivity
  - Use case: Maximum performance, simplicity
- **Moderate**: Hover effects, smooth transitions, basic feedback
  - Use case: Most applications (recommended)
- **Rich**: Animations, micro-interactions, loading skeletons, parallax
  - Use case: Marketing sites, premium feel

**Blueprint field**: `interaction`: "static" | "moderate" | "rich"

**Default**: Moderate

**Implementation**: Determines CSS transition/animation usage, hover states, and JS-based interactions.

---

### Step 9: Responsiveness

**Purpose**: Define responsive design approach

**Options**:
- **Desktop-First**: Design optimized for desktop, scales down to mobile
  - Use case: Internal tools, admin panels
- **Mobile-First**: Design optimized for mobile, scales up to desktop
  - Use case: Public sites, consumer apps
- **Fully Responsive**: Equal optimization for all screen sizes
  - Use case: Multi-device applications

**Blueprint field**: `responsiveness`: "desktop-first" | "mobile-first" | "fully-responsive"

**Default**: Fully Responsive

**Implementation**: Affects breakpoint usage, component hiding/showing, and navigation patterns.

---

### Step 10: Code Preferences

**Purpose**: Control code style and verbosity in generated output

**Options**:
- **Clean & Minimal**: Concise code, minimal comments, experienced developers
- **Verbose & Explicit**: Explicit variable names, longer logic, intermediate developers
- **Commented for Learning**: Heavy documentation, explanations, beginners

**Blueprint field**: `codeStyle`: "minimal" | "verbose" | "documented"

**Default**: Minimal

**Implementation**: Affects MCP prompt instructions for variable naming, comment density, and code structure.

---

### Step 11: Output Intent

**Purpose**: Signal the expected maturity of generated code

**Options**:
- **MVP-Ready Scaffold**: Quick start, placeholder content, basic functionality
  - Use case: Rapid prototyping, proof-of-concept
- **Production-Ready Base**: Robust code, error handling, accessibility considered
  - Use case: Starting point for real projects
- **Design System Starter**: Component library, documentation, reusable patterns
  - Use case: Establishing shared UI standards

**Blueprint field**: `outputIntent`: "mvp" | "production" | "design-system"

**Default**: Production-Ready Base

**Implementation**: Affects:
- Error handling depth
- Accessibility attributes (ARIA labels, focus management)
- Component documentation level
- Test coverage expectations (in future iterations)

---

## User Journey Example

### Scenario: Product Manager needs an admin dashboard

**Step-by-step wizard flow**:

1. **Framework**: Tailwind CSS (modern, customizable)
2. **Category**: Admin Dashboard
3. **Pages**: Dashboard, User Management, Charts, Settings, Profile
4. **Layout**: Hybrid (sidebar + topbar), Breadcrumbs: On, Footer: Minimal
5. **Theme**: Primary=#10B981 (green), Secondary=#3B82F6 (blue), Dark mode, Solid background
6. **UI Density**: Comfortable, Rounded borders
7. **Components**: Buttons, Forms, Modals, Alerts, Cards, Tabs, Charts (Chart.js)
8. **Interaction**: Moderate (hover, transitions)
9. **Responsiveness**: Desktop-first
10. **Code Style**: Verbose & Explicit
11. **Output Intent**: Production-Ready Base

**Result**: System generates:
- Blueprint JSON (stored in database)
- MCP prompt (deterministic text)
- LLM implementation call
- Preview-ready Vue components + Tailwind styles
- File structure with all selected pages
- Components organized per Tailwind patterns
- Dark mode CSS variables configured
- Chart.js integrated in Charts page
- Responsive breakpoints (desktop-first)
- Verbose naming, moderate comments

**Reproducibility**: Selecting the same options tomorrow, next week, or next year produces **identical output**.

---

## Non-Goals and Constraints

### What This System Does NOT Do

❌ **Accept free-form prompts**: No text boxes asking "describe your design"

❌ **Make design decisions**: LLM never chooses colors, layouts, or component styles

❌ **Support every CSS framework**: Only Tailwind and Bootstrap (chart libraries constrained to Chart.js or Apache ECharts)

❌ **Provide drag-and-drop editing**: Generated code is the final output; edit in your IDE

❌ **Learn from user behavior**: No ML training on user preferences (deterministic by design)

❌ **Generate backend logic**: Frontend templates only; API integration is user's responsibility

### Technology Boundaries

**CSS Frameworks**: Tailwind CSS, Bootstrap
- No Material UI, Bulma, Foundation, etc.

**Chart Libraries**: Chart.js, Apache ECharts
- No D3.js, Recharts, Victory, etc.

---

## System-Level Requirements (Non-Wizard)

The following requirements apply to the generator application itself (not the generated templates). These do not change the 11-step wizard; they define platform capabilities around generation.

### Languages (ID/EN)

- The application must support two languages: **Bahasa Indonesia** and **English**.
- All wizard labels, option labels, descriptions, validation messages, and admin/billing UI must be translatable.
- Default language selection must be deterministic (e.g., from user profile setting; fallback to English).

### Theme (Dark/Light)

- The generator UI must support **dark** and **light** themes.
- Theme selection is distinct from the wizard’s Step 5 theme settings (which describe generated template identity).

### Membership Tiers

- **Free member**:
  - Uses **Gemini Flash** model.
  - No model choice.
  - Generation limitations can be enforced via rate limiting.
- **Premium member**:
  - Can choose from model options configured by admin.
  - Uses a **credit** system.

### Premium Credits & Top-Up

- Premium users can top up credits.
- Each premium generation consumes credits based on cost calculation.

### Premium Cost Markup

- For premium members, the LLM output (generation) cost must have an additional **percentage markup**.
- The markup percentage must be configurable by admin.
- The **final charged amount** (base cost + markup) is what reduces the premium user’s credit balance.

### Admin Panel

Admin must be able to:
- View usage statistics (generations, cost, revenue/markup, users)
- Configure:
  - Available premium models
  - Markup percentage
  - Any generation limits and operational settings

**Component Logic**: Vue.js only
- No React, Angular, Svelte, etc.

**Output Format**: Vue SFCs (Single File Components) + CSS
- No Web Components, Lit, etc.

### Why These Constraints?

1. **Maintainability**: Supporting every framework is infeasible
2. **Quality**: Deep expertise in fewer tools beats shallow coverage
3. **Determinism**: Limited options = predictable output
4. **LLM Performance**: Popular frameworks = better LLM training data

---

## Key Differentiators

| Feature | This System | Prompt-to-Design Tools | Manual Coding |
|---------|-------------|------------------------|---------------|
| **Reproducibility** | ✅ Deterministic | ❌ Varies | ✅ Manual control |
| **Speed** | ✅ Minutes | ✅ Minutes | ❌ Hours/Days |
| **Customization** | ✅ 11 wizard steps | ❌ Limited | ✅ Unlimited |
| **Learning Curve** | ✅ Low (wizard UI) | ⚠️ Prompt engineering | ❌ High (coding) |
| **Quality Control** | ✅ Constrained options | ❌ Unpredictable | ✅ Manual review |
| **Scalability** | ✅ Templated | ⚠️ Varies | ❌ Time-consuming |

---

## Blueprint-to-MCP Flow

This is the core intellectual property of the system:

```
WIZARD UI (Vue)
   ↓
   11 steps of structured input
   ↓
BLUEPRINT JSON (Laravel validation)
   ↓
   {
     "framework": "tailwind",
     "category": "admin-dashboard",
     "pages": ["dashboard", "users"],
     "layout": {...},
     "theme": {...},
     "ui": {...},
     "components": [...],
     "interaction": "moderate",
     "responsiveness": "fully-responsive",
     "codeStyle": "minimal",
     "outputIntent": "production"
   }
   ↓
MCP PROMPT BUILDER (McpPromptBuilder.php)
   ↓
   Assembles deterministic prompt:
   - ROLE: "You are a Vue.js + Tailwind expert..."
   - CONTEXT: "Generate an admin dashboard with..."
   - CONSTRAINTS: "Use only Tailwind utilities, no custom CSS..."
   - REQUIREMENTS: "Include these pages: dashboard, users..."
   - OUTPUT FORMAT: "File structure: src/pages/..."
   ↓
LLM API CALL (OpenAI, Anthropic, etc.)
   ↓
   Returns generated code
   ↓
PREVIEW RENDERER (Vue component)
   ↓
USER SEES TEMPLATE
```

**Critical**: The MCP prompt contains ZERO ambiguity. The LLM has no creative freedom.

---

## Future Considerations (Post-MVP)

**Not in initial release, but planned**:
- Save/load Blueprint presets
- Team sharing of Blueprints
- Version history of generated templates
- A/B testing different Output Intents
- Export to Figma (design tokens)
- Component library marketplace
- CLI tool for blueprint-based generation

**Explicitly Out of Scope**:
- Visual design editor
- Real-time collaborative editing
- Backend code generation
- Database schema generation
- AI-suggested improvements

---

## Success Metrics

How we measure if this system works:

1. **Reproducibility**: Same Blueprint → Same output 100% of the time
2. **Wizard Completion Rate**: >80% of users complete all 11 steps
3. **Time to Template**: <5 minutes from start to preview
4. **Code Quality**: Generated code passes linting, type checking
5. **User Satisfaction**: 4.5+ stars on ease of use
6. **Adoption**: Users prefer wizard over manual coding for initial scaffolding

---

## Conclusion

This system replaces subjective, unpredictable prompt-to-design tools with a **structured, deterministic, and repeatable** wizard-driven workflow.

Every decision is explicit. Every output is traceable. Every result is reproducible.

The LLM is a tool, not a designer. The wizard is the source of truth.
