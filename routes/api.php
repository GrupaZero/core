<?php

// Admin API
use Barryvdh\Cors\HandleCors;
use Gzero\Core\Http\Middleware\AdminAccess;

Route::group(
    [
        'domain'     => 'api.' . config('gzero.domain'),
        'prefix'     => 'v1',
        'namespace'  => 'Gzero\Core\Http\Controllers\Api',
        'middleware' => [HandleCors::class, 'auth:api', AdminAccess::class]
    ],
    function ($router) {
        /** @var \Illuminate\Routing\Router $router */

        // ======== Users ========
        $router->get('users', 'UserController@index');
        $router->get('users/{id}', 'UserController@show');
        $router->patch('users/{id}', 'UserController@update');
        $router->delete('users/{id}', 'UserController@destroy');

        // ======== Options ========
        $router->put('options/{category}', 'OptionController@update');

        // ======== Files ========
        $router->get('files', 'FileController@index');
        $router->post('files', 'FileController@store');
        $router->get('files/{id}', 'FileController@show');
        $router->patch('files/{id}', 'FileController@update');
        $router->delete('files/{id}', 'FileController@destroy');

        $router->get('files/{id}/translations', 'FileTranslationController@index');
        $router->post('files/{id}/translations', 'FileTranslationController@store');
        $router->delete('files/{id}/translations/{translationId}', 'FileTranslationController@destroy');
    }
);

// Public API
Route::group(
    [
        'domain'     => 'api.' . config('gzero.domain'),
        'prefix'     => 'v1',
        'namespace'  => 'Gzero\Core\Http\Controllers\Api',
        'middleware' => [HandleCors::class]
    ],
    function ($router) {
        /** @var \Illuminate\Routing\Router $router */
        $router->group(['middleware' => ['auth:api']], function ($router) {
            /** @var \Illuminate\Routing\Router $router */

            // ======== Users ========
            $router->patch('users/me', 'UserController@updateMe');
        });

        // ======== Languages ========
        $router->get('languages', 'LanguageController@index');
        $router->get('languages/{code}', 'LanguageController@show')->where('code', '[a-z]{2}');

        // ======== Options ========
        $router->get('options', 'OptionController@index');
        $router->get('options/{category}', 'OptionController@show');
    }
);
