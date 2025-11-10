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
        Schema::create('time_logs', function (Blueprint $table) {
            $table->id('log_id');
            $table->foreignId('card_id')->nullable()->references('card_id')->on('cards')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('subtask_id')->nullable()->references('subtask_id')->on('subtasks')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->references('user_id')->on('users')->onUpdate('cascade')->onDelete('set null');
            $table->datetime('start_time')->nullable();
            $table->datetime('end_time')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->text('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_logs');
    }
};
