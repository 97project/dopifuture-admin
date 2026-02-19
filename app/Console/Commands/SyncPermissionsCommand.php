<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;

class SyncPermissionsCommand extends Command
{
    protected $signature = 'permissions:sync';
    protected $description = 'Synchronize module permissions';

    protected array $modules = [
        'users' => ['view', 'create', 'edit', 'delete', 'bulk_action', 'export'],
        'roles' => ['view', 'create', 'edit', 'delete'],
        'permissions' => ['view', 'sync'],
        'settings' => ['view', 'edit'],
        'languages' => ['view', 'create', 'edit', 'delete'],
        'translations' => ['view', 'create', 'edit', 'delete', 'import', 'export'],
        'activity_logs' => ['view', 'export'],
        'api_keys' => ['view', 'create', 'edit', 'delete', 'rotate'],
        'profile' => ['view', 'edit', 'two_factor'],
    ];

    public function handle(): int
    {
        $this->info('Syncing permissions...');

        $existingPermissions = Permission::pluck('name')->toArray();
        $newPermissions = [];
        $allPermissions = [];

        foreach ($this->modules as $module => $actions) {
            foreach ($actions as $action) {
                $permissionName = "{$module}.{$action}";
                $allPermissions[] = $permissionName;

                if (!in_array($permissionName, $existingPermissions)) {
                    Permission::create(['name' => $permissionName, 'guard_name' => 'web']);
                    $newPermissions[] = $permissionName;
                    $this->line("  + {$permissionName}");
                }
            }
        }

        $deprecated = array_diff($existingPermissions, $allPermissions);
        foreach ($deprecated as $perm) {
            $this->warn("  ~ {$perm} (deprecated - not in module list)");
        }

        $this->info("Done! Added: " . count($newPermissions) . ", Deprecated: " . count($deprecated));

        return self::SUCCESS;
    }
}
