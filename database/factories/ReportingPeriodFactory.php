<?php

/**
 * @var Illuminate\Database\Eloquent\Factory $factory
 */

use Faker\Generator as Faker;
use IFRS\Models\ReportingPeriod;

$factory->define(
    ReportingPeriod::class,
    function (Faker $faker) {
        return [
            'period_count' => $faker->randomDigit,
            'calendar_year' => $faker->unique()->year,
            'status' => ReportingPeriod::OPEN,
        ];
    },
);
