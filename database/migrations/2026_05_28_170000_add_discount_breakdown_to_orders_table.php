<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('promotion_discount_amount', 10, 2)->default(0)->after('discount_amount');
            $table->decimal('coupon_discount_amount', 10, 2)->default(0)->after('promotion_discount_amount');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['promotion_discount_amount', 'coupon_discount_amount']);
        });
    }
};
