<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('type_maintenances', function (Blueprint $table) {
            if (! Schema::hasColumn('type_maintenances', 'duree_estimee')) {
                $table->decimal('duree_estimee', 8, 2)->nullable()->after('maintenance_category_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('type_maintenances', function (Blueprint $table) {
            if (Schema::hasColumn('type_maintenances', 'duree_estimee')) {
                $table->dropColumn('duree_estimee');
            }
        });
    }
};
