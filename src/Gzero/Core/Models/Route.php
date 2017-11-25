<?php namespace Gzero\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Route extends Model {

    /** @var array */
    protected $fillable = [
        'language_code',
        'path',
        'is_active'
    ];

    /** @var array */
    protected $attributes = [
        'is_active' => false
    ];

    /**
     * Polymorphic relation to entities that could have route
     *
     * @return MorphTo
     */
    public function routable()
    {
        return $this->morphTo();
    }

    /**
     * @return Routable|null
     */
    public function getRoutable():?Routable
    {
        return $this->routable;
    }

    /**
     * Language reverse relation
     *
     * @return BelongsTo
     */
    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * Check if route have active translation in specific language
     *
     * @return mixed
     */
    public function canBeShown()
    {
        return $this->is_active;
    }

    /**
     * Function returns an unique path address from given path in specific language
     *
     * @param string $path         string path address to search for
     * @param string $languageCode translation language code
     *
     * @return string an unique path address
     */
    public static function buildUniquePath($path, $languageCode)
    {
        $count = self::query()
            ->where('language_code', $languageCode)
            ->whereRaw("path ~ '^$path($|-[0-9]+$)'")
            ->count();

        return ($count) ? $path . '-' . $count : $path;
    }
}
