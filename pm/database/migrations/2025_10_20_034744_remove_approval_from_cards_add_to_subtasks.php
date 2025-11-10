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
        // Remove approval fields from cards
        Schema::table('cards', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'needs_approval',
                'is_approved',
                'approved_by',
                'approved_at',
                'rejection_reason'
            ]);
        });

        // Add approval and time tracking fields to subtasks
        Schema::table('subtasks', function (Blueprint $table) {
            // Time tracking fields
            $table->timestamp('started_at')->nullable()->after('status');
            $table->timestamp('completed_at')->nullable()->after('started_at');
            $table->integer('duration_minutes')->nullable()->after('completed_at');
            
            // Approval fields - ALWAYS REQUIRED
            $table->boolean('needs_approval')->default(true)->after('duration_minutes'); // Changed to true
            $table->boolean('is_approved')->default(false)->after('needs_approval');
            $table->foreignId('approved_by')->nullable()->after('is_approved')
                ->references('user_id')->on('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('rejection_reason')->nullable()->after('approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore approval fields to cards
        Schema::table('cards', function (Blueprint $table) {
            $table->boolean('needs_approval')->default(false);
            $table->boolean('is_approved')->default(false);
            $table->foreignId('approved_by')->nullable()
                ->references('user_id')->on('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
        });

        // Remove fields from subtasks
        Schema::table('subtasks', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'started_at',
                'completed_at',
                'duration_minutes',
                'needs_approval',
                'is_approved',
                'approved_by',
                'approved_at',
                'rejection_reason'
            ]);
        });
    }
};
