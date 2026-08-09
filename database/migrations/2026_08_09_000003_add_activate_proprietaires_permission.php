<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('modules') || !Schema::hasTable('module_actions')) {
            return;
        }

        $moduleId = DB::table('modules')->where('slug', 'proprietaires')->value('module_id');

        if (!$moduleId) {
            return;
        }

        $actionId = DB::table('module_actions')
            ->where('module_id', $moduleId)
            ->where('slug', 'activate')
            ->value('module_action_id');

        if (!$actionId) {
            $actionId = (string) Str::uuid();

            DB::table('module_actions')->insert([
                'module_action_id' => $actionId,
                'module_id' => $moduleId,
                'name' => 'Activer / Désactiver',
                'slug' => 'activate',
                'description' => 'Autorise l’activation et la désactivation d’un propriétaire.',
                'is_critical' => true,
                'order_index' => 5,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (!Schema::hasTable('role_permissions')) {
            return;
        }

        $editActionId = DB::table('module_actions')
            ->where('module_id', $moduleId)
            ->where('slug', 'edit')
            ->value('module_action_id');

        if (!$editActionId) {
            return;
        }

        $roleIds = DB::table('role_permissions')
            ->where('module_id', $moduleId)
            ->where('module_action_id', $editActionId)
            ->where('is_allowed', true)
            ->pluck('role_id');

        foreach ($roleIds as $roleId) {
            $permission = DB::table('role_permissions')
                ->where('role_id', $roleId)
                ->where('module_id', $moduleId)
                ->where('module_action_id', $actionId);

            if ($permission->exists()) {
                $permission->update([
                    'is_allowed' => true,
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('role_permissions')->insert([
                    'role_permission_id' => (string) Str::uuid(),
                    'role_id' => $roleId,
                    'module_id' => $moduleId,
                    'module_action_id' => $actionId,
                    'is_allowed' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('modules') || !Schema::hasTable('module_actions')) {
            return;
        }

        $moduleId = DB::table('modules')->where('slug', 'proprietaires')->value('module_id');
        $actionId = $moduleId
            ? DB::table('module_actions')->where('module_id', $moduleId)->where('slug', 'activate')->value('module_action_id')
            : null;

        if (!$actionId) {
            return;
        }

        if (Schema::hasTable('role_permissions')) {
            DB::table('role_permissions')->where('module_action_id', $actionId)->delete();
        }

        DB::table('module_actions')->where('module_action_id', $actionId)->delete();
    }
};
