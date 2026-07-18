<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('abonnements', function (Blueprint $table) {
            $table->string('type')->default('plan')->after('abonnement_id');
            $table->string('agence_id')->nullable()->index()->after('type');
            $table->unsignedBigInteger('ancien_abonnement_id')->nullable()->after('agence_id');
            $table->unsignedBigInteger('nouvel_abonnement_id')->nullable()->after('ancien_abonnement_id');
            $table->date('ancienne_date_debut')->nullable()->after('nouvel_abonnement_id');
            $table->date('ancienne_date_fin')->nullable()->after('ancienne_date_debut');
            $table->date('nouvelle_date_debut')->nullable()->after('ancienne_date_fin');
            $table->date('nouvelle_date_fin')->nullable()->after('nouvelle_date_debut');
            $table->unsignedInteger('duree_mois')->nullable()->after('nouvelle_date_fin');
            $table->decimal('montant_ht', 12, 2)->default(0)->after('duree_mois');
            $table->string('action')->nullable()->after('montant_ht');
            $table->string('action_par')->nullable()->after('action');
            $table->text('notes')->nullable()->after('action_par');
        });

        Schema::table('abonnements', function (Blueprint $table) {
            $table->unique('agence_id', 'abonnements_agence_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('abonnements', function (Blueprint $table) {
            $table->dropUnique('abonnements_agence_id_unique');
            $table->dropColumn([
                'type',
                'agence_id',
                'ancien_abonnement_id',
                'nouvel_abonnement_id',
                'ancienne_date_debut',
                'ancienne_date_fin',
                'nouvelle_date_debut',
                'nouvelle_date_fin',
                'duree_mois',
                'montant_ht',
                'action',
                'action_par',
                'notes',
            ]);
        });
    }
};
