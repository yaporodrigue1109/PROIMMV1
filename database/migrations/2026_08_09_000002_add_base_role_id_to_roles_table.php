<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('roles') && !Schema::hasColumn('roles', 'base_role_id')) {
            Schema::table('roles', function (Blueprint $table): void {
                $table->string('base_role_id', 150)->nullable()->after('agence_id')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('roles') && Schema::hasColumn('roles', 'base_role_id')) {
            Schema::table('roles', function (Blueprint $table): void {
                $table->dropColumn('base_role_id');
            });
        }
    }
};
