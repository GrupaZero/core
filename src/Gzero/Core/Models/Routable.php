<?php namespace Gzero\Core\Models;

use Illuminate\Http\Response;

interface Routable {

    /**
     * @param Language $language Language
     *
     * @return Response
     */
    public function handle(Language $language): Response;

    /**
     * @return bool
     */
    public function canBeShown();

    /**
     * @return array
     */
    public function getTreePath(): array;

}
