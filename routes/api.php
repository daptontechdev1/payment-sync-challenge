<?php

use App\Http\Controllers\PaymentWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::post('/webhooks/payments', [PaymentWebhookController::class, 'handle']);

// Helper route to check orders (for debugging during assessment)
Route::get('/orders', function () {
    return \App\Models\Order::with(['customer', 'products', 'payments'])->get();
});

Route::get('/orders/{reference}', function ($reference) {
    return \App\Models\Order::with(['customer', 'products', 'payments'])
        ->where('external_reference', $reference)
        ->firstOrFail();
});
