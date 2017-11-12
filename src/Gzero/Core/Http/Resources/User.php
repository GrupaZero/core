<?php namespace Gzero\Core\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

/**
 * @SWG\Definition(
 *   definition="User",
 *   type="object",
 *   required={"email", "name"},
 *   @SWG\Property(
 *     property="email",
 *     type="string",
 *     example="john.doe@example.com"
 *   ),
 *   @SWG\Property(
 *     property="name",
 *     type="string",
 *     example="JohnDoe"
 *   ),
 *   @SWG\Property(
 *     property="first_name",
 *     type="string",
 *     example="John"
 *   ),
 *   @SWG\Property(
 *     property="last_name",
 *     type="string",
 *     example="Doe"
 *   ),
 *   @SWG\Property(
 *     property="roles",
 *     type="array",
 *     @SWG\Items(ref="#/definitions/Role")
 *   )
 * )
 */
class User extends Resource {

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
            'id'         => (int) $this->id,
            'email'      => $this->email,
            'name'       => $this->name,
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'roles'      => Role::collection($this->whenLoaded('roles')),
        ];
    }
}
