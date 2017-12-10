<?php namespace Gzero\Core\Http\Middleware;

use Closure;
use Gzero\Core\Services\LanguageService;
use Gzero\Core\ServiceProvider;

class MultiLanguage {

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request Request object
     * @param \Closure                 $next    Next middleware
     *
     * @throws \Gzero\DomainException
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /** @var LanguageService $languageService */
        $languageService = resolve(LanguageService::class);
        $languages       = $languageService->getAllEnabled();
        $language        = $languages->first(function ($language) use ($request) {
            return $language->code === $request->segment(1);
        });

        if (!empty($language)) {
            app()->setLocale($language->code);
        } else {
            $language = ServiceProvider::setDefaultLocale();
        }

        view()->share('language', $language);
        view()->share('languages', $languages);

        return $next($request);
    }

}
