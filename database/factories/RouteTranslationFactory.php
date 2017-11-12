<?php

use Faker\Generator as Faker;
use Gzero\Core\Models\RouteTranslation;
use Gzero\Core\Models\Language;

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

$factory->define(RouteTranslation::class, function (Faker $faker) {
    return [
        'language_code' => function () {
            return factory(Language::class)->make()->code;
        },
        'path'          => $faker->slug,
        'is_active'     => true,
    ];
});

$factory->state(RouteTranslation::class, 'inactive', function (Faker $faker) {
    return [
        'language_code' => function () {
            return factory(Language::class)->make()->code;
        },
        'path'          => $faker->slug,
        'is_active'     => false,
    ];
});
