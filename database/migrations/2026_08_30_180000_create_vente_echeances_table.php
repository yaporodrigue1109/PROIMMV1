<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vente_echeances')) {
            return;
        }

        Schema::create('vente_echeances', function (Blueprint $table) {
            $table->char('vente_echeance_id', 36)->primary();
            $table->string('vente_id', 150)->index();
            $table->string('agence_id', 150)->index();
            $table->string('libelle', 150);
            $table->unsignedInteger('numero_echeance');
            $table->date('date_echeance')->index();
            $table->decimal('montant_prevu', 15, 2);
            $table->decimal('montant_paye', 15, 2)->default(0);
            $table->decimal('montant_amende', 15, 2)->default(0);
            $table->unsignedBigInteger('mode_paiement_id')->nullable();
            $table->string('statut', 30)->default('en_attente')->index();
            $table->dateTime('paye_at')->nullable();
            $table->string('transaction_agence_id', 150)->nullable()->index();
            $table->timestamps();

            $table->index(['vente_id', 'date_echeance']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vente_echeances');
    }
};
