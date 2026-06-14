<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('expense_subsection_default_rows')) {
            return;
        }

        if (Schema::hasColumn('expense_subsection_default_rows', 'default_values')) {
            DB::table('expense_subsection_default_rows')
                ->whereNotNull('default_values')
                ->orderBy('id')
                ->select('id', 'default_values')
                ->chunkById(100, function ($rows): void {
                    foreach ($rows as $row) {
                        $values = json_decode((string) $row->default_values, true);

                        if (! is_array($values) || ! array_key_exists('reference', $values)) {
                            continue;
                        }

                        unset($values['reference']);

                        DB::table('expense_subsection_default_rows')
                            ->where('id', $row->id)
                            ->update(['default_values' => $values === [] ? null : json_encode($values, JSON_UNESCAPED_UNICODE)]);
                    }
                });
        }

        if (! Schema::hasColumn('expense_subsection_default_rows', 'reference')) {
            return;
        }

        Schema::table('expense_subsection_default_rows', function (Blueprint $table): void {
            $table->dropColumn('reference');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('expense_subsection_default_rows') || Schema::hasColumn('expense_subsection_default_rows', 'reference')) {
            return;
        }

        Schema::table('expense_subsection_default_rows', function (Blueprint $table): void {
            $table->string('reference', 80)->nullable()->after('item_name');
        });
    }
};
