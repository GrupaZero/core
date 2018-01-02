<?php namespace App;

use Gzero\Core\Models\File;
use Gzero\Core\Models\Uploadable;
use Illuminate\Support\Collection;

class UploadableEntity implements Uploadable {

    /** @var Collection */
    protected $files;

    /**
     * Mocked files relation which returns this instance to access sync method
     *
     * @param bool $active is active
     *
     * @return mixed this instance
     */
    public function files($active = true)
    {
        return $this;
    }

    public function getSyncedFiles()
    {
        return $this->files;
    }

    /**
     * Mock sync method and get files instances to match id's form assoc array and set them as files
     * e.g: [1 => ['weight' => 3], 5, 8, 10 => ['weight' => 2]]
     *
     * @param array $filesIds array with id's and arguments for pivot table
     */
    public function sync($filesIds)
    {
        $ids = collect($filesIds)->map(
            function ($key, $value) {
                if (is_array($key)) {
                    return $value;
                }

                return $key;
            }
        )->toArray();

        $this->files = File::whereIn('id', $ids)->get();
    }
}