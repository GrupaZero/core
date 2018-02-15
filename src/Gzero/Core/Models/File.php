<?php namespace Gzero\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Robbo\Presenter\PresentableInterface;
use Gzero\InvalidArgumentException;
use Gzero\Core\ViewModels\FileViewModel;

class File extends Model implements PresentableInterface {

    /** @var array */
    protected $fillable = [
        'type',
        'name',
        'extension',
        'size',
        'mime_type',
        'info',
        'is_active'
    ];

    /**
     * @var array
     */
    protected $attributes = [
        'is_active' => false
    ];

    /**
     * File type relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function type()
    {
        return $this->belongsTo(FileType::class);
    }

    /**
     * Translation one to many relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations()
    {
        return $this->hasMany(FileTranslation::class);
    }

    /**
     * Get all of the entities that are assigned this file.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function uploadable()
    {
        return $this->morphedByMany(Uploadable::class, 'uploadable')->withPivot('weight')->withTimestamps();
    }

    /**
     * @return Uploadable|null
     */
    public function getUploadable(): ?Uploadable
    {
        return $this->uploadable;
    }

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
     * Return a created presenter.
     *
     * @return FileViewModel
     */
    public function getPresenter()
    {
        return new FileViewModel($this->toArray());
    }

    /**
     * Return file name with extension
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->name . '.' . $this->extension;
    }

    /**
     * Returns file upload path based on file type plural name e.g. 'images', 'documents'
     *
     * @return string
     */
    public function getUploadPath()
    {
        return str_plural($this->type->name) . '/';
    }

    /**
     * Returns file upload path
     *
     * @return string
     */
    public function getFullPath()
    {
        return $this->getUploadPath() . $this->getFileName();
    }

    /**
     * Returns unique file name
     *
     * @return string
     */
    public function buildUniqueName()
    {
        return uniqid($this->name . '_');
    }

    /**
     * @param string $type File type
     *
     * @throws InvalidArgumentException
     *
     * @return void
     */
    public function setTypeAttribute($type)
    {
        if (!$type instanceof FileType) {
            $type = FileType::getByName($type);
        }
        if (!$type) {
            throw new InvalidArgumentException('Unknown file type');
        }
        $this->type()->associate($type);
    }

    /**
     * Set the info value
     *
     * @param string $value info value
     *
     * @return string
     */
    public function setInfoAttribute($value)
    {
        return ($value) ? $this->attributes['info'] = json_encode($value) : $this->attributes['info'] = null;
    }

    /**
     * Get the info value
     *
     * @param string $value info value
     *
     * @return string
     */
    public function getInfoAttribute($value)
    {
        return ($value) ? json_decode($value, true) : $value;
    }

    /**
     * Function removes file translations in provided language code
     *
     * @param string $languageCode language code
     *
     * @return mixed
     */
    public function removeExistingTranslation($languageCode)
    {
        return $this->translations()
            ->where('file_id', $this->id)
            ->where('language_code', $languageCode)
            ->delete();
    }

    /**
     * Check if multiple files exists and return id's of existing ones
     *
     * @param array $ids array with files ids
     *
     * @return Collection with id's of exiting files
     */
    public static function getExistingIds($ids)
    {
        return self::whereIn('id', $ids)->pluck('id');
    }
}
