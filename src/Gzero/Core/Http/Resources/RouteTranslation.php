<?php namespace Gzero\Core\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

/**
 * @SWG\Definition(
 *   definition="RouteTranslation",
 *   type="object",
 *   required={"language_code","path"},
 *   @SWG\Property(
 *     property="language_code",
 *     type="string",
 *     example="en"
 *   ),
 *   @SWG\Property(
 *     property="path",
 *     type="string",
 *     example="news/article-title/"
 *   ),
 *   @SWG\Property(
 *     property="is_active",
 *     type="boolean",
 *     example="true"
 *   )
 * )
 */
class RouteTranslation extends Resource {

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
            'id'            => (int) $this->id,
            'language_code' => $this->language_code,
            'path'          => $this->path,
            'is_active'     => $this->is_active
        ];
    }
}
