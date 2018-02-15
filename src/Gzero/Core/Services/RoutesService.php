<?php namespace Gzero\Core\Services;

use Illuminate\Support\Facades\Route;

class RoutesService {

    /** @var array */
    protected $routes = [
        'ml'       => [],
        'regular'  => [],
        'catchAll' => []
    ];

    /**
     * Add non multilingual route
     *
     * @param array ...$parameters closure or group options plus closure
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    public function add(...$parameters)
    {
        if (count($parameters) === 1) {
            $this->routes['regular'][] = ['closure' => $parameters[0], 'groupArgs' => []];
        } elseif (count($parameters) === 2) {
            $this->routes['regular'][] = ['closure' => $parameters[1], 'groupArgs' => $parameters[0]];
        } else {
            throw new \InvalidArgumentException;
        }
    }

    /**
     * Add multilingual route
     *
     * @param array ...$parameters closure or group options plus closure
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    public function addMultiLanguage(...$parameters)
    {
        if (count($parameters) === 1) {
            $this->routes['ml'][] = ['closure' => $parameters[0], 'groupArgs' => []];
        } elseif (count($parameters) === 2) {
            $this->routes['ml'][] = ['closure' => $parameters[1], 'groupArgs' => $parameters[0]];
        } else {
            throw new \InvalidArgumentException;
        }
    }

    /**
     * Add catch all route
     *
     * @param array ...$parameters closure or group options plus closure
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    public function setCatchAll(...$parameters)
    {
        if (count($parameters) === 1) {
            $this->routes['catchAll'] = ['closure' => $parameters[0], 'groupArgs' => []];
        } elseif (count($parameters) === 2) {
            $this->routes['catchAll'] = ['closure' => $parameters[1], 'groupArgs' => $parameters[0]];
        } else {
            throw new \InvalidArgumentException;
        }
    }

    /**
     * It registers all routes
     *
     * @return void
     */
    public function registerAll()
    {
        /** @var LanguageService $service */
        $service   = resolve(LanguageService::class);
        $languages = $service->getAllEnabled();
        foreach ($this->routes['ml'] as $value) {
            foreach ($languages as $language) {
                $prefix = null;
                if (!$language->is_default) {
                    $prefix = $language->code;
                }
                Route::group(
                    array_merge(['prefix' => $prefix], $value['groupArgs']),
                    function ($router) use ($value, $language) {
                        $value['closure']($router, $language->code);
                    }
                );
            }
        }
        foreach ($this->routes['regular'] as $value) {
            Route::group(
                $value['groupArgs'],
                function ($router) use ($value) {
                    $value['closure']($router);
                }
            );
        }
        if (!empty($this->routes['catchAll'])) {
            Route::group(
                $this->routes['catchAll']['groupArgs'],
                function ($router) {
                    $this->routes['catchAll']['closure']($router);
                }
            );
        }

        $router = resolve('router');
        $router->getRoutes()->refreshActionLookups();
        $router->getRoutes()->refreshNameLookups();
    }

}
