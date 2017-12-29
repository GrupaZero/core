<?php namespace Gzero\Core\Jobs;

use Gzero\Core\Models\FileTranslation;
use Gzero\Core\DBTransactionTrait;

class DeleteFileTranslation {

    use DBTransactionTrait;

    /** @var FileTranslation */
    protected $translation;

    /**
     * Create a new job instance.
     *
     * @param FileTranslation $translation Block translation model
     */
    public function __construct(FileTranslation $translation)
    {
        $this->translation = $translation;
    }

    /**
     * Execute the job.
     *
     * @return bool
     */
    public function handle()
    {
        return $this->dbTransaction(function () {
            $lastAction = $this->translation->delete();

            event('block.translation.deleted', [$this->translation]);

            return $lastAction;
        });
    }
}
