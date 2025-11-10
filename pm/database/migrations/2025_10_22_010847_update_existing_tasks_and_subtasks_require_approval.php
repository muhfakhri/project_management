<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Update all existing tasks and subtasks to require approval
     */
    public function up(): void
    {
        // Update all existing tasks to require approval
        DB::table('cards')
            ->update(['needs_approval' => true]);

        // Update all existing subtasks to require approval
        DB::table('subtasks')
            ->update(['needs_approval' => true]);

        // Update the default value in schema for future records
        Schema::table('cards', function (Blueprint $table) {
            $table->boolean('needs_approval')->default(true)->change();
        });

        Schema::table('subtasks', function (Blueprint $table) {
            $table->boolean('needs_approval')->default(true)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to optional approval (old behavior)
        DB::table('cards')
            ->update(['needs_approval' => false]);

        DB::table('subtasks')
            ->update(['needs_approval' => false]);

        Schema::table('cards', function (Blueprint $table) {
            $table->boolean('needs_approval')->default(false)->change();
        });

        Schema::table('subtasks', function (Blueprint $table) {
            $table->boolean('needs_approval')->default(false)->change();
        });
    }
};
