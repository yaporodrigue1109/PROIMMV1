<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('propietaire_lots', function (Blueprint $table) {
            $table->boolean('is_for_sale')->default(false)->after('adresse')->index('lots_for_sale_idx');
            $table->decimal('sale_price', 15, 2)->nullable()->after('is_for_sale');
        });

        Schema::table('propriete', function (Blueprint $table) {
            $table->enum('sale_type', ['none', 'whole', 'by_door'])->default('none')->after('is_allocation')->index('properties_sale_type_idx');
            $table->decimal('sale_price', 15, 2)->nullable()->after('sale_type');
        });

        DB::table('propriete')
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('batiment')
                    ->join('porte', 'porte.batiment_id', '=', 'batiment.batiment_id')
                    ->whereColumn('batiment.propriete_id', 'propriete.propriete_id')
                    ->where('porte.is_allocation', false);
            })
            ->update(['sale_type' => 'by_door']);
    }

    public function down(): void
    {
        Schema::table('propriete', function (Blueprint $table) {
            $table->dropIndex('properties_sale_type_idx');
            $table->dropColumn(['sale_type', 'sale_price']);
        });
        Schema::table('propietaire_lots', function (Blueprint $table) {
            $table->dropIndex('lots_for_sale_idx');
            $table->dropColumn(['is_for_sale', 'sale_price']);
        });
    }
};
