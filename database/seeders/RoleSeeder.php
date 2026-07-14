<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('roles')) {
            return;
        }

        $now = now();
        $roles = [
            'role-responsable' => 'Responsable',
            'role-agent' => 'Agent',
            'role-comptable' => 'Comptable',
            'role-technicien' => 'Technicien',
        ];

        foreach ($roles as $id => $name) {
            DB::table('roles')->updateOrInsert(
                ['role_id' => $id],
                [
                    'name' => $name,
                    'agence_id' => null,
                    'created_by' => null,
                    'updated_by' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
