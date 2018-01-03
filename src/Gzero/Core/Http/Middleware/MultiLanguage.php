<?php namespace Gzero\Core\Http\Middleware;

use Closure;
use Gzero\Core\Services\LanguageService;
use Gzero\Core\ServiceProvider;

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

        // We use this callbacks to register some view shares after we change app locale
        foreach (static::$callbacks as $callback) {
            $callback();
            static::clearCallbacks();
        }

        return $next($request);
    }

}
