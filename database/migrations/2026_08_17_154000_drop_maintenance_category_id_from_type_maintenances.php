<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('type_maintenances') && Schema::hasColumn('type_maintenances', 'maintenance_category_id')) {
            Schema::table('type_maintenances', function (Blueprint $table) {
                $table->dropColumn('maintenance_category_id');
            });
        }
    }

    public function down(): void
    {
        // La catégorie d'un type est conservée dans la colonne `categorie`.
    }
};
