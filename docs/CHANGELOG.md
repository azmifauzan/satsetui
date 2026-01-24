# Changelog - SatsetUI

All notable changes to SatsetUI will be documented in this file.

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
