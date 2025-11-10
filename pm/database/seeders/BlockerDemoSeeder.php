<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BlockerDemoSeeder extends Seeder
{
    /**
     * Run the database seeder to create sample blocker data.
     */
    public function run(): void
    {
        // Check if users exist
        $users = DB::table('users')->pluck('user_id', 'username');
        if ($users->isEmpty()) {
            echo "❌ No users found. Please run AdminUserSeeder first.\n";
            return;
        }

        // Check if cards/tasks exist
        $cards = DB::table('cards')->pluck('card_id')->toArray();
        if (empty($cards)) {
            echo "⚠️  No tasks found. Creating sample tasks first...\n";
            
            // Get or create a project
            $projectId = DB::table('projects')->value('project_id');
            if (!$projectId) {
                $projectId = DB::table('projects')->insertGetId([
                    'project_name' => 'Demo Project',
                    'description' => 'Sample project for blocker demo',
                    'start_date' => now(),
                    'end_date' => now()->addMonths(3),
                    'status' => 'active',
                    'created_by' => $users->get('admin@pm.com', 1),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Get or create a board
            $boardId = DB::table('boards')->value('board_id');
            if (!$boardId) {
                $boardId = DB::table('boards')->insertGetId([
                    'board_name' => 'Sprint 1',
                    'project_id' => $projectId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Create sample tasks
            $cardIds = [];
            $tasks = [
                [
                    'card_title' => 'Implement User Authentication',
                    'description' => 'Create login and registration functionality',
                    'status' => 'in_progress',
                    'priority' => 'high',
                ],
                [
                    'card_title' => 'Design Dashboard UI',
                    'description' => 'Create responsive dashboard layout',
                    'status' => 'in_progress',
                    'priority' => 'medium',
                ],
                [
                    'card_title' => 'Setup CI/CD Pipeline',
                    'description' => 'Configure automated deployment',
                    'status' => 'todo',
                    'priority' => 'high',
                ],
                [
                    'card_title' => 'API Documentation',
                    'description' => 'Document all REST API endpoints',
                    'status' => 'in_progress',
                    'priority' => 'medium',
                ],
            ];

            foreach ($tasks as $task) {
                $cardIds[] = DB::table('cards')->insertGetId([
                    'card_title' => $task['card_title'],
                    'description' => $task['description'],
                    'status' => $task['status'],
                    'priority' => $task['priority'],
                    'board_id' => $boardId,
                    'project_id' => $projectId,
                    'assigned_to' => $users->get('john', 1),
                    'created_by' => $users->get('teamlead', 1),
                    'created_at' => now()->subDays(rand(1, 7)),
                    'updated_at' => now()->subDays(rand(0, 3)),
                ]);
            }
            
            $cards = $cardIds;
            echo "✅ Created 4 sample tasks\n";
        }

        // Sample blockers data
        $blockers = [
            [
                'card_id' => $cards[0],
                'reporter_id' => $users->get('john', 1),
                'reason' => 'Missing OAuth credentials for Google authentication. Need access to Google Cloud Console to create OAuth client ID.',
                'priority' => 'critical',
                'status' => 'reported',
                'assigned_to' => null,
                'created_at' => now()->subHours(26), // Overdue
            ],
            [
                'card_id' => $cards[1] ?? $cards[0],
                'reporter_id' => $users->get('alice', $users->get('john', 1)),
                'reason' => 'Design assets not yet approved by client. Need feedback on color scheme and layout before proceeding.',
                'priority' => 'high',
                'status' => 'assigned',
                'assigned_to' => $users->get('teamlead', 1),
                'created_at' => now()->subHours(5),
            ],
            [
                'card_id' => $cards[2] ?? $cards[0],
                'reporter_id' => $users->get('jane', $users->get('john', 1)),
                'reason' => 'Docker deployment failing on staging server. Getting "permission denied" error when accessing /var/www directory.',
                'priority' => 'high',
                'status' => 'in_progress',
                'assigned_to' => $users->get('admin', 1),
                'created_at' => now()->subHours(12),
            ],
            [
                'card_id' => $cards[3] ?? $cards[0],
                'reporter_id' => $users->get('john', 1),
                'reason' => 'Unclear API response format for paginated endpoints. Need specification document or example.',
                'priority' => 'medium',
                'status' => 'resolved',
                'assigned_to' => $users->get('teamlead', 1),
                'resolution_note' => 'Added API specification document to Confluence. Link: https://wiki.company.com/api-docs. All paginated endpoints now follow the standard format: {data: [], meta: {current_page, total, per_page}}',
                'resolved_at' => now()->subHours(2),
                'created_at' => now()->subDays(1),
            ],
            [
                'card_id' => $cards[0],
                'reporter_id' => $users->get('jane', $users->get('john', 1)),
                'reason' => 'Third-party payment gateway test credentials expired. Cannot test payment flow.',
                'priority' => 'medium',
                'status' => 'assigned',
                'assigned_to' => $users->get('teamlead', 1),
                'created_at' => now()->subHours(3),
            ],
        ];

        $blockerIds = [];
        foreach ($blockers as $blocker) {
            $blockerId = DB::table('blockers')->insertGetId(array_merge($blocker, [
                'updated_at' => now(),
            ]));
            $blockerIds[] = $blockerId;
        }

        echo "✅ Created " . count($blockerIds) . " sample blockers\n";

        // Add sample comments to blockers
        $comments = [
            [
                'blocker_id' => $blockerIds[1], // Design blocker
                'user_id' => $users->get('teamlead', 1),
                'comment' => 'I\'ve reached out to the client. They should get back to us by end of day.',
                'created_at' => now()->subHours(4),
            ],
            [
                'blocker_id' => $blockerIds[1],
                'user_id' => $users->get('alice', $users->get('john', 1)),
                'comment' => 'Thanks! I\'ll prepare the alternative color scheme in the meantime.',
                'created_at' => now()->subHours(3),
            ],
            [
                'blocker_id' => $blockerIds[2], // Docker blocker
                'user_id' => $users->get('admin', 1),
                'comment' => 'Checking the server permissions now. It looks like the user needs to be added to www-data group.',
                'created_at' => now()->subHours(11),
            ],
            [
                'blocker_id' => $blockerIds[2],
                'user_id' => $users->get('jane', $users->get('john', 1)),
                'comment' => 'I tried that but still getting the same error. Could it be a SELinux issue?',
                'created_at' => now()->subHours(10),
            ],
            [
                'blocker_id' => $blockerIds[2],
                'user_id' => $users->get('admin', 1),
                'comment' => 'Good catch! Yes, SELinux is blocking it. Running: sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www(/.*)?"',
                'created_at' => now()->subHours(9),
            ],
            [
                'blocker_id' => $blockerIds[3], // Resolved API docs blocker
                'user_id' => $users->get('teamlead', 1),
                'comment' => 'I\'ve created the documentation. Please check and let me know if you need anything else.',
                'created_at' => now()->subHours(3),
            ],
            [
                'blocker_id' => $blockerIds[3],
                'user_id' => $users->get('john', 1),
                'comment' => 'Perfect! This is exactly what I needed. Thanks!',
                'created_at' => now()->subHours(2),
            ],
        ];

        foreach ($comments as $comment) {
            DB::table('blocker_comments')->insert(array_merge($comment, [
                'updated_at' => now(),
            ]));
        }

        echo "✅ Created " . count($comments) . " sample comments\n";

        // Add sample notifications
        $notifications = [
            [
                'user_id' => $users->get('teamlead', 1),
                'type' => 'blocker_reported',
                'title' => 'New Critical Blocker Reported',
                'message' => 'John Doe reported a critical blocker on task: Implement User Authentication',
                'data' => json_encode([
                    'blocker_id' => $blockerIds[0],
                    'card_id' => $cards[0],
                    'priority' => 'critical',
                ]),
                'read_at' => null,
                'created_at' => now()->subHours(26),
                'updated_at' => now()->subHours(26),
            ],
            [
                'user_id' => $users->get('admin', 1),
                'type' => 'blocker_reported',
                'title' => 'New Critical Blocker Reported',
                'message' => 'John Doe reported a critical blocker on task: Implement User Authentication',
                'data' => json_encode([
                    'blocker_id' => $blockerIds[0],
                    'card_id' => $cards[0],
                    'priority' => 'critical',
                ]),
                'read_at' => null,
                'created_at' => now()->subHours(26),
                'updated_at' => now()->subHours(26),
            ],
            [
                'user_id' => $users->get('teamlead', 1),
                'type' => 'blocker_assigned',
                'title' => 'You Have Been Assigned to Help',
                'message' => 'You have been assigned to help with a blocker on task: Design Dashboard UI',
                'data' => json_encode([
                    'blocker_id' => $blockerIds[1],
                    'card_id' => $cards[1] ?? $cards[0],
                    'priority' => 'high',
                ]),
                'read_at' => now()->subHours(4),
                'created_at' => now()->subHours(5),
                'updated_at' => now()->subHours(4),
            ],
            [
                'user_id' => $users->get('john', 1),
                'type' => 'blocker_resolved',
                'title' => 'Blocker Resolved',
                'message' => 'Your blocker on task "API Documentation" has been resolved',
                'data' => json_encode([
                    'blocker_id' => $blockerIds[3],
                    'card_id' => $cards[3] ?? $cards[0],
                    'resolution_note' => 'Added API specification document to Confluence',
                ]),
                'read_at' => now()->subHours(1),
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subHours(1),
            ],
        ];

        foreach ($notifications as $notification) {
            DB::table('notifications')->insert($notification);
        }

        echo "✅ Created " . count($notifications) . " sample notifications\n";

        echo "\n";
        echo "========================================\n";
        echo "✅ DEMO DATA CREATED SUCCESSFULLY!\n";
        echo "========================================\n";
        echo "\n";
        echo "📊 Summary:\n";
        echo "   - 5 Blockers (1 overdue, 1 critical, 2 high, 2 medium)\n";
        echo "   - 1 Resolved blocker\n";
        echo "   - 7 Comments across blockers\n";
        echo "   - 4 Notifications\n";
        echo "\n";
        echo "🔐 Test Users:\n";
        echo "   Admin      : admin / admin123\n";
        echo "   Team Lead  : teamlead / teamlead123\n";
        echo "   Developer 1: john / john123\n";
        echo "   Developer 2: jane / jane123\n";
        echo "   Designer   : alice / alice123\n";
        echo "\n";
        echo "🧪 Test Scenarios:\n";
        echo "   1. Login as john - See 'My Reports' tab (2 blockers)\n";
        echo "   2. Login as teamlead - See 'Assigned to Me' (2 blockers to help)\n";
        echo "   3. Login as admin - See all blockers + 1 assigned\n";
        echo "   4. Critical blocker is OVERDUE (>24h) - should show warning!\n";
        echo "   5. View blocker #3 (Docker issue) - has active discussion thread\n";
        echo "   6. View blocker #4 (API docs) - resolved with solution note\n";
        echo "\n";
        echo "🚀 Next Steps:\n";
        echo "   1. Test API: POST /api/login with above credentials\n";
        echo "   2. Get blockers: GET /api/blockers\n";
        echo "   3. Report new blocker: POST /api/blockers\n";
        echo "   4. Open Flutter app and test UI\n";
        echo "\n";
    }
}
