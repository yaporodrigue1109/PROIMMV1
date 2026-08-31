<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->boolean('subscription_manual_payment_enabled')->default(true);
            $table->string('subscription_wave_number', 30)->nullable();
            $table->string('subscription_orange_money_number', 30)->nullable();
            $table->string('subscription_moov_money_number', 30)->nullable();
            $table->string('subscription_mtn_money_number', 30)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn([
                'subscription_manual_payment_enabled',
                'subscription_wave_number',
                'subscription_orange_money_number',
                'subscription_moov_money_number',
                'subscription_mtn_money_number',
            ]);
        });
    }
};
