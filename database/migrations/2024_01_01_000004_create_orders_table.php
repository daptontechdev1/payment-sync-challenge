<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->string('external_reference')->unique();
            $table->unsignedInteger('amount'); // in cents
            $table->enum('status', [
                'pending',
                'processing',
                'paid',
                'payment_failed',
                'refunded',
                'partially_refunded'
            ])->default('pending');
            $table->timestamps();

            $table->index('external_reference');
            $table->index(['merchant_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
