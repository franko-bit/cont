-- ============================================
-- EXAM SYSTEM TABLES
-- ============================================

-- Exam definitions
CREATE TABLE IF NOT EXISTS exams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    language_pair VARCHAR(20) NOT NULL, -- 'en-rw', 'fr-rw', 'rw-en', 'rw-fr'
    level_number INT NOT NULL, -- 1-6
    title VARCHAR(255) NOT NULL,
    description TEXT,
    exam_language VARCHAR(10) NOT NULL, -- 'en' or 'fr' (target exam language)
    duration_minutes INT DEFAULT 60,
    passing_score INT DEFAULT 60,
    total_questions INT DEFAULT 25,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_language_pair (language_pair),
    INDEX idx_level (level_number)
);

-- Exam attempts (user exam sessions)
CREATE TABLE IF NOT EXISTS exam_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    exam_id INT NOT NULL,
    status ENUM('pending', 'verification', 'in_progress', 'submitted', 'graded', 'cancelled') DEFAULT 'pending',
    started_at TIMESTAMP NULL,
    submitted_at TIMESTAMP NULL,
    graded_at TIMESTAMP NULL,
    score DECIMAL(5,2) DEFAULT 0,
    passing_score INT DEFAULT 60,
    passed BOOLEAN DEFAULT FALSE,
    certificate_id VARCHAR(100) NULL,
    time_remaining_seconds INT DEFAULT 0,
    current_question_index INT DEFAULT 0,
    device_checked BOOLEAN DEFAULT FALSE,
    verification_status ENUM('pending', 'id_verified', 'selfie_verified', 'fully_verified', 'failed') DEFAULT 'pending',
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
    INDEX idx_user_exam (user_id, exam_id),
    INDEX idx_status (status)
);

-- Exam questions (question bank)
CREATE TABLE IF NOT EXISTS exam_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_id INT NOT NULL,
    language_pair VARCHAR(20) NOT NULL,
    level_number INT NOT NULL,
    question_type ENUM('reading', 'listening', 'translation', 'speaking', 'writing') NOT NULL,
    question_text TEXT NOT NULL,
    question_audio TEXT, -- TTS or audio file URL
    question_image TEXT, -- Optional image
    correct_answer TEXT NOT NULL,
    options JSON, -- Multiple choice options if applicable
    hints TEXT,
    difficulty ENUM('easy', 'medium', 'hard') DEFAULT 'medium',
    points INT DEFAULT 10,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
    INDEX idx_exam (exam_id),
    INDEX idx_type (question_type),
    INDEX idx_difficulty (difficulty)
);

-- User answers for exam attempts
CREATE TABLE IF NOT EXISTS exam_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    attempt_id INT NOT NULL,
    question_id INT NOT NULL,
    answer_text TEXT,
    answer_audio TEXT, -- For speaking answers
    is_correct BOOLEAN DEFAULT FALSE,
    points_earned INT DEFAULT 0,
    time_spent_seconds INT DEFAULT 0,
    answered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (attempt_id) REFERENCES exam_attempts(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES exam_questions(id) ON DELETE CASCADE,
    INDEX idx_attempt (attempt_id)
);

-- Verification records
CREATE TABLE IF NOT EXISTS exam_verifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    attempt_id INT NOT NULL,
    verification_type ENUM('id_photo', 'selfie', 'device_check') NOT NULL,
    id_photo_url VARCHAR(500),
    selfie_photo_url VARCHAR(500),
    id_number VARCHAR(50),
    id_name VARCHAR(255),
    face_match_score DECIMAL(5,2),
    device_camera_ok BOOLEAN DEFAULT FALSE,
    device_microphone_ok BOOLEAN DEFAULT FALSE,
    device_fullscreen_ok BOOLEAN DEFAULT FALSE,
    verification_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    rejection_reason TEXT,
    verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (attempt_id) REFERENCES exam_attempts(id) ON DELETE CASCADE,
    INDEX idx_attempt_type (attempt_id, verification_type)
);

-- Certificates
CREATE TABLE IF NOT EXISTS certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    certificate_id VARCHAR(50) UNIQUE NULL, -- e.g., 'CERT-ENRW-2026-00001'
    student_name VARCHAR(255) NULL,
    user_id INT NOT NULL,
    exam_attempt_id INT NOT NULL,
    exam_id INT NULL,
    session_id INT NULL,
    language_pair VARCHAR(20) NOT NULL,
    level_number INT NOT NULL,
    score DECIMAL(5,2) DEFAULT 0,
    passing_score INT DEFAULT 0,
    overall_score DECIMAL(5,2) DEFAULT 0,
    total_questions INT DEFAULT 0,
    correct_answers INT DEFAULT 0,
    percentage DECIMAL(5,2) DEFAULT 0,
    passed TINYINT(1) DEFAULT 0,
    passed_score INT DEFAULT 0,
    strengths JSON NULL,
    weaknesses JSON NULL,
    recommendations JSON NULL,
    graded_at TIMESTAMP NULL,
    status VARCHAR(20) DEFAULT 'pending',
    issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL, -- NULL = never expires
    certificate_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (exam_attempt_id) REFERENCES exam_attempts(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_certificate_id (certificate_id),
    UNIQUE INDEX ux_certificates_attempt (exam_attempt_id)
);

-- Exam results / Analytics
CREATE TABLE IF NOT EXISTS exam_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    attempt_id INT NOT NULL,
    user_id INT NOT NULL,
    exam_id INT NOT NULL,
    reading_score DECIMAL(5,2) DEFAULT 0,
    listening_score DECIMAL(5,2) DEFAULT 0,
    translation_score DECIMAL(5,2) DEFAULT 0,
    speaking_score DECIMAL(5,2) DEFAULT 0,
    writing_score DECIMAL(5,2) DEFAULT 0,
    overall_score DECIMAL(5,2) DEFAULT 0,
    strengths JSON, -- Array of strong areas
    weaknesses JSON, -- Array of weak areas
    recommendations JSON, -- Recommended next steps
    graded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (attempt_id) REFERENCES exam_attempts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE
);

-- ============================================
-- SEED EXAM DATA
-- ============================================

-- Insert exam for English-Kinyarwanda path (Level 1-6)
INSERT INTO exams (language_pair, level_number, title, description, exam_language, duration_minutes, passing_score, total_questions) VALUES
('en-rw', 1, 'English-Kinyarwanda Level 1 Assessment', 'Assess your mastery of Level 1 vocabulary and basic grammar', 'en', 45, 60, 20),
('en-rw', 2, 'English-Kinyarwanda Level 2 Assessment', 'Assess your mastery of Level 2 vocabulary and grammar', 'en', 50, 60, 22),
('en-rw', 3, 'English-Kinyarwanda Level 3 Assessment', 'Assess your mastery of Level 3 vocabulary and grammar', 'en', 55, 65, 24),
('en-rw', 4, 'English-Kinyarwanda Level 4 Assessment', 'Assess your mastery of Level 4 vocabulary and grammar', 'en', 60, 65, 25),
('en-rw', 5, 'English-Kinyarwanda Level 5 Assessment', 'Assess your mastery of Level 5 vocabulary and grammar', 'en', 65, 70, 28),
('en-rw', 6, 'English-Kinyarwanda Level 6 Assessment', 'Assess your mastery of Level 6 vocabulary and grammar', 'en', 75, 70, 30),

('rw-en', 1, 'Kinyarwanda-English Level 1 Assessment', 'Assess your mastery of Level 1 Kinyarwanda vocabulary', 'en', 45, 60, 20),
('rw-en', 2, 'Kinyarwanda-English Level 2 Assessment', 'Assess your mastery of Level 2 Kinyarwanda vocabulary', 'en', 50, 60, 22),
('rw-en', 3, 'Kinyarwanda-English Level 3 Assessment', 'Assess your mastery of Level 3 Kinyarwanda vocabulary', 'en', 55, 65, 24),
('rw-en', 4, 'Kinyarwanda-English Level 4 Assessment', 'Assess your mastery of Level 4 Kinyarwanda vocabulary', 'en', 60, 65, 25),
('rw-en', 5, 'Kinyarwanda-English Level 5 Assessment', 'Assess your mastery of Level 5 Kinyarwanda vocabulary', 'en', 65, 70, 28),
('rw-en', 6, 'Kinyarwanda-English Level 6 Assessment', 'Assess your mastery of Level 6 Kinyarwanda vocabulary', 'en', 75, 70, 30),

('fr-rw', 1, 'French-Kinyarwanda Level 1 Assessment', 'Assess your mastery of Level 1 French vocabulary', 'fr', 45, 60, 20),
('fr-rw', 2, 'French-Kinyarwanda Level 2 Assessment', 'Assess your mastery of Level 2 French vocabulary', 'fr', 50, 60, 22),
('fr-rw', 3, 'French-Kinyarwanda Level 3 Assessment', 'Assess your mastery of Level 3 French vocabulary', 'fr', 55, 65, 24),
('fr-rw', 4, 'French-Kinyarwanda Level 4 Assessment', 'Assess your mastery of Level 4 French vocabulary', 'fr', 60, 65, 25),
('fr-rw', 5, 'French-Kinyarwanda Level 5 Assessment', 'Assess your mastery of Level 5 French vocabulary', 'fr', 65, 70, 28),
('fr-rw', 6, 'French-Kinyarwanda Level 6 Assessment', 'Assess your mastery of Level 6 French vocabulary', 'fr', 75, 70, 30),

('rw-fr', 1, 'Kinyarwanda-French Level 1 Assessment', 'Assess your mastery of Level 1 Kinyarwanda vocabulary for French speakers', 'fr', 45, 60, 20),
('rw-fr', 2, 'Kinyarwanda-French Level 2 Assessment', 'Assess your mastery of Level 2 Kinyarwanda vocabulary', 'fr', 50, 60, 22),
('rw-fr', 3, 'Kinyarwanda-French Level 3 Assessment', 'Assess your mastery of Level 3 Kinyarwanda vocabulary', 'fr', 55, 65, 24),
('rw-fr', 4, 'Kinyarwanda-French Level 4 Assessment', 'Assess your mastery of Level 4 Kinyarwanda vocabulary', 'fr', 60, 65, 25),
('rw-fr', 5, 'Kinyarwanda-French Level 5 Assessment', 'Assess your mastery of Level 5 Kinyarwanda vocabulary', 'fr', 65, 70, 28),
('rw-fr', 6, 'Kinyarwanda-French Level 6 Assessment', 'Assess your mastery of Level 6 Kinyarwanda vocabulary', 'fr', 75, 70, 30);

-- Insert sample exam questions
INSERT INTO exam_questions (exam_id, language_pair, level_number, question_type, question_text, correct_answer, options, difficulty, points) VALUES
-- EN-RW Level 1 Reading questions
(1, 'en-rw', 1, 'reading', 'What does "Muraho" mean in English?', 'Hello', '["Hello", "Goodbye", "Thank you", "Please"]', 'easy', 10),
(1, 'en-rw', 1, 'reading', 'How do you say "Goodbye" in Kinyarwanda?', 'Murabeho', '["Muraho", "Murabeho", "Urakoze", "Nyamuneka"]', 'easy', 10),
(1, 'en-rw', 1, 'reading', 'What is "Thank you" in Kinyarwanda?', 'Urakoze', '["Muraho", "Murabeho", "Urakoze", "Mwaramutse"]', 'easy', 10),
(1, 'en-rw', 1, 'reading', 'Translate: "Good morning"', 'Mwaramutse', '["Mwaramutse", "Mwirikire", "Muraho", "Urakoze"]', 'easy', 10),
(1, 'en-rw', 1, 'reading', 'What does "Yego" mean?', 'Yes', '["Yes", "No", "Maybe", "Please"]', 'easy', 10),
(1, 'en-rw', 1, 'translation', 'Translate to Kinyarwanda: "How are you?"', 'Amakuru yawe ni?', '["Amakuru yawe ni?", "Wakane?", "Uri mute?", "Byapewe"]', 'medium', 15),
(1, 'en-rw', 1, 'translation', 'Translate to Kinyarwanda: "I am fine"', 'Ndagira amahoro', '["Ndagira amahoro", "Ndakureba", "Ndashungaye", "Ndabishimiye"]', 'medium', 15),
(1, 'en-rw', 1, 'reading', 'What does "Ibiro" mean?', 'Things', '["People", "Things", "Place", "Time"]', 'medium', 10),
(1, 'en-rw', 1, 'reading', 'Translate: "Nshuti"', 'Friend', '["Family", "Friend", "Teacher", "Doctor"]', 'easy', 10),
(1, 'en-rw', 1, 'reading', 'What is "Umusozi" in English?', 'Hill', '["Mountain", "Hill", "Valley", "River"]', 'medium', 10),

-- EN-RW Level 1 Listening questions (text-to-speech reference)
(1, 'en-rw', 1, 'listening', 'Listen and select what you hear: "Muraho neza"', 'Hello', '["Muraho neza", "Murabeho", "Urakoze", "Mwaramutse"]', 'easy', 10),
(1, 'en-rw', 1, 'listening', 'Listen and select what you hear: "Ndagira amahoro"', 'I am fine', '["I am fine", "I am sad", "I am tired", "I am happy"]', 'medium', 10),
(1, 'en-rw', 1, 'listening', 'Listen and select what you hear: "Amakuru yawe ni?"', 'How are you?', '["How are you?", "What is your name?", "Where are you going?", "How old are you?"]', 'medium', 10),

-- EN-RW Level 1 Writing/Speaking prompts
(1, 'en-rw', 1, 'writing', 'Write a simple greeting in Kinyarwanda that you would use when meeting someone in the morning.', 'Mwaramutse', NULL, 'medium', 20),
(1, 'en-rw', 1, 'speaking', 'Record yourself saying "Thank you" in Kinyarwanda.', 'Urakoze', NULL, 'easy', 15),
(1, 'en-rw', 1, 'speaking', 'Introduce yourself in Kinyarwanda (say your name and greet the listener).', 'Njye...', NULL, 'medium', 25),

-- More Level 1 Reading
(1, 'en-rw', 1, 'reading', 'What does "Abana" mean?', 'Children', '["Children", "Adults", "Family", "Parents"]', 'easy', 10),
(1, 'en-rw', 1, 'reading', 'Translate: "Inka"', 'Cow', '["Cow", "Goat", "Sheep", "Dog"]', 'easy', 10),
(1, 'en-rw', 1, 'reading', 'What is "Amazi" in English?', 'Water', '["Fire", "Water", "Earth", "Air"]', 'easy', 10),
(1, 'en-rw', 1, 'reading', 'What does "Urugero" mean?', 'Example', '["Example", "Question", "Answer", "Lesson"]', 'medium', 10),
(1, 'en-rw', 1, 'reading', 'Translate: "Ibyiringitungo"', 'Knowledge', '["Knowledge", "Wisdom", "Learning", "Study"]', 'hard', 15);

-- Level 2 questions
INSERT INTO exam_questions (exam_id, language_pair, level_number, question_type, question_text, correct_answer, options, difficulty, points) VALUES
(2, 'en-rw', 2, 'reading', 'What does "Kunywa" mean?', 'To drink', '["To eat", "To drink", "To cook", "To buy"]', 'easy', 10),
(2, 'en-rw', 2, 'reading', 'Translate: "Ibiryo"', 'Food', '["Food", "Water", "House", "Clothes"]', 'easy', 10),
(2, 'en-rw', 2, 'reading', 'What is "Imodoka" in English?', 'Car', '["Car", "Bus", "Bicycle", "Motorcycle"]', 'easy', 10),
(2, 'en-rw', 2, 'reading', 'What does "Kurya" mean?', 'To eat', '["To drink", "To eat", "To sleep", "To walk"]', 'easy', 10),
(2, 'en-rw', 2, 'reading', 'Translate: "Ubuki"', 'Honey', '["Sugar", "Honey", "Salt", "Pepper"]', 'medium', 10),
(2, 'en-rw', 2, 'translation', 'Translate to Kinyarwanda: "I want to eat"', 'Nashaka kurya', '["Nashaka kurya", "Ndagira urwaye", "Nifuje ibyo kurya", "Ndiyemeze"]', 'medium', 15),
(2, 'en-rw', 2, 'translation', 'Translate to Kinyarwanda: "Where is the market?"', 'Aho isoko iri?', '["Aho isoko iri?", "Ninde waguze?", "Yahawe iki?", "Wakora iki?"]', 'medium', 15),
(2, 'en-rw', 2, 'listening', 'Listen: "Nashaka kunwa amazi"', 'I want to drink water', '["I want to drink water", "I want to eat food", "I need water", "Give me water"]', 'medium', 15),
(2, 'en-rw', 2, 'listening', 'Listen: "Aho uri?"', 'Where are you?', '["Where are you?", "Who are you?", "What are you doing?", "How are you?"]', 'easy', 10),
(2, 'en-rw', 2, 'reading', 'What does "Ishuri" mean?', 'School', '["School", "Hospital", "Church", "Market"]', 'easy', 10),
(2, 'en-rw', 2, 'reading', 'Translate: "Umusizi"', 'Singer', '["Singer", "Dancer", "Teacher", "Student"]', 'medium', 10),
(2, 'en-rw', 2, 'reading', 'What is "Inzara" in English?', 'Hunger', '["Thirst", "Hunger", "Tiredness", "Sleepiness"]', 'medium', 10),
(2, 'en-rw', 2, 'reading', 'What does "Kuva" mean?', 'To come from', '["To go to", "To come from", "To stay", "To leave"]', 'medium', 10),
(2, 'en-rw', 2, 'reading', 'Translate: "Ijuru"', 'Sky', '["Sky", "Ground", "Mountain", "Cloud"]', 'easy', 10),
(2, 'en-rw', 2, 'writing', 'Describe what you see in a typical Kinyarwanda market using at least 5 Kinyarwanda words.', 'isoko...', NULL, 'hard', 25),
(2, 'en-rw', 2, 'speaking', 'Explain in Kinyarwanda what you ate for breakfast today.', 'Narakiriye...', NULL, 'medium', 20),
(2, 'en-rw', 2, 'reading', 'What does "Kugura" mean?', 'To buy', '["To sell", "To buy", "To give", "To take"]', 'easy', 10),
(2, 'en-rw', 2, 'reading', 'Translate: "Umuvyeyi"', 'Parent', '["Child", "Parent", "Sibling", "Grandparent"]', 'medium', 10),
(2, 'en-rw', 2, 'reading', 'What is "Imbaho" in English?', 'Charcoal', '["Firewood", "Charcoal", "Coal", "Ash"]', 'medium', 10),
(2, 'en-rw', 2, 'reading', 'What does "Kubaho" mean?', 'To live', '["To die", "To live", "To sleep", "To wake up"]', 'medium', 10),
(2, 'en-rw', 2, 'reading', 'Translate: "Umuco"', 'Culture', '["Tradition", "Culture", "Language", "Custom"]', 'medium', 10),
(2, 'en-rw', 2, 'reading', 'What does "Iburira" mean?', 'Mirror', '["Window", "Door", "Mirror", "Wall"]', 'easy', 10);

-- Level 3 questions
INSERT INTO exam_questions (exam_id, language_pair, level_number, question_type, question_text, correct_answer, options, difficulty, points) VALUES
(3, 'en-rw', 3, 'reading', 'What does "Kugenda" mean?', 'To go or to walk', '["To run", "To go or to walk", "To stay", "To come"]', 'easy', 10),
(3, 'en-rw', 3, 'reading', 'Translate: "Ibikorwa bya buri munsi"', 'Daily activities', '["Morning routine", "Daily activities", "Weekly schedule", "Yearly plans"]', 'medium', 10),
(3, 'en-rw', 3, 'translation', 'Translate to Kinyarwanda: "I went to the hospital yesterday"', 'Yanko muri dispensaire ejo', '["Yanko muri dispensaire ejo", "Ndagiye mu bitaro ejo", "Yagwiye mu bitaro ejo", "Yafashije mu bitaro ejo"]', 'hard', 20),
(3, 'en-rw', 3, 'reading', 'What is "Indwara" in English?', 'Disease/illness', '["Health", "Disease/illness", "Medicine", "Doctor"]', 'easy', 10),
(3, 'en-rw', 3, 'reading', 'What does "Kuvura" mean?', 'To treat or to cure', '["To sicken", "To treat or to cure", "To diagnose", "To prevent"]', 'medium', 10),
(3, 'en-rw', 3, 'listening', 'Listen: "Ejo hazaba hafi y\'iminsi myinshi"', 'Yesterday was many days ago', '["Yesterday was many days ago", "Tomorrow will be many days", "Today is many days", "Days will pass soon"]', 'hard', 15),
(3, 'en-rw', 3, 'listening', 'Listen: "Yagiye yinjira mu isoko"', 'He/she entered the market', '["He/she left the market", "He/she entered the market", "He/she stayed at market", "He/she bought from market"]', 'medium', 10),
(3, 'en-rw', 3, 'reading', 'Translate: "Injira"', 'Enter', '["Exit", "Enter", "Stay", "Walk"]', 'easy', 10),
(3, 'en-rw', 3, 'reading', 'What does "Ahansi" mean?', 'Below/Under', '["Above", "Below/Under", "Inside", "Outside"]', 'easy', 10),
(3, 'en-rw', 3, 'reading', 'What is "Umugenzi" in English?', 'Friend', '["Enemy", "Stranger", "Friend", "Neighbor"]', 'easy', 10),
(3, 'en-rw', 3, 'translation', 'Translate to Kinyarwanda: "The book is on the table"', 'Igitabo kiri ku meza', '["Igitabo kiri ku meza", "Igitabo kirimo gupakurura", "Igitabo kirakongera", "Igitabo kirazima"]', 'medium', 15),
(3, 'en-rw', 3, 'reading', 'What does "Gukora" mean?', 'To do or to work', '["To play", "To do or to work", "To rest", "To watch"]', 'easy', 10),
(3, 'en-rw', 3, 'reading', 'Translate: "Umwaka"', 'Year', '["Month", "Week", "Year", "Day"]', 'easy', 10),
(3, 'en-rw', 3, 'reading', 'What is "Ubusanzwe" in English?', 'Usually/Normally', '["Always", "Usually/Normally", "Sometimes", "Never"]', 'medium', 10),
(3, 'en-rw', 3, 'reading', 'What does "Kugira nka" mean?', 'To be like / such as', '["To be equal to", "To be like / such as", "To prefer", "To compare"]', 'medium', 10),
(3, 'en-rw', 3, 'writing', 'Write a short paragraph in Kinyarwanda about your daily routine starting from morning.', 'Kuva mu gitondo...', NULL, 'hard', 25),
(3, 'en-rw', 3, 'speaking', 'Describe your neighborhood in Kinyarwanda using direction words (north, south, east, west).', 'Muri yunzenneri...', NULL, 'hard', 25),
(3, 'en-rw', 3, 'reading', 'What does "Kwigenga" mean?', 'To educate oneself', '["To teach others", "To educate oneself", "To study hard", "To learn quickly"]', 'hard', 15),
(3, 'en-rw', 3, 'reading', 'Translate: "Ibikorwa"', 'Activities/Actions', '["Thoughts", "Activities/Actions", "Words", "Feelings"]', 'easy', 10),
(3, 'en-rw', 3, 'reading', 'What is "Ubufasha" in English?', 'Help/Assistance', '["Problem", "Solution", "Help/Assistance", "Question"]', 'easy', 10),
(3, 'en-rw', 3, 'reading', 'What does "Gushaka" mean?', 'To want or to love', '["To need", "To want or to love", "To have", "To give"]', 'easy', 10),
(3, 'en-rw', 3, 'reading', 'Translate: "Imiterere"', 'Character/Nature', '["Appearance", "Character/Nature", "Behavior", "All of the above"]', 'medium', 10),
(3, 'en-rw', 3, 'reading', 'What does "Kwerekana" mean?', 'To show', '["To hide", "To show", "To look", "To see"]', 'easy', 10),
(3, 'en-rw', 3, 'reading', 'What is "Abatari" in English?', 'Those who are not', '["Everyone", "Someone", "Those who are not", "No one"]', 'medium', 10);
