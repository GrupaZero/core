<?php

addMultiLanguageRoutes([
    'domain'     => config('gzero.domain'),
    'namespace'  => 'Gzero\Core\Http\Controllers',
    'middleware' => ['web', 'auth']
], function ($router) {
    /** @var \Illuminate\Routing\Router $router */

    // ======== Account ========
    $router->get('account', 'AccountController@index')->name(mlSuffix('account', $language));
    $router->get('account/edit', 'AccountController@edit')->name(mlSuffix('account.edit', $language));
    $router->get('account/welcome', 'AccountController@welcome')->name(mlSuffix('account.welcome', $language));
    $router->get('account/oauth', 'AccountController@oauth')->name(mlSuffix('account.oauth', $language));
});

addRoutes([
    'domain'     => config('gzero.domain'),
    'namespace'  => 'Gzero\Core\Http\Controllers',
    'middleware' => ['web', 'auth']
], function ($router) {
    //  ======== Auth routes ========
    $router->get('logout', 'Auth\LoginController@logout')->name('logout');
});

addMultilanguageRoutes([
    'domain'     => config('gzero.domain'),
    'namespace'  => 'Gzero\Core\Http\Controllers',
    'middleware' => ['web']
], function ($router) {
    /** @var \Illuminate\Routing\Router $router */

    // Authentication Routes...
    $router->get('login', 'Auth\LoginController@showLoginForm')->name(mlSuffix('login', $language));
    $router->post('login', 'Auth\LoginController@login')->name(mlSuffix('post.login', $language));

    // Registration Routes...
    $router->get('register', 'Auth\RegisterController@showRegistrationForm')->name(mlSuffix('register', $language));
    $router->post('register', 'Auth\RegisterController@register')->name(mlSuffix('post.register', $language));

    // Password Reset Routes...
    $router->get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name(mlSuffix('password.request', $language));
    $router->post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name(mlSuffix('post.password.email', $language));
    $router->get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name(mlSuffix('password.reset', $language));
    $router->post('password/reset', 'Auth\ResetPasswordController@reset')->name(mlSuffix('post.password.reset', $language));
});

setCatchAllRoute([
    'domain'     => config('gzero.domain'),
    'middleware' => ['web']
], function ($router) {
    /** @var \Illuminate\Routing\Router $router */
    if (config('app.env') !== 'testing') {
        // We're manually registering dynamicRouter for test cases
        $router->get('{path?}', 'Gzero\Core\Http\Controllers\RouteController@dynamicRouter')->where('path', '.*');
    }
});
