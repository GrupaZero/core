<?php namespace Gzero\Core\Models;

use Illuminate\Database\Eloquent\Model;

class OptionCategory extends Model {

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string
     */
    protected $primaryKey = 'key';

    /**
     * @var array
     */
    protected $fillable = [
        'key'
    ];

    /**
     * Returns option category by key name
     *
     * @param string $key Option category key
     *
     * @return mixed
     */
    public static function getByKey($key)
    {
        return static::where('key', $key)->first();
    }

    /**
     * Options one to many relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function options()
    {
        return $this->hasMany(Option::class, 'category_key');
    }

}
