<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('reversements', 'frais_dossier')) {
            Schema::table('reversements', function (Blueprint $table) {
                $table->decimal('frais_dossier', 15, 2)->default(0)->after('depenses_effectuees');
            });
        }
    }

    public function down(): void
    {
        // Cette donnée financière archivée est volontairement conservée.
    }
};
