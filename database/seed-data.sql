-- Use your database
USE playmates;

-- ============================================
-- 1. LEVELS TABLE - 6 Learning Levels with Image Links
-- ============================================
INSERT INTO levels (level_number, name, description, image_url, created_at) VALUES
(1, 'Beginner', 'Start your language journey here with basic vocabulary and simple greetings. Perfect for absolute beginners!', 'https://images.unsplash.com/photo-1503676260728-5177c2b399b2?w=400', NOW()),
(2, 'Elementary', 'Build your foundation with everyday vocabulary and simple sentence structures.', 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=400', NOW()),
(3, 'Intermediate', 'Express yourself in daily situations. Learn to have simple conversations.', 'https://images.unsplash.com/photo-1517486808906-6ca8b3f04846?w=400', NOW()),
(4, 'Upper Intermediate', 'Handle complex topics with confidence. Discuss opinions and abstract ideas.', 'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?w=400', NOW()),
(5, 'Advanced', 'Master nuanced language, idioms, and cultural references. Speak naturally.', 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?w=400', NOW()),
(6, 'Expert', 'Achieve near-native fluency. Understand and produce complex texts on any topic.', 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?w=400', NOW());

-- ============================================
-- 2. CATEGORIES TABLE with Image Links
-- ============================================
INSERT INTO categories (name, description, level_id, icon_url) VALUES
-- Level 1 Categories
('Greetings', 'Learn how to greet people, introduce yourself, and use polite expressions', 1, 'https://img.icons8.com/color/96/000000/handshake.png'),
('Numbers', 'Count from 1-100, tell time, and talk about prices and quantities', 1, 'https://img.icons8.com/color/96/000000/numbers.png'),
('Family', 'Learn vocabulary for family members and relationships', 1, 'https://img.icons8.com/color/96/000000/family.png'),
('Colors', 'Identify and describe colors of objects around you', 1, 'https://img.icons8.com/color/96/000000/color-palette.png'),
('Animals', 'Common animals and pets vocabulary with sounds', 1, 'https://img.icons8.com/color/96/000000/pets.png'),

-- Level 2 Categories
('Food & Drinks', 'Common food items, ordering at restaurants, and cooking vocabulary', 2, 'https://img.icons8.com/color/96/000000/restaurant.png'),
('Daily Routines', 'Talk about your daily activities, habits, and schedules', 2, 'https://img.icons8.com/color/96/000000/daily-calendar.png'),
('Weather', 'Describe weather conditions, seasons, and natural phenomena', 2, 'https://img.icons8.com/color/96/000000/partly-cloudy-day.png'),
('Clothing', 'Vocabulary for clothes, shopping, and describing what people wear', 2, 'https://img.icons8.com/color/96/000000/t-shirt.png'),
('House', 'Rooms, furniture, and household items vocabulary', 2, 'https://img.icons8.com/color/96/000000/house.png'),

-- Level 3 Categories
('Travel', 'Vocabulary for transportation, hotels, airports, and tourism', 3, 'https://img.icons8.com/color/96/000000/airplane-mode-on.png'),
('Shopping', 'Phrases for shopping, bargaining, and describing products', 3, 'https://img.icons8.com/color/96/000000/shopping-bag.png'),
('Restaurant', 'Dining out, ordering food, and restaurant etiquette', 3, 'https://img.icons8.com/color/96/000000/dinner.png'),
('Directions', 'Asking for and giving directions, locations, and places in town', 3, 'https://img.icons8.com/color/96/000000/worldwide-location.png'),
('Emotions', 'Expressing feelings, emotions, and describing how you feel', 3, 'https://img.icons8.com/color/96/000000/happy.png'),

-- Level 4 Categories
('Past Tense', 'Learn to talk about past events and experiences', 4, 'https://img.icons8.com/color/96/000000/time-machine.png'),
('Future Tense', 'Discuss future plans, predictions, and arrangements', 4, 'https://img.icons8.com/color/96/000000/future.png'),
('Opinions', 'Express your opinions, agree/disagree, and discuss preferences', 4, 'https://img.icons8.com/color/96/000000/speech-bubble.png'),
('Health', 'Vocabulary about health, medicine, and describing symptoms', 4, 'https://img.icons8.com/color/96/000000/hospital.png'),
('Education', 'Talk about school, learning, and academic subjects', 4, 'https://img.icons8.com/color/96/000000/student-male.png'),

-- Level 5 Categories
('Business', 'Professional vocabulary, meetings, emails, and workplace communication', 5, 'https://img.icons8.com/color/96/000000/briefcase.png'),
('News & Media', 'Understand news articles, reports, and media vocabulary', 5, 'https://img.icons8.com/color/96/000000/news.png'),
('Debates', 'Learn to argue, persuade, and discuss controversial topics', 5, 'https://img.icons8.com/color/96/000000/ debate.png'),
('Idioms', 'Common idiomatic expressions and figurative language', 5, 'https://img.icons8.com/color/96/000000/idiom.png'),
('Culture', 'Discuss cultural differences, traditions, and social norms', 5, 'https://img.icons8.com/color/96/000000/cultural.png'),

-- Level 6 Categories
('Fluency', 'Practice natural speech patterns and connected speech', 6, 'https://img.icons8.com/color/96/000000/voice.png'),
('Literature', 'Analyze texts, poetry, and literary devices', 6, 'https://img.icons8.com/color/96/000000/book.png'),
('Presentations', 'Learn to give effective presentations and speeches', 6, 'https://img.icons8.com/color/96/000000/presentation.png'),
('Negotiations', 'Vocabulary for negotiations, compromises, and agreements', 6, 'https://img.icons8.com/color/96/000000/negotiation.png'),
('Masterclass', 'Advanced topics for near-native proficiency', 6, 'https://img.icons8.com/color/96/000000/diploma.png');

-- ============================================
-- 3. LESSONS TABLE (YAML files reference)
-- ============================================
INSERT INTO lessons (category_id, name, yaml_file, description, image_url, order_index) VALUES
-- Greetings lessons (category_id 1)
(1, 'Basic Greetings', 'greetings_1.yaml', 'Learn how to say hello, goodbye, and introduce yourself', 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?w=400', 1),
(1, 'Polite Expressions', 'greetings_2.yaml', 'Master please, thank you, sorry, and other polite phrases', 'https://images.unsplash.com/photo-1511632765486-a01980e01a18?w=400', 2),
(1, 'Introductions', 'greetings_3.yaml', 'Learn to introduce yourself and others properly', 'https://images.unsplash.com/photo-1517486808906-6ca8b3f04846?w=400', 3),

-- Numbers lessons (category_id 2)
(2, 'Numbers 1-20', 'numbers_1.yaml', 'Learn to count from one to twenty', 'https://images.unsplash.com/photo-1580519549036-7c42c4e9e1b4?w=400', 1),
(2, 'Numbers 21-100', 'numbers_2.yaml', 'Count higher and form compound numbers', 'https://images.unsplash.com/photo-1580519549036-7c42c4e9e1b4?w=400', 2),
(2, 'Telling Time', 'numbers_3.yaml', 'Learn to tell time and talk about schedules', 'https://images.unsplash.com/photo-1509048197680-3b8c9cf4a2f9?w=400', 3),

-- Family lessons (category_id 3)
(3, 'Immediate Family', 'family_1.yaml', 'Learn words for parents, siblings, and children', 'https://images.unsplash.com/photo-1544725176-7c40e5a71c5e?w=400', 1),
(3, 'Extended Family', 'family_2.yaml', 'Vocabulary for grandparents, aunts, uncles, and cousins', 'https://images.unsplash.com/photo-1544725176-7c40e5a71c5e?w=400', 2),
(3, 'Family Relationships', 'family_3.yaml', 'Describe family relationships and connections', 'https://images.unsplash.com/photo-1544725176-7c40e5a71c5e?w=400', 3),

-- Food & Drinks lessons (category_id 6)
(6, 'Common Foods', 'food_1.yaml', 'Learn names of everyday foods and ingredients', 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=400', 1),
(6, 'Fruits & Vegetables', 'food_2.yaml', 'Vocabulary for fruits and vegetables', 'https://images.unsplash.com/photo-1519996529931-28324d5a630e?w=400', 2),
(6, 'Drinks & Beverages', 'food_3.yaml', 'Names of drinks and how to order them', 'https://images.unsplash.com/photo-1544145945-f90425340c7e?w=400', 3),
(6, 'At the Restaurant', 'food_4.yaml', 'Phrases for ordering food in restaurants', 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=400', 4),

-- Travel lessons (category_id 11)
(11, 'At the Airport', 'travel_1.yaml', 'Vocabulary for airports, flights, and check-in', 'https://images.unsplash.com/photo-1436491865332-7a61a109cc05?w=400', 1),
(11, 'Hotels & Accommodation', 'travel_2.yaml', 'Book hotels and talk about accommodations', 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=400', 2),
(11, 'Transportation', 'travel_3.yaml', 'Vocabulary for buses, trains, taxis, and rental cars', 'https://images.unsplash.com/photo-1474487548417-781cb71495f3?w=400', 3),
(11, 'Tourist Phrases', 'travel_4.yaml', 'Essential phrases for tourists', 'https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?w=400', 4);

-- ============================================
-- 4. BADGES TABLE with Image Links
-- ============================================
INSERT INTO badges (name, description, icon_url, created_at) VALUES
('First Steps', 'Complete your first lesson and begin your language journey', 'https://img.icons8.com/color/96/000000/medal--v1.png', NOW()),
('Quick Learner', 'Complete 10 lessons and show your dedication to learning', 'https://img.icons8.com/color/96/000000/learning.png', NOW()),
('Streak Master', 'Maintain a 7-day learning streak', 'https://img.icons8.com/color/96/000000/fire-element.png', NOW()),
('Vocabulary Builder', 'Learn 100 new words and expand your vocabulary', 'https://img.icons8.com/color/96/000000/dictionary.png', NOW()),
('Grammar Guru', 'Complete all grammar lessons with perfect scores', 'https://img.icons8.com/color/96/000000/grammar.png', NOW()),
('Perfect Score', 'Get 100% on a lesson - no mistakes allowed!', 'https://img.icons8.com/color/96/000000/prize.png', NOW()),
('Early Bird', 'Complete a lesson before 8 AM and start your day right', 'https://img.icons8.com/color/96/000000/sun.png', NOW()),
('Night Owl', 'Complete a lesson after 10 PM and burn the midnight oil', 'https://img.icons8.com/color/96/000000/owl.png', NOW()),
('Social Learner', 'Share your progress on social media', 'https://img.icons8.com/color/96/000000/share.png', NOW()),
('Master Linguist', 'Complete all levels and achieve language mastery', 'https://img.icons8.com/color/96/000000/linguistics.png', NOW()),
('Century Club', 'Earn 1000 XP and show your experience', 'https://img.icons8.com/color/96/000000/100.png', NOW()),
('Week Warrior', 'Complete lessons 7 days in a row', 'https://img.icons8.com/color/96/000000/calendar--v1.png', NOW()),
('Month Master', 'Maintain a 30-day learning streak', 'https://img.icons8.com/color/96/000000/month-calendar.png', NOW()),
('Speed Demon', 'Complete a lesson in under 5 minutes', 'https://img.icons8.com/color/96/000000/speed.png', NOW()),
('Perfectionist', 'Get 10 perfect scores on different lessons', 'https://img.icons8.com/color/96/000000/star--v1.png', NOW());

-- ============================================
-- 5. SAMPLE EXERCISES for first lesson
-- (These will be referenced by your YAML files)
-- ============================================
INSERT INTO exercises (lesson_id, type, question, answer, tts_text, image_url, options, hints, xp_reward, order_index) VALUES
-- For lesson_id 1 (Basic Greetings)
(1, 'translation', 'How do you say "Hello" in Kinyarwanda?', 'Muraho', 'Muraho', 'https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?w=400', NULL, 'Starts with M|Common greeting used anytime', 10, 1),

(1, 'multiple_choice', 'What is "Good morning" in Kinyarwanda?', '0', 'Mwaramutse', 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=400', 
 '["Mwaramutse", "Muraho", "Urakoze", "Amakuru"]', 'Used in the morning|Starts with Mwa', 10, 2),

(1, 'typing', 'Type "Thank you" in Kinyarwanda', 'Urakoze', 'Urakoze', 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?w=400', NULL, 'Starts with U|Ends with e', 15, 3),

(1, 'listening', 'Listen and type what you hear', 'Muraho neza', 'Muraho neza', 'https://images.unsplash.com/photo-1516280440614-37939bbacd81?w=400', NULL, 'Two words|A polite greeting', 20, 4),

(1, 'matching', 'Match the English words with their Kinyarwanda translations', NULL, NULL, 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=400', 
 '[
   {"left":"Hello","right":"Muraho"},
   {"left":"Goodbye","right":"Murabeho"},
   {"left":"Please","right":"Nyamuneka"},
   {"left":"Thank you","right":"Urakoze"}
 ]', 'Match each word with its translation', 20, 5),

(1, 'sentence_building', 'Arrange the words to form "How are you?" in Kinyarwanda', 'Amakuru yawe ni?', 'Amakuru yawe ni?', 'https://images.unsplash.com/photo-1455390582262-044cdead277a?w=400', 
 '["Amakuru", "?", "yawe", "ni"]', 'Starts with Amakuru|Question mark comes last', 25, 6),

-- For lesson_id 2 (Numbers 1-20)
(2, 'translation', 'How do you say "five" in Kinyarwanda?', 'Gatanu', 'Gatanu', 'https://images.unsplash.com/photo-1580519549036-7c42c4e9e1b4?w=400', NULL, 'Starts with Ga|Number after four', 10, 1),

(2, 'multiple_choice', 'What is "ten" in Kinyarwanda?', '2', 'Icumi', 'https://images.unsplash.com/photo-1580519549036-7c42c4e9e1b4?w=400', 
 '["Indwi", "Umunani", "Ijana", "Icumi"]', 'Has two syllables|Starts with I', 10, 2);

-- ============================================
-- 6. SAMPLE USER (for testing)
-- Password: password123 (hashed with bcrypt)
-- ============================================
INSERT INTO users (full_name, email, password, created_at, is_student, subscription_status) VALUES
('Test User', 'test@example.com', '$2y$10$YourHashedPasswordHere', NOW(), 1, 'free');

-- Add to leaderboard
INSERT INTO leaderboard (user_id, xp, streak, updated_at) VALUES
(1, 150, 3, NOW());

-- ============================================
-- 7. VERIFICATION QUERIES
-- Run these to verify your data was inserted
-- ============================================

-- Check levels
SELECT 'LEVELS' as table_name, COUNT(*) as record_count FROM levels
UNION ALL
SELECT 'CATEGORIES', COUNT(*) FROM categories
UNION ALL
SELECT 'LESSONS', COUNT(*) FROM lessons
UNION ALL
SELECT 'EXERCISES', COUNT(*) FROM exercises
UNION ALL
SELECT 'BADGES', COUNT(*) FROM badges;

-- Show sample data
SELECT '--- LEVELS ---' as '';
SELECT level_number, name, LEFT(description, 50) as description_preview FROM levels ORDER BY level_number;

SELECT '--- CATEGORIES BY LEVEL ---' as '';
SELECT l.level_number, c.name, c.description 
FROM categories c
JOIN levels l ON c.level_id = l.id
ORDER BY l.level_number, c.name;

SELECT '--- LESSONS WITH CATEGORIES ---' as '';
SELECT c.name as category, l.name as lesson, l.yaml_file
FROM lessons l
JOIN categories c ON l.category_id = c.id
ORDER BY c.name, l.order_index;

SELECT '--- EXERCISES BY TYPE ---' as '';
SELECT type, COUNT(*) as count FROM exercises GROUP BY type;

SELECT '--- BADGES ---' as '';
SELECT name, description FROM badges ORDER BY id;