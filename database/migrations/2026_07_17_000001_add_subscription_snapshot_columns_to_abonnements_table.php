<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = [
            'type' => fn (Blueprint $table) => $table->string('type')->default('plan')->after('abonnement_id'),
            'agence_id' => fn (Blueprint $table) => $table->string('agence_id')->nullable()->after('type'),
            'ancien_abonnement_id' => fn (Blueprint $table) => $table->unsignedBigInteger('ancien_abonnement_id')->nullable()->after('agence_id'),
            'nouvel_abonnement_id' => fn (Blueprint $table) => $table->unsignedBigInteger('nouvel_abonnement_id')->nullable()->after('ancien_abonnement_id'),
            'ancienne_date_debut' => fn (Blueprint $table) => $table->date('ancienne_date_debut')->nullable()->after('nouvel_abonnement_id'),
            'ancienne_date_fin' => fn (Blueprint $table) => $table->date('ancienne_date_fin')->nullable()->after('ancienne_date_debut'),
            'nouvelle_date_debut' => fn (Blueprint $table) => $table->date('nouvelle_date_debut')->nullable()->after('ancienne_date_fin'),
            'nouvelle_date_fin' => fn (Blueprint $table) => $table->date('nouvelle_date_fin')->nullable()->after('nouvelle_date_debut'),
            'duree_mois' => fn (Blueprint $table) => $table->unsignedInteger('duree_mois')->nullable()->after('nouvelle_date_fin'),
            'montant_ht' => fn (Blueprint $table) => $table->decimal('montant_ht', 12, 2)->default(0)->after('duree_mois'),
            'action' => fn (Blueprint $table) => $table->string('action')->nullable()->after('montant_ht'),
            'action_par' => fn (Blueprint $table) => $table->string('action_par')->nullable()->after('action'),
            'notes' => fn (Blueprint $table) => $table->text('notes')->nullable()->after('action_par'),
        ];

        foreach ($columns as $column => $definition) {
            if (! Schema::hasColumn('abonnements', $column)) {
                Schema::table('abonnements', $definition);
            }
        }

        if (! Schema::hasIndex('abonnements', 'abonnements_agence_id_unique')) {
            Schema::table('abonnements', function (Blueprint $table) {
                $table->unique('agence_id', 'abonnements_agence_id_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('abonnements', 'abonnements_agence_id_unique')) {
            Schema::table('abonnements', fn (Blueprint $table) => $table->dropUnique('abonnements_agence_id_unique'));
        }

        $columns = array_values(array_filter([
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
            ], fn ($column) => Schema::hasColumn('abonnements', $column)));

        if ($columns) {
            Schema::table('abonnements', fn (Blueprint $table) => $table->dropColumn($columns));
        }
    }
};
