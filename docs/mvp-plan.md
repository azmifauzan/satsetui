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
- Progress tracking (X of Y pages) via SSE streaming
- Error recovery (continue on single page failure)
- Automatic retry mechanism (3x with exponential backoff)
- 2 model types: Satset (cepat) & Expert (premium)
- Background queue generation via ProcessTemplateGeneration job

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

✅ **Billing (Credits)**
- All users start with 100 credits
- Each generation charges credits with margins
- Admin-configurable margin percentages
- Auto-refund on generation failure

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
- Email verification (MustVerifyEmail)
- 100 credits on registration
- Dashboard with statistics
- Rate limiting (5 login attempts)

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

✅ **Refinement Chat**: Post-generation editing via conversational refinement (implemented)

❌ **JS Framework Live Preview**: No live preview with framework-specific rendering

❌ **Payment System**: No credit purchase/topup flow

---

## Current Implementation Status

### Completed ✅

1. ✅ Laravel 12 + Vue 3 + Inertia v2 setup with Vite 7
2. ✅ Database migrations (17 files)
3. ✅ Authentication (login, register, email verification)
4. ✅ 3-step wizard UI with state management (wizardState.ts)
5. ✅ McpPromptBuilder with per-page generation (1236 lines)
6. ✅ GenerationService with retry, context, progress tracking (664 lines)
7. ✅ LLM integration (OpenAI-compatible API via Sumopod)
8. ✅ 2-model system (Satset + Expert) — admin-configurable
9. ✅ Credit system with margins + auto-refund
10. ✅ Credit estimation learning (CreditEstimationService)
11. ✅ Cost tracking (USD + IDR) per page generation
12. ✅ Admin panel (Dashboard, Users, Models, Settings, Generations)
13. ✅ Bilingual support (ID/EN) with user persistence
14. ✅ Dark/Light theme support
15. ✅ Automatic retry mechanism (3x, exponential backoff)
16. ✅ SSE streaming for real-time generation progress
17. ✅ Background queue generation (ProcessTemplateGeneration job)
18. ✅ Refinement chat (post-generation editing)
19. ✅ ZIP download (JSZip)
20. ✅ Project info/branding consistency across pages
21. ✅ Telegram notifications for admin (user registration)
22. ✅ Generation policy (user access control)
23. ✅ Generation naming
24. ✅ Docker deployment setup
25. ✅ Test suite (13 files: 9 Feature + 4 Unit)

### Known Issues / TODO 🔄

1. 🔄 Custom page type detection always records 'predefined'
2. 🔄 Dashboard recent activity is placeholder (empty array)
3. 🔄 AdminStatisticsController exists but has no routes (unused)
4. 🔄 UserFactory missing credits, is_premium, is_admin, is_active fields
5. 🔄 Legacy wizard step files still present (old 5-step system)
6. 🔄 Admin retry action has dispatched job commented out

### Not Implemented ❌

1. ❌ Payment/topup flow (CreditTransaction::TYPE_TOPUP exists, no controller)
2. ❌ Rate limiting for generation endpoint
3. ❌ Bulk admin actions (CSV export, bulk credit adjustment)
4. ❌ JS framework live preview in workspace
5. ❌ Blueprint presets / template library
6. ❌ Team collaboration

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
    credits INT DEFAULT 100,
    is_premium BOOLEAN DEFAULT FALSE,
    is_admin BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    language VARCHAR(5) DEFAULT 'id',
    suspended_at TIMESTAMP NULL
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

-- LLM models (2-type system)
CREATE TABLE llm_models (
    id BIGINT PRIMARY KEY,
    model_type ENUM('satset', 'expert') UNIQUE,
    provider ENUM('gemini', 'openai'),
    model_name VARCHAR(255),
    api_key TEXT, -- encrypted
    base_url TEXT, -- encrypted
    base_credits INT DEFAULT 6,
    is_active BOOLEAN DEFAULT TRUE
);

-- Refinement messages
CREATE TABLE refinement_messages (
    id BIGINT PRIMARY KEY,
    generation_id BIGINT,
    role ENUM('user', 'assistant'),
    content TEXT,
    type VARCHAR(50),
    page_name VARCHAR(100)
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
- `GET /generation/{id}/progress` - Get progress (polling)
- `GET /generation/{id}/stream` - SSE streaming progress
- `POST /generation/{id}/next` - Generate next page
- `POST /generation/{id}/background` - Continue in background
- `POST /generation/{id}/refine` - Refinement chat
- `PATCH /generation/{id}/name` - Update template name

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
