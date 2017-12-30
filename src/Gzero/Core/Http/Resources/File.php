<?php namespace Gzero\Core\Http\Resources;

use Gzero\Core\Models\FileTranslation;
use Illuminate\Http\Resources\Json\Resource;

/**
 * @SWG\Definition(
 *   definition="File",
 *   type="object",
 *   required={},
 *   @SWG\Property(
 *     property="type",
 *     type="string",
 *     example="basic"
 *   ),
 *   @SWG\Property(
 *     property="file",
 *     type="file"
 *   ),
 *   @SWG\Property(
 *     property="name",
 *     type="string",
 *     example="file"
 *   ),
 *   @SWG\Property(
 *     property="extension",
 *     type="string",
 *     example="jpg"
 *   ),
 *   @SWG\Property(
 *     property="size",
 *     type="number",
 *     description="File size in bytes",
 *     example="10240"
 *   ),
 *   @SWG\Property(
 *     property="mime_type",
 *     type="string",
 *     example="image/jpeg"
 *   ),
 *   @SWG\Property(
 *     property="info",
 *     description="Contains customizable info",
 *     type="json",
 *     example="{'key':'value'}"
 *   ),
 *   @SWG\Property(
 *     property="author_id",
 *     type="number",
 *     example="10"
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
 *   ),
 *   @SWG\Property(
 *     property="translations",
 *     type="array",
 *     @SWG\Items(ref="#/definitions/FileTranslation")
 *   )
 * )
 */
class File extends Resource {

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
            'author_id'    => $this->author_id,
            'type'         => $this->whenLoaded('type', function () {
                return $this->type->name;
            }),
            'extension'    => $this->extension,
            'size'         => $this->size,
            'mime_type'    => $this->mime_type,
            'info'         => $this->info,
            'is_active'    => $this->is_active,
            'created_at'   => $this->created_at->toIso8601String(),
            'updated_at'   => $this->updated_at->toIso8601String(),
            'translations' => FileTranslation::collection($this->whenLoaded('translations')),
        ];
    }
}
