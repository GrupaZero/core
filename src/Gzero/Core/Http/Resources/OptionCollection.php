<?php namespace Gzero\Core\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class OptionCollection extends ResourceCollection {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection,
        ];
    }
}
