<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agency_announcements', function (Blueprint $table) {
            $table->uuid('announcement_id')->primary();
            $table->string('agence_id', 150)->index();
            $table->string('title', 200);
            $table->text('message');
            $table->enum('target_type', ['all', 'property', 'building', 'tenant']);
            $table->string('target_id', 150)->nullable()->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->string('created_by', 150)->nullable();
            $table->timestamps();
        });

        Schema::create('agency_announcement_recipients', function (Blueprint $table) {
            $table->uuid('announcement_recipient_id')->primary();
            $table->uuid('announcement_id')->index();
            $table->string('locataire_id', 150)->index();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->unique(['announcement_id', 'locataire_id'], 'announcement_tenant_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agency_announcement_recipients');
        Schema::dropIfExists('agency_announcements');
    }
};
