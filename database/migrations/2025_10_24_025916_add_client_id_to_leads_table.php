<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            if (!Schema::hasColumn('leads', 'client_id')) {
                $table->foreignId('client_id')
                    ->nullable()
                    ->constrained()     // references id on clients
                    ->nullOnDelete();   // set null if client deleted
            }
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            if (Schema::hasColumn('leads', 'client_id')) {
                $table->dropConstrainedForeignId('client_id');
            }
        });
    }
};
