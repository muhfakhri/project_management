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
        Schema::create('card_assignments', function (Blueprint $table) {
            $table->id('assignment_id');
            $table->foreignId('card_id')->references('card_id')->on('cards')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('user_id')->references('user_id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->enum('assignment_status', ['assigned', 'in_progress', 'completed'])->default('assigned');
            $table->datetime('started_at')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->timestamp('assigned_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_assignments');
    }
};
