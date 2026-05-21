<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('client_requests', function (Blueprint $table) {
            $table->id();

            // who sent it (client user)
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // optional related task
            $table->foreignId('task_id')->nullable()
                  ->constrained()->nullOnDelete();

            $table->string('subject');
            $table->text('message');

            // open | read | closed
            $table->string('status')->default('open');

            // admin can leave notes when processing
            $table->text('admin_notes')->nullable();

            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_requests');
    }
};
