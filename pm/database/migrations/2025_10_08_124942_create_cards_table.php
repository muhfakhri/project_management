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
        Schema::create('cards', function (Blueprint $table) {
            $table->id('card_id');
            $table->foreignId('board_id')->references('board_id')->on('boards')->onUpdate('cascade')->onDelete('cascade');
            $table->string('card_title', 100);
            $table->text('description')->nullable();
            $table->integer('position')->default(0);
            $table->foreignId('created_by')->nullable()->references('user_id')->on('users')->onUpdate('cascade')->onDelete('set null');
            $table->date('due_date')->nullable();
            $table->enum('status', ['todo', 'in_progress', 'review', 'done'])->default('todo');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->decimal('estimated_hours', 5, 2)->default(0.00);
            $table->decimal('actual_hours', 5, 2)->default(0.00);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
