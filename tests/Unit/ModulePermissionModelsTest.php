<?php

namespace Tests\Unit;

use App\Models\Module;
use App\Models\ModuleAction;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use Tests\TestCase;

class ModulePermissionModelsTest extends TestCase
{
    public function test_module_permission_models_use_string_primary_keys(): void
    {
        $models = [
            Module::class => 'module_id',
            ModuleAction::class => 'module_action_id',
            RolePermission::class => 'role_permission_id',
            Role::class => 'role_id',
        ];

        foreach ($models as $modelClass => $primaryKey) {
            $model = new $modelClass();

            $this->assertSame($primaryKey, $model->getKeyName());
            $this->assertSame('string', $model->getKeyType());
            $this->assertFalse($model->getIncrementing());
        }
    }

    public function test_module_permission_relations_use_the_expected_foreign_keys(): void
    {
        $module = new Module();
        $action = new ModuleAction();
        $role = new Role();

        $this->assertSame('parent_id', $module->children()->getForeignKeyName());
        $this->assertSame('module_id', $module->actions()->getForeignKeyName());
        $this->assertSame('module_id', $action->module()->getForeignKeyName());
        $this->assertSame('role_id', $role->rolePermissions()->getForeignKeyName());
    }

    public function test_responsable_has_all_permissions_by_default(): void
    {
        $role = new Role([
            'name' => 'Responsable',
            'slug' => 'role-responsable',
            'is_active' => true,
        ]);

        $user = new User(['is_responsable' => true]);
        $user->setRelation('role', $role);

        $this->assertTrue($role->grantsAllPermissions());
        $this->assertTrue($role->hasPermission('nouveau-module', 'nouvelle-action'));
        $this->assertTrue($user->hasPermission('nouvelle-action_nouveau-module'));
        $this->assertTrue($user->canPerform('nouveau-module', 'nouvelle-action'));
    }
}
