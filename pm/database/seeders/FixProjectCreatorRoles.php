<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\ProjectMember;

class FixProjectCreatorRoles extends Seeder
{
    /**
     * Fix all project creators to have Project Admin role instead of Team Lead
     */
    public function run(): void
    {
        echo "\n=== Checking Project Members ===\n\n";
        
        $fixed = 0;
        
        Project::with(['members.user', 'creator'])->get()->each(function($project) use (&$fixed) {
            echo "Project: {$project->project_name}\n";
            echo "  Created by: {$project->creator->username} (ID: {$project->created_by})\n";
            
            // Find the project creator in project_members
            $creator = ProjectMember::where('project_id', $project->project_id)
                ->where('user_id', $project->created_by)
                ->first();
            
            if ($creator) {
                if ($creator->role !== 'Project Admin') {
                    $oldRole = $creator->role;
                    $creator->update(['role' => 'Project Admin']);
                    $fixed++;
                    echo "  ✅ FIXED: Creator role changed from '{$oldRole}' to 'Project Admin'\n";
                } else {
                    echo "  ✓ Creator role is already: {$creator->role}\n";
                }
            } else {
                echo "  ⚠️ WARNING: Creator is not in project_members table!\n";
            }
            
            // Show all members
            echo "  Members:\n";
            $project->members->each(function($member) use ($project) {
                $isCreator = $member->user_id === $project->created_by ? ' [CREATOR]' : '';
                echo "    - {$member->user->username}: {$member->role}{$isCreator}\n";
            });
            echo "\n";
        });
        
        echo "=================================\n";
        if ($fixed > 0) {
            echo "✅ Fixed {$fixed} project creator(s) to Project Admin role\n";
        } else {
            echo "✓ All project creators already have correct role\n";
        }
    }
}
