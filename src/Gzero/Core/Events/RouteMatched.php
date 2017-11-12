<?php namespace Gzero\Core\Events;

use Gzero\Core\Models\Route;
use Illuminate\Http\Request;

class RouteMatched {
    /**
     * The route instance.
     *
     * @var Route
     */
    public $route;

    /**
     * The request instance.
     *
     * @var Request
     */
    public $request;

    /**
     * Create a new event instance.
     *
     * @param  Route   $route   Matched content
     * @param  Request $request Request
     *
     */
    public function __construct(Route $route, $request)
    {
        $this->route   = $route;
        $this->request = $request;
    }
}
