<?php

use Faker\Generator as Faker;
use Gzero\Core\Models\Language;
use Gzero\Core\Models\Route;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(Route::class, function (Faker $faker) {
    return [
        'language_code' => function () {
            return factory(Language::class)->make()->code;
        },
        'path'          => $faker->slug,
        'routable_id'   => null,
        'routable_type' => null,
        'is_active'     => true
    ];
});

$factory->state(Route::class, 'inactive', function (Faker $faker) {
    return [
        'is_active' => false
    ];
});

$factory->state(Route::class, 'makeRoutableHelloWorld', function (Faker $faker) {
    return [
        'routable' => function () {
            return new App\HelloWorld();
        }
    ];
});

$factory->state(Route::class, 'makeInactiveRoutableHelloWorld', function (Faker $faker) {
    return [
        'routable' => function () {
            return new App\HelloWorld(false);
        }
    ];
});
