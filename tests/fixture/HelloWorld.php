<?php namespace App;

use Gzero\Core\Models\Language;
use Gzero\Core\Models\Routable;
use Gzero\Core\Models\Route;
use Illuminate\Http\Response;

class HelloWorld implements Routable {
    public function handle(Route $route, Language $lang): Response
    {
        return response('Hello World');
    }
}