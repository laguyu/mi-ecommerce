<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('show_in_main_banner')->default(false)->after('is_featured');
            $table->unsignedSmallInteger('main_banner_order')->nullable()->after('show_in_main_banner');
            $table->boolean('show_in_home_carousel')->default(false)->after('main_banner_order');
            $table->unsignedSmallInteger('home_carousel_order')->nullable()->after('show_in_home_carousel');

            $table->index(['show_in_main_banner', 'main_banner_order']);
            $table->index(['show_in_home_carousel', 'home_carousel_order']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['show_in_main_banner', 'main_banner_order']);
            $table->dropIndex(['show_in_home_carousel', 'home_carousel_order']);
            $table->dropColumn([
                'show_in_main_banner',
                'main_banner_order',
                'show_in_home_carousel',
                'home_carousel_order',
            ]);
        });
    }
};
