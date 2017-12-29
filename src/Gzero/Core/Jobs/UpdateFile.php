<?php namespace Gzero\Core\Jobs;

use Gzero\Core\DBTransactionTrait;
use Gzero\Core\Models\File;

class UpdateFile {

    use DBTransactionTrait;

    /** @var File */
    protected $file;

    /** @var array */
    protected $attributes;

    /** @var array */
    protected $allowedAttributes = [
        'info',
        'is_active'
    ];

    /**
     * Create a new job instance.
     *
     * @param File  $file       File model
     * @param array $attributes Array of attributes
     */
    public function __construct(File $file, array $attributes = [])
    {
        $this->file       = $file;
        $this->attributes = array_only($attributes, $this->allowedAttributes);
    }

    /**
     * Execute the job.
     *
     * @return File
     */
    public function handle()
    {
        $file = $this->dbTransaction(function () {
            $this->file->fill($this->attributes);
            $this->file->save();

            event('file.updated', [$this->file]);
            return $this->file;
        });
        return $file;
    }

}
