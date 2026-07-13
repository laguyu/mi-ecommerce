<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('email', 120);
            $table->string('phone', 40)->nullable();
            $table->string('subject', 160);
            $table->text('message');
            $table->string('recipient_email', 120)->nullable();
            $table->string('status', 30)->default('stored');
            $table->timestamp('sent_at')->nullable();
            $table->text('delivery_error')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('recipient_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};