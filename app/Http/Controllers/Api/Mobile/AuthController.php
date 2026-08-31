<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Locataire;
use App\Models\LocataireAgence;
use App\Models\MobileApiToken;
use App\Models\Proprietaire;
use App\Models\ProprietaireAgence;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function register(Request $request, string $role): JsonResponse
    {
        $this->assertRole($role);
        $request->merge([
            'phone' => trim((string) $request->input('phone')),
            'num_piece' => trim((string) $request->input('num_piece')),
        ]);

        $ownerRules = $role === 'proprietaire' ? [
            'name' => ['required', 'string', 'max:200'],
            'adresse' => ['required', 'string', 'max:500'],
            'nationalite' => ['required', 'string', 'max:100'],
            'date_expiration_piece' => ['required', 'date'],
            'genre_id' => ['required', 'integer', 'exists:genres,id'],
        ] : [];
        $data = $request->validate([
            'first_name' => [$role === 'locataire' ? 'required' : 'nullable', 'string', 'max:100'],
            'last_name' => [$role === 'locataire' ? 'required' : 'nullable', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:50'],
            'type_piece_id' => ['required', 'integer', 'exists:type_pieces,type_pieces_id'],
            'num_piece' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:250'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ] + $ownerRules);

        $modelClass = $role === 'locataire' ? Locataire::class : Proprietaire::class;
        $pieceColumn = $role === 'locataire' ? 'num_piece' : 'numpiece';

        $result = DB::transaction(function () use ($data, $role, $modelClass, $pieceColumn) {
            $phones = $this->phoneVariants($data['phone']);
            $actorByPhone = $modelClass::query()
                ->where(function ($query) use ($phones) {
                    $query->whereIn('tel1', $phones)
                        ->orWhereIn('tel2', $phones);
                })
                ->lockForUpdate()
                ->first();

            $actorByPiece = $modelClass::query()
                ->where($pieceColumn, $data['num_piece'])
                ->lockForUpdate()
                ->first();

            if ($actorByPhone && $actorByPiece && ! $actorByPhone->is($actorByPiece)) {
                throw ValidationException::withMessages([
                    'phone' => 'Le téléphone et le numéro de pièce correspondent à deux propriétaires différents.',
                ]);
            }

            if ($actorByPhone || $actorByPiece) {
                $actor = $actorByPhone ?? $actorByPiece;
                if ($role === 'proprietaire') {
                    $actor->fill([
                        'name' => trim($data['name']),
                        'tel1' => $data['phone'],
                        'adresse' => trim($data['adresse']),
                        'nationalite' => trim($data['nationalite']),
                        'type_pieces_id' => $data['type_piece_id'],
                        'numpiece' => $data['num_piece'],
                        'date_expiration_piece' => $data['date_expiration_piece'],
                        'genre_id' => $data['genre_id'],
                    ]);
                }

                $actor->password = Hash::make($data['password']);
                $actor->saveQuietly();

                return ['actor' => $actor, 'created' => false];
            }

            $code = $this->generateCode($role);
            $common = [
                'name' => $role === 'proprietaire'
                    ? trim($data['name'])
                    : trim($data['first_name'].' '.$data['last_name']),
                'code' => $code,
                'tel1' => $data['phone'],
                'email' => $data['email'] ?? null,
            ];

            $actor = $role === 'locataire'
                ? Locataire::create($common + [
                    'type_piece_id' => $data['type_piece_id'],
                    'num_piece' => $data['num_piece'],
                ])
                : Proprietaire::create($common + [
                    'type_pieces_id' => $data['type_piece_id'],
                    'numpiece' => $data['num_piece'],
                    'adresse' => trim($data['adresse']),
                    'nationalite' => trim($data['nationalite']),
                    'date_expiration_piece' => $data['date_expiration_piece'],
                    'genre_id' => $data['genre_id'],
                ]);

            // Les modèles historiques appliquent un mot de passe initial à la création.
            $actor->password = Hash::make($data['password']);
            $actor->saveQuietly();

            return ['actor' => $actor, 'created' => true];
        });

        return response()->json(
            $this->authenticatedPayload($result['actor'], $role, $data['device_name'] ?? 'mobile'),
            $result['created'] ? 201 : 200,
        );
    }

    public function identityDocumentTypes(): JsonResponse
    {
        return response()->json([
            'data' => DB::table('type_pieces')
                ->orderBy('name')
                ->get(['type_pieces_id as id', 'name']),
        ]);
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
        $phones = $this->phoneVariants($data['phone']);
        $actor = $modelClass::withDefaultRelations()->where(function ($query) use ($phones) {
            $query->whereIn('tel1', $phones)->orWhereIn('tel2', $phones);
        })
            ->first();
        // return response()->json($actor);

        if (! $actor || ! $actor->password || ! Hash::check($data['password'], $actor->password)) {
            return response()->json(['message' => 'Téléphone ou mot de passe incorrect.'], 422);
            // throw ValidationException::withMessages(['phone' => 'Téléphone ou mot de passe incorrect.']);
        }

        if ($role === 'locataire') {
            $allAgencyLinks = LocataireAgence::withoutGlobalScopes()
                ->where('locataire_id', $actor->getKey());
            $agencyLinks = LocataireAgence::withoutGlobalScopes()
                ->where('locataire_id', $actor->getKey())
                ->where('is_active', true);
            $hasAnyLease = (clone $allAgencyLinks)->exists();
            $hasAgency = (clone $agencyLinks)->exists();

            if ($hasAnyLease && ! $hasAgency) {
                return response()->json([
                    'message' => 'Votre contrat a été désactivé. La connexion est impossible.',
                ], 403);
            }

            $hasActiveAgency = (clone $agencyLinks)
                ->whereHas('agency', fn ($query) => $query->where('statut', '!=', 'desactive'))
                ->exists();

            if ($hasAgency && ! $hasActiveAgency) {
                return response()->json([
                    'message' => 'Votre agence est désactivée. La connexion est temporairement impossible.',
                ], 403);
            }
        }

        if ($role === 'proprietaire') {
            $allAgencyLinks = ProprietaireAgence::withTrashed()
                ->where('proprietaire_id', $actor->getKey());
            $activeAgencyLinks = ProprietaireAgence::query()
                ->where('proprietaire_id', $actor->getKey())
                ->where('is_active', true);
            $hasAnyMandate = (clone $allAgencyLinks)->exists();
            $hasActiveMandate = (clone $activeAgencyLinks)->exists();

            if ($hasAnyMandate && ! $hasActiveMandate) {
                return response()->json([
                    'message' => 'Votre compte propriétaire a été désactivé. La connexion est impossible.',
                ], 403);
            }

            $hasActiveAgency = (clone $activeAgencyLinks)
                ->whereHas('agence', fn ($query) => $query->where('statut', '!=', 'desactive'))
                ->exists();

            if ($hasActiveMandate && ! $hasActiveAgency) {
                return response()->json([
                    'message' => 'Votre agence est désactivée. La connexion est temporairement impossible.',
                ], 403);
            }
        }

        return response()->json($this->authenticatedPayload($actor, $role, $data['device_name'] ?? 'mobile'));
    }

    private function phoneVariants(string $phone): array
    {
        $digits = preg_replace('/\D+/', '', trim($phone));
        $national = str_starts_with($digits, '225') ? substr($digits, 3) : $digits;

        return array_values(array_unique(array_filter([
            trim($phone),
            $digits,
            $national,
            $national !== '' ? '+225'.$national : null,
            $national !== '' ? '225'.$national : null,
        ])));
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->actorData($request->attributes->get('mobile_actor'), $request->attributes->get('mobile_role'))]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $role = $request->attributes->get('mobile_role');
        $ownerRule = $role === 'proprietaire' ? 'required' : 'nullable';
        $data = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'phone_secondary' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:250'],
            'adresse' => ['nullable', 'string', 'max:500'],
            'nationalite' => [$ownerRule, 'string', 'max:100'],
            'date_naissance' => [$ownerRule, 'date', 'before:today'],
            'lieu_naissance' => [$ownerRule, 'string', 'max:200'],
            'profession' => ['nullable', 'string', 'max:150'],
            'region_id' => ['nullable', 'integer', 'exists:regions,id'],
            'ville_id' => ['nullable', 'integer', 'exists:villes,id'],
            'genre_id' => [$ownerRule, 'integer', 'exists:genres,id'],
            'date_expiration_piece' => [$ownerRule, 'date'],
            'type_piece_id' => [$ownerRule, 'integer', 'exists:type_pieces,type_pieces_id'],
            'num_piece' => [$ownerRule, 'string', 'max:100'],
        ]);

        if (isset($data['ville_id']) && (! isset($data['region_id']) || ! DB::table('villes')
            ->where('id', $data['ville_id'])
            ->where('region_id', $data['region_id'])
            ->exists())) {
            throw ValidationException::withMessages([
                'ville_id' => 'La ville sélectionnée ne correspond pas à la région.',
            ]);
        }

        $actor = $request->attributes->get('mobile_actor');
        if (filled($data['num_piece'] ?? null)) {
            $pieceColumn = $role === 'locataire' ? 'num_piece' : 'numpiece';
            $table = $role === 'locataire' ? 'locataires' : 'proprietaires';
            validator($data, [
                'num_piece' => [Rule::unique($table, $pieceColumn)->ignore($actor->getKey(), $actor->getKeyName())],
            ])->validate();
        }
        $actor->fill([
            'name' => trim($data['name']),
            'tel2' => $data['phone_secondary'] ?? null,
            'email' => $data['email'] ?? null,
            'adresse' => $data['adresse'] ?? null,
            'nationalite' => $data['nationalite'] ?? null,
            $role === 'locataire' ? 'date_naissance' : 'date_naiss' => $data['date_naissance'] ?? null,
            $role === 'locataire' ? 'lieu_naissance' : 'lieu_naiss' => $data['lieu_naissance'] ?? null,
            'profession' => $data['profession'] ?? null,
            'region_id' => $data['region_id'] ?? null,
            'ville_id' => $data['ville_id'] ?? null,
            'genre_id' => $data['genre_id'] ?? null,
            'date_expiration_piece' => $data['date_expiration_piece'] ?? null,
            $role === 'locataire' ? 'type_piece_id' : 'type_pieces_id' => $data['type_piece_id'] ?? null,
            $role === 'locataire' ? 'num_piece' : 'numpiece' => $data['num_piece'] ?? null,
        ]);
        $actor->save();
        $actor->unsetRelations();

        return response()->json([
            'message' => 'Profil mis à jour avec succès.',
            'data' => $this->actorData($actor, $role),
        ]);
    }

    public function updateProfilePhoto(Request $request): JsonResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ]);

        $actor = $request->attributes->get('mobile_actor');
        $role = $request->attributes->get('mobile_role');
        if ($request->hasFile('photo')) {
            $directory = $role === 'proprietaire' ? 'proprietaire' : 'locataires/photo';
            $photo = upload($directory, 'png', 'photo', $request);
            $actor->photo = $this->mediaPath($photo);
        }
        $actor->save();
        $actor->unsetRelations();

        return response()->json([
            'message' => 'Photo de profil mise à jour avec succès.',
            'data' => $this->actorData($actor, $role),
        ]);
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
            'token' => $plainToken,
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
            'adresse' => $actor->adresse ?? null,
            'nationalite' => $actor->nationalite ?? null,
            'type_piece_id' => $actor->type_piece_id ?? $actor->type_pieces_id ?? null,
            'type_piece_name' => $actor->typePiece?->name ?? null,
            'num_piece' => $actor->num_piece ?? $actor->numpiece ?? null,
            'date_expiration_piece' => $this->dateString($actor->date_expiration_piece ?? null),
            'date_naissance' => $this->dateString($actor->date_naissance ?? $actor->date_naiss ?? null),
            'lieu_naissance' => $actor->lieu_naissance ?? $actor->lieu_naiss ?? null,

            'profession' => $actor->profession ?? null,
            'region_id' => $actor->region_id ?? null,
            'ville_id' => $actor->ville_id ?? null,
            'genre_id' => $actor->genre_id ?? null,
            'region_name' => $actor->region?->name ?? null,
            'ville_name' => $actor->ville?->name ?? null,
            'genre_name' => $actor->genre?->name ?? null,
            'genre_abreviation' => $actor->genre?->abreviation ?? null,
            'photo_url' => $this->mediaUrl($actor->photo),
            'image_piece' => $this->mediaUrl($actor->image_piece),
        ];
    }

    private function mediaPath(?string $value): ?string
    {
        if (! filled($value)) {
            return null;
        }

        $path = parse_url($value, PHP_URL_PATH) ?: $value;

        return ltrim($path, '/');
    }

    private function mediaUrl(?string $value): ?string
    {
        $path = $this->mediaPath($value);

        return $path ? request()->root().'/'.ltrim($path, '/') : null;
    }

    private function dateString(mixed $value): ?string
    {
        if (! $value) {
            return null;
        }

        return $value instanceof DateTimeInterface
            ? $value->format('Y-m-d')
            : substr((string) $value, 0, 10);
    }

    private function assertRole(string $role): void
    {
        abort_unless(in_array($role, ['locataire', 'proprietaire'], true), 404);
    }

    private function generateCode(string $role): string
    {
        $modelClass = $role === 'locataire' ? Locataire::class : Proprietaire::class;
        do {
            $letters = strtoupper(chr(rand(65, 90)).chr(rand(65, 90)));
            $numbers = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
            $code = $letters.'-'.$numbers;
        } while ($modelClass::where('code', $code)->exists());

        return $code;
    }
}
