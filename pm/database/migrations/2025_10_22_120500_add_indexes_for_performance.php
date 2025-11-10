<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add helpful indexes to speed up navbar and notifications queries
        if (Schema::hasTable('project_members')) {
            Schema::table('project_members', function (Blueprint $table) {
                $table->index(['user_id', 'role'], 'pm_user_role_idx');
            });
        }

        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->index(['user_id', 'created_at'], 'notifications_user_created_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('project_members')) {
            Schema::table('project_members', function (Blueprint $table) {
                $table->dropIndex('pm_user_role_idx');
            });
        }

        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->dropIndex('notifications_user_created_idx');
            });
        }
    }
};
