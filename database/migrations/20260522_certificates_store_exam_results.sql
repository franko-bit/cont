-- Migration: Store completed exam result details in certificates instead of exam_results
-- Run as a DB admin (phpMyAdmin or mysql CLI)

ALTER TABLE certificates
  MODIFY COLUMN certificate_id VARCHAR(50) NULL,
  MODIFY COLUMN language_pair VARCHAR(100) NOT NULL,
  ADD COLUMN IF NOT EXISTS exam_id INT NULL AFTER exam_attempt_id,
  ADD COLUMN IF NOT EXISTS session_id INT NULL AFTER exam_id,
  ADD COLUMN IF NOT EXISTS overall_score DECIMAL(5,2) DEFAULT 0 AFTER level_number,
  ADD COLUMN IF NOT EXISTS total_questions INT DEFAULT 0 AFTER overall_score,
  ADD COLUMN IF NOT EXISTS correct_answers INT DEFAULT 0 AFTER total_questions,
  ADD COLUMN IF NOT EXISTS percentage DECIMAL(5,2) DEFAULT 0 AFTER correct_answers,
  ADD COLUMN IF NOT EXISTS passed TINYINT(1) DEFAULT 0 AFTER percentage,
  ADD COLUMN IF NOT EXISTS passed_score INT DEFAULT 0 AFTER passed,
  ADD COLUMN IF NOT EXISTS strengths JSON NULL AFTER passed_score,
  ADD COLUMN IF NOT EXISTS weaknesses JSON NULL AFTER strengths,
  ADD COLUMN IF NOT EXISTS recommendations JSON NULL AFTER weaknesses,
  ADD COLUMN IF NOT EXISTS graded_at TIMESTAMP NULL AFTER recommendations,
  ADD COLUMN IF NOT EXISTS status VARCHAR(20) DEFAULT 'pending' AFTER graded_at,
  ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

ALTER TABLE certificates
  ADD UNIQUE INDEX IF NOT EXISTS ux_certificates_attempt (exam_attempt_id);

COMMIT;
