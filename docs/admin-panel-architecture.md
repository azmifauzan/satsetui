# Arsitektur Halaman Admin - SatsetUI

## Overview

Halaman admin SatsetUI dirancang untuk memberikan kontrol penuh kepada administrator platform dalam mengelola sistem, pengguna, model LLM, dan konfigurasi platform. Panel admin terpisah dari halaman landing dan hanya dapat diakses oleh user dengan role admin.

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
- LLM API key
- LLM base URL
- Default model for free users
- API timeout settings

#### d. General Settings
- Platform name (SatsetUI)
- Platform description
- Support email
- Maintenance mode
- Registration enabled

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

---

## Technical Architecture

### Backend Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Admin/
│   │       ├── DashboardController.php
│   │       ├── UserManagementController.php
│   │       ├── LlmModelController.php
│   │       ├── SettingsController.php
│   │       └── GenerationHistoryController.php
│   └── Middleware/
│       └── AdminMiddleware.php
├── Services/
│   └── AdminStatisticsService.php
└── Models/
    └── AdminSetting.php
```

### Frontend Structure

```
resources/js/
├── pages/
│   └── Admin/
│       ├── Index.vue              # Dashboard
│       ├── Users/
│       │   └── Index.vue          # Users list
│       └── Models/
│           ├── Index.vue          # LLM models list
│           └── Edit.vue           # Edit model
└── components/
    └── admin/
        └── (admin components)
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
});
```

---

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

3. **Data Protection**
   - API keys never displayed in full
   - User passwords never shown
   - Sensitive settings encrypted

---

## Implementation Priority

### Phase 1 (MVP) - Completed ✅
1. ✅ Middleware admin
2. ✅ Dashboard admin (basic stats)
3. ✅ User management (view, edit credits)
4. ✅ LLM models management (CRUD)
5. ✅ Settings management (billing & generation)
6. ✅ Generation history

### Phase 2 - Pending ⏳
7. ⏳ Custom pages statistics
8. ⏳ Advanced charts & visualizations
9. ⏳ Bulk actions
10. ⏳ Export functionality

---

## Sat-set! 🚀
