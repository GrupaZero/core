<?php namespace Gzero\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class FileType extends Model {

    /** @var array */
    protected $fillable = [
        'name'
    ];

    /**
     * Return list of all types.
     *
     * @return array
     */
    public function getTypes()
    {
        $types = Cache::rememberForever(
            'file_types',
            function () {
                return array_pluck($this->get(['name'])->toArray(), 'name');
            }
        );

        return $types;
    }

    /**
     * Get content type by name
     *
     * @param string $name Type name
     *
     * @return FileType
     */
    public static function getByName($name)
    {
        return self::where('name', $name)->first();
    }
}
