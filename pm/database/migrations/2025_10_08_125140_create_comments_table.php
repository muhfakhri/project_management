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
        Schema::create('comments', function (Blueprint $table) {
            $table->id('comment_id');
            $table->foreignId('card_id')->nullable()->references('card_id')->on('cards')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('subtask_id')->nullable()->references('subtask_id')->on('subtasks')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->references('user_id')->on('users')->onUpdate('cascade')->onDelete('set null');
            $table->text('comment_text');
            $table->enum('comment_type', ['card', 'subtask'])->default('card');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
