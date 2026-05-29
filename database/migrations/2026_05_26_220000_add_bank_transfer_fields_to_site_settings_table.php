<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('bank_name', 120)->nullable()->after('footer_note');
            $table->string('bank_account_holder', 160)->nullable()->after('bank_name');
            $table->string('bank_account_number', 80)->nullable()->after('bank_account_holder');
            $table->string('bank_account_type', 80)->nullable()->after('bank_account_number');
            $table->text('bank_reference_note')->nullable()->after('bank_account_type');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'bank_name',
                'bank_account_holder',
                'bank_account_number',
                'bank_account_type',
                'bank_reference_note',
            ]);
        });
    }
};
