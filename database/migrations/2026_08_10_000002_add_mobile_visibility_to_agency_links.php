<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locataire_agence', function (Blueprint $table) {
            $table->boolean('is_mobile_visible')->default(false)->after('is_active')
                ->comment("Activé uniquement après saisie du code agence dans l'application mobile")
                ->index();
        });

        Schema::table('proprietaire_agences', function (Blueprint $table) {
            $table->boolean('is_mobile_visible')->default(false)->after('is_active')
                ->comment("Activé uniquement après saisie du code agence dans l'application mobile")
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('locataire_agence', function (Blueprint $table) {
            $table->dropColumn('is_mobile_visible');
        });

        Schema::table('proprietaire_agences', function (Blueprint $table) {
            $table->dropColumn('is_mobile_visible');
        });
    }
};
