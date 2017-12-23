<?php namespace Gzero\Core\Jobs;

use Gzero\Core\DBTransactionTrait;
use Gzero\Core\Models\File;
use Gzero\Core\Models\FileTranslation;
use Gzero\Core\Models\Language;
use Gzero\Core\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CreateFile {

    use DBTransactionTrait;

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
        'type'        => 'image',
        'description' => null,
        'is_active'   => false
    ];

    /**
     * Create a new job instance.
     *
     * @param UploadedFile $file       Uploaded file
     * @param string       $title      Translation title
     * @param Language     $language   Language
     * @param User         $author     User model
     * @param array        $attributes Array of optional attributes
     */
    protected function __construct(UploadedFile $file, string $title, Language $language, User $author, array $attributes = [])
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
     * It creates job to create file
     *
     * @param UploadedFile $file       Uploaded file
     * @param string       $title      Translation title
     * @param Language     $language   Language
     * @param User         $author     User model
     * @param array        $attributes Array of optional attributes
     *
     * @return CreateFile
     */
    public static function make(UploadedFile $file, string $title, Language $language, User $author, array $attributes = [])
    {
        return new self($file, $title, $language, $author, $attributes);
    }

    /**
     * It creates job to create file
     *
     * @param UploadedFile $file       Uploaded file
     * @param string       $title      Translation title
     * @param Language     $language   Language
     * @param User         $author     User model
     * @param array        $attributes Array of optional attributes
     *
     * @return CreateFile
     */
    public static function image(UploadedFile $file, string $title, Language $language, User $author, array $attributes = [])
    {
        return new self($file, $title, $language, $author, array_merge($attributes, ['type' => 'image']));
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
        $file = $this->dbTransaction(function () {
            $file = new File();
            $file->fill([
                'type'      => $this->attributes['type'],
                'is_active' => $this->attributes['is_active']

            ]);
            $file->author()->associate($this->author);
            $file->save();

            $translation = new FileTranslation();
            $translation->fill([
                'title'         => $this->title,
                'language_code' => $this->language->code,
                'description'   => $this->attributes['description']
            ]);
            $file->translations()->save($translation);

            event('file.created', [$file]);
            return $file;
        });
        return $file;
    }
}
