<?php namespace Gzero\Core\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Gzero\Core\Services\LanguageService;
use Gzero\Core\Services\RoutesService;
use Gzero\DomainException;

class MultiLanguage {

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request Request object
     * @param \Closure                 $next    Next middleware
     *
     * @throws DomainException
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /** @var LanguageService $languageService */
        $languageService    = resolve(LanguageService::class);
        $availableLanguages = $languageService->getAllEnabled();
        $requestLanguage    = null;

        if (str_is('api.*', $request->getHost())) {
            $requestLanguage = $availableLanguages->first(function ($language) use ($request) {
                return $language->code === $request->header('Accept-Language');
            });
        } else {
            $requestLanguage = $availableLanguages->first(function ($language) use ($request) {
                return $language->code === $request->segment(1);
            });
        }

        if (empty($requestLanguage)) {
            $requestLanguage = $languageService->getDefault();
        }

        if (empty($requestLanguage)) {
            throw new DomainException('No default language found');
        }

        app()->setLocale($requestLanguage->code);

        // We need it to make carbon's diffForHumans work correctly
        Carbon::setLocale(app()->getLocale());

        if (!str_is('api.*', $request->getHost())) {
            view()->share('requestLanguage', $requestLanguage);
            view()->share('availableLanguages', $availableLanguages);
        }

        resolve(RoutesService::class)->registerAll();

        return $next($request);
    }

}
