<?php

namespace App\Services\Agence;

use App\Models\AgencyAnnouncement;
use App\Models\LocataireAgence;
use Illuminate\Support\Facades\DB;

class AnnouncementService
{
    public function create(string $agencyId, array $data, ?string $userId): AgencyAnnouncement
    {
        return DB::transaction(function () use ($agencyId, $data, $userId) {
            $announcement = AgencyAnnouncement::create([
                'agence_id' => $agencyId,
                'title' => trim($data['title']),
                'message' => trim($data['message']),
                'target_type' => $data['target_type'],
                'target_id' => $data['target_type'] === 'all' ? null : $data['target_id'],
                'published_at' => now(),
                'created_by' => $userId,
            ]);

            $contracts = LocataireAgence::query()
                ->where('agence_id', $agencyId)
                ->where('is_active', true)
                ->when($data['target_type'] === 'property', fn ($q) => $q->where('propriete_id', $data['target_id']))
                ->when($data['target_type'] === 'building', fn ($q) => $q->where('batiment_id', $data['target_id']))
                ->when($data['target_type'] === 'tenant', fn ($q) => $q->where('locataire_id', $data['target_id']))
                ->pluck('locataire_id')
                ->unique();

            $announcement->recipients()->createMany($contracts->map(fn ($id) => [
                'locataire_id' => $id,
            ])->all());

            return $announcement->loadCount('recipients');
        });
    }
}
