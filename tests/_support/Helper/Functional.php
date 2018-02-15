<?php namespace Core\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Gzero\Core\Models\File;
use Gzero\Core\Models\FileTranslation;
use Gzero\Core\Models\User;
use Illuminate\Routing\Router;

class Functional extends \Codeception\Module {

    /**
     * Create user and return entity
     *
     * @param array $attributes
     *
     * @return User
     */
    public function haveUser($attributes = [])
    {
        return factory(User::class)->create($attributes);
    }

    /**
     * Create users and return collection
     *
     * @param array|integer $users
     *
     * @return array
     */
    public function haveUsers($users)
    {
        if (is_numeric($users)) {
            return factory(User::class, $users)->create();
        }

        $result = [];

        foreach ($users as $attributes) {
            $result[] = $this->haveUser($attributes);
        }

        return $result;
    }

    /**
     * @param callable $closure
     */
    public function haveMlRoutes(callable $closure)
    {
        $this->getModule('Laravel5')
            ->haveApplicationHandler(function ($app) use ($closure) {
                addMultiLanguageRoutes(function ($router, $language) use ($closure) {
                    /** @var Router $router */
                    $closure($router, $language);

                    $router->getRoutes()->refreshActionLookups();
                    $router->getRoutes()->refreshNameLookups();
                });
            });
    }

    /**
     * @param callable $closure
     */
    public function haveRoutes(callable $closure)
    {
        $this->getModule('Laravel5')
            ->haveApplicationHandler(function ($app) use ($closure) {
                addRoutes(function ($router) use ($closure) {
                    /** @var Router $router */
                    $closure($router);

                    $router->getRoutes()->refreshActionLookups();
                    $router->getRoutes()->refreshNameLookups();
                });
            });
    }

    /**
     * It clears all application handlers
     */
    public function clearRoutes()
    {
        $this->getModule('Laravel5')->clearApplicationHandlers();
    }

    /**
     * Create file with translations and return entity
     *
     * @param array $attributes
     *
     * @return \Gzero\Core\Models\File
     */
    public function haveFile($attributes = [])
    {
        $data            = array_except($attributes, ['translations']);
        $transByLangCode = collect(array_get($attributes, 'translations'))->groupBy('language_code');

        $block = factory(File::class)->make($data);
        $block->save();

        if (empty($transByLangCode)) {
            return $block;
        }

        $transByLangCode->each(function ($translations) use ($block) {
            // Create block translations
            foreach ($translations as $translation) {
                $block->translations()
                    ->save(
                        factory(FileTranslation::class)
                            ->make($translation)
                    );
            }
        });

        return $block;
    }

}
