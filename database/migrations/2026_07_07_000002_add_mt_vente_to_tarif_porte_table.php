<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tarif_porte', function (Blueprint $table) {
            $table->decimal('mt_vente', 12, 2)->nullable()->after('mt_loyer');
        });
    }

    public function down(): void
    {
        Schema::table('tarif_porte', function (Blueprint $table) {
            $table->dropColumn('mt_vente');
        });
    }
};
