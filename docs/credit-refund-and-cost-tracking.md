# Credit Refund & Cost Tracking System - SatsetUI

## Overview

Sistem SatsetUI untuk menangani credit refund saat generation gagal dan tracking biaya LLM untuk analisa admin.

## Components

### 1. Database Tables

#### `generation_failures`
Records setiap kegagalan generation dengan detail lengkap untuk debugging dan analytics.

**Key Fields:**
- `failure_type`: Jenis kegagalan (generation_error, timeout, api_error, dll)
- `error_code`, `error_message`, `error_stack_trace`: Detail error
- `credits_refunded`, `credits_refunded_at`: Status refund
- `request_data`, `response_data`: Payload untuk debugging
- `additional_context`: Extra info (blueprint, page, dll)

#### `credit_transactions`
Complete audit trail untuk semua pergerakan credit.

**Transaction Types:**
- `charge`: Deduction untuk generation
- `refund`: Return credit saat generation gagal
- `topup`: User membeli credit
- `bonus`: Free credit (promo, etc)
- `adjustment`: Manual admin adjustment

**Key Fields:**
- `amount`: Jumlah credit (negative untuk deduction)
- `balance_before`, `balance_after`: Balance tracking
- `reference_type`, `reference_id`: Polymorphic relation
- `metadata`: Extra context (JSON)

#### `generation_costs`
Tracking actual cost ke LLM provider untuk profitability analysis.

**Key Fields:**
- `input_tokens`, `output_tokens`: Token usage
- `input_cost_usd`, `output_cost_usd`, `total_cost_usd`: Actual cost
- `credits_charged`: Credit yang dicharge ke user
- `profit_margin_percent`: Calculated profit margin
- `usd_to_local_rate`: Exchange rate untuk local currency

### 2. Services

#### `CreditService`

Handles all credit operations with transaction recording.

**Methods:**
- `charge(User, amount, Generation, description)`: Charge credits from user
- `refund(User, amount, Generation, GenerationFailure, reason)`: Refund credits on failure
- `addCredits(User, amount, type, description, metadata)`: Add credits (topup/bonus)
- `adminAdjustment(User, amount, adminUser, notes, metadata)`: Manual adjustment
- `getStatistics(days)`: Get credit statistics for admin

**Example:**
```php
// Charge credits
$creditService->charge($user, 100, $generation, "Template generation");

// Refund on failure
$creditService->refund($user, 100, $generation, $failure, "Generation failed at page 'home'");

// Get statistics
$stats = $creditService->getStatistics(30); // Last 30 days
// Returns: total_charges, total_refunds, total_topups, net_revenue, refund_rate, active_users
```

#### `CostTrackingService`

Tracks actual LLM costs and calculates profitability.

**Methods:**
- `recordCost(PageGeneration, Generation, User, modelName, provider, inputTokens, outputTokens, creditsCharged, processingTime)`: Record cost per page
- `getStatistics(days)`: Overall cost statistics
- `getMostExpensiveGenerations(limit)`: Top expensive generations
- `getModelsByProfitability()`: Models sorted by profit margin

**Example:**
```php
// Record cost setelah page generation
$costTrackingService->recordCost(
    $pageGeneration,
    $generation,
    $user,
    'gpt-4-turbo',
    'openai',
    1000, // input tokens
    2000, // output tokens
    50,   // credits charged
    5000  // processing time ms
);

// Get profitability analysis
$stats = $costTrackingService->getStatistics(30);
// Returns: total_cost_usd, total_credits_charged, profit_margin, cost_by_provider, cost_by_model
```

### 3. Integration in GenerationService

#### Automatic Credit Charging
```php
// In startGeneration()
$this->creditService->charge(
    $user,
    $requiredCredits,
    $generation,
    "Template generation: {$projectName}"
);
```

#### Automatic Cost Recording
```php
// After successful page generation
$this->costTrackingService->recordCost(
    $pageGeneration,
    $generation,
    $generation->user,
    $generation->model_used,
    $this->getProviderName($generation->model_used),
    $inputTokens,
    $outputTokens,
    $creditsChargedThisPage,
    $processingTime
);
```

#### Automatic Refund on Failure
```php
// In generateNextPage() catch block
$failure = GenerationFailure::create([
    'generation_id' => $generation->id,
    'user_id' => $generation->user_id,
    'failure_type' => 'generation_error',
    'error_message' => $e->getMessage(),
    // ... other fields
]);

if ($generation->credits_used > 0) {
    $this->creditService->refund(
        $generation->user,
        $generation->credits_used,
        $generation,
        $failure,
        "Generation failed at page '{$currentPage}': {$e->getMessage()}"
    );
}
```

## Admin Analytics

### Statistics Routes

```
GET /admin              - Admin dashboard (uses AdminStatisticsService for credit, cost, and failure stats)
GET /admin/generations  - Generation history (includes cost, failure, and refund info)
```

### Available Metrics

#### Credit Statistics
- Total credits charged (deductions)
- Total credits refunded
- Total credits topped up
- Net revenue (charges - refunds)
- Refund rate (% of failures)
- Active users count

#### Cost Statistics
- Total cost in USD and local currency
- Total credits charged
- Total tokens used (input/output)
- Average profit margin
- Cost breakdown by provider
- Cost breakdown by model
- Profitability analysis

#### Failure Statistics
- Total failures count
- Total credits refunded
- Failures by type
- Failures by model
- Top error messages

## Configuration

### LLM Provider Pricing

SatsetUI menggunakan 2 model types (satset & expert) yang dikonfigurasi admin via LLM Models panel. Pricing di `CostTrackingService` memiliki fallback defaults per provider/model, namun admin dapat override via database. Actual model yang digunakan tergantung konfigurasi admin (`LlmModel` fields: `model_type`, `provider`, `model_name`, `api_key`, `base_url`, `base_credits`, `is_active`).

Fallback pricing contoh di `CostTrackingService`:

```php
$pricing = [
    'openai' => [
        'gpt-4' => ['input_price_per_million' => 30.00, 'output_price_per_million' => 60.00],
        'gpt-4-turbo' => ['input_price_per_million' => 10.00, 'output_price_per_million' => 30.00],
        'gpt-3.5-turbo' => ['input_price_per_million' => 0.50, 'output_price_per_million' => 1.50],
    ],
    'anthropic' => [
        'claude-3-opus' => ['input_price_per_million' => 15.00, 'output_price_per_million' => 75.00],
        'claude-3-sonnet' => ['input_price_per_million' => 3.00, 'output_price_per_million' => 15.00],
    ],
    'google' => [
        'gemini-2.0-flash-exp' => ['input_price_per_million' => 0.075, 'output_price_per_million' => 0.30],
        'gemini-1.5-pro' => ['input_price_per_million' => 1.25, 'output_price_per_million' => 5.00],
    ],
];
```

Admin dapat override pricing via database:
```php
AdminSetting::create([
    'key' => 'pricing_openai_gpt-4',
    'value' => json_encode([
        'input_price_per_million' => 30.00,
        'output_price_per_million' => 60.00,
    ]),
]);
```

### Exchange Rates

Configure USD to local currency rate:
```php
AdminSetting::create([
    'key' => 'usd_to_local_rate',
    'value' => '15000', // 1 USD = 15,000 IDR
]);
```

### Credit Value

Configure berapa IDR per credit:
```php
AdminSetting::create([
    'key' => 'credit_value_local',
    'value' => '100', // 1 credit = 100 IDR
]);
```

## Example Usage Scenarios

### Scenario 1: Normal Generation (Success)
1. User starts generation → Credits charged via `CreditService::charge()`
2. Each page generates → Cost tracked via `CostTrackingService::recordCost()`
3. Generation completes → User keeps charged credits
4. Admin can see: Total revenue, actual cost, profit margin

### Scenario 2: Generation Failure
1. User starts generation → Credits charged
2. Page 1-3 succeed → Costs recorded for those pages
3. Page 4 fails → `GenerationFailure` created
4. Credits refunded → `CreditService::refund()` called
5. User gets credits back, failure recorded for analysis
6. Admin can see: Failure reason, refund amount, partial costs incurred

### Scenario 3: Admin Manual Adjustment
```php
// Admin adds bonus credits
$creditService->addCredits(
    $user,
    500,
    CreditTransaction::TYPE_BONUS,
    "New year promotion bonus",
    ['promotion' => 'new-year-2025']
);

// Admin manual adjustment (e.g., compensation)
$creditService->adminAdjustment(
    $user,
    100,
    $adminUser,
    "Compensation for service downtime",
    ['incident_id' => 'INC-2025-001']
);
```

## Testing

Run tests:
```bash
php artisan test
```

All services have integrated error handling and transaction safety (DB transactions).

## Future Enhancements

1. **Real-time Notifications**: Notify users when refund happens
2. **Batch Refunds**: Admin tool to refund multiple failed generations
3. **Cost Alerts**: Alert when profit margin drops below threshold
4. **Provider Comparison**: Compare cost/quality across providers
5. **Predictive Analytics**: ML to predict failure likelihood
6. **Dynamic Pricing**: Adjust credit pricing based on actual costs

## Model Relationships

```
Generation
├── failures (hasMany GenerationFailure)
├── creditTransactions (hasMany CreditTransaction)
└── costs (hasMany GenerationCost)

User
├── generations (hasMany Generation)
├── creditTransactions (hasMany CreditTransaction)
└── failures (hasMany GenerationFailure)

GenerationFailure
├── generation (belongsTo)
├── user (belongsTo)
└── pageGeneration (belongsTo)

CreditTransaction
├── user (belongsTo)
├── generation (belongsTo)
├── adminUser (belongsTo User)
└── reference (morphTo) // Can be Generation, GenerationFailure, etc

GenerationCost
├── generation (belongsTo)
├── pageGeneration (belongsTo)
└── user (belongsTo)
```
