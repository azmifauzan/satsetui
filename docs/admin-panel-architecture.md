# Arsitektur Halaman Admin

## Overview

Halaman admin dirancang untuk memberikan kontrol penuh kepada administrator platform dalam mengelola sistem, pengguna, model LLM, dan konfigurasi platform. Panel admin terpisah dari halaman landing dan hanya dapat diakses oleh user dengan role admin.

## Prinsip Desain

### 1. Akses Terbatas
- Admin panel hanya dapat diakses oleh user dengan `is_admin = true`
- Menggunakan middleware `admin` untuk proteksi route
- Tidak ada link dari landing page ke admin panel
- Akses melalui URL langsung: `/admin`

### 2. Bilingual & Dark Mode
- **WAJIB**: Semua teks menggunakan sistem i18n (`useI18n()`)
- **DEFAULT**: Bahasa Indonesia (`id`)
- **WAJIB**: Support dark mode dengan Tailwind `dark:` variants
- **DEFAULT**: Light theme

### 3. Responsive & User-Friendly
- Layout konsisten dengan `AppLayout.vue`
- Sidebar navigation untuk menu admin
- Real-time statistics dan charts
- Toast notifications untuk feedback

## Struktur Halaman Admin

### 1. Dashboard Admin (`/admin`)
**Tujuan**: Overview statistik sistem secara keseluruhan

**Statistik yang ditampilkan**:
- **Users**
  - Total users (semua)
  - Premium users
  - Free users
  - New users (30 hari terakhir)
  - Active users (7 hari terakhir)
  
- **Templates & Generations**
  - Total generations
  - Completed generations
  - Failed generations
  - In-progress generations
  - Templates generated per kategori
  
- **Credits & Costing**
  - Total credits issued
  - Total credits used
  - Total revenue (rupiah estimation)
  - Average credits per generation
  - Credit distribution chart
  
- **LLM Models**
  - Total models configured
  - Active models
  - Most used model
  - Model usage distribution
  
- **System Health**
  - Queue jobs pending
  - Failed jobs (last 24h)
  - Average generation time
  - Error rate

**Visualisasi**:
- Line chart: Generations per day (7 hari terakhir)
- Pie chart: Template categories distribution
- Bar chart: Model usage comparison
- Area chart: Credits usage trend

### 2. User Management (`/admin/users`)
**Tujuan**: Mengelola akun pengguna

**Fitur**:
- List semua users dengan pagination
- Filter: Premium/Free, Active/Inactive
- Search by name/email
- Actions per user:
  - View user details
  - Edit credits
  - Toggle premium status
  - Suspend/Activate account
  - View user's generations
- Bulk actions:
  - Export to CSV
  - Bulk credit adjustment

**Data ditampilkan**:
- Name, Email
- Credits balance
- Premium status
- Total generations
- Last login
- Registration date
- Actions

### 3. LLM Models Management (`/admin/models`)
**Tujuan**: Mengelola konfigurasi model LLM

**Fitur**:
- List semua LLM models
- Add new model
- Edit model configuration
- Toggle active/inactive
- Reorder models (sort_order)
- Delete model
- Bulk actions

**Data per model**:
- Name (identifier)
- Display name (user-facing)
- Description
- Input price per million tokens
- Output price per million tokens
- Estimated credits per generation
- Context length
- Is free (for non-premium users)
- Is active
- Sort order

**Form fields**:
```typescript
interface LlmModelForm {
  name: string; // e.g., "gemini-2.0-flash"
  display_name: string; // e.g., "Gemini 2.0 Flash"
  description: string;
  input_price_per_million: number; // USD
  output_price_per_million: number; // USD
  estimated_credits_per_generation: number;
  context_length: number; // tokens
  is_free: boolean;
  is_active: boolean;
  sort_order: number;
}
```

### 4. Settings Management (`/admin/settings`)
**Tujuan**: Konfigurasi platform secara keseluruhan

**Sections**:

#### a. Billing Settings
- Error margin (%) - default: 10%
- Profit margin (%) - default: 5%
- Base credits per generation
- Extra page credits multiplier
- Extra component credits multiplier
- USD to IDR exchange rate

#### b. Generation Settings
- Max concurrent generations per user
- Max pages per generation
- Max components per page
- Generation timeout (seconds)
- Queue driver preference

#### c. LLM API Settings
- OpenAI API key
- Google AI API key
- Other LLM provider keys
- Default model for free users
- API timeout settings

#### d. Email Settings
- SMTP configuration
- Email notifications enabled
- Admin notification email

#### e. General Settings
- Platform name
- Platform description
- Support email
- Maintenance mode
- Registration enabled

**Implementation**:
- Menggunakan `AdminSetting` model
- Settings di-cache untuk performance
- Validation untuk setiap setting type
- Grouping untuk organization

### 5. Generation History (`/admin/generations`)
**Tujuan**: Monitoring semua generation yang terjadi

**Fitur**:
- List semua generations dengan pagination
- Filter: Status (completed/failed/in-progress), Date range, User, Model
- Search by user name/email
- View generation details:
  - Blueprint JSON
  - MCP Prompt
  - Generated content
  - Error messages (jika failed)
  - Credit breakdown
  - Processing time
- Actions:
  - View full generation
  - Download generated files
  - Retry failed generation
  - Refund credits (jika failed)
  
**Data ditampilkan**:
- User name
- Model used
- Status
- Credits used
- Pages generated
- Started at
- Completed at
- Processing time
- Actions

### 6. Custom Page Statistics (`/admin/custom-pages`)
**Tujuan**: Analisis custom pages untuk promosi

**Fitur**:
- List custom pages dengan usage count
- Sort by usage (most popular)
- Filter by category
- Actions:
  - Promote to predefined page
  - View example generations
  - Mark as featured

**Data ditampilkan**:
- Page name
- Category
- Usage count
- Average rating (future)
- Created by (users)
- Actions

### 7. System Logs (`/admin/logs`)
**Tujuan**: Monitoring dan debugging

**Fitur**:
- View Laravel logs
- Filter by level (error, warning, info)
- Filter by date
- Search logs
- Download logs
- Clear old logs

## Technical Architecture

### Backend Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Admin/
│   │       ├── DashboardController.php      # Admin dashboard
│   │       ├── UserManagementController.php # User management
│   │       ├── LlmModelController.php       # LLM model CRUD
│   │       ├── SettingsController.php       # Settings management
│   │       ├── GenerationHistoryController.php
│   │       ├── CustomPageStatsController.php
│   │       └── SystemLogsController.php
│   ├── Middleware/
│   │   └── AdminMiddleware.php              # Check is_admin
│   └── Requests/
│       └── Admin/
│           ├── UpdateLlmModelRequest.php
│           ├── UpdateSettingRequest.php
│           └── UpdateUserRequest.php
├── Services/
│   ├── AdminStatisticsService.php           # Dashboard stats
│   └── SystemHealthService.php              # System health checks
└── Models/
    └── AdminSetting.php                      # Existing
```

### Frontend Structure

```
resources/js/
├── pages/
│   └── Admin/
│       ├── Index.vue                        # Dashboard
│       ├── Users/
│       │   ├── Index.vue                    # Users list
│       │   ├── Show.vue                     # User detail
│       │   └── Edit.vue                     # Edit user
│       ├── Models/
│       │   ├── Index.vue                    # LLM models list
│       │   ├── Create.vue                   # Add model
│       │   └── Edit.vue                     # Edit model
│       ├── Settings/
│       │   └── Index.vue                    # Settings page
│       ├── Generations/
│       │   ├── Index.vue                    # Generations list
│       │   └── Show.vue                     # Generation detail
│       ├── CustomPages/
│       │   └── Index.vue                    # Custom pages stats
│       └── Logs/
│           └── Index.vue                    # System logs
├── components/
│   └── admin/
│       ├── StatCard.vue                     # Statistics card
│       ├── ChartCard.vue                    # Chart wrapper
│       ├── UsersTable.vue                   # Users table
│       ├── ModelsTable.vue                  # LLM models table
│       ├── GenerationsTable.vue             # Generations table
│       └── SettingsForm.vue                 # Settings form
└── lib/
    └── charts.ts                            # Chart.js utilities
```

### Routes Structure

```php
// routes/web.php

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');
    
    // User Management
    Route::resource('users', Admin\UserManagementController::class);
    Route::post('users/{user}/credits', [Admin\UserManagementController::class, 'adjustCredits'])->name('users.credits');
    Route::post('users/{user}/toggle-premium', [Admin\UserManagementController::class, 'togglePremium'])->name('users.toggle-premium');
    Route::post('users/{user}/toggle-status', [Admin\UserManagementController::class, 'toggleStatus'])->name('users.toggle-status');
    
    // LLM Models Management
    Route::resource('models', Admin\LlmModelController::class);
    Route::post('models/reorder', [Admin\LlmModelController::class, 'reorder'])->name('models.reorder');
    
    // Settings
    Route::get('settings', [Admin\SettingsController::class, 'index'])->name('settings.index');
    Route::post('settings', [Admin\SettingsController::class, 'update'])->name('settings.update');
    Route::post('settings/{key}/reset', [Admin\SettingsController::class, 'reset'])->name('settings.reset');
    
    // Generation History
    Route::get('generations', [Admin\GenerationHistoryController::class, 'index'])->name('generations.index');
    Route::get('generations/{generation}', [Admin\GenerationHistoryController::class, 'show'])->name('generations.show');
    Route::post('generations/{generation}/refund', [Admin\GenerationHistoryController::class, 'refund'])->name('generations.refund');
    Route::post('generations/{generation}/retry', [Admin\GenerationHistoryController::class, 'retry'])->name('generations.retry');
    
    // Custom Pages Statistics
    Route::get('custom-pages', [Admin\CustomPageStatsController::class, 'index'])->name('custom-pages.index');
    Route::post('custom-pages/{id}/promote', [Admin\CustomPageStatsController::class, 'promote'])->name('custom-pages.promote');
    
    // System Logs
    Route::get('logs', [Admin\SystemLogsController::class, 'index'])->name('logs.index');
    Route::post('logs/clear', [Admin\SystemLogsController::class, 'clear'])->name('logs.clear');
});
```

### Database Schema Updates

#### Users Table
```sql
ALTER TABLE users ADD COLUMN is_admin BOOLEAN DEFAULT FALSE;
ALTER TABLE users ADD COLUMN is_active BOOLEAN DEFAULT TRUE;
ALTER TABLE users ADD COLUMN suspended_at TIMESTAMP NULL;
```

## Security Considerations

1. **Authentication & Authorization**
   - Middleware `admin` checks `is_admin` flag
   - All admin routes protected
   - CSRF protection on all POST requests
   - Rate limiting on sensitive actions

2. **Input Validation**
   - All inputs validated via Form Requests
   - XSS protection on output
   - SQL injection protection via Eloquent
   - File upload restrictions (logs, exports)

3. **Audit Trail**
   - Log all admin actions
   - Track who changed what settings
   - Record credit adjustments
   - Monitor model configuration changes

4. **Data Protection**
   - Sensitive settings encrypted
   - API keys never displayed in full
   - User passwords never shown
   - PII handling compliant

## Performance Considerations

1. **Caching**
   - Cache AdminSettings for 1 hour
   - Cache dashboard statistics for 5 minutes
   - Cache model lists for 10 minutes
   - Clear cache on updates

2. **Pagination**
   - All lists paginated (25 items default)
   - Lazy loading for large datasets
   - Optimized queries with relationships

3. **Real-time Updates**
   - Use polling for dashboard stats (30s interval)
   - WebSocket for live generation monitoring (future)
   - Queue status updates

## Internationalization

Semua teks admin panel ditambahkan ke `i18n.ts`:

```typescript
admin: {
  dashboard: {
    title: string;
    statistics: string;
    users: string;
    templates: string;
    credits: string;
    models: string;
    systemHealth: string;
  };
  users: {
    title: string;
    totalUsers: string;
    premiumUsers: string;
    freeUsers: string;
    editCredits: string;
    // ... more keys
  };
  models: {
    title: string;
    addModel: string;
    editModel: string;
    // ... more keys
  };
  settings: {
    title: string;
    billingSettings: string;
    generationSettings: string;
    // ... more keys
  };
  // ... more sections
}
```

## Charts & Visualizations

Menggunakan **Chart.js** (sesuai constraint):

1. **Dashboard Charts**:
   - Line Chart: Generations trend
   - Pie Chart: Category distribution
   - Bar Chart: Model usage
   - Doughnut Chart: Credit distribution

2. **Chart Configuration**:
   - Responsive
   - Dark mode aware
   - Animated transitions
   - Interactive tooltips
   - Export to PNG

## Implementation Priority

### Phase 1 (MVP) - Priority High
1. ✅ Middleware admin
2. ✅ Dashboard admin (basic stats)
3. ✅ User management (view, edit credits)
4. ✅ LLM models management (CRUD)
5. ✅ Settings management (billing & generation)

### Phase 2 - Priority Medium
6. ⏳ Generation history (full)
7. ⏳ Custom pages statistics
8. ⏳ Advanced charts & visualizations
9. ⏳ Bulk actions

### Phase 3 - Priority Low
10. ⏳ System logs viewer
11. ⏳ Audit trail
12. ⏳ Export functionality
13. ⏳ Advanced filtering

## Testing Strategy

### Unit Tests
- AdminStatisticsService methods
- AdminMiddleware logic
- Credit adjustment calculations
- Setting validation

### Feature Tests
- Admin dashboard loading
- User management CRUD
- Model management CRUD
- Settings update
- Access control (non-admin blocked)
- Credit refund workflow

### Browser Tests (Manual)
- Responsive layout
- Dark mode
- Charts rendering
- Form validations
- Real-time updates

## Maintenance

1. **Regular Tasks**
   - Monitor system health metrics
   - Review failed generations weekly
   - Audit credit transactions monthly
   - Update LLM pricing quarterly
   - Clear old logs monthly

2. **Monitoring**
   - Track admin actions
   - Monitor API usage
   - Watch error rates
   - Review performance metrics

## Future Enhancements

1. **Advanced Features**
   - Role-based admin levels
   - Multi-admin support
   - API key management UI
   - Webhook configuration
   - A/B testing tools

2. **Analytics**
   - User behavior tracking
   - Conversion funnel
   - Retention metrics
   - Cohort analysis

3. **Automation**
   - Auto-scaling credits
   - Anomaly detection
   - Automated reports
   - Smart recommendations
