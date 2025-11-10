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
        Schema::create('project_members', function (Blueprint $table) {
            $table->id('member_id');
            $table->foreignId('project_id')->references('project_id')->on('projects')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('user_id')->references('user_id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->enum('role', ['Project Admin', 'Team Lead', 'Developer', 'Designer'])->default('Developer');
            $table->timestamp('joined_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_members');
    }
};
