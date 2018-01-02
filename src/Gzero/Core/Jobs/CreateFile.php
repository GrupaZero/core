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

    /** @var UploadedFile */
    protected $file;

    /** @var string */
    protected $title;

    /** @var string */
    protected $language;

    /** @var User */
    protected $author;

    /** @var array */
    protected $attributes;

    /** @var string */
    protected $disc;

    /** @var array */
    protected $allowedAttributes = [
        'type'        => 'image',
        'info'        => null,
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
        $this->file       = $file;
        $this->title      = $title;
        $this->language   = $language;
        $this->author     = $author;
        $this->disc       = config('gzero.upload.disk');
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
    public static function document(UploadedFile $file, string $title, Language $language, User $author, array $attributes = [])
    {
        return new self($file, $title, $language, $author, array_merge($attributes, ['type' => 'document']));
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
    public static function video(UploadedFile $file, string $title, Language $language, User $author, array $attributes = [])
    {
        return new self($file, $title, $language, $author, array_merge($attributes, ['type' => 'video']));
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
    public static function music(UploadedFile $file, string $title, Language $language, User $author, array $attributes = [])
    {
        return new self($file, $title, $language, $author, array_merge($attributes, ['type' => 'music']));
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
                'name'      => str_slug(pathinfo($this->file->getClientOriginalName(), PATHINFO_FILENAME)),
                'extension' => mb_strtolower($this->file->getClientOriginalExtension()),
                'size'      => $this->file->getSize(),
                'mime_type' => $this->file->getMimeType(),
                'type'      => $this->attributes['type'],
                'info'      => $this->attributes['info'],
                'is_active' => $this->attributes['is_active']

            ]);

            if (Storage::disk($this->disc)->exists($file->getFullPath())) {
                $file->name = $file->buildUniqueName();
            }

            $file->author()->associate($this->author);
            $file->save();

            $translation = new FileTranslation();
            $translation->fill([
                'title'         => $this->title,
                'language_code' => $this->language->code,
                'description'   => $this->attributes['description']
            ]);
            $file->translations()->save($translation);

            Storage::disk($this->disc)->putFileAs($file->getUploadPath(), $this->file, $file->getFileName());

            event('file.created', [$file]);
            return $file;
        });
        return $file;
    }
}
