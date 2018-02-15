<?php namespace Gzero\Core\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Gzero\Core\Services\LanguageService;
use Gzero\Core\Services\RoutesService;
use Gzero\DomainException;

class MultiLanguage {

    /** @var array */
    protected static $callbacks = [];

    /**
     * It adds callback to callbacks array
     *
     * @param Closure $callback Callback
     *
     * @return void
     */
    public static function addCallback(Closure $callback)
    {
        static::$callbacks[] = $callback;
    }

    /**
     * It clears callbacks array
     *
     * @return void
     */
    public static function clearCallbacks()
    {
        static::$callbacks = [];
    }

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
        $languageService = resolve(LanguageService::class);
        $languages       = $languageService->getAllEnabled();
        $language        = null;

        if (str_is('api.*', $request->getHost())) {
            $language = $languages->first(function ($language) use ($request) {
                return $language->code === $request->header('Accept-Language');
            });
        } else {
            $language = $languages->first(function ($language) use ($request) {
                return $language->code === $request->segment(1);
            });
        }

        if (empty($language)) {
            $language = $languageService->getDefault();
        }

        if (empty($language)) {
            throw new DomainException('No default language found');
        }

        app()->setLocale($language->code);

        // We need it to make carbon's diffForHumans work correctly
        Carbon::setLocale(app()->getLocale());

        if (!str_is('api.*', $request->getHost())) {
            view()->share('language', $language);
            view()->share('languages', $languages);
        }

        resolve(RoutesService::class)->registerAll();

        // We use this callbacks to register some view shares after we change app locale
        foreach (static::$callbacks as $callback) {
            $callback();
            static::clearCallbacks();
        }

        return $next($request);
    }

}
