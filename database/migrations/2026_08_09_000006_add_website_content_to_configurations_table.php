<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->text('website_story')->nullable();
            $table->string('website_mission_title')->nullable();
            $table->text('website_mission_text')->nullable();
            $table->json('website_commitments')->nullable();
            $table->json('website_faqs')->nullable();
            $table->string('owner_android_url')->nullable();
            $table->string('owner_ios_url')->nullable();
            $table->string('tenant_android_url')->nullable();
            $table->string('tenant_ios_url')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn([
                'website_story',
                'website_mission_title',
                'website_mission_text',
                'website_commitments',
                'website_faqs',
                'owner_android_url',
                'owner_ios_url',
                'tenant_android_url',
                'tenant_ios_url',
            ]);
        });
    }
};
