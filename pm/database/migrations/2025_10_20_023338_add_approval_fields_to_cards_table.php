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
        Schema::table('cards', function (Blueprint $table) {
            $table->boolean('needs_approval')->default(false)->after('status');
            $table->boolean('is_approved')->default(false)->after('needs_approval');
            $table->foreignId('approved_by')->nullable()->references('user_id')->on('users')->onUpdate('cascade')->onDelete('set null')->after('is_approved');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('rejection_reason')->nullable()->after('approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
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
    }
};
