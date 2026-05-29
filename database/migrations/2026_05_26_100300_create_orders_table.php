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
            $table->string('order_number', 40)->unique();
            $table->string('customer_full_name', 120);
            $table->string('customer_email', 120);
            $table->string('customer_address', 255);
            $table->string('customer_city', 80);
            $table->string('customer_postal_code', 30);
            $table->string('coupon_code', 40)->nullable();
            $table->decimal('discount_rate', 5, 2)->default(0);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('shipping_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->string('status', 30)->default('pending');
            $table->timestamps();

            $table->index(['customer_email', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
