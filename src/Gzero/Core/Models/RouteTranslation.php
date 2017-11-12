<?php namespace Gzero\Core\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouteTranslation extends Base {

    /**
     * @var array
     */
    protected $fillable = [
        'language_code',
        'path',
        'is_active'
    ];

    /**
     * @var array
     */
    protected $attributes = [
        'is_active' => false
    ];

    /**
     * Lang reverse relation
     *
     * @return BelongsTo
     */
    public function language()
    {
        return $this->belongsTo(Language::class);
    }
}
