-- Rollback script to undo migrations
-- WARNING: This will delete data! Use with caution.

-- Migration 010
DROP TABLE IF EXISTS daily_stats;
DROP TABLE IF EXISTS exercise_analytics;
DROP TABLE IF EXISTS user_sessions;
DROP PROCEDURE IF EXISTS UpdateExerciseAnalytics;
DROP EVENT IF EXISTS update_daily_stats;

-- Migration 009
DROP INDEX IF EXISTS idx_users_email ON users;
DROP INDEX IF EXISTS idx_users_created ON users;
DROP INDEX IF EXISTS idx_users_total_xp ON users;
DROP INDEX IF EXISTS idx_users_subscription ON users;
DROP INDEX IF EXISTS idx_progress_user_completed ON user_progress;
DROP INDEX IF EXISTS idx_progress_lesson_user ON user_progress;
DROP INDEX IF EXISTS idx_progress_completed_at ON user_progress;
DROP INDEX IF EXISTS idx_exercises_lesson ON exercises;
DROP INDEX IF EXISTS idx_exercises_type ON exercises;
DROP INDEX IF EXISTS idx_leaderboard_xp ON leaderboard;
DROP INDEX IF EXISTS idx_leaderboard_streak ON leaderboard;
DROP INDEX IF EXISTS idx_responses_user_date ON activity_responses;
DROP INDEX IF EXISTS idx_responses_correct ON activity_responses;
DROP INDEX IF EXISTS idx_user_badges_earned ON user_badges;
DROP INDEX IF EXISTS idx_user_progress_summary ON user_progress;
DROP INDEX IF EXISTS idx_leaderboard_summary ON leaderboard;
ALTER TABLE lessons DROP INDEX ft_lesson_search;
ALTER TABLE exercises DROP INDEX ft_exercise_search;

-- Migration 008
DROP TABLE IF EXISTS payment_history;
DROP TABLE IF EXISTS user_subscriptions;
DROP TABLE IF EXISTS subscription_plans;
DROP EVENT IF EXISTS check_expired_subscriptions;

-- Migration 007
DROP TABLE IF EXISTS activity_feed;
DROP TABLE IF EXISTS friend_requests;
DROP TABLE IF EXISTS friends;

-- Migration 006
DROP TABLE IF EXISTS user_challenges;
DROP TABLE IF EXISTS challenges;
DROP EVENT IF EXISTS assign_daily_challenges;

-- Migration 005
DROP TABLE IF EXISTS monthly_summaries;
DROP TABLE IF EXISTS achievement_history;
DROP PROCEDURE IF EXISTS GenerateMonthlySummary;

-- Migration 004
DROP TABLE IF EXISTS user_goals;
DROP TABLE IF EXISTS user_preferences;

-- Migration 003
DROP TABLE IF EXISTS lesson_milestones;
ALTER TABLE lessons 
DROP COLUMN IF EXISTS difficulty,
DROP COLUMN IF EXISTS estimated_minutes,
DROP COLUMN IF EXISTS required_xp,
DROP COLUMN IF EXISTS is_premium,
DROP COLUMN IF EXISTS tags,
DROP COLUMN IF EXISTS prerequisites;
ALTER TABLE exercises
DROP COLUMN IF EXISTS difficulty,
DROP COLUMN IF EXISTS time_limit_seconds,
DROP COLUMN IF EXISTS points_bonus;

-- Migration 002
DROP TABLE IF EXISTS badge_progress;
DROP PROCEDURE IF EXISTS UpdateBadgeProgress;
ALTER TABLE badges 
DROP COLUMN IF EXISTS requirement_type,
DROP COLUMN IF EXISTS requirement_value,
DROP COLUMN IF EXISTS requirement_operator,
DROP COLUMN IF EXISTS badge_category,
DROP COLUMN IF EXISTS is_hidden,
DROP COLUMN IF EXISTS order_priority;

-- Migration 001
DROP TABLE IF EXISTS streak_history;
ALTER TABLE users 
DROP COLUMN IF EXISTS current_streak,
DROP COLUMN IF EXISTS longest_streak,
DROP COLUMN IF EXISTS last_activity,
DROP COLUMN IF EXISTS total_xp,
DROP COLUMN IF EXISTS daily_goal;
DROP TRIGGER IF EXISTS update_streak_on_activity;