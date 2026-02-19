<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;

class SyncPermissions extends Command
{
    protected $signature = 'permissions:sync';

    protected $description = 'Sync permissions: add new ones, deprecate removed ones (never delete)';

    /**
     * Canonical permission definitions.
     * Modules and their actions — the single source of truth.
     */
    protected function getModules(): array
    {
        return [
            'users' => ['view', 'create', 'edit', 'delete', 'bulk_action', 'export'],
            'roles' => ['view', 'create', 'edit', 'delete'],
            'permissions' => ['view', 'sync'],
            'settings' => ['view', 'edit'],
            'languages' => ['view', 'create', 'edit', 'delete'],
            'translations' => ['view', 'create', 'edit', 'delete', 'import', 'export'],
            'activity_logs' => ['view', 'export'],
            'api_keys' => ['view', 'create', 'edit', 'delete', 'rotate'],
            'profile' => ['view', 'edit', 'two_factor'],
            'notifications' => ['view', 'create', 'edit', 'delete', 'send'],
            'backups' => ['view', 'create', 'delete'],
            'pages' => ['view', 'create', 'edit', 'delete'],
            'posts' => ['view', 'create', 'edit', 'delete'],
            'categories' => ['view', 'create', 'edit', 'delete'],
            'menus' => ['view', 'create', 'edit', 'delete'],
            'media' => ['view', 'create', 'delete'],
            'faqs' => ['view', 'create', 'edit', 'delete'],
            'forms' => ['view', 'create', 'edit', 'delete'],
            'applications' => ['view', 'create', 'edit', 'delete'],
            'schools' => ['view', 'create', 'edit', 'delete'],
            'classes' => ['view', 'create', 'edit', 'delete'],
            'licenses' => ['view', 'create', 'edit', 'delete'],
            'registration_requests' => ['view', 'edit', 'delete', 'export'],
        ];
    }

    public function handle(): int
    {
        $modules = $this->getModules();
        $canonical = [];
        $created = 0;
        $deprecated = 0;
        $reactivated = 0;

        // Build canonical list
        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $canonical[] = "{$module}.{$action}";
            }
        }

        // Create new permissions
        foreach ($canonical as $name) {
            $perm = Permission::where('name', $name)->where('guard_name', 'web')->first();

            if (!$perm) {
                Permission::create([
                    'name' => $name,
                    'guard_name' => 'web',
                ]);
                $created++;
                $this->line("  <info>+</info> Created: {$name}");
            } elseif ($perm->is_deprecated) {
                $perm->is_deprecated = false;
                $perm->save();
                $reactivated++;
                $this->line("  <comment>↻</comment> Reactivated: {$name}");
            }
        }

        // Deprecate permissions no longer in canonical list
        $existing = Permission::where('guard_name', 'web')
            ->where('is_deprecated', false)
            ->pluck('name')
            ->toArray();

        foreach ($existing as $name) {
            if (!in_array($name, $canonical)) {
                Permission::where('name', $name)->where('guard_name', 'web')
                    ->update(['is_deprecated' => true]);
                $deprecated++;
                $this->line("  <fg=yellow>⚠</> Deprecated: {$name}");
            }
        }

        $this->newLine();
        $this->info("Sync complete: {$created} created, {$reactivated} reactivated, {$deprecated} deprecated.");

        // Clear Spatie cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return self::SUCCESS;
    }
}
