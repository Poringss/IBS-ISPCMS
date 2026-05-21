<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Only add if absent
            if (!Schema::hasColumn('tasks', 'client_user_id')) {
                $table->foreignId('client_user_id')
                      ->nullable()
                      ->after('id')
                      ->constrained('users')
                      ->nullOnDelete()
                      ->index();
            }
            if (!Schema::hasColumn('tasks', 'client_rating')) {
                $table->tinyInteger('client_rating')->nullable()->after('handler_rating');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'client_rating')) {
                $table->dropColumn('client_rating');
            }
            if (Schema::hasColumn('tasks', 'client_user_id')) {
                $table->dropConstrainedForeignId('client_user_id');
            }
        });
    }
};

