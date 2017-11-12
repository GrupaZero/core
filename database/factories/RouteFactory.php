<?php

use Faker\Generator as Faker;
use Gzero\Core\Models\Route;
use Gzero\Core\Models\RouteTranslation;

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
        'routable_id'   => null,
        'routable_type' => null
    ];
});

$factory->state(Route::class, 'makeTranslationEn', function (Faker $faker) {
    return [
        'translations' => function () {
            return factory(RouteTranslation::class, 1)
                ->make(['language_code' => 'en']);
        },
    ];
});

$factory->state(Route::class, 'makeInactiveTranslationEn', function (Faker $faker) {
    return [
        'translations' => function () {
            return factory(RouteTranslation::class, 1)
                ->states('inactive')
                ->make(['language_code' => 'en']);
        },
    ];
});

$factory->state(Route::class, 'makeRoutableHelloWorld', function (Faker $faker) {
    return [
        'routable' => function () {
            return new App\HelloWorld();
        }
    ];
});
