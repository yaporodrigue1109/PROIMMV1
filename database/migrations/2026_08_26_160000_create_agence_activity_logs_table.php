<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agence_activity_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('agence_id')->index();
            $table->string('user_id')->nullable()->index();
            $table->string('user_name')->nullable();
            $table->string('action', 30)->index();
            $table->string('description');
            $table->string('route_name')->nullable();
            $table->string('method', 10);
            $table->string('path');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('context')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();

            $table->index(['agence_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agence_activity_logs');
    }
};
