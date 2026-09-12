-- Migration: Add expected columns to exam_results so backend inserts succeed
-- Run as a DB admin (phpMyAdmin or mysql CLI)

-- Add missing columns (MySQL 8+ supports IF NOT EXISTS)
ALTER TABLE exam_results
  ADD COLUMN IF NOT EXISTS attempt_id INT NULL,
  ADD COLUMN IF NOT EXISTS user_id INT NULL,
  ADD COLUMN IF NOT EXISTS exam_id INT NULL,
  ADD COLUMN IF NOT EXISTS reading_score DECIMAL(5,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS listening_score DECIMAL(5,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS translation_score DECIMAL(5,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS speaking_score DECIMAL(5,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS writing_score DECIMAL(5,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS overall_score DECIMAL(5,2) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS strengths JSON NULL,
  ADD COLUMN IF NOT EXISTS weaknesses JSON NULL,
  ADD COLUMN IF NOT EXISTS recommendations JSON NULL,
  ADD COLUMN IF NOT EXISTS graded_at TIMESTAMP NULL;

-- Add a unique index on attempt_id so backend can safely use ON DUPLICATE KEY
-- If this fails (permission/index exists), it's safe to ignore the error
ALTER TABLE exam_results ADD UNIQUE INDEX ux_exam_results_attempt (attempt_id);

-- Optional: if your DB uses session_id instead of attempt_id and you want to keep it, you can map values later:
-- UPDATE exam_results er JOIN exam_attempts ea ON er.session_id = ea.id SET er.attempt_id = ea.id WHERE er.attempt_id IS NULL;

-- Done
COMMIT;
