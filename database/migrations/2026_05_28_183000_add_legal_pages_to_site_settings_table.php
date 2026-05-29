<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->longText('privacy_policy_content')->nullable()->after('free_shipping_threshold');
            $table->longText('terms_of_service_content')->nullable()->after('privacy_policy_content');
            $table->longText('shipping_policy_content')->nullable()->after('terms_of_service_content');
            $table->longText('refund_policy_content')->nullable()->after('shipping_policy_content');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'privacy_policy_content',
                'terms_of_service_content',
                'shipping_policy_content',
                'refund_policy_content',
            ]);
        });
    }
};
