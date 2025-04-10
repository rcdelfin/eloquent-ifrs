<?php

/**
 * Eloquent IFRS Accounting
 *
 * @author Edward Mungai
 * @copyright Edward Mungai, 2020, Germany
 * @license MIT
 */
use IFRS\Models\Transaction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIfrsTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('ifrs.table_prefix') . 'transactions', function (Blueprint $table) {
            $table->bigIncrements('id');

            // relationships
            $table->unsignedBigInteger('entity_id');
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('currency_id');
            $table->unsignedBigInteger('exchange_rate_id');

            // constraints
            $table->foreign('entity_id')->references('id')->on(config('ifrs.table_prefix') . 'entities');
            $table->foreign('currency_id')->references('id')->on(config('ifrs.table_prefix') . 'currencies');
            $table->foreign('exchange_rate_id')->references('id')->on(config('ifrs.table_prefix') . 'exchange_rates');
            $table->foreign('account_id')->references('id')->on(config('ifrs.table_prefix') . 'accounts');

            // attributes
            $table->dateTime('transaction_date', 0);
            $table->string('reference', 255)->nullable();
            $table->string('transaction_no', 255);
            $table->enum('transaction_type', [
                Transaction::CS,
                Transaction::IN,
                Transaction::CN,
                Transaction::RC,
                Transaction::CP,
                Transaction::BL,
                Transaction::DN,
                Transaction::PY,
                Transaction::CE,
                Transaction::JN,
            ]);
            $table->string('narration', 1000);
            $table->boolean('credited')->default(true);

            // *permanent* deletion
            $table->dateTime('destroyed_at')->nullable();

            //soft deletion
            $table->softDeletes();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('ifrs.table_prefix') . 'transactions');
    }
}
