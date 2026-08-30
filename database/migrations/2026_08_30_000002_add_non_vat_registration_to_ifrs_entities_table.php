<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNonVatRegistrationToIfrsEntitiesTable extends Migration
{
    public function up(): void
    {
        Schema::table(config('ifrs.table_prefix') . 'entities', function (Blueprint $table): void {
            $table->boolean('non_vat_registered')->default(false)->after('address');
        });
    }

    public function down(): void
    {
        Schema::table(config('ifrs.table_prefix') . 'entities', function (Blueprint $table): void {
            $table->dropColumn('non_vat_registered');
        });
    }
}
