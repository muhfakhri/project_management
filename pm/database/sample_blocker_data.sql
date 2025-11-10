-- ============================================
-- Quick SQL Setup for Blocker Demo
-- ============================================
-- Run this if you want to quickly populate sample data
-- without going through web interface

-- Step 1: Check current state
SELECT 'Users:' as info, COUNT(*) as count FROM users
UNION ALL
SELECT 'Projects:', COUNT(*) FROM projects
UNION ALL
SELECT 'Cards:', COUNT(*) FROM cards
UNION ALL
SELECT 'Blockers:', COUNT(*) FROM blockers;

-- ============================================
-- IMPORTANT: Check your actual schema first!
-- ============================================
-- Run these to see actual column names:
-- DESCRIBE projects;
-- DESCRIBE boards;
-- DESCRIBE cards;

-- ============================================
-- Sample Data Creation (ADJUST TO YOUR SCHEMA)
-- ============================================

-- Example 1: If your projects table has these columns:
-- INSERT INTO projects (project_name, description, status, created_by, created_at, updated_at)
-- VALUES ('Demo Project', 'Sample project for blocker testing', 'active', 1, NOW(), NOW());

-- Example 2: If your cards table structure (common scenario):
-- INSERT INTO cards (board_id, card_name, description, status, assigned_to, created_at, updated_at)
-- VALUES (1, 'Implement OAuth Login', 'Need to add Google OAuth authentication', 'in_progress', 3, NOW(), NOW());

-- ============================================
-- Create Blockers (This should work as-is)
-- ============================================

-- Critical Blocker (Overdue - created 26 hours ago)
INSERT INTO blockers (card_id, reporter_id, reason, priority, status, created_at, updated_at)
VALUES (
    1, -- YOUR_CARD_ID: Change this to actual card ID
    3, -- john's user_id
    'Missing OAuth credentials for Google authentication. Need access to Google Cloud Console to create OAuth client ID and secret.',
    'critical',
    'reported',
    DATE_SUB(NOW(), INTERVAL 26 HOUR),
    DATE_SUB(NOW(), INTERVAL 26 HOUR)
);

-- High Priority Blocker (Assigned)
INSERT INTO blockers (card_id, reporter_id, reason, priority, status, assigned_to, created_at, updated_at)
VALUES (
    2, -- YOUR_CARD_ID
    5, -- alice's user_id
    'Design assets not yet approved by client. Need feedback on color scheme and logo placement before proceeding with implementation.',
    'high',
    'assigned',
    2, -- Assigned to teamlead
    DATE_SUB(NOW(), INTERVAL 5 HOUR),
    DATE_SUB(NOW(), INTERVAL 5 HOUR)
);

-- High Priority Blocker (In Progress)
INSERT INTO blockers (card_id, reporter_id, reason, priority, status, assigned_to, created_at, updated_at)
VALUES (
    3, -- YOUR_CARD_ID
    4, -- jane's user_id
    'Docker deployment failing on production server. Getting "permission denied" error when trying to access /var/www directory. SELinux might be blocking.',
    'high',
    'in_progress',
    1, -- Assigned to admin
    DATE_SUB(NOW(), INTERVAL 12 HOUR),
    DATE_SUB(NOW(), INTERVAL 1 HOUR)
);

-- Medium Priority Blocker (Resolved)
INSERT INTO blockers (card_id, reporter_id, reason, priority, status, assigned_to, resolution_note, resolved_at, created_at, updated_at)
VALUES (
    4, -- YOUR_CARD_ID
    3, -- john's user_id
    'Unclear API response format for paginated endpoints. Need specification document showing consistent format across all endpoints.',
    'medium',
    'resolved',
    2, -- Assigned to teamlead
    'Added comprehensive API specification document. All paginated endpoints now follow standard format: {data: [], meta: {current_page, last_page, per_page, total}}',
    DATE_SUB(NOW(), INTERVAL 2 HOUR),
    DATE_SUB(NOW(), INTERVAL 24 HOUR),
    DATE_SUB(NOW(), INTERVAL 2 HOUR)
);

-- ============================================
-- Add Comments to Blockers
-- ============================================

-- Comments on the "Docker deployment" blocker
INSERT INTO blocker_comments (blocker_id, user_id, comment, created_at, updated_at)
VALUES 
(3, 1, 'Checking server permissions now. User needs to be added to www-data group.', DATE_SUB(NOW(), INTERVAL 11 HOUR), DATE_SUB(NOW(), INTERVAL 11 HOUR)),
(3, 4, 'Tried adding to www-data group but still same error. Could it be SELinux blocking the access?', DATE_SUB(NOW(), INTERVAL 10 HOUR), DATE_SUB(NOW(), INTERVAL 10 HOUR)),
(3, 1, 'Yes, SELinux is enforcing. Running: sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www(/.*)?"', DATE_SUB(NOW(), INTERVAL 9 HOUR), DATE_SUB(NOW(), INTERVAL 9 HOUR));

-- Comments on the "OAuth" blocker
INSERT INTO blocker_comments (blocker_id, user_id, comment, created_at, updated_at)
VALUES 
(1, 2, 'I have access to Google Cloud Console. Creating OAuth client now.', DATE_SUB(NOW(), INTERVAL 25 HOUR), DATE_SUB(NOW(), INTERVAL 25 HOUR)),
(1, 3, 'Thank you! Also need the redirect URI to be whitelisted.', DATE_SUB(NOW(), INTERVAL 24 HOUR), DATE_SUB(NOW(), INTERVAL 24 HOUR));

-- ============================================
-- Add Notifications
-- ============================================

-- Notification for team lead about critical blocker
INSERT INTO notifications (user_id, type, title, message, data, read_at, created_at, updated_at)
VALUES (
    2, -- teamlead
    'blocker_reported',
    'New Critical Blocker Reported',
    'John Developer reported a critical blocker on task "Implement OAuth Login"',
    JSON_OBJECT('blocker_id', 1, 'priority', 'critical', 'reporter', 'john'),
    NULL, -- unread
    DATE_SUB(NOW(), INTERVAL 26 HOUR),
    DATE_SUB(NOW(), INTERVAL 26 HOUR)
);

-- Notification for assignee
INSERT INTO notifications (user_id, type, title, message, data, read_at, created_at, updated_at)
VALUES (
    1, -- admin
    'blocker_assigned',
    'Blocker Assigned to You',
    'Team Lead assigned you to help with Docker deployment blocker',
    JSON_OBJECT('blocker_id', 3, 'priority', 'high', 'assigner', 'teamlead'),
    NULL,
    DATE_SUB(NOW(), INTERVAL 12 HOUR),
    DATE_SUB(NOW(), INTERVAL 12 HOUR)
);

-- Notification for reporter when blocker resolved
INSERT INTO notifications (user_id, type, title, message, data, read_at, created_at, updated_at)
VALUES (
    3, -- john
    'blocker_resolved',
    'Your Blocker Has Been Resolved',
    'Team Lead resolved your blocker about API documentation',
    JSON_OBJECT('blocker_id', 4, 'resolver', 'teamlead'),
    DATE_SUB(NOW(), INTERVAL 1 HOUR), -- read
    DATE_SUB(NOW(), INTERVAL 2 HOUR),
    DATE_SUB(NOW(), INTERVAL 1 HOUR)
);

-- ============================================
-- Verify Results
-- ============================================

SELECT 'Summary:' as info, '' as details
UNION ALL
SELECT 'Blockers Created:', COUNT(*) FROM blockers
UNION ALL
SELECT 'Comments Added:', COUNT(*) FROM blocker_comments
UNION ALL
SELECT 'Notifications Sent:', COUNT(*) FROM notifications WHERE type LIKE 'blocker_%';

-- ============================================
-- View Sample Data
-- ============================================

-- All blockers with details
SELECT 
    b.id,
    b.priority,
    b.status,
    u.username as reporter,
    u2.username as assigned_to,
    LEFT(b.reason, 50) as reason_preview,
    b.created_at
FROM blockers b
JOIN users u ON b.reporter_id = u.user_id
LEFT JOIN users u2 ON b.assigned_to = u2.user_id
ORDER BY 
    FIELD(b.priority, 'critical', 'high', 'medium', 'low'),
    b.created_at DESC;
