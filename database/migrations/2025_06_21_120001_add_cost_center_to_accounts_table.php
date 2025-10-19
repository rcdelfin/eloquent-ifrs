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

class AddCostCenterToAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            config('ifrs.table_prefix') . 'accounts',
            function (Blueprint $table) {
                // Add cost center relationship
                $table->unsignedBigInteger('cost_center_id')->nullable()->after('category_id');

                // Add foreign key constraint
                $table->foreign('cost_center_id')->references('id')->on(config('ifrs.table_prefix') . 'cost_centers');

                // Add index for better performance
                $table->index(['entity_id', 'cost_center_id']);
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
            config('ifrs.table_prefix') . 'accounts',
            function (Blueprint $table) {
                // Drop foreign key constraint first
                try {
                    $table->dropForeign(['cost_center_id']);
                } catch (Exception $e) {
                    // If the foreign key doesn't exist or can't be dropped, continue
                }

                // Drop index
                try {
                    $table->dropIndex(['entity_id', 'cost_center_id']);
                } catch (Exception $e) {
                    // If the index doesn't exist or can't be dropped, continue
                }

                // Drop column
                try {
                    $table->dropColumn('cost_center_id');
                } catch (Exception $e) {
                    // If the column can't be dropped, continue
                }
            },
        );
    }
}
