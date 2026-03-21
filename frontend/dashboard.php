<?php
session_start();
require_once '../backend/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$active_lang = $_GET['lang'] ?? $_SESSION['active_language'] ?? 'rw';
$active_level = isset($_GET['level']) ? (int)$_GET['level'] : ($_SESSION['active_level'] ?? 1);

$_SESSION['active_language'] = $active_lang;
$_SESSION['active_level'] = $active_level;

$lang_names = ['en' => 'English', 'rw' => 'Kinyarwanda', 'fr' => 'French', 'sw' => 'Swahili', 'es' => 'Spanish', 'de' => 'German'];
$current_lang_name = $lang_names[$active_lang] ?? 'Kinyarwanda';

$levels = [
    1 => ['name' => 'Beginner', 'tag' => 'A1', 'icon' => '🌱'],
    2 => ['name' => 'Elementary', 'tag' => 'A2', 'icon' => '🌿'],
    3 => ['name' => 'Intermediate', 'tag' => 'B1', 'icon' => '🌳'],
    4 => ['name' => 'Upper Intermediate', 'tag' => 'B2', 'icon' => '⚡'],
    5 => ['name' => 'Advanced', 'tag' => 'C1', 'icon' => '🔥'],
    6 => ['name' => 'Expert', 'tag' => 'C2', 'icon' => '🏆']
];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$stmt = $pdo->prepare("
    SELECT * FROM user_languages 
    WHERE user_id = ? 
    ORDER BY CASE language_code WHEN ? THEN 0 ELSE 1 END, last_activity DESC
");
$stmt->execute([$user_id, $active_lang]);
$user_languages = $stmt->fetchAll();

if (empty($user_languages)) {
    $stmt = $pdo->prepare("INSERT INTO user_languages (user_id, language_code, language_name) VALUES (?, 'rw', 'Kinyarwanda')");
    $stmt->execute([$user_id]);
    $active_lang = 'rw';
    $_SESSION['active_language'] = 'rw';
}

$stmt = $pdo->prepare("
    SELECT 
        COALESCE(SUM(CASE WHEN DATE(completed_at) = CURDATE() THEN xp_earned END), 0) as xp_today,
        COALESCE(AVG(accuracy), 0) as avg_accuracy,
        COUNT(DISTINCT CONCAT(level_number, '/', topic_file)) as lessons_completed,
        COALESCE(SUM(xp_earned), 0) as total_xp,
        COUNT(DISTINCT id) as total_exercises,
        COUNT(DISTINCT CONCAT(level_number, '/', topic_file, '/', exercise_id)) as words_learned
    FROM user_progress 
    WHERE user_id = ? AND language_code = ? AND level_number = ? AND completed = 1
");
$stmt->execute([$user_id, $active_lang, $active_level]);
$stats = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM user_streaks WHERE user_id = ?");
$stmt->execute([$user_id]);
$streak = $stmt->fetch() ?: ['current_streak' => 0, 'longest_streak' => 0];

$stmt = $pdo->prepare("
    SELECT DATE(completed_at) as date, SUM(xp_earned) as xp
    FROM user_progress
    WHERE user_id = ? AND language_code = ? AND level_number = ? AND completed_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(completed_at)
    ORDER BY date
");
$stmt->execute([$user_id, $active_lang, $active_level]);
$xp_week = $stmt->fetchAll();

$xp_data = array_fill(0, 7, 0);
$days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
foreach ($xp_week as $row) {
    $day_index = date('N', strtotime($row['date'])) - 1;
    $xp_data[$day_index] = (int)$row['xp'];
}

$lang_folder_map = [
    'en' => 'EN-TO-RW',
    'rw' => 'RW-TO-EN',
    'fr' => 'FR-TO-EN',
    'sw' => 'SW-TO-EN',
    'es' => 'ES-TO-EN',
    'de' => 'DE-TO-EN'
];

$content_dir = "../content/level$active_level/";
$mapped_folder = $lang_folder_map[$active_lang] ?? null;
if ($mapped_folder && is_dir("../content/{$mapped_folder}/level$active_level/")) {
    $content_dir = "../content/{$mapped_folder}/level$active_level/";
} elseif (is_dir("../content/{$active_lang}/level$active_level/")) {
    $content_dir = "../content/{$active_lang}/level$active_level/";
}

$topics = [];
if (is_dir($content_dir)) {
    $files = scandir($content_dir);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) == 'yaml') {
            $name = pathinfo($file, PATHINFO_FILENAME);
            $yaml_content = file_get_contents($content_dir . $file);
            if (function_exists('yaml_parse')) {
                $data = yaml_parse($yaml_content);
                $display_name = $data['lesson']['name'] ?? ucwords(str_replace('-', ' ', $name));
                $total_exercises = count($data['exercises'] ?? []);
            } else {
                $display_name = ucwords(str_replace('-', ' ', $name));
                $total_exercises = substr_count($yaml_content, '- id:');
            }
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as completed FROM user_progress
                WHERE user_id = ? AND language_code = ? AND level_number = ? AND topic_file = ? AND completed = 1
            ");
            $stmt->execute([$user_id, $active_lang, $active_level, $file]);
            $completed = $stmt->fetchColumn();
            $pct = $total_exercises > 0 ? round(($completed / $total_exercises) * 100) : 0;
            $topics[] = [
                'name' => $display_name,
                'file' => $file,
                'pct' => $pct,
                'total' => $total_exercises,
                'completed' => $completed,
                'icon' => get_icon_for_topic($name)
            ];
        }
    }
}

$stmt = $pdo->prepare("
    SELECT up.level_number, up.topic_file, COUNT(up.id) as exercises_done,
           SUM(up.xp_earned) as xp_earned, MAX(up.completed_at) as last_attempt
    FROM user_progress up
    WHERE up.user_id = ? AND up.language_code = ? AND up.level_number = ? AND up.completed = 1
    GROUP BY up.level_number, up.topic_file
    ORDER BY last_attempt DESC LIMIT 3
");
$stmt->execute([$user_id, $active_lang, $active_level]);
$recent_lessons = $stmt->fetchAll();

foreach ($recent_lessons as &$lesson) {
    $mapped_folder = $lang_folder_map[$active_lang] ?? null;
    if ($mapped_folder && is_dir("../content/{$mapped_folder}/level{$lesson['level_number']}/")) {
        $yaml_file = "../content/{$mapped_folder}/level{$lesson['level_number']}/{$lesson['topic_file']}";
    } else {
        $yaml_file = "../content/level{$lesson['level_number']}/{$lesson['topic_file']}";
    }
    if (file_exists($yaml_file)) {
        $content = file_get_contents($yaml_file);
        if (function_exists('yaml_parse')) {
            $data = yaml_parse($content);
            $lesson['lesson_name'] = $data['lesson']['name'] ?? pathinfo($lesson['topic_file'], PATHINFO_FILENAME);
        } else {
            $lesson['lesson_name'] = pathinfo($lesson['topic_file'], PATHINFO_FILENAME);
        }
    } else {
        $lesson['lesson_name'] = pathinfo($lesson['topic_file'], PATHINFO_FILENAME);
    }
}

$stmt = $pdo->prepare("
    SELECT session_date, exercises_completed FROM user_sessions
    WHERE user_id = ? AND session_date >= DATE_SUB(CURDATE(), INTERVAL 91 DAY)
    ORDER BY session_date
");
$stmt->execute([$user_id]);
$activity = $stmt->fetchAll();

$activity_map = [];
foreach ($activity as $row) {
    $count = $row['exercises_completed'];
    $level_act = 0;
    if ($count > 20) $level_act = 3;
    elseif ($count > 10) $level_act = 2;
    elseif ($count > 0) $level_act = 1;
    $activity_map[$row['session_date']] = $level_act;
}

$stmt = $pdo->prepare("
    SELECT a.*, CASE WHEN ua.achievement_id IS NOT NULL THEN 1 ELSE 0 END as earned, ua.earned_at
    FROM achievements a
    LEFT JOIN user_achievements ua ON ua.achievement_id = a.id AND ua.user_id = ?
    ORDER BY a.criteria_value LIMIT 4
");
$stmt->execute([$user_id]);
$achievements = $stmt->fetchAll();

if (empty($achievements)) {
    $achievements = [
        ['id' => 1, 'name' => 'First Steps', 'earned' => ($stats['total_exercises'] ?? 0) >= 1 ? 1 : 0, 'icon' => '👣'],
        ['id' => 2, 'name' => 'Getting Started', 'earned' => ($stats['total_exercises'] ?? 0) >= 10 ? 1 : 0, 'icon' => '🌱'],
        ['id' => 3, 'name' => 'Week Warrior', 'earned' => ($streak['current_streak'] ?? 0) >= 7 ? 1 : 0, 'icon' => '🔥'],
        ['id' => 4, 'name' => 'Word Collector', 'earned' => ($stats['words_learned'] ?? 0) >= 5 ? 1 : 0, 'icon' => '📝']
    ];
}

function get_icon_for_topic($topic) {
    $icons = [
        'greetings' => '👋', 'numbers' => '🔢', 'family' => '👨‍👩‍👧',
        'colors' => '🎨', 'animals' => '🐕', 'food' => '🍔',
        'daily-routines' => '☀️', 'weather' => '☁️', 'clothing' => '👕',
        'house' => '🏠', 'travel' => '✈️', 'shopping' => '🛍️',
        'restaurant' => '🍽️', 'directions' => '🗺️', 'emotions' => '😊'
    ];
    return $icons[$topic] ?? '📚';
}

$current_level_num = floor(($stats['total_xp'] ?? 0) / 1000) + 1;
$next_level_xp = $current_level_num * 1000;
$xp_progress = ($stats['total_xp'] ?? 0) % 1000;
$xp_percent = round(($xp_progress / 1000) * 100);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Playmates · <?= $current_lang_name ?> Level <?= $active_level ?></title>
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons/css/flag-icons.min.css">

<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{
  --sand:      #f5ede6;
  --sand-mid:  #ede3d8;
  --sand-dark: #ddd0c2;
  --sage:      #c6dfd5;
  --sage-mid:  #8bbfad;
  --sage-dark: #3a7a68;
  --sage-deep: #225548;
  --lavender:  #d4cfed;
  --butter:    #f5e8b0;
  --peach:     #f5d6c4;
  --blush:     #f9d8cd;
  --ink:       #1a1a18;
  --ink-mid:   #4a4845;
  --muted:     #8a8178;
  --soft:      #b8b0a5;
  --surface:   #faf8f5;
  --border:    rgba(0,0,0,0.07);
  --radius-sm: 8px;
  --radius:    14px;
  --radius-lg: 20px;
  --sidebar-w: 228px;
  --sidebar-col: 64px;
  --pad:       16px;
  --transition: 0.25s cubic-bezier(.4,0,.2,1);
}

html,body{height:100%;font-family:'DM Sans',sans-serif;font-size:14px;color:var(--ink);background:var(--sand)}

/* ═══ LAYOUT ═══ */
.dash{display:grid;grid-template-columns:var(--sidebar-w) 1fr;height:100vh;min-height:600px;transition:grid-template-columns var(--transition)}
.dash.collapsed{grid-template-columns:var(--sidebar-col) 1fr}

/* ═══ SIDEBAR ═══ */
.sidebar{background:var(--sage-deep);display:flex;flex-direction:column;overflow-y:auto;overflow-x:hidden;position:relative;width:var(--sidebar-w);flex-shrink:0;transition:width var(--transition)}
.dash.collapsed .sidebar{width:var(--sidebar-col)}

.sidebar-toggle{position:absolute;top:22px;right:-13px;width:26px;height:26px;border-radius:50%;background:var(--sage-deep);border:2px solid rgba(255,255,255,0.2);color:rgba(255,255,255,0.7);font-size:11px;cursor:pointer;display:flex;align-items:center;justify-content:center;z-index:20;transition:transform var(--transition)}
.sidebar-toggle:hover{border-color:rgba(255,255,255,0.5)}
.dash.collapsed .sidebar-toggle{transform:rotate(180deg)}

.brand{padding:22px 18px 16px;border-bottom:1px solid rgba(255,255,255,0.08);overflow:hidden;white-space:nowrap;display:flex;flex-direction:column;transition:padding var(--transition)}
.dash.collapsed .brand{padding:16px 0;align-items:center}
.brand-name{font-family:'DM Serif Display',serif;font-size:20px;color:#fff;letter-spacing:-0.2px;display:flex;align-items:center;gap:7px}
.brand-name img{width:24px;height:24px;object-fit:contain;border-radius:5px;flex-shrink:0}
.brand-sub{font-size:10px;color:rgba(255,255,255,0.4);margin-top:3px;font-weight:500;letter-spacing:1px;text-transform:uppercase}
.brand-logo{display:none;width:34px;height:34px;background:rgba(255,255,255,0.08);border-radius:10px;align-items:center;justify-content:center;overflow:hidden}
.brand-logo img{width:22px;height:22px;object-fit:contain}
.dash.collapsed .brand-name,.dash.collapsed .brand-sub{display:none}
.dash.collapsed .brand-logo{display:flex}

.streak-pill{margin:12px 14px 0;background:rgba(255,255,255,0.08);border-radius:30px;padding:8px 13px;display:flex;align-items:center;gap:8px;overflow:hidden;white-space:nowrap;transition:all var(--transition)}
.dash.collapsed .streak-pill{margin:10px 0 0;padding:8px;border-radius:0;justify-content:center;background:transparent;border-bottom:1px solid rgba(255,255,255,0.08)}
.streak-num{font-family:'DM Serif Display',serif;font-size:20px;color:#f5e8b0;line-height:1}
.streak-lbl{font-size:11px;color:rgba(255,255,255,0.5);font-weight:500;line-height:1.3}
.dash.collapsed .streak-lbl,.dash.collapsed .streak-num{display:none}
.streak-icon-only{display:none;font-size:18px}
.dash.collapsed .streak-icon-only{display:block}

.nav{padding:14px 10px;flex:1;overflow:hidden}
.dash.collapsed .nav{padding:14px 6px}

.nav-section{font-size:9px;font-weight:500;text-transform:uppercase;letter-spacing:1.3px;color:rgba(255,255,255,0.28);padding:0 8px;margin:14px 0 5px;white-space:nowrap;overflow:hidden;transition:opacity var(--transition),height var(--transition),margin var(--transition)}
.dash.collapsed .nav-section{opacity:0;height:0;margin:0;padding:0}

.nav-item{display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:var(--radius-sm);cursor:pointer;margin-bottom:2px;border:none;background:transparent;width:100%;text-align:left;font-family:'DM Sans',sans-serif;transition:background 0.15s,padding var(--transition);position:relative;overflow:hidden;white-space:nowrap}
.dash.collapsed .nav-item{padding:9px;justify-content:center}
.nav-item:hover{background:rgba(255,255,255,0.08)}
.nav-item.active{background:rgba(255,255,255,0.13)}

.nav-icon{width:30px;height:30px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.nav-item.active .nav-icon{background:rgba(255,255,255,0.14)}

.nav-label{font-size:13px;font-weight:400;color:rgba(255,255,255,0.6);overflow:hidden}
.nav-item.active .nav-label{color:#fff;font-weight:500}
.dash.collapsed .nav-label{opacity:0;width:0;pointer-events:none}

.nav-badge{margin-left:auto;background:var(--peach);color:#7a3020;font-size:9px;font-weight:700;padding:2px 7px;border-radius:20px;line-height:1.5;flex-shrink:0}
.dash.collapsed .nav-badge{opacity:0;width:0;overflow:hidden;padding:0;margin:0}

.sidebar-footer{padding:16px;border-top:1px solid rgba(255,255,255,0.08);overflow:hidden;transition:opacity var(--transition),height var(--transition),padding var(--transition)}
.dash.collapsed .sidebar-footer{opacity:0;height:0;padding:0}
.xp-label{font-size:10px;color:rgba(255,255,255,0.45);margin-bottom:6px;font-weight:500;display:flex;justify-content:space-between}
.xp-bar{height:5px;background:rgba(255,255,255,0.1);border-radius:10px;overflow:hidden;margin-bottom:8px}
.xp-fill{height:100%;background:linear-gradient(90deg,var(--sage),var(--butter));border-radius:10px}
.level-txt{font-family:'DM Serif Display',serif;font-size:12px;font-style:italic;color:rgba(255,255,255,0.5)}

/* ═══ MAIN ═══ */
.main{padding:22px;overflow-y:auto;background:var(--sand)}
.page{display:none}.page.active{display:block}

.topbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;flex-wrap:wrap;gap:10px}
.page-title{font-family:'DM Serif Display',serif;font-size:24px;color:var(--ink);letter-spacing:-0.3px}
.topbar-right{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.date-pill{font-size:11px;color:var(--muted);background:var(--surface);border:1px solid var(--border);border-radius:30px;padding:5px 13px;font-weight:500}

.lang-selector-group{display:flex;gap:3px;align-items:center;background:var(--surface);border:1px solid var(--border);border-radius:30px;padding:3px}
.lang-pill{padding:4px 11px;border-radius:30px;border:none;background:transparent;font-weight:500;font-size:11px;font-family:'DM Sans',sans-serif;color:var(--muted);cursor:pointer;transition:all 0.15s;display:flex;align-items:center;gap:5px}
.lang-pill:hover{background:var(--sand-mid)}
.lang-pill.active{background:var(--sage-dark);color:#fff}
.lang-pill.add-lang{color:var(--sage-dark);font-weight:700}
.lang-pill.add-lang:hover{background:var(--sage)}

.level-pills{display:flex;gap:3px;flex-wrap:wrap}
.level-pill{padding:4px 10px;border-radius:30px;background:var(--surface);border:1px solid var(--border);font-weight:500;font-size:11px;color:var(--ink);text-decoration:none;transition:all 0.15s}
.level-pill:hover{background:var(--sage)}
.level-pill.active{background:var(--sage-dark);color:#fff;border-color:var(--sage-dark)}

.start-btn{font-size:12px;font-weight:500;background:var(--ink);color:#fff;border:none;border-radius:30px;padding:7px 16px;cursor:pointer;font-family:'DM Sans',sans-serif;transition:opacity 0.15s;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
.start-btn:hover{opacity:0.85}

/* ═══ METRICS ═══ */
.metrics{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:16px}
.metric{background:var(--surface);border-radius:var(--radius);padding:16px;border:1px solid var(--border)}
.metric-lbl{font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:6px}
.metric-val{font-family:'DM Serif Display',serif;font-size:28px;line-height:1;letter-spacing:-0.5px}
.metric-sub{font-size:10px;margin-top:5px;font-weight:400}

.m-green .metric-lbl,.m-green .metric-sub{color:var(--sage-dark)}.m-green .metric-val{color:var(--sage-dark)}
.m-amber .metric-lbl,.m-amber .metric-sub{color:#7a5c1e}.m-amber .metric-val{color:#a07820}
.m-coral .metric-lbl,.m-coral .metric-sub{color:#7a3020}.m-coral .metric-val{color:#b04428}
.m-purple .metric-lbl,.m-purple .metric-sub{color:#4a3e9a}.m-purple .metric-val{color:#6860c0}

/* ═══ CARDS ═══ */
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px}
.card{background:var(--surface);border-radius:var(--radius);border:1px solid var(--border);padding:var(--pad)}
.card-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
.card-title{font-size:11px;font-weight:500;color:var(--ink);text-transform:uppercase;letter-spacing:0.8px}
.card-action{font-size:11px;font-weight:500;color:var(--sage-dark);cursor:pointer;background:var(--sage);padding:3px 10px;border-radius:20px;border:none;font-family:'DM Sans',sans-serif;transition:opacity 0.15s}
.card-action:hover{opacity:0.75}
.chart-wrap{position:relative;height:130px;width:100%}

.lessons-list{display:flex;flex-direction:column;gap:9px}
.lesson-row{display:flex;align-items:center;gap:9px}
.lesson-dot{width:7px;height:7px;border-radius:50%;flex-shrink:0}
.lesson-name{font-size:12px;font-weight:500;flex:1;color:var(--ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.lesson-bar-wrap{flex:2;height:5px;background:var(--sand-dark);border-radius:10px;overflow:hidden}
.lesson-bar{height:100%;border-radius:10px}
.lesson-pct{font-size:10px;font-weight:500;color:var(--muted);min-width:28px;text-align:right}

.heatmap{display:grid;grid-template-columns:repeat(13,1fr);gap:3px}
.hm-cell{aspect-ratio:1;border-radius:2px}

.achievements{display:grid;grid-template-columns:repeat(4,1fr);gap:10px}
.badge-item{display:flex;flex-direction:column;align-items:center;gap:5px}
.badge-icon{width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:18px}
.badge-lbl{font-size:9px;font-weight:500;text-align:center;color:var(--muted);line-height:1.3}
.badge-item.earned .badge-lbl{color:var(--ink)}
.badge-item.locked .badge-icon{filter:grayscale(1);opacity:0.3}

.bottom-grid{display:grid;grid-template-columns:2fr 1fr;gap:12px}
.recent-list{display:flex;flex-direction:column;gap:8px}
.recent-row{display:flex;align-items:center;gap:9px;padding:9px 11px;background:var(--sand);border-radius:var(--radius-sm)}
.recent-icon{width:32px;height:32px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0}
.recent-info{flex:1}
.recent-name{font-size:12px;font-weight:500;color:var(--ink)}
.recent-meta{font-size:10px;color:var(--muted);margin-top:1px}
.recent-xp{font-family:'DM Serif Display',serif;font-size:15px;color:var(--sage-dark)}

/* ═══ LESSONS PAGE ═══ */
.lessons-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:16px}
.lesson-card{background:var(--surface);border-radius:var(--radius);border:1px solid var(--border);padding:16px;cursor:pointer;transition:transform 0.15s}
.lesson-card:hover{transform:translateY(-3px)}
.lesson-card-icon{font-size:28px;margin-bottom:9px;line-height:1}
.lesson-card-name{font-family:'DM Serif Display',serif;font-size:16px;color:var(--ink);margin-bottom:3px}
.lesson-card-meta{font-size:10px;color:var(--muted);font-weight:400;margin-bottom:9px}
.lesson-card-bar{height:5px;background:var(--sand-dark);border-radius:10px;overflow:hidden;margin-bottom:8px}
.lesson-card-fill{height:100%;border-radius:10px}
.lesson-card-foot{display:flex;align-items:center;justify-content:space-between}
.lesson-card-pct{font-size:11px;font-weight:500;color:var(--sage-dark)}
.lesson-card-xp{font-size:10px;color:var(--muted)}
.lesson-badge{display:inline-block;font-size:9px;font-weight:500;padding:2px 8px;border-radius:20px;margin-bottom:8px}

/* ═══ PROFILE ═══ */
.profile-header{display:flex;align-items:center;gap:18px;background:var(--surface);border-radius:var(--radius);border:1px solid var(--border);padding:20px;margin-bottom:14px}
.profile-avatar{width:64px;height:64px;border-radius:50%;background:var(--sage);display:flex;align-items:center;justify-content:center;font-family:'DM Serif Display',serif;font-size:24px;color:var(--sage-deep);flex-shrink:0;border:2px solid var(--sage-mid)}
.profile-name{font-family:'DM Serif Display',serif;font-size:22px;color:var(--ink);letter-spacing:-0.3px}
.profile-handle{font-size:12px;color:var(--muted);margin-bottom:8px}
.profile-tags{display:flex;gap:6px;flex-wrap:wrap}
.profile-tag{font-size:10px;font-weight:500;padding:3px 9px;border-radius:20px}
.profile-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:14px}
.profile-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.stat-box{background:var(--surface);border-radius:var(--radius);border:1px solid var(--border);padding:14px;text-align:center}
.stat-box-val{font-family:'DM Serif Display',serif;font-size:26px;color:var(--sage-dark)}
.stat-box-lbl{font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:0.8px;color:var(--muted);margin-top:4px}
.lang-row{display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--border)}
.lang-row:last-child{border-bottom:none}
.lang-flag{font-size:20px;line-height:1}
.lang-info{flex:1}
.lang-name{font-size:13px;font-weight:500;color:var(--ink)}
.lang-level{font-size:10px;color:var(--muted);margin-top:2px}
.lang-xp{font-family:'DM Serif Display',serif;font-size:14px;color:var(--sage-dark)}

/* ═══ MODAL ═══ */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(26,26,24,0.45);z-index:999;align-items:center;justify-content:center}
.modal-overlay.open{display:flex}
.modal-box{background:var(--surface);border-radius:var(--radius-lg);padding:26px;width:430px;max-width:92vw;border:1px solid var(--border);animation:modalIn 0.2s ease}
@keyframes modalIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.modal-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:4px}
.modal-title{font-family:'DM Serif Display',serif;font-size:20px;color:var(--ink)}
.modal-close{background:var(--sand-mid);border:none;border-radius:50%;width:30px;height:30px;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;color:var(--muted)}
.modal-sub{font-size:10px;color:var(--muted);font-weight:500;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:16px}
.lang-cards-grid{display:grid;grid-template-columns:1fr 1fr;gap:9px}
.lang-pick-card{display:flex;flex-direction:column;align-items:flex-start;gap:3px;padding:13px 15px;border-radius:var(--radius);cursor:pointer;background:var(--sand);border:1px solid var(--border);font-family:'DM Sans',sans-serif;text-align:left;transition:all 0.15s}
.lang-pick-card:hover:not(.is-added){border-color:var(--sage-dark);background:var(--sage)}
.lang-pick-card.is-added{opacity:0.45;cursor:default;background:var(--sand-mid)}
.lang-pick-flag{font-size:20px;line-height:1;margin-bottom:3px}
.lang-pick-name{font-size:13px;font-weight:500;color:var(--ink)}
.lang-pick-desc{font-size:10px;color:var(--muted)}
.lang-pick-added{font-size:10px;color:var(--sage-dark);font-weight:600}
.modal-note{font-size:10px;color:var(--muted);text-align:center;margin-top:14px;line-height:1.6}

/* ═══════════════════════════════════════════
   MOBILE — slim topbar + slide-in sidebar
   ═══════════════════════════════════════════ */

/* Slim single-row topbar */
.mob-topbar{
  display:none;
  position:fixed;top:0;left:0;right:0;z-index:90;
  background:var(--sage-deep);
  align-items:center;
  justify-content:space-between;
  padding:0 14px;
  height:52px;
  box-shadow:0 1px 0 rgba(255,255,255,0.05),0 2px 10px rgba(0,0,0,0.15);
}
.mob-flag-trigger{
  display:flex;align-items:center;justify-content:center;
  width:38px;height:38px;border-radius:50%;
  background:none;
  cursor:pointer;font-size:20px;flex-shrink:0;
}
.mob-flag-trigger .fi{font-size:20px}

.mob-topbar-brand{
  font-family:'DM Serif Display',serif;
  font-size:17px;color:#fff;letter-spacing:-0.2px;
  display:flex;align-items:center;gap:7px;
  flex:1;justify-content:center;
}
.mob-topbar-brand img{width:22px;height:22px;object-fit:contain;border-radius:4px}

.mob-topbar-right{display:flex;align-items:center;gap:8px;flex-shrink:0}

.mob-hamburger{
  display:flex;flex-direction:column;justify-content:center;
  gap:5px;cursor:pointer;padding:7px;flex-shrink:0;
  background:rgba(255,255,255,0.08);border:1.5px solid rgba(255,255,255,0.15);
  border-radius:9px;width:38px;height:38px;align-items:center;
  transition:background 0.15s;
}
.mob-hamburger:hover{background:rgba(255,255,255,0.16)}
.mob-hamburger span{display:block;height:1.5px;background:rgba(255,255,255,0.85);border-radius:2px}
.mob-hamburger span:nth-child(1){width:16px}
.mob-hamburger span:nth-child(2){width:12px}
.mob-hamburger span:nth-child(3){width:8px}

/* Overlay */
.mob-overlay{
  display:none;position:fixed;inset:0;
  background:rgba(26,26,24,0.45);z-index:199;
}
.mob-overlay.open{display:block}

/* Slide-in sidebar */
.mob-sidebar{
  position:fixed;top:0;left:0;bottom:0;
  width:270px;max-width:82vw;
  background:var(--sage-deep);
  z-index:200;display:flex;flex-direction:column;
  transform:translateX(-100%);
  transition:transform 0.28s cubic-bezier(.4,0,.2,1);
  overflow-y:auto;overflow-x:hidden;
}
.mob-sidebar.open{transform:translateX(0)}

.mob-sidebar-close{
  position:absolute;top:16px;right:14px;
  background:rgba(255,255,255,0.1);border:none;border-radius:50%;
  width:28px;height:28px;cursor:pointer;font-size:16px;
  display:flex;align-items:center;justify-content:center;
  color:rgba(255,255,255,0.7);transition:background 0.15s;
}
.mob-sidebar-close:hover{background:rgba(255,255,255,0.18)}

/* Language rows inside sidebar */
.mob-sidebar-section{
  padding:12px 14px;
  border-top:1px solid rgba(255,255,255,0.08);
}
.mob-sidebar-section-lbl{
  font-size:9px;font-weight:500;text-transform:uppercase;
  letter-spacing:1.2px;color:rgba(255,255,255,0.3);
  margin-bottom:8px;
}
.mob-lang-rows{display:flex;flex-direction:column;gap:2px}
.mob-lang-row{
  display:flex;align-items:center;gap:9px;
  padding:7px 9px;border-radius:var(--radius-sm);
  cursor:pointer;transition:background 0.15s;
  border:none;background:transparent;width:100%;
  text-align:left;font-family:'DM Sans',sans-serif;
}
.mob-lang-row:hover{background:rgba(255,255,255,0.07)}
.mob-lang-row.active{background:rgba(255,255,255,0.12)}
.mob-lang-dot{width:7px;height:7px;border-radius:50%;background:rgba(255,255,255,0.2);flex-shrink:0;transition:background 0.15s}
.mob-lang-row.active .mob-lang-dot{background:var(--butter)}
.mob-lang-row-name{font-size:13px;color:rgba(255,255,255,0.55)}
.mob-lang-row.active .mob-lang-row-name{color:#fff;font-weight:500}
.mob-lang-row-add{font-size:12px;color:var(--sage-mid)}

/* Level tiles inside sidebar */
.mob-level-grid{display:flex;gap:5px;flex-wrap:wrap;padding:0 2px}
.mob-level-tile{
  display:flex;align-items:center;gap:4px;
  padding:5px 10px;border-radius:var(--radius-sm);
  background:rgba(255,255,255,0.06);
  border:1px solid rgba(255,255,255,0.1);
  color:rgba(255,255,255,0.4);font-size:11px;
  font-family:'DM Sans',sans-serif;font-weight:500;
  cursor:pointer;text-decoration:none;transition:all 0.15s;
}
.mob-level-tile:hover{background:rgba(255,255,255,0.1)}
.mob-level-tile.active{background:var(--sage-dark);border-color:var(--sage-dark);color:#fff}

/* Mobile sidebar nav — fully independent, never affected by desktop collapse rules */
.mob-nav-list{padding:10px 10px 4px;display:flex;flex-direction:column;gap:1px}
.mob-nav-section-lbl{font-size:9px;font-weight:500;text-transform:uppercase;letter-spacing:1.3px;color:rgba(255,255,255,0.28);padding:0 8px;margin:8px 0 4px}
.mob-nav-item{display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:var(--radius-sm);cursor:pointer;border:none;background:transparent;width:100%;text-align:left;font-family:'DM Sans',sans-serif;transition:background 0.15s}
.mob-nav-item:hover{background:rgba(255,255,255,0.08)}
.mob-nav-item.active{background:rgba(255,255,255,0.13)}
.mob-nav-icon{width:30px;height:30px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.mob-nav-item.active .mob-nav-icon{background:rgba(255,255,255,0.14)}
.mob-nav-lbl{font-size:13px;font-weight:400;color:rgba(255,255,255,0.6)}
.mob-nav-item.active .mob-nav-lbl{color:#fff;font-weight:500}
.mob-nav-cnt{margin-left:auto;background:var(--peach);color:#7a3020;font-size:9px;font-weight:700;padding:2px 7px;border-radius:20px;line-height:1.5;flex-shrink:0}

/* ═══ RESPONSIVE ═══ */
@media(max-width:900px){
  :root{--sidebar-w:196px}
  .metrics{gap:8px}.metric-val{font-size:24px}
  .lessons-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
}
@media(max-width:768px){
  .sidebar,.topbar-right,.date-pill{display:none}
  .mob-topbar{display:flex}
  .dash{display:block;height:auto;min-height:100vh}
  .main{padding:66px 12px 24px;background:var(--sand)}
  .page-title{font-size:20px}
  .metrics{grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px}
  .metric{padding:11px 13px}.metric-val{font-size:21px}
  .grid2{grid-template-columns:1fr}.bottom-grid{grid-template-columns:1fr}
  .lessons-grid{grid-template-columns:1fr 1fr}
  .profile-stats{grid-template-columns:1fr 1fr}.profile-grid{grid-template-columns:1fr}
  .achievements{gap:8px}
  .card{padding:13px;border-radius:var(--radius-sm)}
  .chart-wrap{height:110px}
}
@media(max-width:430px){
  .main{padding:66px 10px 20px}
  .lessons-grid{grid-template-columns:1fr}
  .achievements{gap:6px}.badge-icon{width:34px;height:34px;font-size:15px}
  .profile-header{flex-direction:column;text-align:center}
  .profile-tags{justify-content:center}
}
</style>
</head>
<body>

<div class="dash" id="dash">

  <!-- ===== DESKTOP SIDEBAR ===== -->
  <div class="sidebar" id="sidebar">
    <button class="sidebar-toggle" id="sidebarToggle" title="Toggle sidebar">‹</button>
    <div class="brand">
      <div class="brand-logo"><img src="https://res.cloudinary.com/franklinrw/image/upload/v1755169717/kjk_bnrbmp.png" alt="Playmates logo"></div>
      <div class="brand-name"><img src="https://res.cloudinary.com/franklinrw/image/upload/v1755169717/kjk_bnrbmp.png" alt="">Playmates</div>
      <div class="brand-sub">Language Platform</div>
    </div>
    <div class="streak-pill">
      
      <span class="streak-lbl"><?= $streak['current_streak'] ?> day streak</span>
    </div>
    <nav class="nav">
      <div class="nav-section">Learn</div>
      <button class="nav-item active" data-page="dashboard">
        <div class="nav-icon">
          <svg width="15" height="15" fill="none" stroke="#fff" stroke-width="2"><rect x="1" y="1" width="5.5" height="5.5" rx="1.5"/><rect x="8.5" y="1" width="5.5" height="5.5" rx="1.5"/><rect x="1" y="8.5" width="5.5" height="5.5" rx="1.5"/><rect x="8.5" y="8.5" width="5.5" height="5.5" rx="1.5"/></svg>
        </div>
        <span class="nav-label">Dashboard</span>
      </button>
      <button class="nav-item" data-page="lessons">
        <div class="nav-icon">
          <svg width="15" height="15" fill="none" stroke="rgba(255,255,255,0.55)" stroke-width="2"><path d="M7.5 1.5l1.8 5h5l-4 3 1.5 5-4.3-3.1L3.2 14.5l1.5-5-4-3h5z"/></svg>
        </div>
        <span class="nav-label">Lessons</span>
        <span class="nav-badge"><?= count(array_filter($topics, fn($t) => $t['pct'] < 100)) ?></span>
      </button>
      <div class="nav-section">Account</div>
      <button class="nav-item" data-page="profile">
        <div class="nav-icon">
          <svg width="15" height="15" fill="none" stroke="rgba(255,255,255,0.55)" stroke-width="2"><circle cx="7.5" cy="5.5" r="3"/><path d="M1 14c0-3.6 2.9-6.5 6.5-6.5S14 10.4 14 14"/></svg>
        </div>
        <span class="nav-label">Profile</span>
      </button>
    </nav>
    <div class="sidebar-footer">
      <div class="xp-label">
        <span>Level <?= $current_level_num ?></span>
        <span><?= number_format($stats['total_xp'] ?? 0) ?> / <?= $next_level_xp ?> XP</span>
      </div>
      <div class="xp-bar"><div class="xp-fill" style="width:<?= $xp_percent ?>%"></div></div>
      <div class="level-txt">→ Level <?= $current_level_num + 1 ?></div>
    </div>
  </div>

  <!-- ===== MAIN ===== -->
  <div class="main">

    <!-- DASHBOARD PAGE -->
    <div class="page active" id="page-dashboard">
      <div class="topbar">
        <div class="page-title">Muraho, <?= htmlspecialchars($user['full_name'] ?? $user['username']) ?> 👋</div>
        <div class="topbar-right">
          <div class="lang-selector-group">
            <?php foreach ($user_languages as $lang_option): ?>
              <button class="lang-pill <?= $active_lang == $lang_option['language_code'] ? 'active' : '' ?>" onclick="changeLanguage('<?= $lang_option['language_code'] ?>')">
                <span class="fi fi-<?= $lang_option['language_code'] === 'en' ? 'gb' : $lang_option['language_code'] ?>"></span>
                <?= $lang_option['language_name'] ?>
              </button>
            <?php endforeach; ?>
            <button class="lang-pill add-lang" onclick="openLangModal()">+ Add</button>
          </div>
          <div class="level-pills">
            <?php for ($lvl = 1; $lvl <= 6; $lvl++): ?>
              <a href="?lang=<?= $active_lang ?>&level=<?= $lvl ?>" class="level-pill <?= $active_level == $lvl ? 'active' : '' ?>">
                <?= $levels[$lvl]['icon'] ?> <?= $lvl ?>
              </a>
            <?php endfor; ?>
          </div>
          <div class="date-pill"><?= date('D, M j Y') ?></div>
          <a href="choose-level.php?lang=<?= $active_lang ?>" class="start-btn">Start lesson →</a>
        </div>
      </div>

      <div class="metrics">
        <div class="metric m-green">
          <div class="metric-lbl">XP today</div>
          <div class="metric-val"><?= number_format($stats['xp_today'] ?? 0) ?></div>
          <div class="metric-sub">↑ Keep going!</div>
        </div>
        <div class="metric m-amber">
          <div class="metric-lbl">Accuracy</div>
          <div class="metric-val"><?= round($stats['avg_accuracy'] ?? 0) ?>%</div>
          <div class="metric-sub">last 50 exercises</div>
        </div>
        <div class="metric m-coral">
          <div class="metric-lbl">Lessons done</div>
          <div class="metric-val"><?= $stats['lessons_completed'] ?? 0 ?></div>
          <div class="metric-sub">in Level <?= $active_level ?></div>
        </div>
        <div class="metric m-purple">
          <div class="metric-lbl">Words learned</div>
          <div class="metric-val"><?= $stats['words_learned'] ?? 0 ?></div>
          <div class="metric-sub">this level</div>
        </div>
      </div>

      <div class="grid2">
        <div class="card">
          <div class="card-head"><span class="card-title">XP this week</span><button class="card-action" onclick="navigate('lessons')">view all</button></div>
          <div class="chart-wrap"><canvas id="xpChart"></canvas></div>
        </div>
        <div class="card">
          <div class="card-head"><span class="card-title">Topic progress</span><button class="card-action" onclick="navigate('lessons')">continue</button></div>
          <div class="lessons-list" id="topicList"></div>
        </div>
      </div>

      <div class="bottom-grid">
        <div class="card">
          <div class="card-head"><span class="card-title">Activity · last 91 days</span></div>
          <div class="heatmap" id="heatmap"></div>
          <div style="display:flex;gap:6px;align-items:center;margin-top:10px">
            <span style="font-size:10px;color:var(--muted);font-weight:700">less</span>
            <div style="width:11px;height:11px;border-radius:3px;background:#F1EFE8;border:0.5px solid var(--border)"></div>
            <div style="width:11px;height:11px;border-radius:3px;background:#9FE1CB"></div>
            <div style="width:11px;height:11px;border-radius:3px;background:#1D9E75"></div>
            <div style="width:11px;height:11px;border-radius:3px;background:#085041"></div>
            <span style="font-size:10px;color:var(--muted);font-weight:700">more</span>
          </div>
        </div>
        <div style="display:flex;flex-direction:column;gap:12px">
          <div class="card">
            <div class="card-head" style="margin-bottom:12px"><span class="card-title">Achievements</span><button class="card-action" onclick="navigate('profile')">all</button></div>
            <div class="achievements" id="achievements"></div>
          </div>
          <div class="card">
            <div class="card-head" style="margin-bottom:12px"><span class="card-title">Recent lessons</span><button class="card-action" onclick="navigate('lessons')">all</button></div>
            <div class="recent-list" id="recentLessons"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- LESSONS PAGE -->
    <div class="page" id="page-lessons">
      <div class="topbar">
        <div class="page-title">Lessons</div>
        <div class="topbar-right">
          <div class="lang-selector-group">
            <?php foreach ($user_languages as $lang_option): ?>
              <button class="lang-pill <?= $active_lang == $lang_option['language_code'] ? 'active' : '' ?>" onclick="changeLanguage('<?= $lang_option['language_code'] ?>')">
                <span class="fi fi-<?= $lang_option['language_code'] === 'en' ? 'gb' : $lang_option['language_code'] ?>"></span>
                <?= $lang_option['language_name'] ?>
              </button>
            <?php endforeach; ?>
            <button class="lang-pill add-lang" onclick="openLangModal()">+ Add</button>
          </div>
          <div class="level-pills">
            <?php for ($lvl = 1; $lvl <= 6; $lvl++): ?>
              <a href="?lang=<?= $active_lang ?>&level=<?= $lvl ?>" class="level-pill <?= $active_level == $lvl ? 'active' : '' ?>"><?= $lvl ?></a>
            <?php endfor; ?>
          </div>
          <div class="date-pill"><?= $current_lang_name ?> · Level <?= $active_level ?></div>
        </div>
      </div>
      <?php
      $total_topics = count($topics);
      $completed_topics = count(array_filter($topics, fn($t) => $t['pct'] == 100));
      $total_xp_available = array_reduce($topics, fn($c, $t) => $c + ($t['total'] * 15), 0);
      $earned_xp = $stats['total_xp'] ?? 0;
      $incomplete = array_filter($topics, fn($t) => $t['pct'] == 0);
      $next_topic = count($incomplete) > 0 ? $topics[array_key_first($incomplete)]['name'] : 'All done!';
      ?>
      <div class="metrics" style="grid-template-columns:repeat(3,minmax(0,1fr))">
        <div class="metric m-green">
          <div class="metric-lbl">Completed</div>
          <div class="metric-val"><?= $completed_topics ?>/<?= $total_topics ?></div>
          <div class="metric-sub">this level</div>
        </div>
        <div class="metric m-amber">
          <div class="metric-lbl">XP available</div>
          <div class="metric-val"><?= number_format($total_xp_available - $earned_xp) ?></div>
          <div class="metric-sub">remaining</div>
        </div>
        <div class="metric m-coral">
          <div class="metric-lbl">Next topic</div>
          <div class="metric-val" style="font-size:18px"><?= htmlspecialchars($next_topic) ?></div>
          <div class="metric-sub">ready to start</div>
        </div>
      </div>
      <div class="lessons-grid" id="lessonsGrid"></div>
    </div>

    <!-- PROFILE PAGE -->
    <div class="page" id="page-profile">
      <div class="topbar">
        <div class="page-title">Profile</div>
        <button class="start-btn" onclick="window.location.href='edit-profile.php'">Edit profile</button>
      </div>
      <div class="profile-header">
        <div class="profile-avatar"><?= strtoupper(substr($user['full_name'] ?? $user['email'] ?? 'U', 0, 2)) ?></div>
        <div>
          <div class="profile-name"><?= htmlspecialchars($user['full_name'] ?? $user['username']) ?></div>
          <div class="profile-handle">@<?= strtolower(str_replace(' ', '_', $user['full_name'] ?? $user['email'] ?? 'user')) ?> · joined <?= date('M Y', strtotime($user['created_at'])) ?></div>
          <div class="profile-tags">
            <span class="profile-tag" style="background:#E1F5EE;color:#0F6E56">🔥 <?= $streak['current_streak'] ?>-day streak</span>
            <span class="profile-tag" style="background:#EEEDFE;color:#534AB7">Level <?= $current_level_num ?></span>
          </div>
        </div>
      </div>
      <div class="profile-stats">
        <div class="stat-box"><div class="stat-box-val"><?= number_format($stats['total_xp'] ?? 0) ?></div><div class="stat-box-lbl">Total XP</div></div>
        <div class="stat-box"><div class="stat-box-val"><?= $stats['lessons_completed'] ?? 0 ?></div><div class="stat-box-lbl">Lessons done</div></div>
        <div class="stat-box"><div class="stat-box-val"><?= $stats['words_learned'] ?? 0 ?></div><div class="stat-box-lbl">Words learned</div></div>
        <div class="stat-box"><div class="stat-box-val"><?= round($stats['avg_accuracy'] ?? 0) ?>%</div><div class="stat-box-lbl">Avg accuracy</div></div>
      </div>
      <div class="profile-grid">
        <div class="card">
          <div class="card-head">
            <span class="card-title">My Languages</span>
            <button class="card-action" onclick="openLangModal()">+ Add</button>
          </div>
          <?php foreach ($user_languages as $lang_item):
            $isActive = $lang_item['language_code'] == $active_lang;
          ?>
            <div class="lang-row" style="<?= $isActive ? 'background:var(--sage);border-radius:10px;padding:10px;margin-bottom:6px;' : '' ?>">
              <div class="lang-flag"><span class="fi fi-<?= $lang_item['language_code'] === 'en' ? 'gb' : $lang_item['language_code'] ?>"></span></div>
              <div class="lang-info">
                <div class="lang-name"><?= $lang_item['language_name'] ?></div>
                <div class="lang-level"><?= $isActive ? 'Active · Level ' . $active_level : 'Click to switch' ?></div>
              </div>
              <?php if ($isActive): ?>
                <div class="lang-xp">Active</div>
              <?php else: ?>
                <a href="?lang=<?= $lang_item['language_code'] ?>&level=<?= $active_level ?>" class="lang-xp" style="color:var(--sage-dark);text-decoration:none">Switch</a>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="card">
          <div class="card-head"><span class="card-title">All achievements</span></div>
          <div class="achievements" id="allAchievements"></div>
        </div>
      </div>
    </div>

  </div><!-- /main -->
</div><!-- /dash -->

<!-- ===== MOBILE SLIM TOPBAR ===== -->
<div class="mob-topbar" id="mobTopbar">
 <button class="mob-hamburger" onclick="openMobSidebar()" aria-label="Open menu">
    <span></span><span></span><span></span>
  </button>
  <div class="mob-topbar-brand">
    <img src="https://res.cloudinary.com/franklinrw/image/upload/v1755169717/kjk_bnrbmp.png" alt="">
    PLAYMATES
  </div>
  <button class="mob-flag-trigger"  aria-label="Open menu">
    <span class="fi fi-<?= $active_lang === 'en' ? 'gb' : $active_lang ?>"></span>
  </button>
</div>

<!-- ===== MOBILE SIDEBAR OVERLAY ===== -->
<div class="mob-overlay" id="mobOverlay" onclick="closeMobSidebar()"></div>

<!-- ===== MOBILE SLIDE-IN SIDEBAR ===== -->
<div class="mob-sidebar" id="mobSidebar">
  <button class="mob-sidebar-close" onclick="closeMobSidebar()" aria-label="Close menu">×</button>

    <div class="brand" style="padding:20px 16px 14px">
      <div class="brand-name"><img src="https://res.cloudinary.com/franklinrw/image/upload/v1755169717/kjk_bnrbmp.png" alt="">Playmates</div>
      <div class="brand-sub">Language Platform</div>
    </div>

  <div class="streak-pill" style="margin:10px 12px 0">
    <span class="streak-lbl"><?= $streak['current_streak'] ?> day streak</span>
  </div>

  <nav class="mob-nav-list">
    <div class="mob-nav-section-lbl">Learn</div>
    <button class="mob-nav-item active" data-mob-page="dashboard" onclick="navigate('dashboard');closeMobSidebar()">
      <div class="mob-nav-icon">
        <svg width="15" height="15" fill="none" stroke="#fff" stroke-width="2"><rect x="1" y="1" width="5.5" height="5.5" rx="1.5"/><rect x="8.5" y="1" width="5.5" height="5.5" rx="1.5"/><rect x="1" y="8.5" width="5.5" height="5.5" rx="1.5"/><rect x="8.5" y="8.5" width="5.5" height="5.5" rx="1.5"/></svg>
      </div>
      <span class="mob-nav-lbl">Dashboard</span>
    </button>
    <button class="mob-nav-item" data-mob-page="lessons" onclick="navigate('lessons');closeMobSidebar()">
      <div class="mob-nav-icon">
        <svg width="15" height="15" fill="none" stroke="rgba(255,255,255,0.55)" stroke-width="2"><path d="M7.5 1.5l1.8 5h5l-4 3 1.5 5-4.3-3.1L3.2 14.5l1.5-5-4-3h5z"/></svg>
      </div>
      <span class="mob-nav-lbl">Lessons</span>
      <span class="mob-nav-cnt"><?= count(array_filter($topics, fn($t) => $t['pct'] < 100)) ?></span>
    </button>
    <div class="mob-nav-section-lbl" style="margin-top:10px">Account</div>
    <button class="mob-nav-item" data-mob-page="profile" onclick="navigate('profile');closeMobSidebar()">
      <div class="mob-nav-icon">
        <svg width="15" height="15" fill="none" stroke="rgba(255,255,255,0.55)" stroke-width="2"><circle cx="7.5" cy="5.5" r="3"/><path d="M1 14c0-3.6 2.9-6.5 6.5-6.5S14 10.4 14 14"/></svg>
      </div>
      <span class="mob-nav-lbl">Profile</span>
    </button>
  </nav>

  <!-- Language switcher -->
  <div class="mob-sidebar-section">
    <div class="mob-sidebar-section-lbl">Language</div>
    <div class="mob-lang-rows">
      <?php foreach ($user_languages as $lang_opt): ?>
        <button class="mob-lang-row <?= $active_lang == $lang_opt['language_code'] ? 'active' : '' ?>"
                onclick="changeLanguage('<?= $lang_opt['language_code'] ?>')">
          <span class="mob-lang-dot"></span>
          <span class="fi fi-<?= $lang_opt['language_code'] === 'en' ? 'gb' : $lang_opt['language_code'] ?>" style="font-size:15px;flex-shrink:0"></span>
          <span class="mob-lang-row-name"><?= $lang_opt['language_name'] ?></span>
        </button>
      <?php endforeach; ?>
      <button class="mob-lang-row" onclick="closeMobSidebar();openLangModal()">
        <span class="mob-lang-dot" style="background:rgba(255,255,255,0.12)"></span>
        <span class="mob-lang-row-add">+ Add language</span>
      </button>
    </div>
  </div>

  <!-- Level switcher -->
  <div class="mob-sidebar-section">
    <div class="mob-sidebar-section-lbl">Level</div>
    <div class="mob-level-grid">
      <?php for ($lvl = 1; $lvl <= 6; $lvl++): ?>
        <a href="?lang=<?= $active_lang ?>&level=<?= $lvl ?>"
           class="mob-level-tile <?= $active_level == $lvl ? 'active' : '' ?>">
          <?= $levels[$lvl]['icon'] ?> <?= $lvl ?>
        </a>
      <?php endfor; ?>
    </div>
  </div>

  <!-- XP footer -->
  <div class="sidebar-footer" style="margin-top:auto">
    <div class="xp-label">
      <span>Level <?= $current_level_num ?></span>
      <span><?= number_format($stats['total_xp'] ?? 0) ?> / <?= $next_level_xp ?> XP</span>
    </div>
    <div class="xp-bar"><div class="xp-fill" style="width:<?= $xp_percent ?>%"></div></div>
    <div class="level-txt">→ Level <?= $current_level_num + 1 ?></div>
  </div>
</div>

<!-- ===== ADD LANGUAGE MODAL ===== -->
<div class="modal-overlay" id="langModal">
  <div class="modal-box">
    <div class="modal-head">
      <span class="modal-title">Add a Language</span>
      <button class="modal-close" onclick="closeLangModal()">×</button>
    </div>
    <div class="modal-sub">Choose a language to learn</div>
    <div class="lang-cards-grid" id="langPickerGrid"></div>
    <p class="modal-note">Your progress is saved separately for each language.<br>You can switch between them anytime.</p>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
<script>
// ===================== DESKTOP SIDEBAR COLLAPSE =====================
const dash = document.getElementById('dash');
const toggleBtn = document.getElementById('sidebarToggle');
if (localStorage.getItem('sidebarCollapsed') === 'true') dash.classList.add('collapsed');
toggleBtn.addEventListener('click', () => {
  dash.classList.toggle('collapsed');
  localStorage.setItem('sidebarCollapsed', dash.classList.contains('collapsed'));
});

// ===================== MOBILE SIDEBAR =====================
function openMobSidebar() {
  document.getElementById('mobSidebar').classList.add('open');
  document.getElementById('mobOverlay').classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeMobSidebar() {
  document.getElementById('mobSidebar').classList.remove('open');
  document.getElementById('mobOverlay').classList.remove('open');
  document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeMobSidebar(); });

// ===================== NAVIGATION =====================
function navigate(page) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  document.querySelectorAll('.mob-nav-item').forEach(n => n.classList.remove('active'));
  const target = document.getElementById('page-' + page);
  if (target) target.classList.add('active');
  document.querySelectorAll('.nav-item[data-page="' + page + '"]').forEach(btn => btn.classList.add('active'));
  document.querySelectorAll('.mob-nav-item[data-mob-page="' + page + '"]').forEach(btn => {
    btn.classList.add('active');
    btn.querySelectorAll('svg [stroke]').forEach(el => el.setAttribute('stroke','#fff'));
  });
  updateNavIcons();
}
function updateNavIcons() {
  document.querySelectorAll('.nav-item').forEach(btn => {
    const isActive = btn.classList.contains('active');
    btn.querySelectorAll('svg [stroke]').forEach(el => {
      el.setAttribute('stroke', isActive ? '#fff' : 'rgba(255,255,255,0.55)');
    });
  });
}
document.querySelectorAll('.nav-item[data-page]').forEach(btn => {
  btn.addEventListener('click', () => navigate(btn.dataset.page));
});
updateNavIcons();

// ===================== LANGUAGE =====================
function changeLanguage(lang) {
  window.location.href = 'dashboard.php?lang=' + lang + '&level=<?= $active_level ?>';
}

// ===================== MODAL =====================
const allLanguages = [
  { code: 'en', name: 'English',     flag: 'gb', desc: 'Global · 1.5B speakers' },
  { code: 'rw', name: 'Kinyarwanda', flag: 'rw', desc: 'Rwanda · 12M speakers' },
  { code: 'fr', name: 'French',      flag: 'fr', desc: 'Europe, Africa · 300M' },
  { code: 'sw', name: 'Swahili',     flag: 'tz', desc: 'East Africa · 200M' },
  { code: 'es', name: 'Spanish',     flag: 'es', desc: 'Global · 500M speakers' },
  { code: 'de', name: 'German',      flag: 'de', desc: 'Europe · 130M speakers' },
];
const userLangCodes = <?= json_encode(array_column($user_languages, 'language_code')) ?>;
function openLangModal() {
  const grid = document.getElementById('langPickerGrid');
  grid.innerHTML = '';
  allLanguages.forEach(lang => {
    const added = userLangCodes.includes(lang.code);
    const card = document.createElement('button');
    card.className = 'lang-pick-card' + (added ? ' is-added' : '');
    card.innerHTML =
      '<span class="lang-pick-flag"><span class="fi fi-' + lang.flag + '"></span></span>' +
      '<span class="lang-pick-name">' + lang.name + '</span>' +
      (added ? '<span class="lang-pick-added">✓ Added</span>' : '<span class="lang-pick-desc">' + lang.desc + '</span>');
    if (!added) card.onclick = () => addLanguage(lang.code, lang.name);
    grid.appendChild(card);
  });
  document.getElementById('langModal').classList.add('open');
}
function closeLangModal() { document.getElementById('langModal').classList.remove('open'); }
document.getElementById('langModal').addEventListener('click', function(e) { if (e.target === this) closeLangModal(); });
function addLanguage(code, name) {
  fetch('add-language-pair.php', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({ language_code: code, language_name: name })
  }).then(r => r.json()).then(data => {
    if (data.success) window.location.href = 'dashboard.php?lang=' + code + '&level=1';
    else alert(data.message || 'Could not add language.');
  }).catch(() => alert('Network error. Please try again.'));
}

// ===================== XP CHART =====================
const xpData = <?= json_encode($xp_data) ?>;
new Chart(document.getElementById('xpChart'), {
  type: 'bar',
  data: {
    labels: <?= json_encode($days) ?>,
    datasets: [{ data: xpData, backgroundColor: xpData.map((v,i) => i === new Date().getDay() ? '#1E9E6B' : '#9FE1CB'), borderRadius: 7, borderSkipped: false }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => ' ' + c.raw + ' XP' } } },
    scales: {
      x: { grid: { display: false }, border: { display: false }, ticks: { font: { family: 'DM Sans', size: 11, weight: '500' }, color: '#888780' } },
      y: { display: false }
    }
  }
});

// ===================== TOPIC BARS =====================
const topics = <?= json_encode($topics) ?>;
const tl = document.getElementById('topicList');
topics.slice(0, 5).forEach(t => {
  const color = t.pct > 75 ? '#1D9E75' : t.pct > 50 ? '#1E9E6B' : t.pct > 25 ? '#EF9F27' : '#7F77DD';
  tl.innerHTML += '<div class="lesson-row">' +
    '<div class="lesson-dot" style="background:' + (t.pct === 0 ? '#888780' : color) + '"></div>' +
    '<div class="lesson-name" title="' + t.name + '">' + t.name + '</div>' +
    '<div class="lesson-bar-wrap"><div class="lesson-bar" style="width:' + t.pct + '%;background:' + (t.pct === 0 ? '#888780' : color) + '"></div></div>' +
    '<div class="lesson-pct">' + t.pct + '%</div>' +
  '</div>';
});

// ===================== HEATMAP =====================
const hm = document.getElementById('heatmap');
const greens = ['#F1EFE8','#9FE1CB','#1D9E75','#085041'];
const activityMap = <?= json_encode($activity_map) ?>;
for (let i = 0; i < 91; i++) {
  const date = new Date();
  date.setDate(date.getDate() - (90 - i));
  const dateStr = date.toISOString().split('T')[0];
  const lvl = activityMap[dateStr] || 0;
  const cell = document.createElement('div');
  cell.className = 'hm-cell';
  cell.style.background = greens[lvl];
  cell.title = (lvl === 0 ? 'No activity' : lvl === 1 ? 'Light' : lvl === 2 ? 'Moderate' : 'Heavy') + ' — ' + dateStr;
  hm.appendChild(cell);
}

// ===================== LESSONS GRID =====================
const grid = document.getElementById('lessonsGrid');
grid.innerHTML = '';
topics.forEach(t => {
  const card = document.createElement('div');
  card.className = 'lesson-card';
  const badge = t.pct === 100 ? 'Completed' : t.pct > 0 ? 'In progress' : 'Not started';
  const badgeBg = t.pct === 100 ? '#E1F5EE' : t.pct > 0 ? '#FAEEDA' : '#F1EFE8';
  const badgeCol = t.pct === 100 ? '#0F6E56' : t.pct > 0 ? '#854F0B' : '#5F5E5A';
  const color = t.pct > 75 ? '#1D9E75' : t.pct > 50 ? '#1E9E6B' : t.pct > 25 ? '#EF9F27' : '#7F77DD';
  card.innerHTML =
    '<div class="lesson-card-icon">' + t.icon + '</div>' +
    '<span class="lesson-badge" style="background:' + badgeBg + ';color:' + badgeCol + '">' + badge + '</span>' +
    '<div class="lesson-card-name">' + t.name + '</div>' +
    '<div class="lesson-card-meta">' + t.total + ' exercises</div>' +
    '<div class="lesson-card-bar"><div class="lesson-card-fill" style="width:' + t.pct + '%;background:' + (t.pct === 0 ? '#888780' : color) + '"></div></div>' +
    '<div class="lesson-card-foot"><span class="lesson-card-pct">' + t.pct + '% done</span><span class="lesson-card-xp">' + (t.pct === 100 ? '✓ completed' : 'Start →') + '</span></div>';
  card.addEventListener('click', () => {
    window.location.href = 'lesson.php?lang=<?= $active_lang ?>&level=<?= $active_level ?>&topic=' + encodeURIComponent(t.file) + '&id=1';
  });
  grid.appendChild(card);
});

// ===================== ACHIEVEMENTS =====================
const achievements = <?= json_encode($achievements) ?>;
const achColors = ['#FAEEDA','#E1F5EE','#EEEDFE','#F1EFE8','#FAEEDA','#E6F1FB','#FAECE7','#EEEDFE'];
const achIcons  = ['🔥','🌱','⚡','🚀','📝','📖','🗣️','🏆'];
['achievements','allAchievements'].forEach(id => {
  const el = document.getElementById(id);
  if (!el) return;
  el.innerHTML = '';
  achievements.forEach((ach, i) => {
    const div = document.createElement('div');
    div.className = 'badge-item ' + (ach.earned ? 'earned' : 'locked');
    div.innerHTML =
      '<div class="badge-icon" style="background:' + achColors[i % achColors.length] + '">' + (achIcons[i % achIcons.length] || '🏅') + '</div>' +
      '<div class="badge-lbl">' + ach.name + '</div>';
    el.appendChild(div);
  });
});

// ===================== RECENT LESSONS =====================
const recentLessons = <?= json_encode($recent_lessons) ?>;
const recentDiv = document.getElementById('recentLessons');
const recentIcons = ['🐕','👋','🔢','📚'];
recentDiv.innerHTML = '';
if (recentLessons.length === 0) {
  recentDiv.innerHTML = '<div class="recent-row"><div class="recent-icon" style="background:#F1EFE8">📘</div><div class="recent-info"><div class="recent-name">No lessons yet</div><div class="recent-meta">Start your first lesson!</div></div><div class="recent-xp">→</div></div>';
} else {
  recentLessons.forEach((l, i) => {
    const div = document.createElement('div');
    div.className = 'recent-row';
    div.innerHTML =
      '<div class="recent-icon" style="background:#E1F5EE">' + (recentIcons[i] || '📚') + '</div>' +
      '<div class="recent-info"><div class="recent-name">' + (l.lesson_name || 'Lesson') + '</div><div class="recent-meta">Level ' + l.level_number + ' · ' + l.exercises_done + ' exercises</div></div>' +
      '<div class="recent-xp">+' + l.xp_earned + '</div>';
    recentDiv.appendChild(div);
  });
}
</script>
</body>
</html>