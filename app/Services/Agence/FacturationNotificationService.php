<?php

namespace App\Services\Agence;

use App\Models\Agence;
use App\Models\FacturationNotificationLog;
use App\Models\Locataire;
use App\Models\Loyer;
use App\Models\ParametrageAgence;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class FacturationNotificationService
{
    public function sendScheduledAlerts(): array
    {
        $counts = ['rappels' => 0, 'retards' => 0, 'erreurs' => 0];

        if (! Schema::hasTable('facturation_notification_logs')) {
            return $counts;
        }

        ParametrageAgence::query()
            ->with('agence')
            ->where(fn ($query) => $query->where('notif_rappel', true)->orWhere('notif_retard', true))
            ->chunkById(50, function ($settings) use (&$counts): void {
                foreach ($settings as $param) {
                    $agence = $param->agence;
                    if (! $agence || $agence->statut !== 'active') continue;

                    if ($param->notif_rappel) {
                        $date = today()->addDays((int) ($param->delai_rappel ?? 7));
                        Loyer::query()->with('locataire')
                            ->where('agence_id', $agence->agence_id)
                            ->where('montant_restant', '>', 0)
                            ->whereDate('date_limit_paiement', $date)
                            ->each(fn (Loyer $loyer) => $this->sendLoyerAlert($loyer, $agence, $param, 'rappel', $counts));
                    }

                    if ($param->notif_retard) {
                        Loyer::query()->with('locataire')
                            ->where('agence_id', $agence->agence_id)
                            ->where('montant_restant', '>', 0)
                            ->whereDate('date_limit_paiement', '<', today())
                            ->each(fn (Loyer $loyer) => $this->sendLoyerAlert($loyer, $agence, $param, 'retard', $counts));
                    }
                }
            }, 'parametrages_agence_id');

        return $counts;
    }

    public function sendPaymentReceipt(string $agenceId, string $locataireId, array $payment): bool
    {
        $agence = Agence::with('parametrage')->find($agenceId);
        $tenant = Locataire::find($locataireId);
        $param = $agence?->parametrage;
        if (! $agence || ! $param?->notif_recu) return false;

        $amount = (float) ($payment['montant_verse'] ?? 0);
        $reference = (string) ($payment['reference'] ?? now()->format('YmdHis'));
        $recipients = $this->recipients($tenant?->email, $param, $amount);
        if ($recipients === []) return false;

        return $this->sendOnce(
            $agence,
            null,
            'recu',
            "recu:{$agenceId}:{$reference}",
            $recipients,
            'Confirmation de paiement - '.$agence->name,
            '<p>Bonjour '.e($tenant?->name ?: 'client').',</p><p>Votre paiement de <strong>'.$this->money($amount).'</strong> a été enregistré.</p><p>Référence : <strong>'.e($reference).'</strong></p>'
        );
    }

    private function sendLoyerAlert(Loyer $loyer, Agence $agence, ParametrageAgence $param, string $type, array &$counts): void
    {
        $amount = (float) $loyer->montant_restant;
        $recipients = $this->recipients($loyer->locataire?->email, $param, $amount);
        if ($recipients === []) return;

        $period = str_pad((string) $loyer->mois_paiement, 2, '0', STR_PAD_LEFT).'/'.$loyer->annee_paiement;
        $isReminder = $type === 'rappel';
        $subject = ($isReminder ? 'Rappel d’échéance' : 'Alerte de retard').' - '.$agence->name;
        $message = '<p>Bonjour '.e($loyer->locataire?->name ?: 'client').',</p>'
            .'<p>'.($isReminder ? 'Votre échéance approche.' : 'Votre échéance de paiement est dépassée.').'</p>'
            .'<p>Période : <strong>'.e($period).'</strong><br>Montant restant : <strong>'.$this->money($amount).'</strong><br>Date limite : <strong>'.optional($loyer->date_limit_paiement)->format('d/m/Y').'</strong></p>';

        $sent = $this->sendOnce($agence, $loyer, $type, "{$type}:{$loyer->loyer_id}:".$loyer->date_limit_paiement?->format('Y-m-d'), $recipients, $subject, $message);
        $counts[$sent ? ($isReminder ? 'rappels' : 'retards') : 'erreurs']++;
    }

    private function recipients(?string $tenantEmail, ParametrageAgence $param, float $amount): array
    {
        $emails = [$tenantEmail, $param->email_compta];
        if ($amount >= (float) ($param->seuil_dg ?? 1000000)) $emails[] = $param->email_dg;

        return collect($emails)
            ->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values()
            ->all();
    }

    private function sendOnce(Agence $agence, ?Loyer $loyer, string $type, string $key, array $recipients, string $subject, string $html): bool
    {
        $log = FacturationNotificationLog::query()->firstOrCreate(
            ['event_key' => $key],
            ['agence_id' => $agence->agence_id, 'loyer_id' => $loyer?->loyer_id, 'type' => $type, 'recipients' => implode(',', $recipients)]
        );
        if ($log->status === 'sent') return true;

        try {
            Mail::html($html, fn ($mail) => $mail->to($recipients)->subject($subject));
            $log->update(['status' => 'sent', 'sent_at' => now(), 'error' => null]);
            return true;
        } catch (\Throwable $e) {
            $log->update(['status' => 'failed', 'error' => mb_substr($e->getMessage(), 0, 2000)]);
            report($e);
            return false;
        }
    }

    private function money(float $amount): string
    {
        return number_format($amount, 0, ',', ' ').' FCFA';
    }
}
