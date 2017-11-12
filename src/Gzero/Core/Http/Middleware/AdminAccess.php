<?php namespace Gzero\Core\Http\Middleware;

use Closure;

class AdminAccess {

    /**
     * Return 404 if user is not authenticated or got no admin rights
     *
     * @param \Illuminate\Http\Request $request Request object
     * @param \Closure                 $next    Next middleware
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->user()->hasPermission('admin-access') || $request->user()->isSuperAdmin()) {
            return $next($request);
        }
        return abort(404);
    }
}
