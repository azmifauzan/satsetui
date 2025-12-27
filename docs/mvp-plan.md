# MVP Development Plan

## Project Overview

**Goal**: Launch a functional wizard-driven frontend template generator that produces deterministic, high-quality Vue.js templates.

**Core Value Proposition**: Replace unpredictable prompt-to-design tools with a structured, repeatable, wizard-based configuration system.

**Success Criteria**:
- Users can complete wizard in <5 minutes
- Same wizard selections produce identical output
- Generated templates are functional and deploy-ready

---

## MVP Boundaries

### In Scope (Must Have)

✅ **11-Step Wizard UI** (Vue.js)
- All steps defined in product-instruction.md
- Client-side state management (wizardState.ts)
- Step validation and conditional logic
- Progress indicator

✅ **Blueprint Generation** (Laravel)
- JSON Schema validation
- Storage in database (blueprints table)
- Blueprint → MCP prompt translation (McpPromptBuilder.php)

✅ **LLM Integration** (Laravel Service)
- Membership-aware model selection
- Free tier uses Gemini Flash
- Premium tier can choose admin-configured models
- MCP prompt assembly
- Response parsing and file extraction
- Error handling and retries

✅ **Billing (Premium Credits)**
- Premium users top up credits
- Each premium generation charges credits
- Admin-configurable markup percentage applied to premium charge

✅ **Admin Panel (MVP)**
- View usage statistics
- Configure premium models allow-list
- Configure markup percentage

✅ **Bilingual UI (ID/EN)**
- Wizard UI strings translatable
- Admin/billing strings translatable

✅ **Template Preview** (Vue.js)
- Display generated files in code viewer
- Syntax highlighting
- File tree navigation
- Download as ZIP

✅ **Basic Auth** (Laravel Breeze)
- User registration and login
- Dashboard to view saved blueprints
- Rate limiting (10 generations/hour)

✅ **Documentation**
- Product instruction (wizard specification)
- Architecture overview
- Copilot instructions
- Blueprint schema

### Out of Scope (Deferred to Post-MVP)

❌ **Multiple LLM Providers**: MVP uses Gemini only; add other providers later

❌ **Advanced billing analytics**: Cohort analysis, invoices, tax documents

❌ **Blueprint Presets**: No saved presets or templates library (manual selection each time)

❌ **Team Collaboration**: Single-user only (no sharing, comments, or permissions)

❌ **Version History**: No blueprint versioning or change tracking

❌ **Advanced Preview**: No live interactive preview (code view only, no iframe rendering)

❌ **Export Formats**: ZIP download only (no Git repo, Docker, or npm package)

❌ **CI/CD Integration**: No GitHub Actions, automated deployment, or testing

❌ **Component Customization**: No post-generation editing or visual tweaking

❌ **Analytics Dashboard**: No usage metrics, popular components, or insights

---

## Development Phases

### Phase 1: Foundation (Week 1)

**Goal**: Set up project structure and core wizard flow

**Tasks**:
1. Laravel + Vue + Inertia setup (Vite build)
2. Database migrations: `users`, `blueprints`, `templates`
3. Laravel Breeze authentication
4. Wizard container component (step navigation)
5. wizardState.ts implementation (all 11 steps)
6. Basic step components (placeholders)

**Deliverables**:
- Users can navigate through 11 wizard steps
- State persists across steps (no backend submission yet)
- Basic UI with Tailwind styling

**Validation**:
- ✅ Wizard navigation works (back, next, jump to step)
- ✅ State validation prevents invalid progression
- ✅ wizardState.ts matches JSON schema structure

---

### Phase 2: Blueprint System (Week 2)

**Goal**: Implement Blueprint validation and storage

**Tasks**:
1. JSON Schema definition (template-blueprint.schema.json)
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

### Phase 3: MCP Prompt Builder (Week 3)

**Goal**: Translate Blueprint to deterministic MCP prompt

**Tasks**:
1. McpPromptBuilder.php implementation
2. All 11 section builders (role, context, constraints, etc.)
3. Page-specific requirements logic
4. Unit tests for prompt assembly
5. Test fixtures for common blueprint scenarios

**Deliverables**:
- Blueprint → MCP conversion is deterministic
- MCP prompts are complete (no missing requirements)
- Edge cases handled (e.g., topbar navigation, no charts)

**Validation**:
- ✅ Same blueprint produces identical MCP (hash comparison)
- ✅ MCP includes all framework-specific instructions
- ✅ No placeholders or vague requirements in MCP

---

### Phase 4: LLM Integration (Week 4)

**Goal**: Connect to OpenAI API and generate templates

**Tasks**:
1. LlmService.php implementation (Gemini)
2. Environment configuration (API keys, rate limits)
3. Response parsing (extract code blocks, file structure)
4. Error handling (timeouts, invalid responses, rate limits)
5. Template model and storage (filesystem or S3)
6. TemplateProcessor.php (file extraction and storage)

7. Model selection
	- Free: force Gemini Flash
	- Premium: validate requested model is allowed by admin

8. Billing
	- Premium credit balance
	- Charge calculation = base cost + markup percentage
	- Deduct credits atomically on success

**Deliverables**:
- MCP prompt sent to OpenAI
- Response parsed into individual files
- Files stored with Blueprint association

**Validation**:
- ✅ LLM returns valid Vue components
- ✅ Response parsing handles multi-file output
- ✅ Error states handled gracefully (timeout, invalid JSON)

---

### Phase 5: Preview & Download (Week 5)

**Goal**: Display generated templates and allow download

**Tasks**:
1. TemplatePreview.vue component
2. Code viewer with syntax highlighting (Prism.js or Shiki)
3. File tree navigation
4. ZIP generation and download endpoint
5. Copy-to-clipboard per file
6. Loading states and error messages

**Deliverables**:
- Users can view generated code files
- Syntax highlighting for Vue, TypeScript, CSS
- Download as ZIP for local development

**Validation**:
- ✅ All generated files displayed in preview
- ✅ ZIP download includes correct file structure
- ✅ Syntax highlighting works for all file types

---

### Phase 6: Polish & Testing (Week 6)

**Goal**: Refine UX, add tests, and prepare for launch

**Tasks**:
1. Wizard step components (detailed implementation)
2. Form validation and error messages
3. Loading spinners and progress indicators
4. Rate limiting middleware (10 generations/hour)
5. Admin settings (markup %, premium models)
6. i18n integration (ID/EN) for wizard/admin/billing
5. Feature tests (wizard submission, blueprint generation)
6. Unit tests (McpPromptBuilder, validators)
7. Documentation finalization

**Deliverables**:
- Complete wizard flow with polished UI
- Comprehensive test coverage (>80%)
- User-facing documentation (README, guides)

**Validation**:
- ✅ All wizard steps functional and styled
- ✅ Test suite passes (feature + unit tests)
- ✅ No console errors or warnings

---

## Technical Stack Summary

### Backend
- **Framework**: Laravel 11.x
- **Database**: PostgreSQL (JSONB for blueprints)
- **Queue**: Laravel Queue (for async LLM calls)
- **Storage**: Local filesystem (MVP) → S3 (production)
- **Testing**: Pest

### Frontend
- **Framework**: Vue 3 (Composition API)
- **Language**: TypeScript
- **Build Tool**: Vite
- **UI Framework**: Tailwind CSS
- **Routing**: Inertia.js (Laravel-driven)
- **Syntax Highlighting**: Prism.js or Shiki

### External Services
- **LLM**: OpenAI GPT-4 (via REST API)
- **CDN**: None (MVP serves assets locally)

---

## Deployment Plan

### MVP Hosting (Week 7)

**Infrastructure**:
- **Web Server**: DigitalOcean App Platform or Heroku
- **Database**: Managed PostgreSQL
- **Queue Worker**: Laravel Horizon on same server
- **Storage**: S3-compatible object storage

**Environment**:
- **Domain**: template-generator.com (or similar)
- **SSL**: Let's Encrypt (automatic)
- **Monitoring**: Laravel Telescope (dev), Sentry (errors)

**Deployment Process**:
1. Push to `main` branch (GitHub)
2. Automated build and deploy (GitHub Actions)
3. Run migrations and seed data
4. Restart queue workers
5. Smoke tests (health check endpoint)

---

## Risk Mitigation

### Risk 1: LLM Output Quality

**Risk**: LLM generates invalid or non-functional code

**Mitigation**:
- Extensive MCP testing with real LLM calls
- Response validation (check for syntax errors)
- Fallback templates if LLM fails
- User feedback mechanism to report issues

### Risk 2: LLM API Costs

**Risk**: High API costs during development and testing

**Mitigation**:
- Use cheaper GPT-3.5 for development
- Cache identical blueprints (hash-based)
- Rate limit users (10 generations/hour)
- Monitor spending with alerts

### Risk 3: Wizard Complexity

**Risk**: 11 steps may overwhelm users

**Mitigation**:
- Progress indicator showing completion %
- "Suggested defaults" based on category
- Allow skipping to preview with defaults
- Tooltips and help text per step

### Risk 4: Determinism Failure

**Risk**: Same blueprint produces different outputs

**Mitigation**:
- Hash-based blueprint comparison in tests
- Seeded LLM API calls (if supported)
- Manual QA: Generate same blueprint 5 times, compare
- Comprehensive unit tests for McpPromptBuilder

---

## Post-MVP Roadmap

### Version 1.1 (Month 2)
- Multiple LLM providers (Anthropic, Cohere)
- Blueprint presets and templates library
- Live interactive preview (iframe with sandbox)

### Version 1.2 (Month 3)
- Team collaboration (sharing, permissions)
- Version history for blueprints
- Export to GitHub repository

### Version 1.3 (Month 4)
- Component customization (post-generation editing)
- Visual theme editor (color picker, font selector)
- Analytics dashboard (usage metrics, popular components)

### Version 2.0 (Month 6)
- Design system starter (component documentation)
- CLI tool for blueprint-based generation
- API for programmatic template generation

---

## Success Metrics

### Launch Goals (First 30 Days)

**User Metrics**:
- 100+ registered users
- 500+ blueprints created
- 300+ templates generated
- 70%+ wizard completion rate

**Technical Metrics**:
- 99%+ uptime
- <30s average generation time
- <5% LLM error rate
- 0 critical bugs

**Quality Metrics**:
- 4.5+ user satisfaction rating
- 80%+ templates used in real projects
- <10% support tickets per user

---

## Timeline Summary

| Phase | Duration | Key Deliverable |
|-------|----------|----------------|
| Phase 1: Foundation | Week 1 | Wizard navigation works |
| Phase 2: Blueprint System | Week 2 | Blueprint storage & validation |
| Phase 3: MCP Prompt Builder | Week 3 | Deterministic prompt generation |
| Phase 4: LLM Integration | Week 4 | Template generation working |
| Phase 5: Preview & Download | Week 5 | Code viewer & ZIP download |
| Phase 6: Polish & Testing | Week 6 | Complete MVP ready |
| Phase 7: Deployment | Week 7 | Live production system |

**Total MVP Timeline**: 7 weeks from start to production launch

---

## Conclusion

This MVP plan focuses on delivering a **functional, deterministic, and user-friendly** template generator in 7 weeks.

By constraining scope (single LLM, no collaboration, code-only preview), we can validate the core value proposition: **structured wizard beats unpredictable prompts**.

Post-MVP iterations will expand features based on user feedback and usage patterns.

The goal is not to build the most feature-rich tool, but the most **reliable and repeatable** template generator.

Wizard decides. Blueprint stores. MCP instructs. LLM implements. User downloads.

Simple. Fast. Deterministic.
