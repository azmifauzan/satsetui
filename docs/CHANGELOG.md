# Changelog

## [Unreleased] - 2025-12-31

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

### Technical Details

#### Retry Logic
```php
// In GenerationService::generateNextPage()
$maxRetries = 3;

if ($isTimeoutError && $retryCount < $maxRetries) {
    sleep(pow(2, $retryCount)); // Exponential backoff
    return $this->generateNextPage($generation, $retryCount + 1);
}
```

#### Context Building
```php
// buildPreviousPageContext() extracts:
- CSS classes from previous pages
- Head section (first 500 chars)
- Body structure preview
- Last 2 pages only (prevents prompt bloat)
```

#### Prompt Enhancement
```
=== CRITICAL OUTPUT REQUIREMENTS ===
- Return ONLY the complete, working code
- DO NOT include any explanations, comments, or markdown formatting
- DO NOT wrap code in ```html or ``` blocks
- Start directly with <!DOCTYPE html>
- End directly with </html>
```

### Database Schema

#### New Tables
- `generation_failures`: Records all generation failures with context
- `credit_transactions`: Complete audit trail for credit movements
- `generation_costs`: LLM provider cost tracking

#### Modified Tables
- `generation_costs`: Added `raw_request` and `raw_response` text fields
- `credit_transactions`: Changed `description` to text type

### Performance
- Retry mechanism adds 1-7 seconds per failed attempt (exponential backoff)
- Context extraction negligible (<10ms per page)
- Raw response storage adds ~5-50KB per page generation

### Migration Path
Run migrations:
```bash
php artisan migrate
```

No data migration needed. System handles both new installs and upgrades gracefully.
