# Payment Sync System - Technical Assessment

## ⏱ Time: 45-60 minutes
---

## Setup Instructions

### Automated Setup

```bash
# Clone the repository
git clone https://github.com/daptontechdev1/payment-sync-challenge.git
cd payment-sync-challenge

composer install
php artisan key:generate

# Start the server
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

## Scenario

A payment processor sends webhooks to sync order payment statuses. The current implementation is **buggy and incomplete**. 

The client is complaining about:
1. **Duplicate payments** being recorded
2. Orders stuck in **"processing" state forever**
3. **Refunds not reflecting** correctly
4. System **slows down drastically** at month-end (high volume)

Your job: **Debug, fix, and extend this system.**

---

Good luck! 🚀
