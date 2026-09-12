<?php
session_start();
require_once '../backend/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$user_id     = $_SESSION['user_id'];
$level       = $_GET['level'] ?? 1;
$topic       = $_GET['topic'] ?? 'ENGLISH.yaml';
$exercise_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$ui_lang     = $_GET['ui_lang'] ?? 'en';

$pair      = $_GET['pair']      ?? $_SESSION['active_pair']      ?? 'en-rw';
$direction = $_GET['direction'] ?? $_SESSION['active_direction'] ?? 'en';
$lang      = $direction;

// ===== VALIDATE ATTEMPT_ID =====
$attempt_id_param = (int)($_GET['attempt_id'] ?? 0);
$valid_attempt_id = 0;

if ($attempt_id_param > 0) {
    // Check if this attempt belongs to the current user
    $stmt = $pdo->prepare("SELECT id, user_id, status FROM exam_attempts WHERE id = ? LIMIT 1");
    $stmt->execute([$attempt_id_param]);
    $attempt = $stmt->fetch();
    
    if ($attempt && $attempt['user_id'] == $user_id && $attempt['status'] !== 'submitted') {
        // Attempt is valid, belongs to this user, and is not already submitted
        $valid_attempt_id = $attempt_id_param;
        error_log("[LESSONEXAM] User $user_id loaded exam with valid attempt_id=$valid_attempt_id, status=" . $attempt['status']);
    } else if ($attempt && $attempt['user_id'] == $user_id && $attempt['status'] === 'submitted') {
        // Attempt already submitted - reject stale URL
        error_log("[LESSONEXAM] BLOCKED: User $user_id tried to retake submitted attempt_id=$attempt_id_param");
    } else if ($attempt) {
        // Attempt exists but belongs to different user - security breach!
        error_log("[LESSONEXAM] SECURITY: User $user_id tried to access attempt_id=$attempt_id_param which belongs to user " . $attempt['user_id']);
        // Don't use this attempt_id, create a new one instead
    } else {
        // Attempt doesn't exist at all
        error_log("[LESSONEXAM] User $user_id specified non-existent attempt_id=$attempt_id_param");
    }
}

// If no valid attempt_id, try to find or create one
if ($valid_attempt_id <= 0 && !empty($_GET['pair']) && !empty($_GET['level']) && !empty($_GET['direction'])) {
    $exam_pair      = $_GET['pair'];
    $exam_direction = $_GET['direction'];
    $exam_level     = (int)$_GET['level'];
    
    // Find existing PENDING/IN_PROGRESS attempt (not submitted ones)
    $stmt = $pdo->prepare("
        SELECT ea.id FROM exam_attempts ea
        JOIN exams e ON ea.exam_id = e.id
        WHERE ea.user_id = ? AND e.language_pair = ? AND e.level_number = ? 
        AND ea.status IN ('pending', 'verification', 'in_progress')
        ORDER BY ea.created_at DESC LIMIT 1
    ");
    $stmt->execute([$user_id, $exam_pair, $exam_level]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        $valid_attempt_id = $existing['id'];
        error_log("[LESSONEXAM] User $user_id found existing pending attempt_id={$valid_attempt_id}");
    } else {
        // Create NEW attempt for this user+exam pair
        $stmt = $pdo->prepare("SELECT id FROM exams WHERE language_pair = ? AND level_number = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$exam_pair, $exam_level]);
        $exam = $stmt->fetch();
        
        if ($exam) {
            $stmt = $pdo->prepare("
                INSERT INTO exam_attempts (user_id, exam_id, status, verification_status, ip_address, user_agent)
                VALUES (?, ?, 'pending', 'pending', ?, ?)
            ");
            $stmt->execute([$user_id, $exam['id'], $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']);
            $valid_attempt_id = $pdo->lastInsertId();
            error_log("[LESSONEXAM] User $user_id created NEW attempt_id={$valid_attempt_id}");
        }
    }
}

// Store in sessionStorage for frontend to use
$js_attempt_id = $valid_attempt_id > 0 ? $valid_attempt_id : 'null';
error_log("[LESSONEXAM] Final attempt_id for user $user_id: $js_attempt_id");

$db_lang_map = [
    'en-rw:en' => 'en-to-rw', 'en-rw:rw' => 'rw-to-en',
    'fr-rw:fr' => 'fr-to-rw', 'fr-rw:rw' => 'rw-to-fr',
    'en-sw:en' => 'en-to-sw', 'en-sw:sw' => 'sw-to-en',
    'fr-sw:fr' => 'fr-to-sw', 'fr-sw:sw' => 'sw-to-fr',
];
$target_lang = $db_lang_map[$pair . ':' . $direction] ?? strtolower($lang);

$pair_folder_map = [
    'en-rw:en' => 'EN-TO-RW',
    'en-rw:rw' => 'RW-TO-EN',
    'fr-rw:fr' => 'FR-TO-RW',
    'fr-rw:rw' => 'RW-TO-FR',
    'en-sw:en' => 'EN-TO-SW',
    'en-sw:sw' => 'SW-TO-EN',
    'fr-sw:fr' => 'FR-TO-SW',
    'fr-sw:sw' => 'SW-TO-FR',
];

// First try pair-based mapping
$mapped_folder = null;
if (isset($pair_folder_map[$pair . ':' . $direction])) {
    $mapped_folder = $pair_folder_map[$pair . ':' . $direction];
} else {
    // Fallback to old direction-only mapping
    $lang_folder_map = [
        'en' => 'EN-TO-RW', 'rw' => 'RW-TO-EN', 'fr' => 'FR-TO-RW',
        'sw' => 'SW-TO-RW', 'es' => 'ES-TO-RW', 'de' => 'DE-TO-RW'
    ];
    $mapped_folder = $lang_folder_map[$lang] ?? null;
}

if ($mapped_folder && !is_dir("../content/{$mapped_folder}/level{$level}") && is_dir("../content/{$mapped_folder}/{$mapped_folder}/level{$level}")) {
    $mapped_folder .= "/{$mapped_folder}";
}

$_SESSION['active_language']  = $target_lang;
$_SESSION['active_pair']      = $pair;
$_SESSION['active_direction'] = $direction;

// NOTE: mapped_folder is already set above via pair_folder_map
$lesson_direction = '';
$direction_color  = '';
$direction_icon   = '';

if ($mapped_folder) {
    if ($mapped_folder == 'EN-TO-RW') {
        $lesson_direction = 'English → Kinyarwanda';
        $direction_color  = 'var(--sage-dark)';
        $direction_icon   = '<span class="fi fi-gb"></span> → <span class="fi fi-rw"></span>';
    } elseif ($mapped_folder == 'RW-TO-EN') {
        $lesson_direction = 'Kinyarwanda → English';
        $direction_color  = '#2B6CB0';
        $direction_icon   = '<span class="fi fi-rw"></span> → <span class="fi fi-gb"></span>';
    } elseif ($mapped_folder == 'FR-TO-RW') {
        $lesson_direction = 'French → Kinyarwanda';
        $direction_color  = '#7a5c1e';
        $direction_icon   = '<span class="fi fi-fr"></span> → <span class="fi fi-rw"></span>';
    } elseif ($mapped_folder == 'EN-TO-SW') {
        $lesson_direction = 'English → Kiswahili';
        $direction_color  = '#FF6F00';
        $direction_icon   = '<span class="fi fi-gb"></span> → <span class="fi fi-tz"></span>';
    } elseif ($mapped_folder == 'SW-TO-EN') {
        $lesson_direction = 'Kiswahili → English';
        $direction_color  = '#E65100';
        $direction_icon   = '<span class="fi fi-tz"></span> → <span class="fi fi-gb"></span>';
    } elseif ($mapped_folder == 'FR-TO-SW') {
        $lesson_direction = 'French → Kiswahili';
        $direction_color  = '#D84315';
        $direction_icon   = '<span class="fi fi-fr"></span> → <span class="fi fi-tz"></span>';
    } elseif ($mapped_folder == 'SW-TO-FR') {
        $lesson_direction = 'Kiswahili → French';
        $direction_color  = '#BF360C';
        $direction_icon   = '<span class="fi fi-tz"></span> → <span class="fi fi-fr"></span>';
    } else {
        $lesson_direction = $mapped_folder;
        $direction_color  = 'var(--ink-mid)';
        $direction_icon   = '🔀';
    }
}

// ── YAML parser functions ─────────────────────────────────
function _yaml_scalar($v) {
    $v=trim($v);if(strlen($v)>=2){$f=$v[0];$l=$v[strlen($v)-1];if(($f==='"'&&$l==='"')||($f==="'"&&$l==="'"))return substr($v,1,-1);}
    if(strtolower($v)==='true')return true;if(strtolower($v)==='false')return false;
    if(strtolower($v)==='null'||$v==='~')return null;if(is_numeric($v))return $v+0;return $v;
}
function _yaml_inline_seq($str){
    $str=trim($str);if(!$str||$str[0]!=='[')return[];
    $inner=substr($str,1,strrpos($str,']')-1);
    $items=[];$depth=0;$cur='';$inq=false;$qc='';
    for($i=0,$len=strlen($inner);$i<$len;$i++){$c=$inner[$i];
        if(!$inq&&($c==='"'||$c==="'")){$inq=true;$qc=$c;$cur.=$c;}
        elseif($inq&&$c===$qc){$inq=false;$cur.=$c;}
        elseif(!$inq&&$c==='['){$depth++;$cur.=$c;}
        elseif(!$inq&&$c===']'){$depth--;$cur.=$c;}
        elseif(!$inq&&$c===','&&$depth===0){$items[]=_yaml_scalar(trim($cur));$cur='';}
        else{$cur.=$c;}}
    if(trim($cur)!=='')$items[]=_yaml_scalar(trim($cur));return $items;
}
function parse_yaml_manual($yaml){
    $yaml=str_replace(["\r\n","\r"],"\n",$yaml);$lines=explode("\n",$yaml);$n=count($lines);
    $pool=[[]];$pi=1;$stk=[[-1,0]];$i=0;
    while($i<$n){$raw=$lines[$i];$i++;$t=rtrim($raw);$tr=trim($t);
        if($tr===''||$tr[0]==='#')continue;
        $ind=strlen($t)-strlen(ltrim($t));
        if($tr[0]!=='"'&&$tr[0]!=="'")$tr=trim(preg_replace('/\s+#[^"\']*$/','', $tr));
        if($tr==='')continue;
        while(count($stk)>1&&$stk[count($stk)-1][0]>=$ind)array_pop($stk);
        $si=count($stk)-1;$pIdx=$stk[$si][1];$dash=false;
        if($tr[0]==='-'&&(strlen($tr)===1||$tr[1]===' '||$tr[1]==="\t")){$dash=true;$tr=strlen($tr)>1?trim(substr($tr,2)):'';}
        if($tr===''&&$dash){$pool[$pi]=[];$pool[$pIdx][]=&$pool[$pi];array_push($stk,[$ind,$pi]);$pi++;continue;}
        if(strlen($tr)>0&&$tr[0]==='['&&$dash){$pool[$pIdx][]=_yaml_inline_seq($tr);continue;}
        if(preg_match('/^([^:]+):\s*(.*)$/',$tr,$m)){
            $key=trim($m[1]);$val=trim($m[2]);
            if(strlen($key)>=2){$f=$key[0];$l=$key[strlen($key)-1];if(($f==='"'&&$l==='"')||($f==="'"&&$l==="'"))$key=substr($key,1,-1);}
            if($val!==''&&$val[0]!=='"'&&$val[0]!=="'")$val=trim(preg_replace('/\s+#[^"\']*$/','', $val));
            if($dash){$pool[$pi]=[];$pool[$pIdx][]=&$pool[$pi];$itemIdx=$pi++;array_push($stk,[$ind,$itemIdx]);$si=count($stk)-1;$pIdx=$itemIdx;}
            if($val===''||$val==='|'||$val==='>'){
                if($val==='|'||$val==='>'){$block='';$base=-1;
                    while($i<$n){$bl=$lines[$i];if(trim($bl)===''){$block.="\n";$i++;continue;}
                        $bi=strlen($bl)-strlen(ltrim($bl));if($base<0)$base=$bi;if($bi<$base)break;$block.=substr($bl,$base)."\n";$i++;}
                    $pool[$pIdx][$key]=rtrim($block);
                } else {$pool[$pi]=[];$pool[$pIdx][$key]=&$pool[$pi];array_push($stk,[$ind,$pi]);$pi++;}
            } elseif(strlen($val)>0&&$val[0]==='['){$pool[$pIdx][$key]=_yaml_inline_seq($val);
            } else {$pool[$pIdx][$key]=_yaml_scalar($val);}
        } elseif($dash){$pool[$pIdx][]=_yaml_scalar($tr);}
    }
    return $pool[0];
}

// ── YAML file discovery ──────────────────────────────────────────────────────
$script_dir    = dirname($_SERVER['SCRIPT_FILENAME']);
$document_root = $_SERVER['DOCUMENT_ROOT'];

// Determine which YAML file to load based on topic parameter
$topic_clean = preg_replace('/[^a-zA-Z0-9]/', '', $topic ?? 'ENGLISH');
$yaml_filenames = [];

// Add topic-specific files first (e.g., AcademicEnglish.yaml, BusinessEnglish.yaml)
$yaml_filenames[] = $topic_clean . ".yaml";
$yaml_filenames[] = $topic_clean . ".yml";
$yaml_filenames[] = strtolower($topic_clean) . ".yaml";

// For English topics, also try generic fallbacks
if ($lang === 'en' || strpos($topic, 'English') !== false) {
    $yaml_filenames[] = "AcademicEnglish.yaml";
    $yaml_filenames[] = "BusinessEnglish.yaml";
    $yaml_filenames[] = "ENGLISH.yaml";
    $yaml_filenames[] = "ENGLISH.yml";
    $yaml_filenames[] = "english.yaml";
}

// For French topics
if ($lang === 'fr' || strpos($topic, 'French') !== false) {
    $yaml_filenames[] = "FRENCH.yaml";
    $yaml_filenames[] = "french.yaml";
}

$possible_paths = [];
foreach ($yaml_filenames as $filename) {
    $possible_paths[] = "C:/xampp/htdocs/language-platform/content/EXAMS/" . $filename;
    $possible_paths[] = $document_root . "/language-platform/content/EXAMS/" . $filename;
    $possible_paths[] = $document_root . "/content/EXAMS/" . $filename;
    $possible_paths[] = "../content/EXAMS/" . $filename;
    $possible_paths[] = "./content/EXAMS/" . $filename;
    $possible_paths[] = "../../content/EXAMS/" . $filename;
    $possible_paths[] = $script_dir . "/../content/EXAMS/" . $filename;
    $possible_paths[] = $script_dir . "/content/EXAMS/" . $filename;
}

$yaml_file = null;
foreach ($possible_paths as $path) { if (file_exists($path)) { $yaml_file = $path; break; } }

$all_exercises = [];
$lesson_info   = ['name' => 'Adaptive Exam', 'description' => 'Test your language skills'];

if ($yaml_file && file_exists($yaml_file)) {
    $content = file_get_contents($yaml_file);
    $data = function_exists('yaml_parse') ? yaml_parse($content) : parse_yaml_manual($content);
    if ($data && isset($data['exercises'])) {
        $lesson_info   = $data['lesson'] ?? $lesson_info;
        $all_exercises = $data['exercises'];
    }
} else {
    for ($i = 1; $i <= 2; $i++) {
        $all_exercises[] = ['id'=>$i,'type'=>'multiple_choice','difficulty'=>'medium','topic'=>'sample',
            'question'=>"Sample Question $i: Fallback exercise — YAML not found.",
            'options'=>['Option A','Option B','Option C','Option D'],'answer'=>'Option A','xp'=>10,'time_limit_seconds'=>30];
    }
}

// ── Normalize exercise data ──────────────────────────────────────────────────────
foreach ($all_exercises as $idx => &$ex) {
    if (!isset($ex['id'])) $ex['id'] = $idx + 1;
    if (!isset($ex['xp'])) $ex['xp'] = $ex['xp_reward'] ?? 10;
    if (!isset($ex['difficulty'])) $ex['difficulty'] = $ex['difficulty'] ?? 'medium';
    if (!isset($ex['topic'])) $ex['topic'] = $ex['topic'] ?? 'general';

    if (isset($ex['correct_answer']) && !isset($ex['answer'])) $ex['answer'] = $ex['correct_answer'];
    if (isset($ex['correct']) && !isset($ex['answer'])) $ex['answer'] = $ex['correct'];
    if (isset($ex['statement']) && !isset($ex['question'])) $ex['question'] = $ex['statement'];
    if (isset($ex['prompt']) && !isset($ex['tts_text'])) $ex['tts_text'] = $ex['prompt'];
    if (isset($ex['dialogue_tts']) && !isset($ex['tts_text'])) $ex['tts_text'] = $ex['dialogue_tts'];
    if (isset($ex['bot_message']) && !isset($ex['bot'])) $ex['bot'] = $ex['bot_message'];
    if (isset($ex['text_with_blank']) && !isset($ex['text'])) $ex['text'] = $ex['text_with_blank'];
    if (isset($ex['scrambled_words']) && !isset($ex['word_bank'])) $ex['word_bank'] = $ex['scrambled_words'];
    if (isset($ex['correct_taps']) && !isset($ex['correct_answer'])) $ex['correct_answer'] = $ex['correct_taps'];
    if (isset($ex['correct_response']) && !isset($ex['correct_answer'])) $ex['correct_answer'] = $ex['correct_response'];
    if (isset($ex['correct_sentence']) && !isset($ex['answer'])) $ex['answer'] = $ex['correct_sentence'];
    if (isset($ex['incorrect_sentence']) && !isset($ex['incorrect'])) $ex['incorrect'] = $ex['incorrect_sentence'];
    if (isset($ex['story_segments']) && !isset($ex['segments'])) $ex['segments'] = $ex['story_segments'];
    if (isset($ex['story_exercises']) && !isset($ex['items'])) $ex['items'] = $ex['story_exercises'];
    if (isset($ex['context_sentence']) && !isset($ex['context'])) $ex['context'] = $ex['context_sentence'];
    if (isset($ex['flashcards']) && !isset($ex['cards'])) $ex['cards'] = $ex['flashcards'];

    if ($ex['type'] === 'translation_english') $ex['type'] = 'translation';
    if (!isset($ex['options'])) $ex['options'] = [];

    if ($ex['type'] === 'true_false') {
        $raw = null;
        if (isset($ex['correct_answer'])) {
            $raw = $ex['correct_answer'];
        } elseif (isset($ex['answer'])) {
            $raw = $ex['answer'];
        } elseif (isset($ex['correct'])) {
            $raw = $ex['correct'];
        }
        if (is_bool($raw)) {
            $ex['correct'] = $raw;
        } elseif (is_string($raw)) {
            $ex['correct'] = strtolower(trim($raw)) === 'true';
        } elseif (is_numeric($raw)) {
            $ex['correct'] = ((int)$raw) === 1;
        }
    }
    
    if ($ex['type'] === 'picture_word') {
        if (empty($ex['image_url']) && !empty($ex['image'])) {
            $ex['image_url'] = $ex['image'];
        } elseif (empty($ex['image_url']) && !empty($ex['img'])) {
            $ex['image_url'] = $ex['img'];
        } elseif (empty($ex['image_url']) && !empty($ex['img_url'])) {
            $ex['image_url'] = $ex['img_url'];
        }
    }
    
    if ($ex['type'] === 'speaking' || $ex['type'] === 'pronunciation') {
        if (!isset($ex['speaking_type'])) {
            if (!empty($ex['tts_text']) && (strpos(strtolower($ex['question'] ?? ''), 'repeat') !== false || 
                strpos(strtolower($ex['question'] ?? ''), 'say') !== false ||
                strpos(strtolower($ex['question'] ?? ''), 'read') !== false)) {
                $ex['speaking_type'] = 'repetition';
            } else {
                $ex['speaking_type'] = 'open_ended';
            }
        }
    }
}
unset($ex);

$filtered_exercises = [];
foreach ($all_exercises as $exercise) {
    $id = (int)($exercise['id'] ?? 0);
    $has_text = !empty($exercise['question']) || !empty($exercise['title']) || !empty($exercise['prompt']);
    if ($id >= 1 && $id <= 85 && $has_text && !isset($filtered_exercises[$id])) {
        $filtered_exercises[$id] = $exercise;
    }
}
$all_exercises = array_values($filtered_exercises);

// ========== INTELLIGENT QUESTION SELECTION SYSTEM ==========

// Define question categories with their types
$question_categories = [
    'fixed_answer' => [
        'multiple_choice',
        'true_false',
        'fill_blank',
        'matching',
        'listen_and_choose',
        'identify_meaning',
        'choose_missing'
    ],
    'constructed_response' => [
        'writing',
        'translation',
        'error_correction',
        'sentence_scramble',
        'word_bank',
        'tap_hear',
        'listen_and_type'
    ],
    'speaking' => [
        'speaking',
        'pronunciation'
    ],
    'reading' => [
        'read_answer',
        'picture_word'
    ]
];

// Define how many questions per category (total 2 questions)
$questions_per_category = [
    'fixed_answer' => 1,
    'constructed_response' => 1,
    'speaking' => 0,
    'reading' => 0
];

// Function to get questions by type with randomization and no repeats
function getQuestionsByType($exercises, $type, $count, &$used_ids) {
    $available = [];
    foreach ($exercises as $ex) {
        if ($ex['type'] === $type && !in_array($ex['id'], $used_ids)) {
            $available[] = $ex;
        }
    }
    
    shuffle($available);
    $selected = array_slice($available, 0, $count);
    
    foreach ($selected as $ex) {
        $used_ids[] = $ex['id'];
    }
    
    return $selected;
}

// Function to build balanced exam with no question repeats
function buildBalancedExam($all_exercises, $questions_per_category, $question_categories) {
    $selected_exercises = [];
    $used_ids = [];
    $category_counts = [];
    
    foreach ($questions_per_category as $category => $desired_count) {
        $category_counts[$category] = 0;
        $types_in_category = $question_categories[$category];
        
        $shuffled_types = $types_in_category;
        shuffle($shuffled_types);
        
        $questions_per_type = floor($desired_count / count($shuffled_types));
        $remainder = $desired_count % count($shuffled_types);
        
        foreach ($shuffled_types as $index => $type) {
            $count_for_type = $questions_per_type + ($index < $remainder ? 1 : 0);
            
            if ($count_for_type > 0) {
                $type_questions = getQuestionsByType($all_exercises, $type, $count_for_type, $used_ids);
                foreach ($type_questions as $q) {
                    $selected_exercises[] = $q;
                    $category_counts[$category]++;
                }
            }
        }
        
        $shortfall = $desired_count - $category_counts[$category];
        if ($shortfall > 0) {
            for ($i = 0; $i < $shortfall; $i++) {
                $fallback = createFallbackQuestion($shuffled_types[$i % count($shuffled_types)], count($selected_exercises) + 1);
                $selected_exercises[] = $fallback;
                $category_counts[$category]++;
            }
        }
    }
    
    shuffle($selected_exercises);
    
    foreach ($selected_exercises as $idx => &$ex) {
        $ex['id'] = $idx + 1;
        if (!isset($ex['time_limit_seconds'])) $ex['time_limit_seconds'] = 60;
        if (!isset($ex['xp'])) $ex['xp'] = 15;
    }
    
    return $selected_exercises;
}

// Create a fallback question for any missing type
function createFallbackQuestion($type, $id) {
    $fallback = [
        'id' => $id,
        'type' => $type,
        'xp' => 15,
        'difficulty' => 'medium',
        'topic' => 'general',
        'time_limit_seconds' => 60
    ];
    
    switch ($type) {
        case 'multiple_choice':
            $fallback['question'] = 'What is the capital of France?';
            $fallback['options'] = ['London', 'Berlin', 'Paris', 'Madrid'];
            $fallback['answer'] = 'Paris';
            break;
        case 'true_false':
            $fallback['question'] = 'The Earth is flat.';
            $fallback['correct'] = false;
            break;
        case 'fill_blank':
            $fallback['question'] = 'The _____ is the star at the center of our solar system.';
            $fallback['options'] = ['Moon', 'Sun', 'Earth', 'Mars'];
            $fallback['answer'] = 'Sun';
            $fallback['text'] = 'Complete the sentence:';
            break;
        case 'matching':
            $fallback['question'] = 'Match the words with their meanings.';
            $fallback['pairs'] = [
                ['left' => 'Apple', 'right' => 'A fruit that is red or green'],
                ['left' => 'Car', 'right' => 'A vehicle with four wheels'],
                ['left' => 'Dog', 'right' => 'A loyal pet animal']
            ];
            break;
        case 'listen_and_choose':
            $fallback['question'] = 'Listen and choose the correct word.';
            $fallback['tts_text'] = 'The correct answer is option B.';
            $fallback['options'] = ['Option A', 'Option B', 'Option C', 'Option D'];
            $fallback['answer'] = 'Option B';
            break;
        case 'identify_meaning':
            $fallback['word'] = 'Benevolent';
            $fallback['context'] = 'The benevolent king helped his people.';
            $fallback['question'] = 'What does "benevolent" mean?';
            $fallback['options'] = ['Cruel', 'Kind', 'Rich', 'Poor'];
            $fallback['answer'] = 'Kind';
            break;
        case 'choose_missing':
            $fallback['text'] = 'She ___ to the store yesterday.';
            $fallback['question'] = 'Choose the correct word.';
            $fallback['options'] = ['go', 'went', 'gone', 'going'];
            $fallback['answer'] = 'went';
            break;
        case 'writing':
            $fallback['question'] = 'Write a short paragraph about your favorite hobby. Include why you enjoy it and how often you do it. (Minimum 3 sentences)';
            $fallback['min_sentences'] = 3;
            break;
        case 'translation':
            $fallback['question'] = 'Translate to English: "Bonjour"';
            $fallback['answer'] = 'Hello';
            break;
        case 'error_correction':
            $fallback['incorrect_sentence'] = 'He go to school every day.';
            $fallback['question'] = 'Correct the sentence:';
            $fallback['correct_sentence'] = 'He goes to school every day.';
            break;
        case 'sentence_scramble':
            $fallback['question'] = 'Arrange the words to form a correct sentence.';
            $fallback['word_bank'] = ['the', 'quick', 'brown', 'fox', 'jumps'];
            $fallback['correct_answer'] = 'the quick brown fox jumps';
            break;
        case 'word_bank':
            $fallback['question'] = 'Use the words to form a sentence.';
            $fallback['word_bank'] = ['I', 'like', 'to', 'read', 'books'];
            $fallback['correct_answer'] = 'I like to read books';
            break;
        case 'tap_hear':
            $fallback['question'] = 'Listen and tap the words in order.';
            $fallback['tts_text'] = 'The sky is blue';
            $fallback['word_bank'] = ['The', 'sky', 'is', 'blue', 'clouds'];
            $fallback['correct_answer'] = ['The', 'sky', 'is', 'blue'];
            break;
        case 'listen_and_type':
            $fallback['question'] = 'Listen and type what you hear.';
            $fallback['tts_text'] = 'The weather is nice today.';
            $fallback['answer'] = 'The weather is nice today';
            break;
        case 'speaking':
        case 'pronunciation':
            $fallback['speaking_type'] = 'open_ended';
            $fallback['question'] = 'Please describe your daily routine in 2-3 sentences.';
            $fallback['tts_text'] = 'Describe your daily routine.';
            break;
        case 'read_answer':
            $fallback['text'] = 'The Amazon rainforest is often called the "lungs of the Earth" because it produces about 20% of the world\'s oxygen. It is home to millions of species of plants and animals.';
            $fallback['question'] = 'Why is the Amazon rainforest called the "lungs of the Earth"?';
            $fallback['options'] = [
                'Because it has many trees',
                'Because it produces oxygen',
                'Because it is in South America',
                'Because it rains a lot'
            ];
            $fallback['answer'] = 'Because it produces oxygen';
            break;
        case 'picture_word':
            $fallback['image_url'] = '';
            $fallback['question'] = 'What is this?';
            $fallback['options'] = ['Apple', 'Banana', 'Orange', 'Grape'];
            $fallback['answer'] = 'Apple';
            break;
    }
    
    return $fallback;
}

// Build the balanced exam
$all_exercises = buildBalancedExam($all_exercises, $questions_per_category, $question_categories);

// Store the session ID for this exam attempt
$exam_session_id = session_id() . '_' . time();
$_SESSION['current_exam_questions'] = array_map(function($ex) { return $ex['id']; }, $all_exercises);

?>
<!DOCTYPE html>
<html lang="<?= $ui_lang ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<title><?= htmlspecialchars($lesson_info['name'] ?? 'Adaptive Exam') ?> · Playmates</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons/css/flag-icons.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{
  --sand:#f5ede6;--sand-mid:#ede3d8;--sand-dark:#ddd0c2;
  --sage:#c6dfd5;--sage-mid:#8bbfad;--sage-dark:#3a7a68;--sage-deep:#225548;
  --butter:#f5e8b0;--peach:#f5d6c4;--blush:#f9d8cd;
  --ink:#1a1a18;--ink-mid:#4a4845;--muted:#8a8178;--soft:#b8b0a5;
  --surface:#faf8f5;--border:rgba(0,0,0,0.07);
  --radius-sm:8px;--radius:14px;--radius-lg:20px;
  --transition:0.2s cubic-bezier(.4,0,.2,1);
}
body{font-family:'DM Sans',sans-serif;background:var(--sand);color:var(--ink);min-height:100vh}
#progress-bar{position:fixed;top:0;left:0;right:0;height:4px;background:var(--sand-dark);z-index:999}
#progress-fill{height:100%;background:linear-gradient(90deg,var(--sage-dark),var(--sage-mid));width:0;transition:width .35s}
.hud-timer{position:fixed;top:14px;right:14px;background:var(--ink);border-radius:30px;padding:6px 16px;font-family:'DM Serif Display',serif;font-size:15px;color:#fff;z-index:1000;display:flex;align-items:center;gap:6px}
.hud-timer.danger{color:var(--peach);animation:pulse .5s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:0.55}}
.hud-warning{position:fixed;bottom:16px;left:14px;background:rgba(26,26,24,0.6);border-radius:30px;padding:5px 13px;font-size:11px;color:rgba(255,255,255,0.85);z-index:1000;font-weight:500}
.hud-warning.warning-level{background:rgba(180,68,40,0.85);color:#fff;animation:pulseWarning .8s infinite}
@keyframes pulseWarning{0%,100%{opacity:1}50%{opacity:0.6}}
.nav-back{position:fixed;top:36px;left:14px;z-index:1000;display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:30px;background:var(--surface);border:1px solid var(--border);font-size:12px;font-weight:500;color:var(--ink);text-decoration:none;transition:opacity var(--transition);box-shadow:0 2px 8px rgba(0,0,0,0.06)}
.nav-back:hover{opacity:0.8}
#overview{max-width:620px;margin:0 auto;padding:72px 18px 60px}
.ov-hero{background:var(--sage-deep);border-radius:var(--radius-lg);padding:30px 26px 26px;margin-bottom:16px;color:#fff}
.ov-label{display:inline-flex;align-items:center;gap:7px;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:0.8px;color:rgba(255,255,255,0.5);margin-bottom:12px}
.ov-title{font-family:'DM Serif Display',serif;font-size:28px;letter-spacing:-0.3px;margin-bottom:6px}
.ov-desc{font-size:13px;color:rgba(255,255,255,0.6);line-height:1.65;margin-bottom:20px}
.ov-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:20px}
.ov-stat{background:rgba(255,255,255,0.08);border-radius:var(--radius-sm);padding:13px 8px;text-align:center}
.ov-stat-n{font-family:'DM Serif Display',serif;font-size:22px;margin-bottom:3px}
.ov-stat-l{font-size:9px;font-weight:500;text-transform:uppercase;letter-spacing:0.8px;color:rgba(255,255,255,0.45)}
.xp-badge{display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,0.12);border-radius:30px;padding:6px 14px;font-size:12px;font-weight:500;color:var(--butter)}
.adaptive-row{display:flex;flex-wrap:wrap;gap:7px;margin-top:16px;padding-top:16px;border-top:1px solid rgba(255,255,255,0.1);font-size:11px;color:rgba(255,255,255,0.45);align-items:center}
.adap-badge{background:rgba(255,255,255,0.1);border-radius:30px;padding:3px 10px;color:rgba(255,255,255,0.65);font-weight:500;font-size:10px}
.start-exam-btn{width:100%;padding:15px;border:none;border-radius:var(--radius-sm);background:var(--ink);color:#fff;font-family:'DM Sans',sans-serif;font-size:14px;font-weight:500;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:opacity var(--transition)}
.start-exam-btn:hover{opacity:0.85}
#lesson{display:none;max-width:620px;margin:0 auto;padding:16px 18px 60px}
.lesson-header{display:flex;align-items:center;gap:10px;margin-bottom:20px;background:var(--surface);border:1px solid var(--border);border-radius:30px;padding:9px 14px}
.close-btn{width:32px;height:32px;border-radius:50%;background:#fdf0ed;border:1px solid #f5d6c4;color:#7a3020;font-size:13px;font-weight:700;cursor:pointer;flex-shrink:0;display:flex;align-items:center;justify-content:center;transition:opacity var(--transition)}
.close-btn:hover{opacity:0.75}
.lesson-prog-wrap{flex:1;background:var(--sand-dark);height:6px;border-radius:10px;overflow:hidden}
.lesson-prog{height:100%;background:linear-gradient(90deg,var(--sage-dark),var(--sage-mid));width:0;transition:width .35s}
.lesson-counter{font-size:11px;font-weight:500;color:var(--muted);white-space:nowrap}
.ex-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);padding:26px;box-shadow:0 4px 20px rgba(26,26,24,0.06);animation:slideUp .25s ease}
@keyframes slideUp{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
.ex-meta{display:flex;align-items:center;gap:7px;margin-bottom:18px;flex-wrap:wrap}
.ex-type{background:var(--sand-mid);border-radius:30px;padding:4px 11px;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:0.6px;color:var(--ink-mid)}
.ex-difficulty{border-radius:30px;padding:4px 10px;font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:0.6px}
.diff-easy{background:#e6f4f0;color:var(--sage-deep)}
.diff-medium{background:#fef8ec;color:#7a5c1e}
.diff-hard{background:#fdf0ed;color:#7a3020}
.ex-topic{background:var(--sand);border:1px solid var(--border);border-radius:30px;padding:4px 10px;font-size:10px;color:var(--muted)}
.ex-num{font-size:11px;color:var(--soft);margin-left:auto}
.ex-xp{font-size:11px;font-weight:500;color:var(--sage-dark)}
.question-box{background:var(--sage);border-radius:var(--radius-sm);padding:18px 20px;margin-bottom:20px;font-family:'DM Serif Display',serif;font-size:20px;color:var(--sage-deep);line-height:1.4;border:1px solid var(--sage-mid)}
.mcq-grid{display:flex;flex-direction:column;gap:8px;margin-bottom:16px}
.mcq-btn{display:flex;align-items:center;gap:11px;padding:12px 14px;border:1.5px solid var(--sand-dark);border-radius:var(--radius-sm);background:var(--surface);font-family:'DM Sans',sans-serif;font-size:13px;font-weight:500;cursor:pointer;text-align:left;width:100%;transition:border-color .15s,background .15s}
.mcq-btn:hover{border-color:var(--sage-mid);background:var(--sage)}
.opt-key{width:28px;height:28px;border-radius:7px;flex-shrink:0;display:flex;align-items:center;justify-content:center;background:var(--sand-mid);border:1px solid var(--sand-dark);font-size:11px;font-weight:500;color:var(--muted)}
.text-input,.writing-textarea{width:100%;padding:12px 14px;border:1.5px solid var(--sand-dark);border-radius:var(--radius-sm);font-size:13px;font-family:'DM Sans',sans-serif;font-weight:500;background:#fff;color:var(--ink);outline:none;margin-bottom:14px;transition:border-color var(--transition),box-shadow var(--transition)}
.text-input:focus,.writing-textarea:focus{border-color:var(--sage-dark);box-shadow:0 0 0 3px rgba(58,122,104,0.12)}
.writing-textarea{min-height:130px;resize:vertical}
.tf-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px}
.tf-btn{padding:18px;border:1.5px solid var(--sand-dark);border-radius:var(--radius-sm);font-family:'DM Serif Display',serif;font-size:20px;cursor:pointer;background:var(--surface);text-align:center;transition:all .15s}
.tf-btn:hover{transform:translateY(-2px)}
.mcq-btn.selected{border-color:var(--sage-dark);background:var(--sage);color:var(--sage-deep)}
.tf-btn.selected{background:var(--butter);border-color:#c8921a;color:var(--ink)}
.read-context{background:var(--butter);border-radius:var(--radius-sm);padding:13px 16px;margin-bottom:14px;font-size:13px;line-height:1.65;border:1px solid rgba(245,200,80,0.35);color:var(--ink-mid)}
.audio-btn,.mic-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;background:var(--sage-dark);color:#fff;border:none;border-radius:30px;padding:11px 24px;font-family:'DM Sans',sans-serif;font-weight:500;font-size:13px;cursor:pointer;margin:0 8px 16px 0;width:auto;min-width:160px;transition:opacity var(--transition)}
.audio-btn:hover,.mic-btn:hover{opacity:0.85}
.mic-btn{background:var(--ink)}
.mic-btn.listening{background:var(--sage-dark);opacity:0.8}
.mic-btn.listening::before{content:"🔴";margin-right:6px}
.ex-image{width:100%;max-height:220px;object-fit:cover;border-radius:var(--radius-sm);border:1px solid var(--border);margin-bottom:16px}
.question-controls{display:flex;justify-content:flex-end;margin-top:18px}
.question-nav-btn{width:auto;min-width:120px;border:1px solid var(--sand-dark);border-radius:30px;padding:12px 24px;font-size:13px;font-weight:600;font-family:'DM Sans',sans-serif;background:var(--surface);color:var(--ink);cursor:pointer;transition:opacity var(--transition), background var(--transition)}
.question-nav-btn:hover:not(:disabled){opacity:0.9;background:var(--sage)}
.question-nav-btn:disabled{opacity:0.45;cursor:not-allowed}
.question-nav-btn-primary{background:var(--ink);border-color:var(--ink);color:#fff}
.sentence-builder{min-height:52px;background:var(--sand);border:1.5px dashed var(--sand-dark);border-radius:var(--radius-sm);padding:10px 14px;display:flex;flex-wrap:wrap;gap:7px;align-items:center;margin-bottom:12px}
.builder-placeholder{color:var(--soft);font-style:italic;font-size:12px}
.built-word{background:var(--sage-dark);color:#fff;border-radius:7px;padding:6px 10px;font-size:12px;font-weight:500;display:inline-flex;align-items:center;gap:5px;cursor:pointer}
.built-word .rm{width:15px;height:15px;background:rgba(255,255,255,0.25);border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:700}
.word-bank{display:flex;flex-wrap:wrap;gap:7px;margin-bottom:16px;padding:12px;background:var(--sand);border-radius:var(--radius-sm);border:1px solid var(--border)}
.bank-word{background:var(--surface);border:1.5px solid var(--sand-dark);border-radius:7px;padding:7px 13px;font-size:12px;font-weight:500;cursor:pointer;transition:all .15s}
.bank-word:hover{background:var(--sage);border-color:var(--sage-mid)}
.bank-word.used{opacity:0.3;pointer-events:none;text-decoration:line-through}
.match-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px}
.match-col{display:flex;flex-direction:column;gap:12px}
.match-item{padding:14px 16px;border:2px solid var(--sand-dark);border-radius:var(--radius-sm);text-align:center;cursor:pointer;background:var(--surface);font-size:14px;font-weight:500;transition:all 0.2s ease}
.match-item:hover{background:var(--sage);border-color:var(--sage-mid);transform:translateY(-2px)}
.match-item.selected{background:var(--butter);border-color:#c8921a;box-shadow:0 2px 8px rgba(0,0,0,0.1)}
.match-item.matched{background:#e6f4f0;border-color:var(--sage-dark);color:var(--sage-deep);cursor:default;opacity:0.7;transform:none}
.match-item.matched:hover{transform:none}
.match-score{font-size:14px;font-weight:600;margin-bottom:16px;text-align:center;color:var(--sage-dark);padding:10px;background:var(--sand);border-radius:var(--radius-sm)}
.completion{background:var(--sage-deep);border-radius:var(--radius-lg);padding:36px 26px;text-align:center;color:#fff}
.comp-title{font-family:'DM Serif Display',serif;font-size:28px;margin-bottom:6px}
.comp-sub{font-size:13px;color:rgba(255,255,255,0.6);margin-bottom:24px}
.comp-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:24px}
.comp-stat{background:rgba(255,255,255,0.1);border-radius:var(--radius-sm);padding:16px 10px;text-align:center}
.comp-stat-n{font-family:'DM Serif Display',serif;font-size:28px;margin-bottom:3px}
.comp-stat-l{font-size:9px;font-weight:500;text-transform:uppercase;letter-spacing:0.8px;color:rgba(255,255,255,0.45)}
.comp-btn{padding:12px 24px;border-radius:30px;border:none;background:rgba(255,255,255,0.9);color:var(--sage-deep);font-family:'DM Sans',sans-serif;font-size:13px;font-weight:500;cursor:pointer;transition:opacity var(--transition)}
.comp-btn:hover{opacity:0.85}
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(26,26,24,0.5);z-index:2000;align-items:center;justify-content:center}
.modal-overlay.open{display:flex}
.modal-box{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-lg);padding:28px 24px;max-width:320px;width:90%;text-align:center;box-shadow:0 20px 60px rgba(26,26,24,0.2)}
.modal-title{font-family:'DM Serif Display',serif;font-size:22px;margin-bottom:7px}
.modal-sub{font-size:13px;color:var(--muted);margin-bottom:22px;line-height:1.6}
.modal-actions{display:flex;gap:10px;justify-content:center}
.modal-stay{flex:1;padding:12px;border:1.5px solid var(--sand-dark);border-radius:30px;background:var(--surface);font-family:'DM Sans',sans-serif;font-size:13px;font-weight:500;cursor:pointer;transition:border-color var(--transition)}
.modal-stay:hover{border-color:var(--sage-dark)}
.modal-leave{flex:1;padding:12px;border:none;border-radius:30px;background:#fdf0ed;color:#7a3020;font-family:'DM Sans',sans-serif;font-size:13px;font-weight:500;cursor:pointer;transition:opacity var(--transition)}
.modal-leave:hover{opacity:0.8}
.dismissed-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.85);z-index:10000;display:flex;align-items:center;justify-content:center;backdrop-filter:blur(8px)}
.dismissed-card{background:var(--surface);border-radius:var(--radius-lg);padding:40px 30px;max-width:480px;width:90%;text-align:center;border:2px solid #f5d6c4;animation:shake 0.5s ease}
@keyframes shake{0%,100%{transform:translateX(0)}25%{transform:translateX(-8px)}75%{transform:translateX(8px)}}
.dismissed-icon{font-size:64px;margin-bottom:20px}
.dismissed-title{font-family:'DM Serif Display',serif;font-size:28px;color:#7a3020;margin-bottom:12px}
.dismissed-message{font-size:14px;color:var(--ink-mid);margin-bottom:24px;line-height:1.6}
.dismissed-btn{padding:12px 28px;background:#fdf0ed;border:1px solid #f5d6c4;border-radius:30px;font-family:'DM Sans',sans-serif;font-size:14px;font-weight:500;color:#7a3020;cursor:pointer;transition:opacity var(--transition)}
.dismissed-btn:hover{opacity:0.8}
#questionTimerDisplay{position:fixed;top:14px;right:180px;background:rgba(26,26,24,0.85);border-radius:30px;padding:6px 16px;font-family:'DM Serif Display',serif;font-size:13px;color:var(--peach);z-index:1000;display:flex;align-items:center;gap:6px}
#questionTimerDisplay.danger{color:#fff;background:#7a3020;animation:pulse .5s infinite}
.speaking-instruction{background:var(--sage);border-radius:var(--radius-sm);padding:16px;margin-bottom:16px;text-align:center;font-size:14px;color:var(--sage-deep);border:1px solid var(--sage-mid)}
.mic-btn-large{padding:12px 28px;font-size:14px;min-width:200px}
.button-group{display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-bottom:16px}
.match-header{font-weight:600;margin-bottom:10px;padding:8px;text-align:center;background:var(--sand);border-radius:var(--radius-sm);color:var(--sage-dark)}
.picture-word-img{width:100%;max-height:250px;object-fit:contain;border-radius:var(--radius-sm);margin-bottom:16px;background:var(--surface);padding:10px;border:1px solid var(--border)}
@media(max-width:540px){#overview{padding:58px 14px 40px}#lesson{padding:12px 14px 40px}.ex-card{padding:18px 14px}.question-box{font-size:16px;padding:14px 16px}.ov-stats{grid-template-columns:1fr 1fr}.comp-stats{grid-template-columns:repeat(3,1fr)}.tf-grid{grid-template-columns:1fr 1fr}#questionTimerDisplay{right:14px;top:70px;font-size:11px;padding:4px 12px}.hud-timer{top:14px;right:14px;font-size:12px;padding:4px 12px}.button-group{gap:6px}.button-group .audio-btn,.button-group .mic-btn{flex:1;min-width:0;margin-right:0;padding-left:10px;padding-right:10px}.mic-btn-large{padding:10px 14px;font-size:13px;min-width:0}}
</style>
</head>
<body>

<div id="progress-bar"><div id="progress-fill"></div></div>
<div class="hud-timer" id="timerDisplay">⏱ <span id="timerSeconds">60:00</span></div>
<div id="questionTimerDisplay" style="display:none;">⏳ Q<span id="currentQuestionTimer">00</span>s</div>
<div class="hud-warning" id="warningCounter">🚫 Exam mode — do not exit</div>

<a href="dashboard.php?pair=<?= urlencode($pair) ?>&direction=<?= urlencode($direction) ?>&level=<?= $level ?>&ui_lang=<?= $ui_lang ?>" class="nav-back">
  <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 15 15"><path d="M9 3L4 7.5L9 12"/></svg>
  Dashboard
</a>

<div id="overview">
  <div class="ov-hero">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:14px">
      <div class="ov-label">
        Level <?= (int)$level ?>
        <?php if ($lesson_direction): ?>
          <span style="background:rgba(255,255,255,0.12);border-radius:30px;padding:2px 9px;font-size:9px;display:inline-flex;align-items:center;gap:4px;"><?= $direction_icon ?></span>
        <?php endif; ?>
      </div>
      <div class="xp-badge">⭐ <span id="ov-xp">0</span> XP earned</div>
    </div>
    <div class="ov-title"><?= htmlspecialchars($lesson_info['name'] ?? 'Adaptive Exam') ?></div>
    <div class="ov-desc"><?= htmlspecialchars($lesson_info['description'] ?? 'Complete exam covering all question types.') ?></div>
    <div class="ov-stats">
      <div class="ov-stat"><div class="ov-stat-n"><?= count($all_exercises) ?></div><div class="ov-stat-l">Questions</div></div>
      <div class="ov-stat"><div class="ov-stat-n" id="ov-xp-total">0</div><div class="ov-stat-l">Total XP</div></div>
      <div class="ov-stat"><div class="ov-stat-n">Balanced</div><div class="ov-stat-l">Categories</div></div>
      <div class="ov-stat"><div class="ov-stat-n">No Repeats</div><div class="ov-stat-l">Per Session</div></div>
    </div>
    <div class="adaptive-row">
      <span>🎯 Balanced by type</span>
      <span class="adap-badge">📝 Fixed Answer (10)</span>
      <span class="adap-badge">✍️ Constructed (6)</span>
      <span class="adap-badge">🎤 Speaking (2)</span>
      <span class="adap-badge">📖 Reading (2)</span>
    </div>
  </div>
  <button id="startLessonBtn" class="start-exam-btn">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 15 15"><path d="M4 2l9 5.5L4 13V2z" stroke-linejoin="round"/></svg>
    Start Adaptive Exam
  </button>
</div>

<div id="lesson">
  <div class="lesson-header">
    <button class="close-btn" onclick="confirmClose()">✕</button>
    <?php if ($lesson_direction): ?>
      <span style="font-size:10px;background:var(--sand-mid);color:var(--muted);padding:3px 9px;border-radius:30px;display:inline-flex;align-items:center;gap:4px;white-space:nowrap;"><?= $direction_icon ?></span>
    <?php endif; ?>
    <div class="lesson-prog-wrap"><div class="lesson-prog" id="lesson-prog"></div></div>
    <span class="lesson-counter" id="lesson-counter">0/<?= count($all_exercises) ?></span>
    <div class="lesson-xp-pill" style="display:none;">⭐ <span id="lesson-xp">0</span></div>
  </div>
  <div id="lesson-main"></div>
</div>

<div class="modal-overlay" id="close-modal">
  <div class="modal-box">
    <div style="font-size:2rem;margin-bottom:10px">🚪</div>
    <div class="modal-title">Leave Exam?</div>
    <div class="modal-sub">Your progress will be saved. You can return and continue later.</div>
    <div class="modal-actions">
      <button class="modal-stay" onclick="document.getElementById('close-modal').classList.remove('open')">Stay</button>
      <button class="modal-leave" onclick="closeLesson()">Leave</button>
    </div>
  </div>
</div>
<div class="modal-overlay" id="warning-modal">
  <div class="modal-box">
    <div style="font-size:2rem;margin-bottom:10px">⚠️</div>
    <div class="modal-title">Exam warning</div>
    <div class="modal-sub" id="warning-text">Please stay in fullscreen mode.</div>
    <div class="modal-actions">
      <button class="modal-stay" onclick="hideSecurityWarning()">Continue exam</button>
    </div>
  </div>
</div>

<script>
const EXERCISES = <?php
$js_exercises = [];
foreach ($all_exercises as $ex) {
    $js_exercises[] = [
        'id' => $ex['id'],
        'type' => $ex['type'],
        'xp' => $ex['xp'],
        'difficulty' => $ex['difficulty'] ?? 'medium',
        'topic' => $ex['topic'] ?? 'general',
        'question' => $ex['question'] ?? '',
        'options' => $ex['options'] ?? [],
        'answer' => $ex['answer'] ?? $ex['correct'] ?? '',
        'tts_text' => $ex['tts_text'] ?? '',
        'text' => $ex['text'] ?? '',
        'image_url' => $ex['image_url'] ?? $ex['image'] ?? $ex['img'] ?? $ex['img_url'] ?? '',
        'audio_url' => $ex['audio_url'] ?? $ex['audio'] ?? null,
        'word_bank' => $ex['word_bank'] ?? [],
        'correct_answer' => $ex['correct_answer'] ?? $ex['answer'] ?? '',
        'pairs' => $ex['pairs'] ?? [],
        'flashcards' => $ex['flashcards'] ?? $ex['cards'] ?? [],
        'segments' => $ex['segments'] ?? $ex['story_segments'] ?? [],
        'story_exercises' => $ex['exercises'] ?? $ex['story_exercises'] ?? [],
        'bot' => $ex['bot'] ?? '',
        'incorrect_sentence' => $ex['incorrect_sentence'] ?? '',
        'prompt' => $ex['prompt'] ?? '',
        'title' => $ex['title'] ?? '',
        'questions' => $ex['questions'] ?? [],
        'time_limit_seconds' => $ex['time_limit_seconds'] ?? 45,
        'min_sentences' => $ex['min_sentences'] ?? 3,
        'correct' => $ex['correct'] ?? false,
        'context' => $ex['context'] ?? '',
        'word' => $ex['word'] ?? '',
        'incorrect' => $ex['incorrect'] ?? '',
        'explanation' => $ex['explanation'] ?? '',
        'sentence' => $ex['sentence'] ?? '',
        'explanation_required' => $ex['explanation_required'] ?? false,
        'expected_keywords' => $ex['expected_keywords'] ?? [],
        'correct_response_keywords' => $ex['correct_response_keywords'] ?? [],
        'translation' => $ex['translation'] ?? '',
        'speaking_type' => $ex['speaking_type'] ?? 'open_ended',
        'story' => $ex['story'] ?? $ex['content'] ?? ''
    ];
}
echo json_encode($js_exercises, JSON_UNESCAPED_UNICODE);
?>;

// ========== EXAM SUBMISSION VARIABLES ==========
let examStartTime = Date.now();
// Use the PHP-validated attempt_id, not from URL
let attemptId = <?= $js_attempt_id ?> || null;
console.log('[INIT] ===== EXAM PAGE LOADED =====');
console.log('[INIT] PHP-validated attemptId:', attemptId);
console.log('[INIT] URL attempt_id param was:', <?= (int)($_GET['attempt_id'] ?? 0) ?>);
console.log('[INIT] User ID:', <?= $user_id ?>);
console.log('[INIT] URL:', window.location.href);

// Also store in sessionStorage for recovery
if (attemptId) {
    sessionStorage.setItem('current_exam_attempt_id', attemptId);
    console.log('[INIT] Stored attemptId in sessionStorage');
}

// ========== SPEECH SYNTHESIS WITH FEMALE VOICE ==========
let currentUtterance = null;
let availableVoices = [];

// Load available voices
function loadVoices() {
    return new Promise((resolve) => {
        if (window.speechSynthesis.getVoices().length) {
            availableVoices = window.speechSynthesis.getVoices();
            resolve(availableVoices);
        } else {
            window.speechSynthesis.onvoiceschanged = () => {
                availableVoices = window.speechSynthesis.getVoices();
                resolve(availableVoices);
            };
        }
    });
}

// Get a female voice
function getFemaleVoice() {
    // Priority order: Google UK Female, Google US Female, any female-sounding voice, then default
    const femaleVoices = availableVoices.filter(voice => 
        voice.name.includes('Google UK') || 
        voice.name.includes('Google US') ||
        voice.name.toLowerCase().includes('female') ||
        voice.name.toLowerCase().includes('samantha') ||
        voice.name.toLowerCase().includes('victoria') ||
        voice.name.toLowerCase().includes('karen')
    );
    
    // Also try to find English female voices
    const englishFemale = availableVoices.filter(voice => 
        voice.lang.startsWith('en') && 
        (voice.name.toLowerCase().includes('female') || 
         voice.name.toLowerCase().includes('samantha') ||
         voice.name.toLowerCase().includes('victoria'))
    );
    
    if (englishFemale.length > 0) return englishFemale[0];
    if (femaleVoices.length > 0) return femaleVoices[0];
    
    // Fallback: any English voice
    const englishVoice = availableVoices.find(voice => voice.lang.startsWith('en'));
    if (englishVoice) return englishVoice;
    
    return null;
}

async function speakText(text, btn) {
    if (!text || text === '') {
        if (btn) {
            const originalText = btn.innerHTML;
            btn.innerHTML = '⚠ No text to speak';
            setTimeout(() => { btn.innerHTML = originalText; }, 1500);
        }
        return;
    }
    
    if (!window.speechSynthesis) {
        if (btn) {
            const originalText = btn.innerHTML;
            btn.innerHTML = '❌ Speech not supported';
            setTimeout(() => { btn.innerHTML = originalText; }, 1500);
        }
        return;
    }
    
    // Cancel any ongoing speech
    if (window.speechSynthesis.speaking || window.speechSynthesis.pending) {
        window.speechSynthesis.cancel();
    }
    
    const originalText = btn ? btn.innerHTML : '';
    if (btn) {
        btn.innerHTML = '🔊 Playing...';
        btn.disabled = true;
    }
    
    // Wait for voices to load if needed
    if (availableVoices.length === 0) {
        await loadVoices();
    }
    
    const utterance = new SpeechSynthesisUtterance(text);
    
    // Set female voice if available
    const femaleVoice = getFemaleVoice();
    if (femaleVoice) {
        utterance.voice = femaleVoice;
    }
    
    utterance.rate = 0.85;
    utterance.pitch = 1.1; // Slightly higher pitch for more natural female voice
    utterance.volume = 1.0;
    
    utterance.onend = () => {
        if (btn) {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
        currentUtterance = null;
    };
    
    utterance.onerror = () => {
        if (btn) {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
        currentUtterance = null;
    };
    
    currentUtterance = utterance;
    window.speechSynthesis.speak(utterance);
}

function normalizeText(text) {
    return String(text || '')
        .toLowerCase()
        .trim()
        .replace(/[^\w\s]/g, '')
        .replace(/\s+/g, ' ')
        .trim();
}

function calculateSimilarity(str1, str2) {
    const norm1 = normalizeText(str1);
    const norm2 = normalizeText(str2);
    
    if (norm1 === norm2) return 1.0;
    if (norm1.length === 0 || norm2.length === 0) return 0.0;
    
    const words1 = norm1.split(' ');
    const words2 = norm2.split(' ');
    
    let matches = 0;
    for (let w1 of words1) {
        for (let w2 of words2) {
            if (w1 === w2 || w1.includes(w2) || w2.includes(w1)) {
                matches++;
                break;
            }
        }
    }
    
    const maxLen = Math.max(words1.length, words2.length);
    return matches / maxLen;
}

// ========== SPEAKING/PRONUNCIATION EXERCISE HANDLER ==========
let speakingStream = null;
let speakingRecorder = null;
let speakingChunks = [];
let speakingTimeout = null;
let recordedAudioBlob = null;
let speechRecognition = null;
let speakingAnswerCorrect = false;

function initSpeechRecognition() {
    if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
        console.log('Speech recognition not supported');
        return false;
    }
    const SpeechRecognitionAPI = window.SpeechRecognition || window.webkitSpeechRecognition;
    speechRecognition = new SpeechRecognitionAPI();
    speechRecognition.continuous = false;
    speechRecognition.interimResults = false;
    speechRecognition.lang = 'en-US';
    return true;
}

async function handleSpeakingExercise(ex, btn) {
    if (isLocked || examDismissed) return;
    
    const originalText = btn.innerHTML;
    const isRecording = btn.dataset.recording === 'true';
    const isRepetition = ex.speaking_type === 'repetition';
    
    if (isRecording) {
        // Stop recording
        if (speakingTimeout) clearTimeout(speakingTimeout);
        
        if (isRepetition && speechRecognition) {
            speechRecognition.stop();
        } else if (speakingRecorder && speakingRecorder.state === 'recording') {
            speakingRecorder.stop();
        }
        
        btn.dataset.recording = 'false';
        btn.classList.remove('listening');
        btn.innerHTML = originalText;
        
        // Enable continue button after recording stops
        setTimeout(() => {
            if (!currentAnswerSelected && !isLocked) {
                currentAnswerSelected = true;
                setQuestionControlsState(true);
            }
        }, 500);
        return;
    }
    
    // Start recording
    btn.dataset.recording = 'true';
    btn.classList.add('listening');
    btn.innerHTML = '⏺ Recording... Tap to stop';
    
    if (isRepetition && (window.SpeechRecognition || window.webkitSpeechRecognition)) {
        // Use speech recognition for repetition questions
        if (!speechRecognition) {
            const SpeechRecognitionAPI = window.SpeechRecognition || window.webkitSpeechRecognition;
            speechRecognition = new SpeechRecognitionAPI();
            speechRecognition.continuous = false;
            speechRecognition.interimResults = false;
            speechRecognition.lang = 'en-US';
        }
        
        speechRecognition.onresult = (event) => {
            const transcript = event.results[0][0].transcript;
            const expectedText = ex.prompt || ex.tts_text || ex.question || '';
            const similarity = calculateSimilarity(transcript, expectedText);
            const isCorrect = similarity >= 0.7;
            
            btn.dataset.speakingResult = isCorrect ? 'correct' : 'incorrect';
            btn.dataset.recording = 'false';
            btn.classList.remove('listening');
            btn.innerHTML = originalText;
            
            if (!currentAnswerSelected && !isLocked) {
                currentAnswerSelected = true;
                setQuestionControlsState(true);
            }
        };
        
        speechRecognition.onerror = () => {
            btn.dataset.speakingResult = 'incorrect';
            btn.dataset.recording = 'false';
            btn.classList.remove('listening');
            btn.innerHTML = originalText;
            
            if (!currentAnswerSelected && !isLocked) {
                currentAnswerSelected = true;
                setQuestionControlsState(true);
            }
        };
        
        speechRecognition.start();
        
        // Auto-stop after 15 seconds
        speakingTimeout = setTimeout(() => {
            if (btn.dataset.recording === 'true' && speechRecognition) {
                speechRecognition.stop();
            }
        }, 15000);
    } 
    else {
        // Use audio recording for open-ended questions
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            speakingStream = stream;
            speakingRecorder = new MediaRecorder(stream);
            speakingChunks = [];
            
            speakingRecorder.ondataavailable = (event) => {
                if (event.data && event.data.size > 0) {
                    speakingChunks.push(event.data);
                }
            };
            
            speakingRecorder.onstop = () => {
                if (speakingChunks.length > 0) {
                    recordedAudioBlob = new Blob(speakingChunks, { type: 'audio/webm' });
                }
                
                if (speakingStream) {
                    speakingStream.getTracks().forEach(track => track.stop());
                    speakingStream = null;
                }
                
                btn.dataset.recording = 'false';
                btn.classList.remove('listening');
                btn.innerHTML = originalText;
                
                if (!currentAnswerSelected && !isLocked) {
                    currentAnswerSelected = true;
                    setQuestionControlsState(true);
                }
            };
            
            speakingRecorder.start();
            
            // Auto-stop after 30 seconds
            speakingTimeout = setTimeout(() => {
                if (speakingRecorder && speakingRecorder.state === 'recording') {
                    speakingRecorder.stop();
                }
            }, 30000);
            
        } catch (err) {
            console.error('Microphone error:', err);
            btn.dataset.recording = 'false';
            btn.classList.remove('listening');
            btn.innerHTML = '❌ Mic access denied';
            setTimeout(() => {
                if (btn) btn.innerHTML = originalText;
            }, 2000);
        }
    }
}

// ========== VALIDATION FUNCTIONS ==========
function validateAndEnableButton() {
    if (examDismissed) return;
    const ex = queue[currentIndex];
    if (!ex) return;
    
    let hasAnswer = false;
    
    if (ex.type === 'multiple_choice' || ex.type === 'fill_blank' || ex.type === 'picture_word' || 
        ex.type === 'listen_and_choose' || ex.type === 'identify_meaning' || ex.type === 'choose_missing') {
        hasAnswer = !!document.querySelector('.mcq-btn.selected');
    }
    else if (ex.type === 'true_false') {
        hasAnswer = !!document.querySelector('.tf-btn.selected');
    }
    else if (ex.type === 'writing' || (ex.type === 'read_answer' && !ex.options?.length)) {
        const ta = document.getElementById('writingTextarea');
        hasAnswer = ta && ta.value.trim().length > 0;
    }
    else if (ex.type === 'translation' || ex.type === 'listen_and_type' || ex.type === 'error_correction' || 
             ex.type === 'conversation') {
        const input = document.getElementById('answerInput');
        hasAnswer = input && input.value.trim().length > 0;
    }
    else if (ex.type === 'speaking' || ex.type === 'pronunciation') {
        const recordBtn = document.getElementById('speak-record-btn');
        if (recordBtn) {
            if (ex.speaking_type === 'repetition') {
                hasAnswer = recordBtn.dataset.speakingResult !== undefined;
            } else {
                hasAnswer = speakingChunks.length > 0;
            }
        }
    }
    else if (ex.type === 'word_bank' || ex.type === 'sentence_scramble' || ex.type === 'tap_hear') {
        hasAnswer = builtWords.length > 0;
    }
    else if (ex.type === 'matching' || ex.type === 'speed_matching') {
        const matchedCount = document.querySelectorAll('.match-item.matched').length;
        const totalPairs = document.querySelectorAll('.match-item[data-side="left"]').length;
        hasAnswer = matchedCount === totalPairs * 2 && totalPairs > 0;
    }
    else {
        hasAnswer = true;
    }
    
    if (hasAnswer && !currentAnswerSelected) {
        currentAnswerSelected = true;
        setQuestionControlsState(true);
    } else if (!hasAnswer) {
        currentAnswerSelected = false;
        setQuestionControlsState(false);
    }
}

function handleTextAnswer() {
    if (isLocked || examDismissed) return;
    validateAndEnableButton();
}

function handleWritingAnswer() {
    if (isLocked || examDismissed) return;
    validateAndEnableButton();
}

function handleMCQ(btn) {
    if (isLocked || examDismissed) return;
    const container = btn.closest('.mcq-grid');
    if (container) {
        container.querySelectorAll('.mcq-btn').forEach(b => b.classList.remove('selected'));
    }
    btn.classList.add('selected');
    validateAndEnableButton();
}

function handleTF(btn) {
    if (isLocked || examDismissed) return;
    const container = btn.closest('.tf-grid');
    if (container) {
        container.querySelectorAll('.tf-btn').forEach(b => b.classList.remove('selected'));
    }
    btn.classList.add('selected');
    validateAndEnableButton();
}

function setQuestionControlsState(enabled) {
    if (examDismissed) return;
    const actionBtn = document.getElementById('questionActionBtn');
    if (actionBtn) {
        actionBtn.disabled = !enabled;
        actionBtn.textContent = (currentIndex >= queue.length - 1) ? 'Submit Exam' : 'Continue';
    }
}

// ========== PER-QUESTION TIMER ==========
let questionTimer = null;

function startQuestionTimer(seconds, onExpire) {
    if (questionTimer) clearInterval(questionTimer);
    
    let timeLeft = Math.max(0, parseInt(seconds, 10) || 45);
    updateTimerDisplay(timeLeft);
    
    const timerContainer = document.getElementById('questionTimerDisplay');
    if (timerContainer) timerContainer.style.display = 'flex';
    
    questionTimer = setInterval(() => {
        if (examDismissed) {
            if (questionTimer) clearInterval(questionTimer);
            return;
        }
        
        if (timeLeft > 0) {
            timeLeft--;
            updateTimerDisplay(timeLeft);
            
            if (timeLeft <= 5) {
                const timerEl = document.getElementById('questionTimerDisplay');
                if (timerEl) timerEl.classList.add('danger');
            }
            
            if (timeLeft <= 0) {
                clearInterval(questionTimer);
                const timerEl = document.getElementById('questionTimerDisplay');
                if (timerEl) timerEl.classList.remove('danger');
                if (onExpire && !isLocked && !examDismissed) {
                    onExpire();
                }
            }
        } else {
            clearInterval(questionTimer);
        }
    }, 1000);
}

function updateTimerDisplay(seconds) {
    const timerElement = document.getElementById('currentQuestionTimer');
    if (timerElement) {
        timerElement.textContent = String(Math.max(0, seconds)).padStart(2, '0');
    }
}

function hideQuestionTimer() {
    if (questionTimer) {
        clearInterval(questionTimer);
        questionTimer = null;
    }
    const timerContainer = document.getElementById('questionTimerDisplay');
    if (timerContainer) {
        timerContainer.style.display = 'none';
        timerContainer.classList.remove('danger');
    }
}

function autoSubmitCurrentQuestion() {
    if (isLocked || examDismissed) return;
    
    const ex = queue[currentIndex];
    if (ex) {
        isLocked = true;
        
        let hasAnswer = false;
        
        if (ex.type === 'multiple_choice' || ex.type === 'fill_blank' || ex.type === 'picture_word' || 
            ex.type === 'listen_and_choose' || ex.type === 'identify_meaning' || ex.type === 'choose_missing') {
            hasAnswer = !!document.querySelector('.mcq-btn.selected');
        }
        else if (ex.type === 'true_false') {
            hasAnswer = !!document.querySelector('.tf-btn.selected');
        }
        else if (ex.type === 'writing' || (ex.type === 'read_answer' && !ex.options?.length)) {
            const ta = document.getElementById('writingTextarea');
            hasAnswer = ta && ta.value.length > 0;
        }
        else if (ex.type === 'translation' || ex.type === 'listen_and_type' || ex.type === 'error_correction' || 
                 ex.type === 'conversation') {
            const input = document.getElementById('answerInput');
            hasAnswer = input && input.value.length > 0;
        }
        else if (ex.type === 'speaking' || ex.type === 'pronunciation') {
            const recordBtn = document.getElementById('speak-record-btn');
            if (recordBtn) {
                if (ex.speaking_type === 'repetition') {
                    hasAnswer = recordBtn.dataset.speakingResult !== undefined;
                } else {
                    hasAnswer = speakingChunks.length > 0;
                }
            }
        }
        else if (ex.type === 'matching' || ex.type === 'speed_matching') {
            const matchedCount = document.querySelectorAll('.match-item.matched').length;
            const totalPairs = document.querySelectorAll('.match-item[data-side="left"]').length;
            hasAnswer = matchedCount === totalPairs * 2 && totalPairs > 0;
        }
        else if (ex.type === 'word_bank' || ex.type === 'sentence_scramble' || ex.type === 'tap_hear') {
            hasAnswer = builtWords.length > 0;
        }
        else {
            hasAnswer = true;
        }
        
        recordAnswer(ex.id, hasAnswer, hasAnswer ? ex.xp : 0, ex.time_limit_seconds || 45);
        setQuestionControlsState(false);
        hideQuestionTimer();
        
        setTimeout(() => {
            if (currentIndex >= queue.length - 1) {
                showCompletion();
            } else {
                currentIndex++;
                currentAnswerSelected = false;
                isLocked = false;
                builtWords = [];
                speakingChunks = [];
                recordedAudioBlob = null;
                renderExercise();
            }
        }, 500);
    }
}

// ========== RECORD ANSWER - NO FEEDBACK ==========
function recordAnswer(exId, ok, xp, rt) {
    console.log(`[RECORD] exId=${exId}, ok=${ok}, xp=${xp}, isLocked_before=${isLocked}`);
    if (isLocked || examDismissed) {
        console.warn(`[RECORD] BLOCKED: isLocked=${isLocked}, examDismissed=${examDismissed}`);
        return;
    }
    isLocked = true;
    if (questionTimer) clearInterval(questionTimer);
    
    if (ok) { 
        earnedXP += xp; 
        correctCount++; 
        console.log(`  [RECORD] Answer CORRECT! correctCount now=${correctCount}, earnedXP=${earnedXP}`);
    } else {
        console.log(`❌ [RECORD] Answer INCORRECT. correctCount unchanged=${correctCount}`);
    }
    
    updateProgress();
}

// ========== ADVANCE QUESTION ==========
function advanceQuestion() {
    if (!currentAnswerSelected || isLocked || examDismissed) return;
    
    hideQuestionTimer();
    
    const ex = queue[currentIndex];
    if (!ex) return;
    
    let ok = false;
    let hasAnswer = false;
    
    if (ex.type === 'multiple_choice' || ex.type === 'fill_blank' || ex.type === 'picture_word' || 
        ex.type === 'listen_and_choose' || ex.type === 'identify_meaning' || ex.type === 'choose_missing') {
        const chosen = document.querySelector('.mcq-btn.selected')?.dataset.option;
        const correctValue = String(ex.answer || ex.correct_answer || '').trim();
        ok = chosen ? chosen.toLowerCase() === correctValue.toLowerCase() : false;
        hasAnswer = !!chosen;
    }
    else if (ex.type === 'grammar') {
        const chosen = document.querySelector('.mcq-btn.selected')?.dataset.option;
        const correctValue = String(ex.answer || ex.correct_answer || '').trim();
        ok = chosen ? chosen.toLowerCase() === correctValue.toLowerCase() : false;
        hasAnswer = !!chosen;
    }
    else if (ex.type === 'true_false') {
        const chosen = document.querySelector('.tf-btn.selected')?.dataset.value;
        const rawCorrect = ex.correct ?? ex.answer ?? ex.correct_answer ?? false;
        const correctStr = (String(rawCorrect).trim().toLowerCase() === 'true') ? 'true' : 'false';
        ok = chosen === correctStr;
        hasAnswer = !!chosen;
    }
    else if (ex.type === 'speaking' || ex.type === 'pronunciation') {
        const recordBtn = document.getElementById('speak-record-btn');
        if (ex.speaking_type === 'repetition') {
            const result = recordBtn?.dataset.speakingResult;
            ok = result === 'correct';
            hasAnswer = result !== undefined;
        } else {
            ok = true;
            hasAnswer = speakingChunks.length > 0;
        }
    }
    else if (ex.type === 'translation' || ex.type === 'listen_and_type' || ex.type === 'error_correction') {
        const rawAnswer = document.getElementById('answerInput')?.value || '';
        hasAnswer = rawAnswer.length > 0;
        if (ex.answer || ex.correct_answer) {
            const u = rawAnswer.trim().toLowerCase();
            const c = String(ex.answer || ex.correct_answer || '').trim().toLowerCase();
            ok = u === c || (Math.abs(u.length - c.length) <= 2 && [...u].filter((ch, i) => ch !== c[i]).length <= 2);
        } else {
            ok = hasAnswer;
        }
    }
    else if (ex.type === 'conversation' || ex.type === 'writing' || (ex.type === 'read_answer' && !ex.options?.length)) {
        const rawAnswer = document.getElementById('writingTextarea')?.value || document.getElementById('answerInput')?.value || '';
        hasAnswer = rawAnswer.length > 0;
        ok = hasAnswer;
    }
    else if (ex.type === 'word_bank' || ex.type === 'sentence_scramble' || ex.type === 'tap_hear') {
        const rawCorrect = ex.correct_answer || ex.answer || '';
        const correctArr = Array.isArray(rawCorrect) ? rawCorrect.map(String) : String(rawCorrect).trim().replace(/[.,!?;:]+$/, '').split(/\s+/).filter(Boolean);
        const builtNorm = builtWords.map(w => w.toLowerCase());
        const correctNorm = correctArr.map(w => w.toLowerCase());
        ok = JSON.stringify(builtNorm) === JSON.stringify(correctNorm);
        hasAnswer = builtWords.length > 0;
    }
    else if (ex.type === 'matching' || ex.type === 'speed_matching') {
        const matchedCount = document.querySelectorAll('.match-item.matched').length;
        const totalPairs = document.querySelectorAll('.match-item[data-side="left"]').length;
        ok = matchedCount === totalPairs * 2 && totalPairs > 0;
        hasAnswer = matchedCount > 0;
    }
    else {
        ok = true;
        hasAnswer = true;
    }
    
    const xpEarned = ok ? ex.xp : 0;
    
    recordAnswer(ex.id, ok, xpEarned, (Date.now() - questionStartTime) / 1000);
    isLocked = true;
    
    if (currentIndex >= queue.length - 1) {
        showCompletion();
    } else {
        setTimeout(() => {
            currentIndex++;
            currentAnswerSelected = false;
            isLocked = false;
            builtWords = [];
            speakingChunks = [];
            recordedAudioBlob = null;
            renderExercise();
        }, 300);
    }
}

// ========== MATCHING FUNCTIONS ==========
let matchSelectedLeftItem = null;

function normalizeMatchingPairs(pairs) {
    if (!pairs || !Array.isArray(pairs)) return [];
    return pairs.map(pair => {
        let left = '';
        let right = '';
        
        if (typeof pair === 'object') {
            left = pair.left || pair.l || pair.term || pair.word || pair.item || '';
            right = pair.right || pair.r || pair.definition || pair.meaning || pair.match || '';
        }
        
        return { left: String(left).trim(), right: String(right).trim() };
    }).filter(p => p.left !== '' && p.right !== '');
}

function setupMatching(pairs) {
    const normalizedPairs = normalizeMatchingPairs(pairs);
    matchSelectedLeftItem = null;
    
    const scoreEl = document.getElementById('match-score');
    const totalPairs = normalizedPairs.length;
    if (scoreEl) scoreEl.textContent = `0 / ${totalPairs} matched`;
    
    document.querySelectorAll('.match-item').forEach(item => {
        item.classList.remove('matched', 'selected');
    });
    
    document.querySelectorAll('.match-item[data-side="left"]').forEach((item, idx) => {
        if (normalizedPairs[idx]) {
            item.dataset.correctMatch = normalizedPairs[idx].right;
        }
    });
    
    document.querySelectorAll('.match-item[data-side="right"]').forEach((item) => {
        const matchingLeft = normalizedPairs.find(p => p.right === item.dataset.val);
        if (matchingLeft) {
            item.dataset.correctMatch = matchingLeft.left;
        }
    });
    
    document.querySelectorAll('.match-item').forEach(item => {
        item.onclick = () => {
            if (isLocked || examDismissed) return;
            if (item.classList.contains('matched')) return;
            
            if (item.dataset.side === 'left') {
                document.querySelectorAll('.match-item.selected').forEach(el => {
                    el.classList.remove('selected');
                });
                item.classList.add('selected');
                matchSelectedLeftItem = item;
            } 
            else if (item.dataset.side === 'right' && matchSelectedLeftItem) {
                const leftVal = matchSelectedLeftItem.dataset.val;
                const rightVal = item.dataset.val;
                const expectedRight = matchSelectedLeftItem.dataset.correctMatch;
                const expectedLeft = item.dataset.correctMatch;
                
                if (leftVal === expectedLeft && rightVal === expectedRight) {
                    matchSelectedLeftItem.classList.add('matched');
                    item.classList.add('matched');
                    matchSelectedLeftItem.classList.remove('selected');
                    
                    const currentMatched = document.querySelectorAll('.match-item.matched').length;
                    const matchedPairs = currentMatched / 2;
                    if (scoreEl) scoreEl.textContent = `${matchedPairs} / ${totalPairs} matched`;
                    
                    if (matchedPairs === totalPairs && totalPairs > 0) {
                        validateAndEnableButton();
                    }
                }
                matchSelectedLeftItem.classList.remove('selected');
                matchSelectedLeftItem = null;
            }
        };
    });
}

// ========== RENDER EXERCISE ==========
let builtWords = [];

function renderPictureWord(ex, letters) {
    let html = '';
    const pictureUrl = ex.image_url || ex.image || ex.img || ex.img_url || null;
    
    if (pictureUrl && pictureUrl !== '') {
        let imgSrc = pictureUrl;
        if (!imgSrc.startsWith('http') && !imgSrc.startsWith('/')) {
            imgSrc = '../' + imgSrc;
        }
        html += `<img src="${escapeHtml(imgSrc)}" class="picture-word-img" onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22150%22 viewBox=%220 0 200 150%22%3E%3Crect width=%22200%22 height=%22150%22 fill=%22%23ddd%22/%3E%3Ctext x=%22100%22 y=%2275%22 text-anchor=%22middle%22 fill=%22%23999%22%3E🖼️ No image%3C/text%3E%3C/svg%3E'; this.style.objectFit='contain';">`;
    } else {
        html += `<div class="read-context" style="text-align:center;background:var(--sand);padding:30px;">`;
        html += `<span style="font-size:48px;">🖼️</span>`;
        html += `<div style="font-size:12px;color:var(--muted);margin-top:8px;">Select the correct word based on the description</div>`;
        html += `</div>`;
    }
    
    html += `<div class="question-box">${escapeHtml(ex.question)}</div>`;
    html += `<div class="mcq-grid">`;
    (ex.options || []).forEach((opt, i) => {
        html += `<button class="mcq-btn" data-option="${escapeHtml(opt)}"><span class="opt-key">${letters[i]}</span>${escapeHtml(opt)}</button>`;
    });
    html += `</div>`;
    return html;
}

function setupWordBank(words) {
    builtWords = [];
    const bank = document.getElementById('bank-wb');
    const builder = document.getElementById('builder-wb');
    if (!bank || !builder) return;
    
    builder.innerHTML = '<span class="builder-placeholder">Tap words below to build sentence…</span>';
    builder.classList.remove('has-words');
    
    bank.querySelectorAll('.bank-word').forEach(el => {
        el.classList.remove('used');
        
        el.onclick = () => {
            if (el.classList.contains('used')) return;
            el.classList.add('used');
            builtWords.push(el.dataset.word);
            renderBuilderWords(builder, bank, builtWords);
            validateAndEnableButton();
        };
    });
}

function renderBuilderWords(builder, bank, words) {
    if (words.length === 0) {
        builder.innerHTML = '<span class="builder-placeholder">Tap words below to build sentence…</span>';
        builder.classList.remove('has-words');
        return;
    }
    builder.classList.add('has-words');
    builder.innerHTML = words.map((w, i) => `<span class="built-word" data-idx="${i}">${escapeHtml(w)} <span class="rm">✕</span></span>`).join('');
    builder.querySelectorAll('.built-word').forEach(wordEl => {
        wordEl.querySelector('.rm').onclick = () => {
            const idx = parseInt(wordEl.dataset.idx);
            const removed = builtWords.splice(idx, 1)[0];
            const bankWord = [...bank.querySelectorAll('.bank-word')].find(bw => bw.dataset.word === removed);
            if (bankWord) bankWord.classList.remove('used');
            renderBuilderWords(builder, bank, builtWords);
            validateAndEnableButton();
        };
    });
}

function renderExercise() {
    if (examDismissed) return;
    if (currentIndex >= queue.length) { showCompletion(); return; }
    
    isLocked = false;
    currentAnswerSelected = false;
    questionStartTime = Date.now();
    builtWords = [];
    speakingChunks = [];
    recordedAudioBlob = null;
    matchSelectedLeftItem = null;
    
    if (questionTimer) clearInterval(questionTimer);
    hideQuestionTimer();
    
    scheduleImageCaptures();
    
    const ex = queue[currentIndex];
    if (!ex) { advanceQuestion(); return; }

    timedChallengeActive = false;
    timedChallengeIndex = 0;
    timedChallengeScore = 0;
    timedChallengeQuestions = [];

    const main = document.getElementById('lesson-main');
    const typeName = ex.type.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
    const letters = ['A', 'B', 'C', 'D', 'E', 'F'];
    const imageSrc = ex.image_url || ex.image || ex.img || ex.img_url || null;
    
    let html = `<div class="ex-card"><div class="ex-meta">
        <span class="ex-type">${typeName}</span>
        <span class="ex-topic">${escapeHtml(ex.topic)}</span>
        <span class="ex-num">${currentIndex + 1}/${queue.length}</span>
        <span class="ex-xp">+${ex.xp} XP</span>
    </div>`;
    
    html += `<div class="audio-wrap" style="margin-bottom:16px"></div>`;
    
    if (imageSrc && ex.type !== 'picture_word') {
        let imgSrc = imageSrc;
        if (!imgSrc.startsWith('http') && !imgSrc.startsWith('/')) imgSrc = '../' + imgSrc;
        html += `<img src="${escapeHtml(imgSrc)}" class="ex-image" onerror="this.style.display='none'">`;
    }

    // SPEAKING / PRONUNCIATION EXERCISE
    if (ex.type === 'speaking' || ex.type === 'pronunciation') {
        const promptText = ex.prompt || ex.tts_text || ex.question;
        const isRepetition = ex.speaking_type === 'repetition';
        
        html += `<div class="question-box">🗣️ ${escapeHtml(ex.question)}</div>`;
        
        if (isRepetition && promptText) {
            html += `<div class="speaking-instruction" style="background:var(--sage);">
                🎧 <strong>Listen and Repeat</strong><br>
                1. Click the "Listen" button to hear the audio<br>
                2. Click "Record" and speak clearly<br>
                3. Click "Record" again to stop<br>
                4. Click "Continue" to proceed
            </div>`;
            html += `<div class="button-group" style="justify-content:center;">`;
            html += `<button class="audio-btn" id="speak-audio-btn" style="background:var(--sage-dark);">🔊 Listen to audio</button>`;
            html += `<button class="mic-btn mic-btn-large" id="speak-record-btn" style="background:var(--ink);">🎙️ Tap to record</button>`;
            html += `</div>`;
        } else {
            html += `<div class="speaking-instruction" style="background:var(--sage);">
                🎙️ <strong>Record Your Answer</strong><br>
                1. Click the "Record" button below<br>
                2. Speak your answer clearly<br>
                3. Click "Record" again to stop<br>
                4. Click "Continue" to proceed
            </div>`;
            html += `<div class="button-group" style="justify-content:center;">`;
            html += `<button class="mic-btn mic-btn-large" id="speak-record-btn" style="background:var(--ink);">🎙️ Tap to record</button>`;
            html += `</div>`;
        }
        
        html += `<div id="speaking-status" class="read-context" style="margin-top:12px; text-align:center; font-size:12px; background:var(--sand);">
            ⚪ Ready to record
        </div>`;
    }
    // PICTURE WORD
    else if (ex.type === 'picture_word') {
        html += renderPictureWord(ex, letters);
    }
    // GRAMMAR EXERCISE
    else if (ex.type === 'grammar') {
        if (ex.sentence) html += `<div class="read-context" style="background:var(--sage);color:var(--sage-deep);">📖 ${escapeHtml(ex.sentence)}</div>`;
        html += `<div class="question-box">${escapeHtml(ex.question)}</div>`;
        html += `<div class="mcq-grid">`;
        (ex.options || []).forEach((opt, i) => {
            html += `<button class="mcq-btn" data-option="${escapeHtml(opt)}"><span class="opt-key">${letters[i]}</span>${escapeHtml(opt)}</button>`;
        });
        if (ex.explanation_required) {
            html += `<textarea class="writing-textarea" id="grammarExplanation" rows="3" placeholder="Explain why this is correct..."></textarea>`;
        }
        html += `</div>`;
    }
    // CONVERSATION EXERCISE
    else if (ex.type === 'conversation') {
        const botMsg = ex.bot || '';
        html += `<div class="question-box">🤖 ${escapeHtml(botMsg)}</div>`;
        if (ex.translation) html += `<div style="font-size:11px;color:var(--muted);text-align:center;margin-bottom:12px;">"${escapeHtml(ex.translation)}"</div>`;
        if (ex.options && ex.options.length) {
            html += `<div class="mcq-grid">`;
            (ex.options || []).forEach((opt, i) => {
                html += `<button class="mcq-btn" data-option="${escapeHtml(opt)}"><span class="opt-key">${letters[i]}</span>${escapeHtml(opt)}</button>`;
            });
            html += `</div>`;
        } else {
            html += `<textarea class="writing-textarea" id="answerInput" rows="3" placeholder="Type your response..." style="margin-bottom:8px;"></textarea>`;
        }
    }
    // MULTIPLE CHOICE / FILL BLANK
    else if (ex.type === 'fill_blank' || ex.type === 'multiple_choice') {
        if (ex.text) html += `<div class="read-context">${escapeHtml(ex.text)}</div>`;
        html += `<div class="question-box">${escapeHtml(ex.question)}</div>`;
        html += `<div class="mcq-grid">`;
        (ex.options || []).forEach((opt, i) => {
            html += `<button class="mcq-btn" data-option="${escapeHtml(opt)}"><span class="opt-key">${letters[i]}</span>${escapeHtml(opt)}</button>`;
        });
        html += `</div>`;
    }
    // TRANSLATION
    else if (ex.type === 'translation') {
        html += `<div class="question-box">${escapeHtml(ex.question)}</div>`;
        html += `<input type="text" class="text-input" id="answerInput" placeholder="Type your translation…" autocomplete="off">`;
    }
    // TRUE/FALSE
    else if (ex.type === 'true_false') {
        html += `<div class="question-box">${escapeHtml(ex.question)}</div>`;
        html += `<div class="tf-grid">
            <button class="tf-btn true-btn" data-value="true">✓ True</button>
            <button class="tf-btn false-btn" data-value="false">✗ False</button>
        </div>`;
    }
    // WORD BANK / SENTENCE SCRAMBLE / TAP HEAR
    else if (ex.type === 'word_bank' || ex.type === 'sentence_scramble' || ex.type === 'tap_hear') {
        html += `<div class="question-box">${escapeHtml(ex.question)}</div>`;
        if (ex.type === 'tap_hear' && ex.tts_text) {
            html += `<div class="button-group"><button class="audio-btn" id="tap-hear-audio">🔊 Play audio first</button></div>`;
        }
        html += `<div class="sentence-builder" id="builder-wb"><span class="builder-placeholder">Tap words below to build sentence…</span></div>`;
        html += `<div class="word-bank" id="bank-wb">`;
        const shuffledWords = [...(ex.word_bank || [])].sort(() => Math.random() - 0.5);
        shuffledWords.forEach(w => { html += `<span class="bank-word" data-word="${escapeHtml(w)}">${escapeHtml(w)}</span>`; });
        html += `</div>`;
    }
    // LISTEN AND CHOOSE
    else if (ex.type === 'listen_and_choose' || ex.type === 'listening_comprehension') {
        html += `<div class="question-box">${escapeHtml(ex.question)}</div>`;
        if (ex.tts_text) {
            html += `<div class="button-group"><button class="audio-btn" id="listen-audio">🔊 Play audio</button></div>`;
        }
        html += `<div class="mcq-grid">`;
        (ex.options || []).forEach((opt, i) => {
            html += `<button class="mcq-btn" data-option="${escapeHtml(opt)}"><span class="opt-key">${letters[i]}</span>${escapeHtml(opt)}</button>`;
        });
        html += `</div>`;
    }
    // LISTEN AND TYPE
    else if (ex.type === 'listen_and_type') {
        html += `<div class="question-box">${escapeHtml(ex.question)}</div>`;
        if (ex.tts_text) {
            html += `<div class="button-group"><button class="audio-btn" id="listen-type-audio">🔊 Play audio</button></div>`;
        }
        html += `<input type="text" class="text-input" id="answerInput" placeholder="Type what you hear…" autocomplete="off">`;
    }
    // READ ANSWER
    else if (ex.type === 'read_answer') {
        if (ex.text) html += `<div class="read-context">${escapeHtml(ex.text)}</div>`;
        html += `<div class="question-box">${escapeHtml(ex.question)}</div>`;
        if (ex.options && ex.options.length) {
            html += `<div class="mcq-grid">`;
            (ex.options || []).forEach((opt, i) => {
                html += `<button class="mcq-btn" data-option="${escapeHtml(opt)}"><span class="opt-key">${letters[i]}</span>${escapeHtml(opt)}</button>`;
            });
            html += `</div>`;
        } else {
            html += `<textarea class="writing-textarea" id="writingTextarea" rows="4" placeholder="Write your answer here..."></textarea>`;
        }
    }
    // ERROR CORRECTION
    else if (ex.type === 'error_correction') {
        html += `<div class="question-box">✏️ ${escapeHtml(ex.question)}</div>`;
        html += `<div class="read-context" style="background:#fdf0ed;border-left:4px solid #f5d6c4;">❌ <strong>Incorrect:</strong> ${escapeHtml(ex.incorrect || ex.incorrect_sentence)}</div>`;
        html += `<input type="text" class="text-input" id="answerInput" placeholder="Type the corrected sentence..." autocomplete="off">`;
        if (ex.explanation) html += `<div style="font-size:11px;color:var(--muted);margin-bottom:8px;">💡 ${escapeHtml(ex.explanation)}</div>`;
    }
    // MATCHING
    else if (ex.type === 'matching' || ex.type === 'speed_matching') {
        const normalizedPairs = normalizeMatchingPairs(ex.pairs || []);
        const leftItems = normalizedPairs.map(p => p.left);
        const rightItems = [...normalizedPairs.map(p => p.right)].sort(() => Math.random() - 0.5);
        
        html += `<div class="question-box">${escapeHtml(ex.question)}</div>`;
        html += `<div class="match-score" id="match-score">0 / ${normalizedPairs.length} matched</div>`;
        html += `<div class="match-grid">`;
        
        html += `<div class="match-col">`;
        html += `<div class="match-header">📝 Terms</div>`;
        leftItems.forEach((item, idx) => {
            html += `<div class="match-item" data-side="left" data-val="${escapeHtml(item)}">${escapeHtml(item)}</div>`;
        });
        html += `</div>`;
        
        html += `<div class="match-col">`;
        html += `<div class="match-header">📖 Definitions</div>`;
        rightItems.forEach((item) => {
            html += `<div class="match-item" data-side="right" data-val="${escapeHtml(item)}">${escapeHtml(item)}</div>`;
        });
        html += `</div>`;
        
        html += `</div>`;
        html += `<div class="read-context" style="margin-top:12px; text-align:center; font-size:12px;">💡 Click a term, then click a definition to match them.</div>`;
    }
    // WRITING
    else if (ex.type === 'writing') {
        html += `<div class="question-box">✍️ ${escapeHtml(ex.question)}</div>`;
        html += `<textarea class="writing-textarea" id="writingTextarea" rows="6" placeholder="Write your answer here..."></textarea>`;
        if (ex.min_sentences) {
            html += `<div class="read-context" style="margin-top:8px; font-size:11px;">💡 Minimum ${ex.min_sentences} sentences required.</div>`;
        }
    }
    // DEFAULT
    else {
        html += `<div class="question-box">${escapeHtml(ex.question)}</div>`;
        if (ex.options && ex.options.length) {
            html += `<div class="mcq-grid">`;
            (ex.options || []).forEach((opt, i) => {
                html += `<button class="mcq-btn" data-option="${escapeHtml(opt)}"><span class="opt-key">${letters[i]}</span>${escapeHtml(opt)}</button>`;
            });
            html += `</div>`;
        } else {
            html += `<input type="text" class="text-input" id="answerInput" placeholder="Type your answer…" autocomplete="off">`;
        }
    }

    html += `<div class="question-controls"><button class="question-nav-btn question-nav-btn-primary" id="questionActionBtn" disabled>Continue</button></div></div>`;
    main.innerHTML = html;
    
    initAudioPlayer(ex);
    
    const actionBtn = document.getElementById('questionActionBtn');
    if (actionBtn) {
        actionBtn.disabled = true;
        actionBtn.onclick = advanceQuestion;
    }
    
    // Speaking/Pronunciation setup
    if (ex.type === 'speaking' || ex.type === 'pronunciation') {
        const promptText = ex.prompt || ex.tts_text || ex.question;
        const isRepetition = ex.speaking_type === 'repetition';
        
        const audioBtn = document.getElementById('speak-audio-btn');
        if (audioBtn && promptText) {
            audioBtn.onclick = (e) => {
                e.preventDefault();
                speakText(promptText, audioBtn);
            };
        }
        
        const recordBtn = document.getElementById('speak-record-btn');
        const statusDiv = document.getElementById('speaking-status');
        
        if (recordBtn) {
            recordBtn.onclick = async (e) => {
                e.preventDefault();
                
                const isRecording = recordBtn.dataset.recording === 'true';
                
                if (!isRecording) {
                    if (statusDiv) {
                        statusDiv.innerHTML = '🔴 Recording in progress... Speak clearly!';
                        statusDiv.style.background = '#fdf0ed';
                    }
                    await handleSpeakingExercise(ex, recordBtn);
                } else {
                    if (statusDiv) {
                        statusDiv.innerHTML = '  Recording complete! Click Continue to proceed.';
                        statusDiv.style.background = '#e6f4f0';
                    }
                    handleSpeakingExercise(ex, recordBtn);
                }
            };
        }
    }
    
    // Grammar setup
    if (ex.type === 'grammar') {
        const explanationArea = document.getElementById('grammarExplanation');
        if (explanationArea) {
            explanationArea.addEventListener('input', handleTextAnswer);
        }
    }
    
    // Attach handlers for all MCQ and TF buttons
    document.querySelectorAll('.mcq-btn').forEach(btn => {
        btn.onclick = () => handleMCQ(btn);
    });
    
    document.querySelectorAll('.tf-btn').forEach(btn => {
        btn.onclick = () => handleTF(btn);
    });
    
    // Conversation setup
    if (ex.type === 'conversation') {
        const textarea = document.getElementById('answerInput');
        if (textarea) {
            textarea.addEventListener('input', handleTextAnswer);
        }
    }
    
    // Translation, error correction, listen_and_type setup
    if (ex.type === 'translation' || ex.type === 'listen_and_type' || ex.type === 'error_correction') {
        const answerInput = document.getElementById('answerInput');
        if (answerInput) {
            answerInput.addEventListener('input', handleTextAnswer);
            answerInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !isLocked && currentAnswerSelected) {
                    e.preventDefault();
                    advanceQuestion();
                }
            });
        }
    }
    
    // Writing setup
    if (ex.type === 'writing' || (ex.type === 'read_answer' && !ex.options?.length)) {
        const writingTextarea = document.getElementById('writingTextarea');
        if (writingTextarea) {
            writingTextarea.addEventListener('input', handleWritingAnswer);
        }
    }
    
    // Word bank setup
    if (['word_bank','sentence_scramble','tap_hear'].includes(ex.type) && (ex.word_bank || []).length) {
        setTimeout(() => {
            setupWordBank(ex.word_bank);
        }, 50);
        if (ex.type === 'tap_hear' && ex.tts_text) {
            const tapAudioBtn = document.getElementById('tap-hear-audio');
            if (tapAudioBtn) {
                tapAudioBtn.onclick = (e) => {
                    e.preventDefault();
                    speakText(ex.tts_text, tapAudioBtn);
                };
            }
        }
    }
    
    // Matching setup
    if (['matching','speed_matching'].includes(ex.type) && ex.pairs?.length) {
        setTimeout(() => {
            setupMatching(ex.pairs);
        }, 50);
    }
    
    // Audio buttons for listening exercises
    const listenAudio = document.getElementById('listen-audio');
    if (listenAudio && ex.tts_text) {
        listenAudio.onclick = (e) => {
            e.preventDefault();
            speakText(ex.tts_text, listenAudio);
        };
    }
    
    const listenTypeAudio = document.getElementById('listen-type-audio');
    if (listenTypeAudio && ex.tts_text) {
        listenTypeAudio.onclick = (e) => {
            e.preventDefault();
            speakText(ex.tts_text, listenTypeAudio);
        };
    }
    
    // Start per-question timer
    if (ex.type !== 'timed_challenge') {
        const timeLimit = ex.time_limit_seconds || 45;
        startQuestionTimer(timeLimit, () => autoSubmitCurrentQuestion());
    }
    
    setQuestionControlsState(false);
}

// ========== AUDIO PLAYER INITIALIZATION ==========
function initAudioPlayer(ex) {
    const audioWrap = document.querySelector('.audio-wrap');
    if (!audioWrap) return;
    
    const audioSrc = ex.audio_url || ex.listening_audio || ex.tap_hear_audio || ex.audio || null;
    const hasTTS = ex.tts_text || ex.prompt || ex.question;
    
    if (audioSrc) {
        let audioPath = audioSrc;
        if (!audioPath.startsWith('http') && !audioPath.startsWith('/') && !audioPath.startsWith('data:')) {
            audioPath = '../' + audioPath;
        }
        audioWrap.innerHTML = `
            <audio controls class="audio-player" style="width:100%; border-radius:8px; margin-bottom:16px; background:#f0f0f0; padding:8px;">
                <source src="${escapeHtml(audioPath)}" type="audio/mpeg">
                Your browser does not support the audio element.
            </audio>
        `;
        const audioPlayer = audioWrap.querySelector('audio');
        if (audioPlayer) audioPlayer.load();
    } else if (hasTTS) {
        audioWrap.innerHTML = '';
    }
}

// ========== EXAM CONTROL FUNCTIONS ==========
let queue = [], currentIndex = 0, earnedXP = 0, correctCount = 0;
let examTimer = null, examTimeLeft = 3600;
let isLocked = false, examActive = false, examDismissed = false;
let questionStartTime = 0, currentAnswerSelected = false;
let warningCount = 0;
let topicMap = {};

function shuffle(arr){ for(let i=arr.length-1;i>0;i--){const j=Math.floor(Math.random()*(i+1));[arr[i],arr[j]]=[arr[j],arr[i]];} return arr; }
function escapeHtml(s){ if(!s) return ''; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

function updateProgress(){
    const pct = queue.length ? ((currentIndex + 1)/queue.length)*100 : 0;
    const progressFill = document.getElementById('progress-fill');
    const lessonProg = document.getElementById('lesson-prog');
    if (progressFill) progressFill.style.width = pct + '%';
    if (lessonProg) lessonProg.style.width = pct + '%';
    const counter = document.getElementById('lesson-counter');
    if (counter) counter.textContent = (currentIndex + 1) + '/' + queue.length;
    const ovXp = document.getElementById('ov-xp');
    if (ovXp) ovXp.textContent = earnedXP;
}

function buildTopicMap() {
    EXERCISES.forEach(ex => { topicMap[ex.id] = ex.topic || 'general'; });
}

// ========== CAMERA FUNCTIONS ==========
let cameraStream = null, cameraCaptureTimers = [], cameraCanvas = null, cameraCtx = null, cameraPreviewVideo = null;
let currentQuestionImages = [], capturedImages = [];

function startCameraCapture() {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) return;
    navigator.mediaDevices.getUserMedia({ video: { width: 320, height: 240 } }).then(stream => {
        cameraStream = stream;
        cameraPreviewVideo = document.createElement('video');
        cameraPreviewVideo.srcObject = stream;
        cameraPreviewVideo.play();
        cameraCanvas = document.createElement('canvas');
        cameraCanvas.width = 320;
        cameraCanvas.height = 240;
        cameraCtx = cameraCanvas.getContext('2d');
        scheduleImageCaptures();
    }).catch(() => {});
}

function scheduleImageCaptures() {
    cameraCaptureTimers.forEach(timer => clearTimeout(timer));
    cameraCaptureTimers = [];
    currentQuestionImages = [];
    for (let i = 0; i < 4; i++) {
        cameraCaptureTimers.push(setTimeout(() => {
            if (examActive && !examDismissed && cameraStream && cameraCtx && cameraCanvas && cameraPreviewVideo && cameraPreviewVideo.readyState >= 2) {
                cameraCtx.drawImage(cameraPreviewVideo, 0, 0, cameraCanvas.width, cameraCanvas.height);
                currentQuestionImages.push({
                    data: cameraCanvas.toDataURL('image/jpeg', 0.5),
                    timestamp: new Date().toLocaleTimeString(),
                    questionNum: currentIndex + 1
                });
            }
        }, i * 10000));
    }
}

function stopCameraCapture() {
    return new Promise(resolve => {
        cameraCaptureTimers.forEach(timer => clearTimeout(timer));
        cameraCaptureTimers = [];
        if (cameraStream) {
            cameraStream.getTracks().forEach(track => track.stop());
            cameraStream = null;
        }
        resolve();
    });
}

// ========== SHOW COMPLETION WITH DATABASE SUBMISSION ==========
async function submitExamToBackend(score, correctCount, totalQuestions) {
    console.log(`[SUBMIT-START] attemptId=${attemptId}, score=${score}, correctCount=${correctCount}, totalQuestions=${totalQuestions}`);
    
    if (!attemptId) {
        console.error('🔴 NO ATTEMPT ID - Exam data NOT being saved to database');
        console.error('   examStartTime:', examStartTime);
        console.error('   URL params:', window.location.search);
        
        // Try to get attempt_id from session storage
        const storedAttemptId = sessionStorage.getItem('current_exam_attempt_id');
        if (storedAttemptId) {
            attemptId = storedAttemptId;
            console.log('📌 Retrieved attempt_id from sessionStorage:', attemptId);
        } else {
            alert('ERROR: No attempt ID found. Exam results cannot be saved. Please contact support.');
            return;
        }
    }

    const timeTaken = Math.floor((Date.now() - examStartTime) / 1000);
    
    // Collect all answers for detailed record
    const answers = [];
    for (let i = 0; i < queue.length; i++) {
        const ex = queue[i];
        answers.push({
            question_id: ex.id,
            question_type: ex.type,
            user_answer: 'Recorded', // In a real system, you'd capture the actual answer
            is_correct: i < correctCount // Simplified - in production, track per question
        });
    }
    
    const payload = {
        attempt_id: attemptId,
        score: score,
        correct_answers: correctCount,
        total_questions: totalQuestions,
        time_taken_seconds: timeTaken,
        answers: answers,
        completed_at: new Date().toISOString()
    };

    console.log('[SUBMIT-PAYLOAD] Sending:', JSON.stringify(payload));

    try {
        const response = await fetch('../backend/exam-api.php?action=submit_lessonexam', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        
        const text = await response.text();
        console.log('[SUBMIT-RAW] Response body:', text);
        
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('🔴 Failed to parse response as JSON:', e);
            throw new Error(`Invalid JSON response: ${text}`);
        }
        
        if (data.success) {
            console.log('  Exam submitted successfully:', data);
            if (data.data && data.data.certificate_id) {
                console.log('  Certificate ID created:', data.data.certificate_id);
            }
            return true;
        } else {
            console.error('🔴 Backend returned success=false:', data.message || data);
            alert('Warning: ' + (data.message || 'Could not save all results'));
            return false;
        }
    } catch (err) {
        console.error('🔴 Submission request failed:', err);
        alert('Failed to save exam results: ' + err.message);
        return false;
    }
}

function showCompletion() {
    console.log('[COMPLETION] ===== EXAM COMPLETION TRIGGERED =====');
    if (examDismissed) {
        console.log('[COMPLETION] examDismissed=true, returning');
        return;
    }
    if (examTimer) clearInterval(examTimer);
    if (questionTimer) clearInterval(questionTimer);
    hideQuestionTimer();
    examActive = false;
    stopCameraCapture();
    
    const score = queue.length > 0 ? Math.round((correctCount / queue.length) * 100) : 0;
    console.log(`[COMPLETION] score=${score}, correctCount=${correctCount}, totalQuestions=${queue.length}, earnedXP=${earnedXP}`);
    console.log('[COMPLETION] Calling submitExamToBackend()...');
    
    // Submit to database
    submitExamToBackend(score, correctCount, queue.length).then(() => {
        console.log('[COMPLETION] submitExamToBackend completed successfully');
        document.getElementById('lesson-main').innerHTML = `
            <div class="completion">
                <div style="font-size:3rem;margin-bottom:12px">✓</div>
                <div class="comp-title">Exam Completed</div>
                <div class="comp-sub">Score: ${score}%</div>
                <div class="comp-stats">
                    <div class="comp-stat"><div class="comp-stat-n">${correctCount}</div><div class="comp-stat-l">Correct</div></div>
                    <div class="comp-stat"><div class="comp-stat-n">${queue.length - correctCount}</div><div class="comp-stat-l">Incorrect</div></div>
                    <div class="comp-stat"><div class="comp-stat-n">${earnedXP}</div><div class="comp-stat-l">XP Earned</div></div>
                </div>
                <button class="comp-btn" onclick="backToOverview()">← Back to Dashboard</button>
            </div>`;
    }).catch(() => {
        // Still show completion even if DB save fails
        document.getElementById('lesson-main').innerHTML = `
            <div class="completion">
                <div style="font-size:3rem;margin-bottom:12px">✓</div>
                <div class="comp-title">Exam Completed</div>
                <div class="comp-sub">Score: ${score}%</div>
                <div class="comp-stats">
                    <div class="comp-stat"><div class="comp-stat-n">${correctCount}</div><div class="comp-stat-l">Correct</div></div>
                    <div class="comp-stat"><div class="comp-stat-n">${queue.length - correctCount}</div><div class="comp-stat-l">Incorrect</div></div>
                    <div class="comp-stat"><div class="comp-stat-n">${earnedXP}</div><div class="comp-stat-l">XP Earned</div></div>
                </div>
                <button class="comp-btn" onclick="backToOverview()">← Back to Dashboard</button>
            </div>`;
    });
    
    ['progress-fill', 'lesson-prog'].forEach(id => { const el = document.getElementById(id); if (el) el.style.width = '100%'; });
    if (document.exitFullscreen) document.exitFullscreen();
}

function backToOverview() {
    if (examTimer) clearInterval(examTimer);
    if (questionTimer) clearInterval(questionTimer);
    examActive = false;
    warningCount = 0;
    examDismissed = false;
    updateWarningCounter();
    document.getElementById('lesson').style.display = 'none';
    document.querySelector('.nav-back').style.display = '';
    document.getElementById('overview').style.display = 'block';
}

function confirmClose() { document.getElementById('close-modal').classList.add('open'); }
function closeLesson() {
    document.getElementById('close-modal').classList.remove('open');
    backToOverview();
}

// ========== TAB SWITCHING WARNING SYSTEM ==========
function updateWarningCounter() {
    const warningEl = document.getElementById('warningCounter');
    if (!warningEl) return;
    if (examDismissed) {
        warningEl.textContent = '⛔ EXAM DISMISSED — Rule violation';
        warningEl.classList.add('warning-level');
    } else if (warningCount === 0) {
        warningEl.textContent = '🚫 Exam mode — do not exit';
        warningEl.classList.remove('warning-level');
    } else if (warningCount < 7) {
        warningEl.textContent = `⚠️ WARNING ${warningCount}/7 — Do not switch tabs`;
        warningEl.classList.add('warning-level');
    } else if (warningCount >= 7) {
        warningEl.textContent = '⛔ EXAM DISMISSED — Rule violation';
        warningEl.classList.add('warning-level');
    }
}

function dismissExam(reason) {
    if (examDismissed) return;
    examDismissed = true;
    examActive = false;
    if (examTimer) clearInterval(examTimer);
    if (questionTimer) clearInterval(questionTimer);
    stopCameraCapture();
    isLocked = true;
    updateWarningCounter();
    
    const overlay = document.createElement('div');
    overlay.className = 'dismissed-overlay';
    overlay.innerHTML = `
        <div class="dismissed-card">
            <div class="dismissed-icon">🚫</div>
            <div class="dismissed-title">Exam Dismissed</div>
            <div class="dismissed-message">Your exam has been dismissed because you switched tabs ${warningCount} times, which violates the exam rules.</div>
            <button class="dismissed-btn" onclick="closeDismissedExam()">Return to Dashboard</button>
        </div>`;
    document.body.appendChild(overlay);
    
    document.getElementById('lesson-main').innerHTML = `
        <div class="completion" style="background:#fdf0ed; border:2px solid #f5d6c4;">
            <div style="font-size:3rem;margin-bottom:12px">🚫</div>
            <div class="comp-title" style="color:#7a3020">Exam Dismissed</div>
            <div class="comp-sub" style="color:#7a3020;">Your exam has been dismissed because you switched tabs ${warningCount} times, which violates the exam rules.</div>
            <button class="comp-btn dismissed-btn" onclick="closeDismissedExam()">← Back to Dashboard</button>
        </div>`;
}

function closeDismissedExam() {
    const overlay = document.querySelector('.dismissed-overlay');
    if (overlay) overlay.remove();
    backToOverview();
}

function handleTabSwitch() {
    if (!examActive || examDismissed) return;
    warningCount++;
    updateWarningCounter();
    if (warningCount >= 7) {
        dismissExam('User switched tabs ' + warningCount + ' times');
    } else {
        showSecurityWarning(`Warning ${warningCount}/7: You switched tabs. After 7 violations, your exam will be dismissed.`);
    }
}

function enterFullscreen() {
    if (document.documentElement.requestFullscreen) {
        document.documentElement.requestFullscreen().catch(() => {});
    }
}

function showSecurityWarning(message) {
    const modal = document.getElementById('warning-modal');
    const text = document.getElementById('warning-text');
    if (!modal || !text) return;
    text.textContent = message;
    modal.classList.add('open');
}

function hideSecurityWarning() {
    const modal = document.getElementById('warning-modal');
    if (!modal) return;
    modal.classList.remove('open');
}

async function startLesson() {
    if (EXERCISES.length === 0) { alert('No exercises available.'); return; }
    
    // Load voices before starting
    await loadVoices();
    
    queue = [...EXERCISES];
    currentIndex = 0;
    earnedXP = 0;
    correctCount = 0;
    warningCount = 0;
    examDismissed = false;
    examActive = true;
    examTimeLeft = 3600;
    
    buildTopicMap();
    hideQuestionTimer();
    updateWarningCounter();
    updateProgress();
    
    document.getElementById('overview').style.display = 'none';
    document.querySelector('.nav-back').style.display = 'none';
    document.getElementById('lesson').style.display = 'block';
    
    examTimer = setInterval(() => {
        if (examActive && !examDismissed && examTimeLeft > 0) {
            examTimeLeft--;
            const mins = Math.floor(examTimeLeft / 60);
            const secs = examTimeLeft % 60;
            const ts = document.getElementById('timerSeconds');
            if (ts) ts.textContent = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
            const td = document.getElementById('timerDisplay');
            if (td) td.classList.toggle('danger', examTimeLeft <= 300);
            if (examTimeLeft <= 0 && !examDismissed) {
                clearInterval(examTimer);
                showCompletion();
            }
        }
    }, 1000);
    
    enterFullscreen();
    startCameraCapture();
    renderExercise();
}

function initOverview() {
    const totalXp = EXERCISES.reduce((s, ex) => s + (ex.xp || 0), 0);
    const el = document.getElementById('ov-xp-total');
    if (el) el.textContent = totalXp;
    const btn = document.getElementById('startLessonBtn');
    if (btn) btn.onclick = startLesson;
}
initOverview();

// Tab switching detection
document.addEventListener('visibilitychange', () => {
    if (!examActive || examDismissed) return;
    if (document.hidden) handleTabSwitch();
});

window.addEventListener('blur', () => {
    if (!examActive || examDismissed) return;
    if (!document.hidden) handleTabSwitch();
});

document.addEventListener('fullscreenchange', () => {
    if (!examActive || examDismissed) return;
    if (!document.fullscreenElement) {
        handleTabSwitch();
        setTimeout(() => {
            if (examActive && !examDismissed && !document.fullscreenElement) enterFullscreen();
        }, 250);
    }
});
</script>
</body>
</html>