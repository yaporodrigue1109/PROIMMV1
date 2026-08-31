<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('roles', 'is_system')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->boolean('is_system')->default(false);
            });
        }

        $now = now();
        DB::table('roles')->updateOrInsert(
            ['role_id' => 'role-responsable'],
            [
                'name' => 'Responsable',
                'slug' => 'role-responsable',
                'description' => "Rôle système non modifiable disposant de tous les droits de l'agence.",
                'agence_id' => null,
                'base_role_id' => null,
                'is_active' => true,
                'is_system' => true,
                'created_by' => null,
                'updated_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    public function down(): void
    {
        // Le rôle Responsable et son indicateur sont conservés pour éviter toute perte d'accès.
    }
};
