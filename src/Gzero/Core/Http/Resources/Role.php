<?php namespace Gzero\Core\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

/**
 * @SWG\Definition(
 *   definition="Role",
 *   type="object",
 *   required={"name"},
 *   @SWG\Property(
 *     property="name",
 *     type="string",
 *     example="Moderator"
 *   )
 * )
 */
class Role extends Resource {

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
            'id'   => (int) $this->id,
            'name' => $this->name
        ];
    }
}
