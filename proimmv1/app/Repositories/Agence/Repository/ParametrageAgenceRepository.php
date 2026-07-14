<?php

namespace App\Repositories\Agence\Repository;

use App\Models\Agence;
use App\Models\ParametrageAgence;
use App\Repositories\Agence\Interfaces\ParametrageAgenceRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ParametrageAgenceRepository implements ParametrageAgenceRepositoryInterface
{
    protected ParametrageAgence $model;

    public function __construct(ParametrageAgence $parametrage)
    {
        $this->model = $parametrage;
    }

    public function getByAgence(string $agenceId): ParametrageAgence
    {
        return ParametrageAgence::getForAgence($agenceId);
    }

    public function updateGeneral(string $agenceId, array $data): ParametrageAgence
    {
        $parametrage = $this->getByAgence($agenceId);

        $fillable = [
            'devise', 'langue', 'format_date', 'timezone',
            'sauvegarde_auto', 'double_validation', 'journal_activites', 'multi_session'
        ];

        $parametrage->update(array_intersect_key($data, array_flip($fillable)));

        $parametrage->save();

        return $parametrage;
    }

    public function updateFacturation(string $agenceId, array $data): ParametrageAgence
    {
        $parametrage = $this->getByAgence($agenceId);

        $fillable = [
            'periode_facturation', 'jour_emission', 'delai_paiement',
            'penalite_retard', 'prefixe_facture', 'sequence_facture',
            'commission', 'base_commission', 'tva', 'aib', 'ras',
            'acompte_min', 'mode_reglement_id'
        ];

        $parametrage->update(array_intersect_key($data, array_flip($fillable)));

        $parametrage->save();

        return $parametrage;
    }

    public function updateLogos(string $agenceId, array $files, array $data): ParametrageAgence
    {
        $parametrage = $this->getByAgence($agenceId);

        // Upload des logos
        if (isset($files['logo'])) {
            $this->deleteOldFile($parametrage->logo);
            $parametrage->logo = $this->uploadFile($files['logo'], 'logos');
        }

        if (isset($files['logo_tutelle'])) {
            $this->deleteOldFile($parametrage->logo_tutelle);
            $parametrage->logo_tutelle = $this->uploadFile($files['logo_tutelle'], 'logos');
        }

        if (isset($files['logo_partenaire'])) {
            $this->deleteOldFile($parametrage->logo_partenaire);
            $parametrage->logo_partenaire = $this->uploadFile($files['logo_partenaire'], 'logos');
        }

        if (isset($files['cachet'])) {
            $this->deleteOldFile($parametrage->cachet);
            $parametrage->cachet = $this->uploadFile($files['cachet'], 'cachets');
        }

        // Mise à jour des paramètres
        if (isset($data['logo_largeur'])) {
            $parametrage->logo_largeur = $data['logo_largeur'];
        }
        if (isset($data['logo_position'])) {
            $parametrage->logo_position = $data['logo_position'];
        }

        $parametrage->updated_by = auth()->id();
        $parametrage->save();

        return $parametrage;
    }

    public function updateSignatures(string $agenceId, array $files, array $data): ParametrageAgence
    {
        $parametrage = $this->getByAgence($agenceId);

        // Upload des signatures
        $signatureFields = ['signature_dg', 'signature_sg', 'signature_cpt'];
        foreach ($signatureFields as $field) {
            if (isset($files[$field])) {
                $this->deleteOldFile($parametrage->$field);
                $parametrage->$field = $this->uploadFile($files[$field], 'signatures');
            }
        }

        // Mise à jour des noms et titres
        $fillable = ['dg_nom', 'dg_titre', 'sg_nom', 'sg_titre', 'cpt_nom', 'cpt_titre'];
        $parametrage->update(array_intersect_key($data, array_flip($fillable)));

        // Mise à jour des règles
        $rules = ['sig_dg_facture', 'sig_double', 'cachet_auto'];
        $parametrage->update(array_intersect_key($data, array_flip($rules)));

        $parametrage->updated_by = auth()->id();
        $parametrage->save();

        return $parametrage;
    }

    public function updateNotifications(string $agenceId, array $data): ParametrageAgence
    {
        $parametrage = $this->getByAgence($agenceId);

        $fillable = [
            'notif_rappel', 'notif_retard', 'notif_recu',
            'email_compta', 'email_dg', 'delai_rappel', 'seuil_dg'
        ];

        $parametrage->update(array_intersect_key($data, array_flip($fillable)));
        $parametrage->updated_by = auth()->id();
        $parametrage->save();

        return $parametrage;
    }

    public function uploadFile(UploadedFile $file, string $path): string
    {
        return $file->store($path, 'public');
    }

    protected function deleteOldFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}