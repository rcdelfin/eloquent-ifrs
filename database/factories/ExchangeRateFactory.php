<?php

/**
 * @var Illuminate\Database\Eloquent\Factory $factory
 */

use Carbon\Carbon;
use Faker\Generator as Faker;
use IFRS\Models\Currency;
use IFRS\Models\ExchangeRate;

$factory->define(
    ExchangeRate::class,
    function (Faker $faker) {
        return [
            'valid_from'  => $faker->dateTimeThisMonth(),
            'valid_to'    => Carbon::now(),
            'currency_id' => factory(Currency::class)->create()->id,
            'rate'        => $faker->randomFloat(2, 1, 5),
        ];
    },
);
