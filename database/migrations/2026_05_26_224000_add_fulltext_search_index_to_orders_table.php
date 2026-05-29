<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->fullText(['order_number', 'customer_email', 'customer_full_name'], 'orders_admin_search_fulltext');
        });
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->dropFullText('orders_admin_search_fulltext');
        });
    }
};
