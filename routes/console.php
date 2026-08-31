<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Commande par défaut
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('caisse:cloturer-expirees')
    ->dailyAt('00:00')
    ->withoutOverlapping();

Schedule::call(function () {
    \App\Jobs\GenerateLoyerMensuel::dispatchSync(now()->startOfMonth());
})
    ->name('loyers-generation-mensuelle-rattrapage')
    ->dailyAt('00:05')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/loyer_generation.log'));

Schedule::command('loyer:actualiser-statuts')
    ->dailyAt('00:15')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/loyer_statuts.log'));

Schedule::command('facturation:envoyer-alertes')
    ->dailyAt('00:20')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/facturation_notifications.log'));

Schedule::call(function () {
    \App\Models\Agence::query()
        ->where('statut', 'active')
        ->whereNotNull('abonnement_id')
        ->whereNotNull('abonnement_end')
        ->whereDate('abonnement_end', '<', now()->toDateString())
        ->update(['statut' => 'fin_abonnement']);
})
    ->name('agences-fin-abonnement-automatique')
    ->dailyAt('00:01')
    ->withoutOverlapping();
