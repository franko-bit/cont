<?php
/**
 * TAKE EXAM ENDPOINT
 * Loads exam questions from YAML and returns structured data for lessonexam.php
 * POST /backend/take-exam.php
 * Params: exam_id, language (optional)
 */

header('Content-Type: application/json');
require 'config.php';

try {
  if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    throw new Exception('POST method required');
  }

  $input = json_decode(file_get_contents('php://input'), true);
  if(!$input) $input = $_POST;
  
  $exam_id = $input['exam_id'] ?? null;
  if(!$exam_id) {
    throw new Exception('exam_id is required');
  }

  // Get exam from database
  $stmt = $pdo->prepare('SELECT * FROM institution_assessments WHERE id = ? LIMIT 1');
  $stmt->execute([$exam_id]);
  $exam = $stmt->fetch(PDO::FETCH_ASSOC);
  
  if(!$exam) {
    throw new Exception('Exam not found');
  }

  // Determine YAML file path based on exam language
  $language = strtolower($exam['exam_language'] ?? 'english');
  if(in_array($language, ['en', 'english', 'english'])) {
    $yamlFile = __DIR__ . '/../content/EXAMS/ENGLISH.yaml';
  } elseif(in_array($language, ['fr', 'french', 'francais'])) {
    $yamlFile = __DIR__ . '/../content/EXAMS/French.yaml';
  } else {
    throw new Exception('Unsupported language: ' . $language);
  }

  if(!file_exists($yamlFile)) {
    throw new Exception('YAML file not found: ' . $yamlFile);
  }

  // Load and parse YAML file
  $yamlContent = file_get_contents($yamlFile);
  
  // Extract lesson metadata
  $lessonName = '';
  $lessonDescription = '';
  $totalXp = 0;
  
  if(preg_match('/^\s*name:\s*["\']?(.*?)["\']?\s*$/mi', $yamlContent, $matches)) {
    $lessonName = $matches[1];
  }
  if(preg_match('/^\s*description:\s*["\']?(.*?)["\']?\s*$/mi', $yamlContent, $matches)) {
    $lessonDescription = $matches[1];
  }
  if(preg_match('/^\s*xp_reward:\s*(\d+)\s*$/mi', $yamlContent, $matches)) {
    $totalXp = (int)$matches[1];
  }

  // Extract individual exercise XP values
  preg_match_all('/^\s*xp_reward:\s*(\d+)\s*$/mi', $yamlContent, $xpMatches);
  $exerciseXps = $xpMatches[1] ?? [];
  
  // Calculate total XP from exercises (first item is lesson-level, rest are exercises)
  $totalExerciseXp = 0;
  for($i = 1; $i < count($exerciseXps); $i++) {
    $totalExerciseXp += (int)$exerciseXps[$i];
  }
  if($totalExerciseXp === 0 && $totalXp > 0) {
    $totalExerciseXp = $totalXp;
  }

  // Count exercises
  $exerciseCount = substr_count($yamlContent, '- id:');

  // Parse exercises - this is a simplified parser for basic YAML structure
  $exercises = parseYamlExercises($yamlContent);

  // Prepare response
  $response = [
    'success' => true,
    'exam' => [
      'id' => $exam['id'],
      'title' => $exam['title'],
      'language' => $exam['exam_language'],
      'passing_score' => $exam['passing_score'] ?? 70,
      'total_xp' => $totalExerciseXp,
      'exercise_count' => count($exercises),
      'lesson_name' => $lessonName,
      'lesson_description' => $lessonDescription
    ],
    'questions' => $exercises
  ];

  sendResponse($response);

} catch(Exception $e) {
  sendResponse(['success' => false, 'error' => $e->getMessage()], 400);
}

/**
 * Parse YAML exercises into structured array
 * Simple parser for basic YAML format
 */
function parseYamlExercises($content) {
  $exercises = [];
  
  // Split by exercise marker (- id:)
  $parts = preg_split('/^(\s*- id:)/m', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
  
  for($i = 2; $i < count($parts); $i += 2) {
    $exerciseBlock = $parts[$i];
    $exercise = parseExerciseBlock($exerciseBlock);
    if($exercise) {
      $exercises[] = $exercise;
    }
  }
  
  return $exercises;
}

/**
 * Parse individual exercise block
 */
function parseExerciseBlock($block) {
  $exercise = [];
  
  // Extract id
  if(preg_match('/^(\d+)/', $block, $m)) {
    $exercise['id'] = (int)$m[1];
  } else {
    return null;
  }
  
  // Extract type
  if(preg_match('/^\s*type:\s*(\w+)/m', $block, $m)) {
    $exercise['type'] = $m[1];
  }
  
  // Extract question
  if(preg_match('/^\s*question:\s*["\']?(.*?)["\']?\s*$/m', $block, $m)) {
    $exercise['question'] = trim($m[1]);
  }
  
  // Extract options (if present)
  if(preg_match('/^\s*options:\s*\[(.*?)\]/ms', $block, $m)) {
    $optionsStr = $m[1];
    $options = [];
    if(preg_match_all('/["\']([^"\']*)["\']/', $optionsStr, $om)) {
      $options = $om[1];
    }
    $exercise['options'] = $options;
  }
  
  // Extract correct answer or answer
  if(preg_match('/^\s*correct_answer:\s*(?:true|false|["\']?(.*?)["\']?)\s*$/m', $block, $m)) {
    if(strtolower($m[0]) === 'correct_answer: true') {
      $exercise['correct_answer'] = true;
    } elseif(strtolower($m[0]) === 'correct_answer: false') {
      $exercise['correct_answer'] = false;
    } else {
      $exercise['correct_answer'] = isset($m[1]) ? trim($m[1]) : null;
    }
  }
  if(preg_match('/^\s*answer:\s*["\']?(.*?)["\']?\s*$/m', $block, $m)) {
    $exercise['correct_answer'] = trim($m[1]);
  }
  
  // Extract XP reward
  if(preg_match('/^\s*xp_reward:\s*(\d+)\s*$/m', $block, $m)) {
    $exercise['xp_reward'] = (int)$m[1];
  }
  
  // Extract difficulty
  if(preg_match('/^\s*difficulty:\s*(\w+)/m', $block, $m)) {
    $exercise['difficulty'] = $m[1];
  }
  
  // Extract time limit
  if(preg_match('/^\s*time_limit_seconds:\s*(\d+)\s*$/m', $block, $m)) {
    $exercise['time_limit_seconds'] = (int)$m[1];
  }
  
  // Extract topic
  if(preg_match('/^\s*topic:\s*["\']?(\w+)["\']?\s*$/m', $block, $m)) {
    $exercise['topic'] = $m[1];
  }
  
  return $exercise;
}
?>
