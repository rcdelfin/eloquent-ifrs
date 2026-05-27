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

class AddLogoToIfrsEntitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            config('ifrs.table_prefix') . 'entities',
            function (Blueprint $table) {
                $table->string('logo')->nullable()->after('name');
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
        Schema::table(
            config('ifrs.table_prefix') . 'entities',
            function (Blueprint $table) {
                $table->dropColumn('logo');
            },
        );
    }
}
