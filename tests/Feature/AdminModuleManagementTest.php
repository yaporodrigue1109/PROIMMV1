<?php

namespace Tests\Feature;

use App\Models\Module;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminModuleManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('modules', function (Blueprint $table): void {
            $table->uuid('module_id')->primary();
            $table->string('name', 150);
            $table->string('slug', 150)->unique();
            $table->string('icon')->nullable();
            $table->string('route')->nullable();
            $table->uuid('parent_id')->nullable();
            $table->unsignedInteger('order_index')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('created_by', 150)->nullable();
            $table->string('updated_by', 150)->nullable();
            $table->string('deleted_by', 150)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('module_actions', function (Blueprint $table): void {
            $table->uuid('module_action_id')->primary();
            $table->uuid('module_id');
            $table->string('name', 150);
            $table->string('slug', 100);
            $table->string('description')->nullable();
            $table->boolean('is_critical')->default(false);
            $table->unsignedInteger('order_index')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('created_by', 150)->nullable();
            $table->string('updated_by', 150)->nullable();
            $table->timestamps();
            $table->unique(['module_id', 'slug']);
        });

        $this->withoutMiddleware();
    }

    public function test_admin_can_manage_a_module_and_its_actions(): void
    {
        $createResponse = $this->post('/admin/modules', [
            'name' => 'Gestion des missions',
            'slug' => 'missions',
            'route' => 'agence.missions.index',
            'icon' => 'admin/icones_module/missions.svg',
            'parent' => 'none',
            'status' => 'Actif',
            'actions' => [
                ['label' => 'Voir', 'slug' => 'view', 'order' => 1],
                ['label' => 'Créer', 'slug' => 'create', 'order' => 2, 'is_critical' => true],
            ],
        ]);

        $createResponse->assertSessionHasNoErrors();
        $createResponse->assertRedirect('/admin/modules/missions/edit');

        $module = Module::query()->where('slug', 'missions')->firstOrFail();
        $this->assertTrue($module->is_active);
        $this->assertCount(2, $module->actions);
        $this->assertTrue($module->actions->firstWhere('slug', 'create')->is_critical);
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/i', $module->module_id);

        $viewAction = $module->actions->firstWhere('slug', 'view');

        $updateResponse = $this->put('/admin/modules/missions', [
            'name' => 'Opérations',
            'slug' => 'operations',
            'route' => 'agence.operations.index',
            'icon' => null,
            'parent' => 'none',
            'status' => 'Actif',
            'actions' => [
                [
                    'module_action_id' => $viewAction->module_action_id,
                    'label' => 'Consulter',
                    'slug' => 'view',
                    'order' => 1,
                ],
                ['label' => 'Valider', 'slug' => 'validate', 'order' => 2, 'is_critical' => true],
            ],
        ]);

        $updateResponse->assertSessionHasNoErrors();
        $updateResponse->assertRedirect('/admin/modules/operations/edit');

        $module->refresh();
        $this->assertSame('Opérations', $module->name);
        $this->assertSame(['view', 'validate'], $module->actions()->pluck('slug')->all());
        $this->assertTrue($module->actions()->where('slug', 'validate')->value('is_critical'));
        $this->assertSame(
            $viewAction->module_action_id,
            $module->actions()->where('slug', 'view')->value('module_action_id')
        );

        $toggleResponse = $this->patch('/admin/modules/operations/toggle');
        $toggleResponse->assertSessionHasNoErrors();
        $this->assertFalse($module->fresh()->is_active);

        $secondModule = Module::query()->create([
            'name' => 'Second module',
            'slug' => 'second-module',
            'order_index' => 2,
            'is_active' => true,
        ]);

        $reorderResponse = $this->post('/admin/modules/reorder', [
            'items' => [
                ['module_id' => $secondModule->module_id, 'parent_id' => null, 'order_index' => 1],
                ['module_id' => $module->module_id, 'parent_id' => null, 'order_index' => 2],
            ],
        ]);

        $reorderResponse->assertSessionHasNoErrors();
        $this->assertSame(1, $secondModule->fresh()->order_index);
        $this->assertSame(2, $module->fresh()->order_index);

        $deleteResponse = $this->delete('/admin/modules/operations');
        $deleteResponse->assertSessionHasNoErrors();
        $deleteResponse->assertRedirect('/admin/modules');
        $this->assertSoftDeleted('modules', ['module_id' => $module->module_id]);
    }
}
