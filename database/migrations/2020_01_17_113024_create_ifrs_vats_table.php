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

class CreateIfrsVatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            config('ifrs.table_prefix') . 'vats',
            function (Blueprint $table) {
                $table->bigIncrements('id');

                // relationships
                $table->unsignedBigInteger('entity_id');
                $table->unsignedBigInteger('account_id')->nullable();

                // constraints
                $table->foreign('entity_id')->references('id')->on(config('ifrs.table_prefix') . 'entities');
                $table->foreign('account_id')->references('id')->on(config('ifrs.table_prefix') . 'accounts');

                // attributes
                $table->string('code', 1);
                $table->string('name', 300);
                $table->decimal('rate', 13, 4);

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
        Schema::dropIfExists(config('ifrs.table_prefix') . 'vats');
    }
}
