<?php

namespace Tests\Feature;

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\Module;
use App\Models\ModuleAction;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

class AgencyRolePermissionManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('roles', function (Blueprint $table): void {
            $table->string('role_id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->string('agence_id')->nullable();
            $table->string('base_role_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table): void {
            $table->string('id_users')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('agence_id')->nullable();
            $table->string('role_id')->nullable();
            $table->boolean('is_responsable')->default(false);
            $table->string('statut')->default('actif');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('modules', function (Blueprint $table): void {
            $table->uuid('module_id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('route')->nullable();
            $table->string('icon')->nullable();
            $table->uuid('parent_id')->nullable();
            $table->unsignedInteger('order_index')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('module_actions', function (Blueprint $table): void {
            $table->uuid('module_action_id')->primary();
            $table->uuid('module_id');
            $table->string('name');
            $table->string('slug');
            $table->unsignedInteger('order_index')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_critical')->default(false);
            $table->timestamps();
        });

        Schema::create('role_permissions', function (Blueprint $table): void {
            $table->uuid('role_permission_id')->primary();
            $table->string('role_id');
            $table->uuid('module_id');
            $table->uuid('module_action_id');
            $table->boolean('is_allowed')->default(true);
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
        });

        $this->withoutMiddleware();
    }

    public function test_responsable_can_create_update_and_delete_an_agency_role(): void
    {
        (new Role())->forceFill([
            'role_id' => 'role-responsable',
            'name' => 'Responsable',
            'slug' => 'role-responsable',
            'is_active' => true,
        ])->save();

        DB::table('users')->insert([
            'id_users' => 'responsable-1',
            'name' => 'Responsable agence',
            'email' => 'responsable@example.test',
            'password' => bcrypt('password'),
            'agence_id' => 'agence-1',
            'role_id' => 'role-responsable',
            'is_responsable' => true,
            'statut' => 'actif',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $module = Module::query()->create([
            'name' => 'Biens',
            'slug' => 'biens',
            'is_active' => true,
        ]);
        $view = ModuleAction::query()->create([
            'module_id' => $module->module_id,
            'name' => 'Voir',
            'slug' => 'view',
            'is_active' => true,
        ]);
        $delete = ModuleAction::query()->create([
            'module_id' => $module->module_id,
            'name' => 'Supprimer',
            'slug' => 'delete',
            'is_critical' => true,
            'is_active' => true,
        ]);

        $this->actingAs(User::query()->findOrFail('responsable-1'), 'user');

        $this->post('/agence/parametrage/roles', [
            'name' => 'Gestionnaire',
            'description' => 'Gestion des biens',
            'permissions' => [$view->module_action_id, $delete->module_action_id],
        ])->assertSessionHasNoErrors()
            ->assertRedirect('/agence/parametrage?tab=roles');

        $role = Role::query()->where('name', 'Gestionnaire')->firstOrFail();
        $this->assertSame('agence-1', $role->agence_id);
        $this->assertCount(2, $role->allowedPermissions()->get());

        $this->put("/agence/parametrage/roles/{$role->role_id}", [
            'name' => 'Gestionnaire principal',
            'description' => 'Gestion mise à jour',
            'permissions' => [$view->module_action_id],
        ])->assertSessionHasNoErrors()
            ->assertRedirect('/agence/parametrage?tab=roles');

        $this->assertSame('Gestionnaire principal', $role->fresh()->name);
        $this->assertSame([$view->module_action_id], $role->allowedPermissions()->pluck('module_action_id')->all());

        DB::table('users')->insert([
            'id_users' => 'agent-1',
            'name' => 'Agent agence',
            'email' => 'agent@example.test',
            'password' => bcrypt('password'),
            'agence_id' => 'agence-1',
            'role_id' => $role->role_id,
            'is_responsable' => false,
            'statut' => 'actif',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->delete("/agence/parametrage/roles/{$role->role_id}")
            ->assertSessionHas('error');
        $this->assertDatabaseHas('roles', ['role_id' => $role->role_id]);

        DB::table('users')->where('id_users', 'agent-1')->delete();

        $this->delete("/agence/parametrage/roles/{$role->role_id}")
            ->assertSessionHasNoErrors()
            ->assertRedirect('/agence/parametrage?tab=roles');
        $this->assertDatabaseMissing('roles', ['role_id' => $role->role_id]);
    }

    public function test_modifying_a_predefined_role_creates_an_agency_override(): void
    {
        (new Role())->forceFill([
            'role_id' => 'role-responsable',
            'name' => 'Responsable',
            'slug' => 'role-responsable',
            'is_active' => true,
        ])->save();
        (new Role())->forceFill([
            'role_id' => 'role-agent',
            'name' => 'Agent',
            'slug' => 'role-agent',
            'is_active' => true,
        ])->save();

        foreach ([
            ['responsable-1', 'Responsable', 'responsable@test.local', 'agence-1', 'role-responsable', true],
            ['agent-1', 'Agent agence 1', 'agent1@test.local', 'agence-1', 'role-agent', false],
            ['agent-2', 'Agent agence 2', 'agent2@test.local', 'agence-2', 'role-agent', false],
        ] as [$id, $name, $email, $agenceId, $roleId, $isResponsable]) {
            DB::table('users')->insert([
                'id_users' => $id,
                'name' => $name,
                'email' => $email,
                'password' => bcrypt('password'),
                'agence_id' => $agenceId,
                'role_id' => $roleId,
                'is_responsable' => $isResponsable,
                'statut' => 'actif',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $module = Module::query()->create([
            'name' => 'Biens',
            'slug' => 'biens',
            'is_active' => true,
        ]);
        $view = ModuleAction::query()->create([
            'module_id' => $module->module_id,
            'name' => 'Voir',
            'slug' => 'view',
            'is_active' => true,
        ]);

        $this->actingAs(User::query()->findOrFail('responsable-1'), 'user');

        $this->put('/agence/parametrage/roles/role-agent', [
            'name' => 'Agent commercial',
            'description' => 'Rôle adapté à cette agence',
            'permissions' => [$view->module_action_id],
        ])->assertSessionHasNoErrors();

        $override = Role::query()
            ->where('agence_id', 'agence-1')
            ->where('base_role_id', 'role-agent')
            ->firstOrFail();

        $this->assertSame('Agent', Role::query()->findOrFail('role-agent')->name);
        $this->assertSame('Agent commercial', $override->name);
        $this->assertSame($override->role_id, DB::table('users')->where('id_users', 'agent-1')->value('role_id'));
        $this->assertSame('role-agent', DB::table('users')->where('id_users', 'agent-2')->value('role_id'));
        $this->assertSame([$view->module_action_id], $override->allowedPermissions()->pluck('module_action_id')->all());
    }

    public function test_responsable_can_reduce_own_permissions_but_keeps_parametrage(): void
    {
        (new Role())->forceFill([
            'role_id' => 'role-responsable',
            'name' => 'Responsable',
            'slug' => 'role-responsable',
            'is_active' => true,
        ])->save();

        DB::table('users')->insert([
            'id_users' => 'responsable-1',
            'name' => 'Responsable agence',
            'email' => 'responsable@test.local',
            'password' => bcrypt('password'),
            'agence_id' => 'agence-1',
            'role_id' => 'role-responsable',
            'is_responsable' => true,
            'statut' => 'actif',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $biens = Module::query()->create(['name' => 'Biens', 'slug' => 'biens', 'is_active' => true]);
        $parametrage = Module::query()->create(['name' => 'Paramétrage', 'slug' => 'parametrage', 'is_active' => true]);
        $viewBiens = ModuleAction::query()->create(['module_id' => $biens->module_id, 'name' => 'Voir', 'slug' => 'view', 'is_active' => true]);
        $deleteBiens = ModuleAction::query()->create(['module_id' => $biens->module_id, 'name' => 'Supprimer', 'slug' => 'delete', 'is_active' => true]);
        $viewParametrage = ModuleAction::query()->create(['module_id' => $parametrage->module_id, 'name' => 'Voir', 'slug' => 'view', 'is_active' => true]);

        $existingOverride = Role::query()->create([
            'name' => 'Responsable agence',
            'slug' => 'role-responsable-agence-1',
            'description' => 'Copie existante',
            'agence_id' => 'agence-1',
            'base_role_id' => 'role-responsable',
            'is_active' => true,
        ]);

        $this->actingAs(User::query()->findOrFail('responsable-1'), 'user');

        $this->put("/agence/parametrage/roles/{$existingOverride->role_id}", [
            'name' => 'Responsable principal',
            'description' => 'Accès ajustés',
            'permissions' => [$viewBiens->module_action_id],
        ])->assertSessionHasNoErrors();

        $override = Role::query()
            ->where('agence_id', 'agence-1')
            ->where('base_role_id', 'role-responsable')
            ->firstOrFail();
        $permissionIds = $override->allowedPermissions()->pluck('module_action_id')->all();

        $this->assertContains($viewBiens->module_action_id, $permissionIds);
        $this->assertContains($viewParametrage->module_action_id, $permissionIds);
        $this->assertNotContains($deleteBiens->module_action_id, $permissionIds);
        $this->assertFalse($override->hasPermission('biens', 'delete'));
        $this->assertTrue($override->hasPermission('parametrage', 'view'));
        $this->assertSame($override->role_id, DB::table('users')->where('id_users', 'responsable-1')->value('role_id'));
    }

    public function test_sidebar_only_displays_modules_with_view_permission(): void
    {
        $role = Role::query()->create([
            'name' => 'Agent personnalisé',
            'slug' => 'agent-personnalise',
            'agence_id' => 'agence-1',
            'is_active' => true,
        ]);
        $user = new User([
            'agence_id' => 'agence-1',
            'role_id' => $role->role_id,
            'is_responsable' => false,
        ]);
        $user->setRelation('role', $role);

        $visibleModule = Module::query()->create([
            'name' => 'Visible',
            'slug' => 'visible',
            'route' => 'agence.dashboard',
            'is_active' => true,
        ]);
        $hiddenModule = Module::query()->create([
            'name' => 'Masqué',
            'slug' => 'masque',
            'route' => 'agence.dashboard',
            'is_active' => true,
        ]);
        $viewAction = ModuleAction::query()->create([
            'module_id' => $visibleModule->module_id,
            'name' => 'Voir',
            'slug' => 'view',
            'is_active' => true,
        ]);
        $createAction = ModuleAction::query()->create([
            'module_id' => $hiddenModule->module_id,
            'name' => 'Créer',
            'slug' => 'create',
            'is_active' => true,
        ]);

        foreach ([[$visibleModule, $viewAction], [$hiddenModule, $createAction]] as [$module, $action]) {
            RolePermission::query()->create([
                'role_id' => $role->role_id,
                'module_id' => $module->module_id,
                'module_action_id' => $action->module_action_id,
                'is_allowed' => true,
            ]);
        }

        $method = new ReflectionMethod(HandleInertiaRequests::class, 'navigationModules');
        $navigation = $method->invoke(app(HandleInertiaRequests::class), $user);

        $this->assertSame(['visible'], array_column($navigation, 'slug'));
    }
}
