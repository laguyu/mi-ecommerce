<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->string('banner_title', 160)->nullable()->after('status');
            $table->string('banner_subtitle', 220)->nullable()->after('banner_title');
            $table->string('banner_image_url', 255)->nullable()->after('banner_subtitle');
        });
    }

    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->dropColumn(['banner_title', 'banner_subtitle', 'banner_image_url']);
        });
    }
};
