<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('prossimite_proprietes') && Schema::hasColumn('prossimite_proprietes', 'agence_id')) {
            DB::table('prossimite_proprietes')
                ->where('agence_id', '')
                ->update(['agence_id' => null]);
        }
    }

    public function down(): void
    {
        // NULL est la représentation définitive des proximités globales.
    }
};
