<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Clean up in case a previous failed run left this behind
        Schema::dropIfExists('client_requests_new');

        // Create the new table with the columns you need
        Schema::create('client_requests_new', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->constrained('tasks')->nullOnDelete();
            $table->string('subject');
            $table->text('message');

            // Option A: no CHECK (most flexible)
            $table->string('status')->default('open');

            // Timestamps were missing before — add them
            $table->timestamps();
        });

        // If you want an explicit CHECK in SQLite instead of the flexible string above,
        // comment out the Schema::create block and use this raw SQL instead:
        //
        // DB::statement('
        //     CREATE TABLE client_requests_new (
        //         id integer primary key autoincrement not null,
        //         user_id integer not null,
        //         task_id integer null,
        //         subject varchar(255) not null,
        //         message text not null,
        //         status text not null default "open" CHECK (status in ("open","resolved")),
        //         created_at datetime null,
        //         updated_at datetime null,
        //         foreign key(user_id) references users(id) on delete cascade,
        //         foreign key(task_id) references tasks(id) on delete set null
        //     )
        // ');

        // Copy data from the old table (assumes old table had these columns)
        DB::statement('
            INSERT INTO client_requests_new (id, user_id, task_id, subject, message, status, created_at, updated_at)
            SELECT id, user_id, task_id, subject, message, status, created_at, updated_at
            FROM client_requests
        ');

        // Swap the tables
        Schema::drop('client_requests');
        Schema::rename('client_requests_new', 'client_requests');
    }

    public function down(): void
    {
        // Minimal down: drop the rebuilt table
        // (Optionally recreate the old structure if you need a true rollback.)
        // Schema::dropIfExists('client_requests');
    }
};
