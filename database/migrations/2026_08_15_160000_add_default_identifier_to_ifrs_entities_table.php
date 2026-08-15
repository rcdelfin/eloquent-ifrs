<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddDefaultIdentifierToIfrsEntitiesTable extends Migration
{
    public function up(): void
    {
        $table = config('ifrs.table_prefix') . 'entities';

        Schema::table($table, function (Blueprint $table): void {
            $table->boolean('is_default')->default(false)->after('name');
        });

        $defaultEntityId = DB::table($table)
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->value('id');

        if (null !== $defaultEntityId) {
            DB::table($table)
                ->where('id', $defaultEntityId)
                ->update(['is_default' => true]);
        }
    }

    public function down(): void
    {
        Schema::table(config('ifrs.table_prefix') . 'entities', function (Blueprint $table): void {
            $table->dropColumn('is_default');
        });
    }
}
