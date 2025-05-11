<?php

namespace Tests\Unit;

use Carbon\Carbon;
use IFRS\Exceptions\InvalidCurrency;
use IFRS\Exceptions\MissingAccountType;
use IFRS\Models\Account;
use IFRS\Models\RecycledObject;
use IFRS\Models\TransactionSchedule;
use IFRS\Models\TransactionScheduleItem;
use IFRS\Tests\TestCase;

class TransactionScheduleTest extends TestCase
{
    private $entity;

    public function setUp(): void
    {
        parent::setUp();

        // Create a test entity
        $this->entity = auth()->user()->entity->refresh('currency');
    }

    /**
     * Test TransactionSchedule Model relationships.
     *
     * @return void
     */
    public function testTransactionScheduleRelationships()
    {
        $entity = $this->entity;
        $currency = $entity->currency;

        $revenueAccount = Account::create([
            "name" => "Test Revenue Account",
            "account_type" => Account::OPERATING_REVENUE,
            "currency_id" => $currency->id,
            "entity_id" => $entity->id,
        ]);

        $receivableAccount = Account::create([
            "name" => "Test Student Receivable",
            "account_type" => Account::RECEIVABLE,
            "currency_id" => $currency->id,
            "entity_id" => $entity->id,
        ]);

        // Create schedule
        $schedule = TransactionSchedule::create([
            "name" => "Monthly Tuition",
            "description" => "Regular monthly tuition payment",
            "frequency" => TransactionSchedule::MONTHLY,
            "start_date" => Carbon::now(),
            "end_date" => Carbon::now()->addYear(),
            "is_active" => true,
            "entity_id" => $entity->id,
        ]);

        // Create schedule item
        $scheduleItem = TransactionScheduleItem::create([
            "schedule_id" => $schedule->id,
            "account_id" => $revenueAccount->id,
            "receivable_account_id" => $receivableAccount->id,
            "currency_id" => $currency->id,
            "amount" => 500,
        ]);

        $this->assertEquals($scheduleItem->account->name, $revenueAccount->name);
        $this->assertEquals($scheduleItem->receivableAccount->name, $receivableAccount->name);
        $this->assertEquals($scheduleItem->currency->name, $currency->name);
        $this->assertEquals($schedule->type, "MONTHLY");
        $this->assertEquals(1, $schedule->items()->count());
    }

    /**
     * Test TransactionSchedule date generation
     *
     * @return void
     */
    public function testTransactionScheduleDateGeneration()
    {
        $entity = $this->entity;
        $currency = $entity->currency;

        $revenueAccount = Account::create([
            "name" => "Test Revenue Account",
            "account_type" => Account::OPERATING_REVENUE,
            "currency_id" => $currency->id,
            "entity_id" => $entity->id,
        ]);

        $receivableAccount = Account::create([
            "name" => "Test Student Receivable",
            "account_type" => Account::RECEIVABLE,
            "currency_id" => $currency->id,
            "entity_id" => $entity->id,
        ]);

        // Create a monthly schedule
        $startDate = Carbon::create(2025, 1, 1); // January 1, 2025
        $endDate = Carbon::create(2025, 6, 30); // June 30, 2025

        $schedule = TransactionSchedule::create([
            "name" => "Monthly Tuition",
            "description" => "Regular monthly tuition payment",
            "frequency" => TransactionSchedule::MONTHLY,
            "start_date" => $startDate,
            "end_date" => $endDate,
            "is_active" => true,
            "entity_id" => $entity->id,
        ]);

        // Create schedule item
        TransactionScheduleItem::create([
            "schedule_id" => $schedule->id,
            "account_id" => $revenueAccount->id,
            "receivable_account_id" => $receivableAccount->id,
            "amount" => 500,
        ]);

        // Get the scheduled dates
        $dates = $schedule->generateScheduledDates();

        // Should be 6 entries (Jan to Jun)
        $this->assertCount(6, $dates);

        // Check first and last dates
        $this->assertEquals("2025-01-01", $dates[0]->format("Y-m-d"));
        $this->assertEquals("2025-06-01", $dates[5]->format("Y-m-d"));

        // Create a quarterly schedule
        $schedule->frequency = TransactionSchedule::QUARTERLY;
        $schedule->save();

        $dates = $schedule->generateScheduledDates();

        // Should be 2 entries (Jan and Apr)
        $this->assertCount(2, $dates);
        $this->assertEquals("2025-01-01", $dates[0]->format("Y-m-d"));
        $this->assertEquals("2025-04-01", $dates[1]->format("Y-m-d"));
    }

    /**
     * Test TransactionSchedule template generation
     *
     * @return void
     */
    public function testTransactionScheduleTemplateGeneration()
    {
        $entity = $this->entity;
        $currency = $entity->currency;

        $revenueAccount = Account::create([
            "name" => "Test Revenue Account",
            "account_type" => Account::OPERATING_REVENUE,
            "currency_id" => $currency->id,
            "entity_id" => $entity->id,
        ]);

        $receivableAccount = Account::create([
            "name" => "Test Student Receivable",
            "account_type" => Account::RECEIVABLE,
            "currency_id" => $currency->id,
            "entity_id" => $entity->id,
        ]);

        // Create a monthly schedule
        $startDate = Carbon::create(2025, 1, 1); // January 1, 2025
        $endDate = Carbon::create(2025, 12, 31); // December 31, 2025

        $schedule = TransactionSchedule::create([
            "name" => "Monthly Tuition",
            "description" => "Regular monthly tuition payment",
            "frequency" => TransactionSchedule::MONTHLY,
            "start_date" => $startDate,
            "end_date" => $endDate,
            "is_active" => true,
            "entity_id" => $entity->id,
        ]);

        // Create schedule item
        TransactionScheduleItem::create([
            "schedule_id" => $schedule->id,
            "account_id" => $revenueAccount->id,
            "receivable_account_id" => $receivableAccount->id,
            "amount" => 500,
            "entity_id" => $entity->id,
        ]);

        // Get transaction templates for January to February
        $templateStartDate = Carbon::create(2025, 1, 1);
        $templateEndDate = Carbon::create(2025, 2, 28);

        $templates = $schedule->generateTransactionTemplates($templateStartDate, $templateEndDate);

        // Should be 2 entries (Jan and Feb)
        $this->assertCount(2, $templates);

        // Check the first template
        $this->assertEquals($receivableAccount->id, $templates[0]["account_id"]);
        $this->assertEquals(500, $templates[0]["amount"]);
        $this->assertEquals($templateStartDate->format("Y-m-d"), $templates[0]["transaction_date"]->format("Y-m-d"));

        // Check line items
        $this->assertCount(1, $templates[0]["line_items"]);
        $this->assertEquals($revenueAccount->id, $templates[0]["line_items"][0]["account_id"]);
        $this->assertEquals(500, $templates[0]["line_items"][0]["amount"]);
    }

    /**
     * Test TransactionSchedule Model recycling
     *
     * @return void
     */
    public function testTransactionScheduleRecycling()
    {
        $entity = $this->entity;
        $currency = $entity->currency;

        $revenueAccount = Account::create([
            "name" => "Test Revenue Account",
            "account_type" => Account::OPERATING_REVENUE,
            "currency_id" => $currency->id,
            "entity_id" => $entity->id,
        ]);

        $receivableAccount = Account::create([
            "name" => "Test Student Receivable",
            "account_type" => Account::RECEIVABLE,
            "currency_id" => $currency->id,
            "entity_id" => $entity->id,
        ]);

        $schedule = TransactionSchedule::create([
            "name" => "Monthly Tuition",
            "description" => "Regular monthly tuition payment",
            "frequency" => TransactionSchedule::MONTHLY,
            "start_date" => Carbon::now(),
            "end_date" => Carbon::now()->addYear(),
            "is_active" => true,
            "entity_id" => $entity->id,
        ]);

        // Create schedule item
        TransactionScheduleItem::create([
            "schedule_id" => $schedule->id,
            "account_id" => $revenueAccount->id,
            "receivable_account_id" => $receivableAccount->id,
            "currency_id" => $currency->id,
            "amount" => 500,
        ]);

        $scheduleId = $schedule->id;
        $scheduleName = $schedule->name;

        $this->assertEquals(0, RecycledObject::count());

        $schedule->delete();

        $this->assertEquals(1, RecycledObject::count());

        $recycled = RecycledObject::first();

        $this->assertEquals(TransactionSchedule::class, $recycled->recyclable_type);
        $this->assertEquals($scheduleId, $recycled->recyclable_id);

        $schedule->restore();

        $this->assertEquals(0, RecycledObject::count());
        $this->assertEquals($scheduleName, TransactionSchedule::find($scheduleId)->name);
    }

    /**
     * Test TransactionScheduleItem with nullable currency
     *
     * @return void
     */
    public function testTransactionScheduleItemWithNullableCurrency()
    {
        $entity = $this->entity;
        $currency = $entity->currency;

        $revenueAccount = Account::create([
            "name" => "Test Revenue Account",
            "account_type" => Account::OPERATING_REVENUE,
            "currency_id" => $currency->id,
            "entity_id" => $entity->id,
        ]);

        $receivableAccount = Account::create([
            "name" => "Test Student Receivable",
            "account_type" => Account::RECEIVABLE,
            "currency_id" => $currency->id,
            "entity_id" => $entity->id,
        ]);

        // Set start and end date to the same month to ensure only one template is generated
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $schedule = TransactionSchedule::create([
            "name" => "Monthly Tuition with Null Currency",
            "description" => "Testing nullable currency feature",
            "frequency" => TransactionSchedule::MONTHLY,
            "start_date" => $startOfMonth,
            "end_date" => $endOfMonth,
            "is_active" => true,
            "entity_id" => $entity->id,
        ]);

        // Create schedule item with null currency
        $scheduleItem = TransactionScheduleItem::create([
            "schedule_id" => $schedule->id,
            "account_id" => $revenueAccount->id,
            "receivable_account_id" => $receivableAccount->id,
            "currency_id" => null, // Testing null currency
            "amount" => 500,
        ]);

        // Verify the currency is null
        $this->assertNull($scheduleItem->currency_id);

        // When we generate templates, it should use the entity's default currency
        $templates = $schedule->generateTransactionTemplates(
            $startOfMonth,
            $endOfMonth,
        );

        // Should be 1 entry for the current month
        $this->assertCount(1, $templates);

        // Check the template's currency is set to entity's default currency
        $this->assertEquals($entity->currency_id, $templates[0]["currency_id"]);
    }

    /**
     * Test all frequency types of TransactionSchedule
     *
     * @return void
     */
    public function testAllTransactionScheduleFrequencies()
    {
        $entity = $this->entity;

        // Create a schedule with start date January 1, 2025
        $startDate = Carbon::create(2025, 1, 1);
        $endDate = Carbon::create(2025, 12, 31);

        // Test each frequency type
        $frequencyTests = [
            TransactionSchedule::DAILY => [
                'expected_count' => 365, // Full year 2025
                'expected_dates' => [
                    '2025-01-01', '2025-01-02', '2025-01-03', '2025-12-31',
                ],
            ],
            TransactionSchedule::WEEKLY => [
                'expected_count' => 53, // Full year = 53 weeks (Jan 1, 2025 to Dec 31, 2025)
                'expected_dates' => [
                    '2025-01-01', '2025-01-08', '2025-01-15', '2025-12-31',
                ],
            ],
            TransactionSchedule::BIWEEKLY => [
                'expected_count' => 27, // Full year = 27 biweekly periods (Jan 1, 2025 to Dec 31, 2025)
                'expected_dates' => [
                    '2025-01-01', '2025-01-15', '2025-12-31',
                ],
            ],
            TransactionSchedule::MONTHLY => [
                'expected_count' => 12, // Full year = 12 months
                'expected_dates' => [
                    '2025-01-01', '2025-02-01', '2025-03-01', '2025-12-01',
                ],
            ],
            TransactionSchedule::QUARTERLY => [
                'expected_count' => 4, // Full year = 4 quarters
                'expected_dates' => [
                    '2025-01-01', '2025-04-01', '2025-07-01', '2025-10-01',
                ],
            ],
            TransactionSchedule::SEMESTERLY => [
                'expected_count' => 2, // Full year = 2 semesters
                'expected_dates' => [
                    '2025-01-01', '2025-07-01',
                ],
            ],
            TransactionSchedule::ANNUALLY => [
                'expected_count' => 1, // One year = 1 annual payment
                'expected_dates' => [
                    '2025-01-01',
                ],
            ],
        ];

        foreach ($frequencyTests as $frequency => $test) {
            // Create schedule for this frequency
            $schedule = TransactionSchedule::create([
                "name" => "{$frequency} Schedule",
                "description" => "Testing {$frequency} frequency",
                "frequency" => $frequency,
                "start_date" => $startDate,
                "end_date" => $endDate,
                "is_active" => true,
                "entity_id" => $entity->id,
            ]);

            // Generate dates for the full year
            $dates = $schedule->generateScheduledDates();

            // Test count for full year
            $this->assertCount($test['expected_count'], $dates, "Failed count for {$frequency}");

            // Test specific dates (first, second, and last)
            $this->assertEquals($test['expected_dates'][0], $dates[0]->format('Y-m-d'), "Failed first date for {$frequency}");

            if (count($test['expected_dates']) > 1) {
                $this->assertEquals($test['expected_dates'][1], $dates[1]->format('Y-m-d'), "Failed second date for {$frequency}");
            }

            // Test last date
            $lastDateIndex = count($test['expected_dates']) - 1;
            $lastGeneratedIndex = count($dates) - 1;
            $this->assertEquals($test['expected_dates'][$lastDateIndex], $dates[$lastGeneratedIndex]->format('Y-m-d'), "Failed last date for {$frequency}");

            // Test limited generation (only 2 dates)
            $limitedDates = $schedule->generateScheduledDates(null, 2);
            $this->assertCount(min($test['expected_count'], 2), $limitedDates, "Failed limited count for {$frequency}");

            // Test with a different start date
            $midYearStart = Carbon::create(2025, 6, 1);
            $midYearDates = $schedule->generateScheduledDates($midYearStart);

            // First date should be the specified start date
            $this->assertEquals('2025-06-01', $midYearDates[0]->format('Y-m-d'), "Failed mid-year date for {$frequency}");

            // Clean up
            $schedule->delete();
        }
    }

    /**
     * Test TransactionScheduleItem validation and errors
     *
     * @return void
     */
    public function testTransactionScheduleItemValidation()
    {
        $entity = $this->entity;
        $currency = $entity->currency;

        // Create accounts
        $revenueAccount = Account::create([
            "name" => "Test Revenue Account",
            "account_type" => Account::OPERATING_REVENUE,
            "currency_id" => $currency->id,
            "entity_id" => $entity->id,
        ]);

        $receivableAccount = Account::create([
            "name" => "Test Student Receivable",
            "account_type" => Account::RECEIVABLE,
            "currency_id" => $currency->id,
            "entity_id" => $entity->id,
        ]);

        $assetAccount = Account::create([
            "name" => "Test Asset Account",
            "account_type" => Account::NON_CURRENT_ASSET,
            "currency_id" => $currency->id,
            "entity_id" => $entity->id,
        ]);

        // Create a second currency
        $secondCurrency = $this->createCurrency();

        // Create an account with the second currency
        $secondCurrencyAccount = Account::create([
            "name" => "Second Currency Account",
            "account_type" => Account::OPERATING_REVENUE,
            "currency_id" => $secondCurrency->id,
            "entity_id" => $entity->id,
        ]);

        // Create schedule
        $schedule = TransactionSchedule::create([
            "name" => "Test Schedule for Validation",
            "description" => "Testing validation scenarios",
            "frequency" => TransactionSchedule::MONTHLY,
            "start_date" => Carbon::now(),
            "end_date" => Carbon::now()->addYear(),
            "is_active" => true,
            "entity_id" => $entity->id,
        ]);

        // Test 1: Non-receivable account as receivable should fail
        $this->expectException(MissingAccountType::class);
        TransactionScheduleItem::create([
            "schedule_id" => $schedule->id,
            "account_id" => $revenueAccount->id,
            "receivable_account_id" => $assetAccount->id, // Not a receivable account
            "currency_id" => $currency->id,
            "amount" => 500,
        ]);
    }

    /**
     * Test currency validation in TransactionScheduleItem
     *
     * @return void
     */
    public function testCurrencyValidation()
    {
        $entity = $this->entity;
        $currency = $entity->currency;

        // Create accounts
        $revenueAccount = Account::create([
            "name" => "Test Revenue Account",
            "account_type" => Account::OPERATING_REVENUE,
            "currency_id" => $currency->id,
            "entity_id" => $entity->id,
        ]);

        $receivableAccount = Account::create([
            "name" => "Test Student Receivable",
            "account_type" => Account::RECEIVABLE,
            "currency_id" => $currency->id,
            "entity_id" => $entity->id,
        ]);

        // Create a second currency
        $secondCurrency = $this->createCurrency();

        // Create schedule
        $schedule = TransactionSchedule::create([
            "name" => "Test Schedule for Currency Validation",
            "description" => "Testing currency validation",
            "frequency" => TransactionSchedule::MONTHLY,
            "start_date" => Carbon::now(),
            "end_date" => Carbon::now()->addYear(),
            "is_active" => true,
            "entity_id" => $entity->id,
        ]);

        // Configure single currency accounts for testing
        $singleCurrencyTypes = [Account::OPERATING_REVENUE];
        config(['ifrs.single_currency' => $singleCurrencyTypes]);

        // Test: Currency mismatch for single currency account should fail
        $this->expectException(InvalidCurrency::class);

        // Create a schedule item with mismatched currency
        TransactionScheduleItem::create([
            "schedule_id" => $schedule->id,
            "account_id" => $revenueAccount->id, // Operating revenue (single currency)
            "receivable_account_id" => $receivableAccount->id,
            "currency_id" => $secondCurrency->id, // Different from account's currency
            "amount" => 500,
        ]);
    }

    /**
     * Test template flag functionality
     *
     * @return void
     */
    public function testTransactionScheduleTemplateFlag()
    {
        $entity = $this->entity;
        $currency = $entity->currency;

        // Create accounts
        $revenueAccount = Account::create([
            "name" => "Test Revenue Account",
            "account_type" => Account::OPERATING_REVENUE,
            "currency_id" => $currency->id,
            "entity_id" => $entity->id,
        ]);

        $receivableAccount = Account::create([
            "name" => "Test Student Receivable",
            "account_type" => Account::RECEIVABLE,
            "currency_id" => $currency->id,
            "entity_id" => $entity->id,
        ]);

        // Create a template schedule
        $templateSchedule = TransactionSchedule::create([
            "name" => "Template Schedule",
            "description" => "This is a template schedule that can be reused",
            "frequency" => TransactionSchedule::MONTHLY,
            "start_date" => Carbon::now(),
            "end_date" => Carbon::now()->addYear(),
            "is_active" => false,
            "is_template" => true,
            "entity_id" => $entity->id,
        ]);

        // Create schedule item for template
        TransactionScheduleItem::create([
            "schedule_id" => $templateSchedule->id,
            "account_id" => $revenueAccount->id,
            "receivable_account_id" => $receivableAccount->id,
            "currency_id" => $currency->id,
            "amount" => 1000,
        ]);

        // Verify template was created with correct flags
        $this->assertTrue($templateSchedule->is_template);
        $this->assertFalse($templateSchedule->is_active);

        // Create a non-template schedule for comparison
        $regularSchedule = TransactionSchedule::create([
            "name" => "Regular Schedule",
            "description" => "This is a normal active schedule",
            "frequency" => TransactionSchedule::MONTHLY,
            "start_date" => Carbon::now(),
            "end_date" => Carbon::now()->addYear(),
            // Don't set is_active or is_template, rely on defaults
            "entity_id" => $entity->id,
        ]);

        // Create schedule item for regular schedule
        TransactionScheduleItem::create([
            "schedule_id" => $regularSchedule->id,
            "account_id" => $revenueAccount->id,
            "receivable_account_id" => $receivableAccount->id,
            "currency_id" => $currency->id,
            "amount" => 500,
        ]);

        // Verify default values were applied
        $this->assertTrue($regularSchedule->is_active);
        $this->assertFalse($regularSchedule->is_template);

        // Test that template schedules can still generate dates
        $templateDates = $templateSchedule->generateScheduledDates();
        $this->assertNotEmpty($templateDates);

        // Test toString method with type flag
        $this->assertEquals('Template Schedule', $templateSchedule->toString());
        $this->assertEquals('MONTHLY: Template Schedule', $templateSchedule->toString(true));
    }

    /**
     * Helper method to create a test currency
     */
    private function createCurrency()
    {
        // Create a new currency for testing
        return \IFRS\Models\Currency::create([
            'name' => 'Test Euro',
            'currency_code' => 'EUR',
            'symbol' => '€',
            'entity_id' => $this->entity->id,
        ]);
    }
}
