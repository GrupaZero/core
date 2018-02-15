<?php

addRoutes([
    'domain'     => config('gzero.domain'),
    'namespace'  => 'Gzero\Core\Http\Controllers',
    'middleware' => ['web', 'auth']
], function ($router) {
    /** @var \Illuminate\Routing\Router $router */

    // ======== Account ========
    $router->get('account', 'AccountController@index')->name('account');
    $router->get('account/edit', 'AccountController@edit')->name('account.edit');
    $router->get('account/welcome', 'AccountController@welcome')->name('account.welcome');
    $router->get('account/oauth', 'AccountController@oauth')->name('account.oauth');

    //  ======== Auth routes ========
    $router->get('logout', 'Auth\LoginController@logout')->name('logout');
});

addRoutes([
    'domain'     => config('gzero.domain'),
    'namespace'  => 'Gzero\Core\Http\Controllers',
    'middleware' => ['web']
], function ($router) {
    /** @var \Illuminate\Routing\Router $router */

    //  ======== Auth routes ========
    // Authentication Routes...
    $router->get('login', 'Auth\LoginController@showLoginForm')->name('login');
    $router->post('login', 'Auth\LoginController@login')->name('post.login');

    // Registration Routes...
    $router->get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
    $router->post('register', 'Auth\RegisterController@register')->name('post.register');

    // Password Reset Routes...
    $router->get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
    $router->post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('post.password.email');
    $router->get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
    $router->post('password/reset', 'Auth\ResetPasswordController@reset')->name('post.password.reset');
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
