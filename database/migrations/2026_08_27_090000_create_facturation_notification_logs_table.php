<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facturation_notification_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('agence_id')->index();
            $table->uuid('loyer_id')->nullable()->index();
            $table->string('type', 30)->index();
            $table->string('event_key')->unique();
            $table->string('recipients', 1000);
            $table->string('status', 20)->default('pending');
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facturation_notification_logs');
    }
};
