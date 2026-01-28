# Payment Sync System - Technical Assessment

## ⏱ Time: 60-75 minutes

**AI Tools Allowed:** You may use ChatGPT, Claude, Copilot, or any AI assistant. We're evaluating HOW you use AI, not whether you avoid it.

---

## Setup Instructions

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

| Order Reference | Amount  | Status     | Products |
|-----------------|---------|------------|----------|
| ORD-1001        | 25000   | pending    | 2 items  |
| ORD-1002        | 15000   | processing | 1 item   |
| ORD-1003        | 50000   | paid       | 3 items  |
| ORD-1004        | 8000    | pending    | 1 item   |
| ORD-1005        | 120000  | processing | 5 items  |

---

## The Scenario

A payment processor sends webhooks to sync order payment statuses. The current implementation is **buggy and incomplete**.

The client is complaining about:
1. **Duplicate payments** being recorded
2. Orders stuck in **"processing" state** forever
3. **Refunds not reflecting** correctly
4. System **slows down drastically** at month-end (high volume)

---

## Your Tasks

### Part A: Bug Investigation (15 min)

Investigate and document the root cause of each issue listed above.

**Deliverable:** Add comments in the code OR create a `FINDINGS.md` file explaining:
- What's causing each bug
- Where in the code the problem exists

---

### Part B: Fix & Harden (25 min)

1. Fix all identified bugs
2. Ensure webhooks are processed **idempotently** (same webhook received twice = no duplicate effects)
3. Add proper error handling and validation
4. Optimize for performance

---

### Part C: Extend the System (15 min)

Add support for a **new webhook event** - partial payments:

```json
{
    "event": "payment.partial",
    "order_ref": "ORD-12345",
    "transaction_id": "txn_partial_001",
    "amount_received": 10000,
    "amount_remaining": 5000,
    "timestamp": "2024-01-15T10:30:00Z"
}
```

**Requirements:**
- Record the partial payment
- Order should remain in `processing` status
- When a subsequent `payment.success` arrives for the remaining amount, mark as `paid`
- Handle edge case: what if partial payments exceed the order total?

---

### Part D: Discussion (10 min with interviewer)

Be prepared to discuss:

1. **AI Usage:** Walk us through how you used AI. What did you prompt? What suggestions did you accept, reject, or modify? Why?

2. **Edge Case:** What happens if a `refund.processed` webhook arrives BEFORE the corresponding `payment.success`? How would you handle out-of-order webhook delivery?

3. **Scale:** The system needs to handle 100K webhooks/day at month-end. Beyond code optimization, what architectural changes would you consider?

---

## AI Usage Log (Required)

Create a file called `AI_USAGE.md` in the root directory. Track your AI interactions:

```markdown
# AI Usage Log

## Bug: Duplicate Payments
**Prompt:** [What you asked the AI]
**AI Suggestion:** [Brief summary of what it suggested]
**My Decision:** [What you actually implemented and why]

## Bug: Orders Stuck in Processing
**Prompt:** 
**AI Suggestion:** 
**My Decision:** 

## Performance Optimization
**Prompt:** 
**AI Suggestion:** 
**My Decision:** 

## New Feature: Partial Payments
**Prompt:** 
**AI Suggestion:** 
**My Decision:** 
```

**Why we ask for this:** We want to see your critical thinking. Did you verify the AI's suggestions? Did you adapt them to fit this specific codebase? This is more valuable than the code itself.

---

## Evaluation Criteria

| Area | What We're Looking For |
|------|------------------------|
| **Problem Solving** | Can you diagnose issues systematically? Do you understand the root cause, not just the symptoms? |
| **AI Usage** | Do you use AI as a tool or a crutch? Can you evaluate and adapt AI suggestions? |
| **Code Quality** | Clean, readable code. Proper Laravel patterns. Meaningful variable names. |
| **Edge Case Thinking** | Do you consider what could go wrong? Race conditions, out-of-order events, validation? |
| **Communication** | Can you explain your decisions clearly? |

---

## Submission

1. Push your changes to a new branch: `solution/[your-name]`
2. Ensure `AI_USAGE.md` is included
3. Ensure `FINDINGS.md` is included (or comments in code)

---

## Tips

- Don't spend too long on any single bug. If stuck for 10+ minutes, document what you tried and move on.
- It's okay if you don't finish everything. Quality > quantity.
- We care more about your thinking process than perfect code.
- Ask clarifying questions if something is unclear.

---

Good luck! 🚀
