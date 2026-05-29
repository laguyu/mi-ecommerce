<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('menu_background_color', 20)->default('#ffffff')->after('site_tagline');
            $table->string('menu_text_color', 20)->default('#111827')->after('menu_background_color');
            $table->string('menu_active_background_color', 20)->default('#111827')->after('menu_text_color');
            $table->string('menu_active_text_color', 20)->default('#ffffff')->after('menu_active_background_color');
            $table->string('button_background_color', 20)->default('#111827')->after('menu_active_text_color');
            $table->string('button_text_color', 20)->default('#ffffff')->after('button_background_color');
            $table->string('footer_background_color', 20)->default('#111827')->after('button_text_color');
            $table->string('footer_text_color', 20)->default('#e2e8f0')->after('footer_background_color');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'menu_background_color',
                'menu_text_color',
                'menu_active_background_color',
                'menu_active_text_color',
                'button_background_color',
                'button_text_color',
                'footer_background_color',
                'footer_text_color',
            ]);
        });
    }
};
