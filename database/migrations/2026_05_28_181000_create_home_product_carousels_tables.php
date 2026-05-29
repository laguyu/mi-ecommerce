<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_product_carousels', function (Blueprint $table) {
            $table->id();
            $table->string('title', 160);
            $table->string('subtitle', 220)->nullable();
            $table->string('image_url', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('sort_order')->default(1);
            $table->timestamps();
        });

        Schema::create('home_product_carousel_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_product_carousel_id')->constrained('home_product_carousels')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedTinyInteger('sort_order')->default(1);
            $table->timestamps();

            $table->unique(['home_product_carousel_id', 'product_id'], 'home_carousel_product_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_product_carousel_product');
        Schema::dropIfExists('home_product_carousels');
    }
};
