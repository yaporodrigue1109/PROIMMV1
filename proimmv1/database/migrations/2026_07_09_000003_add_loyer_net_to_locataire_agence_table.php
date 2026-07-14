<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Normalise the table first so MySQL accepts the ALTER in strict mode.
        DB::statement("ALTER TABLE locataire_agence MODIFY updated_at DATETIME NULL DEFAULT NULL");

        Schema::table('locataire_agence', function (Blueprint $table) {
            if (! Schema::hasColumn('locataire_agence', 'loyer_net')) {
                $table->decimal('loyer_net', 12, 2)->nullable()->after('proprietaire_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('locataire_agence', function (Blueprint $table) {
            if (Schema::hasColumn('locataire_agence', 'loyer_net')) {
                $table->dropColumn('loyer_net');
            }
        });
    }
};
