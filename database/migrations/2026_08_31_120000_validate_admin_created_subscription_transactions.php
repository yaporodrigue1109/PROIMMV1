<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('transactions') || ! Schema::hasTable('admins')) {
            return;
        }

        DB::table('transactions')
            ->where('statut', 'en_attente')
            ->whereIn('type_operation', ['souscription', 'renouvellement'])
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('admins')
                    ->whereColumn('admins.id_admin', 'transactions.created_by');
            })
            ->update([
                'statut' => 'validee',
                'mode_paiement' => DB::raw("COALESCE(mode_paiement, 'autre')"),
                'date_paiement' => DB::raw('COALESCE(date_paiement, created_at)'),
                'date_validation' => DB::raw('COALESCE(date_validation, created_at)'),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // La validation d'une transaction financière ne doit pas être annulée automatiquement.
    }
};
