<?php namespace Gzero\Core\Models;

use Gzero\Core\Traits\DatesFormatTrait;
use Illuminate\Database\Eloquent\Model;

abstract class Base extends Model {

    use DatesFormatTrait;

    /**
     * The storage format of the model's date columns.
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * Check if file exists
     *
     * @param int $entityId file id
     *
     * @return boolean
     */
    public static function checkIfExists($entityId): bool
    {
        return self::where('id', $entityId)->exists();
    }
}
