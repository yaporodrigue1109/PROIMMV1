<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pays')) {
            Schema::create('pays', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->char('iso2', 2)->unique();
                $table->string('indicatif', 8);
                $table->boolean('actif')->default(true);
                $table->timestamps();
            });
        }

        $now = now();
        foreach (require database_path('data/countries.php') as $country) {
            DB::table('pays')->updateOrInsert(
                ['iso2' => $country['iso2']],
                [
                    'name' => $country['name'],
                    'indicatif' => $country['indicatif'],
                    'actif' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        if (Schema::hasTable('regions') && ! Schema::hasColumn('regions', 'pays_id')) {
            Schema::table('regions', function (Blueprint $table) {
                $table->unsignedBigInteger('pays_id')->nullable()->index();
            });
        }

        $ciId = DB::table('pays')->where('iso2', 'CI')->value('id');
        $gnId = DB::table('pays')->where('iso2', 'GN')->value('id');

        DB::table('regions')->whereNull('pays_id')->update(['pays_id' => $ciId]);

        foreach (['Boké', 'Conakry', 'Faranah', 'Kankan', 'Kindia', 'Labé', 'Mamou', 'N’Zérékoré'] as $name) {
            $regionId = DB::table('regions')->where('name', $name)->where('pays_id', $gnId)->value('id');
            if (! $regionId) {
                $values = ['name' => $name, 'pays_id' => $gnId];
                if (Schema::hasColumn('regions', 'created_at')) $values['created_at'] = $now;
                if (Schema::hasColumn('regions', 'updated_at')) $values['updated_at'] = $now;
                $regionId = DB::table('regions')->insertGetId($values);
            }

            if (! DB::table('villes')->where('name', $name)->where('region_id', $regionId)->exists()) {
                $values = ['name' => $name, 'region_id' => $regionId];
                if (Schema::hasColumn('villes', 'created_at')) $values['created_at'] = $now;
                if (Schema::hasColumn('villes', 'updated_at')) $values['updated_at'] = $now;
                DB::table('villes')->insert($values);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('regions') && Schema::hasColumn('regions', 'pays_id')) {
            Schema::table('regions', fn (Blueprint $table) => $table->dropColumn('pays_id'));
        }
        Schema::dropIfExists('pays');
    }
};
