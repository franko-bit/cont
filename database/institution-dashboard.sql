-- Institution dashboard database objects for playmates
-- Run this against the playmates database.

CREATE TABLE IF NOT EXISTS institutions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NULL,
    assessment_model VARCHAR(255) NULL,
    default_passing_score INT DEFAULT 70,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS institution_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    institution_id INT NOT NULL,
    user_id INT NOT NULL,
    role ENUM('admin','manager','proctor','observer') DEFAULT 'admin',
    is_active BOOLEAN DEFAULT TRUE,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (institution_id) REFERENCES institutions(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uq_institution_user (institution_id, user_id),
    INDEX idx_institution_role (institution_id, role)
);

CREATE TABLE IF NOT EXISTS institution_assessments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    institution_id INT NOT NULL,
    cohort_id INT NULL,
    title VARCHAR(255) NOT NULL,
    invite_code VARCHAR(100) NOT NULL UNIQUE,
    invite_link VARCHAR(500) NOT NULL UNIQUE,
    language_pair VARCHAR(20) NULL,
    exam_level INT NULL,
    duration_minutes INT DEFAULT 60,
    skills VARCHAR(255) NULL,
    passing_score INT DEFAULT 70,
    assessment_model VARCHAR(255) NULL,
    total_questions INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (institution_id) REFERENCES institutions(id) ON DELETE CASCADE,
    INDEX idx_institution_assessment (institution_id, is_active),
    INDEX idx_cohort_assessment (cohort_id)
);

CREATE TABLE IF NOT EXISTS institution_candidates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    institution_id INT NOT NULL,
    cohort_id INT NULL,
    assessment_id INT NOT NULL,
    user_id INT NULL,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    invited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending','started','completed','failed','cancelled') DEFAULT 'pending',
    score DECIMAL(5,2) NULL,
    time_taken_seconds INT NULL,
    attempts INT DEFAULT 0,
    exam_attempt_id INT NULL,
    last_activity TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (institution_id) REFERENCES institutions(id) ON DELETE CASCADE,
    FOREIGN KEY (assessment_id) REFERENCES institution_assessments(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (exam_attempt_id) REFERENCES exam_attempts(id) ON DELETE SET NULL,
    INDEX idx_institution_candidate (institution_id, assessment_id),
    INDEX idx_candidate_status (status),
    INDEX idx_candidate_email (email)
);

CREATE TABLE IF NOT EXISTS institution_popcorns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    institution_id INT NOT NULL,
    created_by INT NULL,
    school_name VARCHAR(255) NOT NULL,
    school_prefix VARCHAR(50) NOT NULL DEFAULT '',
    popcorn_code VARCHAR(100) NOT NULL UNIQUE,
    popcorn_count INT NOT NULL DEFAULT 0,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (institution_id) REFERENCES institutions(id) ON DELETE CASCADE,
    INDEX idx_institution_popcorns (institution_id),
    INDEX idx_created_by (created_by)
);

-- Optional: if you want exam_attempts to track the institution invite and candidate relationship,
-- add these columns manually using your MySQL client.
--
-- ALTER TABLE exam_attempts
--     ADD COLUMN invite_code VARCHAR(100) NULL,
--     ADD COLUMN institution_id INT NULL,
--     ADD COLUMN assessment_id INT NULL,
--     ADD COLUMN candidate_id INT NULL;
--
-- ALTER TABLE exam_attempts
--     ADD CONSTRAINT fk_exam_attempts_institution FOREIGN KEY (institution_id) REFERENCES institutions(id) ON DELETE SET NULL,
--     ADD CONSTRAINT fk_exam_attempts_assessment FOREIGN KEY (assessment_id) REFERENCES institution_assessments(id) ON DELETE SET NULL,
--     ADD CONSTRAINT fk_exam_attempts_candidate FOREIGN KEY (candidate_id) REFERENCES institution_candidates(id) ON DELETE SET NULL;
