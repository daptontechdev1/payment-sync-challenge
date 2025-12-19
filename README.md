# Payment Sync System - Technical Assessment

## ⏱ Time: 45-60 minutes

---

## Scenario

A payment processor sends webhooks to sync order payment statuses. The current implementation is **buggy and incomplete**. 

The client is complaining about:
1. **Duplicate payments** being recorded
2. Orders stuck in **"processing" state forever**
3. **Refunds not reflecting** correctly
4. System **slows down drastically** at month-end (high volume)

Your job: **Debug, fix, and extend this system.**

---

## Setup Instructions

### Option A: Automated Setup (Recommended)

```bash
# Clone the repository
git clone <repo-url>
cd payment-sync-challenge

# Run the setup script
chmod +x setup.sh
./setup.sh

# Start the server
php artisan serve
```

### Option B: Manual Setup (Fresh Laravel Install)

```bash
# Create fresh Laravel project
composer create-project laravel/laravel payment-sync-challenge
cd payment-sync-challenge

# Copy the challenge files from this repo:
# - app/Http/Controllers/PaymentWebhookController.php
# - app/Models/*.php
# - app/Mail/PaymentConfirmed.php
# - database/migrations/*.php
# - database/seeders/DatabaseSeeder.php
# - routes/api.php
# - resources/views/emails/payment-confirmed.blade.php

# Configure database in .env (SQLite for simplicity)
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite

# Create SQLite database
touch database/database.sqlite

# Run migrations and seed data
php artisan migrate:fresh --seed

# Start server
php artisan serve
```

---

## Database Schema

```
merchants: id, name, api_key, webhook_secret
customers: id, merchant_id, name, email
products: id, merchant_id, name, price, stock
orders: id, merchant_id, customer_id, external_reference, amount, status, created_at
payments: id, order_id, amount, provider_id, status, created_at
order_product: order_id, product_id, quantity
```

**Order Statuses:** `pending`, `processing`, `paid`, `payment_failed`, `refunded`, `partially_refunded`

---

## Webhook Endpoint

```
POST /api/webhooks/payments
```

### Webhook Payloads

**payment.success**
```json
{
    "event": "payment.success",
    "order_ref": "ORD-12345",
    "transaction_id": "txn_abc123",
    "amount": 15000,
    "currency": "USD",
    "timestamp": "2024-01-15T10:30:00Z"
}
```

**payment.failed**
```json
{
    "event": "payment.failed",
    "order_ref": "ORD-12345",
    "reason": "insufficient_funds",
    "timestamp": "2024-01-15T10:30:00Z"
}
```

**refund.processed**
```json
{
    "event": "refund.processed",
    "order_ref": "ORD-12345",
    "transaction_id": "ref_xyz789",
    "refund_amount": 5000,
    "original_transaction_id": "txn_abc123",
    "timestamp": "2024-01-15T10:30:00Z"
}
```

---

## Test Data (After Seeding)

| Order Reference | Amount | Status | Products |
|-----------------|--------|--------|----------|
| ORD-1001 | 25000 | pending | 2 items |
| ORD-1002 | 15000 | processing | 1 item |
| ORD-1003 | 50000 | paid | 3 items |
| ORD-1004 | 8000 | pending | 1 item |
| ORD-1005 | 120000 | processing | 5 items |

---

## Your Tasks

### Part 1: Bug Identification (10 mins)
Review `app/Http/Controllers/PaymentWebhookController.php`

- What bugs and issues do you see?
- Walk through what happens when a webhook is received
- Identify potential failure scenarios

### Part 2: Design Discussion (15 mins)
- How would you restructure this system?
- What patterns would you apply?
- How would you handle failures and retries?

### Part 3: Implement Critical Fix (20 mins)
- Pick the **most critical issue** and implement a proper fix
- Explain why you prioritized this issue
- Consider backward compatibility

### Part 4: Curveball 🎯
> "Product team just told us: customers can now pay in **installments**. A single order can have multiple `payment.success` webhooks (30% now, 70% later). How does this affect your solution?"

---

## Testing Webhooks

```bash
# Test payment success
curl -X POST http://localhost:8000/api/webhooks/payments \
  -H "Content-Type: application/json" \
  -d '{
    "event": "payment.success",
    "order_ref": "ORD-1001",
    "transaction_id": "txn_test_001",
    "amount": 25000,
    "currency": "USD",
    "timestamp": "2024-01-15T10:30:00Z"
  }'

# Test duplicate webhook (send same request again)
# What happens?

# Test refund
curl -X POST http://localhost:8000/api/webhooks/payments \
  -H "Content-Type: application/json" \
  -d '{
    "event": "refund.processed",
    "order_ref": "ORD-1003",
    "transaction_id": "ref_test_001",
    "refund_amount": 10000,
    "original_transaction_id": "txn_original",
    "timestamp": "2024-01-15T10:30:00Z"
  }'

# Test with non-existent order
curl -X POST http://localhost:8000/api/webhooks/payments \
  -H "Content-Type: application/json" \
  -d '{
    "event": "payment.success",
    "order_ref": "ORD-INVALID",
    "transaction_id": "txn_test_002",
    "amount": 5000,
    "currency": "USD",
    "timestamp": "2024-01-15T10:30:00Z"
  }'
```

---

## Evaluation Criteria

| Area | What We're Looking For |
|------|------------------------|
| **Bug Detection** | Can you identify subtle issues, not just obvious ones? |
| **Prioritization** | Do you fix the right things first? |
| **System Design** | Do you think about queues, transactions, idempotency? |
| **Code Quality** | Clean, readable, maintainable solutions |
| **Communication** | Can you explain your thought process clearly? |
| **Adaptability** | How do you handle changing requirements? |

---

## Files to Review

```
app/Http/Controllers/PaymentWebhookController.php  <- Main buggy code
app/Models/Order.php
app/Models/Payment.php
app/Models/Product.php
app/Models/Customer.php
app/Models/Merchant.php
```

Good luck! 🚀
