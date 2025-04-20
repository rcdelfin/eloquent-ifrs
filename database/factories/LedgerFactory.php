<?php

/** @var Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use IFRS\Models\Account;
use IFRS\Models\Balance;
use IFRS\Models\Currency;
use IFRS\Models\Ledger;
use IFRS\Models\LineItem;
use IFRS\Models\Transaction;
use IFRS\Models\Vat;

$factory->define(
    Ledger::class,
    function (Faker $faker) {
        return [
            'transaction_id' => factory(Transaction::class)->create()->id,
            'currency_id' => factory(Currency::class)->create()->id,
            'vat_id' => factory(Vat::class)->create()->id,
            'post_account' => factory(Account::class)->create([
                'category_id' => null,
            ])->id,
            'folio_account' => factory(Account::class)->create([
                'category_id' => null,
            ])->id,
            'line_item_id' => factory(LineItem::class)->create()->id,
            'posting_date' => $faker->dateTimeThisMonth(),
            'entry_type' => $faker->randomElement([
                Balance::DEBIT,
                Balance::CREDIT,
            ]),
            'amount' => $faker->randomFloat(2),
            'rate' => $faker->randomFloat(2),
        ];
    },
);
