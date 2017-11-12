<?php namespace Gzero\Core\Models;

use Illuminate\Http\Response;

interface Routable {

    /**
     * @param Route    $route Route
     * @param Language $lang  Language
     *
     * @return Response
     */
    public function handle(Route $route, Language $lang): Response;

}
