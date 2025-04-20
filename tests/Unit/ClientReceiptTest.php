<?php

namespace Tests\Unit;

use Carbon\Carbon;
use IFRS\Exceptions\LineItemAccount;
use IFRS\Exceptions\MainAccount;
use IFRS\Exceptions\VatCharge;
use IFRS\Models\Account;
use IFRS\Models\Balance;
use IFRS\Models\Currency;
use IFRS\Models\Ledger;
use IFRS\Models\LineItem;
use IFRS\Models\Vat;
use IFRS\Tests\TestCase;
use IFRS\Transactions\ClientReceipt;

class ClientReceiptTest extends TestCase
{
    /**
     * Test Creating ClientReceipt Transaction
     *
     * @return void
     */
    public function testCreateClientReceiptTransaction()
    {
        $clientAccount = factory(Account::class)->create([
            'account_type' => Account::RECEIVABLE,
            'category_id' => null,
        ]);

        $clientReceipt = new ClientReceipt([
            "account_id" => $clientAccount->id,
            "transaction_date" => Carbon::now(),
            "narration" => $this->faker->word,
        ]);
        $clientReceipt->save();

        $this->assertEquals($clientReceipt->account->name, $clientAccount->name);
        $this->assertEquals($clientReceipt->account->description, $clientAccount->description);
        $this->assertEquals($clientReceipt->transaction_no, "RC0" . $this->period->period_count . "/0001");
    }

    /**
     * Test Posting ClientReceipt Transaction
     *
     * @return void
     */
    public function testPostClientReceiptTransaction()
    {
        $currency = factory(Currency::class)->create();
        $clientReceipt = new ClientReceipt(
            [
                "account_id" => factory(Account::class)->create([
                    'account_type' => Account::RECEIVABLE,
                    'category_id' => null,
                ])->id,
                "transaction_date" => Carbon::now(),
                "narration" => $this->faker->word,
                'currency_id' => $currency->id,
            ],
        );

        $lineItem = factory(LineItem::class)->create([
            "amount" => 100,
            "account_id" => factory(Account::class)->create([
                "account_type" => Account::BANK,
                'category_id' => null,
                'currency_id' => $currency->id,
            ])->id,
            "quantity" => 1,
        ]);
        $clientReceipt->addLineItem($lineItem);

        $clientReceipt->post();

        $debit = Ledger::where("entry_type", Balance::DEBIT)->get()[0];
        $credit = Ledger::where("entry_type", Balance::CREDIT)->get()[0];

        $this->assertEquals($debit->post_account, $lineItem->account_id);
        $this->assertEquals($debit->folio_account, $clientReceipt->account->id);
        $this->assertEquals($credit->folio_account, $lineItem->account_id);
        $this->assertEquals($credit->post_account, $clientReceipt->account->id);
        $this->assertEquals($debit->amount, 100);
        $this->assertEquals($credit->amount, 100);

        $this->assertEquals($clientReceipt->amount, 100);
    }

    /**
     * Test Client Receipt Line Item Account.
     *
     * @return void
     */
    public function testClientReceiptLineItemAccount()
    {
        $clientReceipt = new ClientReceipt([
            "account_id" => factory(Account::class)->create([
                'account_type' => Account::RECEIVABLE,
                'category_id' => null,
            ])->id,
            "transaction_date" => Carbon::now(),
            "narration" => $this->faker->word,
        ]);
        $this->expectException(LineItemAccount::class);
        $this->expectExceptionMessage('Client Receipt LineItem Account must be of type Bank');

        $lineItem = factory(LineItem::class)->create([
            "amount" => 100,
            "account_id" => factory(Account::class)->create([
                "account_type" => Account::RECONCILIATION,
                'category_id' => null,
            ])->id,
        ]);
        $lineItem->addVat(
            factory(Vat::class)->create([
                "rate" => 16,
            ]),
        );
        $lineItem->save();
        $clientReceipt->addLineItem($lineItem);

        $clientReceipt->post();
    }

    /**
     * Test Client Receipt Main Account.
     *
     * @return void
     */
    public function testClientReceiptMainAccount()
    {
        $currency = factory(Currency::class)->create();
        $clientReceipt = new ClientReceipt([
            "account_id" => factory(Account::class)->create([
                'account_type' => Account::RECONCILIATION,
                'category_id' => null,
            ])->id,
            "transaction_date" => Carbon::now(),
            'currency_id' => $currency->id,
            "narration" => $this->faker->word,
        ]);
        $this->expectException(MainAccount::class);
        $this->expectExceptionMessage('Client Receipt Main Account must be of type Receivable');

        $lineItem = factory(LineItem::class)->create([
            "amount" => 100,
            "account_id" => factory(Account::class)->create([
                "account_type" => Account::BANK,
                'category_id' => null,
                'currency_id' => $currency->id,
            ])->id,
        ]);
        $clientReceipt->addLineItem($lineItem);

        $clientReceipt->post();
    }

    /**
     * Test Client Receipt Vat Charge Account.
     *
     * @return void
     */
    public function testClientReceiptVatCharge()
    {
        $clientReceipt = new ClientReceipt([
            "account_id" => factory(Account::class)->create([
                'account_type' => Account::RECEIVABLE,
                'category_id' => null,
            ])->id,
            "transaction_date" => Carbon::now(),
            "narration" => $this->faker->word,
        ]);
        $this->expectException(VatCharge::class);
        $this->expectExceptionMessage('Client Receipt LineItems cannot be Charged VAT');

        $lineItem = factory(LineItem::class)->create([
            "amount" => 100,
            "account_id" => factory(Account::class)->create([
                "account_type" => Account::BANK,
                'category_id' => null,
            ])->id,
        ]);
        $lineItem->addVat(
            factory(Vat::class)->create([
                "rate" => 10,
            ]),
        );
        $lineItem->save();
        $clientReceipt->addLineItem($lineItem);

        $clientReceipt->post();
    }

    /**
     * Test Client Receipt Find.
     *
     * @return void
     */
    public function testClientReceiptFind()
    {
        $account = factory(Account::class)->create([
            'account_type' => Account::RECEIVABLE,
            'category_id' => null,
        ]);
        $transaction = new ClientReceipt([
            "account_id" => $account->id,
            "transaction_date" => Carbon::now(),
            "narration" => $this->faker->word,
        ]);
        $transaction->save();

        $found = ClientReceipt::find($transaction->id);
        $this->assertEquals($found->transaction_no, $transaction->transaction_no);
    }
}
