<?php
session_start();
require_once '../backend/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$level   = $_GET['level'] ?? 1;
$topic   = $_GET['topic'] ?? 'animals.yaml';
$exercise_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// Get language from URL or session
$lang = $_GET['lang'] ?? $_SESSION['active_language'] ?? 'rw';
$_SESSION['active_language'] = $lang;

// ========== Map your folder names to language codes ==========
$lang_folder_map = [
    'en' => 'EN-TO-RW',  // English to Kinyarwanda
    'rw' => 'RW-TO-EN',  // Kinyarwanda to English
    // Add more mappings as needed for other languages
    'fr' => 'FR-TO-EN',
    'sw' => 'SW-TO-EN',
    'es' => 'ES-TO-EN',
    'de' => 'DE-TO-EN'
];

// ========== Determine lesson direction for display ==========
$mapped_folder = $lang_folder_map[$lang] ?? null;
$lesson_direction = '';
$direction_color = '';
$direction_icon = '';

// ========== Determine lesson direction for display ==========
$mapped_folder = $lang_folder_map[$lang] ?? null;
$lesson_direction = '';
$direction_color = '';
$direction_icon = '';

if ($mapped_folder) {
    if ($mapped_folder == 'EN-TO-RW') {
        $lesson_direction = 'English → Kinyarwanda';
        $direction_color = '#4CAF50'; // Green
        $direction_icon = '<span class="fi fi-gb"></span> → <span class="fi fi-rw"></span>';
    } elseif ($mapped_folder == 'RW-TO-EN') {
        $lesson_direction = 'Kinyarwanda → English';
        $direction_color = '#2196F3'; // Blue
        $direction_icon = '<span class="fi fi-rw"></span> → <span class="fi fi-gb"></span>';
    } else {
        $lesson_direction = $mapped_folder;
        $direction_color = '#9C27B0'; // Purple for other
        $direction_icon = '🔀';
    }
}
// ─── Minimal YAML parser — no extension required ─────────────────────────
function _yaml_scalar($v) {
    $v = trim($v);
    if (strlen($v)>=2){$f=$v[0];$l=$v[strlen($v)-1];if(($f==='"'&&$l==='"')||($f==="'"&&$l==="'"))return substr($v,1,-1);}
    if(strtolower($v)==='true')return true;
    if(strtolower($v)==='false')return false;
    if(strtolower($v)==='null'||$v==='~')return null;
    if(is_numeric($v))return $v+0;
    return $v;
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
    if(trim($cur)!=='')$items[]=_yaml_scalar(trim($cur));
    return $items;
}
function parse_yaml_manual($yaml){
    $yaml=str_replace(["\r\n","\r"],"\n",$yaml);
    $lines=explode("\n",$yaml);$n=count($lines);
    $pool=[[]];$pi=1;
    $stk=[[-1,0]];
    $i=0;
    while($i<$n){
        $raw=$lines[$i];$i++;
        $t=rtrim($raw);$tr=trim($t);
        if($tr===''||$tr[0]==='#')continue;
        $ind=strlen($t)-strlen(ltrim($t));
        if($tr[0]!=='"'&&$tr[0]!=="'")$tr=trim(preg_replace('/\s+#[^"\']*$/','', $tr));
        if($tr==='')continue;

        while(count($stk)>1&&$stk[count($stk)-1][0]>=$ind)array_pop($stk);
        $si=count($stk)-1;
        $pIdx=$stk[$si][1];

        $dash=false;
        if($tr[0]==='-'&&(strlen($tr)===1||$tr[1]===' '||$tr[1]==="\t")){
            $dash=true;$tr=strlen($tr)>1?trim(substr($tr,2)):'';
        }

        if($tr===''&&$dash){
            $pool[$pi]=[];$pool[$pIdx][]= &$pool[$pi];
            array_push($stk,[$ind,$pi]);$pi++;continue;
        }
        if(strlen($tr)>0&&$tr[0]==='['&&$dash){
            $pool[$pIdx][]=_yaml_inline_seq($tr);continue;
        }

        if(preg_match('/^([^:]+):\s*(.*)$/',$tr,$m)){
            $key=trim($m[1]);$val=trim($m[2]);
            if(strlen($key)>=2){$f=$key[0];$l=$key[strlen($key)-1];if(($f==='"'&&$l==='"')||($f==="'"&&$l==="'"))$key=substr($key,1,-1);}
            if($val!==''&&$val[0]!=='"'&&$val[0]!=="'")$val=trim(preg_replace('/\s+#[^"\']*$/','', $val));

            if($dash){
                $pool[$pi]=[];$pool[$pIdx][]= &$pool[$pi];
                $itemIdx=$pi++;
                array_push($stk,[$ind,$itemIdx]);
                $si=count($stk)-1;$pIdx=$itemIdx;
            }

            if($val===''||$val==='|'||$val==='>'){
                if($val==='|'||$val==='>'){
                    $block='';$base=-1;
                    while($i<$n){$bl=$lines[$i];
                        if(trim($bl)===''){$block.="\n";$i++;continue;}
                        $bi=strlen($bl)-strlen(ltrim($bl));
                        if($base<0)$base=$bi;if($bi<$base)break;
                        $block.=substr($bl,$base)."\n";$i++;}
                    $pool[$pIdx][$key]=rtrim($block);
                } else {
                    $pool[$pi]=[];$pool[$pIdx][$key]= &$pool[$pi];
                    array_push($stk,[$ind,$pi]);$pi++;
                }
            } elseif(strlen($val)>0&&$val[0]==='['){
                $pool[$pIdx][$key]=_yaml_inline_seq($val);
            } else {
                $pool[$pIdx][$key]=_yaml_scalar($val);
            }
        } elseif($dash){
            $pool[$pIdx][]=_yaml_scalar($tr);
        }
    }
    return $pool[0];
}

// ========== Load YAML file with folder mapping ==========
// Try language-specific path with mapping first
$mapped_folder = $lang_folder_map[$lang] ?? null;
$yaml_file = null;

if ($mapped_folder && file_exists("../content/{$mapped_folder}/level$level/$topic")) {
    $yaml_file = "../content/{$mapped_folder}/level$level/$topic";
} elseif (file_exists("../content/$lang/level$level/$topic")) {
    // Try standard language code folder
    $yaml_file = "../content/$lang/level$level/$topic";
} elseif (file_exists("../content/level$level/$topic")) {
    // Fallback to non-language-specific
    $yaml_file = "../content/level$level/$topic";
}

if (!$yaml_file || !file_exists($yaml_file)) {
    die("Lesson not found. Tried: " . ($mapped_folder ? "../content/{$mapped_folder}/level$level/$topic, " : "") . "../content/$lang/level$level/$topic, ../content/level$level/$topic");
}

$content = file_get_contents($yaml_file);

if (function_exists('yaml_parse')) {
    $data = yaml_parse($content);
} else {
    $data = parse_yaml_manual($content);
}

$lesson_info      = $data['lesson']    ?? ['name' => 'Lesson', 'description' => '', 'xp_reward' => 500];
$exercises        = $data['exercises'] ?? [];
foreach ($exercises as $idx => &$ex) { if (!isset($ex['id'])) $ex['id'] = $idx + 1; }
unset($ex);

// Find current exercise and calculate progress
$current_exercise = null;
$next_id          = null;
$prev_id          = null;
$total_exercises  = count($exercises);
$completed_count  = 0;
$current_index    = 0;

// Get completed exercises from database
$stmt = $pdo->prepare("
    SELECT exercise_id, completed
    FROM user_progress
    WHERE user_id = ? AND language_code = ? AND level_number = ? AND topic_file = ?
");
$stmt->execute([$user_id, $lang, $level, $topic]);
$completed_exercises = [];
while ($row = $stmt->fetch()) {
    $completed_exercises[$row['exercise_id']] = $row['completed'];
}

foreach ($exercises as $i => $ex) {
    if (isset($completed_exercises[$ex['id']]) && $completed_exercises[$ex['id']]) {
        $completed_count++;
    }
    if ($ex['id'] == $exercise_id) {
        $current_exercise = $ex;
        $current_index    = $i;
        $next_id          = $exercises[$i + 1]['id'] ?? null;
        $prev_id          = $exercises[$i - 1]['id'] ?? null;
    }
}

if (!$current_exercise) {
    header("Location: lesson.php?lang=$lang&level=$level&topic=$topic&id=" . ($exercises[0]['id'] ?? 1));
    exit;
}

$is_completed = isset($completed_exercises[$exercise_id]) && $completed_exercises[$exercise_id];

// Handle answer submission (for traditional form posts)
$feedback       = '';
$feedback_class = '';
$show_success   = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$is_completed) {
    $user_answer = $_POST['answer'] ?? '';
    $time_spent  = time() - ($_SESSION['exercise_start_' . $exercise_id] ?? time());

    $result = check_answer($current_exercise, $user_answer, $_POST);

    if ($result['correct']) {
        $xp = $current_exercise['xp_reward'] ?? $current_exercise['xp'] ?? 10;

        $stmt = $pdo->prepare("
            INSERT INTO user_progress
            (user_id, language_code, level_number, topic_file, exercise_id, completed, xp_earned, accuracy, time_spent, completed_at)
            VALUES (?, ?, ?, ?, ?, 1, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
            completed = 1,
            xp_earned = VALUES(xp_earned),
            accuracy = VALUES(accuracy),
            time_spent = time_spent + VALUES(time_spent),
            attempts = attempts + 1,
            completed_at = NOW()
        ");
        $stmt->execute([$user_id, $lang, $level, $topic, $exercise_id, $xp, $result['accuracy'], $time_spent]);

        update_streak($pdo, $user_id);
        update_daily_session($pdo, $user_id, $lang);

        if (in_array($current_exercise['type'], ['translation', 'picture_word', 'multiple_choice'])) {
            track_vocabulary($pdo, $user_id, $lang, $level, $topic, $current_exercise);
        }

        check_achievements($pdo, $user_id);

        $feedback       = "✅ Correct! +$xp XP";
        $feedback_class = 'correct';
        $show_success   = true;
        $is_completed   = true;
        $completed_exercises[$exercise_id] = true;
        $completed_count++;

    } else {
        $stmt = $pdo->prepare("
            INSERT INTO user_progress
            (user_id, language_code, level_number, topic_file, exercise_id, completed, attempts, accuracy)
            VALUES (?, ?, ?, ?, ?, 0, 1, ?)
            ON DUPLICATE KEY UPDATE
            attempts = attempts + 1,
            accuracy = (accuracy + VALUES(accuracy)) / 2
        ");
        $stmt->execute([$user_id, $lang, $level, $topic, $exercise_id, $result['accuracy']]);

        $feedback       = "❌ " . ($result['message'] ?? 'Incorrect. Try again!');
        $feedback_class = 'incorrect';
    }
}

$_SESSION['exercise_start_' . $exercise_id] = time();

// ─── Helper functions ───────────────────────────────────────────────────────
function check_answer($exercise, $user_answer, $post_data) {
    $result = ['correct' => false, 'accuracy' => 0, 'message' => ''];
    $correct_val = $exercise['answer'] ?? $exercise['correct_answer'] ?? $exercise['correct'] ?? '';

    switch ($exercise['type']) {
        case 'translation':
        case 'listen_and_type':
        case 'pronunciation':
        case 'character_writing':
            $result['correct']  = strcasecmp(trim($user_answer), trim($correct_val)) == 0;
            $result['accuracy'] = $result['correct'] ? 100 : 0;
            break;

        case 'multiple_choice':
        case 'listen_and_choose':
        case 'picture_word':
        case 'choose_missing':
        case 'fill_blank':
        case 'read_answer':
        case 'listening_comprehension':
        case 'conversation_choice':
        case 'identify_meaning':
            $result['correct']  = strcasecmp(trim($user_answer), trim($correct_val)) == 0;
            $result['accuracy'] = $result['correct'] ? 100 : 0;
            break;

        case 'true_false':
            $correct_bool = is_bool($correct_val) ? $correct_val : (strtolower((string)$correct_val) === 'true');
            $user_bool    = strtolower(trim($user_answer)) === 'true';
            $result['correct']  = $user_bool === $correct_bool;
            $result['accuracy'] = $result['correct'] ? 100 : 0;
            break;

        case 'word_bank':
        case 'tap_hear':
            $correct_arr = is_array($correct_val) ? $correct_val : [];
            $user_arr    = $post_data['selected_words'] ?? [];
            if (is_string($user_arr)) $user_arr = json_decode($user_arr, true) ?: [];
            $result['correct']  = $user_arr === $correct_arr;
            $result['accuracy'] = $result['correct'] ? 100 : 0;
            break;

        case 'sentence_scramble':
            $correct_str = trim($exercise['correct_sentence'] ?? $correct_val ?? '');
            $user_str    = trim($user_answer);
            $result['correct']  = strcasecmp(
                preg_replace('/\s+/', ' ', $user_str),
                preg_replace('/\s+/', ' ', $correct_str)
            ) == 0;
            $result['accuracy'] = $result['correct'] ? 100 : 0;
            break;

        case 'matching':
            $pairs       = $exercise['pairs'] ?? [];
            $user_matches = $post_data['matches'] ?? [];
            if (is_string($user_matches)) $user_matches = json_decode($user_matches, true) ?: [];
            $correct_count = 0;
            foreach ($pairs as $pair) {
                $l = $pair['l'] ?? $pair['left'] ?? '';
                $r = $pair['r'] ?? $pair['right'] ?? '';
                if (isset($user_matches[$l]) && $user_matches[$l] == $r) $correct_count++;
            }
            $result['accuracy'] = count($pairs) > 0 ? round(($correct_count / count($pairs)) * 100) : 0;
            $result['correct']  = $result['accuracy'] == 100;
            break;

        case 'grammar':
        case 'error_correction':
            $correct_str = trim($exercise['correct_sentence'] ?? $correct_val ?? '');
            $result['correct']  = strcasecmp(trim($user_answer), $correct_str) == 0;
            $result['accuracy'] = $result['correct'] ? 100 : 0;
            break;

        default:
            $result['correct']  = false;
            $result['accuracy'] = 0;
    }
    return $result;
}

function update_streak($pdo, $user_id) {
    $today     = date('Y-m-d');
    $stmt      = $pdo->prepare("SELECT * FROM user_streaks WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $streak    = $stmt->fetch();

    if (!$streak) {
        $stmt = $pdo->prepare("INSERT INTO user_streaks (user_id, current_streak, last_activity_date) VALUES (?, 1, ?)");
        $stmt->execute([$user_id, $today]);
        return;
    }

    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $last      = $streak['last_activity_date'];

    if ($last == $yesterday) {
        $new = $streak['current_streak'] + 1;
        $stmt = $pdo->prepare("UPDATE user_streaks SET current_streak=?, longest_streak=GREATEST(longest_streak,?), last_activity_date=?, total_active_days=total_active_days+1 WHERE user_id=?");
        $stmt->execute([$new, $new, $today, $user_id]);
    } elseif ($last != $today) {
        $stmt = $pdo->prepare("UPDATE user_streaks SET current_streak=1, last_activity_date=?, total_active_days=total_active_days+1 WHERE user_id=?");
        $stmt->execute([$today, $user_id]);
    }
}

function update_daily_session($pdo, $user_id, $lang) {
    $today = date('Y-m-d');
    $stmt  = $pdo->prepare("
        INSERT INTO user_sessions (user_id, session_date, xp_earned, exercises_completed, time_spent)
        SELECT user_id, DATE(completed_at), SUM(xp_earned), COUNT(*), SUM(time_spent)
        FROM user_progress
        WHERE user_id = ? AND language_code = ? AND DATE(completed_at) = ? AND completed = 1
        GROUP BY user_id, DATE(completed_at)
        ON DUPLICATE KEY UPDATE
        xp_earned = VALUES(xp_earned),
        exercises_completed = VALUES(exercises_completed),
        time_spent = VALUES(time_spent)
    ");
    $stmt->execute([$user_id, $lang, $today]);
}

function track_vocabulary($pdo, $user_id, $lang, $level, $topic, $exercise) {
    $word        = '';
    $translation = '';

    if ($exercise['type'] == 'translation') {
        $word = $exercise['answer'] ?? '';
        preg_match('/\'(.+?)\'/', $exercise['question'] ?? '', $m);
        $translation = $m[1] ?? '';
    } elseif ($exercise['type'] == 'picture_word') {
        $word = $exercise['correct_answer'] ?? $exercise['correct'] ?? '';
        $translation = $exercise['question'] ?? '';
    } elseif ($exercise['type'] == 'multiple_choice') {
        $word = $exercise['answer'] ?? '';
        $translation = str_replace(['What is ', '?'], '', $exercise['question'] ?? '');
    }

    if ($word) {
        $stmt = $pdo->prepare("
            INSERT INTO user_vocabulary
            (user_id, language_code, level_number, topic_file, exercise_id, word_text, translation, times_seen, times_correct, last_reviewed)
            VALUES (?, ?, ?, ?, ?, ?, ?, 1, 1, NOW())
            ON DUPLICATE KEY UPDATE
            times_seen = times_seen + 1,
            times_correct = times_correct + 1,
            last_reviewed = NOW(),
            mastery_level = LEAST(5, mastery_level + 1)
        ");
        $stmt->execute([$user_id, $lang, $level, $topic, $exercise['id'], $word, $translation]);
    }
}

function check_achievements($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM user_stats WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $stats = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT current_streak FROM user_streaks WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $streak = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT * FROM achievements WHERE id NOT IN (SELECT achievement_id FROM user_achievements WHERE user_id = ?)");
    $stmt->execute([$user_id]);
    $achievements = $stmt->fetchAll();

    foreach ($achievements as $ach) {
        $earned = false;
        switch ($ach['criteria_type']) {
            case 'xp':        $earned = ($stats['total_xp'] >= $ach['criteria_value']); break;
            case 'streak':    $earned = ($streak >= $ach['criteria_value']); break;
            case 'exercises': $earned = ($stats['total_exercises'] >= $ach['criteria_value']); break;
            case 'lessons':   $earned = ($stats['lessons_completed'] >= $ach['criteria_value']); break;
        }
        if ($earned) {
            $stmt = $pdo->prepare("INSERT INTO user_achievements (user_id, achievement_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $ach['id']]);
            if ($ach['xp_reward'] > 0) {
                $stmt = $pdo->prepare("INSERT INTO user_progress (user_id, language_code, level_number, topic_file, exercise_id, completed, xp_earned, completed_at) VALUES (?, 'system', 0, 'achievement', ?, 1, ?, NOW())");
                $stmt->execute([$user_id, $ach['id'], $ach['xp_reward']]);
            }
        }
    }
}

$progress_percentage = $total_exercises > 0 ? round(($completed_count / $total_exercises) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($lesson_info['name'] ?? 'Lesson') ?> · Kinyarwanda</title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Fredoka+One&display=swap" rel="stylesheet">
<!-- Add this after your other CSS links -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons/css/flag-icons.min.css">
<style>
/* ═══════════════════════════════════════════════════════════════
   KINYARWANDA LESSON — FULL REDESIGN
   Aesthetic: Warm savanna palette · Bold display type · Tactile depth
   Fonts: Baloo 2 (display) + Nunito (body) — import in <head>
   ═══════════════════════════════════════════════════════════════ */

@import url('https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700;800;900&family=Nunito:wght@400;600;700;800;900&display=swap');

/* ── TOKENS ──────────────────────────────────────────────────── */
:root {
  /* Palette — warm savanna */
  --sage:    #4A7C59;
  --sage-d:  #2F5E3E;
  --sage-l:  #D6ECD F;
  --sage-l:  #DCF0E2;
  --sun:     #F5A623;
  --sun-d:   #D4841A;
  --sun-l:   #FFF2D8;
  --sky:     #3B82C4;
  --sky-d:   #1E5A9C;
  --sky-l:   #D8EDFB;
  --coral:   #E85D4A;
  --coral-d: #B33526;
  --coral-l: #FDECEA;
  --plum:    #8B5CF6;
  --plum-l:  #EDE9FE;
  --sand:    #F9F4EE;
  --bark:    #1C1A17;
  --stone:   #6B6560;
  --border:  #E5DDD5;
  --white:   #FFFFFF;

  /* Semantic aliases (keep compatibility) */
  --green:       var(--sage);
  --green-dark:  var(--sage-d);
  --green-light: var(--sage-l);
  --blue:        var(--sky);
  --blue-dark:   var(--sky-d);
  --blue-light:  var(--sky-l);
  --orange:      var(--sun);
  --orange-dark: var(--sun-d);
  --orange-light:var(--sun-l);
  --red:         var(--coral);
  --red-dark:    var(--coral-d);
  --red-light:   var(--coral-l);
  --yellow:      var(--sun);
  --yellow-dark: var(--sun-d);
  --yellow-light:var(--sun-l);
  --purple:      var(--plum);
  --purple-light:var(--plum-l);
  --bg:          var(--sand);
  --card:        var(--white);
  --text:        var(--bark);
  --muted:       var(--stone);
  --radius:      18px;
  --shadow:      0 2px 12px rgba(28,26,23,.08);
  --shadow-lg:   0 8px 32px rgba(28,26,23,.14);

  /* Typography */
  --font-display: 'Baloo 2', cursive;
  --font-body:    'Nunito', sans-serif;
}

/* ── RESET & BASE ────────────────────────────────────────────── */
*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

body {
  font-family: var(--font-body);
  background: var(--sand);
  color: var(--bark);
  min-height: 100vh;
  background-image:
    radial-gradient(circle at 15% 20%, rgba(74,124,89,.07) 0%, transparent 50%),
    radial-gradient(circle at 85% 80%, rgba(245,166,35,.06) 0%, transparent 50%);
}

/* ── GLOBAL PROGRESS BAR ─────────────────────────────────────── */
#progress-bar {
  position: fixed;
  top: 0; left: 0; right: 0;
  height: 5px;
  background: var(--border);
  z-index: 999;
}
#progress-fill {
  height: 100%;
  background: linear-gradient(90deg, var(--sage), var(--sun));
  width: 0;
  transition: width .5s cubic-bezier(.4,0,.2,1);
  border-radius: 0 3px 3px 0;
}

/* ── NAVIGATION ──────────────────────────────────────────────── */
.lesson-nav {
  position: fixed;
  top: 12px;
  right: 14px;
  z-index: 1000;
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  max-width: calc(100vw - 28px);
  justify-content: flex-end;
}

.nav-button {
  padding: 9px 18px;
  border-radius: 50px;
  font-family: var(--font-display);
  font-weight: 700;
  font-size: 13px;
  text-decoration: none;
  transition: transform .15s, box-shadow .15s;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  letter-spacing: .2px;
  white-space: nowrap;
}
.nav-button.dashboard {
  background: var(--sky);
  color: #fff;
  box-shadow: 0 4px 0 var(--sky-d);
}
.nav-button.games {
  background: var(--sun);
  color: #fff;
  box-shadow: 0 4px 0 var(--sun-d);
}
.nav-button:hover  { transform: translateY(-2px); }
.nav-button:active { transform: translateY(2px); box-shadow: 0 2px 0 currentColor; }

/* ── OVERVIEW CONTAINER ──────────────────────────────────────── */
#overview {
  max-width: 660px;
  margin: 0 auto;
  padding: 76px 18px 80px;
}

/* ── HERO CARD ───────────────────────────────────────────────── */
.ov-hero {
  background: linear-gradient(140deg, var(--sage-d) 0%, var(--sage) 55%, #5B9E6E 100%);
  color: #fff;
  border-radius: 24px;
  padding: 32px 28px 28px;
  margin-bottom: 20px;
  position: relative;
  overflow: hidden;
  box-shadow: 0 8px 0 rgba(47,94,62,.5), var(--shadow-lg);
}

/* Decorative circles */
.ov-hero::before {
  content: '';
  position: absolute;
  width: 200px; height: 200px;
  background: rgba(255,255,255,.06);
  border-radius: 50%;
  top: -60px; right: -40px;
  pointer-events: none;
}
.ov-hero::after {
  content: '';
  position: absolute;
  width: 140px; height: 140px;
  background: rgba(245,166,35,.15);
  border-radius: 50%;
  bottom: -50px; right: 60px;
  pointer-events: none;
}

.ov-tag {
  background: rgba(255,255,255,.2);
  border: 1px solid rgba(255,255,255,.3);
  border-radius: 50px;
  padding: 4px 14px;
  font-size: .72rem;
  font-family: var(--font-display);
  font-weight: 700;
  letter-spacing: .8px;
  text-transform: uppercase;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 14px;
  flex-wrap: wrap;
}

.ov-title {
  font-family: var(--font-display);
  font-weight: 900;
  font-size: 2.8rem;
  line-height: 1.05;
  margin-bottom: 8px;
  text-shadow: 0 2px 8px rgba(0,0,0,.15);
}

.ov-desc {
  font-size: .95rem;
  opacity: .88;
  margin-bottom: 24px;
  font-weight: 600;
  max-width: 420px;
}

.ov-stats {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 10px;
  margin-bottom: 22px;
}

.ov-stat {
  background: rgba(255,255,255,.15);
  border: 1px solid rgba(255,255,255,.2);
  border-radius: 14px;
  padding: 14px 8px;
  text-align: center;
  backdrop-filter: blur(4px);
}
.ov-stat-n {
  font-family: var(--font-display);
  font-weight: 900;
  font-size: 1.9rem;
  line-height: 1;
}
.ov-stat-l {
  font-size: .62rem;
  font-weight: 800;
  letter-spacing: .8px;
  text-transform: uppercase;
  opacity: .75;
  margin-top: 3px;
}

.xp-badge {
  background: var(--sun);
  color: #fff;
  border-radius: 50px;
  padding: 7px 16px;
  font-family: var(--font-display);
  font-weight: 800;
  font-size: .9rem;
  display: inline-flex;
  align-items: center;
  gap: 5px;
  box-shadow: 0 3px 0 var(--sun-d);
}

/* ── CTA BUTTONS ─────────────────────────────────────────────── */
#cta-row {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 20px;
}

.oh-btn-primary {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  padding: 17px 24px;
  background: var(--sage);
  color: #fff;
  border: none;
  border-radius: 16px;
  font-family: var(--font-display);
  font-weight: 800;
  font-size: 1.15rem;
  cursor: pointer;
  box-shadow: 0 6px 0 var(--sage-d), 0 8px 20px rgba(74,124,89,.25);
  transition: transform .1s, box-shadow .1s;
  letter-spacing: .3px;
  text-decoration: none;
  box-sizing: border-box;
  position: relative;
  overflow: hidden;
}
.oh-btn-primary::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, rgba(255,255,255,.12) 0%, transparent 60%);
  pointer-events: none;
}
.oh-btn-primary:hover  { transform: translateY(-2px); box-shadow: 0 8px 0 var(--sage-d), 0 12px 24px rgba(74,124,89,.3); }
.oh-btn-primary:active { transform: translateY(5px);  box-shadow: 0 1px 0 var(--sage-d); }

.oh-btn-primary.continue {
  background: var(--sun);
  box-shadow: 0 6px 0 var(--sun-d), 0 8px 20px rgba(245,166,35,.25);
}
.oh-btn-primary.continue:hover  { box-shadow: 0 8px 0 var(--sun-d), 0 12px 24px rgba(245,166,35,.3); }
.oh-btn-primary.continue:active { box-shadow: 0 1px 0 var(--sun-d); }
.oh-btn-primary:disabled { opacity: .5; pointer-events: none; }

.oh-btn-game {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  padding: 17px 24px;
  background: var(--coral);
  color: #fff;
  border: none;
  border-radius: 16px;
  font-family: var(--font-display);
  font-weight: 800;
  font-size: 1.15rem;
  cursor: pointer;
  box-shadow: 0 6px 0 var(--coral-d), 0 8px 20px rgba(232,93,74,.2);
  transition: transform .1s, box-shadow .1s;
  letter-spacing: .3px;
  text-decoration: none;
  box-sizing: border-box;
  position: relative;
  overflow: hidden;
}
.oh-btn-game::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, rgba(255,255,255,.12) 0%, transparent 60%);
  pointer-events: none;
}
.oh-btn-game:hover  { transform: translateY(-2px); box-shadow: 0 8px 0 var(--coral-d), 0 12px 24px rgba(232,93,74,.3); }
.oh-btn-game:active { transform: translateY(5px);  box-shadow: 0 1px 0 var(--coral-d); }

/* ── RANGE SELECTOR ──────────────────────────────────────────── */
.range-selector {
  display: flex;
  align-items: center;
  gap: 10px;
  background: var(--white);
  border: 2px solid var(--border);
  border-radius: 50px;
  padding: 10px 18px;
  margin-bottom: 18px;
  flex-wrap: wrap;
  box-shadow: var(--shadow);
}
.range-selector label {
  font-weight: 800;
  font-size: .88rem;
  color: var(--stone);
  white-space: nowrap;
}
.range-select {
  padding: 7px 14px;
  border: 2px solid var(--border);
  border-radius: 50px;
  font-weight: 700;
  font-size: .88rem;
  background: var(--sage-l);
  color: var(--sage-d);
  cursor: pointer;
  outline: none;
  font-family: var(--font-body);
  transition: border-color .2s;
}
.range-select:focus { border-color: var(--sage); }
.range-badge {
  background: var(--sage);
  color: #fff;
  border-radius: 50px;
  padding: 5px 14px;
  font-size: .78rem;
  font-weight: 800;
  white-space: nowrap;
  margin-left: auto;
}

/* ── TYPE CHIPS ──────────────────────────────────────────────── */
.type-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 18px; }
.type-chip {
  background: var(--white);
  border: 2px solid var(--border);
  border-radius: 50px;
  padding: 5px 14px;
  font-size: .75rem;
  font-weight: 700;
  color: var(--stone);
  box-shadow: 0 2px 0 var(--border);
}

/* ── PREVIEW CARD ────────────────────────────────────────────── */
.preview-card {
  background: var(--white);
  border-radius: var(--radius);
  border: 2px solid var(--border);
  overflow: hidden;
  box-shadow: var(--shadow);
}
.preview-hdr {
  padding: 14px 20px;
  border-bottom: 2px solid var(--border);
  font-family: var(--font-display);
  font-weight: 700;
  font-size: .8rem;
  text-transform: uppercase;
  letter-spacing: .6px;
  color: var(--stone);
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: var(--sand);
}
.preview-row {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 20px;
  border-bottom: 1px solid var(--border);
  font-size: .88rem;
  transition: background .15s;
}
.preview-row:last-child { border-bottom: none; }
.preview-row:hover      { background: var(--sand); }
.preview-num {
  width: 28px; height: 28px;
  background: var(--sage-l);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: .75rem; font-weight: 800; color: var(--sage-d);
  flex-shrink: 0;
}
.preview-type {
  background: var(--sun-l);
  border-radius: 20px;
  padding: 3px 10px;
  font-size: .68rem;
  font-weight: 800;
  color: var(--sun-d);
  white-space: nowrap;
  flex-shrink: 0;
  border: 1px solid rgba(245,166,35,.3);
}
.preview-q { flex: 1; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; color: var(--stone); }
.preview-xp { font-family: var(--font-display); font-weight: 800; color: var(--sage); font-size: .85rem; flex-shrink: 0; }

/* ── LESSON SCREEN ───────────────────────────────────────────── */
#lesson { display: none; max-width: 660px; margin: 0 auto; padding: 16px 18px 80px; }

.lesson-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 20px;
  margin-top: 14px;
  background: var(--white);
  border-radius: 50px;
  padding: 10px 16px;
  box-shadow: var(--shadow);
  border: 2px solid var(--border);
  overflow: hidden;
}
.close-btn {
  width: 34px; height: 34px;
  border-radius: 50%;
  background: var(--coral-l);
  border: 2px solid var(--coral);
  color: var(--coral-d);
  font-size: .9rem; font-weight: 900;
  cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
  transition: background .15s, color .15s;
}
.close-btn:hover { background: var(--coral); color: #fff; }
.lesson-title {
  font-family: var(--font-display);
  font-weight: 800;
  font-size: 1.2rem;
  color: var(--bark);
  flex-shrink: 0;
}
.lesson-prog-wrap {
  flex: 1;
  background: var(--sand);
  height: 10px;
  border-radius: 20px;
  overflow: hidden;
  min-width: 40px;
  border: 1px solid var(--border);
}
.lesson-prog {
  height: 100%;
  background: linear-gradient(90deg, var(--sage), var(--sun));
  width: 0;
  transition: width .4s cubic-bezier(.4,0,.2,1);
  border-radius: 20px;
}
.lesson-counter { font-weight: 800; color: var(--stone); font-size: .82rem; flex-shrink: 0; white-space: nowrap; }
.lesson-xp {
  background: var(--sun);
  border-radius: 50px;
  padding: 5px 12px;
  font-family: var(--font-display);
  font-weight: 800;
  font-size: .82rem;
  display: flex; align-items: center; gap: 4px;
  flex-shrink: 0; white-space: nowrap;
  color: #fff;
  box-shadow: 0 3px 0 var(--sun-d);
}

/* ── EXERCISE CARD ───────────────────────────────────────────── */
.ex-card {
  background: var(--white);
  border-radius: 22px;
  padding: 30px 26px;
  box-shadow: 0 6px 0 var(--border), var(--shadow-lg);
  border: 2px solid var(--border);
  animation: slideUp .3s cubic-bezier(.34,1.56,.64,1);
}
@keyframes slideUp {
  from { opacity: 0; transform: translateY(18px) scale(.97); }
  to   { opacity: 1; transform: translateY(0) scale(1); }
}

.ex-meta {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}
.ex-type {
  background: var(--sun-l);
  border-radius: 20px;
  padding: 5px 14px;
  font-family: var(--font-display);
  font-weight: 700;
  font-size: .75rem;
  text-transform: uppercase;
  letter-spacing: .5px;
  color: var(--sun-d);
  border: 1.5px solid rgba(245,166,35,.4);
}
.ex-xp {
  margin-left: auto;
  background: var(--sage-l);
  border-radius: 20px;
  padding: 5px 14px;
  font-family: var(--font-display);
  font-weight: 800;
  font-size: .78rem;
  color: var(--sage-d);
}

/* ── QUESTION BOX ────────────────────────────────────────────── */
.question-box {
  background: linear-gradient(135deg, var(--sage-l), #EBF5EE);
  border-radius: 16px;
  padding: 22px 24px;
  margin-bottom: 22px;
  font-family: var(--font-display);
  font-weight: 700;
  font-size: 1.45rem;
  line-height: 1.35;
  border: 2px solid rgba(74,124,89,.2);
  color: var(--bark);
}

/* ── TEXT INPUT ──────────────────────────────────────────────── */
.text-input {
  width: 100%;
  padding: 14px 20px;
  border: 2.5px solid var(--border);
  border-radius: 50px;
  font-size: 1.05rem;
  font-family: var(--font-body);
  font-weight: 700;
  outline: none;
  margin-bottom: 16px;
  transition: border-color .2s, box-shadow .2s, background .2s;
  background: var(--sand);
  color: var(--bark);
}
.text-input:focus   { border-color: var(--sage); background: #fff; box-shadow: 0 0 0 4px rgba(74,124,89,.12); }
.text-input.correct { border-color: var(--sage); background: var(--sage-l); }
.text-input.wrong   { border-color: var(--coral); background: var(--coral-l); }

/* ── MCQ ─────────────────────────────────────────────────────── */
.mcq-grid { display: flex; flex-direction: column; gap: 10px; margin-bottom: 18px; }
.mcq-btn {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 18px;
  border: 2.5px solid var(--border);
  border-radius: 14px;
  background: var(--white);
  font-size: .97rem;
  font-weight: 700;
  font-family: var(--font-body);
  cursor: pointer;
  transition: border-color .15s, background .15s, transform .08s, box-shadow .08s;
  box-shadow: 0 3px 0 var(--border);
  text-align: left;
  width: 100%;
  color: var(--bark);
}
.mcq-btn:hover   { border-color: var(--sage); background: var(--sage-l); transform: translateY(-1px); box-shadow: 0 4px 0 rgba(74,124,89,.3); }
.mcq-btn:active  { transform: translateY(2px); box-shadow: 0 1px 0 var(--border); }
.mcq-btn.correct { border-color: var(--sage);  background: var(--sage-l);  box-shadow: 0 3px 0 var(--sage-d); }
.mcq-btn.wrong   { border-color: var(--coral); background: var(--coral-l); box-shadow: 0 3px 0 var(--coral-d); }
.mcq-btn.selected{ border-color: var(--sky);   background: var(--sky-l); }

.opt-key {
  width: 34px; height: 34px;
  background: var(--sand);
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-family: var(--font-display);
  font-weight: 800;
  flex-shrink: 0;
  font-size: .82rem;
  border: 1.5px solid var(--border);
}
.mcq-btn.correct .opt-key { background: var(--sage);  color: #fff; border-color: var(--sage-d); }
.mcq-btn.wrong   .opt-key { background: var(--coral); color: #fff; border-color: var(--coral-d); }

/* ── SENTENCE BUILDER ────────────────────────────────────────── */
.sentence-builder {
  min-height: 60px;
  background: var(--sand);
  border: 2.5px dashed var(--border);
  border-radius: 16px;
  padding: 12px 16px;
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  align-items: center;
  margin-bottom: 14px;
  cursor: text;
  transition: border-color .2s, background .2s;
}
.sentence-builder.has-words {
  border-style: solid;
  border-color: var(--sage);
  background: var(--sage-l);
}
.builder-placeholder { color: var(--stone); font-style: italic; font-size: .9rem; pointer-events: none; }
.built-word {
  background: var(--sage);
  color: #fff;
  border-radius: 10px;
  padding: 7px 12px;
  font-family: var(--font-display);
  font-weight: 700;
  font-size: .88rem;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  user-select: none;
  animation: popWord .2s cubic-bezier(.34,1.56,.64,1);
  box-shadow: 0 2px 0 var(--sage-d);
}
@keyframes popWord { from { scale: 0; } to { scale: 1; } }
.built-word:hover { background: var(--sage-d); }
.built-word .rm {
  width: 17px; height: 17px;
  background: rgba(255,255,255,.3);
  border-radius: 5px;
  display: flex; align-items: center; justify-content: center;
  font-size: .6rem; font-weight: 900;
}

/* ── WORD BANK ───────────────────────────────────────────────── */
.word-bank {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 18px;
  padding: 14px;
  background: var(--sand);
  border-radius: 16px;
  border: 2px solid var(--border);
}
.bank-word {
  background: var(--white);
  border: 2px solid var(--border);
  border-radius: 10px;
  padding: 9px 16px;
  font-family: var(--font-display);
  font-weight: 700;
  font-size: .88rem;
  cursor: pointer;
  box-shadow: 0 3px 0 var(--border);
  transition: transform .08s, box-shadow .08s, background .1s;
  user-select: none;
  color: var(--bark);
}
.bank-word:hover  { background: var(--sage-l); border-color: var(--sage); transform: translateY(-1px); box-shadow: 0 4px 0 rgba(74,124,89,.3); }
.bank-word:active { transform: translateY(2px); box-shadow: 0 1px 0 var(--border); }
.bank-word.used   { opacity: .3; pointer-events: none; text-decoration: line-through; }

/* ── MATCHING ────────────────────────────────────────────────── */
.match-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 18px; }
.match-col  { display: flex; flex-direction: column; gap: 8px; }
.match-item {
  padding: 13px 14px;
  border: 2.5px solid var(--border);
  border-radius: 14px;
  font-family: var(--font-display);
  font-weight: 700;
  text-align: center;
  cursor: pointer;
  background: var(--white);
  box-shadow: 0 3px 0 var(--border);
  transition: transform .08s, box-shadow .08s, border-color .15s, background .15s;
  font-size: .88rem;
  user-select: none;
  color: var(--bark);
}
.match-item:hover    { border-color: var(--sky); background: var(--sky-l); transform: translateY(-1px); }
.match-item:active   { transform: translateY(2px); box-shadow: 0 1px 0 var(--border); }
.match-item.selected { border-color: var(--sky);  background: var(--sky-l);  box-shadow: 0 3px 0 var(--sky-d); }
.match-item.matched  { border-color: var(--sage); background: var(--sage-l); opacity: .7; pointer-events: none; }
.match-item.flash-wrong { animation: flashRed .4s ease; }
@keyframes flashRed {
  0%,100% { background: var(--white); }
  50%     { background: var(--coral-l); border-color: var(--coral); }
}
.match-score { font-family: var(--font-display); font-weight: 800; color: var(--stone); font-size: .9rem; margin-bottom: 10px; }

/* ── IMAGE ───────────────────────────────────────────────────── */
.ex-image {
  width: 100%;
  max-height: 240px;
  object-fit: cover;
  border-radius: 16px;
  border: 2px solid var(--border);
  margin-bottom: 18px;
  display: block;
}

/* ── FLASHCARDS ──────────────────────────────────────────────── */
.fc-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(125px, 1fr)); gap: 10px; margin-bottom: 18px; }
.fc {
  background: var(--white);
  border: 2.5px solid var(--border);
  border-radius: 16px;
  padding: 20px 10px;
  text-align: center;
  cursor: pointer;
  box-shadow: 0 4px 0 var(--border);
  transition: transform .1s, box-shadow .1s;
  min-height: 90px;
  display: flex; align-items: center; justify-content: center;
}
.fc:hover  { transform: translateY(-2px); box-shadow: 0 6px 0 var(--border); }
.fc:active { transform: translateY(3px); box-shadow: 0 1px 0 var(--border); }
.fc-front  { font-family: var(--font-display); font-weight: 800; font-size: .95rem; }
.fc-back   { font-family: var(--font-display); font-weight: 700; font-size: 1rem; color: var(--sage-d); display: none; }
.fc.flipped .fc-front { display: none; }
.fc.flipped .fc-back  { display: block; }
.fc.flipped { background: var(--sage-l); border-color: var(--sage); }

/* ── TRUE / FALSE ────────────────────────────────────────────── */
.tf-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 18px; }
.tf-btn {
  padding: 20px;
  border: 2.5px solid var(--border);
  border-radius: 16px;
  font-family: var(--font-display);
  font-weight: 800;
  font-size: 1.3rem;
  cursor: pointer;
  box-shadow: 0 4px 0 var(--border);
  transition: transform .08s, box-shadow .08s;
  text-align: center;
  background: var(--white);
}
.tf-btn:hover   { transform: translateY(-2px); }
.tf-btn:active  { transform: translateY(3px); box-shadow: 0 1px 0 var(--border); }
.tf-btn.true-btn  { border-color: var(--sage);  }
.tf-btn.false-btn { border-color: var(--coral); }
.tf-btn.correct   { background: var(--sage-l);  }
.tf-btn.wrong     { background: var(--coral-l); }

/* ── AUDIO BUTTON ────────────────────────────────────────────── */
.audio-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  background: var(--sky);
  color: #fff;
  border: none;
  border-radius: 50px;
  padding: 13px 26px;
  font-family: var(--font-display);
  font-weight: 800;
  font-size: .95rem;
  cursor: pointer;
  box-shadow: 0 5px 0 var(--sky-d);
  transition: transform .08s, box-shadow .08s;
  margin: 0 auto 18px;
  min-width: 170px;
}
.audio-btn:active { transform: translateY(4px); box-shadow: 0 1px 0 var(--sky-d); }
.audio-btn.playing { background: var(--sage); box-shadow: 0 5px 0 var(--sage-d); }

/* ── MIC ─────────────────────────────────────────────────────── */
.mic-wrap { text-align: center; margin-bottom: 18px; }
.speak-prompt {
  font-family: var(--font-display);
  font-weight: 900;
  font-size: 2.2rem;
  margin-bottom: 14px;
  color: var(--sage-d);
}
.mic-btn {
  background: var(--sun);
  border: 2.5px solid var(--sun-d);
  border-radius: 50px;
  padding: 16px 32px;
  font-family: var(--font-display);
  font-weight: 800;
  font-size: 1.1rem;
  cursor: pointer;
  box-shadow: 0 5px 0 var(--sun-d);
  transition: transform .08s;
  color: #fff;
}
.mic-btn:active { transform: translateY(4px); box-shadow: 0 1px 0 var(--sun-d); }
.mic-btn.listening {
  background: var(--coral);
  border-color: var(--coral-d);
  box-shadow: 0 5px 0 var(--coral-d);
  animation: pulseMic .9s infinite;
}
@keyframes pulseMic { 0%,100% { scale: 1; } 50% { scale: 1.04; } }

/* ── STORY / CONTEXT ─────────────────────────────────────────── */
.story-text {
  background: var(--sky-l);
  border-radius: 14px;
  padding: 13px 16px;
  margin-bottom: 10px;
  font-weight: 600;
  border-left: 4px solid var(--sky);
  font-size: .93rem;
}
.read-context {
  background: var(--sun-l);
  border-radius: 14px;
  padding: 15px 18px;
  margin-bottom: 16px;
  font-size: .93rem;
  line-height: 1.65;
  border: 2px solid rgba(245,166,35,.3);
}
.err-sentence {
  font-size: 1.1rem;
  margin-bottom: 16px;
  text-decoration: line-through;
  color: var(--coral-d);
  font-weight: 700;
  background: var(--coral-l);
  padding: 12px 16px;
  border-radius: 12px;
  border-left: 4px solid var(--coral);
}
.grammar-sentence {
  font-size: 1.05rem;
  margin-bottom: 16px;
  font-weight: 700;
  background: var(--sky-l);
  padding: 12px 16px;
  border-radius: 12px;
  border-left: 4px solid var(--sky);
}

/* ── TIMER ───────────────────────────────────────────────────── */
.timer-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: var(--sky);
  color: #fff;
  border-radius: 50px;
  padding: 7px 16px;
  font-family: var(--font-display);
  font-weight: 800;
  font-size: .95rem;
  margin-bottom: 16px;
  box-shadow: 0 3px 0 var(--sky-d);
}
.timer-pill.warning { background: var(--sun);   box-shadow: 0 3px 0 var(--sun-d); }
.timer-pill.danger  { background: var(--coral); box-shadow: 0 3px 0 var(--coral-d); animation: pulseMic .5s infinite; }

/* ── FEEDBACK ────────────────────────────────────────────────── */
.feedback {
  padding: 13px 20px;
  border-radius: 14px;
  font-family: var(--font-display);
  font-weight: 700;
  font-size: .92rem;
  margin-bottom: 14px;
  display: none;
  animation: fadeIn .2s ease;
}
@keyframes fadeIn { from { opacity: 0; transform: scale(.96); } to { opacity: 1; transform: scale(1); } }
.feedback.show    { display: block; }
.feedback.correct { background: var(--sage-l);  color: var(--sage-d);  border: 2px solid rgba(74,124,89,.3);  border-left: 4px solid var(--sage); }
.feedback.wrong   { background: var(--coral-l); color: var(--coral-d); border: 2px solid rgba(232,93,74,.3); border-left: 4px solid var(--coral); }
.feedback.skip    { background: var(--sun-l);   color: var(--sun-d);   border: 2px solid rgba(245,166,35,.3); border-left: 4px solid var(--sun); }

/* ── ACTION ROW ──────────────────────────────────────────────── */
.action-row {
  display: flex;
  gap: 10px;
  align-items: center;
  flex-wrap: nowrap;
  margin-top: 6px;
}

.check-btn {
  flex: 1;
  padding: 15px;
  background: var(--sky);
  color: #fff;
  border: none;
  border-radius: 14px;
  font-family: var(--font-display);
  font-weight: 800;
  font-size: 1.05rem;
  cursor: pointer;
  box-shadow: 0 5px 0 var(--sky-d);
  transition: transform .08s, box-shadow .08s;
  letter-spacing: .2px;
}
.check-btn:hover  { transform: translateY(-1px); box-shadow: 0 6px 0 var(--sky-d); }
.check-btn:active { transform: translateY(4px);  box-shadow: 0 1px 0 var(--sky-d); }

.next-btn {
  flex: 1;
  padding: 15px;
  background: var(--sage);
  color: #fff;
  border: none;
  border-radius: 14px;
  font-family: var(--font-display);
  font-weight: 800;
  font-size: 1.05rem;
  cursor: pointer;
  box-shadow: 0 5px 0 var(--sage-d);
  transition: transform .08s, box-shadow .08s;
  display: none;
  letter-spacing: .2px;
}
.next-btn:hover  { transform: translateY(-1px); box-shadow: 0 6px 0 var(--sage-d); }
.next-btn:active { transform: translateY(4px);  box-shadow: 0 1px 0 var(--sage-d); }
.next-btn.show   { display: block; }

.skip-btn {
  padding: 13px 18px;
  background: var(--white);
  color: var(--stone);
  border: 2px solid var(--border);
  border-radius: 14px;
  font-family: var(--font-display);
  font-weight: 700;
  font-size: .9rem;
  cursor: pointer;
  box-shadow: 0 3px 0 var(--border);
  transition: transform .08s, box-shadow .08s;
  flex-shrink: 0;
}
.skip-btn:hover  { background: var(--sand); }
.skip-btn:active { transform: translateY(2px); box-shadow: 0 1px 0 var(--border); }

/* ── COMPLETION CARD ─────────────────────────────────────────── */
.completion {
  background: linear-gradient(140deg, var(--sage-d), var(--sage) 60%, #5DAE73);
  color: #fff;
  border-radius: 24px;
  padding: 40px 28px;
  text-align: center;
  animation: slideUp .4s ease;
  box-shadow: 0 8px 0 rgba(47,94,62,.5), var(--shadow-lg);
}
.comp-title {
  font-family: var(--font-display);
  font-weight: 900;
  font-size: 2.8rem;
  margin-bottom: 6px;
}
.comp-sub { opacity: .85; margin-bottom: 26px; font-weight: 600; }
.comp-stats { display: grid; grid-template-columns: repeat(3,1fr); gap: 12px; margin-bottom: 26px; }
.comp-stat {
  background: rgba(255,255,255,.15);
  border: 1px solid rgba(255,255,255,.2);
  border-radius: 16px;
  padding: 18px 10px;
  text-align: center;
}
.comp-stat-n { font-family: var(--font-display); font-weight: 900; font-size: 2.2rem; }
.comp-stat-l { font-size: .65rem; font-weight: 800; letter-spacing: .8px; text-transform: uppercase; opacity: .75; }
.comp-btns   { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
.comp-btn {
  padding: 14px 26px;
  border-radius: 14px;
  border: none;
  font-family: var(--font-display);
  font-weight: 800;
  font-size: 1rem;
  cursor: pointer;
  box-shadow: 0 4px 0 rgba(0,0,0,.2);
  transition: transform .08s, box-shadow .08s;
}
.comp-btn:hover  { transform: translateY(-2px); }
.comp-btn:active { transform: translateY(3px); box-shadow: 0 1px 0 rgba(0,0,0,.2); }
.comp-btn.green  { background: rgba(255,255,255,.9); color: var(--sage-d); }
.comp-btn.yellow { background: var(--sun); color: #fff; box-shadow: 0 4px 0 var(--sun-d); }

/* ── MISC ────────────────────────────────────────────────────── */
@keyframes shake {
  0%,100% { transform: translateX(0); }
  25%     { transform: translateX(-7px); }
  75%     { transform: translateX(7px); }
}
.shake { animation: shake .3s ease; }

.ctx-sentence  { font-size: 1.05rem; margin-bottom: 6px; font-weight: 600; }
.highlight-word { background: var(--sun); padding: 2px 8px; border-radius: 6px; font-weight: 800; }
.word-meaning  {
  font-family: var(--font-display);
  font-weight: 900;
  font-size: 1.9rem;
  margin: 12px 0 18px;
  color: var(--plum);
  text-align: center;
}

/* ── CLOSE MODAL ─────────────────────────────────────────────── */
#close-modal > div {
  background: var(--white) !important;
  border-radius: 22px !important;
  box-shadow: 0 24px 64px rgba(28,26,23,.25) !important;
}

/* ── LEGACY start-btn (overview) ────────────────────────────── */
.start-btn {
  display: block; width: 100%; padding: 17px;
  background: var(--sage); color: #fff; border: none;
  border-radius: 16px;
  font-family: var(--font-display); font-weight: 800; font-size: 1.25rem;
  cursor: pointer;
  box-shadow: 0 6px 0 var(--sage-d), 0 8px 16px rgba(74,124,89,.2);
  transition: transform .08s, box-shadow .08s;
  margin-bottom: 12px;
}
.start-btn:active { transform: translateY(4px); box-shadow: 0 2px 0 var(--sage-d); }
.start-btn.continue { background: var(--sun); box-shadow: 0 6px 0 var(--sun-d); }

/* ═══════════════════════════════════════════════════════════════
   GIF ANIMATION STYLES - Duolingo Style
   ═══════════════════════════════════════════════════════════════ */
#gif-container {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}

#gif-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.2);
    backdrop-filter: blur(3px);
    opacity: 0;
    transition: opacity 0.3s ease;
}

#gif-container.show #gif-overlay {
    opacity: 1;
}

#gif-wrapper {
    position: relative;
    z-index: 10000;
    max-width: 400px;
    width: 90%;
    border-radius: 30px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    transform: scale(0.8);
    opacity: 0;
    transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.3s ease;
}

#gif-container.show #gif-wrapper {
    transform: scale(1);
    opacity: 1;
}

#gif-animation {
    width: 100%;
    height: auto;
    display: block;
    border-radius: 30px;
}

/* Animation types */
.gif-correct #gif-wrapper {
    border: 4px solid var(--sage);
}

.gif-wrong #gif-wrapper {
    border: 4px solid var(--coral);
}

.gif-idle #gif-wrapper {
    border: 4px solid var(--sky);
}

/* Position variations for different screen sizes */
@media (max-width: 600px) {
    #gif-wrapper {
        max-width: 300px;
    }
}

/* Small animations for inline feedback */
.inline-gif {
    display: inline-block;
    width: 40px;
    height: 40px;
    margin-right: 8px;
    vertical-align: middle;
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center;
    animation: bounce 0.5s ease;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-5px); }
}

/* Typing indicator */
.typing-indicator {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 8px 12px;
    background: var(--sand);
    border-radius: 20px;
    margin-left: 8px;
}

.typing-dot {
    width: 8px;
    height: 8px;
    background: var(--stone);
    border-radius: 50%;
    animation: typingPulse 1.4s infinite;
}

.typing-dot:nth-child(2) { animation-delay: 0.2s; }
.typing-dot:nth-child(3) { animation-delay: 0.4s; }

@keyframes typingPulse {
    0%, 60%, 100% { transform: translateY(0); opacity: 0.6; }
    30% { transform: translateY(-4px); opacity: 1; }
}

/* Waiting animation for idle state */
.waiting-animation {
    position: relative;
    width: 100%;
    height: 4px;
    background: var(--border);
    border-radius: 2px;
    overflow: hidden;
    margin-top: 16px;
}

.waiting-bar {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    width: 30%;
    background: linear-gradient(90deg, var(--sky), var(--sage));
    border-radius: 2px;
    animation: waitingMove 2s infinite ease-in-out;
}

@keyframes waitingMove {
    0% { left: -30%; }
    100% { left: 100%; }
}

.correct-flash {
    animation: correctFlash 0.5s ease;
}

@keyframes correctFlash {
    0%, 100% { box-shadow: 0 6px 0 var(--border), var(--shadow-lg); }
    50% { box-shadow: 0 6px 0 var(--sage), 0 0 20px rgba(74,124,89,0.5); }
}

/* Toast notification styles */
.toast {
    position: fixed;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%) translateY(100px);
    background: white;
    color: var(--bark);
    padding: 12px 24px;
    border-radius: 50px;
    font-family: var(--font-display);
    font-weight: 700;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    z-index: 10001;
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    border: 2px solid var(--border);
}

.toast.show {
    transform: translateX(-50%) translateY(0);
}

.toast-correct { background: var(--sage-l); border-color: var(--sage); color: var(--sage-d); }
.toast-wrong { background: var(--coral-l); border-color: var(--coral); color: var(--coral-d); }
.toast-streak { background: var(--sun-l); border-color: var(--sun); color: var(--sun-d); }
.toast-levelup { background: var(--purple-l); border-color: var(--purple); color: var(--purple); }

/* ═══════════════════════════════════════════════════════════════
   RESPONSIVE
   ═══════════════════════════════════════════════════════════════ */
@media (max-width: 600px) {

  #overview { padding: 68px 12px 70px; }
  #lesson   { padding: 12px 12px 70px; }

  .nav-button { padding: 8px 11px; font-size: 12px; }

  .ov-hero    { padding: 22px 18px 20px; border-radius: 18px; }
  .ov-title   { font-size: 2rem; }
  .ov-desc    { font-size: .88rem; }
  .ov-stats   { grid-template-columns: repeat(2, 1fr); gap: 8px; }
  .ov-stat    { padding: 12px 6px; }
  .ov-stat-n  { font-size: 1.6rem; }

  .oh-btn-primary, .oh-btn-game { font-size: 1rem; padding: 14px 16px; border-radius: 14px; }

  .lesson-header { gap: 6px; padding: 8px 12px; border-radius: 50px; margin-top: 8px; }
  .close-btn     { width: 30px; height: 30px; }
  .lesson-xp     { padding: 4px 10px; font-size: .75rem; }
  .lesson-counter{ font-size: .75rem; }

  .ex-card     { padding: 18px 14px; border-radius: 18px; }
  .question-box{ font-size: 1.15rem; padding: 16px 18px; border-radius: 14px; }

  .mcq-btn     { padding: 11px 13px; font-size: .9rem; border-radius: 12px; }
  .opt-key     { width: 28px; height: 28px; border-radius: 8px; font-size: .78rem; }

  .match-item  { padding: 10px 8px; font-size: .82rem; border-radius: 12px; }
  .tf-btn      { font-size: 1.15rem; padding: 15px; border-radius: 14px; }

  .fc-grid     { grid-template-columns: repeat(auto-fill, minmax(105px, 1fr)); gap: 8px; }
  .fc          { padding: 14px 6px; min-height: 78px; border-radius: 12px; }

  .word-bank   { padding: 10px; gap: 6px; }
  .bank-word   { padding: 8px 13px; font-size: .84rem; border-radius: 9px; }

  .check-btn, .next-btn { font-size: .95rem; padding: 13px; border-radius: 12px; }
  .skip-btn  { padding: 11px 14px; font-size: .85rem; border-radius: 12px; }

  .audio-btn { padding: 11px 18px; font-size: .88rem; min-width: 145px; }
  .speak-prompt { font-size: 1.7rem; }
  .mic-btn   { padding: 13px 26px; font-size: 1rem; }

  .completion { padding: 26px 18px; border-radius: 18px; }
  .comp-title { font-size: 2rem; }
  .comp-stat  { padding: 14px 6px; }
  .comp-stat-n{ font-size: 1.7rem; }
  .comp-btns  { flex-direction: column; align-items: stretch; }

  .preview-row { padding: 10px 14px; font-size: .84rem; }
}

@media (max-width: 380px) {
  .ov-title { font-size: 1.7rem; }
  .action-row { flex-direction: column; }
  .skip-btn   { order: -1; text-align: center; width: 100%; }
  .check-btn, .next-btn { width: 100%; }
}
</style>
</head>
<body>

<!-- Navigation buttons -->


<div id="progress-bar"><div id="progress-fill"></div></div>

<!-- ═══ GIF ANIMATION CONTAINER ═══ -->
<div id="gif-container" style="display: none;">
    <div id="gif-overlay"></div>
    <div id="gif-wrapper">
        <img id="gif-animation" src="" alt="Animation">
    </div>
</div>

<!-- ═══ OVERVIEW SCREEN ═══ -->
<div id="overview">
  <div class="ov-hero">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px">
<span class="ov-tag" style="display: flex; align-items: center; gap: 8px;">
    <span>Level <?= (int)$level ?></span>
    <?php if ($lesson_direction): ?>
        <span style="background: <?= $direction_color ?>; padding: 2px 10px; border-radius: 30px; font-size: 0.7rem;">
            <?= $direction_icon ?> 
        </span>
    <?php endif; ?>
</span>      <div class="xp-badge">⭐ <span id="ov-xp">0</span> XP</div>
    </div>
    <div class="ov-title"><?= htmlspecialchars($lesson_info['name'] ?? 'Lesson') ?></div>
    <div class="ov-desc"><?= htmlspecialchars($lesson_info['description'] ?? '') ?></div>
    <div class="ov-stats">
      <div class="ov-stat"><div class="ov-stat-n" id="ov-total"><?= count($exercises) ?></div><div class="ov-stat-l">Exercises</div></div>
      <div class="ov-stat"><div class="ov-stat-n" id="ov-xp-total"><?= array_sum(array_column($exercises, 'xp_reward')) ?></div><div class="ov-stat-l">Total XP</div></div>
      <div class="ov-stat"><div class="ov-stat-n"><?= count(array_unique(array_column($exercises, 'type'))) ?></div><div class="ov-stat-l">Types</div></div>
      <div class="ov-stat"><div class="ov-stat-n"><?= strtoupper($lang) ?></div><div class="ov-stat-l">Lang</div></div>
    </div>
  </div>

  <div style="margin-bottom:20px">
    <div class="type-chips" id="type-chips"></div>
  </div>

  <div class="range-selector">
    <label>📚 Show:</label>
    <select class="range-select" id="range-select" onchange="updateRange()">
      <option value="10">10 exercises</option>
      <option value="15">15 exercises</option>
      <option value="20" selected>20 exercises</option>
      <option value="30">30 exercises</option>
      <option value="50">50 exercises</option>
      <option value="9999">All (<?= count($exercises) ?>)</option>
    </select>
    <span class="range-badge" id="range-badge">20 selected · Mixed</span>
  </div>

 <div style="display: column; gap: 12px; margin-bottom: 20px;">
    <button id="startLessonBtn" class="oh-btn-primary" onclick="startLesson()" style="flex: 2;">📚 &nbsp; Start Lesson</button>
    <a href="game.php?lang=<?= $lang ?>&level=<?= $level ?>&topic=<?= urlencode($topic) ?>" class="oh-btn-game" style="flex: 1;">🎮 &nbsp; Play Voice Game</a>
  </div>

  <div class="preview-card">
    <div class="preview-hdr"><span>Exercise Preview (Randomized)</span><span id="preview-count"></span></div>
    <div id="preview-list"></div>
  </div>
</div>

<!-- ═══ LESSON SCREEN ═══ -->
<div id="lesson">
  <div class="lesson-header">
    <button class="close-btn" onclick="confirmClose()" title="Close lesson">✕</button>
<?php if ($lesson_direction): ?>
    <span style="font-size: 0.7rem; background: <?= $direction_color ?>20; color: <?= $direction_color ?>; padding: 3px 8px; border-radius: 20px; font-weight: 700; margin-left: 8px;">
        <?= $direction_icon ?> 
    </span>
<?php endif; ?>    <div class="lesson-prog-wrap"><div class="lesson-prog" id="lesson-prog"></div></div>
    <span class="lesson-counter" id="lesson-counter">0/0</span>
    <div class="lesson-xp">⭐ <span id="lesson-xp">0</span></div>
  </div>
  <div id="lesson-main"></div>
</div>

<!-- Close confirm modal -->
<div id="close-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:2000;align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:24px;padding:32px 28px;max-width:320px;width:90%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.3)">
    <div style="font-size:2.5rem;margin-bottom:12px">🚪</div>
    <div style="font-family:'Fredoka One',cursive;font-size:1.6rem;margin-bottom:8px">Leave Lesson?</div>
    <div style="color:var(--muted);font-size:.95rem;margin-bottom:24px">Your progress will be lost.</div>
    <div style="display:flex;gap:12px;justify-content:center">
      <button onclick="document.getElementById('close-modal').style.display='none'" style="flex:1;padding:14px;border:3px solid var(--border);border-radius:60px;font-family:'Fredoka One',cursive;font-size:1rem;background:#fff;cursor:pointer">Stay</button>
      <button onclick="closeLesson()" style="flex:1;padding:14px;background:var(--red);color:#fff;border:none;border-radius:60px;font-family:'Fredoka One',cursive;font-size:1rem;cursor:pointer;box-shadow:0 4px 0 var(--red-dark)">Leave</button>
    </div>
  </div>
</div>

<script>
// ═══════════════════════════════════════════
// LESSON DATA — from PHP/YAML
// ═══════════════════════════════════════════
var EXERCISES = <?php
$js_exercises = [];
foreach ($exercises as $ex) {
    $e = [];
    $e['id']   = $ex['id'];
    $e['type'] = $ex['type'] ?? 'translation';
    $e['xp']   = $ex['xp_reward'] ?? $ex['xp'] ?? 10;

    foreach ($ex as $k => $v) {
        if (!isset($e[$k])) $e[$k] = $v;
    }

    if (!isset($e['answer']) && isset($e['correct_answer'])) $e['answer'] = $e['correct_answer'];
    if (!isset($e['correct']) && isset($e['correct_answer'])) $e['correct'] = $e['correct_answer'];
    if (!isset($e['correct']) && isset($e['answer'])) $e['correct'] = $e['answer'];

    if (!isset($e['word_bank']) && isset($e['word_bank_for_fix'])) $e['word_bank'] = $e['word_bank_for_fix'];
    if (!isset($e['words']) && isset($e['scrambled_words'])) $e['words'] = $e['scrambled_words'];
    if (!isset($e['tts']) && isset($e['tts_text'])) $e['tts'] = $e['tts_text'];
    if (!isset($e['tts']) && isset($e['dialogue_tts'])) $e['tts'] = $e['dialogue_tts'];
    if (!isset($e['bot']) && isset($e['bot_message'])) $e['bot'] = $e['bot_message'];
    if (!isset($e['image']) && isset($e['image_url'])) $e['image'] = $e['image_url'];

    if (isset($e['pairs'])) {
        foreach ($e['pairs'] as &$pair) {
            if (!isset($pair['l']) && isset($pair['left']))  $pair['l'] = $pair['left'];
            if (!isset($pair['r']) && isset($pair['right'])) $pair['r'] = $pair['right'];
        }
        unset($pair);
        $e['pairs'] = array_values($e['pairs']);
    }

    if (!isset($e['intro']) && isset($e['story_intro'])) $e['intro'] = $e['story_intro'];
    if (!isset($e['segments']) && isset($e['story_segments'])) {
        $segs = [];
        foreach ($e['story_segments'] as $s) {
            $segs[] = is_array($s) ? ($s['text'] ?? '') : $s;
        }
        $e['segments'] = $segs;
    }

    if (!isset($e['cards']) && isset($e['flashcards'])) $e['cards'] = $e['flashcards'];
    if (!isset($e['correct']) && isset($e['correct_response'])) $e['correct'] = $e['correct_response'];
    if (isset($e['exercises']) && !isset($e['items'])) $e['items'] = $e['exercises'];
    if (!isset($e['context']) && isset($e['context_sentence'])) $e['context'] = $e['context_sentence'];
    if (!isset($e['incorrect']) && isset($e['incorrect_sentence'])) $e['incorrect'] = $e['incorrect_sentence'];
    if (!isset($e['correct']) && isset($e['correct_sentence'])) $e['correct'] = $e['correct_sentence'];
    if (!isset($e['time']) && isset($e['time_limit_seconds'])) $e['time'] = $e['time_limit_seconds'];

    if (isset($e['questions']) && is_array($e['questions'])) {
        foreach ($e['questions'] as &$q) {
            if (!isset($q['q']) && isset($q['question'])) $q['q'] = $q['question'];
            if (!isset($q['a']) && isset($q['answer']))   $q['a'] = $q['answer'];
        }
        unset($q);
    }

    $js_exercises[] = $e;
}
echo json_encode($js_exercises, JSON_UNESCAPED_UNICODE);
?>;

// ═══════════════════════════════════════════
// STATE
// ═══════════════════════════════════════════
var SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition || null;
var recognizer = null;
var queue = [];
var idx = 0;
var earnedXP = 0;
var correctCount = 0;
var skipped = 0;
var matchLeft = null;
var timerInt = null;
var SHOW_COUNT = 20;
var currentUserId = <?= $user_id ?>;
var currentLang = '<?= $lang ?>';
var currentLevel = <?= $level ?>;
var currentTopic = '<?= $topic ?>';
var storyExercises = [];
var builtWords = [];
var storyBuiltWords = {};

// ═══════════════════════════════════════════
// COMPLETED EXERCISES FROM DATABASE
// ═══════════════════════════════════════════
var completedExercises = <?= json_encode(array_keys($completed_exercises)) ?>;

console.log('✅ Completed exercises:', completedExercises);

var AUDIO_BASE = '../audio/kinyarwanda/';

var LANG = '<?= $lang ?>';

function shuffle(arr) { 
    for(var i = arr.length - 1; i > 0; i--) {
        var j = Math.floor(Math.random() * (i + 1)); 
        var t = arr[i]; 
        arr[i] = arr[j]; 
        arr[j] = t;
    } 
    return arr; 
}

// ═══════════════════════════════════════════
// FILTER EXERCISES - REMOVE COMPLETED ONES
// ═══════════════════════════════════════════
function getAvailableExercises() {
    // First, filter out completed exercises
    var available = EXERCISES.filter(function(ex) {
        return !completedExercises.includes(ex.id);
    });
    
    console.log('📊 Total exercises: ' + EXERCISES.length + ', Completed: ' + completedExercises.length + ', Available: ' + available.length);
    
    return available;
}

// ═══════════════════════════════════════════
// SAVE PROGRESS TO DATABASE
// ═══════════════════════════════════════════
function saveProgressToDatabase(exerciseId, isCorrect, xpEarned, timeSpent) {
    if (!isCorrect) {
        console.log('❌ Not saving - answer incorrect');
        return;
    }
    
    if (!exerciseId) {
        console.error('❌ Missing exercise ID');
        return;
    }
    
    console.log('🎯 Saving exercise:', exerciseId, 'XP:', xpEarned);
    
    var data = {
        level: currentLevel,
        topic: currentTopic,
        exercise_id: exerciseId,
        xp: xpEarned || 10,
        accuracy: 100,
        time_spent: timeSpent || 5,
        language_code: currentLang
    };
    
    console.log('📤 Sending data:', data);
    
    fetch('save-progress.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(function(response) { 
        return response.json(); 
    })
    .then(function(data) {
        console.log('📥 Server response:', data);
        if (data.success) {
            console.log('✅ Saved successfully!');
            
            // Add to completed exercises list so it won't show again
            if (!completedExercises.includes(exerciseId)) {
                completedExercises.push(exerciseId);
                console.log('📝 Added exercise', exerciseId, 'to completed list');
            }
            
            // Check if we've completed all exercises in the current queue
            var remainingInQueue = 0;
            for (var i = idx + 1; i < queue.length; i++) {
                if (!completedExercises.includes(queue[i].id)) {
                    remainingInQueue++;
                }
            }
            
            console.log('📊 Queue progress: ' + (idx + 1) + '/' + queue.length + ' done, ' + remainingInQueue + ' remaining');
            
        } else {
            console.error('❌ Server error:', data.error);
        }
    })
    .catch(function(error) {
        console.error('❌ Fetch error:', error);
    });
}

// ═══════════════════════════════════════════
// RANGE SELECTOR
// ═══════════════════════════════════════════
function updateRange() {
    var availableExercises = getAvailableExercises();
    var sel = document.getElementById('range-select');
    SHOW_COUNT = Math.min(parseInt(sel.value) || 20, availableExercises.length);
    
    var badge = document.getElementById('range-badge');
    if (badge) badge.textContent = (SHOW_COUNT === availableExercises.length ? 'All' : SHOW_COUNT) + ' selected · ' + availableExercises.length + ' available';
    
    rebuildPreview();
}

function rebuildPreview() {
    var availableExercises = getAvailableExercises();
    var shuffled = shuffle([].concat(availableExercises));
    var prev = document.getElementById('preview-list');
    prev.innerHTML = '';
    
    var show = Math.min(SHOW_COUNT, shuffled.length);
    document.getElementById('preview-count').textContent = show + ' of ' + availableExercises.length + ' available';
    
    for (var i = 0; i < show; i++) {
        var ex = shuffled[i];
        var q = ex.question || ex.title || ex.prompt || 'Exercise';
        prev.innerHTML += '<div class="preview-row">' +
            '<div class="preview-num">' + (i + 1) + '</div>' +
            '<div class="preview-type">' + ex.type.replace(/_/g, ' ') + '</div>' +
            '<div class="preview-q">' + q.substring(0, 45) + '…</div>' +
            '<div class="preview-xp">+' + ex.xp + '</div>' +
        '</div>';
    }
    
    var xpSum = 0;
    for (var i = 0; i < show; i++) {
        xpSum += (shuffled[i].xp || 0);
    }
    document.getElementById('ov-xp-total').textContent = xpSum;
    document.getElementById('ov-total').textContent = show;
    
    // Update start button text based on progress
    updateStartButton();
}

// ═══════════════════════════════════════════
// UPDATE START BUTTON (Start/Continue)
// ═══════════════════════════════════════════
function updateStartButton() {
    var startBtn = document.getElementById('startLessonBtn');
    var availableExercises = getAvailableExercises();
    
    if (completedExercises.length > 0) {
        startBtn.textContent = '▶ Continue Lesson';
        startBtn.classList.add('continue');
    } else {
        startBtn.textContent = '▶ Start Lesson';
        startBtn.classList.remove('continue');
    }
    
    if (availableExercises.length === 0) {
        startBtn.textContent = '✅ All Completed!';
        startBtn.disabled = true;
        startBtn.style.opacity = '0.5';
    }
}

// ═══════════════════════════════════════════
// INIT OVERVIEW
// ═══════════════════════════════════════════
function initOverview() {
    var availableExercises = getAvailableExercises();
    var types = {};
    
    for (var i = 0; i < availableExercises.length; i++) {
        var e = availableExercises[i];
        types[e.type] = (types[e.type] || 0) + 1;
    }
    
    var chips = document.getElementById('type-chips');
    chips.innerHTML = '';
    var typeEntries = Object.entries(types);
    for (var i = 0; i < Math.min(12, typeEntries.length); i++) {
        var t = typeEntries[i][0];
        var c = typeEntries[i][1];
        chips.innerHTML += '<span class="type-chip">' + t.replace(/_/g, ' ') + ' ×' + c + '</span>';
    }
    
    // Update stats to show completed vs total
    document.getElementById('ov-total').textContent = availableExercises.length;
    var totalXp = 0;
    for (var i = 0; i < availableExercises.length; i++) {
        totalXp += (availableExercises[i].xp || 0);
    }
    document.getElementById('ov-xp-total').textContent = totalXp;
    
    // Add completion message if all done
    if (availableExercises.length === 0) {
        chips.innerHTML = '<span class="type-chip" style="background:var(--green-light)">✅ All exercises completed!</span>';
    }
    
    rebuildPreview();
}

// ═══════════════════════════════════════════
// START LESSON - ONLY WITH UNCOMPLETED EXERCISES
// ═══════════════════════════════════════════
function startLesson() {
    var availableExercises = getAvailableExercises();
    
    if (availableExercises.length === 0) {
        // All exercises completed!
        alert('🎉 Congratulations! You have completed all exercises in this lesson!');
        return;
    }
    
    // Shuffle and take only what we need
    var allShuffled = shuffle([].concat(availableExercises));
    queue = allShuffled.slice(0, Math.min(SHOW_COUNT, availableExercises.length));
    
    idx = 0; 
    earnedXP = 0; 
    correctCount = 0; 
    skipped = 0;
    
    console.log('🎯 Lesson queue (only new exercises):', queue.map(function(ex) { return ex.id; }));
    
    document.getElementById('overview').style.display = 'none';
    document.getElementById('lesson').style.display = 'block';
    render();
}

// ═══════════════════════════════════════════
// PROGRESS
// ═══════════════════════════════════════════
function updateProgress() {
    var pct = queue.length ? (idx / queue.length) * 100 : 0;
    document.getElementById('progress-fill').style.width = pct + '%';
    document.getElementById('lesson-prog').style.width = pct + '%';
    document.getElementById('lesson-counter').textContent = idx + '/' + queue.length;
    document.getElementById('lesson-xp').textContent = earnedXP;
    document.getElementById('ov-xp').textContent = earnedXP;
}

// ═══════════════════════════════════════════
// ANSWER CHECKING
// ═══════════════════════════════════════════
function lev(a, b) {
    if (!a.length) return b.length;
    if (!b.length) return a.length;
    var m = [];
    for (var i = 0; i <= b.length; i++) {
        m[i] = [i];
        for (var j = 1; j <= a.length; j++) m[i][j] = i === 0 ? j : 0;
    }
    for (var i = 1; i <= b.length; i++) {
        for (var j = 1; j <= a.length; j++) {
            m[i][j] = b[i - 1] === a[j - 1] ? m[i - 1][j - 1] : 1 + Math.min(m[i - 1][j - 1], m[i][j - 1], m[i - 1][j]);
        }
    }
    return m[b.length][a.length];
}

function fuzzyMatch(user, correct) {
    var u = user.trim().toLowerCase();
    var c = correct.trim().toLowerCase();
    if (u === c) return true;
    return lev(u, c) <= Math.min(2, Math.floor(c.length * 0.25));
}
// ═══════════════════════════════════════════
// GIF ANIMATION MANAGER - Duolingo Style
// ═══════════════════════════════════════════
var gifManager = {
    // GIF paths - UPDATE THESE WITH YOUR ACTUAL GIF PATHS
    gifs: {
        correct: '../assets/success.gif',    // Celebration animation
        wrong: '../assets/wrong.gif',        // Sad/error animation
        skip: '../assets/skip.gif',          // Skip animation
        idle: '../assets/idle.gif'           // Waiting/thinking animation
    },
    
    currentTimeout: null,
    isVisible: false,
    
    // Show a GIF for a specified duration
    show: function(type, duration = 2000) {
        var container = document.getElementById('gif-container');
        var gifImg = document.getElementById('gif-animation');
        
        if (!container || !gifImg) {
            console.warn('GIF container not found');
            return;
        }
        
        // Clear any existing timeout
        if (this.currentTimeout) {
            clearTimeout(this.currentTimeout);
        }
        
        // Set the GIF source
        var gifPath = this.gifs[type] || this.gifs.idle;
        gifImg.src = gifPath + '?t=' + new Date().getTime(); // Add timestamp to prevent caching
        
        // Set container class for border color
        container.className = 'gif-' + type;
        
        // Show the container
        container.style.display = 'flex';
        setTimeout(() => {
            container.classList.add('show');
        }, 10);
        
        this.isVisible = true;
        
        // Hide after duration
        this.currentTimeout = setTimeout(() => {
            this.hide();
        }, duration);
    },
    
    // Hide the GIF
    hide: function() {
        var container = document.getElementById('gif-container');
        if (container) {
            container.classList.remove('show');
            setTimeout(() => {
                container.style.display = 'none';
            }, 400);
        }
        this.isVisible = false;
        if (this.currentTimeout) {
            clearTimeout(this.currentTimeout);
            this.currentTimeout = null;
        }
    },
    
    // Show correct answer animation
    showCorrect: function(duration = 2000) {
        this.show('correct', duration);
    },
    
    // Show wrong answer animation
    showWrong: function(duration = 2000) {
        this.show('wrong', duration);
    },
    
    // Show skip animation
    showSkip: function(duration = 1500) {
        this.show('skip', duration);
    },
    
    // Show idle/waiting animation
    showIdle: function(duration = 3000) {
        this.show('idle', duration);
    },
    
    // Show typing indicator (small inline animation)
    showTyping: function() {
        var input = document.getElementById('main-input');
        if (!input) return;
        
        // Remove any existing typing indicator
        this.removeTypingIndicator();
        
        // Create typing indicator
        var indicator = document.createElement('div');
        indicator.className = 'typing-indicator';
        indicator.id = 'typing-indicator';
        indicator.innerHTML = '<span class="typing-dot"></span><span class="typing-dot"></span><span class="typing-dot"></span>';
        
        // Insert after input
        input.parentNode.insertBefore(indicator, input.nextSibling);
    },
    
    // Remove typing indicator
    removeTypingIndicator: function() {
        var existing = document.getElementById('typing-indicator');
        if (existing) {
            existing.remove();
        }
    },
    
    // Show waiting bar (subtle animation for loading states)
    showWaitingBar: function() {
        var lessonMain = document.getElementById('lesson-main');
        if (!lessonMain) return;
        
        // Remove existing waiting bar
        this.removeWaitingBar();
        
        // Create waiting bar
        var waitingBar = document.createElement('div');
        waitingBar.className = 'waiting-animation';
        waitingBar.id = 'waiting-bar';
        waitingBar.innerHTML = '<div class="waiting-bar"></div>';
        
        lessonMain.appendChild(waitingBar);
    },
    
    // Remove waiting bar
    removeWaitingBar: function() {
        var existing = document.getElementById('waiting-bar');
        if (existing) {
            existing.remove();
        }
    }
};
// ── INPUT ACTIVITY DETECTION FOR TYPING ANIMATION ──
function setupActivityDetection() {
    var input = document.getElementById('main-input');
    if (!input) return;
    
    var typingTimer;
    
    input.addEventListener('input', function() {
        // Show typing indicator when user starts typing
        gifManager.showTyping();
        
        // Clear previous timer
        clearTimeout(typingTimer);
        
        // Set timer to hide typing indicator after user stops typing
        typingTimer = setTimeout(function() {
            gifManager.removeTypingIndicator();
        }, 1000);
    });
    
    input.addEventListener('blur', function() {
        // Hide typing indicator when input loses focus
        clearTimeout(typingTimer);
        gifManager.removeTypingIndicator();
    });
}

// ── IDLE DETECTION ──
var idleDetection = {
    timeout: null,
    idleTime: 5000, // 5 seconds of inactivity shows idle animation
    
    start: function() {
        this.reset();
        
        // Listen for user activity
        var events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'];
        var self = this;
        
        function activityHandler() {
            self.reset();
        }
        
        events.forEach(event => {
            document.addEventListener(event, activityHandler);
        });
        
        // Store handlers for cleanup
        this.handlers = activityHandler;
        this.events = events;
    },
    
    reset: function() {
        if (this.timeout) {
            clearTimeout(this.timeout);
        }
        
        // Hide any existing idle animation
        if (gifManager.isVisible && document.getElementById('gif-container').classList.contains('gif-idle')) {
            gifManager.hide();
        }
        
        // Set new timeout
        var self = this;
        this.timeout = setTimeout(function() {
            // Only show idle if lesson is visible and no other animation is playing
            if (document.getElementById('lesson').style.display === 'block' && !gifManager.isVisible) {
                gifManager.showIdle();
            }
        }, this.idleTime);
    },
    
    stop: function() {
        if (this.timeout) {
            clearTimeout(this.timeout);
        }
        
        if (this.handlers) {
            this.events.forEach(event => {
                document.removeEventListener(event, this.handlers);
            });
        }
    }
};

// ── MODIFY EXISTING FUNCTIONS TO USE GIFS ──

// Override showFeedback function to include GIFs
var originalShowFeedback = showFeedback;
showFeedback = function(isCorrect, msg) {
    // Show appropriate GIF
    if (isCorrect) {
        gifManager.showCorrect();
    } else {
        gifManager.showWrong();
    }
    
    // Call original function
    originalShowFeedback(isCorrect, msg);
};

// Override checkMCQ function to include GIFs
var originalCheckMCQ = checkMCQ;
checkMCQ = function(btn, chosen, correct) {
    var ok = chosen.toLowerCase() === correct.toLowerCase();
    
    // Show GIF before checking
    if (ok) {
        gifManager.showCorrect();
    } else {
        gifManager.showWrong();
    }
    
    // Call original function
    originalCheckMCQ(btn, chosen, correct);
};

// Override checkWordBank function
var originalCheckWordBank = checkWordBank;
checkWordBank = function(correct) {
    var ok = false;
    if (Array.isArray(correct)) {
        ok = builtWords.length === correct.length;
        if (ok) {
            for (var i = 0; i < builtWords.length; i++) {
                if (builtWords[i].toLowerCase() !== correct[i].toLowerCase()) {
                    ok = false;
                    break;
                }
            }
        }
    } else {
        ok = builtWords.join(' ').toLowerCase() === correct.toLowerCase();
    }
    
    // Show GIF
    if (ok) {
        gifManager.showCorrect();
    } else {
        gifManager.showWrong();
    }
    
    // Call original function
    originalCheckWordBank(correct);
};

// Override checkErrorCorrectionFn
var originalCheckErrorCorrection = checkErrorCorrectionFn;
checkErrorCorrectionFn = function(correct, explanation) {
    var correctArray = Array.isArray(correct) ? correct : correct.split(' ');
    var ok = builtWords.length === correctArray.length;
    if (ok) {
        for (var i = 0; i < builtWords.length; i++) {
            if (builtWords[i].toLowerCase() !== correctArray[i].toLowerCase()) {
                ok = false;
                break;
            }
        }
    }
    
    // Show GIF
    if (ok) {
        gifManager.showCorrect();
    } else {
        gifManager.showWrong();
    }
    
    // Call original function
    originalCheckErrorCorrection(correct, explanation);
};

// Add loading animation when moving to next exercise
var originalDoNext = doNext;
doNext = function() {
    // Show waiting bar while loading next exercise
    gifManager.showWaitingBar();
    
    // Hide typing indicator if visible
    gifManager.removeTypingIndicator();
    
    // Call original function after a slight delay
    setTimeout(function() {
        originalDoNext();
        
        // Remove waiting bar
        gifManager.removeWaitingBar();
        
        // Setup activity detection for new input
        setTimeout(setupActivityDetection, 100);
    }, 300);
};

// Add animation when completing lesson
var originalShowCompletion = showCompletion;
showCompletion = function() {
    // Show celebration animation
    gifManager.showCorrect(3000);
    
    // Call original function
    originalShowCompletion();
};

// Initialize activity detection when lesson starts
var originalStartLesson = startLesson;
startLesson = function() {
    originalStartLesson();
    
    // Setup activity detection
    setTimeout(function() {
        setupActivityDetection();
        
        // Start idle detection
        idleDetection.start();
    }, 500);
};

// Clean up when closing lesson
var originalCloseLesson = closeLesson;
closeLesson = function() {
    // Stop idle detection
    idleDetection.stop();
    
    // Hide any visible GIFs
    gifManager.hide();
    gifManager.removeTypingIndicator();
    gifManager.removeWaitingBar();
    
    originalCloseLesson();
};

// ═══════════════════════════════════════════
// SKIP / NEXT
// ═══════════════════════════════════════════
function doSkip() {
    // Show skip animation
    gifManager.showSkip();
    
    skipped++;
    if (timerInt) {
        clearInterval(timerInt);
        timerInt = null;
    }
    idx++;
    render();
}

function doNext() {
    console.log('➡️ Next button clicked'); // Debug log
    
    // Hide any visible GIFs
    if (typeof gifManager !== 'undefined') {
        gifManager.hide();
        gifManager.removeTypingIndicator();
        gifManager.removeWaitingBar();
    }
    
    if (timerInt) {
        clearInterval(timerInt);
        timerInt = null;
    }
    
    idx++;
    console.log('📊 Moving to exercise index:', idx, 'Queue length:', queue.length); // Debug log
    
    // If we've reached the end of the queue
    if (idx >= queue.length) {
        console.log('🏁 End of queue reached'); // Debug log
        // Check if there are more available exercises not in queue
        var availableExercises = getAvailableExercises();
        var remainingNew = [];
        for (var i = 0; i < availableExercises.length; i++) {
            var ex = availableExercises[i];
            var inQueue = false;
            for (var j = 0; j < queue.length; j++) {
                if (queue[j].id === ex.id) {
                    inQueue = true;
                    break;
                }
            }
            if (!inQueue) {
                remainingNew.push(ex);
            }
        }
        
        if (remainingNew.length > 0) {
            // Add more exercises to the queue
            console.log('➕ Adding ' + remainingNew.length + ' more exercises to queue');
            var moreExercises = shuffle(remainingNew).slice(0, SHOW_COUNT);
            queue = queue.concat(moreExercises);
        }
    }
    
    render();
}

function showFeedback(isCorrect, msg) {
    var fb = document.getElementById('fb');
    if (!fb) return;
    fb.className = 'feedback show ' + (isCorrect ? 'correct' : 'wrong');
    fb.textContent = msg;
}

function revealNext() {
    var nb = document.getElementById('next-btn');
    if (nb) nb.classList.add('show');
}

function disableInputs() {
    var inputs = document.querySelectorAll('.mcq-btn,.bank-word,.match-item,.tf-btn');
    for (var i = 0; i < inputs.length; i++) {
        inputs[i].style.pointerEvents = 'none';
    }
    var inp = document.getElementById('main-input');
    if (inp) inp.disabled = true;
}

// ═══════════════════════════════════════════
// WORD BANK ENGINE
// ═══════════════════════════════════════════
function setupWordBank(words, correct, containerId) {
    builtWords = [];
    var bank = document.getElementById('bank-' + containerId);
    var builder = document.getElementById('builder-' + containerId);
    if (!bank || !builder) return;
    
    var bankWords = bank.querySelectorAll('.bank-word');
    for (var i = 0; i < bankWords.length; i++) {
        (function(el) {
            el.addEventListener('click', function() {
                if (el.classList.contains('used')) return;
                el.classList.add('used');
                builtWords.push(el.dataset.word);
                updateBuilder(builder, builtWords, bank, correct, containerId);
            });
        })(bankWords[i]);
    }
}

function updateBuilder(builder, words, bank, correct, cid) {
    if (words.length === 0) {
        builder.innerHTML = '<span class="builder-placeholder">Tap words below to build sentence…</span>';
        builder.classList.remove('has-words');
    } else {
        builder.classList.add('has-words');
        var html = '';
        for (var i = 0; i < words.length; i++) {
            html += '<span class="built-word" data-idx="' + i + '">' + words[i] + ' <span class="rm">✕</span></span>';
        }
        builder.innerHTML = html;
        
        var builtWordsEls = builder.querySelectorAll('.built-word');
        for (var i = 0; i < builtWordsEls.length; i++) {
            (function(idx) {
                builtWordsEls[idx].querySelector('.rm').addEventListener('click', function() {
                    var wi = parseInt(builtWordsEls[idx].dataset.idx);
                    var removed = builtWords.splice(wi, 1)[0];
                    var bankWords = bank.querySelectorAll('.bank-word');
                    for (var j = 0; j < bankWords.length; j++) {
                        if (bankWords[j].dataset.word === removed && bankWords[j].classList.contains('used')) {
                            bankWords[j].classList.remove('used');
                            break;
                        }
                    }
                    updateBuilder(builder, builtWords, bank, correct, cid);
                });
            })(i);
        }
    }
}

function checkWordBank(correct) {
    if (builtWords.length === 0) {
        shake();
        return;
    }
    var isArr = Array.isArray(correct);
    var ok = false;
    var ex = queue[idx];

    if (isArr) {
        ok = builtWords.length === correct.length;
        if (ok) {
            for (var i = 0; i < builtWords.length; i++) {
                if (builtWords[i].toLowerCase() !== correct[i].toLowerCase()) {
                    ok = false;
                    break;
                }
            }
        }
    } else {
        ok = builtWords.join(' ').toLowerCase() === correct.toLowerCase();
    }

    disableInputs();
    var ans = isArr ? correct.join(' ') : correct;
    showFeedback(ok, ok ? '✓ Correct!' : '✗ Correct: ' + ans);

    if (ok) {
        correctCount++;
        earnedXP += ex.xp;
        updateProgress();

        // SAVE TO DATABASE
        console.log('💾 Saving word bank exercise:', ex.id);
        saveProgressToDatabase(ex.id, true, ex.xp, 10);
    }

    revealNext();
}

// ═══════════════════════════════════════════
// ERROR CORRECTION FIX
// ═══════════════════════════════════════════
function checkErrorCorrectionFn(correct, explanation) {
    // Convert both to arrays if needed
    var correctArray = Array.isArray(correct) ? correct : correct.split(' ');
    var userArray = builtWords;
    
    console.log('🔍 Error Correction Check:', {
        userWords: userArray,
        correctWords: correctArray,
        userSentence: userArray.join(' '),
        correctSentence: correctArray.join(' ')
    });
    
    // Compare arrays (order matters for sentences)
    var ok = userArray.length === correctArray.length;
    if (ok) {
        for (var i = 0; i < userArray.length; i++) {
            if (userArray[i].toLowerCase() !== correctArray[i].toLowerCase()) {
                ok = false;
                break;
            }
        }
    }
    
    var ex = queue[idx];
    
    disableInputs();
    var fb = document.getElementById('fb');
    if (fb) {
        fb.className = 'feedback show ' + (ok ? 'correct' : 'wrong');
        fb.textContent = ok ? '✓ Correct! ' + explanation : 
            '✗ Correct: ' + correctArray.join(' ') + '. ' + explanation;
    }
    
    if (ok) {
        correctCount++;
        earnedXP += ex.xp;
        updateProgress();

        // SAVE TO DATABASE
        console.log('💾 Saving error correction:', ex.id);
        saveProgressToDatabase(ex.id, true, ex.xp, 10);
    }

    revealNext();
}

function shake() {
    var card = document.querySelector('.ex-card');
    if (card) {
        card.classList.add('shake');
        setTimeout(function() { card.classList.remove('shake'); }, 400);
    }
}

// ═══════════════════════════════════════════
// TTS
// ═══════════════════════════════════════════
function audioPath(f) {
    return AUDIO_BASE + f + '.mp3';
}

function speak(text, btn) {
    if (!text) return;
    var origLabel = btn ? (btn.dataset.origLabel || btn.textContent) : '🔊 Play Audio';
    if (btn) btn.dataset.origLabel = origLabel;

    function resetBtn() {
        if (btn) {
            btn.classList.remove('playing');
            btn.textContent = origLabel;
        }
    }

    function setPlaying() {
        if (btn) {
            btn.classList.add('playing');
            btn.textContent = '🔊 Playing…';
        }
    }
    if (LANG === 'en') {
        var sm = null;
        for (var i = 0; i < RW_AUDIO_FILES.length; i++) {
            if (RW_AUDIO_FILES[i].toLowerCase() === text.trim().toLowerCase()) {
                sm = RW_AUDIO_FILES[i];
                break;
            }
        }
        if (sm) {
            setPlaying();
            var a = new Audio(audioPath(sm));
            a.onended = resetBtn;
            a.onerror = function() {
                resetBtn();
                if (btn) {
                    btn.textContent = '🔇 File not found';
                    setTimeout(resetBtn, 1800);
                }
            };
            a.play()['catch'](function() {
                resetBtn();
                if (btn) {
                    btn.textContent = '🔇 Playback error';
                    setTimeout(resetBtn, 1800);
                }
            });
            return;
        }
        var words = text.trim().split(/\s+/);
        var hasMatch = false;
        for (var i = 0; i < words.length; i++) {
            for (var j = 0; j < RW_AUDIO_FILES.length; j++) {
                if (RW_AUDIO_FILES[j].toLowerCase() === words[i].toLowerCase()) {
                    hasMatch = true;
                    break;
                }
            }
            if (hasMatch) break;
        }
        if (hasMatch) {
            setPlaying();
            playWordsSequentially(words, 0, resetBtn);
            return;
        }
        if (btn) {
            btn.textContent = '🔇 No audio file';
            setTimeout(resetBtn, 1800);
        }
        return;
    }
    speakWithSynthesis(text, btn, LANG === 'fr' ? 'fr-FR' : 'en-US');
}

function playWordsSequentially(words, i, onDone) {
    if (i >= words.length) {
        onDone();
        return;
    }
    var match = null;
    for (var j = 0; j < RW_AUDIO_FILES.length; j++) {
        if (RW_AUDIO_FILES[j].toLowerCase() === words[i].toLowerCase()) {
            match = RW_AUDIO_FILES[j];
            break;
        }
    }
    if (!match) {
        playWordsSequentially(words, i + 1, onDone);
        return;
    }
    var a = new Audio(audioPath(match));
    a.onended = function() { 
        setTimeout(function() { playWordsSequentially(words, i + 1, onDone); }, 130); 
    };
    a.onerror = function() { playWordsSequentially(words, i + 1, onDone); };
    a.play()['catch'](function() { playWordsSequentially(words, i + 1, onDone); });
}

function speakWithSynthesis(text, btn, lang) {
    if (!window.speechSynthesis) {
        if (btn) {
            btn.textContent = '🔇 Not supported';
            setTimeout(function() {
                if (btn) btn.textContent = btn.dataset.origLabel || '🔊 Play Audio';
                if (btn) btn.classList.remove('playing');
            }, 1800);
        }
        return;
    }
    window.speechSynthesis.cancel();
    var utt = new SpeechSynthesisUtterance(text);
    utt.lang = lang || 'en-US';
    utt.rate = 0.85;
    if (btn) {
        btn.classList.add('playing');
        btn.textContent = '🔊 Playing…';
        var orig = btn.dataset.origLabel || '🔊 Play Audio';
        utt.onend = function() {
            btn.classList.remove('playing');
            btn.textContent = orig;
        };
        utt.onerror = function() {
            btn.classList.remove('playing');
            btn.textContent = orig;
        };
    }
    window.speechSynthesis.speak(utt);
}

// ═══════════════════════════════════════════
// STORY EXERCISES RENDERER
// ═══════════════════════════════════════════
function renderStoryExercises(exercises) {
    var container = document.getElementById('story-exercises');
    if (!container || !exercises.length) return;
    
    storyExercises = exercises;
    container.innerHTML = '<h3 style="margin:20px 0 10px">📝 Now build these sentences:</h3>';
    
    for (var idx = 0; idx < exercises.length; idx++) {
        var ex = exercises[idx];
        if (ex.type === 'word_bank') {
            var containerId = 'story-wb-' + idx;
            var wordBankHtml = '';
            if (ex.word_bank) {
                for (var w = 0; w < ex.word_bank.length; w++) {
                    wordBankHtml += '<span class="bank-word" data-word="' + esc(ex.word_bank[w]) + '">' + esc(ex.word_bank[w]) + '</span>';
                }
            }
            
            container.innerHTML += '<div class="story-exercise-card" id="story-card-' + idx + '">' +
                '<p style="font-weight:700; margin-bottom:10px">' + (idx + 1) + '. ' + esc(ex.question) + '</p>' +
                '<div class="sentence-builder" id="builder-' + containerId + '"><span class="builder-placeholder">Tap words below to build sentence…</span></div>' +
                '<div class="word-bank" id="bank-' + containerId + '">' + wordBankHtml + '</div>' +
                '<button class="check-btn" style="margin-top:10px" onclick="checkStoryExercise(\'' + containerId + '\', ' + JSON.stringify(ex.correct_answer || ex.correct) + ')">Check</button>' +
            '</div>';
            
            // Initialize word bank for this story exercise
            setTimeout(function(id, answer) {
                setupStoryWordBank(id, answer);
            }, 100, containerId, ex.correct_answer || ex.correct);
        }
    }
}

// ═══════════════════════════════════════════
// STORY WORD BANK SETUP
// ═══════════════════════════════════════════
function setupStoryWordBank(containerId, correctAnswer) {
    var bank = document.getElementById('bank-' + containerId);
    var builder = document.getElementById('builder-' + containerId);
    if (!bank || !builder) return;
    
    storyBuiltWords[containerId] = [];
    
    var bankWords = bank.querySelectorAll('.bank-word');
    for (var i = 0; i < bankWords.length; i++) {
        (function(el, id) {
            el.addEventListener('click', function() {
                if (el.classList.contains('used')) return;
                el.classList.add('used');
                storyBuiltWords[id].push(el.dataset.word);
                updateStoryBuilder(id, builder, bank, correctAnswer);
            });
        })(bankWords[i], containerId);
    }
}

function updateStoryBuilder(containerId, builder, bank, correctAnswer) {
    var words = storyBuiltWords[containerId] || [];
    
    if (words.length === 0) {
        builder.innerHTML = '<span class="builder-placeholder">Tap words below to build sentence…</span>';
        builder.classList.remove('has-words');
    } else {
        builder.classList.add('has-words');
        var html = '';
        for (var i = 0; i < words.length; i++) {
            html += '<span class="built-word" data-idx="' + i + '">' + words[i] + ' <span class="rm">✕</span></span>';
        }
        builder.innerHTML = html;
        
        var builtWordsEls = builder.querySelectorAll('.built-word');
        for (var i = 0; i < builtWordsEls.length; i++) {
            (function(idx) {
                builtWordsEls[idx].querySelector('.rm').addEventListener('click', function() {
                    var wi = parseInt(builtWordsEls[idx].dataset.idx);
                    var removed = storyBuiltWords[containerId].splice(wi, 1)[0];
                    var bankWords = bank.querySelectorAll('.bank-word');
                    for (var j = 0; j < bankWords.length; j++) {
                        if (bankWords[j].dataset.word === removed && bankWords[j].classList.contains('used')) {
                            bankWords[j].classList.remove('used');
                            break;
                        }
                    }
                    updateStoryBuilder(containerId, builder, bank, correctAnswer);
                });
            })(i);
        }
    }
}

// ═══════════════════════════════════════════
// CHECK STORY EXERCISE
// ═══════════════════════════════════════════
function checkStoryExercise(containerId, correctAnswer) {
    var builder = document.getElementById('builder-' + containerId);
    if (!builder) return;
    
    // Get words from builder
    var words = storyBuiltWords[containerId] || [];
    
    var correct = Array.isArray(correctAnswer) ? correctAnswer : correctAnswer.split(' ');
    var ok = words.length === correct.length;
    if (ok) {
        for (var i = 0; i < words.length; i++) {
            if (words[i].toLowerCase() !== correct[i].toLowerCase()) {
                ok = false;
                break;
            }
        }
    }
    
    var fb = document.createElement('div');
    fb.className = 'feedback show ' + (ok ? 'correct' : 'wrong');
    fb.textContent = ok ? '✓ Correct!' : '✗ Correct: ' + correct.join(' ');
    
    // Remove any existing feedback in this card
    var cardIdParts = containerId.split('-');
    var card = document.getElementById('story-card-' + cardIdParts[cardIdParts.length - 1]);
    if (card) {
        var oldFb = card.querySelector('.feedback');
        if (oldFb) oldFb.remove();
        card.appendChild(fb);
    }
    
    if (ok) {
        // Disable buttons in this card
        var bank = document.getElementById('bank-' + containerId);
        if (bank) {
            var bankWords = bank.querySelectorAll('.bank-word');
            for (var i = 0; i < bankWords.length; i++) {
                bankWords[i].style.pointerEvents = 'none';
            }
        }
        
        // Check if all story exercises are complete
        var allChecks = document.querySelectorAll('#story-exercises .feedback.correct');
        if (allChecks.length === storyExercises.length) {
            // All story exercises completed!
            var ex = queue[idx];
            correctCount++;
            earnedXP += ex.xp;
            updateProgress();
            saveProgressToDatabase(ex.id, true, ex.xp, 30);
            revealNext();
        }
    }
}

// ═══════════════════════════════════════════
// ESCAPE HTML
// ═══════════════════════════════════════════
function esc(s) {
    return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

// ═══════════════════════════════════════════
// RENDER
// ═══════════════════════════════════════════
function render() {
    updateProgress();
    if (idx >= queue.length) {
        showCompletion();
        return;
    }
    var ex = queue[idx];
    var main = document.getElementById('lesson-main');
    var typeName = ex.type.replace(/_/g, ' ').replace(/\b\w/g, function(c) { return c.toUpperCase(); });
    var letters = ['A', 'B', 'C', 'D', 'E', 'F'];

    var body = '<div class="ex-card">' +
    '<div class="ex-meta">' +
      '<span class="ex-type">' + typeName + '</span>' +
      '<span style="color:var(--muted);font-size:.85rem">' + (idx + 1) + '/' + queue.length + '</span>' +
      '<span class="ex-xp">+' + ex.xp + ' XP</span>' +
    '</div>';

    if (ex.type === 'translation') {
        body += '<div class="question-box">' + esc(ex.question) + '</div>' +
    '<input id="main-input" class="text-input" placeholder="Type your answer…" autocomplete="off" data-answer="' + esc(ex.answer) + '">' +
    '<div id="fb" class="feedback"></div>' +
    '<div class="action-row"><button class="check-btn" id="check-trans">Check</button><button id="next-btn" class="next-btn" onclick="doNext()">Continue →</button><button class="skip-btn" onclick="doSkip()">Skip</button></div>';
    } else if (ex.type === 'multiple_choice') {
        body += '<div class="question-box">' + esc(ex.question) + '</div><div class="mcq-grid">';
        for (var i = 0; i < ex.options.length; i++) {
            var opt = ex.options[i];
            body += '<button class="mcq-btn" onclick="checkMCQ(this,\'' + esc(opt) + '\',\'' + esc(ex.answer || ex.correct) + '\')"><span class="opt-key">' + letters[i] + '</span>' + esc(opt) + '</button>';
        }
        body += '</div><div id="fb" class="feedback"></div><div class="action-row"><button id="next-btn" class="next-btn" onclick="doNext()">Continue →</button><button class="skip-btn" onclick="doSkip()">Skip</button></div>';
    } else if (ex.type === 'listen_and_type') {
        body += '<div class="question-box">' + esc(ex.question) + '</div>' +
    '<button class="audio-btn" id="tts-btn" data-tts="' + esc(ex.tts) + '">🔊 Play Audio</button>' +
    '<input id="main-input" class="text-input" placeholder="Type what you hear…" autocomplete="off" data-answer="' + esc(ex.answer) + '">' +
    '<div id="fb" class="feedback"></div>' +
    '<div class="action-row"><button class="check-btn" id="check-trans">Check</button><button id="next-btn" class="next-btn" onclick="doNext()">Continue →</button><button class="skip-btn" onclick="doSkip()">Skip</button></div>';
    } else if (ex.type === 'listen_and_choose' || ex.type === 'listening_comprehension') {
        var tts = ex.tts || ex.dialogue || '';
        var ans = ex.answer || ex.correct || '';
        body += '<div class="question-box">' + esc(ex.question) + '</div>' +
    '<div style="text-align:center;margin-bottom:16px"><button class="audio-btn" id="tts-btn" data-tts="' + esc(tts) + '">🔊 Play Audio</button></div>' +
    '<div class="mcq-grid" id="lac-grid" data-answer="' + esc(ans) + '">';
        for (var i = 0; i < ex.options.length; i++) {
            var opt = ex.options[i];
            body += '<button class="mcq-btn" data-opt="' + esc(opt) + '"><span class="opt-key">' + letters[i] + '</span>' + esc(opt) + '</button>';
        }
        body += '</div><div id="fb" class="feedback"></div><div class="action-row"><button id="next-btn" class="next-btn" onclick="doNext()">Continue →</button><button class="skip-btn" onclick="doSkip()">Skip</button></div>';
    } else if (ex.type === 'speaking' || ex.type === 'pronunciation') {
        var micLang = LANG === 'en' ? 'rw' : (LANG === 'fr' ? 'fr-FR' : 'en-US');
        body += '<div class="question-box">' + esc(ex.question) + '</div>' +
    '<div class="mic-wrap">' +
      '<div class="speak-prompt">' + esc(ex.prompt) + '</div>' +
      (ex.translation ? '<div style="color:var(--muted);margin-bottom:14px">' + esc(ex.translation) + '</div>' : '') +
      '<button class="audio-btn" style="margin-bottom:16px" id="hear-btn" data-tts="' + esc(ex.prompt) + '"></button>' +
      '<div><button class="mic-btn" id="mic-btn" data-prompt="' + esc(ex.prompt) + '" data-lang="' + micLang + '">🎤 Tap to Speak</button></div>' +
      (!SpeechRecognition ? '<div style="color:var(--red);font-size:.85rem;margin-top:10px">⚠️ Use Chrome for speech recognition</div>' : '') +
    '</div>' +
    '<div id="fb" class="feedback"></div>' +
    '<div class="action-row"><button id="next-btn" class="next-btn" onclick="doNext()">Continue →</button><button class="skip-btn" onclick="doSkip()">Skip</button></div>';
    } else if (ex.type === 'word_bank' || ex.type === 'tap_hear' || ex.type === 'sentence_scramble' || ex.type === 'conversation') {
        var wb = ex.word_bank || ex.words || [];
        var wbHtml = '';
        for (var i = 0; i < wb.length; i++) {
            wbHtml += '<span class="bank-word" data-word="' + esc(wb[i]) + '">' + esc(wb[i]) + '</span>';
        }
        
        if (ex.type === 'tap_hear') body += '<div class="question-box">' + esc(ex.question) + '</div><button class="audio-btn" onclick="speak(\'' + esc(ex.tts || '') + '\',this)">🔊 Play Audio</button>';
        else if (ex.type === 'conversation') body += '<div class="question-box">🗣 ' + esc(ex.bot) + '</div><div style="color:var(--muted);font-size:.85rem;margin-bottom:14px">"' + esc(ex.translation) + '"</div>' + (ex.image ? '<img src="' + esc(ex.image) + '" class="ex-image" alt="" onerror="this.style.display=\'none\'">' : '');
        else if (ex.type === 'sentence_scramble') body += '<div class="question-box">' + esc(ex.question) + '</div><p style="margin-bottom:10px;font-weight:700">Arrange the words:</p>';
        else body += '<div class="question-box">' + esc(ex.question) + '</div>';
        body += '<div class="sentence-builder" id="builder-wb"><span class="builder-placeholder">Tap words below to build sentence…</span></div>' +
    '<div class="word-bank" id="bank-wb">' + wbHtml + '</div>' +
    '<div id="fb" class="feedback"></div>' +
    '<div class="action-row"><button class="check-btn" id="check-wb">Check</button><button id="next-btn" class="next-btn" onclick="doNext()">Continue →</button><button class="skip-btn" onclick="doSkip()">Skip</button></div>';
    } else if (ex.type === 'error_correction') {
        var wb = ex.word_bank_for_fix || ex.word_bank || [];
        var wbHtml = '';
        for (var i = 0; i < wb.length; i++) {
            wbHtml += '<span class="bank-word" data-word="' + esc(wb[i]) + '">' + esc(wb[i]) + '</span>';
        }
        body += '<div class="question-box">✏️ ' + esc(ex.question) + '</div>' +
    '<div class="err-sentence">❌ ' + esc(ex.incorrect || ex.incorrect_sentence) + '</div>' +
    '<p style="margin-bottom:10px;font-weight:700">Build the correct sentence:</p>' +
    '<div class="sentence-builder" id="builder-wb"><span class="builder-placeholder">Tap words below to build the correct sentence…</span></div>' +
    '<div class="word-bank" id="bank-wb">' + wbHtml + '</div>' +
    '<div id="fb" class="feedback"></div>' +
    '<div class="action-row"><button class="check-btn" id="check-wb">Check</button><button id="next-btn" class="next-btn" onclick="doNext()">Continue →</button><button class="skip-btn" onclick="doSkip()">Skip</button></div>';
    } else if (ex.type === 'fill_blank' || ex.type === 'choose_missing' || ex.type === 'grammar') {
        var txt = ex.text || ex.text_with_blank || ex.sentence || '';
        if (ex.type === 'grammar') body += '<div class="question-box">📐 ' + esc(ex.question) + '</div><div class="grammar-sentence">' + esc(ex.sentence) + '</div>';
        else {
            body += '<div class="question-box">' + esc(ex.question) + '</div><div style="font-size:1.2rem;margin-bottom:16px;font-weight:700;background:var(--yellow-light);padding:12px 20px;border-radius:14px">' + esc(txt) + '</div>';
        }
        var ans = ex.correct || ex.correct_answer || ex.answer || '';
        body += '<div class="mcq-grid">';
        for (var i = 0; i < ex.options.length; i++) {
            var opt = ex.options[i];
            var fn = ex.type === 'grammar' ? 'checkMCQWithExplain(this,\'' + esc(opt) + '\',\'' + esc(ans) + '\',\'' + esc(ex.explanation || '') + '\')' : 'checkMCQ(this,\'' + esc(opt) + '\',\'' + esc(ans) + '\')';
            body += '<button class="mcq-btn" onclick="' + fn + '"><span class="opt-key">' + letters[i] + '</span>' + esc(opt) + '</button>';
        }
        body += '</div><div id="fb" class="feedback"></div><div class="action-row"><button id="next-btn" class="next-btn" onclick="doNext()">Continue →</button><button class="skip-btn" onclick="doSkip()">Skip</button></div>';
    } else if (ex.type === 'picture_word') {
        var ans = ex.correct || ex.correct_answer || '';
        body += '<div class="question-box">' + esc(ex.question) + '</div>' +
    '<img src="' + esc(ex.image || ex.image_url || '') + '" class="ex-image" alt="Animal" onerror="this.style.display=\'none\'">' +
    '<div class="mcq-grid">';
        for (var i = 0; i < ex.options.length; i++) {
            var opt = ex.options[i];
            body += '<button class="mcq-btn" onclick="checkMCQ(this,\'' + esc(opt) + '\',\'' + esc(ans) + '\')"><span class="opt-key">' + letters[i] + '</span>' + esc(opt) + '</button>';
        }
        body += '</div><div id="fb" class="feedback"></div><div class="action-row"><button id="next-btn" class="next-btn" onclick="doNext()">Continue →</button><button class="skip-btn" onclick="doSkip()">Skip</button></div>';
    } else if (ex.type === 'matching' || ex.type === 'speed_matching') {
        var pairs = ex.pairs || [];
        var right = [];
        for (var i = 0; i < pairs.length; i++) {
            right.push(pairs[i].r);
        }
        right = shuffle(right);
        
        var leftHtml = '';
        for (var i = 0; i < pairs.length; i++) {
            leftHtml += '<div class="match-item" data-side="L" data-val="' + esc(pairs[i].l) + '" data-pair="' + esc(pairs[i].r) + '">' + esc(pairs[i].l) + '</div>';
        }
        
        var rightHtml = '';
        for (var i = 0; i < right.length; i++) {
            rightHtml += '<div class="match-item" data-side="R" data-val="' + esc(right[i]) + '">' + esc(right[i]) + '</div>';
        }
        
        body += '<div class="question-box">' + esc(ex.question || ex.title || 'Match the pairs') + '</div>';
        if (ex.time && ex.type === 'speed_matching') body += '<div class="timer-pill" id="match-timer">⏱ ' + ex.time + 's</div>';
        body += '<div class="match-score" id="match-score">0 / ' + pairs.length + ' matched</div>' +
    '<div class="match-grid" id="match-wrap" data-total="' + pairs.length + '" data-matched="0">' +
      '<div class="match-col">' + leftHtml + '</div>' +
      '<div class="match-col">' + rightHtml + '</div>' +
    '</div>' +
    '<div id="fb" class="feedback"></div>' +
    '<div class="action-row"><button id="next-btn" class="next-btn" onclick="doNext()">Continue →</button><button class="skip-btn" onclick="doSkip()">Skip</button></div>';
    } else if (ex.type === 'read_answer') {
        var ans = ex.correct || ex.correct_answer || '';
        body += '<div class="read-context">' + esc(ex.text) + '</div><div class="question-box" style="font-size:1.3rem">❓ ' + esc(ex.question) + '</div><div class="mcq-grid">';
        for (var i = 0; i < ex.options.length; i++) {
            var opt = ex.options[i];
            body += '<button class="mcq-btn" onclick="checkMCQ(this,\'' + esc(opt) + '\',\'' + esc(ans) + '\')"><span class="opt-key">' + letters[i] + '</span>' + esc(opt) + '</button>';
        }
        body += '</div><div id="fb" class="feedback"></div><div class="action-row"><button id="next-btn" class="next-btn" onclick="doNext()">Continue →</button><button class="skip-btn" onclick="doSkip()">Skip</button></div>';
    } else if (ex.type === 'true_false') {
        var ans = (ex.correct === true || ex.correct_answer === true) ? 'True' : 'False';
        body += '<div class="question-box">' + esc(ex.statement) + '</div>' +
    '<div style="color:var(--muted);font-size:.9rem;margin-bottom:16px;text-align:center">' + esc(ex.translation) + '</div>' +
    '<div class="tf-grid">' +
      '<button class="tf-btn true-btn" onclick="checkMCQ(this,\'True\',\'' + ans + '\')">✓ True</button>' +
      '<button class="tf-btn false-btn" onclick="checkMCQ(this,\'False\',\'' + ans + '\')">✗ False</button>' +
    '</div>' +
    '<div id="fb" class="feedback"></div>' +
    '<div class="action-row"><button id="next-btn" class="next-btn" onclick="doNext()">Continue →</button><button class="skip-btn" onclick="doSkip()">Skip</button></div>';
    } else if (ex.type === 'conversation_choice') {
        var ans = ex.correct || ex.correct_response || '';
        body += '<div class="question-box">🗣 ' + esc(ex.bot || ex.bot_message) + '</div>' +
    '<div style="color:var(--muted);font-size:.85rem;margin-bottom:14px">"' + esc(ex.translation) + '"</div>' +
    '<div class="mcq-grid">';
        for (var i = 0; i < ex.options.length; i++) {
            var opt = ex.options[i];
            body += '<button class="mcq-btn" onclick="checkMCQ(this,\'' + esc(opt) + '\',\'' + esc(ans) + '\')"><span class="opt-key">' + letters[i] + '</span>' + esc(opt) + '</button>';
        }
        body += '</div><div id="fb" class="feedback"></div><div class="action-row"><button id="next-btn" class="next-btn" onclick="doNext()">Continue →</button><button class="skip-btn" onclick="doSkip()">Skip</button></div>';
    } else if (ex.type === 'story') {
        var segs = ex.segments || [];
        var storyExercisesList = ex.exercises || ex.items || [];
        
        var segsHtml = '';
        for (var i = 0; i < segs.length; i++) {
            segsHtml += '<div class="story-text">' + esc(segs[i].text || segs[i]) + '</div>';
        }
        
        body += '<div class="question-box">📖 ' + esc(ex.title) + '</div>' +
    '<p style="margin-bottom:14px;color:var(--muted)">' + esc(ex.intro || ex.story_intro || '') + '</p>' +
    segsHtml +
    '<div id="story-exercises"></div>' +
    '<div id="fb" class="feedback"></div>' +
    '<div class="action-row"><button id="next-btn" class="next-btn" onclick="doNext()">Continue →</button><button class="skip-btn" onclick="doSkip()">Skip</button></div>';
        
        earnedXP += ex.xp;
        updateProgress();
        
        // After setting innerHTML, render the exercises
        setTimeout(function(list) {
            renderStoryExercises(list);
        }, 100, storyExercisesList);
    } else if (ex.type === 'flashcards') {
        var cards = ex.cards || ex.flashcards || [];
        var cardsHtml = '';
        for (var i = 0; i < cards.length; i++) {
            cardsHtml += '<div class="fc" onclick="this.classList.toggle(\'flipped\')"><div class="fc-front">' + esc(cards[i].front) + '</div><div class="fc-back">' + esc(cards[i].back) + '</div></div>';
        }
        body += '<div class="question-box">' + esc(ex.question) + '</div>' +
    '<p style="color:var(--muted);font-size:.85rem;margin-bottom:14px;text-align:center">Tap each card to reveal the Kinyarwanda word</p>' +
    '<div class="fc-grid">' + cardsHtml + '</div>' +
    '<div class="action-row"><button id="next-btn" class="next-btn show" onclick="doNext()">Continue →</button><button class="skip-btn" onclick="doSkip()">Skip</button></div>';
        earnedXP += ex.xp;
        updateProgress();
    } else if (ex.type === 'identify_meaning') {
        var ctx = ex.context || ex.context_sentence || '';
        var highlighted = ctx.replace(new RegExp('\\b' + ex.word + '\\b', 'gi'), function(m) { return '<span class="highlight-word">' + m + '</span>'; });
        var ans = ex.correct || ex.correct_answer || '';
        body += '<div class="question-box">' + esc(ex.question) + '</div>' +
    '<p class="ctx-sentence">' + highlighted + '</p>' +
    '<div class="word-meaning">' + esc(ex.word) + '</div>' +
    '<div class="mcq-grid">';
        for (var i = 0; i < ex.options.length; i++) {
            var opt = ex.options[i];
            body += '<button class="mcq-btn" onclick="checkMCQ(this,\'' + esc(opt) + '\',\'' + esc(ans) + '\')"><span class="opt-key">' + letters[i] + '</span>' + esc(opt) + '</button>';
        }
        body += '</div><div id="fb" class="feedback"></div><div class="action-row"><button id="next-btn" class="next-btn" onclick="doNext()">Continue →</button><button class="skip-btn" onclick="doSkip()">Skip</button></div>';
    } else if (ex.type === 'timed_challenge') {
        body += '<div class="question-box">⚡ ' + esc(ex.title) + '</div>' +
    '<div class="timer-pill" id="tc-timer">⏱ ' + (ex.time || ex.time_limit_seconds || 30) + 's</div>' +
    '<div id="tc-content">' +
      '<p style="margin-bottom:16px">Translate ' + (ex.questions || []).length + ' words as fast as you can!</p>' +
      '<button class="start-btn" style="width:auto;padding:14px 28px;font-size:1rem" onclick="startTimedChallenge()">Start!</button>' +
    '</div>' +
    '<div id="fb" class="feedback"></div>' +
    '<div class="action-row"><button id="next-btn" class="next-btn" onclick="doNext()">Continue →</button><button class="skip-btn" onclick="doSkip()">Skip</button></div>';
    } else if (ex.type === 'character_writing') {
        body += '<div class="question-box">✍️ ' + esc(ex.question) + '</div>' +
    '<div style="text-align:center;font-family:\'Fredoka One\',cursive;font-size:2rem;margin-bottom:16px">' + esc(ex.prompt) + '</div>' +
    '<div style="color:var(--muted);margin-bottom:12px;text-align:center">' + esc(ex.translation) + '</div>' +
    '<canvas id="write-canvas" width="600" height="180" style="border:3px solid var(--border);border-radius:20px;background:#fff;width:100%;touch-action:none;cursor:crosshair;display:block;margin-bottom:12px"></canvas>' +
    '<div style="text-align:center;margin-bottom:16px"><button onclick="clearWriteCanvas()" style="background:var(--red-light);border:2px solid var(--red);border-radius:30px;padding:8px 20px;font-weight:700;cursor:pointer">Clear</button></div>' +
    '<div id="fb" class="feedback"></div>' +
    '<div class="action-row"><button class="check-btn" onclick="checkWriting()">Submit</button><button id="next-btn" class="next-btn" onclick="doNext()">Continue →</button><button class="skip-btn" onclick="doSkip()">Skip</button></div>';
    } else if (ex.type === 'review') {
        var items = ex.items || ex.exercises || [];
        body += '<div class="question-box">🔄 ' + esc(ex.title || 'Review') + '</div>' +
    '<p style="margin-bottom:18px;color:var(--muted)">Quick review of ' + items.length + ' items:</p>';
        for (var i = 0; i < items.length; i++) {
            var item = items[i];
            if (item.type === 'translation') body += '<div style="background:var(--green-light);border-radius:14px;padding:12px 16px;margin-bottom:10px;font-weight:700">' + esc(item.q || item.question || '') + ' → <span style="color:var(--green-dark)">' + esc(item.a || item.answer || '') + '</span></div>';
            else if (item.type === 'multiple_choice') body += '<div style="background:var(--blue-light);border-radius:14px;padding:12px 16px;margin-bottom:10px;font-weight:700">' + esc(item.q || item.question || '') + ' → <span style="color:var(--blue-dark)">' + esc(item.a || item.answer || '') + '</span></div>';
            else if (item.type === 'fill_blank') body += '<div style="background:var(--yellow-light);border-radius:14px;padding:12px 16px;margin-bottom:10px;font-weight:700">' + esc(item.text || item.text_with_blank || '') + ' → <span style="color:var(--yellow-dark)">' + esc(item.a || item.correct_answer || '') + '</span></div>';
        }
        body += '<div class="action-row"><button id="next-btn" class="next-btn show" onclick="doNext()">Continue →</button></div>';
        earnedXP += ex.xp;
        updateProgress();
    } else {
        body += '<div class="question-box">' + esc(ex.question || ex.title || ex.prompt || 'Exercise') + '</div>' +
    '<div class="action-row"><button id="next-btn" class="next-btn show" onclick="doNext()">Continue →</button><button class="skip-btn" onclick="doSkip()">Skip</button></div>';
    }

    body += '</div>';
    main.innerHTML = body;

    // Wire check-trans
    var checkTransBtn = document.getElementById('check-trans');
    if (checkTransBtn) {
        checkTransBtn.addEventListener('click', function() {
            var inp = document.getElementById('main-input');
            if (!inp || inp.disabled) return;
            var answer = inp.dataset.answer;
            var val = inp.value.trim();
            var ex = queue[idx];

            console.log('🔍 Translation Check:', { userAnswer: val, correctAnswer: answer, exerciseId: ex ? ex.id : null });

            if (!val) {
                shake();
                return;
            }
            var ok = fuzzyMatch(val, answer);
            inp.disabled = true;
            inp.classList.add(ok ? 'correct' : 'wrong');
            showFeedback(ok, ok ? '✓ Correct!' : '✗ Correct answer: ' + answer);

            if (ok && ex) {
                correctCount++;
                earnedXP += ex.xp;
                updateProgress();

                // SAVE TO DATABASE
                console.log('💾 Saving translation exercise:', ex.id);
                saveProgressToDatabase(ex.id, true, ex.xp, 5);
            }

            revealNext();
        });
    }

    // Wire hear + mic
    var hearBtn = document.getElementById('hear-btn');
    if (hearBtn) {
        hearBtn.addEventListener('click', function() {
            speak(this.dataset.tts, this);
        });
    }
    
    var micBtn = document.getElementById('mic-btn');
    if (micBtn) {
        micBtn.addEventListener('click', function() {
            startSpeechRecognition(this.dataset.prompt, this.dataset.lang);
        });
    }

    // Wire tts button
    var ttsBtnEl = document.getElementById('tts-btn');
    if (ttsBtnEl) {
        ttsBtnEl.addEventListener('click', function() {
            speak(this.dataset.tts, this);
        });
    }

    // Wire lac-grid
    var lacGrid = document.getElementById('lac-grid');
    if (lacGrid) {
        var lacAnswer = lacGrid.dataset.answer;
        var lacBtns = lacGrid.querySelectorAll('.mcq-btn');
        for (var i = 0; i < lacBtns.length; i++) {
            (function(btn) {
                btn.addEventListener('click', function() {
                    var ex = queue[idx];
                    var ok = btn.dataset.opt.toLowerCase() === lacAnswer.toLowerCase();
                    checkMCQ(btn, btn.dataset.opt, lacAnswer);
                    if (ok && ex) {
                        console.log('💾 Saving listen & choose exercise:', ex.id);
                        saveProgressToDatabase(ex.id, true, ex.xp, 5);
                    }
                });
            })(lacBtns[i]);
        }
    }

    // Wire word bank
    var WB_TYPES = ['word_bank', 'sentence_scramble', 'tap_hear', 'conversation', 'error_correction'];
    if (WB_TYPES.includes(ex.type)) {
        var wbCorrect = ex.correct || ex.correct_answer || ex.answer || ex.correct_sentence || [];
        var wbExplain = ex.type === 'error_correction' ? (ex.explanation || '') : null;
        
        // For error_correction, use word_bank_for_fix or word_bank
        var wordBank = ex.word_bank_for_fix || ex.word_bank || ex.words || [];
        
        setupWordBank(wordBank, wbCorrect, 'wb');
        var checkWbBtn = document.getElementById('check-wb');
        if (checkWbBtn) {
            checkWbBtn.addEventListener('click', function() {
                if (wbExplain !== null) {
                    // For error_correction, we need to pass the correct sentence array
                    var correctArray = Array.isArray(wbCorrect) ? wbCorrect : 
                                        (typeof wbCorrect === 'string' ? wbCorrect.split(' ') : []);
                    checkErrorCorrectionFn(correctArray, wbExplain);
                } else {
                    checkWordBank(wbCorrect);
                }
            });
        }
    }

    if (ex.type === 'character_writing') setupWriteCanvas();
    if (ex.type === 'speed_matching' && ex.time) startMatchTimer(ex.time || ex.time_limit_seconds);
    if (ex.type === 'matching' || ex.type === 'speed_matching') setupMatching();

    var inp2 = document.getElementById('main-input');
    if (inp2) setTimeout(function() { inp2.focus(); }, 100);
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ═══════════════════════════════════════════
// CHECK FUNCTIONS
// ═══════════════════════════════════════════
function checkMCQ(btn, chosen, correct) {
    var grid = btn.closest('.mcq-grid') || btn.closest('.tf-grid');
    if (!grid || grid.dataset.answered) return;
    grid.dataset.answered = '1';

    var ok = chosen.toLowerCase() === correct.toLowerCase();
    var ex = queue[idx];

    console.log('🔍 MCQ Check:', { chosen: chosen, correct: correct, ok: ok, exerciseId: ex ? ex.id : null });

    var allBtns = grid.querySelectorAll('.mcq-btn,.tf-btn');
    for (var i = 0; i < allBtns.length; i++) {
        allBtns[i].style.pointerEvents = 'none';
    }
    btn.classList.add(ok ? 'correct' : 'wrong');
    if (!ok) {
        var optBtns = grid.querySelectorAll('[data-opt]');
        for (var i = 0; i < optBtns.length; i++) {
            if (optBtns[i].dataset.opt && optBtns[i].dataset.opt.toLowerCase() === correct.toLowerCase()) {
                optBtns[i].classList.add('correct');
            }
        }
    }
    showFeedback(ok, ok ? '✓ Correct!' : '✗ Correct answer: ' + correct);

    if (ok && ex) {
        correctCount++;
        earnedXP += ex.xp;
        updateProgress();

        // SAVE TO DATABASE
        console.log('💾 Saving MCQ exercise:', ex.id);
        saveProgressToDatabase(ex.id, true, ex.xp, 5);
    } else {
        console.log('❌ Wrong answer, not saving');
    }

    revealNext();
}

function checkMCQWithExplain(btn, chosen, correct, explanation) {
    var grid = btn.closest('.mcq-grid');
    if (!grid || grid.dataset.answered) return;
    grid.dataset.answered = '1';
    var ok = chosen.toLowerCase() === correct.toLowerCase();
    var ex = queue[idx];

    var allBtns = grid.querySelectorAll('.mcq-btn');
    for (var i = 0; i < allBtns.length; i++) {
        allBtns[i].style.pointerEvents = 'none';
    }
    btn.classList.add(ok ? 'correct' : 'wrong');
    if (!ok) {
        for (var i = 0; i < allBtns.length; i++) {
            if ((allBtns[i].dataset.opt || '').toLowerCase() === correct.toLowerCase()) {
                allBtns[i].classList.add('correct');
            }
        }
    }
    var fb = document.getElementById('fb');
    if (fb) {
        fb.className = 'feedback show ' + (ok ? 'correct' : 'wrong');
        fb.textContent = (ok ? '✓ Correct! ' : '✗ Wrong. ') + explanation;
    }

    if (ok && ex) {
        correctCount++;
        earnedXP += ex.xp;
        updateProgress();

        // SAVE TO DATABASE
        console.log('💾 Saving grammar exercise:', ex.id);
        saveProgressToDatabase(ex.id, true, ex.xp, 5);
    }

    revealNext();
}

// ── MATCHING ──
function setupMatching() {
    matchLeft = null;
    var matchItems = document.querySelectorAll('.match-item');
    for (var i = 0; i < matchItems.length; i++) {
        (function(el) {
            el.addEventListener('click', function() {
                if (el.classList.contains('matched')) return;
                if (el.dataset.side === 'L') {
                    var selected = document.querySelectorAll('.match-item.selected');
                    for (var j = 0; j < selected.length; j++) {
                        selected[j].classList.remove('selected');
                    }
                    el.classList.add('selected');
                    matchLeft = el;
                } else if (matchLeft) {
                    var ok = matchLeft.dataset.pair === el.dataset.val;
                    if (ok) {
                        matchLeft.classList.add('matched');
                        el.classList.add('matched');
                        matchLeft.classList.remove('selected');
                        var wrap = document.getElementById('match-wrap');
                        if (wrap) {
                            var done = parseInt(wrap.dataset.matched) + 1;
                            wrap.dataset.matched = done;
                            var sc = document.getElementById('match-score');
                            if (sc) sc.textContent = done + ' / ' + wrap.dataset.total + ' matched';
                            if (done >= parseInt(wrap.dataset.total)) {
                                if (timerInt) {
                                    clearInterval(timerInt);
                                    timerInt = null;
                                }
                                showFeedback(true, '✓ All matched!');

                                var ex = queue[idx];
                                correctCount++;
                                earnedXP += ex.xp;
                                updateProgress();

                                // SAVE TO DATABASE
                                console.log('💾 Saving matching exercise:', ex ? ex.id : null);
                                if (ex) saveProgressToDatabase(ex.id, true, ex.xp, 15);

                                revealNext();
                            }
                        }
                    } else {
                        matchLeft.classList.add('flash-wrong');
                        el.classList.add('flash-wrong');
                        setTimeout(function() {
                            matchLeft.classList.remove('flash-wrong', 'selected');
                            el.classList.remove('flash-wrong');
                        }, 400);
                    }
                    matchLeft = null;
                }
            });
        })(matchItems[i]);
    }
}

function startMatchTimer(secs) {
    var left = secs;
    var el = document.getElementById('match-timer');
    timerInt = setInterval(function() {
        left--;
        if (el) {
            el.textContent = '⏱ ' + left + 's';
            el.className = 'timer-pill' + (left <= 10 ? ' warning' : '') + (left <= 5 ? ' danger' : '');
        }
        if (left <= 0) {
            clearInterval(timerInt);
            timerInt = null;
            showFeedback(false, "⏰ Time's up!");
            revealNext();
        }
    }, 1000);
}

// ── SPEECH RECOGNITION ──
function startSpeechRecognition(prompt, lang) {
    var btn = document.getElementById('mic-btn');
    if (!btn) return;
    if (!SpeechRecognition) {
        showFeedback(false, '🎤 Speech recognition not supported. Try Chrome.');
        revealNext();
        return;
    }
    if (btn.classList.contains('listening')) {
        if (recognizer) recognizer.stop();
        return;
    }
    recognizer = new SpeechRecognition();
    recognizer.lang = lang || 'rw';
    recognizer.interimResults = false;
    recognizer.maxAlternatives = 5;
    recognizer.continuous = false;
    btn.classList.add('listening');
    btn.textContent = '🎤 Listening… (tap to stop)';
    recognizer.start();
    recognizer.onresult = function(event) {
        btn.classList.remove('listening');
        btn.textContent = '🎤 Tap to Speak';
        var alternatives = [];
        for (var i = 0; i < event.results[0].length; i++) {
            alternatives.push(event.results[0][i].transcript.trim().toLowerCase());
        }
        var target = prompt.trim().toLowerCase();
        var matched = false;
        for (var i = 0; i < alternatives.length; i++) {
            var alt = alternatives[i];
            if (alt === target) {
                matched = true;
                break;
            }
            if (target.includes(' ') && alt === target.split(' ')[0].toLowerCase()) {
                matched = true;
                break;
            }
            if (lev(alt, target) <= Math.min(3, Math.floor(target.length * 0.3))) {
                matched = true;
                break;
            }
        }
        var best = alternatives[0] || '';
        var ex = queue[idx];

        if (matched) {
            showFeedback(true, '✓ Great! You said: "' + best + '"');
            correctCount++;
            earnedXP += ex.xp;
            updateProgress();

            // SAVE TO DATABASE
            console.log('💾 Saving speaking exercise:', ex.id);
            saveProgressToDatabase(ex.id, true, ex.xp, 10);
            revealNext();
        } else {
            showFeedback(false, '✗ Heard: "' + best + '" — expected: "' + prompt + '". Try again or skip.');
            btn.textContent = '🎤 Try Again';
            return;
        }
    };
    recognizer.onerror = function(event) {
        btn.classList.remove('listening');
        btn.textContent = '🎤 Tap to Speak';
        var errMsg = {
            'no-speech': '⚠️ No speech detected.',
            'not-allowed': '⚠️ Microphone access denied.',
            'network': '⚠️ Network error.',
            'aborted': '⚠️ Recognition stopped.'
        }[event.error] || ('⚠️ Error: ' + event.error);
        showFeedback(false, errMsg);
    };
    recognizer.onend = function() {
        btn.classList.remove('listening');
        if (btn.textContent === '🎤 Listening… (tap to stop)') btn.textContent = '🎤 Tap to Speak';
    };
}

// ── TIMED CHALLENGE ──
var tcQIdx = 0;
var tcScore = 0;

function startTimedChallenge() {
    var ex = queue[idx];
    tcQIdx = 0;
    tcScore = 0;
    var left = ex.time || ex.time_limit_seconds || 30;
    var timer = document.getElementById('tc-timer');
    timerInt = setInterval(function() {
        left--;
        if (timer) {
            timer.textContent = '⏱ ' + left + 's';
            timer.className = 'timer-pill' + (left <= 10 ? ' warning' : '') + (left <= 5 ? ' danger' : '');
        }
        if (left <= 0) {
            clearInterval(timerInt);
            timerInt = null;
            endTimedChallenge(ex);
        }
    }, 1000);
    showNextTCQuestion(ex);
}

function showNextTCQuestion(ex) {
    var cont = document.getElementById('tc-content');
    if (!cont) return;
    if (tcQIdx >= (ex.questions || []).length) {
        endTimedChallenge(ex);
        return;
    }
    var q = ex.questions[tcQIdx];
    cont.innerHTML = '<div style="text-align:center;margin-bottom:14px"><div style="font-family:\'Fredoka One\',cursive;font-size:1.8rem;margin-bottom:14px">' + (q.q || q.question) + '</div><input id="tc-input" class="text-input" style="max-width:320px;margin:0 auto 14px;display:block" placeholder="Type: ' + (q.a || q.answer || '').charAt(0) + '…" autocomplete="off"><button class="check-btn" style="width:auto;padding:14px 28px" onclick="checkTC()">Check</button></div>';
    setTimeout(function() {
        var i = document.getElementById('tc-input');
        if (i) i.focus();
    }, 50);
}

function checkTC() {
    var ex = queue[idx];
    var q = ex.questions[tcQIdx];
    var inp = document.getElementById('tc-input');
    if (!inp) return;
    var ok = fuzzyMatch(inp.value.trim(), q.a || q.answer || '');
    if (ok) {
        tcScore++;
        // Save each correct answer in timed challenge
        saveProgressToDatabase(q.id || ex.id, true, 5, 3);
    }

    tcQIdx++;
    showNextTCQuestion(ex);
}

function endTimedChallenge(ex) {
    if (timerInt) {
        clearInterval(timerInt);
        timerInt = null;
    }
    var cont = document.getElementById('tc-content');
    if (cont) cont.innerHTML = '<div style="text-align:center;font-family:\'Fredoka One\',cursive;font-size:1.5rem">Score: ' + tcScore + '/' + (ex.questions || []).length + '</div>';
    var ok = tcScore >= Math.ceil((ex.questions || []).length / 2);
    showFeedback(ok, ok ? '✓ Great! ' + tcScore + '/' + (ex.questions || []).length + ' correct' : 'You got ' + tcScore + '/' + (ex.questions || []).length);

    if (ok) {
        correctCount++;
        earnedXP += ex.xp;
        updateProgress();

        // SAVE TO DATABASE for completing the challenge
        console.log('💾 Saving timed challenge:', ex.id);
        saveProgressToDatabase(ex.id, true, ex.xp, 30);
    }
    revealNext();
}

// ── CANVAS WRITING ──
function setupWriteCanvas() {
    var cv = document.getElementById('write-canvas');
    if (!cv) return;
    var ctx = cv.getContext('2d');
    ctx.lineWidth = 4;
    ctx.lineCap = 'round';
    ctx.strokeStyle = '#2b2b2b';
    var drawing = false;

    function getPos(e) {
        var r = cv.getBoundingClientRect();
        var t = e.touches ? e.touches[0] : e;
        return {
            x: (t.clientX - r.left) * (cv.width / r.width),
            y: (t.clientY - r.top) * (cv.height / r.height)
        };
    }
    cv.addEventListener('mousedown', function(e) {
        drawing = true;
        var p = getPos(e);
        ctx.beginPath();
        ctx.moveTo(p.x, p.y);
    });
    cv.addEventListener('mousemove', function(e) {
        if (!drawing) return;
        var p = getPos(e);
        ctx.lineTo(p.x, p.y);
        ctx.stroke();
        ctx.beginPath();
        ctx.moveTo(p.x, p.y);
    });
    cv.addEventListener('mouseup', function() {
        drawing = false;
        ctx.beginPath();
    });
    cv.addEventListener('touchstart', function(e) {
        e.preventDefault();
        drawing = true;
        var p = getPos(e);
        ctx.beginPath();
        ctx.moveTo(p.x, p.y);
    }, { passive: false });
    cv.addEventListener('touchmove', function(e) {
        e.preventDefault();
        if (!drawing) return;
        var p = getPos(e);
        ctx.lineTo(p.x, p.y);
        ctx.stroke();
        ctx.beginPath();
        ctx.moveTo(p.x, p.y);
    }, { passive: false });
    cv.addEventListener('touchend', function() {
        drawing = false;
        ctx.beginPath();
    });
}

function clearWriteCanvas() {
    var cv = document.getElementById('write-canvas');
    if (cv) cv.getContext('2d').clearRect(0, 0, cv.width, cv.height);
}

function checkWriting() {
    var cv = document.getElementById('write-canvas');
    if (!cv) return;
    var data = cv.getContext('2d').getImageData(0, 0, cv.width, cv.height).data;
    var hasContent = false;
    for (var i = 0; i < data.length; i += 4) {
        if (data[i + 3] > 10) {
            hasContent = true;
            break;
        }
    }
    var ex = queue[idx];

    if (!hasContent) {
        shake();
        showFeedback(false, 'Please write something first!');
        return;
    }

    showFeedback(true, '✓ Great practice!');
    correctCount++;
    earnedXP += ex.xp;
    updateProgress();

    // SAVE TO DATABASE
    console.log('💾 Saving writing exercise:', ex.id);
    saveProgressToDatabase(ex.id, true, ex.xp, 15);

    revealNext();
}

// ── ENTER KEY ──
document.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        var inp = document.getElementById('main-input');
        if (inp && !inp.disabled) {
            e.preventDefault();
            var ex = queue[idx];
            var ok = fuzzyMatch(inp.value.trim(), ex.answer || '');
            inp.disabled = true;
            inp.classList.add(ok ? 'correct' : 'wrong');
            showFeedback(ok, ok ? '✓ Correct!' : '✗ Correct answer: ' + (ex.answer || ''));

            if (ok && ex) {
                correctCount++;
                earnedXP += ex.xp;
                updateProgress();

                // SAVE TO DATABASE
                console.log('💾 Saving from Enter key:', ex.id);
                saveProgressToDatabase(ex.id, true, ex.xp, 5);
            }
            revealNext();
        }
        var tcInp = document.getElementById('tc-input');
        if (tcInp) {
            e.preventDefault();
            checkTC();
        }
    }
});

// ═══════════════════════════════════════════
// COMPLETION
// ═══════════════════════════════════════════
function showCompletion() {
    var main = document.getElementById('lesson-main');
    var pct = Math.round((correctCount / queue.length) * 100);
    main.innerHTML = '<div class="completion">' +
    '<div style="font-size:3rem;margin-bottom:8px">🎉</div>' +
    '<div class="comp-title">Lesson Complete!</div>' +
    '<div class="comp-sub">You finished all ' + queue.length + ' exercises</div>' +
    '<div class="comp-stats">' +
      '<div class="comp-stat"><div class="comp-stat-n">' + correctCount + '</div><div class="comp-stat-l">Correct</div></div>' +
      '<div class="comp-stat"><div class="comp-stat-n">' + earnedXP + '</div><div class="comp-stat-l">XP Earned</div></div>' +
      '<div class="comp-stat"><div class="comp-stat-n">' + pct + '%</div><div class="comp-stat-l">Accuracy</div></div>' +
    '</div>' +
    '<div class="comp-btns">' +
      '<button class="comp-btn yellow" onclick="startLesson()">🔄 Play Again</button>' +
      '<button class="comp-btn green" onclick="backToOverview()">← Back</button>' +
    '</div>' +
  '</div>';
    document.getElementById('progress-fill').style.width = '100%';
    document.getElementById('lesson-prog').style.width = '100%';

    // Save progress to server
    fetch('save-progress.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            level: currentLevel,
            topic: currentTopic,
            xp_earned: earnedXP,
            correct: correctCount,
            total: queue.length,
            accuracy: pct,
            exercises_completed: true
        })
    })['catch'](function() {});
}

function backToOverview() {
    document.getElementById('lesson').style.display = 'none';
    document.getElementById('overview').style.display = 'block';
    
    // Refresh the overview to show updated completed count
    initOverview();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function confirmClose() {
    document.getElementById('close-modal').style.display = 'flex';
}

function closeLesson() {
    document.getElementById('close-modal').style.display = 'none';
    if (timerInt) {
        clearInterval(timerInt);
        timerInt = null;
    }
    if (window.speechSynthesis) window.speechSynthesis.cancel();
    backToOverview();
}

// ═══════════════════════════════════════════
// BOOT
// ═══════════════════════════════════════════
initOverview();
</script>
</body>
</html>