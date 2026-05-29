<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name', 160)->default('Nova Shop');
            $table->string('site_tagline', 220)->default('Home con carrusel, catalogo, ficha de producto y checkout con Stripe/PayPal.');
            $table->string('logo_path', 500)->nullable();
            $table->text('footer_address')->nullable();
            $table->string('footer_phone', 80)->nullable();
            $table->string('footer_email', 160)->nullable();
            $table->string('footer_facebook_url', 500)->nullable();
            $table->string('footer_instagram_url', 500)->nullable();
            $table->string('footer_x_url', 500)->nullable();
            $table->string('footer_whatsapp_url', 500)->nullable();
            $table->text('footer_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
