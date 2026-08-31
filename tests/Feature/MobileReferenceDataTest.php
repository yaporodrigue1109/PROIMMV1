<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MobileReferenceDataTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        Schema::create('villes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id');
            $table->string('name');
        });
        Schema::create('genres', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('abreviation')->nullable();
        });

        DB::table('regions')->insert([
            ['id' => 1, 'name' => 'Abidjan'],
            ['id' => 2, 'name' => 'Bélier'],
        ]);
        DB::table('villes')->insert([
            ['id' => 1, 'region_id' => 1, 'name' => 'Cocody'],
            ['id' => 2, 'region_id' => 1, 'name' => 'Marcory'],
            ['id' => 3, 'region_id' => 2, 'name' => 'Toumodi'],
        ]);
        DB::table('genres')->insert([
            ['id' => 1, 'name' => 'Femme', 'abreviation' => 'F'],
            ['id' => 2, 'name' => 'Homme', 'abreviation' => 'H'],
        ]);
    }

    public function test_mobile_regions_endpoint_returns_regions_ordered_by_name(): void
    {
        $this->getJson('/api/mobile/reference/regions')
            ->assertOk()
            ->assertExactJson([
                'data' => [
                    ['id' => 1, 'name' => 'Abidjan'],
                    ['id' => 2, 'name' => 'Bélier'],
                ],
            ]);
    }

    public function test_mobile_cities_endpoint_can_filter_by_region(): void
    {
        $this->getJson('/api/mobile/reference/villes?region_id=1')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Cocody')
            ->assertJsonPath('data.1.name', 'Marcory');
    }

    public function test_mobile_cities_endpoint_rejects_unknown_region(): void
    {
        $this->getJson('/api/mobile/reference/villes?region_id=999')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['region_id']);
    }

    public function test_mobile_genders_endpoint_returns_genders(): void
    {
        $this->getJson('/api/mobile/reference/genres')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Femme')
            ->assertJsonPath('data.1.abreviation', 'H');
    }
}
