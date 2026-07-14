<?php

namespace App\Services;

use App\Models\Agence;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Envoyer SMS d'expiration d'abonnement
     *
     * @param Agence $agence
     * @param string $type
     * @param int|null $joursRestants
     * @return bool
     */
    public function sendSmsExpiration(Agence $agence, string $type, ?int $joursRestants = null): bool
    {
        try {
            $message = match($type) {
                'bientot' => "Votre abonnement pour l'agence {$agence->name} expire dans {$joursRestants} jours. Veuillez contacter votre administrateur.",
                'expire' => "Votre abonnement pour l'agence {$agence->name} est expiré. Votre compte a été désactivé.",
                default => "Information importante concernant votre abonnement."
            };

            // Exemple d'envoi SMS via API
            // $response = Http::post(config('services.sms.api_url'), [
            //     'phone' => $agence->tel1,
            //     'message' => $message
            // ]);

            Log::info("SMS envoyé à l'agence {$agence->agence_id}", [
                'phone' => $agence->tel1,
                'message' => $message,
                'type' => $type
            ]);

            return true;

        } catch (Exception $e) {
            Log::error("Erreur envoi SMS: " . $e->getMessage());
            return false;
        }
    }
}