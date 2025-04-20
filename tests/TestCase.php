<?php

namespace IFRS\Tests;

use Faker\Factory as Faker;
use IFRS\IFRSServiceProvider;
use IFRS\Models\Currency;
use IFRS\Models\ReportingPeriod;
use IFRS\User;
use Illuminate\Support\Facades\Config;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    public function setUp(): void
    {

        parent::setUp();

        Config::set('ifrs.user_model', User::class);

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->faker = Faker::create();

        $user = factory(User::class)->create();
        $this->be($user);

        $currency = factory(Currency::class)->create();

        $entity = $user->entity;
        $entity->currency_id = $currency->id;
        $entity->save();

        $this->reportingCurrencyId = $currency->id;

        $this->period = factory(ReportingPeriod::class)->create([
            "calendar_year" => date("Y"),
            "entity_id" => $user->entity->id,
        ]);
    }

    /**
     * Add the package provider
     *
     * @param  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [IFRSServiceProvider::class];
    }
}
