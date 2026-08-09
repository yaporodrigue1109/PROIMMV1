<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('contact_replies')) {
            return;
        }

        Schema::create('contact_replies', function (Blueprint $table): void {
            $table->uuid('contact_reply_id')->primary();
            $table->uuid('contact_message_id')->index();
            $table->string('admin_id', 150)->nullable()->index();
            $table->string('channel', 30)->default('email');
            $table->string('recipient', 150);
            $table->string('subject', 180);
            $table->text('message');
            $table->string('status', 30)->default('pending')->index();
            $table->timestamp('sent_at')->nullable()->index();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->foreign('contact_message_id')
                ->references('contact_message_id')
                ->on('contact_messages')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_replies');
    }
};
