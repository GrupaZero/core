<?php namespace Gzero\Core\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

/**
 * @SWG\Definition(
 *   definition="Route",
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
 *   ),
 *   @SWG\Property(
 *     property="created_at",
 *     type="string",
 *     format="date-time"
 *   ),
 *   @SWG\Property(
 *     property="updated_at",
 *     type="string",
 *     format="date-time"
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
            'id'            => (int) $this->id,
            'language_code' => $this->language_code,
            'path'          => $this->path,
            'is_active'     => $this->is_active,
            'created_at'    => $this->created_at->toIso8601String(),
            'updated_at'    => $this->updated_at->toIso8601String()
        ];
    }
}
