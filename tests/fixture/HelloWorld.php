<?php namespace App;

use Gzero\Core\Models\Language;
use Gzero\Core\Models\Routable;
use Illuminate\Http\Response;

class HelloWorld implements Routable {

    protected $canBeShown;

    /**
     * HelloWorld constructor.
     *
     * @param bool $canBeShown can be shown method return value
     */
    public function __construct($canBeShown = true)
    {
        $this->canBeShown = $canBeShown;
    }

    public function handle(Language $language): Response
    {
        return response('Hello World');
    }

    public function canBeShown()
    {
        return $this->canBeShown;
    }
}