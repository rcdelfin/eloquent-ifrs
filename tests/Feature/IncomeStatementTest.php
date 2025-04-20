<?php

namespace Tests\Feature;

use Carbon\Carbon;
use IFRS\Models\Account;
use IFRS\Models\Assignment;
use IFRS\Models\Currency;
use IFRS\Models\ExchangeRate;
use IFRS\Models\LineItem;
use IFRS\Models\ReportingPeriod;
use IFRS\Models\Vat;
use IFRS\Reports\IncomeStatement;
use IFRS\Tests\TestCase;
use IFRS\Transactions\CashPurchase;
use IFRS\Transactions\CashSale;
use IFRS\Transactions\ClientInvoice;
use IFRS\Transactions\CreditNote;
use IFRS\Transactions\DebitNote;
use IFRS\Transactions\JournalEntry;
use IFRS\Transactions\SupplierBill;
use Illuminate\Support\Facades\Auth;

class IncomeStatementTest extends TestCase
{
    /**
     * Test Income Statement
     *
     * @return void
     */
    public function testIncomeStatement()
    {
        $incomeStatement = new IncomeStatement();
        $incomeStatement->attributes();

        /*
         | ------------------------------
         | Operating Revenue Transactions
         | ------------------------------
         */
        $currency = factory(Currency::class)->create();
        $cashSale = new CashSale([
            "account_id" => factory(Account::class)->create([
                'account_type' => Account::BANK,
                'category_id' => null,
                'currency_id' => $currency->id,
            ])->id,
            "date" => Carbon::now(),
            'currency_id' => $currency->id,
            "narration" => $this->faker->word,
        ]);

        $lineItem = factory(LineItem::class)->create([
            "amount" => 200,
            "account_id" => factory(Account::class)->create([
                "account_type" => Account::OPERATING_REVENUE,
                'category_id' => null,
            ])->id,
            "quantity" => 1,
        ]);
        $lineItem->addVat(
            factory(Vat::class)->create([
                "rate" => 16,
            ]),
        );
        $lineItem->save();

        $cashSale->addLineItem($lineItem);
        $cashSale->post();

        $clientCurrency = factory(Currency::class)->create();
        $client = factory(Account::class)->create([
            'account_type' => Account::RECEIVABLE,
            'category_id' => null,
            'currency_id' => $clientCurrency->id,
        ]);

        $creditNote = new CreditNote([
            "account_id" => $client->id,
            "date" => Carbon::now(),
            "narration" => $this->faker->word,
            "exchange_rate_id" => factory(ExchangeRate::class)->create([
                "rate" => 1.1,
                'currency_id' => $clientCurrency->id,
            ])->id,
        ]);

        $lineItem = factory(LineItem::class)->create([
            "amount" => 50,
            "account_id" => factory(Account::class)->create([
                "account_type" => Account::OPERATING_REVENUE,
                'category_id' => null,
            ])->id,
            "quantity" => 1,
        ]);
        $creditNote->addLineItem($lineItem);

        $creditNote->post();

        $ClientInvoice = new ClientInvoice([
            "account_id" => $client->id,
            "transaction_date" => Carbon::now(),
            "narration" => $this->faker->word,
            "exchange_rate_id" => factory(ExchangeRate::class)->create([
                "rate" => 1,
                'currency_id' => $clientCurrency->id,
            ])->id,
        ]);

        $lineItem = new LineItem([
            'account_id' => factory(Account::class)->create([
                'account_type' => Account::OPERATING_REVENUE,
                'category_id' => null,
            ])->id,
            'amount' => 100,
        ]);

        $ClientInvoice->addLineItem($lineItem);
        $ClientInvoice->post();

        $forex = factory(Account::class)->create([
            'account_type' => Account::NON_OPERATING_REVENUE,
            'category_id' => null,
        ]);

        $assignment = new Assignment([
            'assignment_date' => Carbon::now(),
            'transaction_id' => $creditNote->id,
            'cleared_id' => $ClientInvoice->id,
            'cleared_type' => $ClientInvoice->cleared_type,
            'amount' => 50,
            'forex_account_id' => $forex->id,
        ]);
        $assignment->save();

        /*
         | ------------------------------
         | Non Operating Revenue Transactions
         | ------------------------------
         */
        $currency = factory(Currency::class)->create();
        $journalEntry = new JournalEntry([
            "account_id" => factory(Account::class)->create([
                'account_type' => Account::BANK,
                'category_id' => null,
                'currency_id' => $currency->id,
            ])->id,
            "date" => Carbon::now(),
            "narration" => $this->faker->word,
            "credited" => false,
            'currency_id' => $currency->id,
        ]);

        $lineItem = factory(LineItem::class)->create([
            "amount" => 200,
            "account_id" => factory(Account::class)->create([
                "account_type" => Account::NON_OPERATING_REVENUE,
                'category_id' => null,
            ])->id,
            "quantity" => 1,
        ]);
        $lineItem->addVat(
            factory(Vat::class)->create([
                "rate" => 16,
            ]),
        );
        $lineItem->save();
        $journalEntry->addLineItem($lineItem);
        $journalEntry->post();

        /*
         | ------------------------------
         | Operating Expense Transactions
         | ------------------------------
         */
        $bill = new SupplierBill([
            "account_id" => factory(Account::class)->create([
                'account_type' => Account::PAYABLE,
                'category_id' => null,
            ])->id,
            "date" => Carbon::now(),
            "narration" => $this->faker->word,
        ]);

        $lineItem = factory(LineItem::class)->create([
            "amount" => 100,
            "account_id" => factory(Account::class)->create([
                "account_type" => Account::OPERATING_EXPENSE,
                'category_id' => null,
            ])->id,
            "quantity" => 1,
        ]);
        $lineItem->addVat(
            factory(Vat::class)->create([
                "rate" => 16,
            ]),
        );
        $lineItem->save();
        $bill->addLineItem($lineItem);
        $bill->post();

        /*
         | ------------------------------
         | None Operating Expense Transactions
         | ------------------------------
         */
        $bill = new SupplierBill([
            "account_id" => factory(Account::class)->create([
                'account_type' => Account::PAYABLE,
                'category_id' => null,
            ])->id,
            "date" => Carbon::now(),
            "narration" => $this->faker->word,
        ]);

        $lineItem = factory(LineItem::class)->create([
            "amount" => 70,
            "account_id" => factory(Account::class)->create([
                "account_type" => Account::DIRECT_EXPENSE,
                'category_id' => null,
            ])->id,
            "quantity" => 1,
        ]);
        $lineItem->addVat(
            factory(Vat::class)->create([
                "rate" => 16,
            ]),
        );
        $lineItem->save();
        $bill->addLineItem($lineItem);
        $bill->post();

        $journalEntry = new JournalEntry([
            "account_id" => factory(Account::class)->create([
                'account_type' => Account::PAYABLE,
                'category_id' => null,
            ])->id,
            "date" => Carbon::now(),
            "narration" => $this->faker->word,
        ]);

        $lineItem = factory(LineItem::class)->create([
            "amount" => 70,
            "account_id" => factory(Account::class)->create([
                "account_type" => Account::OVERHEAD_EXPENSE,
                'category_id' => null,
            ])->id,
            "quantity" => 1,
        ]);

        $journalEntry->addLineItem($lineItem);
        $journalEntry->post();

        $currency = factory(Currency::class)->create();
        $cashPurchase = new CashPurchase([
            "account_id" => factory(Account::class)->create([
                'account_type' => Account::BANK,
                'category_id' => null,
                'currency_id' => $currency->id,
            ])->id,
            "date" => Carbon::now(),
            "narration" => $this->faker->word,
            'currency_id' => $currency->id,
        ]);

        $lineItem = factory(LineItem::class)->create([
            "amount" => 70,
            "account_id" => factory(Account::class)->create([
                "account_type" => Account::OTHER_EXPENSE,
                'category_id' => null,
            ])->id,
            "quantity" => 1,
        ]);
        $cashPurchase->addLineItem($lineItem);
        $cashPurchase->post();

        $debitNote = new DebitNote([
            "account_id" => factory(Account::class)->create([
                'account_type' => Account::PAYABLE,
                'category_id' => null,
            ])->id,
            "date" => Carbon::now(),
            "narration" => $this->faker->word,
        ]);

        $lineItem = factory(LineItem::class)->create([
            "amount" => 50,
            "account_id" => factory(Account::class)->create([
                "account_type" => Account::OTHER_EXPENSE,
                'category_id' => null,
            ])->id,
            "quantity" => 1,
        ]);
        $debitNote->addLineItem($lineItem);

        $debitNote->post();

        $startDate = ReportingPeriod::periodStart();
        $endDate = ReportingPeriod::periodEnd();

        $sections = $incomeStatement->getSections($startDate, $endDate, false);
        $incomeStatement->toString();

        $operatingRevenues = IncomeStatement::OPERATING_REVENUES;
        $operatingExpenses = IncomeStatement::OPERATING_EXPENSES;
        $nonOperatingRevenues = IncomeStatement::NON_OPERATING_REVENUES;
        $nonOperatingExpenses = IncomeStatement::NON_OPERATING_EXPENSES;

        $this->assertEquals(
            $sections,
            [
                "accounts" => $incomeStatement->accounts,
                "balances" => $incomeStatement->balances,
                "results" => $incomeStatement->results,
                "totals" => $incomeStatement->totals,
            ],
        );


        $this->assertEquals(
            $incomeStatement->balances[$operatingRevenues][Account::OPERATING_REVENUE],
            -245,
        );

        $this->assertEquals(
            $incomeStatement->balances[$operatingExpenses][Account::OPERATING_EXPENSE],
            100,
        );

        $this->assertEquals(
            $incomeStatement->balances[$nonOperatingRevenues][Account::NON_OPERATING_REVENUE],
            -205,
        );

        $this->assertEquals(
            $incomeStatement->balances[$nonOperatingExpenses][Account::DIRECT_EXPENSE],
            70,
        );

        $this->assertEquals(
            $incomeStatement->balances[$nonOperatingExpenses][Account::OVERHEAD_EXPENSE],
            70,
        );

        $this->assertEquals(
            $incomeStatement->balances[$nonOperatingExpenses][Account::OTHER_EXPENSE],
            20,
        );

        $results = IncomeStatement::getResults(date('m'), date('y'));

        $this->assertEquals(
            $results[IncomeStatement::OPERATING_REVENUES],
            245,
        );

        $this->assertEquals(
            $results[IncomeStatement::NON_OPERATING_REVENUES],
            205,
        );

        $this->assertEquals(
            $results[IncomeStatement::OPERATING_EXPENSES],
            100,
        );

        $this->assertEquals(
            $results[IncomeStatement::GROSS_PROFIT],
            350,
        );

        $this->assertEquals(
            $results[IncomeStatement::NON_OPERATING_EXPENSES],
            160,
        );

        $this->assertEquals(
            $results[IncomeStatement::NET_PROFIT],
            190,
        );
    }

    /**
     * Test Income Statement when logged out
     *
     * @return void
     */
    public function testIncomeStatementLoggedOut()
    {
        $entity = Auth::user()->entity;
        Auth::logout(); //log out user

        $incomeStatement = new IncomeStatement(null, null, $entity);
        $incomeStatement->attributes();

        /*
         | ------------------------------
         | Operating Revenue Transactions
         | ------------------------------
         */
        $currency = factory(Currency::class)->create([
            "entity_id" => $entity->id,
        ]);
        $cashSale = new CashSale([
            "account_id" => Account::create([
                'account_type' => Account::BANK,
                'category_id' => null ,
                'entity_id' => $entity->id,
                'currency_id' => $currency->id,
            ])->id,
            "date" => Carbon::now(),
            'currency_id' => $currency->id,
            "narration" => $this->faker->word,
            "entity_id" => $entity->id,
        ]);

        $lineItem = LineItem::create([
            'amount' => 200,
            "account_id" => Account::create([
                'account_type' => Account::OPERATING_REVENUE,
                'category_id' => null ,
                'entity_id' => $entity->id,
            ])->id,
            "quantity" => 1,
            "entity_id" => $entity->id,
        ]);
        $vat = Vat::create([
            'name' => 'Test vat',
            'code' => 'T',
            'entity_id' => $entity->id,
            'rate' => 16,
            'account_id' => Account::create([
                'account_type' => Account::CONTROL,
                'category_id' => null,
                'entity_id' => $entity->id,
            ])->id,
        ]);
        $lineItem->addVat($vat);


        $cashSale->addLineItem($lineItem);
        $cashSale->post();

        $clientCurrency = factory(Currency::class)->create([
            "entity_id" => $entity->id,
        ]);

        $client = Account::create([
            'account_type' => Account::RECEIVABLE,
            'category_id' => null ,
            'entity_id' => $entity->id,
            'currency_id' => $clientCurrency->id,
        ]);

        $creditNote = new CreditNote([
            "account_id" => $client->id,
            "date" => Carbon::now(),
            "narration" => $this->faker->word,
            "exchange_rate_id" => ExchangeRate::create([
                'valid_from' => $this->faker->dateTimeThisMonth(),
                'valid_to' => Carbon::now(),
                'currency_id' => $clientCurrency->id,
                'rate' => 1.1,
                'entity_id' => $entity->id,
            ])->id,
            "entity_id" => $entity->id,
        ]);

        $lineItem = LineItem::create([
            'amount' => 50,
            "account_id" => Account::create([
                'account_type' => Account::OPERATING_REVENUE,
                'category_id' => null ,
                'entity_id' => $entity->id,
            ])->id,
            "quantity" => 1,
            "entity_id" => $entity->id,
        ]);

        $creditNote->addLineItem($lineItem);

        $creditNote->post();

        $ClientInvoice = new ClientInvoice([
            "account_id" => $client->id,
            "transaction_date" => Carbon::now(),
            "narration" => $this->faker->word,
            "exchange_rate_id" => ExchangeRate::create([
                'valid_from' => $this->faker->dateTimeThisMonth(),
                'valid_to' => Carbon::now(),
                'currency_id' => $clientCurrency->id,
                'rate' => 1,
                'entity_id' => $entity->id,
            ])->id,
            "entity_id" => $entity->id,
        ]);

        $lineItem = LineItem::create([
            'amount' => 100,
            "account_id" => Account::create([
                'account_type' => Account::OPERATING_REVENUE,
                'category_id' => null ,
                'entity_id' => $entity->id,
            ])->id,
            "entity_id" => $entity->id,
        ]);

        $ClientInvoice->addLineItem($lineItem);
        $ClientInvoice->post();

        $forex = Account::create([
            'account_type' => Account::NON_OPERATING_REVENUE,
            'category_id' => null ,
            'entity_id' => $entity->id,
        ]);

        $assignment = new Assignment([
            'assignment_date' => Carbon::now(),
            'transaction_id' => $creditNote->id,
            'cleared_id' => $ClientInvoice->id,
            'cleared_type' => $ClientInvoice->cleared_type,
            'amount' => 50,
            'forex_account_id' => $forex->id,
            'entity_id' => $entity->id,
        ]);
        $assignment->save();

        /*
         | ------------------------------
         | Non Operating Revenue Transactions
         | ------------------------------
         */
        $currency = factory(Currency::class)->create([
            "entity_id" => $entity->id,
        ]);

        $journalEntry = new JournalEntry([
            "account_id" => Account::create([
                'account_type' => Account::BANK,
                'category_id' => null,
                'currency_id' => $currency->id,
                'entity_id' => $entity->id,
            ])->id,
            "date" => Carbon::now(),
            "narration" => $this->faker->word,
            "credited" => false,
            'currency_id' => $currency->id,
            "entity_id" => $entity->id,
        ]);

        $lineItem = LineItem::create([
            'amount' => 200,
            "account_id" => Account::create([
                'account_type' => Account::NON_OPERATING_REVENUE,
                'category_id' => null ,
                'entity_id' => $entity->id,
            ])->id,
            "quantity" => 1,
            "entity_id" => $entity->id,
        ]);
        $vat2 = Vat::create([
            'name' => 'Test vat',
            'code' => 'T',
            'entity_id' => $entity->id,
            'rate' => 16,
            'account_id' => Account::create([
                'account_type' => Account::CONTROL,
                'category_id' => null,
                'entity_id' => $entity->id,
            ])->id,
        ]);
        $lineItem->addVat($vat2);
        $lineItem->save();

        $journalEntry->addLineItem($lineItem);
        $journalEntry->post();

        /*
         | ------------------------------
         | Operating Expense Transactions
         | ------------------------------
         */
        $bill = new SupplierBill([
            "account_id" => Account::create([
                'account_type' => Account::PAYABLE,
                'category_id' => null ,
                'entity_id' => $entity->id,
            ])->id,
            "date" => Carbon::now(),
            "narration" => $this->faker->word,
            "entity_id" => $entity->id,
        ]);

        $lineItem = LineItem::create([
            'amount' => 100,
            "account_id" => Account::create([
                'account_type' => Account::OPERATING_EXPENSE,
                'category_id' => null ,
                'entity_id' => $entity->id,
            ])->id,
            "quantity" => 1,
            "entity_id" => $entity->id,
        ]);
        $vat3 = Vat::create([
            'name' => 'Test vat',
            'code' => 'T',
            'entity_id' => $entity->id,
            'rate' => 16,
            'account_id' => Account::create([
                'account_type' => Account::CONTROL,
                'category_id' => null,
                'entity_id' => $entity->id,
            ])->id,
        ]);
        $lineItem->addVat($vat3);

        $bill->addLineItem($lineItem);
        $bill->post();

        /*
         | ------------------------------
         | None Operating Expense Transactions
         | ------------------------------
         */
        $bill = new SupplierBill([
            "account_id" => Account::create([
                'account_type' => Account::PAYABLE,
                'category_id' => null ,
                'entity_id' => $entity->id,
            ])->id,
            "date" => Carbon::now(),
            "narration" => $this->faker->word,
            "entity_id" => $entity->id,
        ]);

        $lineItem = LineItem::create([
            'amount' => 70,
            "account_id" => Account::create([
                'account_type' => Account::DIRECT_EXPENSE,
                'category_id' => null ,
                'entity_id' => $entity->id,
            ])->id,
            "quantity" => 1,
            "entity_id" => $entity->id,
        ]);
        $vat5 = Vat::create([
            'name' => 'Test vat',
            'code' => 'T',
            'entity_id' => $entity->id,
            'rate' => 16,
            'account_id' => Account::create([
                'account_type' => Account::CONTROL,
                'category_id' => null,
                'entity_id' => $entity->id,
            ])->id,
        ]);
        $lineItem->addVat($vat5);

        $bill->addLineItem($lineItem);
        $bill->post();

        $journalEntry = new JournalEntry([
            "account_id" => Account::create([
                'account_type' => Account::PAYABLE,
                'category_id' => null,
                'entity_id' => $entity->id,
            ])->id,
            "date" => Carbon::now(),
            "narration" => $this->faker->word,
            "entity_id" => $entity->id,
        ]);

        $lineItem = LineItem::create([
            'amount' => 70,
            "account_id" => Account::create([
                'account_type' => Account::OVERHEAD_EXPENSE,
                'category_id' => null ,
                'entity_id' => $entity->id,
            ])->id,
            "quantity" => 1,
            "entity_id" => $entity->id,
        ]);

        $journalEntry->addLineItem($lineItem);
        $journalEntry->post();

        $currency = factory(Currency::class)->create([
            "entity_id" => $entity->id,
        ]);
        $cashPurchase = new CashPurchase([
            "account_id" => Account::create([
                'account_type' => Account::BANK,
                'category_id' => null,
                'currency_id' => $currency->id,
                'entity_id' => $entity->id,
            ])->id,
            "date" => Carbon::now(),
            "narration" => $this->faker->word,
            'currency_id' => $currency->id,
            'entity_id' => $entity->id,
        ]);

        $lineItem = LineItem::create([
            'amount' => 70,
            "account_id" => Account::create([
                'account_type' => Account::OTHER_EXPENSE,
                'category_id' => null ,
                'entity_id' => $entity->id,
            ])->id,
            "quantity" => 1,
            "entity_id" => $entity->id,
        ]);

        $cashPurchase->addLineItem($lineItem);
        $cashPurchase->post();

        $debitNote = new DebitNote([
            "account_id" => Account::create([
                'account_type' => Account::PAYABLE,
                'category_id' => null,
                'entity_id' => $entity->id,
            ])->id,
            "date" => Carbon::now(),
            "narration" => $this->faker->word,
            "entity_id" => $entity->id,
        ]);

        $lineItem = LineItem::create([
            'amount' => 50,
            "account_id" => Account::create([
                'account_type' => Account::OTHER_EXPENSE,
                'category_id' => null ,
                'entity_id' => $entity->id,
            ])->id,
            "quantity" => 1,
            "entity_id" => $entity->id,
        ]);

        $debitNote->addLineItem($lineItem);

        $debitNote->post();

        $startDate = ReportingPeriod::periodStart(null, $entity);
        $endDate = ReportingPeriod::periodEnd(null, $entity);

        $sections = $incomeStatement->getSections($startDate, $endDate, false);
        $incomeStatement->toString();

        $operatingRevenues = IncomeStatement::OPERATING_REVENUES;
        $operatingExpenses = IncomeStatement::OPERATING_EXPENSES;
        $nonOperatingRevenues = IncomeStatement::NON_OPERATING_REVENUES;
        $nonOperatingExpenses = IncomeStatement::NON_OPERATING_EXPENSES;

        $this->assertEquals(
            $sections,
            [
                "accounts" => $incomeStatement->accounts,
                "balances" => $incomeStatement->balances,
                "results" => $incomeStatement->results,
                "totals" => $incomeStatement->totals,
            ],
        );


        $this->assertEquals(
            $incomeStatement->balances[$operatingRevenues][Account::OPERATING_REVENUE],
            -245,
        );

        $this->assertEquals(
            $incomeStatement->balances[$operatingExpenses][Account::OPERATING_EXPENSE],
            100,
        );

        $this->assertEquals(
            $incomeStatement->balances[$nonOperatingRevenues][Account::NON_OPERATING_REVENUE],
            -205,
        );

        $this->assertEquals(
            $incomeStatement->balances[$nonOperatingExpenses][Account::DIRECT_EXPENSE],
            70,
        );

        $this->assertEquals(
            $incomeStatement->balances[$nonOperatingExpenses][Account::OVERHEAD_EXPENSE],
            70,
        );

        $this->assertEquals(
            $incomeStatement->balances[$nonOperatingExpenses][Account::OTHER_EXPENSE],
            20,
        );

        $results = IncomeStatement::getResults(date('m'), date('y'), $entity);

        $this->assertEquals(
            $results[IncomeStatement::OPERATING_REVENUES],
            245,
        );

        $this->assertEquals(
            $results[IncomeStatement::NON_OPERATING_REVENUES],
            205,
        );

        $this->assertEquals(
            $results[IncomeStatement::OPERATING_EXPENSES],
            100,
        );

        $this->assertEquals(
            $results[IncomeStatement::GROSS_PROFIT],
            350,
        );

        $this->assertEquals(
            $results[IncomeStatement::NON_OPERATING_EXPENSES],
            160,
        );

        $this->assertEquals(
            $results[IncomeStatement::NET_PROFIT],
            190,
        );
    }
}
