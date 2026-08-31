<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('type_proprietes') && Schema::hasColumn('type_proprietes', 'agence_id')) {
            DB::table('type_proprietes')->where('agence_id', '')->update(['agence_id' => null]);
        }
    }

    public function down(): void
    {
        // NULL reste la représentation définitive d'un type global.
    }
};
