<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register policies for vendor/non-standard models
        Gate::policy(\Spatie\Permission\Models\Role::class, \App\Policies\RolePolicy::class);

        // Phase 2 policies
        Gate::policy(\App\Models\Page::class, \App\Policies\PagePolicy::class);
        Gate::policy(\App\Models\Post::class, \App\Policies\PostPolicy::class);
        Gate::policy(\App\Models\Category::class, \App\Policies\CategoryPolicy::class);
        Gate::policy(\App\Models\Menu::class, \App\Policies\MenuPolicy::class);
        Gate::policy(\App\Models\Media::class, \App\Policies\MediaPolicy::class);
        Gate::policy(\App\Models\Faq::class, \App\Policies\FaqPolicy::class);
        Gate::policy(\App\Models\Form::class, \App\Policies\FormPolicy::class);

        // Phase 3 policies
        Gate::policy(\App\Models\NotificationTemplate::class, \App\Policies\NotificationTemplatePolicy::class);
    }
}
