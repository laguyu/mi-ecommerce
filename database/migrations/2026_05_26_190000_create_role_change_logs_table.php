<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_change_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('changed_by_user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('target_user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('old_role', 30);
            $table->string('new_role', 30);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_change_logs');
    }
};
