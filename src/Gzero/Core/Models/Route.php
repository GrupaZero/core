<?php namespace Gzero\Core\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Route extends Base {

    protected $with = ['translations'];

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
     * Translation one to many relation
     *
     * @param bool $onlyActive Only active translations
     *
     * @return HasMany
     */
    public function translations($onlyActive = true)
    {
        if ($onlyActive) {
            return $this->hasMany(RouteTranslation::class)->where('is_active', true);
        }
        return $this->hasMany(RouteTranslation::class);
    }

    /**
     * Check if route have active translation in specific language
     *
     * @param string $languageCode Language code
     *
     * @return mixed
     */
    public function hasActiveTranslation($languageCode)
    {
        return $this->translations->first(function ($translation) use ($languageCode) {
            return $translation->is_active === true && $translation->language_code === $languageCode;
        });
    }
}
