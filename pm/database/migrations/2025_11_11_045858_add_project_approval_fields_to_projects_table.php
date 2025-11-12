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
        Schema::table('projects', function (Blueprint $table) {
            $table->enum('completion_status', ['working', 'pending_approval', 'completed', 'rejected'])->default('working')->after('status');
            $table->unsignedBigInteger('requested_by')->nullable()->after('completion_status');
            $table->timestamp('requested_at')->nullable()->after('requested_by');
            $table->unsignedBigInteger('approved_by')->nullable()->after('requested_at');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('approval_notes')->nullable()->after('approved_at');
            
            $table->foreign('requested_by')->references('user_id')->on('users')->onDelete('set null');
            $table->foreign('approved_by')->references('user_id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['requested_by']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['completion_status', 'requested_by', 'requested_at', 'approved_by', 'approved_at', 'approval_notes']);
        });
    }
};
