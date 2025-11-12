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
        // Add fields to cards table for enhanced features
        Schema::table('cards', function (Blueprint $table) {
            $table->boolean('is_overdue')->default(false)->after('actual_hours');
            $table->timestamp('deadline_notified_at')->nullable()->after('is_overdue');
            $table->boolean('is_template')->default(false)->after('deadline_notified_at');
        });

        // Add fields to projects table
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('is_overdue')->default(false)->after('is_archived');
            $table->timestamp('deadline_notified_at')->nullable()->after('is_overdue');
            $table->decimal('completion_percentage', 5, 2)->default(0.00)->after('deadline_notified_at');
            $table->boolean('is_template')->default(false)->after('completion_percentage');
        });

        // Create task_dependencies table
        Schema::create('task_dependencies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('depends_on_task_id');
            $table->enum('dependency_type', ['finish_to_start', 'start_to_start', 'finish_to_finish'])->default('finish_to_start');
            $table->timestamps();
            
            $table->foreign('task_id')->references('card_id')->on('cards')->onDelete('cascade');
            $table->foreign('depends_on_task_id')->references('card_id')->on('cards')->onDelete('cascade');
            $table->unique(['task_id', 'depends_on_task_id']);
        });

        // Create task_attachments table
        Schema::create('task_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('card_id');
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type', 50);
            $table->integer('file_size'); // in bytes
            $table->timestamps();
            
            $table->foreign('card_id')->references('card_id')->on('cards')->onDelete('cascade');
            $table->foreign('uploaded_by')->references('user_id')->on('users')->onDelete('set null');
        });

        // Create task_comments table
        Schema::create('task_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('card_id');
            $table->unsignedBigInteger('user_id');
            $table->text('comment');
            $table->timestamps();
            
            $table->foreign('card_id')->references('card_id')->on('cards')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });

        // Create comment_mentions table
        Schema::create('comment_mentions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('comment_id');
            $table->unsignedBigInteger('mentioned_user_id');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            
            $table->foreign('comment_id')->references('id')->on('task_comments')->onDelete('cascade');
            $table->foreign('mentioned_user_id')->references('user_id')->on('users')->onDelete('cascade');
        });

        // Create project_templates table
        Schema::create('project_templates', function (Blueprint $table) {
            $table->id();
            $table->string('template_name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->json('boards_structure'); // Store board names and card templates
            $table->timestamps();
            
            $table->foreign('created_by')->references('user_id')->on('users')->onDelete('set null');
        });

        // Create audit_logs table
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action'); // created, updated, deleted, etc.
            $table->string('model_type'); // Project, Card, User, etc.
            $table->unsignedBigInteger('model_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');
            $table->index(['model_type', 'model_id']);
            $table->index('created_at');
        });

        // Create saved_searches table
        Schema::create('saved_searches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->json('filters');
            $table->timestamps();
            
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });

        // Create user_preferences table
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->boolean('dark_mode')->default(false);
            $table->boolean('email_notifications')->default(true);
            $table->boolean('deadline_reminders')->default(true);
            $table->json('keyboard_shortcuts')->nullable();
            $table->string('language', 10)->default('en');
            $table->timestamps();
            
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->dropColumn(['is_overdue', 'deadline_notified_at', 'is_template']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['is_overdue', 'deadline_notified_at', 'completion_percentage', 'is_template']);
        });

        Schema::dropIfExists('user_preferences');
        Schema::dropIfExists('saved_searches');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('project_templates');
        Schema::dropIfExists('comment_mentions');
        Schema::dropIfExists('task_comments');
        Schema::dropIfExists('task_attachments');
        Schema::dropIfExists('task_dependencies');
    }
};
