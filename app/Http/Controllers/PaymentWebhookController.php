<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Mail\PaymentConfirmed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PaymentWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();

        Log::info('Webhook received', $payload);

        $order = Order::where('external_reference', $payload['order_ref'])->first();

        if ($payload['event'] == 'payment.success') {
            $payment = new Payment();
            $payment->order_id = $order->id;
            $payment->amount = $payload['amount'];
            $payment->provider_id = $payload['transaction_id'];
            $payment->status = 'completed';
            $payment->save();

            $order->status = 'paid';
            $order->save();

            // Notify customer
            Mail::to($order->customer->email)->send(new PaymentConfirmed($order));

            // Update inventory
            foreach ($order->products as $product) {
                $product->stock = $product->stock - $product->pivot->quantity;
                $product->save();
            }

            // Sync to accounting system
            Http::post('https://accounting.internal/api/orders', [
                'order_id' => $order->id,
                'amount' => $payload['amount'],
            ]);
        }

        if ($payload['event'] == 'payment.failed') {
            $order->status = 'payment_failed';
            $order->save();
        }

        if ($payload['event'] == 'refund.processed') {
            $order->status = 'refunded';
            $order->save();
        }

        return response()->json(['status' => 'ok']);
    }
}
