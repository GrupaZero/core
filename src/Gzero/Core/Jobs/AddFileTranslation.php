<?php namespace Gzero\Core\Jobs;

use Gzero\Core\Models\File;
use Gzero\Core\Models\FileTranslation;
use Gzero\Core\DBTransactionTrait;
use Gzero\Core\Models\Language;
use Gzero\Core\Models\User;

class AddFileTranslation {

    use DBTransactionTrait;

    /** @var File */
    protected $file;

    /** @var string */
    protected $language;

    /** @var string */
    protected $title;

    /** @var User */
    protected $author;

    /** @var array */
    protected $attributes;

    /** @var array */
    protected $allowedAttributes = [
        'title'       => null,
        'description' => null
    ];

    /**
     * Create a new job instance.
     *
     * @param File     $file       File model
     * @param string   $title      Title
     * @param Language $language   Language code
     * @param User     $author     User model
     * @param array    $attributes Array of optional attributes
     *
     * @internal param array $attributes Array of attributes
     */
    public function __construct(File $file, string $title, Language $language, User $author, array $attributes = [])
    {
        $this->file       = $file;
        $this->language   = $language;
        $this->title      = $title;
        $this->author     = $author;
        $this->attributes = array_merge(
            $this->allowedAttributes,
            array_only($attributes, array_keys($this->allowedAttributes))
        );
    }

    /**
     * Execute the job.
     *
     * @return bool
     */
    public function handle()
    {
        $translation = $this->dbTransaction(function () {
            $translation = new FileTranslation();
            $translation->fill([
                'title'         => $this->title,
                'language_code' => $this->language->code,
                'description'   => $this->attributes['description']
            ]);
            $translation->author()->associate($this->author);

            $this->file->removeExistingTranslation($translation->language_code);
            $this->file->translations()->save($translation);

            event('file.translation.created', [$translation]);
            return $translation;
        });
        return $translation;
    }
}
