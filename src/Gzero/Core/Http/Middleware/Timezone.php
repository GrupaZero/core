<?php namespace Gzero\Core\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Determines the time zone for current request
 *
 */
class Timezone {

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
        config(['request.timezone' => $this->determineRequestTimezone($request)]);

        return $next($request);
    }

    /**
     * Determine timezone for this api request.
     * It takes the value from the Accept-Timezone header.
     * If that is not present it returns the default value: UTC.
     *
     * @param Request $request HTTP request
     *
     * @return string
     */
    protected function determineRequestTimezone(Request $request)
    {
        $requestTimezone = 'UTC';

        if (str_is('api.*', $request->getHost()) && $request->hasHeader('Accept-Timezone')) {
            $requestTimezone = $request->header('Accept-Timezone');
        }

        return $requestTimezone;
    }
}
