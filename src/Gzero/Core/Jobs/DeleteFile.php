<?php namespace Gzero\Core\Jobs;

use Gzero\Core\Models\File;
use Gzero\Core\DBTransactionTrait;
use Illuminate\Support\Facades\Storage;

class DeleteFile {

    use DBTransactionTrait;

    /** @var File */
    protected $file;

    /** @var string */
    protected $disc;

    /**
     * Create a new job instance.
     *
     * @param File $file File model
     */
    public function __construct(File $file)
    {
        $this->file = $file;
        $this->disc = config('gzero.upload.disk');
    }

    /**
     * Execute the job.
     *
     * @return bool
     */
    public function handle()
    {
        return $this->dbTransaction(function () {

            $path = $this->file->getFullPath();

            if (Storage::disk($this->disc)->exists($path)) {
                Storage::disk($this->disc)->delete($path);
            }

            $lastAction = $this->file->delete();

            event('file.deleted', [$this->file]);

            return $lastAction;
        });
    }

}
