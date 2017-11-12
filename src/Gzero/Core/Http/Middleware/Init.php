<?php namespace Gzero\Core\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;

class Init {

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
        if (str_contains($request->getRequestUri(), 'index.php')) {
            return new RedirectResponse(url(preg_replace('#index.php(/)?#', '', $request->fullUrl())), 301);
        }
        $this->toSnakeCase($request);
        return $next($request);
    }

    /**
     * It changes all parameters to snake_case
     *
     * @param \Illuminate\Http\Request $request Request object
     *
     * @return void
     */
    private function toSnakeCase($request)
    {
        $request->replace(array_snake_case_keys($request->all()));
    }

}
