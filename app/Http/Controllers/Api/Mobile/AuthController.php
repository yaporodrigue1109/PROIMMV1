<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Locataire;
use App\Models\MobileApiToken;
use App\Models\Proprietaire;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request, string $role): JsonResponse
    {
        $this->assertRole($role);
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:250'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $modelClass = $role === 'locataire' ? Locataire::class : Proprietaire::class;
        $phoneColumn = 'tel1';
        if ($modelClass::where($phoneColumn, $data['phone'])->exists()) {
            throw ValidationException::withMessages(['phone' => 'Ce numéro de téléphone est déjà utilisé.']);
        }

        $typePieceId = DB::table('type_pieces')->orderBy('type_pieces_id')->value('type_pieces_id');
        if (! $typePieceId) {
            throw ValidationException::withMessages([
                'account' => "Aucun type de pièce n'est configuré dans le backend.",
            ]);
        }

        $actor = DB::transaction(function () use ($data, $role, $typePieceId) {
            $code = strtoupper($role === 'locataire' ? 'LOC-' : 'PRO-').Str::random(8);
            $common = [
                'name' => trim($data['first_name'].' '.$data['last_name']),
                'code' => $code,
                'tel1' => $data['phone'],
                'email' => $data['email'] ?? null,
            ];

            $actor = $role === 'locataire'
                ? Locataire::create($common + [
                    'type_piece_id' => $typePieceId,
                    'num_piece' => 'MOBILE-'.$code,
                ])
                : Proprietaire::create($common + [
                    'type_pieces_id' => $typePieceId,
                    'numpiece' => 'MOBILE-'.$code,
                ]);

            // Les modèles historiques appliquent un mot de passe initial à la création.
            $actor->password = Hash::make($data['password']);
            $actor->saveQuietly();

            return $actor;
        });

        return response()->json($this->authenticatedPayload($actor, $role, $data['device_name'] ?? 'mobile'), 201);
    }

    public function login(Request $request, string $role): JsonResponse
    {
        $this->assertRole($role);
        $data = $request->validate([
            'phone' => ['required', 'string'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $modelClass = $role === 'locataire' ? Locataire::class : Proprietaire::class;
        $actor = $modelClass::where('tel1', $data['phone'])
            ->orWhere('tel2', $data['phone'])
            ->first();

        if (! $actor || ! $actor->password || ! Hash::check($data['password'], $actor->password)) {
            throw ValidationException::withMessages(['phone' => 'Téléphone ou mot de passe incorrect.']);
        }

        return response()->json($this->authenticatedPayload($actor, $role, $data['device_name'] ?? 'mobile'));
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->actorData($request->attributes->get('mobile_actor'), $request->attributes->get('mobile_role'))]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->attributes->get('mobile_token')->delete();

        return response()->json(['message' => 'Déconnexion réussie.']);
    }

    private function authenticatedPayload(Model $actor, string $role, string $deviceName): array
    {
        $plainToken = Str::random(80);
        MobileApiToken::create([
            'actor_type' => $role,
            'actor_id' => $actor->getKey(),
            'name' => $deviceName,
            'token_hash' => hash('sha256', $plainToken),
            'expires_at' => now()->addDays(90),
        ]);

        return [
            'token' => $plainToken,
            'token_type' => 'Bearer',
            'expires_at' => now()->addDays(90)->toIso8601String(),
            'data' => $this->actorData($actor, $role),
        ];
    }

    private function actorData(Model $actor, string $role): array
    {
        return [
            'id' => $actor->getKey(),
            'role' => $role,
            'code' => $actor->code,
            'name' => $actor->name,
            'phone' => $actor->tel1,
            'phone_secondary' => $actor->tel2,
            'email' => $actor->email,
            'photo_url' => $actor->photo ? url($actor->photo) : null,
        ];
    }

    private function assertRole(string $role): void
    {
        abort_unless(in_array($role, ['locataire', 'proprietaire'], true), 404);
    }
}
