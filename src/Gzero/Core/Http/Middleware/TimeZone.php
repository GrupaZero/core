<?php namespace Gzero\Core\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Determines the time zone for current request
 *
 */
class TimeZone {

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
        $requestTimezone = null;
        if (str_is('api.*', $request->getHost())) {
            $requestTimezone = $request->header('Accept-Timezone');
        } else if($user = Auth::guard()->user()) {
            $requestTimezone = $user->timezone;
        }

        if(empty($requestTimezone)) {
            $requestTimezone = config('app.timezone', 'UTC');
        }

        config(['app.timezone' => $requestTimezone]);

        if (!str_is('api.*', $request->getHost())) {
            view()->share('requestTimezone', $requestTimezone);
        }

        return $next($request);
    }
}
