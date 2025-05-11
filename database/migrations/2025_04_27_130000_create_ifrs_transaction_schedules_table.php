<?php

/**
 * Eloquent IFRS Accounting
 *
 * @author GitHub Copilot
 * @copyright ReadAhead, 2025, Philippines
 * @license MIT
 */

use IFRS\Models\TransactionSchedule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIfrsTransactionSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config("ifrs.table_prefix") . "transaction_schedules", function (Blueprint $table) {
            $table->bigIncrements("id");

            // relationships
            $table->unsignedBigInteger("entity_id")->nullable();

            // constraints
            $table->foreign("entity_id")->references("id") ->on(config("ifrs.table_prefix") . "entities");

            // attributes
            $table->string("name", 255);
            $table->text("description")->nullable();
            $table->enum("frequency", TransactionSchedule::FREQUENCIES);
            $table->dateTime("start_date");
            $table->dateTime("end_date");
            $table->boolean("is_active")->default(true);
            $table->boolean("is_template")->default(false);

            // *permanent* deletion
            $table->dateTime("destroyed_at")->nullable();

            //soft deletion
            $table->softDeletes();

            $table->timestamps();
        });


        Schema::create(config("ifrs.table_prefix") . "transaction_schedule_items", function (Blueprint $table) {
            $table->bigIncrements("id");

            // relationships
            $table->unsignedBigInteger("schedule_id");
            $table->unsignedBigInteger("account_id");
            $table->unsignedBigInteger("receivable_account_id");
            $table->unsignedBigInteger("currency_id")->nullable();

            // constraints
            $table->foreign("schedule_id")->references("id")->on(config("ifrs.table_prefix") . "transaction_schedules");
            $table->foreign("account_id")->references("id")->on(config("ifrs.table_prefix") . "accounts");
            $table->foreign("receivable_account_id")->references("id")->on(config("ifrs.table_prefix") . "accounts");
            $table->foreign("currency_id")->references("id")->on(config("ifrs.table_prefix") . "currencies");

            // attributes
            $table->decimal("amount", 13, 4);

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
        Schema::dropIfExists(config("ifrs.table_prefix") . "transaction_schedules");
        Schema::dropIfExists(config("ifrs.table_prefix") . "transaction_schedule_items");
    }
}
