<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Board;

class FixBoardsMapping extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'boards:fix-mapping {project_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically set status_mapping for boards based on their names';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $projectId = $this->argument('project_id');
        
        $query = Board::whereNull('status_mapping');
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        
        $boards = $query->get();
        
        if ($boards->isEmpty()) {
            $this->info('✅ All boards already have status_mapping configured!');
            return;
        }
        
        $this->info("Found {$boards->count()} boards without status_mapping.");
        $this->info("Attempting to auto-fix based on board names...\n");
        
        $mappings = [
            'todo' => ['to do', 'todo', 'backlog', 'planned'],
            'in_progress' => ['in progress', 'progress', 'doing', 'working', 'development'],
            'review' => ['review', 'testing', 'qa', 'pending'],
            'done' => ['done', 'completed', 'finished', 'complete']
        ];
        
        $fixed = 0;
        foreach ($boards as $board) {
            $boardNameLower = strtolower($board->board_name);
            $matched = false;
            
            foreach ($mappings as $status => $keywords) {
                foreach ($keywords as $keyword) {
                    if (str_contains($boardNameLower, $keyword)) {
                        $board->status_mapping = $status;
                        $board->save();
                        $this->line("✅ Board '{$board->board_name}' → status_mapping = '{$status}'");
                        $fixed++;
                        $matched = true;
                        break 2;
                    }
                }
            }
            
            if (!$matched) {
                $this->warn("⚠️  Could not auto-detect mapping for board: '{$board->board_name}'");
                $this->line("   Please set manually in UI or database.");
            }
        }
        
        $this->newLine();
        $this->info("Fixed {$fixed} boards!");
        
        if ($fixed < $boards->count()) {
            $this->warn("Note: " . ($boards->count() - $fixed) . " boards still need manual configuration.");
        }
        
        $this->newLine();
        $this->info("Run 'php artisan boards:check' to verify.");
    }
}
