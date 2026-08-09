<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('module_actions') && !Schema::hasColumn('module_actions', 'is_critical')) {
            Schema::table('module_actions', function (Blueprint $table): void {
                $table->boolean('is_critical')->default(false)->after('description')->index();
            });

            DB::table('module_actions')
                ->whereIn('slug', ['delete', 'validate', 'cancel', 'manage'])
                ->update(['is_critical' => true]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('module_actions') && Schema::hasColumn('module_actions', 'is_critical')) {
            Schema::table('module_actions', function (Blueprint $table): void {
                $table->dropColumn('is_critical');
            });
        }
    }
};
