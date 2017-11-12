<?php namespace Gzero\Core\Http\Middleware;

use Gzero\Core\Models\GuestUser;
use Closure;

class ViewShareUser {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request Request
     * @param  \Closure                 $next    Next closure
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (auth()->check()) {
            view()->share('user', auth()->user());
        } else {
            view()->share('user', new GuestUser());
        }

        return $next($request);
    }
}
