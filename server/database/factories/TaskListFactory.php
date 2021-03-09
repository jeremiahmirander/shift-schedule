<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\TaskList;
use Faker\Generator as Faker;

$factory->define(TaskList::class, function (Faker $faker) {
    return [
        'name' => $faker->words(rand(2,3), true),
    ];
});
