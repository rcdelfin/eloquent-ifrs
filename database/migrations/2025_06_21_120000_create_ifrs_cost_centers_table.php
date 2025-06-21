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

class CreateIfrsCostCentersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            config('ifrs.table_prefix') . 'cost_centers',
            function (Blueprint $table) {
                $table->bigIncrements('id');

                // relationships
                $table->unsignedBigInteger('entity_id');

                // constraints
                $table->foreign('entity_id')->references('id')->on(config('ifrs.table_prefix') . 'entities');

                // attributes
                $table->string('code', 20)->unique();
                $table->string('name', 255);
                $table->text('description')->nullable();
                $table->boolean('active')->default(true);

                // *permanent* deletion
                $table->dateTime('destroyed_at')->nullable();

                //soft deletion
                $table->softDeletes();

                $table->timestamps();

                // indexes
                $table->index(['entity_id', 'active']);
                $table->index(['entity_id', 'code']);
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
        Schema::dropIfExists(config('ifrs.table_prefix') . 'cost_centers');
    }
}
