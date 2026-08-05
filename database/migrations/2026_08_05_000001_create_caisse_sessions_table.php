<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('caisse_sessions', function (Blueprint $table) {
            $table->uuid('caisse_session_id')->primary();
            $table->string('agence_id', 150)->index();
            $table->string('opened_by', 150)->nullable();
            $table->string('closed_by', 150)->nullable();
            $table->decimal('solde_ouverture', 15, 2);
            $table->decimal('solde_theorique', 15, 2)->nullable();
            $table->decimal('solde_fermeture', 15, 2)->nullable();
            $table->decimal('ecart', 15, 2)->nullable();
            $table->text('observation_ouverture')->nullable();
            $table->text('observation_fermeture')->nullable();
            $table->timestamp('opened_at')->index();
            $table->timestamp('closed_at')->nullable()->index();
            $table->timestamps();

            $table->index(['agence_id', 'closed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caisse_sessions');
    }
};
