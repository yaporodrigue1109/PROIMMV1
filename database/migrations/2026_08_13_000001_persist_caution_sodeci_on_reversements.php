<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('reversements', 'cautionSodeci')) {
            Schema::table('reversements', function (Blueprint $table) {
                $table->decimal('cautionSodeci', 15, 2)->default(0)
                    ->after('nouvelle_caution');
            });
        }

        DB::table('reversements')
            ->select('id_reversement')
            ->orderBy('id_reversement')
            ->chunk(100, function ($reversements) {
                foreach ($reversements as $reversement) {
                    $total = DB::table('reversement_details')
                        ->where('reversement_id', $reversement->id_reversement)
                        ->sum('caution_sodeci');

                    DB::table('reversements')
                        ->where('id_reversement', $reversement->id_reversement)
                        ->update(['cautionSodeci' => $total ?: 0]);
                }
            });
    }

    public function down(): void
    {
        // Colonne conservée pour ne pas supprimer les données financières archivées.
    }
};
