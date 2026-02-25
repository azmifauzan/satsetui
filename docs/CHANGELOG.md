# Changelog - SatsetUI

All notable changes to SatsetUI will be documented in this file.

## [2.3.0] - 2026-02-25

### Added
- **Live Preview Workspace**: Server-side workspace with Vite dev server for live preview
  - `WorkspaceService` manages workspace lifecycle (create, install deps, start dev server, cleanup)
  - `PreviewController` with setup, status, logs, proxy, stop, static serving endpoints
  - `PreviewSession` model tracking preview lifecycle (creating → installing → booting → running → stopped)
  - Preview proxy rewrites for assets, Vite client stub for HMR compatibility
  - Device switcher (desktop/tablet/mobile) in preview UI
- **Multi-File Framework Output**: Generate full project structures for JS frameworks
  - `ScaffoldGeneratorService` generates deterministic scaffold (package.json, vite config, router, layout)
  - `GenerationFile` model stores individual files per generation
  - Support for React (TSX), Vue (SFC), Svelte, Angular output formats
  - HTML+CSS static preview via iframe
- **File Management**: File tree navigation and per-file content viewing
  - `FileTree.vue` component for project file navigation
  - `LivePreview.vue` component for embedded preview
  - File download endpoints per generation
- **Admin Panel Complete**: All admin frontend pages implemented
  - `Admin/Models/Index.vue`, `Create.vue`, `Show.vue`, `Edit.vue`
  - `Admin/Settings/Index.vue` with grouped settings (billing, generation, email, notification)
  - `Admin/Generations/Index.vue`, `Show.vue` with filtering and detail view
  - `Admin/Users/Show.vue` user detail page
  - `AdminLayout.vue` dedicated admin panel layout
- **Landing Page**: Full component-based landing page
  - Navbar, HeroSection, FeaturesSection, HowItWorksSection, FaqSection, CtaSection, Footer

### Changed
- Database: Added `generation_files` table for multi-file storage
- Database: Added `preview_sessions` table with booting status
- Database: Added `file_path` and `file_type` columns to `page_generations`

### Documentation
- Updated all docs to match current codebase state
- Removed outdated references to old 6-model system
- Fixed admin email references to admin@satsetui.com

## [2.2.0] - 2026-02-16

### Changed
- **LLM Model Simplification**: Reduced from 6 models to **2 model types**
  - **Satset** (default: `gemini-2.0-flash-exp`) — 6 credits, cepat untuk prototyping
  - **Expert** (default: `gemini-2.5-pro-preview`) — 15 credits, kualitas premium
  - Model provider, name, API key, dan base URL admin-configurable (encrypted)
  - Removed free tier concept — semua user punya kredit
- **Registration Credits**: Increased from 25 to **100 credits** for new users
- **LLM Model Structure**: `model_type` field replaces `name`-based identification
  - Migration: `2026_02_11_160454` restructures for 3 types
  - Migration: `2026_02_13_100000` finalizes to 2 types (satset + expert)

### Added
- **Refinement Chat**: Post-generation editing via conversational refinement
  - `RefinementMessage` model with role (user/assistant), type, page context
  - `POST /generation/{id}/refine` endpoint
  - Full chat UI in `Generation/Show.vue`
  - Migration: `2026_02_13_143434` creates `refinement_messages` table
- **SSE Streaming**: Real-time `GET /generation/{id}/stream` (Server-Sent Events)
  - Live progress updates during generation
  - No polling required on client
- **Background Queue Generation**: `POST /generation/{id}/background` to continue in queue
  - `ProcessTemplateGeneration` job (30min timeout, 1 try)
  - `TemplateGenerationCompleted` database notification on finish
- **Generation Policy**: `GenerationPolicy` ensures users can only view their own generations
- **Telegram Notifications**: Admin notified via Telegram when new users register
  - `TelegramChannel` custom notification channel
  - `TelegramService` for bot messaging
  - `UserRegistered` notification
- **Email Verification**: Mandatory email verification before accessing features
  - Uses Laravel's `MustVerifyEmail` trait
  - Registration redirects to login (no auto-login)
  - Rate limited: 6 verification resends per minute
- **Project Info in Wizard**: Company/app/store branding fields for consistent output
  - `companyName`, `companyDescription`, `appName`, `storeName`, `storeDescription`
- **Chart Library Selection**: Chart.js or Apache ECharts in Step 2
- **Generation Naming**: Users can name templates in Step 3
  - `PATCH /generation/{id}/name` endpoint
- **Language Persistence**: `POST /language` saves user language preference to database

### Documentation
- Updated README.md with current feature set and accurate information
- Updated all docs/ files to reflect current 2-model system
- Corrected credit amounts, model names, and feature status

---

## [2.1.0] - 2026-01-25

### Changed
- **Rebranding**: Application renamed from "Template Generator" to **SatsetUI**
  - "Sat-set" is Indonesian slang meaning quick and efficient
  - Updated all documentation with new branding
  - Updated README, architecture docs, and instructions

### Added
- **Antigravity Instructions**: Created `.gemini/AGENTS.md` for Gemini AI assistance
- **Updated Copilot Instructions**: Comprehensive GitHub Copilot guidance

### Documentation
- Updated `README.md` with SatsetUI branding and current features
- Updated `docs/product-instruction.md` with SatsetUI references
- Updated `docs/architecture.md` with current implementation
- Updated `docs/llm-credit-system.md` with current version
- Updated `docs/mvp-plan.md` with implementation status
- Updated `docs/admin-panel-architecture.md`
- Updated `.github/copilot-instructions.md`
- Created `.gemini/AGENTS.md`

---

## [2.0.0] - 2025-12-31

### Added
- **Automatic Retry Mechanism**: System now automatically retries failed page generations up to 3 times before giving up
  - Specifically handles timeout errors (error code 524)
  - Exponential backoff: 1s, 2s, 4s between retries
  - Only refunds credits after all retry attempts exhausted
  - Logs all retry attempts for debugging

- **Previous Page Context**: Each page generation now includes context from the last 2 generated pages
  - Includes CSS classes, structure, and styling patterns
  - Ensures consistent naming conventions across pages
  - Creates more cohesive multi-page templates
  - Context limited to prevent prompt bloat

- **Strict Code-Only Output**: Enhanced prompts to ensure LLM returns only code
  - Multiple layers of instruction to prevent markdown wrapping
  - Eliminates "Here is..." or explanatory text
  - Direct output from `<!DOCTYPE html>` to `</html>`
  - Reduces post-processing needs

- **Raw Request/Response Tracking**: Complete audit trail in `generation_costs` table
  - Full JSON request payload stored
  - Complete LLM API response stored
  - Enables debugging and analysis
  - Supports compliance and cost optimization

- **Credit Refund System**: Automatic credit refunds on generation failures
  - Only triggers after retry attempts fail
  - Complete audit trail in `credit_transactions` table
  - Failure details recorded in `generation_failures` table
  - Admin can track refund patterns

- **Cost Tracking System**: Comprehensive LLM cost tracking
  - Actual USD costs from provider
  - Token usage (input/output)
  - Profit margin calculation
  - Provider/model comparison
  - Exchange rate tracking

### Changed
- `GenerationService::generateNextPage()` now accepts `$retryCount` parameter
- MCP prompts now include strict output formatting rules
- Page generation includes context from previous pages
- Error handling differentiates between retriable and fatal errors

### Fixed
- Timeout errors (524) no longer immediately refund credits
- `credit_transactions.description` field changed from varchar(255) to text
- `generation_costs` table properly initialized with all required columns
- Migration handles both fresh install and existing database scenarios

---

## [1.0.0] - 2025-12-29

### Added
- Initial release of Template Generator
- 3-step wizard UI (Framework, Design, LLM Model)
- Per-page template generation
- 6 LLM models (Gemini, GPT, Claude families)
- Credit system with margins
- Admin panel
- Bilingual support (Indonesian/English)
- Dark/Light theme
- User authentication
