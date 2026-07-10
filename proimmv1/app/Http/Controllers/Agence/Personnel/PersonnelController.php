<?php

namespace App\Http\Controllers\Agence\Personnel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Agence\PersonnelRequest;
use App\Models\Role;
use App\Repositories\Agence\Interfaces\PersonnelRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PersonnelController extends Controller
{
    protected $personnelRepository;

    public function __construct(PersonnelRepositoryInterface $personnelRepository)
    {
        $this->personnelRepository = $personnelRepository;
    }

    public function index()
    {
        $info = getInfoAgent();
        $agenceId = $info->users->agence_id;

        $personnel = $this->personnelRepository->getAllByAgence($agenceId, [
            'statut' => request('statut'),
            'search' => request('search'),
            'role_id' => request('role_id'),
        ]);

        $stats = [
            'total' => $personnel->count(),
            'actifs' => $personnel->where('statut', 'actif')->count(),
            'inactifs' => $personnel->where('statut', 'inactif')->count(),
            'suspendu' => $personnel->where('statut', 'suspendu')->count(),
        ];

        return Inertia::render('Agence/Personnel/Index', [
            'personnel' => $personnel,
            'stats' => $stats,
            'filters' => [
                'search' => request('search'),
                'statut' => request('statut'),
                'role_id' => request('role_id'),
            ],
            'roles' => $this->rolesForAgence(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Agence/Personnel/Form', [
            'mode' => 'create',
            'personnel' => null,
            'roles' => $this->rolesForAgence(),
        ]);
    }

    public function show(string $id): InertiaResponse
    {
        $personnel = $this->personnelRepository->findById($id);

        abort_if(!$personnel, 404);

        return Inertia::render('Agence/Personnel/Show', [
            'personnel' => $personnel,
            'permissions' => $personnel->getPermissions(),
        ]);
    }

    public function store(PersonnelRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $info = getInfoAgent();

        $data['agence_id'] = $info->users->agence_id;
        $data['statut'] = 'actif';

        if ($request->hasFile('photo')) {
            $data['photo'] = upload('personnel', 'png', 'photo', $request);
        }

        $this->personnelRepository->create($data);

        return redirect()->route('agence.personnel.index')
            ->with('success', 'Membre du personnel créé avec succès.');
    }

    public function edit(string $id)
    {
        $personnel = $this->personnelRepository->findById($id);

        return Inertia::render('Agence/Personnel/Form', [
            'mode' => 'edit',
            'personnel' => $personnel,
            'roles' => $this->rolesForAgence(),
        ]);
    }

    public function update(PersonnelRequest $request, string $id): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $existing = $this->personnelRepository->findById($id);

            $data['photo'] = update('personnel', $existing->photo, 'png', $request, 'photo');
        }

        $this->personnelRepository->update($id, $data);

        return redirect()->route('agence.personnel.index')
            ->with('success', 'Membre mis à jour avec succès.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $this->personnelRepository->delete($id);

        return redirect()->route('agence.personnel.index')
            ->with('success', 'Membre supprimé avec succès.');
    }

    public function activate(string $id): RedirectResponse
    {
        $this->personnelRepository->activate($id);

        return back()->with('success', 'Membre activé avec succès.');
    }

    public function deactivate(string $id): RedirectResponse
    {
        $this->personnelRepository->deactivate($id);

        return back()->with('success', 'Membre désactivé avec succès.');
    }

    private function rolesForAgence()
    {
        if (Schema::hasTable('roles')) {
            return Role::query()
                ->orderBy('name')
                ->get(['role_id', 'name']);
        }

        return collect([
            ['role_id' => 'role-responsable', 'name' => 'Responsable'],
            ['role_id' => 'role-agent', 'name' => 'Agent'],
            ['role_id' => 'role-comptable', 'name' => 'Comptable'],
            ['role_id' => 'role-technicien', 'name' => 'Technicien'],
        ]);
    }
}
