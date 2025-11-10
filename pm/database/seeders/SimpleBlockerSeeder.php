<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SimpleBlockerSeeder extends Seeder
{
    /**
     * Create simple blocker demo data assuming tasks exist.
     * If no tasks, user needs to create tasks manually first.
     */
    public function run(): void
    {
        $users = DB::table('users')->pluck('user_id', 'username');
        
        if ($users->isEmpty()) {
            echo "❌ No users found. Run AdminUserSeeder first.\n";
            return;
        }

        // Check for existing cards
        $cards = DB::table('cards')->pluck('card_id')->toArray();
        
        if (empty($cards)) {
            echo "⚠️  No tasks found in database.\n";
            echo "Please create some tasks first via the web interface, then run this seeder.\n";
            echo "\nFor now, I'll show you how to manually insert a blocker via SQL:\n\n";
            echo "INSERT INTO blockers (card_id, reporter_id, reason, priority, status, created_at, updated_at)\n";
            echo "VALUES (YOUR_CARD_ID, " . $users->get('john', 1) . ", 'Missing API credentials', 'high', 'reported', NOW(), NOW());\n\n";
            return;
        }

        echo "✅ Found " . count($cards) . " existing tasks\n";

        // Create sample blockers using existing tasks
        $blockers = [];
        
        // Blocker 1: Critical - Overdue
        if (isset($cards[0])) {
            $blockers[] = [
                'card_id' => $cards[0],
                'reporter_id' => $users->get('john', $users->first()),
                'reason' => 'Missing OAuth credentials for Google authentication. Need access to Google Cloud Console to create OAuth client ID.',
                'priority' => 'critical',
                'status' => 'reported',
                'created_at' => now()->subHours(26), // Overdue
                'updated_at' => now()->subHours(26),
            ];
        }

        // Blocker 2: High - Assigned
        if (isset($cards[1])) {
            $blockers[] = [
                'card_id' => $cards[1],
                'reporter_id' => $users->get('alice', $users->get('jane', $users->first())),
                'reason' => 'Design assets not yet approved by client. Need feedback on color scheme before proceeding.',
                'priority' => 'high',
                'status' => 'assigned',
                'assigned_to' => $users->get('teamlead', $users->first()),
                'created_at' => now()->subHours(5),
                'updated_at' => now()->subHours(5),
            ];
        }

        // Blocker 3: High - In Progress
        if (isset($cards[2])) {
            $blockers[] = [
                'card_id' => $cards[2],
                'reporter_id' => $users->get('jane', $users->first()),
                'reason' => 'Docker deployment failing. Getting "permission denied" error when accessing /var/www directory.',
                'priority' => 'high',
                'status' => 'in_progress',
                'assigned_to' => $users->get('admin', $users->first()),
                'created_at' => now()->subHours(12),
                'updated_at' => now()->subHours(1),
            ];
        }

        // Blocker 4: Medium - Resolved
        if (isset($cards[3])) {
            $blockers[] = [
                'card_id' => $cards[3],
                'reporter_id' => $users->get('john', $users->first()),
                'reason' => 'Unclear API response format for paginated endpoints. Need specification document.',
                'priority' => 'medium',
                'status' => 'resolved',
                'assigned_to' => $users->get('teamlead', $users->first()),
                'resolution_note' => 'Added API specification document. All paginated endpoints follow standard format: {data: [], meta: {current_page, total}}',
                'resolved_at' => now()->subHours(2),
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subHours(2),
            ];
        }

        if (empty($blockers)) {
            echo "⚠️  Need at least 1 task to create blockers.\n";
            return;
        }

        $blockerIds = [];
        foreach ($blockers as $blocker) {
            $blockerId = DB::table('blockers')->insertGetId($blocker);
            $blockerIds[] = $blockerId;
        }

        echo "✅ Created " . count($blockerIds) . " sample blockers\n";

        // Add comments to some blockers
        if (count($blockerIds) >= 3 && isset($users['admin']) && isset($users['jane'])) {
            DB::table('blocker_comments')->insert([
                [
                    'blocker_id' => $blockerIds[2],
                    'user_id' => $users->get('admin'),
                    'comment' => 'Checking server permissions. User needs to be added to www-data group.',
                    'created_at' => now()->subHours(11),
                    'updated_at' => now()->subHours(11),
                ],
                [
                    'blocker_id' => $blockerIds[2],
                    'user_id' => $users->get('jane'),
                    'comment' => 'Tried that but still same error. Could it be SELinux?',
                    'created_at' => now()->subHours(10),
                    'updated_at' => now()->subHours(10),
                ],
            ]);
            echo "✅ Added sample comments\n";
        }

        // Add notifications
        if (count($blockerIds) > 0) {
            $notifications = [];
            
            if (isset($users['teamlead'])) {
                $notifications[] = [
                    'user_id' => $users->get('teamlead'),
                    'type' => 'blocker_reported',
                    'title' => 'New Critical Blocker',
                    'message' => 'A critical blocker was reported',
                    'data' => json_encode(['blocker_id' => $blockerIds[0]]),
                    'read_at' => null,
                    'created_at' => now()->subHours(26),
                    'updated_at' => now()->subHours(26),
                ];
            }

            if (!empty($notifications)) {
                DB::table('notifications')->insert($notifications);
                echo "✅ Added " . count($notifications) . " notifications\n";
            }
        }

        echo "\n========================================\n";
        echo "✅ DEMO DATA CREATED!\n";
        echo "========================================\n\n";
        echo "📊 Created:\n";
        echo "   - " . count($blockerIds) . " blockers\n";
        echo "   - Comments and notifications\n\n";
        echo "🔐 Login credentials:\n";
        echo "   Admin     : admin / admin123\n";
        echo "   Team Lead : teamlead / teamlead123\n";
        echo "   Developer : john / john123\n\n";
        echo "🚀 Test the blocker feature:\n";
        echo "   1. Login to web/mobile app\n";
        echo "   2. View blockers list\n";
        echo "   3. Create new blocker on any task\n\n";
    }
}
