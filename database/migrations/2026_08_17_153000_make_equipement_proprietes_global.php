<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('equipement_proprietes') && Schema::hasColumn('equipement_proprietes', 'agence_id')) {
            Schema::table('equipement_proprietes', function (Blueprint $table) {
                $table->string('agence_id', 150)->nullable()->change();
            });
            DB::table('equipement_proprietes')->where('agence_id', '')->update(['agence_id' => null]);
        }
    }

    public function down(): void
    {
        // NULL reste la représentation définitive d'un équipement global.
    }
};
