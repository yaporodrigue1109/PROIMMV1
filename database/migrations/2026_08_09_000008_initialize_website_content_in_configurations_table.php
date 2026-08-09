<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $faqs = [
            ['question' => 'Pourquoi choisir Pros Immobilier ?', 'answer' => 'La plateforme rassemble la gestion des biens, des locataires, des loyers, des reversements et des interventions dans un espace unique, clair et sécurisé.'],
            ['question' => 'À qui s’adresse la plateforme ?', 'answer' => 'Pros Immobilier s’adresse aux agences immobilières de toutes tailles qui souhaitent structurer leurs opérations et offrir un meilleur suivi à leurs clients.'],
            ['question' => 'Mes données sont-elles sécurisées ?', 'answer' => 'Oui. Chaque agence dispose de son propre espace isolé et les accès peuvent être organisés selon les responsabilités de chaque collaborateur.'],
            ['question' => 'Puis-je essayer la solution avant de m’abonner ?', 'answer' => 'Oui. Vous pouvez demander une démonstration afin de découvrir les fonctionnalités et vérifier qu’elles correspondent aux besoins de votre agence.'],
            ['question' => 'L’équipe accompagne-t-elle la prise en main ?', 'answer' => 'Oui. Notre équipe vous guide dans la configuration initiale et reste disponible pour vous aider à adopter la plateforme sereinement.'],
            ['question' => 'Puis-je accéder à Pros Immobilier en déplacement ?', 'answer' => 'Oui. La plateforme est accessible depuis un navigateur sur ordinateur, tablette ou téléphone, partout où vous disposez d’une connexion internet.'],
        ];

        DB::table('configurations')->whereNull('website_faqs')->update([
            'website_faqs' => json_encode($faqs, JSON_UNESCAPED_UNICODE),
        ]);
    }

    public function down(): void
    {
        // Le contenu administrable saisi par l’utilisateur ne doit pas être supprimé.
    }
};
