<?php

use Faker\Generator as Faker;
use Gzero\Core\Models\File;

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

$factory->define(File::class, function (Faker $faker) {
    return [
        'type'      => 'image',
        'name'      => $faker->slug,
        'extension' => $faker->fileExtension,
        'size'      => $faker->randomNumber(),
        'mime_type' => $faker->mimeType,
        'info'      => array_combine($this->faker->words(), $this->faker->words()),
        'is_active' => false
    ];
});

