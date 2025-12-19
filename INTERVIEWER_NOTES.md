# INTERVIEWER NOTES (Do not share with candidate)

## Expected Bugs to Identify

### Critical Issues (Must Find)

1. **No Idempotency Check**
   - Duplicate webhooks create duplicate payments
   - Should check if `provider_id` already exists before creating payment
   - Fix: `Payment::firstOrCreate(['provider_id' => $payload['transaction_id']], [...])`

2. **No Null Check on Order**
   - `$order` could be null if order_ref doesn't exist
   - Will crash with "Trying to get property of non-object"
   - Fix: Early return with 404 or proper error handling

3. **No Database Transaction**
   - Payment created, but if email/inventory/HTTP fails, data is inconsistent
   - Fix: Wrap in `DB::transaction()` or use try-catch with rollback

4. **Synchronous HTTP Call**
   - External HTTP call in webhook = timeout risk
   - Payment provider may retry, causing duplicates
   - Fix: Queue the accounting sync job

### Important Issues (Should Find)

5. **Synchronous Email**
   - Slow email = slow webhook response = retries
   - Fix: Queue the email notification

6. **N+1 Query on Products**
   - `foreach ($order->products as $product)` triggers N queries
   - Fix: `$order->load('products')` or eager load earlier

7. **Stock Can Go Negative**
   - No validation that stock >= quantity
   - Fix: Check before decrement, throw exception if insufficient

8. **Refund Doesn't Track Amount**
   - Just sets status to 'refunded'
   - Doesn't create refund payment record
   - Doesn't handle partial refunds (no partial_refund logic)

### Good to Notice (Bonus Points)

9. **No Webhook Signature Verification**
   - Anyone can send fake webhooks
   - Should verify using merchant's webhook_secret

10. **No Logging of Failures**
    - If something fails, hard to debug
    - Should log errors with context

11. **Amount Not Validated**
    - Webhook amount could differ from order amount
    - Should validate or at least log discrepancy

12. **Race Conditions**
    - Two webhooks at same time could cause issues
    - Could use database locks or unique constraints

---

## Evaluation Rubric

### Part 1: Bug Identification (10 mins) - Max 25 points

| Points | Criteria |
|--------|----------|
| 20-25 | Finds 6+ issues including critical ones, explains impact clearly |
| 15-19 | Finds 4-5 issues including most critical ones |
| 10-14 | Finds 2-3 obvious issues |
| 0-9 | Misses critical issues or can't explain impact |

### Part 2: Design Discussion (15 mins) - Max 25 points

**What to listen for:**

- [ ] Mentions separating webhook receipt from processing (queued jobs)
- [ ] Discusses idempotency strategy
- [ ] Brings up database transactions
- [ ] Mentions state machine for order status
- [ ] Discusses retry/failure handling
- [ ] Considers observability (logging, monitoring)

| Points | Criteria |
|--------|----------|
| 20-25 | Comprehensive design, mentions queues/transactions/idempotency, considers edge cases |
| 15-19 | Good design, covers most important aspects |
| 10-14 | Basic restructuring ideas, misses key patterns |
| 0-9 | No clear design vision or significantly flawed approach |

### Part 3: Implementation (20 mins) - Max 30 points

| Points | Criteria |
|--------|----------|
| 25-30 | Clean implementation, proper error handling, explains trade-offs |
| 18-24 | Working implementation with minor issues |
| 10-17 | Partial implementation, some bugs |
| 0-9 | Doesn't compile or fundamentally broken |

**Good choices for "most critical":**
- Idempotency (prevents duplicate payments - data integrity)
- Null check (prevents crashes - availability)
- Transaction wrapping (prevents inconsistent state - data integrity)

**Red flag if they pick:**
- Email queueing (optimization, not critical)
- N+1 (performance, not correctness)

### Part 4: Curveball - Installments (10 mins) - Max 20 points

**Good answers should mention:**
- Order status needs new state: `partially_paid`
- Need to track expected vs received amount
- `paid` status only when sum(payments) >= order amount
- Each payment.success creates separate Payment record
- Need to handle refund of single installment

| Points | Criteria |
|--------|----------|
| 16-20 | Quickly adapts solution, identifies state changes needed, handles edge cases |
| 10-15 | Understands the impact, proposes reasonable changes |
| 5-9 | Struggles to adapt, partial understanding |
| 0-4 | Cannot adapt or doesn't understand implications |

---

## Ideal Solution Reference

```php
class PaymentWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // 1. Validate webhook signature (security)
        // $this->verifySignature($request);
        
        $payload = $request->validate([
            'event' => 'required|string',
            'order_ref' => 'required|string',
            'transaction_id' => 'nullable|string',
            'amount' => 'nullable|integer',
            'refund_amount' => 'nullable|integer',
        ]);

        Log::info('Webhook received', ['event' => $payload['event'], 'order_ref' => $payload['order_ref']]);

        // 2. Find order with proper error handling
        $order = Order::where('external_reference', $payload['order_ref'])->first();
        
        if (!$order) {
            Log::warning('Order not found', ['order_ref' => $payload['order_ref']]);
            return response()->json(['error' => 'Order not found'], 404);
        }

        // 3. Dispatch to queue for processing (fast webhook response)
        match ($payload['event']) {
            'payment.success' => ProcessPaymentSuccess::dispatch($order, $payload),
            'payment.failed' => ProcessPaymentFailed::dispatch($order, $payload),
            'refund.processed' => ProcessRefund::dispatch($order, $payload),
            default => Log::warning('Unknown webhook event', $payload),
        };

        return response()->json(['status' => 'queued']);
    }
}

// Jobs/ProcessPaymentSuccess.php
class ProcessPaymentSuccess implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;
    
    public $tries = 3;
    public $backoff = [60, 300, 900];

    public function handle()
    {
        // Idempotency check
        $existingPayment = Payment::where('provider_id', $this->payload['transaction_id'])->first();
        if ($existingPayment) {
            Log::info('Duplicate webhook ignored', ['transaction_id' => $this->payload['transaction_id']]);
            return;
        }

        DB::transaction(function () {
            // Create payment record
            $payment = Payment::create([
                'order_id' => $this->order->id,
                'amount' => $this->payload['amount'],
                'provider_id' => $this->payload['transaction_id'],
                'status' => 'completed',
            ]);

            // Update order status
            $this->order->update(['status' => 'paid']);

            // Deduct inventory with validation
            foreach ($this->order->products as $product) {
                if ($product->stock < $product->pivot->quantity) {
                    throw new InsufficientStockException($product);
                }
                $product->decrement('stock', $product->pivot->quantity);
            }
        });

        // Queue async operations (outside transaction)
        Mail::to($this->order->customer->email)
            ->queue(new PaymentConfirmed($this->order));
        
        SyncToAccounting::dispatch($this->order, $this->payload['amount']);
    }
}
```

---

## Red Flags During Interview

- Can't explain why idempotency matters
- Doesn't mention database transactions
- Wants to "rewrite everything" instead of incremental fixes
- Can't prioritize - tries to fix everything at once
- Doesn't ask clarifying questions
- Copy-pastes AI-generated code without understanding it
- Can't adapt when requirements change (curveball)

## Green Flags

- Asks about expected volume/scale
- Mentions observability/monitoring
- Considers what happens when things fail
- Explains trade-offs in their choices
- Thinks about backward compatibility
- Can whiteboard/explain before coding
- Adapts quickly to installment requirement
