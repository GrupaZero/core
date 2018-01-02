<?php namespace Core\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use App\UploadableEntity;
use Gzero\Core\Models\File;
use Gzero\Core\Models\FileTranslation;
use Gzero\Core\Models\User;

class Unit extends \Codeception\Module {
    /**
     * Create user and return entity
     *
     * @param array $attributes
     *
     * @return \Gzero\Core\Models\User
     */
    public function haveUser($attributes = [])
    {
        return factory(User::class)->create($attributes);
    }

    /**
     * Create user and return entity
     *
     * @return UploadableEntity
     */
    public function haveUploadableEntity()
    {
        return new UploadableEntity();
    }

    /**
     * Create block with translations and return entity
     *
     * @param array $attributes
     *
     * @return \Gzero\Core\Models\File
     */
    public function haveFile($attributes = [])
    {
        $data            = array_except($attributes, ['translations']);
        $transByLangCode = collect(array_get($attributes, 'translations'))->groupBy('language_code');

        $file = factory(File::class)->make($data);
        $file->save();

        if (empty($transByLangCode)) {
            return $file;
        }

        $transByLangCode->each(function ($translations) use ($file) {
            // Create file translations
            foreach ($translations as $translation) {
                $file->translations()
                    ->save(
                        factory(FileTranslation::class)
                            ->make($translation)
                    );
            }
        });

        return $file;
    }

    /**
     * Create files with translations returns collection
     *
     * @param array $blocks
     *
     * @return array
     */
    public function haveFiles($blocks = [])
    {

        $result = [];

        foreach ($blocks as $attributes) {
            $result[] = $this->haveFile($attributes);
        }

        return $result;
    }
}
