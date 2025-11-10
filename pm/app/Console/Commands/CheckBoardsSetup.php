<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Board;

class CheckBoardsSetup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'boards:check {project_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check boards setup and status_mapping';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $projectId = $this->argument('project_id');
        
        $query = Board::query();
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        
        $boards = $query->get(['board_id', 'board_name', 'status_mapping', 'project_id']);
        
        if ($boards->isEmpty()) {
            $this->error('No boards found!');
            return;
        }
        
        $this->info('Boards Setup:');
        $this->table(
            ['Board ID', 'Board Name', 'Status Mapping', 'Project ID'],
            $boards->map(function ($board) {
                return [
                    $board->board_id,
                    $board->board_name,
                    $board->status_mapping ?? '❌ NULL',
                    $board->project_id
                ];
            })
        );
        
        // Check for missing status_mapping
        $missing = $boards->whereNull('status_mapping');
        if ($missing->count() > 0) {
            $this->warn("\n⚠️  WARNING: {$missing->count()} boards have NULL status_mapping!");
            $this->warn('Auto-move will NOT work for these boards.');
            $this->info("\nTo fix, run:");
            $this->line("php artisan boards:fix-mapping");
        } else {
            $this->info("\n✅ All boards have status_mapping configured!");
        }
    }
}
