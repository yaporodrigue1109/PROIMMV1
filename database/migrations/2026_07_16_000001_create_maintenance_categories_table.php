<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_categories', function (Blueprint $table) {
            $table->uuid('maintenance_category_id')->primary();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('type_maintenances', function (Blueprint $table) {
            $table->uuid('maintenance_category_id')->nullable()->after('categorie');
            $table->foreign('maintenance_category_id')
                ->references('maintenance_category_id')
                ->on('maintenance_categories')
                ->nullOnDelete();
        });

        $distinctNames = DB::table('type_maintenances')
            ->whereNotNull('categorie')
            ->where('categorie', '!=', '')
            ->distinct()
            ->pluck('categorie');

        foreach ($distinctNames as $name) {
            $normalized = trim((string) $name);

            if ($normalized === '') {
                continue;
            }

            $category = DB::table('maintenance_categories')->where('name', $normalized)->first();

            if (! $category) {
                DB::table('maintenance_categories')->insert([
                    'maintenance_category_id' => (string) Str::uuid(),
                    'name' => $normalized,
                    'slug' => Str::slug($normalized),
                    'description' => null,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $category = DB::table('maintenance_categories')->where('name', $normalized)->first();
            }

            DB::table('type_maintenances')
                ->where('categorie', $name)
                ->update(['maintenance_category_id' => $category->maintenance_category_id]);
        }
    }

    public function down(): void
    {
        Schema::table('type_maintenances', function (Blueprint $table) {
            $table->dropForeign(['maintenance_category_id']);
            $table->dropColumn('maintenance_category_id');
        });

        Schema::dropIfExists('maintenance_categories');
    }
};
