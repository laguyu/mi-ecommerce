<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['status', 'id'], 'orders_status_id_index');
            $table->index(['payment_method', 'id'], 'orders_payment_method_id_index');
            $table->index(['user_id', 'created_at'], 'orders_user_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_status_id_index');
            $table->dropIndex('orders_payment_method_id_index');
            $table->dropIndex('orders_user_created_at_index');
        });
    }
};