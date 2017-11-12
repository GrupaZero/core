<?php namespace Gzero\Core\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

/**
 * @SWG\Definition(
 *   definition="Language",
 *   type="object",
 *   required={"code", "i18n"},
 *   @SWG\Property(
 *     property="code",
 *     type="string",
 *     example="en"
 *   ),
 *   @SWG\Property(
 *     property="i18n",
 *     type="string",
 *     example="en_US"
 *   ),
 *   @SWG\Property(
 *     property="is_enabled",
 *     type="boolean",
 *     example="true"
 *   ),
 *   @SWG\Property(
 *     property="is_default",
 *     type="boolean",
 *     example="false"
 *   )
 * )
 */
class Language extends Resource {

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
            'code'       => $this->code,
            'i18n'       => $this->i18n,
            'is_enabled' => (bool) $this->is_enabled,
            'is_default' => (bool) $this->is_default,
        ];
    }
}
