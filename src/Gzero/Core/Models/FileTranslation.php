<?php namespace Gzero\Core\Models;

use Illuminate\Database\Eloquent\Model;

class FileTranslation extends Model {

    /** @var array */
    protected $fillable = [
        'language_code',
        'title',
        'description'
    ];

    /**
     * File author relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id', 'id');
    }

    /**
     * Language reverse relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function language()
    {
        return $this->belongsTo(Language::class);
    }
}
