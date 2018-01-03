<?php namespace App;

use Gzero\Core\Models\Language;
use Gzero\Core\Models\Routable;
use Illuminate\Http\Response;

class HelloWorld implements Routable {

    /**@var $canBeShown */
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

    /**
     * @param Language $language Language
     *
     * @return Response
     */
    public function handle(Language $language): Response
    {
        return response('Hello World');
    }

    /**
     * @return bool
     */
    public function canBeShown()
    {
        return $this->canBeShown;
    }

    /**
     * @return array
     */
    public function getTreePath(): array
    {
        return [];
    }
}