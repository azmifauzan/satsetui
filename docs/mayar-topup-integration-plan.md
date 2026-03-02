# Mayar Top-up Integration Plan

## Overview
This document outlines the plan to integrate the [Mayar Payment Gateway](https://docs.mayar.id/api-reference/introduction) for user credit top-ups in the SatsetUI platform. This integration will allow users to purchase additional credits, and provide administrators with tools to manage credit packages and monitor top-up activities.

## 1. Database & Models

### `CreditPackage` Model
Stores the configurable packages that users can purchase.
- **Fields:**
  - `id` (Primary Key)
  - `name` (String, e.g., "Basic Package")
  - `description` (Text, optional)
  - `credits` (Integer, the amount of credits to add)
  - `price` (Decimal/Integer, the price in IDR)
  - `is_active` (Boolean, to enable/disable packages)
  - `timestamps`
  - `deleted_at` (Soft Deletes, optional but recommended to preserve history)

### `TopupTransaction` Model
Records the top-up attempts and their statuses from Mayar.
- **Fields:**
  - `id` (Primary Key)
  - `user_id` (Foreign Key to `users`)
  - `credit_package_id` (Foreign Key to `credit_packages`, nullable in case package is deleted but history remains)
  - `amount` (Decimal/Integer, total amount paid in IDR)
  - `credits_added` (Integer)
  - `mayar_transaction_id` (String, the ID from Mayar)
  - `mayar_payment_link` (String, URL to redirect the user)
  - `status` (Enum: `pending`, `success`, `failed`, `expired`)
  - `paid_at` (Timestamp, when the payment was confirmed)
  - `timestamps`

## 2. Admin Panel Features

### A. Credit Package Management
- **Routes & Controller (`Admin\CreditPackageController`):**
  - Ability to Create, Read, Update, and Delete/Deactivate `CreditPackage` entries.
- **UI (`resources/js/pages/Admin/CreditPackages/`):**
  - List view with a table of all packages.
  - Modal or separate page for creating/editing packages (Name, Credits, Price, Active Status).

### B. Top-up Activity Monitoring
- **Routes & Controller (`Admin\TopupTransactionController`):**
  - Read-only list of all `TopupTransaction` records.
- **UI (`resources/js/pages/Admin/TopupTransactions/`):**
  - Data table showing User, Package Name, Amount, Credits, Status, Date.
  - Filtering by Status (`pending`, `success`, `failed`) and Date range.
  - Dashboard stats showing Total Revenue and Total Credits Sold.

## 3. User Facing Features

### Top-up Page (`resources/js/pages/Credits/Topup.vue`)
- Displays all active `CreditPackage` options (e.g., mapping them as pricing cards).
- User selects a package and clicks "Beli" (Buy).
- **Action:** Triggers an API call to the backend to initiate the top-up.

### Success/Failed Pages
- Users will be redirected from Mayar to a `success` or `failed` page on SatsetUI detailing if the transaction was processed.

## 4. Integration Flow (Backend)

### A. Initiate Payment
1. User selects a package on the Top-up page.
2. Request sent to `TopupController@initiate`.
3. Validate user and package (ensure package `is_active`).
4. Call Mayar API to generate a Payment Link.
    - Reference: `POST /payment/create` (Check exact docs for the single payment creation endpoint).
    - Map parameters: amount, customer details (from authenticated user), and return/redirect URLs.
5. Save the generated transaction in the `TopupTransaction` table with `status = pending`.
6. Return the `mayar_payment_link` to the frontend and redirect the user to Mayar's checkout page.

### B. Mayar Webhook (Callback)
Mayar will send a webhook asynchronously when a payment is successful.
1. Mayar hits `POST /api/webhooks/mayar`.
2. **Secure the webhook:** Validate the payload using Mayar's Webhook Secret token/Signature.
3. Identify the transaction using the `mayar_transaction_id` or `reference_id` passed in the payload.
4. If status is `SUCCESS` / `PAID`:
   - Find matching `TopupTransaction`.
   - Ensure it is currently `pending` to prevent double-crediting.
   - Update `status` to `success` and set `paid_at`.
   - Call `CreditService->addCredits()` to add the purchased credits to the user's balance.
   - Record a `CreditTransaction` reflecting this top-up (type: `topup` or custom transaction type).
5. Respond with `200 OK` to acknowledge receipt to Mayar.

### C. Configuration
- Obtain **Mayar API Key** and **Webhook Secret** from the Mayar dashboard.
- Add to `.env`:
  ```env
  MAYAR_API_KEY=your_key_here
  MAYAR_WEBHOOK_SECRET=your_secret_here
  MAYAR_BASE_URL=https://api.mayar.id
  ```
- Configure the Webhook URL in the Mayar Dashboard pointing to `https://yourdomain.com/api/webhooks/mayar`.

## 5. Development Steps

1. **Migrations & Models:**
   - Run `php artisan make:model CreditPackage -m`
   - Run `php artisan make:model TopupTransaction -m`
   - Define schema and relationships.
2. **Admin Management Implementation:**
   - Create `CreditPackageController` and `TopupTransactionController`.
   - Create Admin UI Vue components for managing packages and monitoring transactions.
3. **Mayar Service Integration:**
   - Create `app/Services/MayarService.php` to handle API communication (Create Payment Link).
4. **User Top-up Flow:**
   - Implement `TopupController`.
   - Create User UI (`Topup.vue`).
5. **Webhook Implementation:**
   - Create `MayarWebhookController`.
   - Add route in `routes/web.php` or `routes/api.php` and exempt it from CSRF configuration in `bootstrap/app.php` (if using web routes).
   - Implement security verification and credit assignment logic.
6. **Testing:**
   - Write Pest Feature tests for credit package management.
   - Write Unit/Feature tests simulating Mayar webhook calls (Mocking `MayarService` and testing credit balance update).
   - End-to-end sandbox testing using Mayar's sandbox environment.

## 6. Security & Concurrency Considerations 
- **Webhook Verification:** Always verify webhook signatures from Mayar to prevent unauthorized requests from simulating "success" calls.
- **Idempotency:** Webhooks can be fired multiple times. Always check if the `TopupTransaction` is already marked as `success` before adding credits.
- **Database Transactions:** Wrap webhook processing in a DB Transaction (`DB::transaction`) to avoid partial updates.
- **Row Locking:** Use `$transaction->lockForUpdate()` when querying the transaction in the webhook so concurrent requests don't process the same top-up twice.
