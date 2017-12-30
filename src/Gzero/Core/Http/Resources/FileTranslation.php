<?php namespace Gzero\Core\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

/**
 * @SWG\Definition(
 *   definition="FileTranslation",
 *   type="object",
 *   required={"title", "language_code"},
 *   @SWG\Property(
 *     property="author_id",
 *     type="number",
 *     example="10"
 *   ),
 *   @SWG\Property(
 *     property="language_code",
 *     type="string",
 *     example="en"
 *   ),
 *   @SWG\Property(
 *     property="title",
 *     type="string",
 *     example="example title"
 *   ),
 *   @SWG\Property(
 *     property="description",
 *     type="string",
 *     example="Example description"
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
class FileTranslation extends Resource {

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
            'author_id'     => $this->author_id,
            'language_code' => $this->language_code,
            'title'         => $this->title,
            'description'   => $this->description,
            'created_at'    => $this->created_at->toIso8601String(),
            'updated_at'    => $this->updated_at->toIso8601String(),
        ];
    }
}
