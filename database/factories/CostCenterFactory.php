<?php

/**
 * @var Illuminate\Database\Eloquent\Factory $factory
 */

use Faker\Generator as Faker;
use IFRS\Models\CostCenter;

$factory->define(
    CostCenter::class,
    function (Faker $faker) {
        return [
            'code' => $faker->unique()->regexify('[A-Z]{2}[0-9]{3}'),
            'name' => $faker->words(2, true),
            'description' => $faker->sentence(),
            'active' => $faker->boolean(80), // 80% chance of being active
        ];
    },
);
