<?php

namespace App\Http\Controllers\Agence\Announcement;

use App\Http\Controllers\Controller;
use App\Models\AgencyAnnouncement;
use App\Models\Batiment;
use App\Models\Locataire;
use App\Models\LocataireAgence;
use App\Models\Propriete;
use App\Services\Agence\AnnouncementService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AnnouncementController extends Controller
{
    public function __construct(private AnnouncementService $service) {}

    public function index()
    {
        $agencyId = $this->agencyId();
        $announcements = AgencyAnnouncement::where('agence_id', $agencyId)
            ->withCount(['recipients', 'recipients as unread_count' => fn ($q) => $q->whereNull('read_at')])
            ->latest('published_at')
            ->get();

        $tenantIds = LocataireAgence::where('agence_id', $agencyId)->where('is_active', true)
            ->pluck('locataire_id')->unique();

        $properties = Propriete::with('proprietaire')
            ->where('agence_id', $agencyId)
            ->orderBy('description')
            ->get()
            ->map(fn ($property) => [
                'id' => $property->getKey(),
                'name' => $property->description ?: ($property->adresse_complete ?: 'Propriété'),
                'owner_id' => $property->proprietaire_id,
                'owner_name' => $property->proprietaire?->name ?: 'Propriétaire non renseigné',
                'owner_phone' => $property->proprietaire?->tel1,
                'owner_phone_secondary' => $property->proprietaire?->tel2,
            ])
            ->values();

        return Inertia::render('Agence/Announcements/Index', [
            'announcements' => $announcements,
            'properties' => $properties,
            'buildings' => Batiment::where('agence_id', $agencyId)->orderBy('name')->get(['batiment_id as id', 'propriete_id', 'name']),
            'tenants' => Locataire::whereIn('locataire_id', $tenantIds)->orderBy('name')->get(['locataire_id as id', 'name', 'tel1']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'message' => ['required', 'string', 'max:5000'],
            'target_type' => ['required', Rule::in(['all', 'property', 'building', 'tenant'])],
            'target_id' => ['nullable', 'string', 'required_unless:target_type,all'],
        ]);

        $this->validateTarget($data);
        $announcement = $this->service->create(
            $this->agencyId(),
            $data,
            getInfoAgent()->users->id_users ?? null,
        );

        return back()->with('success', "Annonce publiée à {$announcement->recipients_count} locataire(s).");
    }

    private function validateTarget(array $data): void
    {
        if ($data['target_type'] === 'all') return;
        $agencyId = $this->agencyId();
        $exists = match ($data['target_type']) {
            'property' => Propriete::where('agence_id', $agencyId)->whereKey($data['target_id'])->exists(),
            'building' => Batiment::where('agence_id', $agencyId)->whereKey($data['target_id'])->exists(),
            'tenant' => LocataireAgence::where('agence_id', $agencyId)->where('locataire_id', $data['target_id'])->where('is_active', true)->exists(),
        };
        abort_unless($exists, 422, 'La cible sélectionnée ne fait pas partie de cette agence.');
    }

    private function agencyId(): string
    {
        return getInfoAgent()->users->agence_id;
    }
}
