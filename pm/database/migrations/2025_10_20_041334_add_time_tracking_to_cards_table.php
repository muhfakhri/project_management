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
            $table->timestamp('started_at')->nullable()->after('due_date');
            $table->timestamp('paused_at')->nullable()->after('started_at');
            $table->integer('total_pause_duration')->default(0)->after('paused_at')->comment('Total pause duration in minutes');
            $table->timestamp('completed_at')->nullable()->after('total_pause_duration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->dropColumn(['started_at', 'paused_at', 'total_pause_duration', 'completed_at']);
        });
    }
};
