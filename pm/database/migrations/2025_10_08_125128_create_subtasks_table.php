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
        Schema::create('subtasks', function (Blueprint $table) {
            $table->id('subtask_id');
            $table->foreignId('card_id')->references('card_id')->on('cards')->onUpdate('cascade')->onDelete('cascade');
            $table->string('subtask_title', 100);
            $table->text('description')->nullable();
            $table->enum('status', ['todo', 'in_progress', 'done'])->default('todo');
            $table->decimal('estimated_hours', 5, 2)->default(0.00);
            $table->decimal('actual_hours', 5, 2)->default(0.00);
            $table->integer('position')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subtasks');
    }
};
