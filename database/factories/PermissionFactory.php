<?php

use Faker\Generator as Faker;
use Gzero\Core\Models\Permission;

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

$factory->define(Permission::class, function (Faker $faker) {
    return [
        'name' => null,
        'category' => 'general',
    ];
});

$factory->state(Permission::class, 'viewInactive', function (Faker $faker) {
    return [
        'name' => 'view-inactive-route',
    ];
});
