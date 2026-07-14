<?php

namespace App\Services;

use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Exception;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Storage;

class UserService
{
    protected UserRepositoryInterface $repository;

    public function __construct(UserRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Récupérer tous les utilisateurs
     */
    public function getAllUsers(array $filters = []): array
    {
        return $this->repository->getAll($filters);
    }

    /**
     * Récupérer les utilisateurs paginés
     */
    public function getPaginatedUsers(int $perPage = 15, array $filters = []): Paginator
    {
        return $this->repository->paginate($perPage, $filters);
    }

    /**
     * Récupérer un utilisateur
     */
    public function getUser(int $id): ?User
    {
        return $this->repository->getById($id);
    }

    /**
     * Créer un utilisateur
     */
    public function createUser(array $data): User
    {
        // Valider que l'email n'existe pas
        if ($this->repository->emailExists($data['email'])) {
            throw new Exception('Cet email est déjà utilisé');
        }

        return $this->repository->create($data);
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function updateUser(int $id, array $data): User
    {
        $user = $this->repository->getById($id);

        if (!$user) {
            throw new Exception('Utilisateur non trouvé');
        }

        // Vérifier l'email s'il a changé
        if (isset($data['email']) && $data['email'] !== $user->email) {
            if ($this->repository->emailExists($data['email'], $id)) {
                throw new Exception('Cet email est déjà utilisé');
            }
        }

        // Gérer la photo
        if (isset($data['photo'])) {
            // Supprimer l'ancienne photo
            if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                Storage::disk('public')->delete($user->photo);
            }
        }

        return $this->repository->update($id, $data);
    }

    /**
     * Supprimer un utilisateur
     */
    public function deleteUser(int $id): bool
    {
        return $this->repository->delete($id);
    }

    /**
     * Restaurer un utilisateur
     */
    public function restoreUser(int $id): bool
    {
        return $this->repository->restore($id);
    }

    /**
     * Supprimer définitivement un utilisateur
     */
    public function forceDeleteUser(int $id): bool
    {
        $user = $this->repository->getById($id);

        if ($user && $user->photo && Storage::disk('public')->exists($user->photo)) {
            Storage::disk('public')->delete($user->photo);
        }

        return $this->repository->forceDelete($id);
    }

    /**
     * Activer un utilisateur
     */
    public function activateUser(int $id): User
    {
        return $this->repository->activate($id);
    }

    /**
     * Désactiver un utilisateur
     */
    public function deactivateUser(int $id): User
    {
        return $this->repository->deactivate($id);
    }

    /**
     * Suspendre un utilisateur
     */
    public function suspendUser(int $id): User
    {
        return $this->repository->suspend($id);
    }

    /**
     * Rechercher des utilisateurs
     */
    public function searchUsers(string $term, array $filters = []): array
    {
        return $this->repository->search($term, $filters);
    }

    /**
     * Récupérer les utilisateurs d'une agence
     */
    public function getUsersByAgence(int $agenceId, array $filters = []): array
    {
        return $this->repository->getByAgence($agenceId, $filters);
    }

    /**
     * Récupérer les utilisateurs d'un rôle
     */
    public function getUsersByRole(int $roleId, array $filters = []): array
    {
        return $this->repository->getByRole($roleId, $filters);
    }

    /**
     * Récupérer les responsables
     */
    public function getResponsables()
    {
        return $this->repository->getResponsables();
    }

    /**
     * Obtenir les statistiques
     */
    public function getStatistics(): array
    {
        return $this->repository->getStatistics();
    }

    /**
     * Valider les données d'un utilisateur
     */
    public function validateUserData(array $data, ?int $userId = null): array
    {
        $errors = [];

        // Valider le nom
        if (empty($data['name'])) {
            $errors[] = 'Le nom est obligatoire';
        }

        // Valider l'email
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Un email valide est obligatoire';
        } elseif ($this->repository->emailExists($data['email'], $userId)) {
            $errors[] = 'Cet email est déjà utilisé';
        }

        // Valider le rôle
        if (empty($data['role_id'])) {
            $errors[] = 'Le rôle est obligatoire';
        }

        // Valider le téléphone
        if (empty($data['tel1'])) {
            $errors[] = 'Le téléphone principal est obligatoire';
        }

        // Valider le statut
        if (empty($data['statut']) || !in_array($data['statut'], ['actif', 'inactif', 'suspendu'])) {
            $errors[] = 'Le statut est invalide';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Exporter les utilisateurs (CSV)
     */
    public function exportToCSV(array $filters = []): string
    {
        $users = $this->repository->getAll($filters);

        $csv = "ID,Nom,Email,Téléphone,Rôle,Agence,Statut,Créé le\n";

        foreach ($users as $user) {
            $csv .= sprintf(
                '"%s","%s","%s","%s","%s","%s","%s","%s"' . "\n",
                $user->id_users,
                $user->name,
                $user->email,
                $user->tel1,
                $user->role->name ?? 'N/A',
                $user->agence->name ?? 'N/A',
                $user->statut,
                $user->created_at->format('Y-m-d H:i:s')
            );
        }

        return $csv;
    }

    /**
     * Obtenir un utilisateur avec ses relations complètes
     */
    public function getUserWithRelations(int $id): ?User
    {
        return $this->repository->getById($id);
    }

    /**
     * Compter les utilisateurs
     */
    public function countUsers(array $filters = []): int
    {
        return $this->repository->count($filters);
    }
}