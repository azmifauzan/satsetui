# MVP Development Plan

## Project Overview

**Goal**: Launch a functional wizard-driven frontend template generator that produces deterministic, high-quality templates with per-page generation and history tracking.

**Core Value Proposition**: Replace unpredictable prompt-to-design tools with a structured, repeatable, wizard-based configuration system.

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
- Storage in database (blueprints table)
- Blueprint → Per-page MCP prompt translation (McpPromptBuilder.php)

✅ **Per-Page LLM Generation** (Laravel Service)
- Generate each page separately with focused context
- Progress tracking (X of Y pages)
- Error recovery (continue on single page failure)
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

✅ **Billing (Premium Credits)**
- Premium users top up credits
- Each premium generation charges credits with margins
- Admin-configurable margin percentages

✅ **Admin Panel (MVP)**
- View usage statistics
- View custom page statistics
- Configure premium models allow-list
- Configure error margin percentage
- Configure profit margin percentage
- View generation history (prompts/responses)

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

✅ **Basic Auth** (Laravel Breeze)
- User registration and login
- 25 credits on registration
- Dashboard to view saved blueprints
- Rate limiting (10 generations/hour)

✅ **Documentation**
- Product instruction (3-step wizard specification)
- Architecture overview (per-page generation)
- Copilot instructions (updated for 3 steps)
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

## Development Phases

### Phase 1: Foundation (Week 1)

**Goal**: Set up project structure and simplified wizard flow

**Tasks**:
1. Laravel + Vue + Inertia setup (Vite build)
2. Database migrations: `users`, `blueprints`, `generations`, `page_generations`
3. Laravel Breeze authentication
4. 3-step wizard container component
5. wizardState.ts implementation (3 steps)
6. Basic step components (placeholders)

**Deliverables**:
- Users can navigate through 3 wizard steps
- State persists across steps (no backend submission yet)
- Basic UI with Tailwind styling

**Validation**:
- ✅ Wizard navigation works (back, next, jump to step)
- ✅ State validation prevents invalid progression
- ✅ wizardState.ts matches JSON schema structure

---

### Phase 2: Blueprint & Schema (Week 2)

**Goal**: Implement Blueprint validation and storage

**Tasks**:
1. JSON Schema definition (template-blueprint.schema.json) - DONE
2. Laravel Form Request validation (StoreBlueprintRequest)
3. BlueprintValidator service (JSON Schema validation)
4. Blueprint model and relationships (User → Blueprints)
5. Blueprint CRUD API endpoints (store, show, index)
6. Dashboard page to list saved blueprints

**Deliverables**:
- Wizard submission creates Blueprint record
- Blueprint data validated against JSON schema
- Users can view/edit saved blueprints

**Validation**:
- ✅ Invalid blueprint data is rejected with clear errors
- ✅ Blueprint stored with correct user association
- ✅ JSON Schema catches all invalid inputs

---

### Phase 3: Per-Page MCP Builder (Week 3)

**Goal**: Translate Blueprint to per-page MCP prompts

**Tasks**:
1. McpPromptBuilder.php - buildForPage() method
2. All section builders (role, context, constraints, etc.)
3. Page-specific requirements logic
4. Auto-apply best defaults (interaction, responsiveness, codeStyle)
5. Unit tests for prompt assembly
6. Test fixtures for common page scenarios

**Deliverables**:
- Blueprint → Per-page MCP conversion is deterministic
- MCP prompts are complete (no missing requirements)
- Auto-selected values applied correctly

**Validation**:
- ✅ Same blueprint + page produces identical MCP
- ✅ MCP includes all framework-specific instructions
- ✅ No placeholders or vague requirements

---

### Phase 4: Per-Page LLM Integration (Week 4)

**Goal**: Generate templates page by page with progress tracking

**Tasks**:
1. GenerationService.php - per-page orchestration
2. LlmService.php - generatePage() method
3. Response parsing (extract code for single page)
4. Error handling per page (retry, continue on failure)
5. Progress tracking (current page / total pages)
6. Template storage per page (filesystem)

**Deliverables**:
- Each page generated with focused MCP prompt
- Progress visible to user during generation
- Failed pages don't stop entire generation

**Validation**:
- ✅ Each page generated separately
- ✅ Progress updates in real-time
- ✅ Single page failure doesn't abort others

---

### Phase 5: History Recording (Week 5)

**Goal**: Record all prompts and responses for learning

**Tasks**:
1. PageGeneration model and migration
2. GenerationHistoryService.php implementation
3. Record prompt, response, tokens, time per page
4. Credit estimation learning algorithm
5. API endpoint to fetch generation history
6. Admin view for generation history

**Deliverables**:
- Every prompt/response stored in database
- Token usage tracked per page
- Historical data available for analysis

**Validation**:
- ✅ All generations have complete history
- ✅ Token counts accurate
- ✅ History accessible via API

---

### Phase 6: Custom Page Statistics (Week 5)

**Goal**: Track custom pages for future wizard enhancements

**Tasks**:
1. CustomPageStatistic model and migration
2. CustomPageStatisticsService.php implementation
3. Normalize custom page names (lowercase, trim)
4. Count usage by page name and category
5. Admin dashboard for custom page statistics
6. Promotion candidates view

**Deliverables**:
- All custom pages recorded
- Usage counts available
- Popular pages identified

**Validation**:
- ✅ Custom pages tracked correctly
- ✅ Statistics accurate
- ✅ Admin can view popular pages

---

### Phase 7: Credit System with Margins (Week 6)

**Goal**: Implement credit calculation with configurable margins

**Tasks**:
1. AdminSetting model for margin configuration
2. BillingCalculator.php with margins
3. Credit breakdown display in wizard
4. Admin UI for margin configuration
5. Credit deduction on generation complete
6. Credit estimation from historical data

**Deliverables**:
- Credits calculated with error and profit margins
- Breakdown shown to user before generation
- Admin can configure margin percentages

**Validation**:
- ✅ Margins applied correctly
- ✅ Breakdown accurate
- ✅ Admin changes reflected immediately

---

### Phase 8: Preview & Download (Week 6)

**Goal**: Display generated templates with progress

**Tasks**:
1. TemplatePreview.vue component
2. GenerationProgress.vue (per-page progress)
3. Code viewer with syntax highlighting
4. File tree navigation
5. ZIP generation and download endpoint
6. Copy-to-clipboard per file

**Deliverables**:
- Users see progress as each page generates
- Generated code displayed with syntax highlighting
- Download as ZIP for local development

**Validation**:
- ✅ Progress shows correct page count
- ✅ All generated files displayed
- ✅ ZIP download works correctly

---

### Phase 9: Polish & Testing (Week 7)

**Goal**: Refine UX, add tests, and prepare for launch

**Tasks**:
1. All 3 wizard step components (detailed implementation)
2. Form validation and error messages
3. Loading spinners and progress indicators
4. Rate limiting middleware
5. i18n integration (ID/EN) for wizard/admin
6. Feature tests (wizard submission, generation)
7. Unit tests (McpPromptBuilder, BillingCalculator)
8. Documentation finalization

**Deliverables**:
- Complete wizard flow with polished UI
- Comprehensive test coverage (>80%)
- User-facing documentation

**Validation**:
- ✅ All wizard steps functional and styled
- ✅ Test suite passes (feature + unit tests)
- ✅ No console errors or warnings

---

## Database Schema Summary

### New Tables

```sql
-- Per-page generation history
CREATE TABLE page_generations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    generation_id BIGINT NOT NULL,
    page_name VARCHAR(100) NOT NULL,
    page_type ENUM('predefined', 'custom') NOT NULL,
    mcp_prompt TEXT NOT NULL,
    llm_response TEXT,
    input_tokens INT DEFAULT 0,
    output_tokens INT DEFAULT 0,
    processing_time_ms INT DEFAULT 0,
    status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    error_message TEXT,
    created_at TIMESTAMP,
    completed_at TIMESTAMP,
    FOREIGN KEY (generation_id) REFERENCES generations(id)
);

-- Custom page statistics
CREATE TABLE custom_page_statistics (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    page_name_normalized VARCHAR(100) NOT NULL,
    original_names JSON,
    category VARCHAR(50) NOT NULL,
    usage_count INT DEFAULT 1,
    first_used_at TIMESTAMP,
    last_used_at TIMESTAMP,
    UNIQUE KEY unique_page_category (page_name_normalized, category)
);

-- Admin settings
CREATE TABLE admin_settings (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    key VARCHAR(100) UNIQUE NOT NULL,
    value TEXT NOT NULL,
    type ENUM('string', 'integer', 'float', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Credit estimation learning
CREATE TABLE credit_estimations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    page_type VARCHAR(50) NOT NULL,
    category VARCHAR(50) NOT NULL,
    model_id VARCHAR(100) NOT NULL,
    avg_input_tokens INT DEFAULT 0,
    avg_output_tokens INT DEFAULT 0,
    sample_count INT DEFAULT 0,
    last_updated_at TIMESTAMP,
    UNIQUE KEY unique_estimation (page_type, category, model_id)
);
```

### Modified Tables

```sql
-- Add margins to generations
ALTER TABLE generations ADD COLUMN error_margin_percent DECIMAL(5,2) DEFAULT 10.00;
ALTER TABLE generations ADD COLUMN profit_margin_percent DECIMAL(5,2) DEFAULT 5.00;
ALTER TABLE generations ADD COLUMN credit_breakdown JSON;
```

---

## API Endpoints Summary

### Wizard & Blueprint
- `GET /wizard` - Wizard page (Inertia)
- `POST /api/blueprints` - Create blueprint
- `GET /api/blueprints` - List user blueprints
- `GET /api/blueprints/{id}` - Get blueprint details

### Generation
- `POST /api/generations` - Start generation
- `GET /api/generations/{id}/progress` - Get generation progress
- `GET /api/generations/{id}/history` - Get page generation history

### Admin
- `GET /admin/statistics` - Usage statistics
- `GET /admin/custom-pages` - Custom page statistics
- `GET /admin/settings` - Get admin settings
- `PUT /admin/settings` - Update admin settings

### LLM Models
- `GET /api/llm-models` - Get available models

---

## Risk Mitigation

### Technical Risks

| Risk | Mitigation |
|------|------------|
| LLM API instability | Per-page retry, partial success handling |
| Token estimation inaccuracy | Learning from historical data, error margin |
| Long generation times | Per-page progress, async processing |

### Business Risks

| Risk | Mitigation |
|------|------------|
| Credit estimation too low | Error margin (10%) + profit margin (5%) |
| Users confused by simplified wizard | Clear labels, helpful descriptions |
| Custom pages not tracked | Automatic recording, admin visibility |

---

## Success Metrics

1. **Wizard Completion**: >85% of users complete 3 steps
2. **Generation Success**: >95% of pages generate successfully
3. **Credit Accuracy**: Estimated within 15% of actual usage
4. **Time to Template**: <3 minutes (wizard) + ~30s per page (generation)
5. **User Satisfaction**: 4.5+ stars on ease of use
