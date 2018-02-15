<?php namespace Gzero\Core\Http\Middleware;

use Closure;

class ViewComposer {

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
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // We use this callbacks to register some view shares after we change app locale
        foreach (static::$callbacks as $callback) {
            $callback();
            static::clearCallbacks();
        }

        return $next($request);
    }

}
