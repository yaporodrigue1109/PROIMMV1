<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reversements', function (Blueprint $table) {
            if (! Schema::hasColumn('reversements', 'type_reversement')) {
                $table->string('type_reversement', 20)->default('location')->after('id_reversement')->index();
            }
            if (! Schema::hasColumn('reversements', 'vente_id')) {
                $table->string('vente_id', 150)->nullable()->after('lot_id')->index();
            }
        });
    }

    public function down(): void
    {
        // Les références financières archivées sont volontairement conservées.
    }
};
