<?php

/**
 * @var Illuminate\Database\Eloquent\Factory $factory
 */

use Faker\Generator as Faker;
use IFRS\Models\Currency;

$factory->define(
    Currency::class,
    function (Faker $faker) {
        return [
            'name'          => $faker->name,
            'currency_code' => $faker->currencyCode,
        ];
    },
);
