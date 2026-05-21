<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_client_id_to_tasks_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // position after project_id if you like
            $table->unsignedBigInteger('client_id')->nullable()->after('project_id');

            // optional FK (SQLite supports this too)
            $table->foreign('client_id')->references('id')->on('clients')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_id'); // or dropForeign then dropColumn if needed
        });
    }
};
