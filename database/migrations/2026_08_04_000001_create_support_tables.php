<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->uuid('support_ticket_id')->primary();
            $table->string('reference', 30)->unique();
            $table->string('agence_id', 150)->index();
            $table->string('demandeur_id', 150)->nullable()->index();
            $table->string('categorie', 40);
            $table->string('sujet', 160);
            $table->text('description');
            $table->enum('statut', ['open', 'pending', 'resolved', 'closed'])->default('open')->index();
            $table->enum('priorite', ['low', 'medium', 'high'])->default('medium');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('support_messages', function (Blueprint $table) {
            $table->uuid('support_message_id')->primary();
            $table->uuid('support_ticket_id')->index();
            $table->string('auteur_id', 150)->nullable();
            $table->enum('auteur_type', ['agence', 'support'])->default('agence');
            $table->text('contenu');
            $table->timestamps();
            $table->foreign('support_ticket_id')->references('support_ticket_id')->on('support_tickets')->cascadeOnDelete();
        });

        Schema::create('support_attachments', function (Blueprint $table) {
            $table->uuid('support_attachment_id')->primary();
            $table->uuid('support_ticket_id')->index();
            $table->uuid('support_message_id')->nullable()->index();
            $table->string('nom_original');
            $table->string('chemin');
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('taille');
            $table->timestamps();
            $table->foreign('support_ticket_id')->references('support_ticket_id')->on('support_tickets')->cascadeOnDelete();
            $table->foreign('support_message_id')->references('support_message_id')->on('support_messages')->nullOnDelete();
        });
    }

    public function down(): void { Schema::dropIfExists('support_attachments'); Schema::dropIfExists('support_messages'); Schema::dropIfExists('support_tickets'); }
};
