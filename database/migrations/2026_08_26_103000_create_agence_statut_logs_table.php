<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agence_statut_logs', function (Blueprint $table) {
            $table->id();
            $table->string('agence_id', 255)->index();
            $table->string('ancien_statut', 30);
            $table->string('nouveau_statut', 30);
            $table->text('motif')->nullable();
            $table->string('changed_by', 255)->nullable()->index();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agence_statut_logs');
    }
};
