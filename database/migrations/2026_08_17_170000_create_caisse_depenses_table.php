<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('caisse_depenses')) {
            return;
        }

        Schema::create('caisse_depenses', function (Blueprint $table) {
            $table->char('caisse_depense_id', 36)->primary();
            $table->string('agence_id', 150)->index();
            $table->string('transaction_agence_id', 150)->nullable()->unique();
            $table->string('categorie', 100);
            $table->string('libelle', 255);
            $table->unsignedInteger('montant');
            $table->unsignedBigInteger('mode_paiement_id');
            $table->string('type_justificatif', 50)->nullable();
            $table->text('observation')->nullable();
            $table->dateTime('date_depense');
            $table->string('created_by', 150)->nullable();
            $table->timestamps();

            $table->index(['agence_id', 'date_depense']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caisse_depenses');
    }
};
