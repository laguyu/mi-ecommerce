<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_secondary_banners', function (Blueprint $table) {
            $table->id();
            $table->string('title', 160);
            $table->string('subtitle', 220)->nullable();
            $table->string('image_url', 500)->nullable();
            $table->string('link_url', 500)->nullable();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('sort_order')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_secondary_banners');
    }
};
