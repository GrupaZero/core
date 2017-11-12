<?php namespace Gzero\Core\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

/**
 * @SWG\Definition(
 *   definition="OptionCategory",
 *   type="object",
 *   required={"key"},
 *   @SWG\Property(
 *     property="key",
 *     type="string",
 *     example="general"
 *   )
 * )
 */
class OptionCategory extends Resource {

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
            'key' => $this->resource
        ];
    }
}
