-- ============================================
-- EXAM SYSTEM SCHEMA (SIMPLIFIED)
-- ============================================

-- 2. EXAM SESSIONS
-- Each exam attempt by a learner
CREATE TABLE IF NOT EXISTS exam_sessions (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  program_id INT NOT NULL,
  status ENUM('created', 'verified', 'in_progress', 'completed', 'failed') DEFAULT 'created',
  started_at TIMESTAMP NULL,
  finished_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_program (user_id, program_id),
  INDEX idx_status (status)
);

-- 3. EXAM QUESTIONS
-- Question bank for exams
CREATE TABLE IF NOT EXISTS exam_questions (
  id INT PRIMARY KEY AUTO_INCREMENT,
  program_id INT NOT NULL,
  question_type ENUM('reading', 'listening', 'writing', 'speaking', 'translation') NOT NULL,
  question_text TEXT NOT NULL,
  option_a TEXT,
  option_b TEXT,
  option_c TEXT,
  option_d TEXT,
  correct_answer TEXT NOT NULL,
  difficulty ENUM('easy', 'medium', 'hard') DEFAULT 'medium',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  INDEX idx_program_type (program_id, question_type),
  INDEX idx_difficulty (difficulty)
);

-- 5. EXAM ANSWERS
-- Stores learner answers
CREATE TABLE IF NOT EXISTS exam_answers (
  id INT PRIMARY KEY AUTO_INCREMENT,
  session_id INT NOT NULL,
  question_id INT NOT NULL,
  user_answer TEXT,
  is_correct BOOLEAN DEFAULT FALSE,
  answered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (session_id) REFERENCES exam_sessions(id) ON DELETE CASCADE,
  FOREIGN KEY (question_id) REFERENCES exam_questions(id) ON DELETE CASCADE,
  INDEX idx_session_question (session_id, question_id)
);

-- 7. EXAM RESULTS
-- Final score after exam
CREATE TABLE IF NOT EXISTS exam_results (
  id INT PRIMARY KEY AUTO_INCREMENT,
  session_id INT NOT NULL UNIQUE,
  score INT DEFAULT 0,
  total_questions INT DEFAULT 0,
  correct_answers INT DEFAULT 0,
  percentage DECIMAL(5, 2) DEFAULT 0,
  passed BOOLEAN DEFAULT FALSE,
  passed_score INT DEFAULT 60,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (session_id) REFERENCES exam_sessions(id) ON DELETE CASCADE,
  INDEX idx_score (score, passed)
);

-- ============================================
-- SAMPLE EXAM PROGRAMS
-- ============================================
-- NOTE: exam_programs has been removed from this simplified schema.

-- ============================================
-- SAMPLE EXAM QUESTIONS
-- ============================================
-- English to Kinyarwanda - Reading questions
INSERT INTO exam_questions (program_id, question_type, question_text, option_a, option_b, option_c, option_d, correct_answer, difficulty) VALUES
(1, 'reading', 'What does "Muraho" mean in English?', 'Goodbye', 'Hello', 'Thank you', 'Please', 'Hello', 'easy'),
(1, 'reading', 'How do you say "Goodbye" in Kinyarwanda?', 'Muraho', 'Murabeho', 'Urakoze', 'Mwaramutse', 'Murabeho', 'easy'),
(1, 'reading', 'What is "Thank you" in Kinyarwanda?', 'Muraho', 'Murabeho', 'Urakoze', 'Mwaramutse', 'Urakoze', 'easy'),
(1, 'reading', 'Translate: "Good morning"', 'Muraho', 'Mwaramutse', 'Murabeho', 'Urakoze', 'Mwaramutse', 'easy'),
(1, 'reading', 'What does "Yego" mean?', 'No', 'Yes', 'Maybe', 'Please', 'Yes', 'easy'),
(1, 'reading', 'What does "Ibiro" mean?', 'People', 'Things', 'Place', 'Time', 'Things', 'medium'),
(1, 'reading', 'Translate: "Nshuti"', 'Family', 'Friend', 'Teacher', 'Doctor', 'Friend', 'easy'),
(1, 'reading', 'What is "Umusozi" in English?', 'Mountain', 'Hill', 'Valley', 'River', 'Hill', 'medium'),
(1, 'reading', 'What does "Abana" mean?', 'Adults', 'Children', 'Family', 'Parents', 'Children', 'easy'),
(1, 'reading', 'What is "Inka" in English?', 'Goat', 'Cow', 'Sheep', 'Dog', 'Cow', 'easy');

-- English to Kinyarwanda - Translation questions
INSERT INTO exam_questions (program_id, question_type, question_text, option_a, option_b, option_c, option_d, correct_answer, difficulty) VALUES
(1, 'translation', 'Translate to Kinyarwanda: "How are you?"', 'Wakane?', 'Uri mute?', 'Amakuru yawe ni?', 'Byapewe', 'Amakuru yawe ni?', 'medium'),
(1, 'translation', 'Translate to Kinyarwanda: "I am fine"', 'Ndakureba', 'Ndagira amahoro', 'Ndashungaye', 'Ndabishimiye', 'Ndagira amahoro', 'medium'),
(1, 'translation', 'Translate: "What is your name?"', 'Nomero yawe?', 'Amazina yawe ni iki?', 'Ego ni iyo?', 'Ari hano', 'Amazina yawe ni iki?', 'medium'),
(1, 'translation', 'Translate: "Where is the bathroom?"', 'Aho ari sipoo?', 'Ishuri ari hehe?', 'Gite ari hehe?', 'Kimera ari hehe?', 'Gite ari hehe?', 'hard'),
(1, 'translation', 'Translate: "Nice to meet you"', 'Mbega gukundanya', 'Ninezapfa', 'Nishaka kumvira', 'Niyandike', 'Mbega gukundanya', 'medium');

-- English to Kinyarwanda - Listening questions
INSERT INTO exam_questions (program_id, question_type, question_text, option_a, option_b, option_c, option_d, correct_answer, difficulty) VALUES
(1, 'listening', 'Listen: "Muraho neza"', 'Goodbye', 'Hello', 'Thank you', 'Excuse me', 'Hello', 'easy'),
(1, 'listening', 'Listen: "Ndagira amahoro"', 'I am sad', 'I am fine', 'I am tired', 'I am happy', 'I am fine', 'medium'),
(1, 'listening', 'Listen: "Amakuru yawe ni?"', 'Where are you?', 'How are you?', 'How old are you?', 'What is your name?', 'How are you?', 'medium');

-- Kinyarwanda to English
INSERT INTO exam_questions (program_id, question_type, question_text, option_a, option_b, option_c, option_d, correct_answer, difficulty) VALUES
(2, 'reading', 'What does "Muraho" mean?', 'Goodbye', 'Hello', 'Thank you', 'Please', 'Hello', 'easy'),
(2, 'reading', 'Translate "Urakoze" to English', 'Hello', 'Goodbye', 'Thank you', 'Please', 'Thank you', 'easy'),
(2, 'reading', 'What is "Mwaramutse" in English?', 'Good night', 'Good morning', 'Good evening', 'Good afternoon', 'Good morning', 'easy'),
(2, 'reading', 'Translate "Yego" to English', 'No', 'Yes', 'Maybe', 'Please', 'Yes', 'easy'),
(2, 'reading', 'What does "Ubwenge" mean?', 'Strength', 'Wisdom', 'Knowledge', 'Learning', 'Wisdom', 'hard');

-- French to Kinyarwanda
INSERT INTO exam_questions (program_id, question_type, question_text, option_a, option_b, option_c, option_d, correct_answer, difficulty) VALUES
(3, 'reading', 'Translate "Bonjour" to Kinyarwanda', 'Muraho', 'Murabeho', 'Urakoze', 'Mwaramutse', 'Muraho', 'easy'),
(3, 'reading', 'Translate "Merci" to Kinyarwanda', 'Muraho', 'Murabeho', 'Urakoze', 'Ura', 'Urakoze', 'easy'),
(3, 'reading', 'Translate "Au revoir" to Kinyarwanda', 'Muraho', 'Murabeho', 'Urakoze', 'Bamushe', 'Murabeho', 'easy');

-- Kinyarwanda to French
INSERT INTO exam_questions (program_id, question_type, question_text, option_a, option_b, option_c, option_d, correct_answer, difficulty) VALUES
(4, 'reading', 'Translate "Muraho" to French', 'Bonjour', 'Au revoir', 'Merci', 'Oui', 'Bonjour', 'easy'),
(4, 'reading', 'Translate "Urakoze" to French', 'Bonjour', 'Au revoir', 'Merci', 'Oui', 'Merci', 'easy'),
(4, 'reading', 'Translate "Murabeho" to French', 'Bonjour', 'Au revoir', 'Merci', 'Oui', 'Au revoir', 'easy');
