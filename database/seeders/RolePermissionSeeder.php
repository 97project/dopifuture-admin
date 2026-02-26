<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Permissions ──────────────────────────────────
        $modules = [
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
            // CMS
            'pages' => ['view', 'create', 'edit', 'delete'],
            'posts' => ['view', 'create', 'edit', 'delete'],
            'categories' => ['view', 'create', 'edit', 'delete'],
            'menus' => ['view', 'create', 'edit', 'delete'],
            'media' => ['view', 'create', 'delete'],
            'faqs' => ['view', 'create', 'edit', 'delete'],
            'forms' => ['view', 'create', 'edit', 'delete'],
            // DopiFuture modules
            'applications' => ['view', 'create', 'edit', 'delete'],
            'schools' => ['view', 'create', 'edit', 'delete'],
            'classes' => ['view', 'create', 'edit', 'delete'],
            'licenses' => ['view', 'create', 'edit', 'delete'],
            'registration_requests' => ['view', 'edit', 'delete', 'export'],
        ];

        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(
                    ['name' => "{$module}.{$action}", 'guard_name' => 'web']
                );
            }
        }

        // ─── Roles ────────────────────────────────────────

        // 1. Super Admin – all permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // 2. Admin – everything except dangerous operations
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::whereNotIn('name', [
            'roles.delete',
            'permissions.sync',
        ])->get());

        // 3. Moderator – content management
        $moderator = Role::firstOrCreate(['name' => 'moderator', 'guard_name' => 'web']);
        $moderator->syncPermissions([
            'users.view',
            'pages.view',
            'pages.create',
            'pages.edit',
            'posts.view',
            'posts.create',
            'posts.edit',
            'posts.delete',
            'categories.view',
            'categories.create',
            'categories.edit',
            'menus.view',
            'menus.create',
            'menus.edit',
            'media.view',
            'media.create',
            'faqs.view',
            'faqs.create',
            'faqs.edit',
            'forms.view',
            'forms.create',
            'forms.edit',
            'notifications.view',
            'notifications.create',
            'notifications.send',
            'activity_logs.view',
            'profile.view',
            'profile.edit',
            'translations.view',
        ]);

        // 4. Editor – limited content editing
        $editor = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $editor->syncPermissions([
            'users.view',
            'pages.view',
            'pages.edit',
            'posts.view',
            'posts.create',
            'posts.edit',
            'categories.view',
            'media.view',
            'media.create',
            'faqs.view',
            'faqs.edit',
            'activity_logs.view',
            'profile.view',
            'profile.edit',
            'translations.view',
        ]);

        // 5. License Manager – license & school administration
        $licenseManager = Role::firstOrCreate(['name' => 'license-manager', 'guard_name' => 'web']);
        $licenseManager->syncPermissions([
            'users.view',
            'users.create',
            'users.edit',
            'schools.view',
            'schools.create',
            'schools.edit',
            'classes.view',
            'classes.create',
            'classes.edit',
            'licenses.view',
            'licenses.create',
            'licenses.edit',
            'licenses.delete',
            'applications.view',
            'registration_requests.view',
            'registration_requests.edit',
            'activity_logs.view',
            'profile.view',
            'profile.edit',
        ]);

        // 6. School Admin – manages their own school
        $schoolAdmin = Role::firstOrCreate(['name' => 'school-admin', 'guard_name' => 'web']);
        $schoolAdmin->syncPermissions([
            'users.view',
            'users.create',
            'users.edit',
            'schools.view',
            'schools.edit',
            'classes.view',
            'classes.create',
            'classes.edit',
            'classes.delete',
            'licenses.view',
            'applications.view',
            'profile.view',
            'profile.edit',
        ]);

        // 7. School Principal – manages classes & teachers
        $schoolPrincipal = Role::firstOrCreate(['name' => 'school-principal', 'guard_name' => 'web']);
        $schoolPrincipal->syncPermissions([
            'users.view',
            'schools.view',
            'classes.view',
            'classes.create',
            'classes.edit',
            'classes.delete',
            'applications.view',
            'profile.view',
            'profile.edit',
        ]);

        // 8. Teacher – sees their students & classes
        $teacher = Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);
        $teacher->syncPermissions([
            'users.view',
            'schools.view',
            'classes.view',
            'applications.view',
            'profile.view',
            'profile.edit',
        ]);

        // 9. Student – minimal access
        $student = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
        $student->syncPermissions([
            'applications.view',
            'profile.view',
            'profile.edit',
        ]);

        // ─── Default Admin User ───────────────────────────
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@dopifuture.com'],
            [
                'name' => 'Admin',
                'surname' => 'User',
                'password' => \Illuminate\Support\Facades\Hash::make('Dp!F2026#Adm1n$'),
                'email_verified_at' => now(),
                'status' => 'active',
                'locale' => 'tr',
                'timezone' => 'Europe/Istanbul',
            ]
        );
        $adminUser->assignRole('super-admin');
    }
}
