<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminEmail = 'admin@pros-immobilier.test';
        $agenceEmail = 'agence.demo@pros-immobilier.test';
        $now = now();
        $adminId = '11111111-1111-1111-1111-111111111111';
        $agenceId = '22222222-2222-2222-2222-222222222222';
        $userId = '33333333-3333-3333-3333-333333333333';
        $parametrageId = '44444444-4444-4444-4444-444444444444';

        $this->updateOrInsertExisting(
            'admins',
            ['email' => $adminEmail],
            [
                'id_admin' => $adminId,
                'name' => 'Admin Principal',
                'phone' => '0000000000',
                'email' => $adminEmail,
                'role' => 'super_admin',
                'status' => 'active',
                'statut' => 1,
                'password' => Hash::make('Admin@12345'),
                'created_by' => null,
                'updated_by' => null,
                'deleted_by' => null,
                'remember_token' => Str::random(10),
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $this->updateOrInsertExisting(
            'agences',
            ['code_agence' => 'AG-DEMO-001'],
            [
                'agence_id' => $agenceId,
                'code_agence' => 'AG-DEMO-001',
                'name' => 'Agence Demo',
                'adresse' => 'Abidjan',
                'tel1' => '0102030405',
                'email1' => $agenceEmail,
                'statut' => 'active',
                'is_principale' => true,
                'responsable_id' => $userId,
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'deleted_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $this->call(RoleSeeder::class);
        $this->updateOrInsertExisting(
            'users',
            ['email' => $agenceEmail],
            [
                'id_users' => $userId,
                'name' => 'Responsable Demo',
                'email' => $agenceEmail,
                'password' => Hash::make('Agence@12345'),
                'agence_id' => $agenceId,
                'is_responsable' => true,
                'role_id' => 'role-responsable',
                'tel1' => '0102030405',
                'tel2' => null,
                'statut' => 'actif',
                'adresse' => 'Abidjan',
                'photo' => null,
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'deleted_by' => null,
                'remember_token' => Str::random(10),
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ]
        );

        $this->updateOrInsertExisting(
            'parametrages_agence',
            ['agence_id' => $agenceId],
            [
                'parametrages_agence_id' => $parametrageId,
                'agence_id' => $agenceId,
                'created_by' => $userId,
                'updated_by' => $userId,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $this->call(ModePaiementSeeder::class);
        $this->call(PeriodicitePaiementSeeder::class);
        $this->call(AgencePropertyFixturesSeeder::class);
        $this->call(LocataireDemoSeeder::class);
    }

    private function updateOrInsertExisting(string $table, array $match, array $values): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $columns = Schema::getColumnListing($table);
        $allowed = array_flip($columns);

        $match = array_intersect_key($match, $allowed);
        $values = array_intersect_key($values, $allowed);

        if (empty($match)) {
            return;
        }

        DB::table($table)->updateOrInsert($match, $values);
    }
}
