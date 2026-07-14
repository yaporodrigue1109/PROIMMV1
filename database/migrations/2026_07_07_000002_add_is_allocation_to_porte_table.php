<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('porte', 'is_allocation')) {
            Schema::table('porte', function (Blueprint $table) {
                $table->boolean('is_allocation')->default(true)->after('etage');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('porte', 'is_allocation')) {
            Schema::table('porte', function (Blueprint $table) {
                $table->dropColumn('is_allocation');
            });
        }
    }
};
