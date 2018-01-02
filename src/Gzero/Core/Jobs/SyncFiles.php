<?php namespace Gzero\Core\Jobs;

use Gzero\Core\DBTransactionTrait;
use Gzero\Core\Models\File;
use Gzero\Core\Models\Uploadable;

class SyncFiles {

    use DBTransactionTrait;

    /** @var Uploadable */
    protected $entity;

    /** @var array */
    protected $files;

    /** @var array */
    protected $filesIds;

    /**
     * Create a new job instance.
     *
     * @param Uploadable $entity Uploadable entity
     * @param array      $files  file id's assoc array with mixed id's and arguments for sync call
     */
    public function __construct(Uploadable $entity, array $files = [])
    {
        $this->entity   = $entity;
        $this->files    = $files;
        $this->filesIds = $this->getFieldIdsFromSyncData($files)->toArray();
    }

    /**
     * Execute the job.
     *
     * @return Uploadable
     */
    public function handle()
    {
        $entity = $this->dbTransaction(function () {
            $filesIds = $this->filterExistingFiles($this->files, File::getExistingIds($this->filesIds)->toArray());

            $this->entity->files()->sync($filesIds);

            event('file.synced', [$this->entity, $this->files]);
            return $this->entity;
        });
        return $entity;
    }

    /**
     * Extracts file id's from assoc array with mixed id's and arguments for sync call
     * e.g: [1 => ['weight' => 3], 5, 8, 10 => ['weight' => 2]]
     *
     * @param array $filesIds array with id's for arguments to pivot table
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getFieldIdsFromSyncData(array $filesIds)
    {
        return collect($filesIds)->map(
            function ($key, $value) {
                if (is_array($key)) {
                    return $value;
                }

                return $key;
            }
        );
    }

    /**
     * Filters files array and returns values ony for existing files
     *
     * @param array $files         array with id's and arguments for pivot table
     * @param array $existingFiles array with id's of existing files
     *
     * @return array Array with records for existing files
     */
    protected function filterExistingFiles(array $files, array $existingFiles)
    {
        return array_where($files, function ($value, $key) use ($existingFiles) {
            if (is_array($value)) {
                return in_array($key, $existingFiles);
            }

            return in_array($value, $existingFiles);
        });
    }
}
