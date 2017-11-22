<?php namespace App;

use Gzero\Core\Models\Language;
use Gzero\Core\Models\Routable;
use Illuminate\Http\Response;

class HelloWorld implements Routable {
    public function handle(Language $language): Response
    {
        return response('Hello World');
    }
}