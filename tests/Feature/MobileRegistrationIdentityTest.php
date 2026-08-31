<?php

namespace Tests\Feature;

use App\Models\Locataire;
use App\Models\Proprietaire;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MobileRegistrationIdentityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('type_pieces', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('type_pieces_id')->unique();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        Schema::create('villes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('region_id');
            $table->string('name');
        });
        Schema::create('genres', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('abreviation')->nullable();
        });
        Schema::create('locataire', function (Blueprint $table) {
            $table->uuid('locataire_id')->primary();
            $table->string('name');
            $table->string('code');
            $table->string('tel1');
            $table->string('tel2')->nullable();
            $table->string('email')->nullable();
            $table->string('adresse')->nullable();
            $table->string('nationalite')->nullable();
            $table->date('date_naissance')->nullable();
            $table->string('lieu_naissance')->nullable();
            $table->string('profession')->nullable();
            $table->unsignedBigInteger('region_id')->nullable();
            $table->unsignedBigInteger('ville_id')->nullable();
            $table->unsignedBigInteger('genre_id')->nullable();
            $table->date('date_expiration_piece')->nullable();
            $table->string('photo')->nullable();
            $table->unsignedBigInteger('type_piece_id');
            $table->string('num_piece');
            $table->string('password');
            $table->timestamps();
        });
        Schema::create('proprietaires', function (Blueprint $table) {
            $table->uuid('proprietaire_id')->primary();
            $table->string('name');
            $table->string('code');
            $table->string('tel1');
            $table->string('tel2')->nullable();
            $table->string('email')->nullable();
            $table->string('adresse')->nullable();
            $table->string('nationalite')->nullable();
            $table->date('date_naiss')->nullable();
            $table->string('lieu_naiss')->nullable();
            $table->string('profession')->nullable();
            $table->unsignedBigInteger('region_id')->nullable();
            $table->unsignedBigInteger('ville_id')->nullable();
            $table->unsignedBigInteger('genre_id')->nullable();
            $table->date('date_expiration_piece')->nullable();
            $table->string('photo')->nullable();
            $table->unsignedBigInteger('type_pieces_id');
            $table->string('numpiece');
            $table->string('password');
            $table->timestamps();
        });
        Schema::create('mobile_api_tokens', function (Blueprint $table) {
            $table->uuid('mobile_api_token_id')->primary();
            $table->string('actor_type');
            $table->string('actor_id');
            $table->string('name');
            $table->string('token_hash')->unique();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        $this->app['db']->table('type_pieces')->insert([
            'type_pieces_id' => 1,
            'name' => 'CNI',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->app['db']->table('regions')->insert(['id' => 1, 'name' => 'Abidjan']);
        $this->app['db']->table('villes')->insert([
            'id' => 1,
            'region_id' => 1,
            'name' => 'Cocody',
        ]);
        $this->app['db']->table('genres')->insert([
            'id' => 1,
            'name' => 'Homme',
            'abreviation' => 'H',
        ]);
    }

    public static function roles(): array
    {
        return [
            'locataire' => ['locataire'],
            'proprietaire' => ['proprietaire'],
        ];
    }

    #[DataProvider('roles')]
    public function test_registration_updates_password_and_connects_existing_actor(string $role): void
    {
        $model = $this->createActor($role, '+2250102030405', 'CI-12345');

        $response = $this->postJson("/api/mobile/auth/{$role}/register", $this->payload());

        $response->assertOk()->assertJsonStructure(['token', 'token_type', 'expires_at', 'data']);
        $this->assertSame(1, $this->actorQuery($role)->count());
        $this->assertTrue(Hash::check('nouveau-mot-de-passe', $model->fresh()->password));
    }

    #[DataProvider('roles')]
    public function test_registration_creates_only_the_requested_actor_type(string $role): void
    {
        $response = $this->postJson("/api/mobile/auth/{$role}/register", $this->payload());

        $response->assertCreated()->assertJsonPath('data.role', $role);
        $this->assertSame(1, $this->actorQuery($role)->count());
        $otherRole = $role === 'locataire' ? 'proprietaire' : 'locataire';
        $this->assertSame(0, $this->actorQuery($otherRole)->count());
    }

    #[DataProvider('roles')]
    public function test_registration_rejects_partial_identity_match(string $role): void
    {
        $this->createActor($role, '+2250102030405', 'AUTRE-PIECE');

        $this->postJson("/api/mobile/auth/{$role}/register", $this->payload())
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['phone']);

        $this->assertSame(1, $this->actorQuery($role)->count());
    }

    #[DataProvider('roles')]
    public function test_authenticated_actor_can_update_profile(string $role): void
    {
        $registration = $this->postJson("/api/mobile/auth/{$role}/register", $this->payload())
            ->assertCreated();

        $this->withToken($registration->json('token'))
            ->patchJson('/api/mobile/me', [
                'name' => 'Jean Kouassi Modifié',
                'phone_secondary' => '+2250708091011',
                'email' => 'jean@example.com',
                'adresse' => 'Cocody, Abidjan',
                'nationalite' => 'Ivoirienne',
                'date_naissance' => '1990-05-12',
                'lieu_naissance' => 'Bouaké',
                'profession' => 'Comptable',
                'region_id' => 1,
                'ville_id' => 1,
                'genre_id' => 1,
                'date_expiration_piece' => '2030-12-31',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Jean Kouassi Modifié')
            ->assertJsonPath('data.phone_secondary', '+2250708091011')
            ->assertJsonPath('data.region_id', 1)
            ->assertJsonPath('data.ville_id', 1)
            ->assertJsonPath('data.genre_id', 1)
            ->assertJsonPath('data.date_expiration_piece', '2030-12-31')
            ->assertJsonPath('data.profession', 'Comptable');

        $actor = $this->actorQuery($role)->first();
        $this->assertSame('Jean Kouassi Modifié', $actor->name);
        $this->assertSame('jean@example.com', $actor->email);
    }

    #[DataProvider('roles')]
    public function test_authenticated_actor_can_upload_profile_photo(string $role): void
    {
        Storage::fake('public');
        $registration = $this->postJson("/api/mobile/auth/{$role}/register", $this->payload())
            ->assertCreated();

        $response = $this->withToken($registration->json('token'))
            ->post('/api/mobile/me/photo', [
                'photo' => UploadedFile::fake()->image('profil.jpg', 400, 400),
            ], ['Accept' => 'application/json']);

        $response->assertOk()->assertJsonPath('data.role', $role);
        $actor = $this->actorQuery($role)->first();
        $this->assertStringStartsWith("storage/mobile/profiles/{$role}/", $actor->photo);
        Storage::disk('public')->assertExists(substr($actor->photo, strlen('storage/')));
    }

    private function payload(): array
    {
        return [
            'first_name' => 'Jean',
            'last_name' => 'Kouassi',
            'phone' => '+2250102030405',
            'type_piece_id' => 1,
            'num_piece' => 'CI-12345',
            'password' => 'nouveau-mot-de-passe',
            'password_confirmation' => 'nouveau-mot-de-passe',
            'device_name' => 'test',
        ];
    }

    private function createActor(string $role, string $phone, string $piece): Locataire|Proprietaire
    {
        $attributes = [
            'name' => 'Compte existant',
            'code' => strtoupper(substr($role, 0, 3)).'-TEST',
            'tel1' => $phone,
            'password' => 'ancien-mot-de-passe',
        ];

        return $role === 'locataire'
            ? Locataire::create($attributes + ['type_piece_id' => 1, 'num_piece' => $piece])
            : Proprietaire::create($attributes + ['type_pieces_id' => 1, 'numpiece' => $piece]);
    }

    private function actorQuery(string $role)
    {
        return $role === 'locataire' ? Locataire::query() : Proprietaire::query();
    }
}
