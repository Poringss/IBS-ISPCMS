<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add "budget" to clients if it doesn't exist yet.
        if (! Schema::hasColumn('clients', 'budget')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->decimal('budget', 12, 2)
                      ->nullable()
                      ->after('name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the column only if it exists.
        if (Schema::hasColumn('clients', 'budget')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->dropColumn('budget');
            });
        }
    }
};
