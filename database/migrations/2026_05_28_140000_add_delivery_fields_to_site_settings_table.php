<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->decimal('delivery_fee', 10, 2)->default(7.99)->after('footer_note');
            $table->decimal('free_shipping_threshold', 10, 2)->default(120)->after('delivery_fee');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['delivery_fee', 'free_shipping_threshold']);
        });
    }
};
