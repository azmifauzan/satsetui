# MVP Development Plan - SatsetUI

## Project Overview

**Goal**: Launch a functional wizard-driven frontend template generator that produces deterministic, high-quality templates with per-page generation and history tracking.

**Core Value Proposition**: Replace unpredictable prompt-to-design tools with a structured, repeatable, wizard-based configuration system.

> **"Sat-set"** - Bahasa slang Indonesia yang berarti cepat dan efisien. SatsetUI membuat pembuatan template UI jadi sat-set!

**Success Criteria**:
- Users can complete wizard in <3 minutes (3 steps)
- Same wizard selections produce identical output
- Generated templates are functional and deploy-ready
- All generation history is recorded for credit estimation improvement

---

## MVP Boundaries

### In Scope (Must Have)

✅ **3-Step Wizard UI** (Vue.js)
- Step 1: Framework, Category & Output Format
- Step 2: Visual Design & Content (Pages, Layout, Theme, UI, Components)
- Step 3: LLM Model Selection
- Client-side state management (wizardState.ts)
- Step validation and conditional logic
- Progress indicator

✅ **Blueprint Generation** (Laravel)
- JSON Schema validation
- Storage in database (generations table)
- Blueprint → Per-page MCP prompt translation (McpPromptBuilder.php)

✅ **Per-Page LLM Generation** (Laravel Service)
- Generate each page separately with focused context
- Progress tracking (X of Y pages)
- Error recovery (continue on single page failure)
- Automatic retry mechanism (3x with exponential backoff)
- Membership-aware model selection
- Free tier uses Gemini 2.5 Flash
- Premium tier can choose admin-configured models

✅ **Generation History Recording**
- Record every prompt sent to LLM
- Record every response from LLM
- Record token usage (input/output)
- Record processing time per page
- Store success/failure status with error messages

✅ **Custom Page Statistics**
- Track all custom page names used
- Normalize and count usage
- Admin view for popular custom pages
- Candidates for promotion to predefined options

✅ **Credit System with Margins**
- Base credit calculation (model + pages + components)
- Error margin: default 10% (admin configurable)
- Profit margin: default 5% (admin configurable)
- Total = CEIL((base + extras) × (1 + error) × (1 + profit))
- Credit estimation learning from historical data
- Automatic refund on generation failure

✅ **Billing (Premium Credits)**
- Premium users top up credits
- Each premium generation charges credits with margins
- Admin-configurable margin percentages
- 25 credits on registration

✅ **Admin Panel (MVP)**
- View usage statistics
- View custom page statistics
- Configure premium models allow-list
- Configure error margin percentage
- Configure profit margin percentage
- View generation history (prompts/responses)
- User management (credits, premium status)

✅ **Bilingual UI (ID/EN)**
- Wizard UI strings translatable
- Admin/billing strings translatable
- Default language: Indonesian

✅ **Template Preview** (Vue.js)
- Display generated files in code viewer
- Per-page progress during generation
- Syntax highlighting
- File tree navigation
- Download as ZIP

✅ **Basic Auth** (Laravel)
- User registration and login
- 25 credits on registration
- Dashboard to view saved blueprints
- Rate limiting (10 generations/hour)

✅ **Documentation**
- Product instruction (3-step wizard specification)
- Architecture overview (per-page generation)
- Copilot instructions (updated for SatsetUI)
- Antigravity instructions (new)
- Blueprint schema (simplified)

### Out of Scope (Deferred to Post-MVP)

❌ **Blueprint Presets**: No saved presets or templates library

❌ **Team Collaboration**: Single-user only

❌ **Version History**: No blueprint versioning

❌ **Advanced Preview**: No live interactive preview (code view only)

❌ **Export Formats**: ZIP download only (no Git repo, Docker)

❌ **Advanced Analytics**: Basic statistics only

❌ **Component Customization**: No post-generation editing

---

## Current Implementation Status

### Completed ✅

1. ✅ Laravel + Vue + Inertia setup with Vite
2. ✅ Database migrations for all tables
3. ✅ Authentication (login, register)
4. ✅ 3-step wizard UI with state management
5. ✅ McpPromptBuilder with per-page generation
6. ✅ GenerationService with progress tracking
7. ✅ LLM integration (OpenAI-compatible API)
8. ✅ Credit system with margins
9. ✅ Admin panel (Dashboard, Users, Models, Settings, Generations)
10. ✅ Bilingual support (ID/EN)
11. ✅ Dark/Light theme support
12. ✅ Automatic retry mechanism
13. ✅ Credit refund on failure
14. ✅ Cost tracking

### In Progress 🔄

1. 🔄 Template preview with syntax highlighting
2. 🔄 ZIP download functionality
3. 🔄 Custom page statistics view in admin

### Pending ⏳

1. ⏳ Comprehensive test suite
2. ⏳ Performance optimization
3. ⏳ Production deployment setup

---

## Database Schema Summary

### Core Tables

```sql
-- User accounts
CREATE TABLE users (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    password VARCHAR(255),
    credits INT DEFAULT 25,
    is_premium BOOLEAN DEFAULT FALSE,
    is_admin BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE
);

-- Generation records
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

-- Per-page history
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

-- LLM models
CREATE TABLE llm_models (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255) UNIQUE,
    display_name VARCHAR(255),
    input_price_per_million DECIMAL(10,7),
    output_price_per_million DECIMAL(10,7),
    estimated_credits_per_generation INT,
    is_free BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0
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

## API Endpoints Summary

### Wizard & Blueprint
- `GET /wizard` - Wizard page (Inertia)
- `POST /generation/generate` - Start generation

### Generation
- `GET /generation/{id}` - View generation
- `GET /generation/{id}/progress` - Get progress
- `POST /generation/{id}/next` - Generate next page
- `POST /generation/{id}/background` - Continue in background

### Templates
- `GET /templates` - User templates list

### LLM Models
- `GET /api/llm/models` - Get available models

### Admin
- `GET /admin` - Admin dashboard
- Resource: `/admin/users` - User management
- Resource: `/admin/models` - LLM models
- `GET /admin/settings` - Settings page
- `GET /admin/generations` - Generation history

---

## Risk Mitigation

### Technical Risks

| Risk | Mitigation |
|------|------------|
| LLM API instability | Per-page retry (3x), partial success handling |
| Token estimation inaccuracy | Learning from historical data, error margin |
| Long generation times | Per-page progress, async processing |
| Timeout errors | Automatic retry with exponential backoff |

### Business Risks

| Risk | Mitigation |
|------|------------|
| Credit estimation too low | Error margin (10%) + profit margin (5%) |
| Users confused by simplified wizard | Clear labels, helpful descriptions |
| Custom pages not tracked | Automatic recording, admin visibility |
| Failed generations | Automatic credit refund |

---

## Success Metrics

1. **Wizard Completion**: >85% of users complete 3 steps
2. **Generation Success**: >95% of pages generate successfully
3. **Credit Accuracy**: Estimated within 15% of actual usage
4. **Time to Template**: <3 minutes (wizard) + ~30s per page (generation)
5. **User Satisfaction**: 4.5+ stars on ease of use

---

## Sat-set! 🚀
