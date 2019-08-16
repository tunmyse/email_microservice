<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Email;
use Faker\Generator as Faker;

$factory->define(Email::class, function (Faker $faker) {
    return [
        'subject' => $faker->sentence,
        'body' => $faker->paragraph,
        'format' => $faker->randomElement(['plain', 'html']),
        'recipients' => $faker->safeEmail
    ];
});
