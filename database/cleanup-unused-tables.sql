-- ============================================
-- CLEANUP SCRIPT - Remove Unused Tables
-- ============================================
-- This script removes tables that are not actively used by the current system
-- Execution date: May 22, 2026
--
-- CURRENT REMOVAL CANDIDATES:
-- 1. exam_results - Scoring breakdown never populated; all data in certificates instead
-- 2. exam_sessions - Legacy table; replaced by exam_attempts  
-- 3. institution_users - Role-based access designed but never implemented
--
-- CONDITIONAL REMOVAL:
-- 4. exam_questions - Currently used by legacy take-exam.php path
--    - CAN BE REMOVED if you delete frontend/take-exam.php and backend/gradeExam()
--    - REQUIRED if you keep take-exam.php as fallback exam interface
--    - Current active path uses lessonexam.php + YAML files for questions
--
-- WARNING: This is destructive. Backup your database before running!
-- Run this script only after verifying your code dependencies.

-- Step 1: Drop dependent foreign keys first
-- Check if exam_answers has FK to exam_questions
ALTER TABLE exam_answers DROP FOREIGN KEY IF EXISTS exam_answers_ibfk_2;

-- Step 2: Drop the main unused tables
DROP TABLE IF EXISTS exam_results;
DROP TABLE IF EXISTS exam_sessions;
DROP TABLE IF EXISTS institution_users;

-- Step 3: OPTIONAL - Drop exam_questions if you've removed take-exam.php
-- Uncomment the line below only if you no longer use the legacy take-exam.php interface
-- DROP TABLE IF EXISTS exam_questions;

-- Step 4: Verify remaining tables (should be 10-11 tables)
SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME IN (
    'exams', 'exam_attempts', 'exam_answers', 'exam_verifications', 'certificates',
    'exam_questions',  -- Keep unless taking exam via YAML-only path
    'institutions', 'institution_assessments', 'institution_candidates', 'institution_popcorns', 
    'popcorn_applications'
)
ORDER BY TABLE_NAME;

-- ============================================
-- ACTIVE DATABASE STRUCTURE AFTER CLEANUP
-- ============================================
--
-- CORE EXAM TABLES (5):
-- - exams (exam definitions: language pairs, levels, passing scores)
-- - exam_attempts (exam sessions: user attempts, verification status, scores)
-- - exam_answers (user responses: which answers students gave)
-- - exam_questions (question bank: KEEP if using take-exam.php, otherwise REMOVE)
-- - certificates (issued certificates: student name, score, status, approval)
--
-- PROCTORING & VERIFICATION (1):
-- - exam_verifications (verification records: ID photos, selfies, device checks)
--
-- INSTITUTION MANAGEMENT (4):
-- - institutions (org records)
-- - institution_assessments (curated exams per institution)
-- - institution_candidates (applicants registered for assessment)
-- - institution_popcorns (popcorn code inventory)
--
-- APPLICATION (1):
-- - popcorn_applications (records of code usage)
--
-- REMOVED TABLES:
-- ✓ exam_results (never populated; data in certificates)
-- ✓ exam_sessions (legacy; replaced by exam_attempts)
-- ✓ institution_users (never implemented)
-- ☐ exam_questions (optional; needed only for take-exam.php legacy path)
--
-- Total Active Tables: 11 (or 10 if exam_questions removed)
-- Total Removed Tables: 4 (or 3 if keeping exam_questions)
