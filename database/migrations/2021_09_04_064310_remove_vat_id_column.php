<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveVatIdColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(config('ifrs.table_prefix') . 'line_items', function (Blueprint $table) {
            // Drop foreign key constraint first if it exists
            try {
                $table->dropForeign(['vat_id']);
            } catch (Exception $e) {
                // If the foreign key doesn't exist or can't be dropped, continue
            }

            // Then drop the column
            try {
                $table->dropColumn('vat_id');
            } catch (Exception $e) {
                // If the column can't be dropped, continue
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(config('ifrs.table_prefix') . 'line_items', function (Blueprint $table) {
            $table->unsignedBigInteger('vat_id')->nullable();
            $table->foreign('vat_id')->references('id')->on(config('ifrs.table_prefix') . 'vats');
        });
    }
}
