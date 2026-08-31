<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('reversements', 'montant_maintenances')) {
            Schema::table('reversements', function (Blueprint $table) {
                $table->decimal('montant_maintenances', 15, 2)->default(0)
                    ->after('depenses_effectuees');
            });
        }
    }

    public function down(): void
    {
        // Donnée financière archivée : la colonne est volontairement conservée.
    }
};
