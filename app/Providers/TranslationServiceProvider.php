<?php

namespace App\Providers;

use Illuminate\Translation\TranslationServiceProvider as BaseTranslationServiceProvider;
use App\Services\Translation\DbTranslationLoader;

class TranslationServiceProvider extends BaseTranslationServiceProvider
{
    protected function registerLoader(): void
    {
        $this->app->singleton('translation.loader', function ($app) {
            return new DbTranslationLoader(
                $app['files'],
                $app['path.lang']
            );
        });
    }
}
