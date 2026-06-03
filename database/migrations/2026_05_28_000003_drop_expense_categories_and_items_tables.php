<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The flat expense_entries table replaces the old category-tree + items model.
     * Drop items first (it FKs into categories), then categories.
     */
    public function up(): void
    {
        Schema::dropIfExists('expense_items');
        Schema::dropIfExists('expense_categories');
    }

    /**
     * Forward redesign — the old tables are not recreated on rollback.
     * (Their original create migrations remain on disk for reference.)
     */
    public function down(): void
    {
        // no-op
    }
};
