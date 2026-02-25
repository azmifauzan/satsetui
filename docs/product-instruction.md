# Product Instruction: SatsetUI - Wizard-Driven Frontend Template Generator

## Problem Statement

Frontend developers and product teams need to quickly scaffold consistent, production-ready UI templates. Current solutions fall into two extremes:

1. **Manual Coding**: Slow, inconsistent, requires design expertise
2. **AI Prompt-to-Design**: Unpredictable, non-deterministic, produces varying results

Both approaches fail to provide **repeatable, configurable, and deterministic** template generation.

## Solution: Wizard-First Configuration

SatsetUI takes a fundamentally different approach:

- **No Free-Text Prompts**: Users never describe what they want in natural language
- **Structured Decisions**: Every configuration choice is explicit and constrained
- **Deterministic Output**: Same selections = same result, always
- **LLM as Tool**: AI implements requirements, never interprets intent

> **"Sat-set"** - Bahasa slang Indonesia yang berarti cepat dan efisien. SatsetUI membuat pembuatan template UI jadi sat-set!

### Why Avoid Prompt-to-Design?

| Problem | Our Solution |
|---------|-------------|
| "Make it modern" (subjective) | Step 2: UI Density (Compact/Comfortable/Spacious) |
| "Add some charts" (vague) | Step 2: Components → Charts (Chart.js / Apache ECharts) |
| "Should be responsive" (implicit) | Auto-selected: Fully Responsive |
| "Use nice colors" (arbitrary) | Step 2: Theme (Primary/Secondary colors, Light/Dark mode) |

**Prompt-based**: "Create a modern admin dashboard with charts and a clean design"
→ Results vary, unpredictable, not reproducible

**Wizard-based**: Framework=Tailwind, Category=Admin Dashboard, Pages=[Dashboard,Charts], Layout=Sidebar+Topbar, Theme=Blue/Indigo/Dark, Density=Comfortable, Components=[Charts], OutputFormat=Vue, LlmModel=Satset
→ Results identical every time

---

## Complete Wizard Specification (3 Steps)

### Step 1: Framework, Category & Output Format

**Purpose**: Choose the CSS framework foundation, define the primary use case, and select output technology

#### Framework Options:
- **Tailwind CSS**: Utility-first, highly customizable
- **Bootstrap**: Component-based, rapid prototyping
- **Pure CSS**: Vanilla CSS, no framework dependencies, full control

**Why this matters**: Dictates entire component structure, utility classes, and responsive patterns. This decision affects every subsequent step.

**Important Note**: If you select **HTML + CSS** as output format, and your framework choice is Tailwind or Bootstrap, the generated HTML will automatically include the framework's CDN link in the `<head>` section. If you choose Pure CSS framework, no external framework will be embedded.

#### Template Category Options:
- **Admin Dashboard**: Internal tools, data-heavy, CRUD operations
- **Company Profile**: Public-facing, content showcase, about/services pages
- **Landing Page**: Marketing-focused, conversion-optimized, hero sections
- **SaaS Application**: User accounts, feature sections, pricing pages
- **Blog / Content Site**: Article listings, reading experience, categories
- **E-Commerce**: Product catalogs, shopping cart, checkout pages
- **Custom**: User-defined custom category with name and description

#### Custom Category Input

When "Custom" is selected, the wizard displays additional input fields:

- **Category Name** (required): Minimum 3 characters
  - Placeholder: "Contoh: Portal Kesehatan, Sistem Inventori, Platform Edukasi"
- **Category Description** (optional): Detailed description of the custom category
  - Placeholder: "Jelaskan tujuan dan fitur utama dari kategori template Anda..."

#### Output Format Options:
- **HTML + CSS**: Pure HTML with plain CSS, no JS framework (Static sites, simple pages)
  - **Note**: If Tailwind or Bootstrap was selected, the generated HTML will include the framework's CDN link in the header.
- **React JS**: React components with JSX and hooks (React ecosystem projects)
- **Vue.js**: Vue 3 components with Composition API (Vue ecosystem projects)
- **Angular**: Angular components with TypeScript (Angular ecosystem projects)
- **Svelte**: Svelte components with compile-time optimization (Svelte ecosystem projects)
- **Custom**: User-defined custom output format with description

#### Custom Output Format

When "Custom" is selected for output format, the wizard displays a textarea:

- **Custom Format Description**: Detailed description of the desired output format
  - Placeholder: "Contoh: PHP dengan Laravel Blade templates dan Alpine.js untuk interaktivitas. Gunakan Tailwind CSS untuk styling."
  - Hint: "Jelaskan teknologi, framework, atau format spesifik yang Anda inginkan."

**Impact**:
- Framework determines component structure and styling approach
- Category determines default page recommendations
- Category influences layout patterns and component priorities
- Custom categories provide full flexibility for unique use cases
- Output format determines the code syntax and structure in the generated template

**Blueprint fields**: 
- `framework`: "tailwind" | "bootstrap" | "pure-css"
- `category`: "admin-dashboard" | "company-profile" | "landing-page" | "saas-application" | "blog-content-site" | "e-commerce" | "custom"
- `customCategoryName`: string (required when category is "custom")
- `customCategoryDescription`: string (optional)
- `outputFormat`: "html-css" | "react" | "vue" | "angular" | "svelte" | "custom"
- `customOutputFormat`: string (required when outputFormat is "custom")

**Default**: Tailwind CSS, Admin Dashboard, Vue.js

---

### Step 2: Visual Design & Content

**Purpose**: Define pages, layout, theme, UI style, and components - all visual and content aspects

#### Page Selection Options (multi-select):
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

#### Custom Pages

Users can add custom pages beyond the predefined options. Each custom page requires:

- **Page Name** (required): Minimum 2 characters
  - Placeholder: "Contoh: Inventory, Reports, Analytics Dashboard"
- **Page Description** (optional): Description of the page's purpose and content
  - Placeholder: "Jelaskan fungsi dan konten dari halaman ini..."

**Custom Page Tracking**: All custom pages are recorded in the database for admin statistics. Popular custom pages will be analyzed and potentially added as predefined options in future wizard versions.

**Credit Impact**: Base quota includes 5 pages. Each additional page beyond the quota costs +1 credit.

#### Layout & Navigation Options:

**Navigation Style** (single-select):
- **Sidebar**: Vertical menu, collapsible, ideal for many menu items
  - Substyle: Collapsed by default / Expanded by default
- **Top Navigation**: Horizontal menu bar, clean for few items
- **Hybrid**: Sidebar + Top bar (user menu, notifications in top)

**Additional Layout Elements** (toggles):
- **Breadcrumbs**: On / Off
- **Footer**: Minimal / Full (with links)

#### Custom Navigation Items

Users can add custom navigation menu items. Each custom nav item requires:

- **Label** (required): Minimum 2 characters
- **Route/URL** (required): The navigation route
- **Icon** (optional): Icon name for the menu item

#### Theme & Visual Identity:
- **Primary Color**: Color picker or preset (Blue, Green, Purple, Red, Orange, Pink)
- **Secondary Color**: Color picker or preset
- **Mode**: Light / Dark
- **Background Style**: Solid / Subtle gradient

#### UI Density & Style:

**Density** (single-select):
- **Compact**: Tight spacing, small fonts, data-dense (Analytics, dashboards, tables)
- **Comfortable**: Balanced spacing, readable (General applications)
- **Spacious**: Generous whitespace, large touch targets (Public sites, accessibility)

**Border Radius Style** (single-select):
- **Sharp**: 0-2px radius, modern/technical aesthetic
- **Rounded**: 4-8px radius, friendly/approachable

#### Components (multi-select):
- **Buttons**: Primary, Secondary, Outline, Icon buttons
- **Forms**: Text inputs, Select, Checkbox, Radio, Textarea, File upload
- **Modals**: Dialog boxes, confirmation prompts
- **Dropdowns**: Menu dropdowns, select alternatives
- **Alerts / Toasts**: Notification messages (success, error, warning, info)
- **Cards**: Content containers with header/body/footer
- **Tabs**: Horizontal/vertical tab navigation
- **Charts**: Data visualizations (Chart.js or Apache ECharts only)

**Important**: When Charts is selected, the wizard must also capture which chart library to use.

#### Custom Components

Users can add custom UI components beyond the predefined options. Each custom component requires:

- **Component Name** (required): Minimum 2 characters
- **Component Description** (optional): Description of the component's purpose and features

**Credit Impact**: Base quota includes 6 components. Each additional component beyond the quota costs +0.5 credit.

**Blueprint fields**:
- `pages`: array (e.g., ["login", "dashboard", "users"])
- `customPages`: array of objects with id, name, description
- `layout.navigation`: "sidebar" | "topbar" | "hybrid"
- `layout.sidebarDefaultState`: "collapsed" | "expanded"
- `layout.breadcrumbs`: boolean
- `layout.footer`: "minimal" | "full"
- `layout.customNavItems`: array of objects with id, label, route, icon
- `theme.primary`: hex code (e.g., "#3B82F6")
- `theme.secondary`: hex code
- `theme.mode`: "light" | "dark"
- `theme.background`: "solid" | "gradient"
- `ui.density`: "compact" | "comfortable" | "spacious"
- `ui.borderRadius`: "sharp" | "rounded"
- `components`: array (e.g., ["buttons", "forms", "cards"])
- `customComponents`: array of objects with id, name, description
- `chartLibrary`: "chartjs" | "echarts" (required when components includes "charts")

**Default**: Pages=["Login", "Dashboard"], Navigation=Sidebar (expanded), Breadcrumbs: On, Footer: Minimal, Primary=#3B82F6 (blue), Secondary=#6366F1 (indigo), Mode=Light, Background=Solid, Density=Comfortable, BorderRadius=Rounded, Components=["Buttons", "Forms", "Cards", "Alerts"]

---

### Step 3: LLM Model Selection

**Purpose**: Choose the AI model type for generation

#### LLM Model Types (2-Model System):

SatsetUI uses a **2-model type system**. Users choose between two model types, each backed by an admin-configurable underlying model:

- **Satset** (default: `gemini-2.0-flash-exp`): Fast generation with good quality — perfect for quick builds (6 credits per generation)
- **Expert** (default: `gemini-2.5-pro-preview`): Best quality with detailed, production-ready output (15 credits per generation)

**Admin Configuration**:
- Model name, provider (`gemini` or `openai`), API key, and base URL are admin-configurable per model type
- API keys and base URLs are stored encrypted in the database (`llm_models` table)
- Base credits per model type are admin-configurable (minimum 1 credit)
- Each model type can be independently activated or deactivated

**Credit System**:
- All users start with **100 credits** at registration
- Users choose between Satset (6 credits) or Expert (15 credits) model types
- Model selection is disabled when user has insufficient credits for the chosen model
- Each generation consumes credits based on the selected model type
- **Extra Page Credits**: +1 credit per page beyond base quota of 5
- **Extra Component Credits**: +0.5 credit per component beyond base quota of 6
- **Error Margin**: +10% (configurable in admin) to account for token estimation variance
- **Profit Margin**: +5% (configurable in admin) for operational costs
- Total cost = (Model cost + Extra page credits + Extra component credits) × (1 + Error Margin) × (1 + Profit Margin)

**Blueprint fields**:
- `llmModel`: string (model type: `"satset"` or `"expert"`)
- `modelCredits`: number (base model cost: 6 or 15)
- `calculatedCredits`: number (total cost including extras and margins)
- `creditBreakdown`: object with detailed cost breakdown (baseCredits, extraPageCredits, extraComponentCredits, subtotal, errorMargin, profitMargin, total)

**Default**: Satset (fast & affordable)

---

## Auto-Selected Best Defaults (Not Shown in Wizard)

The following settings are automatically applied with optimal values and are NOT shown in the wizard UI:

### Responsiveness: Fully Responsive
- Equal optimization for all screen sizes
- Mobile (<640px): Hamburger menu, stacked layout
- Tablet (640-1024px): Collapsible sidebar, responsive grid
- Desktop (>1024px): Expanded sidebar, multi-column layout

### Interaction Level: Moderate
- Hover effects, smooth transitions, basic feedback
- 150ms ease-in-out for interactive elements
- No complex animations, no parallax, no loading skeletons

### Code Style: Documented
- Clear comments explaining code sections
- Well-documented functions and components
- TypeScript interfaces with JSDoc comments

**Blueprint fields (auto-set)**:
- `responsiveness`: "fully-responsive"
- `interaction`: "moderate"
- `codeStyle`: "documented"

---

## Per-Page Generation Architecture

### Generation Flow

SatsetUI generates templates **per page** rather than all at once. This enables:

1. **Better LLM Context**: Each page gets focused attention with full context
2. **Progress Tracking**: Users see real-time progress as each page completes
3. **Error Recovery**: If one page fails, others are not affected
4. **Credit Accuracy**: Actual token usage is tracked per page for better estimation

### Prompt Structure Per Page

For each page, the MCP Prompt Builder creates:

```
SYSTEM ROLE
│
├── PROJECT CONTEXT
│   ├── Framework choice
│   ├── Template category
│   ├── Output format
│   └── Full page list (for navigation context)
│
├── CONSTRAINTS
│   ├── Technology boundaries
│   ├── Auto-selected code style
│   └── Auto-selected responsiveness
│
├── REQUIREMENTS
│   ├── Layout specification
│   ├── Theme configuration
│   ├── Component list
│   └── Auto-selected interaction level
│
├── PAGE-SPECIFIC REQUIREMENTS
│   ├── Current page name and purpose
│   ├── Page-specific functionality
│   └── Page-specific components needed
│
└── OUTPUT FORMAT
    ├── Single file output
    ├── Naming conventions
    └── Expected structure
```

### History Recording

Every generation is recorded with:

1. **Generation Record**: Main generation metadata (project, user, model, total credits)
2. **Page Generation Records**: Individual records per page
   - Page name (predefined or custom)
   - MCP prompt sent to LLM
   - Raw LLM response
   - Token usage (input/output)
   - Processing time
   - Success/failure status
   - Error message (if failed)

### Credit Estimation Learning

SatsetUI uses historical page generation data to improve credit estimation:

1. **Initial Estimation**: Based on model pricing and estimated tokens
2. **Actual Recording**: Real token usage recorded after generation
3. **Moving Average**: Last 100 generations of same page type are averaged
4. **Weighted Learning**: Recent generations have higher weight
5. **Per-Page Estimation**: Different page types have different estimates
   - Login page: ~500 output tokens
   - Dashboard: ~2000 output tokens
   - Charts: ~1500 output tokens
   - Custom pages: Use average of all custom pages

---

## Credit Calculation Details

### Base Formula

```
subtotal = modelCredits + extraPageCredits + extraComponentCredits
withErrorMargin = subtotal × (1 + errorMarginPercent)
totalCredits = CEIL(withErrorMargin × (1 + profitMarginPercent))
```

### Variables

| Variable | Default | Configurable |
|----------|---------|--------------|
| `errorMarginPercent` | 10% (0.10) | Yes (Admin) |
| `profitMarginPercent` | 5% (0.05) | Yes (Admin) |
| `extraPageCredits` | 1 per page over 5 | No |
| `extraComponentCredits` | 0.5 per component over 6 | No |

### Example Calculation

```
Model: Expert (15 credits)
Pages: 8 (3 extra beyond quota of 5)
Components: 10 (4 extra beyond quota of 6)

Subtotal:
- Model: 15 credits
- Extra pages: 3 × 1 = 3 credits
- Extra components: 4 × 0.5 = 2 credits
- Subtotal: 15 + 3 + 2 = 20 credits

With Margins:
- After error margin (10%): 20 × 1.10 = 22 credits
- After profit margin (5%): 22 × 1.05 = 23.1 credits
- Final (rounded up): 24 credits
```

---

## Custom Page Statistics

### Recording Custom Pages

When users create custom pages, SatsetUI records:

1. **Custom Page Name**: Normalized lowercase
2. **Category Context**: Which template category was selected
3. **Usage Count**: How many times this custom page name was used
4. **First Used**: Timestamp of first usage
5. **Last Used**: Timestamp of most recent usage

### Admin Dashboard Features

Admins can view:
- Most popular custom page names
- Custom pages by category
- Trend analysis over time
- Candidates for promotion to predefined options

### Promotion to Predefined

When a custom page reaches threshold (e.g., 100 uses), admin can:
1. Review the page name and common descriptions
2. Add it to predefined page options
3. Include it in future wizard releases

---

## User Journey Example

### Scenario: Product Manager needs an admin dashboard

**3-step wizard flow**:

1. **Framework, Category & Output**: Tailwind CSS + Admin Dashboard + Vue.js
2. **Visual Design & Content**: 
   - Pages: Dashboard, User Management, Charts, Settings, Profile
   - Layout: Hybrid (sidebar + topbar), Breadcrumbs: On, Footer: Minimal
   - Theme: Primary=#10B981 (green), Secondary=#3B82F6 (blue), Dark mode, Solid
   - Density: Comfortable, Rounded borders
   - Components: Buttons, Forms, Modals, Alerts, Cards, Tabs, Charts (Chart.js)
3. **LLM Model**: Expert (15 credits)

**Result**: SatsetUI generates:
- Blueprint JSON (stored in database)
- 5 page-specific MCP prompts
- Each page generated sequentially with progress tracking
- All prompts and responses recorded for history
- Preview-ready Vue components + Tailwind styles
- Credits deducted after successful generation

**Reproducibility**: Selecting the same options tomorrow, next week, or next year produces **identical output**.

---

## Non-Goals and Constraints

### What SatsetUI Does NOT Do

❌ **Accept free-form prompts**: No text boxes asking "describe your design"

❌ **Make design decisions**: LLM never chooses colors, layouts, or component styles

❌ **Support every CSS framework**: Only Tailwind, Bootstrap, and Pure CSS (chart libraries constrained to Chart.js or Apache ECharts)

❌ **Provide drag-and-drop editing**: Generated code is the final output; edit in your IDE

❌ **Learn from user behavior**: No ML training on user preferences (deterministic by design)

❌ **Generate backend logic**: Frontend templates only; API integration is user's responsibility

### Technology Boundaries

**CSS Frameworks**: Tailwind CSS, Bootstrap, Pure CSS
- No Material UI, Bulma, Foundation, etc.

**Chart Libraries**: Chart.js, Apache ECharts
- No D3.js, Recharts, Victory, etc.

---

## System-Level Requirements (Non-Wizard)

The following requirements apply to the SatsetUI application itself (not the generated templates).

### Languages (ID/EN)

- The application must support two languages: **Bahasa Indonesia** and **English**.
- All wizard labels, option labels, descriptions, validation messages, and admin/billing UI must be translatable.
- Default language: Indonesian (id).

### Theme (Dark/Light)

- The SatsetUI UI must support **dark** and **light** themes.
- Theme selection is distinct from the wizard's Step 2 theme settings (which describe generated template identity).
- Default theme: Light.

### Membership Tiers

- All users start with **100 credits** at registration.
- Users choose between **Satset** (6 credits, fast) or **Expert** (15 credits, premium) model types for each generation.
- Admin can change the underlying model configuration (provider, model name, API key, base URL, credits) for each model type.
- Admin can toggle user premium status and adjust credits manually.

### Credits & Top-Up

- Users can top up credits.
- Each generation consumes credits based on the selected model type and cost calculation.
- 100 credits given at registration.

### Cost Margins

- **Error Margin**: Default 10%, configurable by admin. Accounts for token estimation variance.
- **Profit Margin**: Default 5%, configurable by admin. For operational costs.
- Final charged amount = (base cost + extras) × (1 + error margin) × (1 + profit margin)

### Admin Panel

Admin must be able to:
- View usage statistics (generations, cost, revenue, users)
- View custom page statistics (popular custom pages, candidates for promotion)
- Configure:
  - Satset and Expert model settings (provider, model name, API key, base URL, credits)
  - Error margin percentage
  - Profit margin percentage
- View generation history with prompts and responses

---

## Key Differentiators

| Feature | SatsetUI | Prompt-to-Design Tools | Manual Coding |
|---------|----------|------------------------|---------------|
| **Reproducibility** | ✅ Deterministic | ❌ Varies | ✅ Manual control |
| **Speed** | ✅ Minutes | ✅ Minutes | ❌ Hours/Days |
| **Customization** | ✅ 3 wizard steps | ❌ Limited | ✅ Unlimited |
| **Learning Curve** | ✅ Low (wizard UI) | ⚠️ Prompt engineering | ❌ High (coding) |
| **Quality Control** | ✅ Constrained options | ❌ Unpredictable | ✅ Manual review |
| **Scalability** | ✅ Templated | ⚠️ Varies | ❌ Time-consuming |

---

## Blueprint-to-MCP Flow

This is the core intellectual property of SatsetUI:

```
WIZARD UI (Vue)
   ↓
   3 steps of structured input
   ↓
BLUEPRINT JSON (Laravel validation)
   ↓
   {
     "framework": "tailwind",
     "category": "admin-dashboard",
     "outputFormat": "vue",
     "pages": ["dashboard", "users"],
     "layout": {...},
     "theme": {...},
     "ui": {...},
     "components": [...],
     "interaction": "moderate",        // auto-selected
     "responsiveness": "fully-responsive", // auto-selected
     "codeStyle": "documented",        // auto-selected
     "llmModel": "satset",
     "modelCredits": 6
   }
   ↓
MCP PROMPT BUILDER (McpPromptBuilder.php) - PER PAGE
   ↓
   For each page, assembles deterministic prompt:
   - ROLE: "You are a Vue.js + Tailwind expert..."
   - CONTEXT: "Generate an admin dashboard with..."
   - CONSTRAINTS: "Use only Tailwind utilities, no custom CSS..."
   - PAGE REQUIREMENTS: "Generate Dashboard page with..."
   - OUTPUT FORMAT: "File structure: Dashboard.vue"
   ↓
LLM API CALL (per page)
   ↓
   Returns generated code for single page
   ↓
RECORD HISTORY
   ↓
   Store prompt + response + token usage
   ↓
NEXT PAGE (repeat until all pages done)
   ↓
PREVIEW RENDERER (Vue component)
   ↓
USER SEES TEMPLATE
```

**Critical**: The MCP prompt contains ZERO ambiguity. The LLM has no creative freedom.

---

## Success Metrics

How we measure if SatsetUI works:

1. **Reproducibility**: Same Blueprint → Same output 100% of the time
2. **Wizard Completion Rate**: >80% of users complete all 3 steps
3. **Time to Template**: <5 minutes from start to preview
4. **Code Quality**: Generated code passes linting, type checking
5. **User Satisfaction**: 4.5+ stars on ease of use
6. **Credit Accuracy**: Estimated credits within 15% of actual usage

---

## Conclusion

SatsetUI replaces subjective, unpredictable prompt-to-design tools with a **structured, deterministic, and repeatable** wizard-driven workflow.

Every decision is explicit. Every output is traceable. Every result is reproducible.

The LLM is a tool, not a designer. The wizard is the source of truth.

**Sat-set!** 🚀
