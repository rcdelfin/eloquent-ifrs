<?php

/**
 * Eloquent IFRS Accounting
 *
 * @author Edward Mungai
 * @copyright Edward Mungai, 2020, Germany
 * @license MIT
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIfrsExchangeRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            config('ifrs.table_prefix') . 'exchange_rates',
            function (Blueprint $table) {
                $table->bigIncrements('id');

                // relationships
                $table->unsignedBigInteger('entity_id');
                $table->unsignedBigInteger('currency_id');

                // constraints
                $table->foreign('entity_id')->references('id')->on(config('ifrs.table_prefix') . 'entities');
                $table->foreign('currency_id')->references('id')->on(config('ifrs.table_prefix') . 'currencies');

                // attributes
                $table->dateTime('valid_from', 0);
                $table->dateTime('valid_to', 0)->nullable();
                $table->decimal('rate', 13, 4)->default(1);

                // *permanent* deletion
                $table->dateTime('destroyed_at')->nullable();

                //soft deletion
                $table->softDeletes();

                $table->timestamps();
            },
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('ifrs.table_prefix') . 'exchange_rates');
    }
}
