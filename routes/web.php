<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\TwoFactorController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\TranslationController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\FormController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\NotificationTemplateController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\AccountDeletionController;
use App\Http\Controllers\Admin\ApplicationController;
use App\Http\Controllers\Admin\SchoolController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\LicenseController;
use App\Http\Controllers\Admin\RegistrationRequestController;
use App\Http\Controllers\Admin\PermissionController;

/*
|--------------------------------------------------------------------------
| Admin Web Routes
|--------------------------------------------------------------------------
*/

// Auth routes (guest)
Route::prefix('admin')->name('admin.')->middleware(['web', \App\Http\Middleware\SetLocale::class])->group(function () {

    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login'])->name('login.submit')
        ->middleware('throttle:5,1');

    Route::get('2fa/challenge', [TwoFactorController::class, 'challenge'])->name('2fa.challenge');
    Route::post('2fa/verify', [TwoFactorController::class, 'verify'])->name('2fa.verify');

    // Authenticated admin routes
    Route::middleware([
        \App\Http\Middleware\AdminAuth::class,
        \App\Http\Middleware\CheckTwoFactor::class,
        \App\Http\Middleware\SecureHeaders::class,
    ])->group(function () {

        Route::post('logout', [LoginController::class, 'logout'])->name('logout');
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('switch-locale', [DashboardController::class, 'switchLocale'])->name('switch-locale');
        Route::post('toggle-dark-mode', [DashboardController::class, 'toggleDarkMode'])->name('toggle-dark-mode');

        // 2FA Setup
        Route::get('2fa/setup', [TwoFactorController::class, 'setup'])->name('2fa.setup');
        Route::post('2fa/enable', [TwoFactorController::class, 'enable'])->name('2fa.enable');
        Route::post('2fa/disable', [TwoFactorController::class, 'disable'])->name('2fa.disable');
        Route::post('2fa/recovery-codes', [TwoFactorController::class, 'regenerateRecoveryCodes'])->name('2fa.recovery-codes');
        Route::post('2fa/regenerate-recovery', [TwoFactorController::class, 'regenerateRecoveryCodes'])->name('2fa.regenerate-recovery');

        // Profile
        Route::get('profile', [ProfileController::class, 'index'])->name('profile');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

        // Users
        Route::post('users/bulk-action', [UserController::class, 'bulkAction'])->name('users.bulk-action');
        Route::resource('users', UserController::class);

        // User API Key management (admin)
        Route::post('users/{user}/api-keys', [UserController::class, 'storeApiKey'])->name('users.api-keys.store');
        Route::put('users/{user}/api-keys/{apiKey}/toggle', [UserController::class, 'toggleApiKey'])->name('users.api-keys.toggle');
        Route::delete('users/{user}/api-keys/{apiKey}', [UserController::class, 'destroyApiKey'])->name('users.api-keys.destroy');

        // Roles & Permissions
        Route::post('roles/sync-permissions', [RoleController::class, 'syncPermissions'])->name('roles.sync-permissions');
        Route::resource('roles', RoleController::class);

        // Settings
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

        // Languages
        Route::resource('languages', LanguageController::class)->except('show');

        // Translations
        Route::get('translations/export', [TranslationController::class, 'export'])->name('translations.export');
        Route::post('translations/import', [TranslationController::class, 'import'])->name('translations.import');
        Route::resource('translations', TranslationController::class);

        // Activity Logs
        Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::get('activity-logs/export', [ActivityLogController::class, 'export'])->name('activity-logs.export');

        // ── Phase 2: CMS ────────────────────────────────────

        // Pages
        Route::resource('pages', PageController::class)->except('show');

        // Posts
        Route::resource('posts', PostController::class)->except('show');

        // Categories
        Route::resource('categories', CategoryController::class)->except('show');

        // Menus
        Route::resource('menus', MenuController::class)->except('show');
        Route::post('menus/{menu}/items', [MenuController::class, 'storeItem'])->name('menus.items.store');
        Route::put('menus/{menu}/items', [MenuController::class, 'updateItems'])->name('menus.items.update');
        Route::delete('menus/{menu}/items/{item}', [MenuController::class, 'destroyItem'])->name('menus.items.destroy');

        // ── Phase 2: File Manager ───────────────────────────

        Route::get('media', [MediaController::class, 'index'])->name('media.index');
        Route::post('media/upload', [MediaController::class, 'upload'])->name('media.upload');
        Route::delete('media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');
        Route::post('media/folder', [MediaController::class, 'createFolder'])->name('media.folder');

        // ── Phase 2: FAQ ────────────────────────────────────

        Route::get('faqs', [FaqController::class, 'index'])->name('faqs.index');
        Route::get('faqs/create', [FaqController::class, 'create'])->name('faqs.create');
        Route::post('faqs', [FaqController::class, 'store'])->name('faqs.store');
        Route::get('faqs/{faq}/edit', [FaqController::class, 'edit'])->name('faqs.edit');
        Route::put('faqs/{faq}', [FaqController::class, 'update'])->name('faqs.update');
        Route::delete('faqs/{faq}', [FaqController::class, 'destroy'])->name('faqs.destroy');
        // FAQ Categories
        Route::get('faq-categories/create', [FaqController::class, 'createCategory'])->name('faq-categories.create');
        Route::post('faq-categories', [FaqController::class, 'storeCategory'])->name('faq-categories.store');
        Route::get('faq-categories/{category}/edit', [FaqController::class, 'editCategory'])->name('faq-categories.edit');
        Route::put('faq-categories/{category}', [FaqController::class, 'updateCategory'])->name('faq-categories.update');
        Route::delete('faq-categories/{category}', [FaqController::class, 'destroyCategory'])->name('faq-categories.destroy');

        // ── Phase 2: Forms ──────────────────────────────────

        Route::resource('forms', FormController::class)->except('show');
        Route::get('forms/{form}/submissions', [FormController::class, 'submissions'])->name('forms.submissions');
        Route::get('forms/{form}/submissions/{submission}', [FormController::class, 'showSubmission'])->name('forms.submissions.show');
        Route::delete('forms/{form}/submissions/{submission}', [FormController::class, 'destroySubmission'])->name('forms.submissions.destroy');

        // ── Phase 3: Notifications ──────────────────────────

        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('notifications/history', [NotificationController::class, 'history'])->name('notifications.history');
        Route::get('notifications/analytics', [NotificationController::class, 'analytics'])->name('notifications.analytics');
        Route::post('notifications/send', [NotificationController::class, 'send'])->name('notifications.send');
        Route::resource('notification-templates', NotificationTemplateController::class)->except('show');

        // ── Phase 3: Backups ────────────────────────────────

        Route::get('backups', [BackupController::class, 'index'])->name('backups.index');
        Route::post('backups', [BackupController::class, 'create'])->name('backups.create');
        Route::get('backups/{filename}/download', [BackupController::class, 'download'])->name('backups.download');
        Route::delete('backups/{filename}', [BackupController::class, 'destroy'])->name('backups.destroy');

        // ── Phase 3: Account Deletions ───────────────────────

        Route::get('account-deletions', [AccountDeletionController::class, 'index'])->name('account-deletions.index');
        Route::post('account-deletions/{id}/restore', [AccountDeletionController::class, 'restore'])->name('account-deletions.restore');
        Route::delete('account-deletions/{id}', [AccountDeletionController::class, 'forceDelete'])->name('account-deletions.force-delete');

        // ── DopiFuture: Applications ─────────────────────────

        Route::resource('applications', ApplicationController::class);

        // Application Connector Sync
        Route::post('applications/{application}/users', [ApplicationController::class, 'assignUser'])->name('applications.assign-user');
        Route::delete('applications/{application}/users/{user}', [ApplicationController::class, 'removeUser'])->name('applications.remove-user');
        Route::post('applications/{application}/users/{user}/sync', [ApplicationController::class, 'syncUser'])->name('applications.sync-user');
        Route::post('applications/{application}/sync-all', [ApplicationController::class, 'syncAll'])->name('applications.sync-all');

        // ── DopiFuture: Schools ──────────────────────────────

        Route::resource('schools', SchoolController::class);

        // ── DopiFuture: Classes ──────────────────────────────

        Route::resource('classes', ClassController::class);

        // ── DopiFuture: Licenses ─────────────────────────────

        Route::resource('licenses', LicenseController::class);

        // ── DopiFuture: Registration Requests ────────────────

        Route::get('registration-requests', [RegistrationRequestController::class, 'index'])->name('registration-requests.index');
        Route::get('registration-requests/{registrationRequest}', [RegistrationRequestController::class, 'show'])->name('registration-requests.show');
        Route::put('registration-requests/{registrationRequest}', [RegistrationRequestController::class, 'update'])->name('registration-requests.update');
        Route::delete('registration-requests/{registrationRequest}', [RegistrationRequestController::class, 'destroy'])->name('registration-requests.destroy');

        // ── DopiFuture: Permissions ──────────────────────────

        Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
        Route::put('permissions/{permission}', [PermissionController::class, 'update'])->name('permissions.update');
        Route::post('permissions/sync', [PermissionController::class, 'sync'])->name('permissions.sync');
    });
});

// ── Guest: Account Deletion Confirmation (signed URL) ────────────────────
Route::get('account/delete/confirm/{user}', function (\App\Models\User $user) {
    $service = new \App\Services\AccountDeletionService();
    $service->executeDelete($user);
    return view('guest.account-deleted');
})->name('account.delete.confirm')->middleware('signed');

// ── Public: School Registration Form ─────────────────────────────────────
Route::middleware([\App\Http\Middleware\SetLocale::class])->group(function () {
    Route::get('register', [\App\Http\Controllers\RegistrationController::class, 'create'])->name('register.create');
    Route::post('register', [\App\Http\Controllers\RegistrationController::class, 'store'])->name('register.store')->middleware('throttle:5,1');
});

// ── Portal: Auth Routes (separate from admin) ───────────────────────────
Route::middleware(['web', \App\Http\Middleware\SetLocale::class])->group(function () {

    // Guest-only
    Route::get('login', [\App\Http\Controllers\PortalAuthController::class, 'showLogin'])->name('portal.login');
    Route::post('login', [\App\Http\Controllers\PortalAuthController::class, 'login'])->name('portal.login.submit')->middleware('throttle:5,1');

    // Locale switching (available to both guests and auth users)
    Route::post('switch-locale', [\App\Http\Controllers\PortalController::class, 'switchLocale'])->name('portal.switch-locale');
});

// ── Public: Portal Pages ─────────────────────────────────────────────────
Route::middleware([\App\Http\Middleware\SetLocale::class])->group(function () {
    Route::get('/', [\App\Http\Controllers\PortalController::class, 'home'])->name('portal.home');
    Route::get('solutions', [\App\Http\Controllers\PortalController::class, 'solutions'])->name('portal.solutions');
    Route::get('contact', [\App\Http\Controllers\PortalController::class, 'contact'])->name('portal.contact');
    Route::post('contact', [\App\Http\Controllers\PortalController::class, 'contactStore'])->name('portal.contact.store')->middleware('throttle:5,1');
});

// ── Authenticated: Portal Dashboard ──────────────────────────────────────
Route::middleware(['auth', \App\Http\Middleware\SetLocale::class])->group(function () {
    Route::post('logout', [\App\Http\Controllers\PortalAuthController::class, 'logout'])->name('portal.logout');
    Route::get('dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('portal.dashboard');
    Route::get('profile', [\App\Http\Controllers\DashboardController::class, 'profile'])->name('portal.profile');
    Route::put('profile', [\App\Http\Controllers\DashboardController::class, 'profileUpdate'])->name('portal.profile.update');
    Route::get('reports', [\App\Http\Controllers\DashboardController::class, 'reports'])->name('portal.reports');

    // CRUD: Schools (with show/detail page)
    Route::resource('schools', \App\Http\Controllers\PortalSchoolController::class)
        ->names('portal.schools');

    // CRUD: Classes (with show/detail page)
    Route::resource('classes', \App\Http\Controllers\PortalClassController::class)
        ->names('portal.classes');

    // CRUD: Users (with show/detail page)
    Route::resource('users', \App\Http\Controllers\PortalUserController::class)
        ->names('portal.users');

    // CRUD: Licenses (with show/detail page)
    Route::resource('licenses', \App\Http\Controllers\PortalLicenseController::class)
        ->names('portal.licenses');

    // License purchases
    Route::post('licenses/{license}/purchases', [\App\Http\Controllers\PortalLicenseController::class, 'addPurchase'])
        ->name('portal.licenses.add-purchase');
});

