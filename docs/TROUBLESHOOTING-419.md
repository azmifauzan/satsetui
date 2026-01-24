# Troubleshooting: Error 419 pada Login

## Problem
Error 419 "Page Expired" terjadi saat mencoba login dengan kredensial admin.

## Root Cause
Error 419 di Laravel biasanya disebabkan oleh masalah CSRF token atau session. Penyebab umum:

1. **SESSION_SECURE_COOKIE = true** pada development (HTTP)
2. Session driver tidak berfungsi dengan baik
3. Cookie tidak bisa di-set oleh browser
4. CSRF token expired atau tidak valid

## Solution

### 1. Pastikan Environment Variables Correct

Buka file `.env` dan pastikan konfigurasi berikut ada:

```env
# Session Configuration
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=false
SESSION_SAME_SITE=lax
```

**PENTING**: `SESSION_SECURE_COOKIE=false` harus di-set untuk development di localhost (HTTP).

### 2. Clear All Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### 3. Restart Development Server

Jika menggunakan `php artisan serve`:
```bash
# Stop server (Ctrl+C)
php artisan serve
```

Jika menggunakan Docker/Sail:
```bash
./vendor/bin/sail down
./vendor/bin/sail up -d
```

### 4. Clear Browser Cache & Cookies

Opsi 1 - Hard Refresh:
- Chrome/Edge: `Ctrl + Shift + R`
- Firefox: `Ctrl + F5`

Opsi 2 - Clear Cookies:
1. Buka Developer Tools (F12)
2. Application/Storage tab
3. Clear Storage atau delete cookies untuk localhost

### 5. Test Login Kembali

Kredensial default admin:
```
Email: admin@satsetui.com
Password: admin123
```

## Verification Steps

### 1. Check Session Table

Pastikan tabel sessions ada dan bisa diakses:

```bash
php artisan tinker
```

Kemudian run:
```php
DB::table('sessions')->count();
// Seharusnya return angka (0 atau lebih)
```

### 2. Test CSRF Token

Buka halaman login di browser, kemudian cek di Developer Tools Console:

```javascript
// Check CSRF token exists
document.querySelector('meta[name="csrf-token"]').content
// Seharusnya return string token panjang
```

### 3. Check Session Cookie

Setelah buka halaman login, cek di Developer Tools → Application → Cookies:
- Seharusnya ada cookie dengan nama seperti `laravel-session` atau `template-aspri-session`
- Cookie harus memiliki value (string panjang)

## Alternative Solution: Use File Driver untuk Testing

Jika masih error dengan database session, coba gunakan file driver temporary:

```env
SESSION_DRIVER=file
```

Kemudian:
```bash
php artisan config:clear
```

File driver lebih simple dan good untuk debugging.

## Common Issues & Fixes

### Issue 1: "CSRF token mismatch"
**Solusi**: Clear browser cookies dan hard refresh (Ctrl+Shift+R)

### Issue 2: Session table tidak ada
**Solusi**: 
```bash
php artisan session:table
php artisan migrate
```

### Issue 3: Permission denied pada session files (file driver)
**Solusi**:
```bash
# Windows
icacls "storage\framework\sessions" /grant Everyone:F /t

# Linux/Mac
chmod -R 775 storage/framework/sessions
chown -R www-data:www-data storage/framework/sessions
```

### Issue 4: PostgreSQL connection issue
**Solusi**: Pastikan PostgreSQL service running dan .env config benar
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=template_aspri
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

## Quick Fix Command (All-in-One)

Run semua command sekaligus:

```bash
php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear
```

Kemudian restart server dan clear browser cache.

## If All Fails

### Debugging Steps:

1. **Check Laravel Log**:
```bash
# Windows
Get-Content storage\logs\laravel.log -Tail 50

# Linux/Mac
tail -f storage/logs/laravel.log
```

2. **Enable Debug Mode**:
Di `.env`:
```env
APP_DEBUG=true
```

3. **Test with Postman/Insomnia**:
- GET `/login` dulu untuk dapat CSRF token
- Extract token dari response header atau cookie
- POST `/login` dengan body:
```json
{
  "email": "admin@satsetui.com",
  "password": "admin123"
}
```
Dan header:
```
X-CSRF-TOKEN: [token dari GET request]
```

## Production Notes

**JANGAN LUPA** untuk production:
```env
SESSION_SECURE_COOKIE=true  # Force HTTPS
SESSION_SAME_SITE=strict     # Stronger security
APP_DEBUG=false              # Hide error details
```

## Contact

Jika issue masih berlanjut setelah semua langkah di atas, kemungkinan ada issue yang lebih deep. Check:
1. Laravel version compatibility
2. Inertia.js version
3. Browser compatibility (try different browser)
4. Network issues (proxy, firewall, antivirus)
