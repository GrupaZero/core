<?php namespace Gzero\Core\Http\Resources;

use Illuminate\Support\Collection;
use Illuminate\Http\Resources\Json\Resource;

class RouteTranslationCollection extends Resource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request request
     *
     * @return Collection
     */
    public function toArray($request)
    {
        return $this->collection;
    }
}
