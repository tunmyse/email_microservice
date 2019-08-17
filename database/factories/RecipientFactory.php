<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Recipient;
use Faker\Generator as Faker;

$factory->define(Recipient::class, function (Faker $faker) {
    return [
        'address' => $faker->safeEmail
    ];
});
