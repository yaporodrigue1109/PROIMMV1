<?php

namespace App\Http\Controllers\Admin\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;

        // Appliquer les middlewares de permission
        // $this->middleware('permission:view-users')->only(['index', 'show']);
        // $this->middleware('permission:create-users')->only(['create', 'store']);
        // $this->middleware('permission:edit-users')->only(['edit', 'update']);
        // $this->middleware('permission:delete-users')->only(['destroy']);
    }

    /**
     * Afficher la liste des utilisateurs
     */
    public function index(Request $request)
    {
        try {
            $filters = [
                'search' => $request->input('search'),
                'agence_id' => $request->input('agence_id'),
                'role_id' => $request->input('role_id'),
                'statut' => $request->input('statut'),
                'is_responsable' => $request->input('is_responsable'),
                'sort_by' => $request->input('sort_by', 'created_at'),
                'sort_order' => $request->input('sort_order', 'desc'),
            ];

            // Supprimer les filtres vides
            $filters = array_filter($filters);

            $users = $this->userService->getPaginatedUsers(15, $filters);
            $stats = $this->userService->getStatistics();

            return view('admin.users.index', [
                'users' => $users,
                'stats' => $stats,
                'filters' => $request->all(),
            ]);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Enregistrer un nouvel utilisateur
     */
    public function store(StoreUserRequest $request)
    {
        try {
            $user = $this->userService->createUser($request->validated());

            return redirect()
                ->route('admin.users.show', $user->id_users)
                ->with('success', 'Utilisateur créé avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur : ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Afficher les détails d'un utilisateur
     */
    public function show(int $id)
    {
        try {
            $user = $this->userService->getUser($id);

            if (!$user) {
                return redirect()->route('admin.users.index')->with('error', 'Utilisateur non trouvé');
            }

            return view('admin.users.show', compact('user'));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(int $id)
    {
        try {
            $user = $this->userService->getUser($id);

            if (!$user) {
                return redirect()->route('admin.users.index')->with('error', 'Utilisateur non trouvé');
            }

            return view('admin.users.edit', compact('user'));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function update(UpdateUserRequest $request, int $id)
    {
        try {
            $user = $this->userService->updateUser($id, $request->validated());

            return redirect()
                ->route('admin.users.show', $user->id_users)
                ->with('success', 'Utilisateur mis à jour avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur : ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Supprimer un utilisateur
     */
    public function destroy(int $id)
    {
        try {
            $this->userService->deleteUser($id);

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'Utilisateur supprimé avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Restaurer un utilisateur supprimé
     */
    public function restore(int $id)
    {
        try {
            $this->userService->restoreUser($id);

            return redirect()
                ->back()
                ->with('success', 'Utilisateur restauré avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Supprimer définitivement un utilisateur
     */
    public function forceDelete(int $id)
    {
        try {
            $this->userService->forceDeleteUser($id);

            return redirect()
                ->back()
                ->with('success', 'Utilisateur supprimé définitivement');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Activer un utilisateur
     */
    public function activate(int $id)
    {
        try {
            $this->userService->activateUser($id);

            return redirect()
                ->back()
                ->with('success', 'Utilisateur activé avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Désactiver un utilisateur
     */
    public function deactivate(int $id)
    {
        try {
            $this->userService->deactivateUser($id);

            return redirect()
                ->back()
                ->with('success', 'Utilisateur désactivé avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Suspendre un utilisateur
     */
    public function suspend(int $id)
    {
        try {
            $this->userService->suspendUser($id);

            return redirect()
                ->back()
                ->with('success', 'Utilisateur suspendu avec succès');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
     * API: Exporter les utilisateurs en CSV
     */
    public function exportCSV(Request $request)
    {
        try {
            $filters = [
                'agence_id' => $request->input('agence_id'),
                'role_id' => $request->input('role_id'),
                'statut' => $request->input('statut'),
            ];

            $filters = array_filter($filters);
            $csv = $this->userService->exportToCSV($filters);

            return response($csv)
                ->header('Content-Type', 'text/csv; charset=utf-8')
                ->header('Content-Disposition', 'attachment; filename="utilisateurs.csv"');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erreur lors de l\'export : ' . $e->getMessage());
        }
    }

    /**
     * API: Obtenir les statistiques
     */
    public function getStatistics()
    {
        try {
            $stats = $this->userService->getStatistics();

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * API: Rechercher des utilisateurs
     */
    public function search(Request $request)
    {
        try {
            $term = $request->input('q', '');
            $filters = [
                'agence_id' => $request->input('agence_id'),
                'role_id' => $request->input('role_id'),
            ];

            $users = $this->userService->searchUsers($term, array_filter($filters));

            return response()->json([
                'success' => true,
                'data' => $users,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}