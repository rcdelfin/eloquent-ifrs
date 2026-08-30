<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTinAndAddressToIfrsEntitiesTable extends Migration
{
    public function up(): void
    {
        Schema::table(config('ifrs.table_prefix') . 'entities', function (Blueprint $table): void {
            $table->string('tin', 50)->nullable()->after('name');
            $table->text('address')->nullable()->after('tin');
        });
    }

    public function down(): void
    {
        Schema::table(config('ifrs.table_prefix') . 'entities', function (Blueprint $table): void {
            $table->dropColumn(['tin', 'address']);
        });
    }
}
