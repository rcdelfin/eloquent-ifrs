<?php

/** @var Illuminate\Database\Eloquent\Factory $factory */

use Carbon\Carbon;
use Faker\Generator as Faker;
use IFRS\Models\Account;
use IFRS\Models\Transaction;
use IFRS\Transactions\JournalEntry;

$factory->define(
    JournalEntry::class,
    function (Faker $faker) {
        return [
            'account_id'       => factory(Account::class)->create()->id,
            'date'             => Carbon::now(),
            'narration'        => $faker->word,
            'transaction_type' => Transaction::JN,
            'amount'           => $faker->randomFloat(2),
        ];
    },
);
