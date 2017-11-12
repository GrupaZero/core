<?php namespace Gzero\Core\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

/**
 * @SWG\Definition(
 *   definition="Option",
 *   type="object",
 *   required={"key", "value", "category_key"},
 *   @SWG\Property(
 *     property="key",
 *     type="string",
 *     example="site_name"
 *   ),
 *   @SWG\Property(
 *     property="value",
 *     type="string",
 *     example="{'en':'GZERO-CMS','de':'GZERO-CMS','fr':'GZERO-CMS','pl':'GZERO-CMS'}"
 *   ),
 *   @SWG\Property(
 *     property="category_key",
 *     type="string",
 *     example="general"
 *   )
 * )
 */
class Option extends Resource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request request
     *
     * @return array
     */
    public function toArray($request)
    {
        return $this->resource;
    }
}
