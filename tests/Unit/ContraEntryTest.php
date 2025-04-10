<?php

namespace Tests\Unit;

use Carbon\Carbon;
use IFRS\Exceptions\LineItemAccount;
use IFRS\Exceptions\MainAccount;
use IFRS\Models\Account;
use IFRS\Models\Balance;
use IFRS\Models\Currency;
use IFRS\Models\Ledger;
use IFRS\Models\LineItem;
use IFRS\Models\Vat;
use IFRS\Tests\TestCase;
use IFRS\Transactions\ContraEntry;

class ContraEntryTest extends TestCase
{
    /**
     * Test Creating ContraEntry Transaction
     *
     * @return void
     */
    public function testCreateContraEntryTransaction()
    {
        $bankAccount = factory(Account::class)->create([
            'account_type' => Account::BANK,
            'category_id'  => null,
        ]);

        $contraEntry = new ContraEntry([
            "account_id"       => $bankAccount->id,
            "transaction_date" => Carbon::now(),
            "narration"        => $this->faker->word,
            'currency_id'      => $bankAccount->currency_id,
        ]);
        $contraEntry->save();

        $this->assertEquals($contraEntry->account->name, $bankAccount->name);
        $this->assertEquals($contraEntry->account->description, $bankAccount->description);
        $this->assertEquals($contraEntry->transaction_no, "CE0" . $this->period->period_count . "/0001");
    }

    /**
     * Test Posting ContraEntry Transaction
     *
     * @return void
     */
    public function testPostContraEntryTransaction()
    {
        $currency    = factory(Currency::class)->create();
        $contraEntry = new ContraEntry([
            "account_id" => factory(Account::class)->create([
                'account_type' => Account::BANK,
                'category_id'  => null,
                'currency_id'  => $currency->id,
            ])->id,
            "transaction_date" => Carbon::now(),
            "narration"        => $this->faker->word,
            'currency_id'      => $currency->id,
        ]);

        $lineItem = factory(LineItem::class)->create([
            "amount"     => 100,
            "account_id" => factory(Account::class)->create([
                "account_type" => Account::BANK,
                'category_id'  => null,
                'currency_id'  => $currency->id,
            ])->id,
            "quantity" => 1,
        ]);
        $contraEntry->addLineItem($lineItem);

        $contraEntry->post();

        $debit  = Ledger::where("entry_type", Balance::DEBIT)->get()[0];
        $credit = Ledger::where("entry_type", Balance::CREDIT)->get()[0];

        $this->assertEquals($debit->post_account, $contraEntry->account->id);
        $this->assertEquals($debit->folio_account, $lineItem->account_id);
        $this->assertEquals($credit->folio_account, $contraEntry->account->id);
        $this->assertEquals($credit->post_account, $lineItem->account_id);
        $this->assertEquals($debit->amount, 100);
        $this->assertEquals($credit->amount, 100);

        $this->assertEquals($contraEntry->amount, 100);
    }

    /**
     * Test Contra Entry Line Item Account.
     *
     * @return void
     */
    public function testContraEntryLineItemAccount()
    {
        $contraEntry = new ContraEntry([
            "account_id" => factory(Account::class)->create([
                'account_type' => Account::BANK,
                'category_id'  => null,
            ])->id,
            "transaction_date" => Carbon::now(),
            "narration"        => $this->faker->word,
        ]);
        $this->expectException(LineItemAccount::class);
        $this->expectExceptionMessage('Contra Entry LineItem Account must be of type Bank');

        $lineItem = factory(LineItem::class)->create([
            "amount"     => 100,
            "account_id" => factory(Account::class)->create([
                "account_type" => Account::RECONCILIATION,
                'category_id'  => null,
            ])->id,
        ]);
        $lineItem->addVat(
            factory(Vat::class)->create([
                "rate" => 16,
            ]),
        );
        $lineItem->save();
        $contraEntry->addLineItem($lineItem);

        $contraEntry->post();
    }

    /**
     * Test Contra Entry Main Account.
     *
     * @return void
     */
    public function testContraEntryMainAccount()
    {
        $currency    = factory(Currency::class)->create();
        $contraEntry = new ContraEntry([
            "account_id" => factory(Account::class)->create([
                'account_type' => Account::RECONCILIATION,
                'category_id'  => null,
            ])->id,
            "transaction_date" => Carbon::now(),
            "narration"        => $this->faker->word,
            'currency_id'      => $currency->id,
        ]);
        $this->expectException(MainAccount::class);
        $this->expectExceptionMessage('Contra Entry Main Account must be of type Bank');

        $lineItem = factory(LineItem::class)->create([
            "amount"     => 100,
            "account_id" => factory(Account::class)->create([
                "account_type" => Account::BANK,
                'category_id'  => null,
                'currency_id'  => $currency->id,
            ])->id,
        ]);
        $contraEntry->addLineItem($lineItem);

        $contraEntry->save();
    }

    /**
     * Test Contra Entry Find.
     *
     * @return void
     */
    public function testContraEntryFind()
    {
        $account = factory(Account::class)->create([
            'account_type' => Account::BANK,
            'category_id'  => null,
        ]);
        $transaction = new ContraEntry([
            "account_id"       => $account->id,
            "transaction_date" => Carbon::now(),
            "narration"        => $this->faker->word,
            'currency_id'      => $account->currency_id,
        ]);
        $transaction->save();

        $found = ContraEntry::find($transaction->id);
        $this->assertEquals($found->transaction_no, $transaction->transaction_no);
    }
}
