<?php namespace Gzero\Core\Models;

class Option extends Base {

    /**
     * @var array
     */
    protected $fillable = [
        'key',
        'value',
        'category_key'
    ];

    /**
     * Returns option by key name
     *
     * @param string $key Option key
     *
     * @return mixed
     */
    public static function getByKey($key)
    {
        return static::where('key', $key)->first();
    }

    /**
     * Option category relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(OptionCategory::class, 'category_key', 'key');
    }

    /**
     * Set the option value
     *
     * @param string $value option value
     *
     * @return string
     */
    public function setValueAttribute($value)
    {
        // Use json to save lang specific option values
        $this->attributes['value'] = json_encode($value);
    }

    /**
     * Get the option value
     *
     * @param string $value option value
     *
     * @return string
     */
    public function getValueAttribute($value)
    {
        // Decode retrieved value
        return json_decode($value, true);
    }

}
