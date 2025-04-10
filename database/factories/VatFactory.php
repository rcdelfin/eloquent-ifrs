<?php

/**
 * @var Illuminate\Database\Eloquent\Factory $factory
 */

use Faker\Generator as Faker;
use IFRS\Models\Account;
use IFRS\Models\Vat;

$factory->define(
    Vat::class,
    function (Faker $faker) {
        return [
            'name'       => $faker->name,
            'code'       => $faker->randomLetter(),
            'rate'       => $faker->randomDigit(),
            'account_id' => factory(Account::class)->create([
                'account_type' => Account::CONTROL,
                'category_id'  => null,
            ])->id,
        ];
    },
);
