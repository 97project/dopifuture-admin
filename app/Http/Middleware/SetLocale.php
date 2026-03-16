<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Language;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);
        app()->setLocale($locale);

        return $next($request);
    }

    protected function resolveLocale(Request $request): string
    {
        $validLocales = $this->getValidLocales();
        $defaultLocale = config('app.locale', 'tr');

        // API requests: ?lang= or ?locale= → Accept-Language → user pref → default
        if ($request->is('api/*')) {
            $queryLocale = $request->query('lang') ?? $request->query('locale');
            if ($queryLocale && in_array($queryLocale, $validLocales)) {
                return $queryLocale;
            }

            $acceptLocale = $this->parseAcceptLanguage($request->header('Accept-Language'));
            if ($acceptLocale && in_array($acceptLocale, $validLocales)) {
                return $acceptLocale;
            }

            if (auth()->check() && in_array(auth()->user()->locale, $validLocales)) {
                return auth()->user()->locale;
            }

            return $defaultLocale;
        }

        // Web requests: Portal is English-only, always use 'en'
        return 'en';
    }

    protected function parseAcceptLanguage(?string $header): ?string
    {
        if (!$header) {
            return null;
        }

        $parts = explode(',', $header);
        foreach ($parts as $part) {
            $lang = trim(explode(';', $part)[0]);
            $lang = substr($lang, 0, 2);
            return $lang;
        }

        return null;
    }

    protected function getValidLocales(): array
    {
        try {
            return Language::getActiveCodes();
        } catch (\Exception $e) {
            return ['tr', 'en'];
        }
    }
}
