<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create a merchant
        $merchant = Merchant::create([
            'name' => 'Test Merchant',
            'api_key' => 'mk_test_' . Str::random(24),
            'webhook_secret' => 'whsec_' . Str::random(32),
        ]);

        // Create customers
        $customers = collect([
            ['name' => 'John Doe', 'email' => 'john@example.com'],
            ['name' => 'Jane Smith', 'email' => 'jane@example.com'],
            ['name' => 'Bob Wilson', 'email' => 'bob@example.com'],
        ])->map(fn($data) => Customer::create([
            'merchant_id' => $merchant->id,
            ...$data,
        ]));

        // Create products
        $products = collect([
            ['name' => 'Premium Widget', 'price' => 9900, 'stock' => 100],
            ['name' => 'Basic Gadget', 'price' => 4900, 'stock' => 250],
            ['name' => 'Deluxe Package', 'price' => 29900, 'stock' => 50],
            ['name' => 'Standard Service', 'price' => 7500, 'stock' => 500],
            ['name' => 'Enterprise Solution', 'price' => 99900, 'stock' => 20],
        ])->map(fn($data) => Product::create([
            'merchant_id' => $merchant->id,
            ...$data,
        ]));

        // Create orders with different statuses for testing
        $ordersData = [
            [
                'external_reference' => 'ORD-1001',
                'customer' => $customers[0],
                'amount' => 25000,
                'status' => 'pending',
                'products' => [
                    ['product' => $products[0], 'quantity' => 2],
                    ['product' => $products[1], 'quantity' => 1],
                ],
            ],
            [
                'external_reference' => 'ORD-1002',
                'customer' => $customers[1],
                'amount' => 15000,
                'status' => 'processing',
                'products' => [
                    ['product' => $products[3], 'quantity' => 2],
                ],
            ],
            [
                'external_reference' => 'ORD-1003',
                'customer' => $customers[0],
                'amount' => 50000,
                'status' => 'paid',
                'products' => [
                    ['product' => $products[2], 'quantity' => 1],
                    ['product' => $products[0], 'quantity' => 1],
                    ['product' => $products[1], 'quantity' => 2],
                ],
                'has_payment' => true,
            ],
            [
                'external_reference' => 'ORD-1004',
                'customer' => $customers[2],
                'amount' => 8000,
                'status' => 'pending',
                'products' => [
                    ['product' => $products[1], 'quantity' => 1],
                ],
            ],
            [
                'external_reference' => 'ORD-1005',
                'customer' => $customers[1],
                'amount' => 120000,
                'status' => 'processing',
                'products' => [
                    ['product' => $products[4], 'quantity' => 1],
                    ['product' => $products[2], 'quantity' => 1],
                ],
            ],
        ];

        foreach ($ordersData as $orderData) {
            $order = Order::create([
                'merchant_id' => $merchant->id,
                'customer_id' => $orderData['customer']->id,
                'external_reference' => $orderData['external_reference'],
                'amount' => $orderData['amount'],
                'status' => $orderData['status'],
            ]);

            // Attach products
            foreach ($orderData['products'] as $productData) {
                $order->products()->attach($productData['product']->id, [
                    'quantity' => $productData['quantity'],
                ]);
            }

            // Create payment if order is paid
            if (!empty($orderData['has_payment'])) {
                Payment::create([
                    'order_id' => $order->id,
                    'amount' => $orderData['amount'],
                    'provider_id' => 'txn_' . Str::random(16),
                    'status' => 'completed',
                ]);
            }
        }

        $this->command->info('Seeded:');
        $this->command->info('- 1 Merchant');
        $this->command->info('- 3 Customers');
        $this->command->info('- 5 Products');
        $this->command->info('- 5 Orders (ORD-1001 to ORD-1005)');
        $this->command->info('');
        $this->command->info('Test webhook with:');
        $this->command->info('curl -X POST http://localhost:8000/api/webhooks/payments -H "Content-Type: application/json" -d \'{"event":"payment.success","order_ref":"ORD-1001","transaction_id":"txn_test123","amount":25000,"currency":"USD","timestamp":"2024-01-15T10:30:00Z"}\'');
    }
}
