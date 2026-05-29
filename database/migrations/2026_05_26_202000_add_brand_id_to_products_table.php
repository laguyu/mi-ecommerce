<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('brand_id')->nullable()->after('category_id')->constrained('brands')->nullOnDelete()->cascadeOnUpdate();
        });

        $brandNames = DB::table('products')
            ->whereNotNull('brand_name')
            ->where('brand_name', '!=', '')
            ->distinct()
            ->pluck('brand_name');

        foreach ($brandNames as $brandName) {
            $brandId = DB::table('brands')->where('name', $brandName)->value('id');

            if (! $brandId) {
                $brandId = DB::table('brands')->insertGetId([
                    'name' => $brandName,
                    'slug' => Str::slug($brandName),
                    'description' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('products')
                ->where('brand_name', $brandName)
                ->update(['brand_id' => $brandId]);
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('brand_id');
        });
    }
};