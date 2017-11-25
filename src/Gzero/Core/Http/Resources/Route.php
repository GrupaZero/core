<?php namespace Gzero\Core\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

/**
 * @SWG\Definition(
 *   definition="Route",
 *   type="object",
 *   required={"name"},
 *   @SWG\Property(
 *     property="translations",
 *     type="array",
 *     @SWG\Items(ref="#/definitions/RouteTranslation")
 *   )
 * )
 */
class Route extends Resource {

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
            'id'           => (int) $this->id,
            'translations' => RouteTranslation::collection($this->whenLoaded('translations'))
        ];
    }
}
