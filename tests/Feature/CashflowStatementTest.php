<?php

namespace Tests\Feature;

use Carbon\Carbon;
use IFRS\Models\Account;
use IFRS\Models\Balance;
use IFRS\Models\Currency;
use IFRS\Models\ExchangeRate;
use IFRS\Models\LineItem;
use IFRS\Models\ReportingPeriod;
use IFRS\Models\Transaction;
use IFRS\Models\Vat;
use IFRS\Reports\CashFlowStatement;
use IFRS\Tests\TestCase;
use IFRS\Transactions\CashPurchase;
use IFRS\Transactions\ClientInvoice;
use IFRS\Transactions\CreditNote;
use IFRS\Transactions\DebitNote;
use IFRS\Transactions\JournalEntry;
use IFRS\Transactions\SupplierBill;
use Illuminate\Support\Facades\Auth;

class CashFlowStatementTest extends TestCase
{
    /**
     * Test Cash Flow Statement
     *
     * @return void
     */
    public function testCashFlowStatement()
    {
        $lastMonth         = Carbon::now()->subMonths(1);
        $endDate           = date("m") > 1 ? $lastMonth->toDateString() : date("Y-m") . "-01";
        $cashFlowStatement = new CashFlowStatement($endDate);
        $cashFlowStatement->attributes();

        $bank = factory(Account::class)->create([
            'name'         => 'Bank Account',
            'account_type' => Account::BANK,
            'category_id'  => null,
        ]);

        factory(Balance::class)->create([
            "account_id"       => $bank->id,
            "balance_type"     => Balance::DEBIT,
            "exchange_rate_id" => factory(ExchangeRate::class)->create([
                "rate" => 1,
            ])->id,
            'reporting_period_id' => $this->period->id,
            "currency_id"         => $bank->currency_id,
            "balance"             => 100,
        ]);


        /*
         | ------------------------------
         | Operating Revenue Transactions
         | ------------------------------
         */

        $clientInvoice = new ClientInvoice([
            "account_id" => factory(Account::class)->create([
                'account_type' => Account::RECEIVABLE,
                'category_id'  => null,
            ])->id,
            "date"      => Carbon::now(),
            "narration" => $this->faker->word,
        ]);

        $lineItem = factory(LineItem::class)->create([
            "amount"     => 500,
            "account_id" => factory(Account::class)->create([
                "account_type" => Account::OPERATING_REVENUE,
                'category_id'  => null,
            ])->id,
            "quantity" => 1,
        ]);

        $lineItem->addVat(
            factory(Vat::class)->create([
                "rate" => 16,
            ]),
        );
        $lineItem->save();

        $clientInvoice->addLineItem($lineItem);

        $clientInvoice->post();

        $creditNote = new CreditNote([
            "account_id" => factory(Account::class)->create([
                'account_type' => Account::RECEIVABLE,
                'category_id'  => null,
            ])->id,
            "date"      => Carbon::now(),
            "narration" => $this->faker->word,
        ]);

        $lineItem = factory(LineItem::class)->create([
            "amount"     => 50,
            "account_id" => factory(Account::class)->create([
                "account_type" => Account::OPERATING_REVENUE,
                'category_id'  => null,
            ])->id,
            "quantity" => 1,
        ]);
        $creditNote->addLineItem($lineItem);

        $creditNote->post();

        /*
         | ------------------------------
         | Non Operating Revenue Transactions
         | ------------------------------
         */

        $journalEntry = new JournalEntry([
            "account_id"  => $bank->id,
            "date"        => Carbon::now(),
            "narration"   => $this->faker->word,
            "credited"    => false,
            'currency_id' => $bank->currency_id,
        ]);

        $lineItem =  factory(LineItem::class)->create([
            "amount"     => 500,
            "account_id" => factory(Account::class)->create([
                "account_type" => Account::NON_OPERATING_REVENUE,
                'category_id'  => null,
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
                'category_id'  => null,
            ])->id,
            "date"      => Carbon::now(),
            "narration" => $this->faker->word,
        ]);

        $lineItem =  factory(LineItem::class)->create([
            "amount"     => 100,
            "account_id" => factory(Account::class)->create([
                "account_type" => Account::OPERATING_EXPENSE,
                'category_id'  => null,
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
         | Non Operating Expense Transactions
         | ------------------------------
         */
        $bill = new SupplierBill([
            "account_id" => factory(Account::class)->create([
                'account_type' => Account::PAYABLE,
                'category_id'  => null,
            ])->id,
            "date"      => Carbon::now(),
            "narration" => $this->faker->word,
        ]);

        $lineItem = factory(LineItem::class)->create([
            "amount"     => 100,
            "account_id" => factory(Account::class)->create([
                "account_type" => Account::DIRECT_EXPENSE,
                'category_id'  => null,
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

        // Depreciation
        $journalEntry = new JournalEntry([
            "account_id" => factory(Account::class)->create([
                'account_type' => Account::CONTRA_ASSET,
                'category_id'  => null,
            ])->id,
            "date"      => Carbon::now(),
            "narration" => $this->faker->word,
        ]);

        $lineItem = factory(LineItem::class)->create([
            "amount"     => 50,
            "account_id" => factory(Account::class)->create([
                "account_type" => Account::OTHER_EXPENSE,
                'category_id'  => null,
            ])->id,
            "quantity" => 1,
        ]);

        $journalEntry->addLineItem($lineItem);
        $journalEntry->post();

        $cashPurchase = new CashPurchase([
            "account_id"  => $bank->id,
            "date"        => Carbon::now(),
            "narration"   => $this->faker->word,
            'currency_id' => $bank->currency_id,
        ]);

        $lineItem = factory(LineItem::class)->create([
            "amount"     => 50,
            "account_id" => factory(Account::class)->create([
                "account_type" => Account::OTHER_EXPENSE,
                'category_id'  => null,
            ])->id,
            "quantity" => 1,
        ]);
        $cashPurchase->addLineItem($lineItem);
        $cashPurchase->post();

        $debitNote = new DebitNote([
            "account_id" => factory(Account::class)->create([
                'account_type' => Account::PAYABLE,
                'category_id'  => null,
            ])->id,
            "date"      => Carbon::now(),
            "narration" => $this->faker->word,
        ]);

        $lineItem = factory(LineItem::class)->create([
            "amount"     => 50,
            "account_id" => factory(Account::class)->create([
                "account_type" => Account::OTHER_EXPENSE,
                'category_id'  => null,
            ])->id,
            "quantity" => 1,
        ]);
        $debitNote->addLineItem($lineItem);

        $debitNote->post();

        /*
         | ------------------------------
         | Current Asset Transaction
         | ------------------------------
         */

        $bill = new SupplierBill([
            "account_id" => factory(Account::class)->create([
                'account_type' => Account::PAYABLE,
                'category_id'  => null,
            ])->id,
            "date"      => Carbon::now(),
            "narration" => $this->faker->word,
        ]);

        $lineItem = factory(LineItem::class)->create([
            "amount"     => 100,
            "account_id" => factory(Account::class)->create([
                "account_type" => Account::INVENTORY,
                'category_id'  => null,
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
         | Current Liability Transaction
         | ------------------------------
         */

        $journalEntry = new JournalEntry([
            "account_id" => factory(Account::class)->create([
                'account_type' => Account::CURRENT_LIABILITY,
                'category_id'  => null,
            ])->id,
            "date"        => Carbon::now(),
            "narration"   => $this->faker->word,
            'currency_id' => $bank->currency_id,
        ]);

        $lineItem = factory(LineItem::class)->create([
            "amount"     => 100,
            "account_id" => $bank->id,
            "quantity"   => 1,
        ]);

        $journalEntry->addLineItem($lineItem);
        $journalEntry->post();

        /*
         | ------------------------------
         | Non Current Asset Transaction
         | ------------------------------
         */

        $journalEntry = new JournalEntry([
            "account_id"  => $bank->id,
            "date"        => Carbon::now(),
            "narration"   => $this->faker->word,
            'currency_id' => $bank->currency_id,
        ]);

        $lineItem = factory(LineItem::class)->create([
            "amount"     => 150,
            "account_id" => factory(Account::class)->create([
                "account_type" => Account::NON_CURRENT_ASSET,
                'category_id'  => null,
            ])->id,
            "quantity" => 1,
        ]);

        $journalEntry->addLineItem($lineItem);
        $journalEntry->post();

        /*
         | ------------------------------
         | Non Current Liability Transaction
         | ------------------------------
         */

        $journalEntry = new JournalEntry([
            "account_id" => factory(Account::class)->create([
                'account_type' => Account::NON_CURRENT_LIABILITY,
                'category_id'  => null,
            ])->id,
            "date"        => Carbon::now(),
            "narration"   => $this->faker->word,
            'currency_id' => $bank->currency_id,
        ]);

        $lineItem = factory(LineItem::class)->create([
            "amount"     => 100,
            "account_id" => $bank->id,
            "quantity"   => 1,
        ]);

        $journalEntry->addLineItem($lineItem);
        $journalEntry->post();

        /*
         | ------------------------------
         | Equity Transaction
         | ------------------------------
         */

        $journalEntry = new JournalEntry([
            "account_id" => factory(Account::class)->create([
                'account_type' => Account::EQUITY,
                'category_id'  => null,
            ])->id,
            "currency_id" => $bank->id,
            "date"        => Carbon::now(),
            "narration"   => $this->faker->word,
        ]);

        $lineItem = factory(LineItem::class)->create([
            "amount"     => 100,
            "account_id" => $bank->id,
            "quantity"   => 1,
        ]);

        $journalEntry->addLineItem($lineItem);
        $journalEntry->post();

        $startDate = ReportingPeriod::periodStart();
        $endDate   = ReportingPeriod::periodEnd();

        $sections = $cashFlowStatement->getSections($startDate, $endDate);
        $cashFlowStatement->toString();

        $provisions            = CashFlowStatement::PROVISIONS;
        $receivables           = CashFlowStatement::RECEIVABLES;
        $payables              = CashFlowStatement::PAYABLES;
        $taxation              = CashFlowStatement::TAXATION;
        $currentAssets         = CashFlowStatement::CURRENT_ASSETS;
        $currentLiabilities    = CashFlowStatement::CURRENT_LIABILITIES;
        $nonCurrentAssets      = CashFlowStatement::NON_CURRENT_ASSETS;
        $nonCurrentLiabilities = CashFlowStatement::NON_CURRENT_LIABILITIES;
        $equity                = CashFlowStatement::EQUITY;
        $profit                = CashFlowStatement::PROFIT;
        $startCashBalance      = CashFlowStatement::START_CASH_BALANCE;

        $endCashBalance     = CashFlowStatement::END_CASH_BALANCE;
        $cashbookBalance    = CashFlowStatement::CASHBOOK_BALANCE;
        $operationsCashFlow = CashFlowStatement::OPERATIONS_CASH_FLOW;
        $investmentCashFlow = CashFlowStatement::INVESTMENT_CASH_FLOW;
        $financingCashFlow  = CashFlowStatement::FINANCING_CASH_FLOW;
        $netCashFlow        = CashFlowStatement::NET_CASH_FLOW;

        $this->assertEquals(
            $sections,
            [
                "balances" => $cashFlowStatement->balances,
                "results"  => $cashFlowStatement->results,
            ],
        );


        // Statement balances
        $this->assertEquals(
            $cashFlowStatement->balances[$provisions],
            50,
        );
        $this->assertEquals(
            $cashFlowStatement->balances[$receivables],
            -530,
        );
        $this->assertEquals(
            $cashFlowStatement->balances[$payables],
            298,
        );
        $this->assertEquals(
            $cashFlowStatement->balances[$taxation],
            112,
        );
        $this->assertEquals(
            $cashFlowStatement->balances[$currentAssets],
            -100,
        );
        $this->assertEquals(
            $cashFlowStatement->balances[$currentLiabilities],
            100,
        );
        $this->assertEquals(
            $cashFlowStatement->balances[$nonCurrentAssets],
            -150,
        );
        $this->assertEquals(
            $cashFlowStatement->balances[$nonCurrentLiabilities],
            100,
        );
        $this->assertEquals(
            $cashFlowStatement->balances[$equity],
            100,
        );
        $this->assertEquals(
            $cashFlowStatement->balances[$profit],
            700,
        );
        $this->assertEquals(
            $cashFlowStatement->balances[$startCashBalance],
            100,
        );
        $this->assertEquals(
            $cashFlowStatement->balances[$netCashFlow],
            680,
        );

        // Statement Results
        $this->assertEquals(
            $cashFlowStatement->results[$endCashBalance],
            780,
        );
        $this->assertEquals(
            $cashFlowStatement->results[$cashbookBalance],
            780,
        );
        $this->assertEquals(
            $cashFlowStatement->results[$operationsCashFlow],
            630,
        );
        $this->assertEquals(
            $cashFlowStatement->results[$investmentCashFlow],
            -150,
        );
        $this->assertEquals(
            $cashFlowStatement->results[$financingCashFlow],
            200,
        );
    }


    /**
     * Test Cash Flow Statement
     *
     * @return void
     */
    public function testCashFlowStatementLoggedOut()
    {
        $entity = Auth::user()->entity;
        Auth::logout();

        $lastMonth         = Carbon::now()->subMonths(1);
        $endDate           = date("m") > 1 ? $lastMonth->toDateString() : date("Y-m") . "-01";
        $cashFlowStatement = new CashFlowStatement($endDate, null, $entity);
        $cashFlowStatement->attributes();

        $bank = Account::create([
            'name'         => 'Bank Account',
            'account_type' => Account::BANK,
            'category_id'  => null ,
            'entity_id'    => $entity->id,
        ]);

        Balance::create([
            'account_id'       => $bank->id,
            'balance_type'     => Balance::DEBIT,
            'exchange_rate_id' => ExchangeRate::create([
                'valid_from'  => $this->faker->dateTimeThisMonth(),
                'valid_to'    => Carbon::now(),
                'currency_id' => Currency::create([
                    'name'          => $this->faker->name,
                    'currency_code' => $this->faker->currencyCode,
                    'entity_id'     => $entity->id,
                ])->id,
                'rate'      => 1,
                'entity_id' => $entity->id,
            ])->id,
            'reporting_period_id' => $this->period->id,
            'transaction_date'    => Carbon::now()->subYears(1.5),
            'transaction_no'      => $this->faker->word,
            'transaction_type'    => $this->faker->randomElement([
                Transaction::IN,
                Transaction::BL,
                Transaction::JN,
            ]),
            'reference'   => $this->faker->word,
            'currency_id' => $bank->currency_id,
            'balance'     => 100,
            'entity_id'   => $entity->id,
        ]);


        /*
         | ------------------------------
         | Operating Revenue Transactions
         | ------------------------------
         */

        $clientInvoice = new ClientInvoice([
            "account_id" => Account::create([
                'account_type' => Account::RECEIVABLE,
                'category_id'  => null ,
                'entity_id'    => $entity->id,
            ])->id,
            "date"      => Carbon::now(),
            "narration" => $this->faker->word,
            "entity_id" => $entity->id,
        ]);

        $lineItem = LineItem::create([
            'amount'     => 500,
            "account_id" => Account::create([
                'account_type' => Account::OPERATING_REVENUE,
                'category_id'  => null ,
                'entity_id'    => $entity->id,
            ])->id,
            "quantity"  => 1,
            "entity_id" => $entity->id,
        ]);

        $vat = Vat::create([
            'name'       => 'Test vat',
            'code'       => 'T',
            'entity_id'  => $entity->id,
            'rate'       => 16,
            'account_id' => Account::create([
                'account_type' => Account::CONTROL,
                'category_id'  => null,
                'entity_id'    => $entity->id,
            ])->id,
        ]);
        $lineItem->addVat($vat);
        $lineItem->save();

        $clientInvoice->addLineItem($lineItem);

        $clientInvoice->post();

        $creditNote = new CreditNote([
            "account_id" => Account::create([
                'account_type' => Account::RECEIVABLE,
                'category_id'  => null ,
                'entity_id'    => $entity->id,
            ])->id,
            "date"      => Carbon::now(),
            "narration" => $this->faker->word,
            "entity_id" => $entity->id,
        ]);

        $lineItem = LineItem::create([
            'amount'     => 50,
            "account_id" => Account::create([
                'account_type' => Account::OPERATING_REVENUE,
                'category_id'  => null ,
                'entity_id'    => $entity->id,
            ])->id,
            "quantity"  => 1,
            "entity_id" => $entity->id,
        ]);

        $creditNote->addLineItem($lineItem);

        $creditNote->post();

        /*
         | ------------------------------
         | Non Operating Revenue Transactions
         | ------------------------------
         */

        $journalEntry = new JournalEntry([
            "account_id"  => $bank->id,
            "date"        => Carbon::now(),
            "narration"   => $this->faker->word,
            "credited"    => false,
            'currency_id' => $bank->currency_id,
            "entity_id"   => $entity->id,
        ]);

        $lineItem = LineItem::create([
            'amount'     => 500,
            "account_id" => Account::create([
                'account_type' => Account::NON_OPERATING_REVENUE,
                'category_id'  => null ,
                'entity_id'    => $entity->id,
            ])->id,
            "quantity"  => 1,
            "entity_id" => $entity->id,
        ]);
        $vat2 = Vat::create([
            'name'       => 'Test vat',
            'code'       => 'T',
            'entity_id'  => $entity->id,
            'rate'       => 16,
            'account_id' => Account::create([
                'account_type' => Account::CONTROL,
                'category_id'  => null,
                'entity_id'    => $entity->id,
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
                'category_id'  => null,
                'entity_id'    => $entity->id,
            ])->id,
            "date"      => Carbon::now(),
            "narration" => $this->faker->word,
            "entity_id" => $entity->id,
        ]);

        $lineItem = LineItem::create([
            'amount'     => 100,
            "account_id" => Account::create([
                'account_type' => Account::OPERATING_EXPENSE,
                'category_id'  => null ,
                'entity_id'    => $entity->id,
            ])->id,
            "quantity"  => 1,
            "entity_id" => $entity->id,
        ]);
        $vat3 = Vat::create([
            'name'       => 'Test vat',
            'code'       => 'T',
            'entity_id'  => $entity->id,
            'rate'       => 16,
            'account_id' => Account::create([
                'account_type' => Account::CONTROL,
                'category_id'  => null,
                'entity_id'    => $entity->id,
            ])->id,
        ]);
        $lineItem->addVat($vat3);
        $lineItem->save();

        $bill->addLineItem($lineItem);
        $bill->post();

        /*
         | ------------------------------
         | Non Operating Expense Transactions
         | ------------------------------
         */
        $bill = new SupplierBill([
            "account_id" => Account::create([
                'account_type' => Account::PAYABLE,
                'category_id'  => null,
                'entity_id'    => $entity->id,
            ])->id,
            "date"      => Carbon::now(),
            "narration" => $this->faker->word,
            "entity_id" => $entity->id,
        ]);

        $lineItem = LineItem::create([
            'amount'     => 100,
            "account_id" => Account::create([
                'account_type' => Account::DIRECT_EXPENSE,
                'category_id'  => null ,
                'entity_id'    => $entity->id,
            ])->id,
            "quantity"  => 1,
            "entity_id" => $entity->id,
        ]);
        $vat4 = Vat::create([
            'name'       => 'Test vat',
            'code'       => 'T',
            'entity_id'  => $entity->id,
            'rate'       => 16,
            'account_id' => Account::create([
                'account_type' => Account::CONTROL,
                'category_id'  => null,
                'entity_id'    => $entity->id,
            ])->id,
        ]);
        $lineItem->addVat($vat4);
        $lineItem->save();

        $bill->addLineItem($lineItem);
        $bill->post();

        // Depreciation
        $journalEntry = new JournalEntry([
            "account_id" => Account::create([
                'account_type' => Account::CONTRA_ASSET,
                'category_id'  => null ,
                'entity_id'    => $entity->id,
            ])->id,
            "date"      => Carbon::now(),
            "narration" => $this->faker->word,
            "entity_id" => $entity->id,
        ]);

        $lineItem = LineItem::create([
            'amount'     => 50,
            "account_id" => Account::create([
                'account_type' => Account::OTHER_EXPENSE,
                'category_id'  => null ,
                'entity_id'    => $entity->id,
            ])->id,
            "quantity"  => 1,
            "entity_id" => $entity->id,
        ]);

        $journalEntry->addLineItem($lineItem);
        $journalEntry->post();

        $cashPurchase = new CashPurchase([
            "account_id"  => $bank->id,
            "date"        => Carbon::now(),
            "narration"   => $this->faker->word,
            'currency_id' => $bank->currency_id,
            "entity_id"   => $entity->id,
        ]);

        $lineItem = LineItem::create([
            'amount'     => 50,
            "account_id" => Account::create([
                'account_type' => Account::OTHER_EXPENSE,
                'category_id'  => null ,
                'entity_id'    => $entity->id,
            ])->id,
            "quantity"  => 1,
            "entity_id" => $entity->id,
        ]);

        $cashPurchase->addLineItem($lineItem);
        $cashPurchase->post();

        $debitNote = new DebitNote([
            "account_id" => Account::create([
                'account_type' => Account::PAYABLE,
                'category_id'  => null ,
                'entity_id'    => $entity->id,
            ])->id,
            "date"      => Carbon::now(),
            "narration" => $this->faker->word,
            "entity_id" => $entity->id,
        ]);

        $lineItem = LineItem::create([
            'amount'     => 50,
            "account_id" => Account::create([
                'account_type' => Account::OTHER_EXPENSE,
                'category_id'  => null ,
                'entity_id'    => $entity->id,
            ])->id,
            "quantity"  => 1,
            "entity_id" => $entity->id,
        ]);

        $debitNote->addLineItem($lineItem);

        $debitNote->post();

        /*
         | ------------------------------
         | Current Asset Transaction
         | ------------------------------
         */

        $bill = new SupplierBill([
            "account_id" => Account::create([
                'account_type' => Account::PAYABLE,
                'category_id'  => null ,
                'entity_id'    => $entity->id,
            ])->id,
            "date"      => Carbon::now(),
            "narration" => $this->faker->word,
            "entity_id" => $entity->id,
        ]);

        $lineItem = LineItem::create([
            'amount'     => 100,
            "account_id" => Account::create([
                'account_type' => Account::INVENTORY,
                'category_id'  => null ,
                'entity_id'    => $entity->id,
            ])->id,
            "quantity"  => 1,
            "entity_id" => $entity->id,
        ]);
        $vat5 = Vat::create([
            'name'       => 'Test vat',
            'code'       => 'T',
            'entity_id'  => $entity->id,
            'rate'       => 16,
            'account_id' => Account::create([
                'account_type' => Account::CONTROL,
                'category_id'  => null,
                'entity_id'    => $entity->id,
            ])->id,
        ]);
        $lineItem->addVat($vat5);
        $lineItem->save();

        $bill->addLineItem($lineItem);
        $bill->post();

        /*
         | ------------------------------
         | Current Liability Transaction
         | ------------------------------
         */

        $journalEntry = new JournalEntry([
            "account_id" => Account::create([
                'account_type' => Account::CURRENT_LIABILITY,
                'category_id'  => null ,
                'entity_id'    => $entity->id,
            ])->id,
            "date"        => Carbon::now(),
            "narration"   => $this->faker->word,
            'currency_id' => $bank->currency_id,
            "entity_id"   => $entity->id,
        ]);

        $lineItem = LineItem::create([
            'amount'     => 100,
            "account_id" => $bank->id,
            "quantity"   => 1,
            "entity_id"  => $entity->id,
        ]);

        $journalEntry->addLineItem($lineItem);
        $journalEntry->post();

        /*
         | ------------------------------
         | Non Current Asset Transaction
         | ------------------------------
         */

        $journalEntry = new JournalEntry([
            "account_id"  => $bank->id,
            "date"        => Carbon::now(),
            "narration"   => $this->faker->word,
            'currency_id' => $bank->currency_id,
            "entity_id"   => $entity->id,
        ]);

        $lineItem = LineItem::create([
            'amount'     => 150,
            "account_id" => Account::create([
                'account_type' => Account::NON_CURRENT_ASSET,
                'category_id'  => null,
                'entity_id'    => $entity->id,
            ])->id,
            "quantity"  => 1,
            "entity_id" => $entity->id,
        ]);

        $journalEntry->addLineItem($lineItem);
        $journalEntry->post();

        /*
         | ------------------------------
         | Non Current Liability Transaction
         | ------------------------------
         */

        $journalEntry = new JournalEntry([
            "account_id" => Account::create([
                'account_type' => Account::NON_CURRENT_LIABILITY,
                'category_id'  => null,
                'entity_id'    => $entity->id,
            ])->id,
            "date"        => Carbon::now(),
            "narration"   => $this->faker->word,
            'currency_id' => $bank->currency_id,
            "entity_id"   => $entity->id,
        ]);

        $lineItem = LineItem::create([
            'amount'     => 100,
            "account_id" => $bank->id,
            "quantity"   => 1,
            "entity_id"  => $entity->id,
        ]);

        $journalEntry->addLineItem($lineItem);
        $journalEntry->post();

        /*
         | ------------------------------
         | Equity Transaction
         | ------------------------------
         */

        $journalEntry = new JournalEntry([
            "account_id" => Account::create([
                'account_type' => Account::EQUITY,
                'category_id'  => null,
                'entity_id'    => $entity->id,
            ])->id,
            "currency_id" => $bank->currency_id,
            "date"        => Carbon::now(),
            "narration"   => $this->faker->word,
            "entity_id"   => $entity->id,
        ]);

        $lineItem = LineItem::create([
            'amount'     => 100,
            "account_id" => $bank->id,
            "quantity"   => 1,
            "entity_id"  => $entity->id,
        ]);

        $journalEntry->addLineItem($lineItem);
        $journalEntry->post();

        $startDate = ReportingPeriod::periodStart(null, $entity);
        $endDate   = ReportingPeriod::periodEnd(null, $entity);

        $sections = $cashFlowStatement->getSections($startDate, $endDate);
        $cashFlowStatement->toString();

        $provisions            = CashFlowStatement::PROVISIONS;
        $receivables           = CashFlowStatement::RECEIVABLES;
        $payables              = CashFlowStatement::PAYABLES;
        $taxation              = CashFlowStatement::TAXATION;
        $currentAssets         = CashFlowStatement::CURRENT_ASSETS;
        $currentLiabilities    = CashFlowStatement::CURRENT_LIABILITIES;
        $nonCurrentAssets      = CashFlowStatement::NON_CURRENT_ASSETS;
        $nonCurrentLiabilities = CashFlowStatement::NON_CURRENT_LIABILITIES;
        $equity                = CashFlowStatement::EQUITY;
        $profit                = CashFlowStatement::PROFIT;
        $startCashBalance      = CashFlowStatement::START_CASH_BALANCE;

        $endCashBalance     = CashFlowStatement::END_CASH_BALANCE;
        $cashbookBalance    = CashFlowStatement::CASHBOOK_BALANCE;
        $operationsCashFlow = CashFlowStatement::OPERATIONS_CASH_FLOW;
        $investmentCashFlow = CashFlowStatement::INVESTMENT_CASH_FLOW;
        $financingCashFlow  = CashFlowStatement::FINANCING_CASH_FLOW;
        $netCashFlow        = CashFlowStatement::NET_CASH_FLOW;

        $this->assertEquals(
            $sections,
            [
                "balances" => $cashFlowStatement->balances,
                "results"  => $cashFlowStatement->results,
            ],
        );


        // Statement balances
        $this->assertEquals(
            $cashFlowStatement->balances[$provisions],
            50,
        );
        $this->assertEquals(
            $cashFlowStatement->balances[$receivables],
            -530,
        );
        $this->assertEquals(
            $cashFlowStatement->balances[$payables],
            298,
        );
        $this->assertEquals(
            $cashFlowStatement->balances[$taxation],
            112,
        );
        $this->assertEquals(
            $cashFlowStatement->balances[$currentAssets],
            -100,
        );
        $this->assertEquals(
            $cashFlowStatement->balances[$currentLiabilities],
            100,
        );
        $this->assertEquals(
            $cashFlowStatement->balances[$nonCurrentAssets],
            -150,
        );
        $this->assertEquals(
            $cashFlowStatement->balances[$nonCurrentLiabilities],
            100,
        );
        $this->assertEquals(
            $cashFlowStatement->balances[$equity],
            100,
        );
        $this->assertEquals(
            $cashFlowStatement->balances[$profit],
            700,
        );
        $this->assertEquals(
            $cashFlowStatement->balances[$startCashBalance],
            100,
        );
        $this->assertEquals(
            $cashFlowStatement->balances[$netCashFlow],
            680,
        );

        // Statement Results
        $this->assertEquals(
            $cashFlowStatement->results[$endCashBalance],
            780,
        );
        $this->assertEquals(
            $cashFlowStatement->results[$cashbookBalance],
            780,
        );
        $this->assertEquals(
            $cashFlowStatement->results[$operationsCashFlow],
            630,
        );
        $this->assertEquals(
            $cashFlowStatement->results[$investmentCashFlow],
            -150,
        );
        $this->assertEquals(
            $cashFlowStatement->results[$financingCashFlow],
            200,
        );
    }
}
