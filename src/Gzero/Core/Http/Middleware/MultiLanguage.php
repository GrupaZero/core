<?php namespace Gzero\Core\Http\Middleware;

use Closure;
use Gzero\Core\Exception;
use Gzero\Core\Services\LanguageService;
use Gzero\Core\ServiceProvider;

class MultiLanguage {

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request Request object
     * @param \Closure                 $next    Next middleware
     *
     * @throws Exception
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /** @var LanguageService $languageService */
        $languageService = resolve(LanguageService::class);
        $languages       = $languageService->getAllEnabled()->pluck('code');
        $language        = $languages->first(function ($code) use ($request) {
            return $code === $request->segment(1);
        });

        if (!empty($language)) {
            app()->setLocale($language);
        } else {
            ServiceProvider::setDefaultLocale();
        }

        return $next($request);
    }

}
