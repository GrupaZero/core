<?php namespace Gzero\Cms\Jobs;

use Gzero\Core\Models\File;
use Gzero\Core\Models\FileTranslation;
use Gzero\Core\Models\Language;
use Gzero\Core\Models\User;
use Illuminate\Support\Facades\DB;

class CreateFile {

    /** @var string */
    protected $title;

    /** @var string */
    protected $language;

    /** @var User */
    protected $author;

    /** @var array */
    protected $attributes;

    /** @var array */
    protected $allowedAttributes = [
        'type'          => 'image',
        'region'        => null,
        'theme'         => null,
        'weight'        => 0,
        'filter'        => null,
        'options'       => false,
        'is_active'     => false,
        'is_cacheable'  => false,
        'body'          => null,
        'custom_fields' => null
    ];

    /**
     * Create a new job instance.
     *
     * @param string   $title      Translation title
     * @param Language $language   Language
     * @param User     $author     User model
     * @param array    $attributes Array of optional attributes
     */
    protected function __construct(string $title, Language $language, User $author, array $attributes = [])
    {
        $this->title      = $title;
        $this->language   = $language;
        $this->author     = $author;
        $this->attributes = array_merge(
            $this->allowedAttributes,
            array_only($attributes, array_keys($this->allowedAttributes))
        );
    }

    /**
     * It creates job to create block
     *
     * @param string   $title      Translation title
     * @param Language $language   Language
     * @param User     $author     User model
     * @param array    $attributes Array of optional attributes
     *
     * @return CreateFile
     */
    public static function make(string $title, Language $language, User $author, array $attributes = [])
    {
        return new self($title, $language, $author, $attributes);
    }

    /**
     * Execute the job.
     *
     * @throws \InvalidArgumentException
     * @throws \Exception|\Throwable
     *
     * @return File
     */
    public function handle()
    {
        $file = DB::transaction(
            function () {
                $file = new File();
                $file->fill([
                    'type'         => $this->attributes['type'],
                    'region'       => $this->attributes['region'],
                    'theme'        => $this->attributes['theme'],
                    'weight'       => $this->attributes['weight'],
                    'filter'       => $this->attributes['filter'],
                    'options'      => $this->attributes['options'],
                    'is_active'    => $this->attributes['is_active'],
                    'is_cacheable' => $this->attributes['is_cacheable']

                ]);
                $file->author()->associate($this->author);
                $file->save();

                $translation = new FileTranslation();
                $translation->fill([
                    'title'         => $this->title,
                    'language_code' => $this->language->code,
                    'body'          => $this->attributes['body'],
                    'custom_fields' => $this->attributes['custom_fields'],
                    'is_active'     => true
                ]);
                $file->translations()->save($translation);

                event('block.created', [$file]);
                return $file;
            }
        );
        return $file;
    }
}
