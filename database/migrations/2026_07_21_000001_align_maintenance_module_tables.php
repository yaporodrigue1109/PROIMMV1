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
        $this->ensureMaintenanceCategoriesTable();
        $this->ensureFonctionMaintenanceTable();
        $this->ensureTypeMaintenancesTable();
        $this->ensureMaintenanciersTable();
        $this->ensureMaintenanceTable();
        $this->ensureMaintenanceDetailTable();
        $this->syncLegacyTypeCategories();
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_detail');
        Schema::dropIfExists('maintenance');
        Schema::dropIfExists('maintenanciers');
        Schema::dropIfExists('type_maintenances');
        Schema::dropIfExists('fonction_maintenance');
        Schema::dropIfExists('maintenance_categories');
    }

    private function ensureMaintenanceCategoriesTable(): void
    {
        if (! Schema::hasTable('maintenance_categories')) {
            Schema::create('maintenance_categories', function (Blueprint $table) {
                $table->char('maintenance_category_id', 36)->primary();
                $table->string('name')->unique();
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            return;
        }

        Schema::table('maintenance_categories', function (Blueprint $table) {
            if (! Schema::hasColumn('maintenance_categories', 'description')) {
                $table->text('description')->nullable();
            }

            if (! Schema::hasColumn('maintenance_categories', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }

            if (! Schema::hasColumn('maintenance_categories', 'slug')) {
                $table->string('slug')->nullable();
            }
        });
    }

    private function ensureFonctionMaintenanceTable(): void
    {
        if (! Schema::hasTable('fonction_maintenance')) {
            Schema::create('fonction_maintenance', function (Blueprint $table) {
                $table->char('fonction_maintenance_id', 36)->primary();
                $table->string('agence_id', 150)->index();
                $table->string('name', 250);
                $table->string('description', 250)->nullable();
                $table->string('created_by', 150)->nullable();
                $table->string('updated_by', 150)->nullable();
                $table->timestamps();
            });

            return;
        }

        Schema::table('fonction_maintenance', function (Blueprint $table) {
            if (! Schema::hasColumn('fonction_maintenance', 'description')) {
                $table->string('description', 250)->nullable();
            }

            if (! Schema::hasColumn('fonction_maintenance', 'created_by')) {
                $table->string('created_by', 150)->nullable();
            }

            if (! Schema::hasColumn('fonction_maintenance', 'updated_by')) {
                $table->string('updated_by', 150)->nullable();
            }
        });
    }

    private function ensureTypeMaintenancesTable(): void
    {
        if (! Schema::hasTable('type_maintenances')) {
            Schema::create('type_maintenances', function (Blueprint $table) {
                $table->char('type_maintenance_id', 36)->primary();
                $table->string('agence_id', 150)->index();
                $table->string('name', 150);
                $table->string('categorie', 150)->nullable();
                $table->char('maintenance_category_id', 36)->nullable()->index();
                $table->decimal('duree_estimee', 8, 2)->nullable();
                $table->string('description', 250)->nullable();
                $table->string('created_by', 150)->nullable();
                $table->string('updated_by', 150)->nullable();
                $table->timestamps();
            });

            return;
        }

        Schema::table('type_maintenances', function (Blueprint $table) {
            if (! Schema::hasColumn('type_maintenances', 'categorie')) {
                $table->string('categorie', 150)->nullable();
            }

            if (! Schema::hasColumn('type_maintenances', 'maintenance_category_id')) {
                $table->char('maintenance_category_id', 36)->nullable()->index();
            }

            if (! Schema::hasColumn('type_maintenances', 'duree_estimee')) {
                $table->decimal('duree_estimee', 8, 2)->nullable();
            }

            if (! Schema::hasColumn('type_maintenances', 'description')) {
                $table->string('description', 250)->nullable();
            }

            if (! Schema::hasColumn('type_maintenances', 'created_by')) {
                $table->string('created_by', 150)->nullable();
            }

            if (! Schema::hasColumn('type_maintenances', 'updated_by')) {
                $table->string('updated_by', 150)->nullable();
            }
        });
    }

    private function ensureMaintenanciersTable(): void
    {
        if (! Schema::hasTable('maintenanciers')) {
            Schema::create('maintenanciers', function (Blueprint $table) {
                $table->char('maintenancier_id', 36)->primary();
                $table->char('fonction_maintenance_id', 36)->index();
                $table->string('agence_id', 150)->index();
                $table->string('name', 250);
                $table->string('tel1', 50);
                $table->string('tel2', 50)->nullable();
                $table->string('email', 250)->nullable();
                $table->boolean('statut')->default(true);
                $table->string('adresse', 250)->nullable();
                $table->string('entreprise', 250)->nullable();
                $table->unsignedBigInteger('type_piece_id')->nullable()->index();
                $table->string('numero_piece', 150)->nullable();
                $table->date('date_validite_piece')->nullable();
                $table->string('created_by', 150)->nullable();
                $table->string('updated_by', 150)->nullable();
                $table->timestamps();
            });

            return;
        }

        Schema::table('maintenanciers', function (Blueprint $table) {
            if (! Schema::hasColumn('maintenanciers', 'entreprise')) {
                $table->string('entreprise', 250)->nullable();
            }

            if (! Schema::hasColumn('maintenanciers', 'type_piece_id')) {
                $table->unsignedBigInteger('type_piece_id')->nullable()->index();
            }

            if (! Schema::hasColumn('maintenanciers', 'numero_piece')) {
                $table->string('numero_piece', 150)->nullable();
            }

            if (! Schema::hasColumn('maintenanciers', 'date_validite_piece')) {
                $table->date('date_validite_piece')->nullable();
            }

            if (! Schema::hasColumn('maintenanciers', 'created_by')) {
                $table->string('created_by', 150)->nullable();
            }

            if (! Schema::hasColumn('maintenanciers', 'updated_by')) {
                $table->string('updated_by', 150)->nullable();
            }
        });
    }

    private function ensureMaintenanceTable(): void
    {
        if (! Schema::hasTable('maintenance')) {
            Schema::create('maintenance', function (Blueprint $table) {
                $table->char('maintenance_id', 36)->primary();
                $table->string('agence_id', 150)->index();
                $table->string('proprietaire_id', 150)->nullable()->index();
                $table->string('lot_id', 150)->nullable()->index();
                $table->string('propriete_id', 150)->nullable()->index();
                $table->string('batiment_id', 150)->nullable()->index();
                $table->string('porte_id', 150)->nullable()->index();
                $table->string('titre', 250);
                $table->text('description')->nullable();
                $table->enum('statut', ['en attente', 'en cours', 'terminer', 'annuler'])->default('en attente');
                $table->integer('montant_global')->default(0);
                $table->enum('prise_en_charge_par', ['proprietaire', 'locataire', 'agence'])->default('proprietaire');
                $table->string('created_by', 150)->nullable();
                $table->string('updated_by', 150)->nullable();
                $table->string('deleted_by', 150)->nullable();
                $table->timestamps();
                $table->softDeletes();
            });

            return;
        }

        Schema::table('maintenance', function (Blueprint $table) {
            if (! Schema::hasColumn('maintenance', 'proprietaire_id')) {
                $table->string('proprietaire_id', 150)->nullable()->index();
            }

            if (! Schema::hasColumn('maintenance', 'lot_id')) {
                $table->string('lot_id', 150)->nullable()->index();
            }

            if (! Schema::hasColumn('maintenance', 'propriete_id')) {
                $table->string('propriete_id', 150)->nullable()->index();
            }

            if (! Schema::hasColumn('maintenance', 'batiment_id')) {
                $table->string('batiment_id', 150)->nullable()->index();
            }

            if (! Schema::hasColumn('maintenance', 'porte_id')) {
                $table->string('porte_id', 150)->nullable()->index();
            }

            if (! Schema::hasColumn('maintenance', 'description')) {
                $table->text('description')->nullable();
            }

            if (! Schema::hasColumn('maintenance', 'deleted_by')) {
                $table->string('deleted_by', 150)->nullable();
            }

            if (! Schema::hasColumn('maintenance', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    private function ensureMaintenanceDetailTable(): void
    {
        if (! Schema::hasTable('maintenance_detail')) {
            Schema::create('maintenance_detail', function (Blueprint $table) {
                $table->char('maintenance_detail_id', 36)->primary();
                $table->string('maintenance_id', 150)->index();
                $table->string('maintenancier_id', 150)->index();
                $table->string('type_intervention_id', 150)->index();
                $table->date('date_debut')->nullable();
                $table->date('date_fin')->nullable();
                $table->enum('priorite', ['basse', 'normale', 'haute'])->default('normale');
                $table->integer('montant')->default(0);
                $table->text('note')->nullable();
                $table->enum('statut', ['en attente', 'en cours', 'terminer', 'annuler'])->default('en attente');
                $table->string('created_by', 150)->nullable();
                $table->string('updated_by', 150)->nullable();
                $table->string('deleted_by', 150)->nullable();
                $table->timestamps();
                $table->softDeletes();
            });

            return;
        }

        Schema::table('maintenance_detail', function (Blueprint $table) {
            if (! Schema::hasColumn('maintenance_detail', 'note')) {
                $table->text('note')->nullable();
            }

            if (! Schema::hasColumn('maintenance_detail', 'deleted_by')) {
                $table->string('deleted_by', 150)->nullable();
            }

            if (! Schema::hasColumn('maintenance_detail', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    private function syncLegacyTypeCategories(): void
    {
        if (! Schema::hasTable('type_maintenances') || ! Schema::hasTable('maintenance_categories')) {
            return;
        }

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
};
