<?php namespace Gzero\Core\Http\Controllers;

use Gzero\Core\DynamicRouter;
use Gzero\Core\Services\LanguageService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class RouteController extends Controller {

    /**
     * @param DynamicRouter   $router  DynamicRouter service
     * @param LanguageService $service LanguageService
     * @param Request         $request Request object
     *
     * @return \Illuminate\Http\Response
     */
    public function dynamicRouter(DynamicRouter $router, LanguageService $service, Request $request)
    {
        return $router->handleRequest($request, $service->getCurrent());
    }
}
