<?php

use Faker\Generator as Faker;
use Gzero\core\Models\FileTranslation;
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

$factory->define(FileTranslation::class, function (Faker $faker) {
    return [
        'language_code' => function () {
            return factory(Language::class)->make()->code;
        },
        'title'         => $faker->realText(38, 1),
        'description'   => $faker->realText(160, 1)
    ];
});
