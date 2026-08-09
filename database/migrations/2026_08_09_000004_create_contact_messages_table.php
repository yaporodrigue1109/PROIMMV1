<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('contact_messages')) {
            return;
        }

        Schema::create('contact_messages', function (Blueprint $table): void {
            $table->uuid('contact_message_id')->primary();
            $table->string('request_type', 50)->index();
            $table->string('name', 100);
            $table->string('email', 150)->index();
            $table->string('phone', 30)->nullable();
            $table->string('subject', 150);
            $table->text('message');
            $table->string('status', 30)->default('new')->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('processed_at')->nullable()->index();
            $table->string('processed_by', 150)->nullable()->index();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
