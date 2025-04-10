<?php

/**
 * @var Illuminate\Database\Eloquent\Factory $factory
 */

use Faker\Generator as Faker;
use IFRS\Models\Category;

$factory->define(
    Category::class,
    function (Faker $faker) {
        return [
            'name'          => $faker->word,
            'category_type' => $faker->randomElement(
                array_keys(config('ifrs')['accounts']),
            ),
        ];
    },
);
