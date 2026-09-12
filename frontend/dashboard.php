<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Show fatal error details on shutdown to help diagnose 500 responses
register_shutdown_function(function(){
  $err = error_get_last();
  if($err && ($err['type'] & (E_ERROR|E_PARSE|E_CORE_ERROR|E_COMPILE_ERROR))){
    echo "<pre style=\"color:#b00020;background:#fff7f7;padding:12px;border-radius:6px;\">PHP Fatal Error:\n";
    echo htmlspecialchars(print_r($err, true));
    echo "</pre>";
  }
});

// Load backend config for DB and session


// Ensure UI language is set
$ui_lang = $_GET['ui_lang'] ?? $_SESSION['ui_lang'] ?? 'en';

function t($key){ global $ui_lang;
  $translations = [
      'en' => [
            'recent_lessons' => 'Recent lessons',
            'less' => 'less',
            'more' => 'more',
            'no_activity' => 'No activity',
            'light_activity' => 'Light activity',
            'moderate_activity' => 'Moderate activity',
            'heavy_activity' => 'Heavy activity',
            'click_to_start' => 'click a node to start',
            
            // Lessons page
            'completed' => 'Completed',
            'xp_available' => 'XP available',
            'remaining' => 'remaining',
            'next_topic' => 'Next topic',
            'ready_to_start' => 'ready to start',
            'exercises' => 'exercises',
            'done' => 'done',
            'start' => 'Start →',
            'completed_badge' => 'Completed',
            'in_progress_badge' => 'In progress',
            'not_started_badge' => 'Not started',
            'all_done' => 'All done!',
            
            // Profile page
            'edit_profile' => 'Edit profile',
            'joined' => 'joined',
            'total_xp' => 'Total XP',
            'lessons_completed' => 'Lessons done',
            'words_learned_total' => 'Words learned',
            'avg_accuracy' => 'Avg accuracy',
            'my_languages' => 'My Languages',
            'add_language' => '+ Add',
            'all_achievements' => 'All achievements',
            'click_to_switch' => 'Click to switch',
            'active' => 'Active',
            'switch' => 'Switch',
            'remove' => 'Remove',
            
            // Common
            'start_lesson' => 'Start lesson →',
            'take_exam' => 'Take Exam',
            'no_topics' => 'No topics found for this level',
            'no_lessons_yet' => 'Play your first game to see your data.',
            'start_first_lesson' => 'Start your first lesson!',
            'level' => 'Level',
            'guidebook' => 'Guidebook',
            
            // Modal
            'add_language_pair' => 'Add a Language Pair',
            'choose_language_pair' => 'Choose a language pair to learn',
            'your_progress_saved' => 'Your progress is saved separately for each language pair.',
            'you_can_switch' => 'You can switch between them anytime.',
            'start_learning' => 'Start learning',
            'add' => 'Add',
            'active_language' => '✓ Active',

            // Topics
            'greetings' => 'Greetings',
            'numbers' => 'Numbers',
            'family' => 'Family',
            'colors' => 'Colors',
            'animals' => 'Animals',
            'food' => 'Food',
            'daily-routines' => 'Daily Routines',
            'weather' => 'Weather',
            'clothing' => 'Clothing',
            'house' => 'House',
            'travel' => 'Travel',
            'shopping' => 'Shopping',
            'restaurant' => 'Restaurant',
            'directions' => 'Directions',
            'emotions' => 'Emotions',
            'storytelling' => 'Storytelling',
            'business' => 'Business',
            'culture' => 'Culture',
            'days' => 'Days',
            'debates' => 'Debates',
            'fluency' => 'Fluency',
            'negotiations' => 'Negotiations',
            'opinions' => 'Opinions',
            'past-tense' => 'Past Tense',
            'future-tense' => 'Future Tense',
            'conditionals' => 'Conditionals',
        ],
        'rw' => [
            // Navigation
            'dashboard' => 'Ikibaho',
            'lessons' => 'Amasomo',
            'map' => 'Ikarita',
            'profile' => 'Ibyerekeye',
            'sign_out' => 'Sohoka',
            'language_platform' => 'Ururimi Platform',
            'day_streak' => 'iminsi ikurikirana',
            
            // Dashboard page
            'xp_today' => 'XP uyu munsi',
            'keep_going' => 'Komeza!',
            'accuracy' => 'Ibyakozwe neza',
            'last_50_exercises' => 'imyitozo 50 yashize',
            'lessons_done' => 'Amasomo yarangiye',
            'in_level' => 'muri rwego',
            'words_learned' => 'Amagambo yize',
            'this_level' => 'uru rwego',
            'xp_this_week' => 'XP iki cyumweru',
            'view_all' => 'reba byose',
            'close' => 'Funga',
            'topic_progress' => 'Iterambere ry\'amasomo',
            'continue' => 'komeza',
            'activity_last_91_days' => 'Ibikorwa · iminsi 91 ishize',
            'achievements' => 'Ibyagezweho',
            'curriculum_outline' => 'Imbonerahamwe y\'amasomo',
            'lesson_plan' => 'Umugambi w\'isomo',
            'jump_here' => 'Jya hano',
            'lesson_plan_section_1' => 'Abantu n\'ibikorwa bya buri munsi',
            'lesson_plan_section_2' => 'Ibintu n\'ahantu',
            'lesson_plan_section_1_desc' => 'Tangira n\'indamutso n\'ibikorwa bya buri munsi',
            'lesson_plan_section_2_desc' => 'Higa ibintu, ahantu n\'ibiganiro',
            'recent_lessons' => 'Amasomo ashize',
            'less' => 'gake',
            'more' => 'nyinshi',
            'no_activity' => 'Nta kintu',
            'light_activity' => 'Gake',
            'moderate_activity' => 'Hagati',
            'heavy_activity' => 'Byinshi',
            'click_to_start' => 'Kanda kuri nodi utangire',
            
            // Lessons page
            'completed' => 'Byarangiye',
            'xp_available' => 'XP isigaye',
            'remaining' => 'isigaye',
            'next_topic' => 'Ikurikira',
            'ready_to_start' => 'iteguye gutangira',
            'exercises' => 'imyitozo',
            'done' => 'byakozwe',
            'start' => 'Tangira →',
            'completed_badge' => 'Byarangiye',
            'in_progress_badge' => 'Biracyakorwa',
            'not_started_badge' => 'Ntabwo byatangiye',
            'all_done' => 'Byose byarangiye!',
            
            // Profile page
            'edit_profile' => 'Hindura profil',
            'joined' => 'yinjiiye',
            'total_xp' => 'XP yose',
            'lessons_completed' => 'Amasomo yarangiye',
            'words_learned_total' => 'Amagambo yize',
            'avg_accuracy' => 'Impuzandengo y\'ibyakozwe neza',
            'my_languages' => 'Indimi zanjye',
            'add_language' => '+ Ongera',
            'all_achievements' => 'Ibyagezweho byose',
            'click_to_switch' => 'Kanda guhindura',
            'active' => 'Gikora',
            'switch' => 'Hindura',
            'remove' => 'Kuraho',
            
            // Common
            'start_lesson' => 'Tangira isomo →',
            'take_exam' => 'Gira Ibizamini',
            'no_topics' => 'Nta masomo yabonetse muri uru rwego',
            'no_lessons_yet' => 'Kina umukino wa mbere',
            'start_first_lesson' => 'Tangira isomo rya mbere!',
            'level' => 'Rwego',
            'guidebook' => 'Umutwe w’amafashanyigisho',
            
            // Modal
            'add_language_pair' => 'Ongera ururimi',
            'choose_language_pair' => 'Hitamo ururimi ushaka kwiga',
            'your_progress_saved' => 'Iterambere ryawe ribikwa kuri buri rurimi.',
            'you_can_switch' => 'Urashobora guhindura igihe icyo aricyo cyose.',
            'start_learning' => 'Tangira kwiga',
            'add' => 'Ongera',
            'active_language' => '✓ Gikora',

            // Topics
            'greetings' => 'Indamutso',
            'numbers' => 'Imibare',
            'family' => 'Umuryango',
            'colors' => 'Amabara',
            'animals' => 'Inyamaswa',
            'food' => 'Ibiribwa',
            'daily-routines' => 'Ibikorwa buri munsi',
            'weather' => 'Ibihe',
            'clothing' => 'Imyambaro',
            'house' => 'Inzu',
            'travel' => 'Ingendo',
            'shopping' => 'Ubuguzi',
            'restaurant' => 'Resitora',
            'directions' => 'Amayerekezo',
            'emotions' => 'Amarangamutima',
            'storytelling' => 'Imigani',
            'business' => 'Ubucuruzi',
            'culture' => 'Umuco',
            'days' => 'Iminsi',
            'debates' => 'Ibiganiro',
            'fluency' => 'Kuvuga neza',
            'negotiations' => 'Ibiganiro',
            'opinions' => 'Ibitekerezo',
            'past-tense' => 'Igihe cyashize',
            'future-tense' => 'Igihe kizaza',
            'conditionals' => 'Ibisabwa',
        ],
        'sw' => [
            // Navigation
            'dashboard' => 'Dashibodi',
            'lessons' => 'Masomo',
            'map' => 'Ramani',
            'profile' => 'Wasifu',
            'sign_out' => 'Toka nje',
            'language_platform' => 'Jukwaa la Lugha',
            'day_streak' => 'siku mfuatano',
            
            // Dashboard page
            'xp_today' => 'XP leo',
            'keep_going' => 'Endelea!',
            'accuracy' => 'Usahihi',
            'last_50_exercises' => 'zoezi 50 zilizopita',
            'lessons_done' => 'Masomo yaliyomalizwa',
            'in_level' => 'kwenye kiwango',
            'words_learned' => 'Maneno yaliyofunzwa',
            'this_level' => 'kiwango hiki',
            'xp_this_week' => 'XP wiki hii',
            'view_all' => 'angalia yote',
            'close' => 'Funga',
            'topic_progress' => 'Maendeleo ya mada',
            'continue' => 'endelea',
            'activity_last_91_days' => 'Shughuli · siku 91 zilizopita',
            'achievements' => 'Mafanikio',
            'curriculum_outline' => 'Mpangilio ya kozi',
            'lesson_plan' => 'Mpangilio ya somo',
            'jump_here' => 'Ruka hapa',
            'lesson_plan_section_1' => 'Watu na Vitendo vya Kila Siku',
            'lesson_plan_section_2' => 'Mali, Vitu na Mahali',
            'lesson_plan_section_1_desc' => 'Anza na salamu na ratiba za kila siku',
            'lesson_plan_section_2_desc' => 'Jifunze vitu, mahali na mazungumzo',
            'recent_lessons' => 'Masomo ya karibuni',
            'less' => 'chache',
            'more' => 'zaidi',
            'no_activity' => 'Hakuna shughuli',
            'light_activity' => 'Shughuli ndogo',
            'moderate_activity' => 'Shughuli ya kati',
            'heavy_activity' => 'Shughuli kubwa',
            'click_to_start' => 'bonyeza nodi kuanza',
            
            // Lessons page
            'completed' => 'Limemaliza',
            'xp_available' => 'XP inayopatikana',
            'remaining' => 'iliyobaki',
            'next_topic' => 'Mada inayofuata',
            'ready_to_start' => 'iko tayari kuanza',
            'exercises' => 'mazoezi',
            'done' => 'malizi',
            'start' => 'Anza →',
            'completed_badge' => 'Limemaliza',
            'in_progress_badge' => 'Inaendelea',
            'not_started_badge' => 'Haijaanza',
            'all_done' => 'Limemaliza yote!',
            
            // Profile page
            'edit_profile' => 'Hariri wasifu',
            'joined' => 'alijiunga',
            'total_xp' => 'XP jumla',
            'lessons_completed' => 'Masomo yaliyomalizwa',
            'words_learned_total' => 'Jumla ya maneno yaliyofunzwa',
            'avg_accuracy' => 'Usahihi wa wastani',
            'my_languages' => 'Lugha zangu',
            'add_language' => '+ Ongeza',
            'all_achievements' => 'Mafanikio yote',
            'click_to_switch' => 'Bonyeza kubadili',
            'active' => 'Amilifu',
            'switch' => 'Badili',
            'remove' => 'Ondoa',
            
            // Common
            'start_lesson' => 'Anza somo →',
            'take_exam' => 'Fanya Mtihani',
            'no_topics' => 'Hakuna mada kwenye kiwango hiki',
            'no_lessons_yet' => 'Cheza mchezo wa kwanza',
            'start_first_lesson' => 'Anza somo la kwanza!',
            'level' => 'Kiwango',
            
            // Modal
            'add_language_pair' => 'Ongeza jozi ya lugha',
            'choose_language_pair' => 'Chagua jozi ya lugha unataka kujifunza',
            'your_progress_saved' => 'Maendeleo yako yalihifadhiwa kwa kila jozi ya lugha.',
            'you_can_switch' => 'Unaweza kubadili wakati wowote.',
            'start_learning' => 'Anza kujifunza',
            'add' => 'Ongeza',
            'active_language' => '✓ Amilifu',

            // Topics
            'greetings' => 'Salamu',
            'numbers' => 'Namba',
            'family' => 'Familia',
            'colors' => 'Rangi',
            'animals' => 'Wanyama',
            'food' => 'Chakula',
            'daily-routines' => 'Kazi ya kila siku',
            'weather' => 'Tabia nchi',
            'clothing' => 'Mavazi',
            'house' => 'Nyumba',
            'travel' => 'Usafiri',
            'shopping' => 'Ununuzi',
            'restaurant' => 'Mgahawa',
            'directions' => 'Mwelekeo',
            'emotions' => 'Hisia',
            'storytelling' => 'Kuweza kusomeka',
            'business' => 'Biashara',
            'culture' => 'Utamaduni',
            'days' => 'Siku',
            'debates' => 'Mijadala',
            'fluency' => 'Fluency',
            'negotiations' => 'Mazungumzo',
            'opinions' => 'Maoni',
            'past-tense' => 'Wakati uliopita',
            'future-tense' => 'Wakati ujao',
            'conditionals' => 'Masharti',
        ],
    ];
    
    return $translations[$ui_lang][$key] ?? $key;
}

$user_id = $_SESSION['user_id'];
$active_pair      = $_GET['pair']      ?? $_SESSION['active_pair']      ?? 'en-rw';
$active_direction = $_GET['direction'] ?? $_SESSION['active_direction'] ?? 'en';
$active_level     = isset($_GET['level']) ? (int)$_GET['level'] : ($_SESSION['active_level'] ?? 1);
$additional_rw_languages = [
  'id' => 'Indonesian', 'uk' => 'Ukrainian', 'sw' => 'Kiswahili', 'vi' => 'Vietnamese',
  'zu' => 'Zulu', 'gd' => 'Scottish Gaelic', 'la' => 'Latin', 'el' => 'Greek',
  'he' => 'Hebrew', 'pl' => 'Polish', 'no' => 'Norwegian', 'da' => 'Danish',
  'fi' => 'Finnish', 'cs' => 'Czech', 'ar' => 'Arabic', 'ru' => 'Russian',
  'hi' => 'Hindi', 'tr' => 'Turkish', 'nl' => 'Dutch', 'sv' => 'Swedish',
  'ga' => 'Irish', 'pt' => 'Portuguese', 'ja' => 'Japanese', 'ko' => 'Korean',
  'zh' => 'Chinese',
];
$language_flag_map = [
  'en' => 'gb', 'rw' => 'rw', 'fr' => 'fr', 'sw' => 'tz', 'id' => 'id',
  'uk' => 'ua', 'vi' => 'vn', 'zu' => 'za', 'gd' => 'gb', 'la' => 'it',
  'el' => 'gr', 'he' => 'il', 'pl' => 'pl', 'no' => 'no', 'da' => 'dk',
  'fi' => 'fi', 'cs' => 'cz', 'ar' => 'sa', 'ru' => 'ru', 'hi' => 'in',
  'tr' => 'tr', 'nl' => 'nl', 'sv' => 'se', 'ga' => 'ie', 'pt' => 'pt',
  'ja' => 'jp', 'ko' => 'kr', 'zh' => 'cn',
];
$get_language_flag = static function ($language_code) use ($language_flag_map) {
    return $language_flag_map[$language_code] ?? 'un';
};
$valid_directions = ['en', 'rw', 'fr', 'sw'];
$valid_pairs = ['en-rw', 'fr-rw', 'en-sw', 'fr-sw'];
foreach (array_keys($additional_rw_languages) as $language_code) {
  $valid_pairs[] = 'rw-' . $language_code;
  $valid_directions[] = $language_code;
}

if (strpos($active_direction, 'business-english') !== false || !in_array($active_direction, $valid_directions)) {
    $active_direction = 'en';
    error_log("RESET_INVALID_DIRECTION: Was '$active_direction', reset to 'en'");
}
if (strpos($active_pair, 'business-english') !== false || !in_array($active_pair, $valid_pairs)) {
    $active_pair = 'en-rw';
    error_log("RESET_INVALID_PAIR: Was '$active_pair', reset to 'en-rw'");
}

error_log("DASHBOARD_LOAD: active_pair=$active_pair active_direction=$active_direction SESSION[active_pair]=" . ($_SESSION['active_pair'] ?? 'NULL'));

// Only update session for regular language pairs, NOT Business English
// This prevents Business English navigation from interfering with regular lesson selection
if ($active_pair !== 'business-english') {
    $_SESSION['active_pair']      = $active_pair;
    $_SESSION['active_direction'] = $active_direction;
    $_SESSION['active_level']     = $active_level;
}

$lang_pairs = [
    'en-rw' => [
        'code' => 'en-rw',
        'name' => 'English ↔ Kinyarwanda',
        'languages' => ['en' => 'English', 'rw' => 'Kinyarwanda'],
        'directions' => [
            'en' => ['name' => 'Eng → Kiny', 'flag' => 'gb', 'folder' => 'EN-TO-RW'],
            'rw' => ['name' => 'Kiny → Eng', 'flag' => 'rw', 'folder' => 'RW-TO-EN']
        ]
    ],
    'fr-rw' => [
        'code' => 'fr-rw',
        'name' => 'French ↔ Kinyarwanda',
        'languages' => ['fr' => 'French', 'rw' => 'Kinyarwanda'],
        'directions' => [
            'fr' => ['name' => 'Fre → Kiny', 'flag' => 'fr', 'folder' => 'FR-TO-RW'],
            'rw' => ['name' => 'Kiny → Fre', 'flag' => 'rw', 'folder' => 'RW-TO-FR']
        ]
    ],
    'en-sw' => [
        'code' => 'en-sw',
        'name' => 'English ↔ Kiswahili',
        'languages' => ['en' => 'English', 'sw' => 'Kiswahili'],
        'directions' => [
            'en' => ['name' => 'Eng → Ksw', 'flag' => 'gb', 'folder' => 'EN-TO-SW'],
            'sw' => ['name' => 'Ksw → Eng', 'flag' => 'tz', 'folder' => 'SW-TO-EN']
        ]
    ],
    'fr-sw' => [
        'code' => 'fr-sw',
        'name' => 'French ↔ Kiswahili',
        'languages' => ['fr' => 'French', 'sw' => 'Kiswahili'],
        'directions' => [
            'fr' => ['name' => 'Fre → Ksw', 'flag' => 'fr', 'folder' => 'FR-TO-SW'],
            'sw' => ['name' => 'Ksw → Fre', 'flag' => 'tz', 'folder' => 'SW-TO-FR']
        ]
    ]
];

    foreach ($additional_rw_languages as $language_code => $language_name) {
      $pair_code = 'rw-' . $language_code;
      $lang_pairs[$pair_code] = [
        'code' => $pair_code,
        'name' => 'Kinyarwanda ↔ ' . $language_name,
        'languages' => ['rw' => 'Kinyarwanda', $language_code => $language_name],
        'directions' => [
          'rw' => [
            'name' => 'RW-' . strtoupper($language_code),
            'flag' => $get_language_flag('rw'),
            'folder' => 'RW-TO-' . strtoupper($language_code),
          ],
          $language_code => [
            'name' => strtoupper($language_code) . '-RW',
            'flag' => $get_language_flag($language_code),
            'folder' => strtoupper($language_code) . '-TO-RW',
          ],
        ],
        'short' => 'RW-' . strtoupper($language_code),
      ];
    }

$current_pair = $lang_pairs[$active_pair] ?? $lang_pairs['en-rw'];
$dir_info     = $current_pair['directions'][$active_direction] ?? null;

$folder      = $dir_info['folder'] ?? 'EN-TO-RW';
$active_lang = strtolower($folder);

// Some generated English source content is wrapped in an extra EN-TO-RW directory.
// Use it when the mapped folder has no level directories so the roadmap and lessons stay available.
$content_root = "../content/{$folder}";
if (!is_dir($content_root . "/level{$active_level}") && is_dir($content_root . "/{$folder}/level{$active_level}")) {
  $folder .= "/{$folder}";
}

$folder_parts     = explode('-to-', $active_lang);
$target_lang_code = $folder_parts[1] ?? 'rw';
$current_lang_name = $current_pair['languages'][$target_lang_code] ?? 'Kinyarwanda';

$levels = [
    1 => ['name' => 'Beginner',          'tag' => 'A1', 'icon' => '🟢'],
    2 => ['name' => 'Elementary',        'tag' => 'A2', 'icon' => '🔵'],
    3 => ['name' => 'Intermediate',      'tag' => 'B1', 'icon' => '🟡'],
    4 => ['name' => 'Upper Intermediate','tag' => 'B2', 'icon' => '🟠'],
    5 => ['name' => 'Advanced',          'tag' => 'C1', 'icon' => '🔴'],
    6 => ['name' => 'Expert',            'tag' => 'C2', 'icon' => '⚪']
];

function get_lesson_name_safe($folder, $level, $topic_file) {
    $yaml_file = "../content/{$folder}/level{$level}/{$topic_file}";
    if (file_exists($yaml_file)) {
        $content = file_get_contents($yaml_file);
        if (function_exists('yaml_parse')) {
            $data = yaml_parse($content);
            return $data['lesson']['name'] ?? pathinfo($topic_file, PATHINFO_FILENAME);
        }
        return pathinfo($topic_file, PATHINFO_FILENAME);
    }
    return pathinfo($topic_file, PATHINFO_FILENAME);
}

function get_icon_for_topic($topic) {
    $icons = [
        'greetings'     => '👋', 'numbers'  => '🔢', 'family'   => '👨‍👩‍👧',
        'colors'        => '🎨', 'animals'  => '🐕', 'food'     => '🍔',
        'daily-routines'=> '☀️', 'weather'  => '☁️', 'clothing' => '👕',
        'house'         => '🏠', 'travel'   => '✈️', 'shopping' => '🛍️',
        'restaurant'    => '🍽️', 'directions'=> '🗺️','emotions' => '😊'
    ];
    return $icons[$topic] ?? '📚';
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$stmt = $pdo->prepare("SELECT pair_code FROM user_language_pairs WHERE user_id = ?");
$stmt->execute([$user_id]);
$user_pairs = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (empty($user_pairs)) {
    // Automatically add all language pairs for new users
    foreach (array_keys($lang_pairs) as $pair_code) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO user_language_pairs (user_id, pair_code) VALUES (?, ?)");
        $stmt->execute([$user_id, $pair_code]);
    }
    $user_pairs   = array_keys($lang_pairs);
    $active_pair  = 'en-rw';
    $_SESSION['active_pair'] = 'en-rw';
}

$stmt = $pdo->prepare("
    SELECT
        COALESCE(SUM(CASE WHEN DATE(completed_at) = CURDATE() THEN xp_earned END), 0) as xp_today,
        COALESCE(AVG(accuracy), 0)                                                     as avg_accuracy,
        COUNT(DISTINCT CONCAT(level_number,'/',topic_file))                            as lessons_completed,
        COALESCE(SUM(xp_earned), 0)                                                    as total_xp,
        COUNT(DISTINCT id)                                                             as total_exercises,
        COUNT(DISTINCT CONCAT(level_number,'/',topic_file,'/',exercise_id))            as words_learned
    FROM user_progress
    WHERE user_id = ? AND language_code = ? AND level_number = ? AND completed = 1
");
$stmt->execute([$user_id, $active_lang, $active_level]);
$stats = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM user_streaks WHERE user_id = ?");
$stmt->execute([$user_id]);
$streak = $stmt->fetch() ?: ['current_streak' => 0, 'longest_streak' => 0];

// Load user certificates from institution exams - get latest per exam title to avoid duplicates
$stmt = $pdo->prepare("
    SELECT c.*,
           COALESCE(NULLIF(TRIM(e.title), ''), NULLIF(TRIM(a.title), ''), 'Verified Certificate') AS exam_title,
           COALESCE(NULLIF(TRIM(e.language_pair), ''), NULLIF(TRIM(a.exam_language), ''), c.language_pair) AS exam_language,
           COALESCE(NULLIF(TRIM(c.student_name), ''), NULLIF(TRIM(u.full_name), ''), 'Certificate Holder') AS holder_name
    FROM certificates c
    LEFT JOIN exams e ON e.id = c.exam_id
    LEFT JOIN institution_assessments a ON c.assessment_id = a.id
    LEFT JOIN users u ON c.user_id = u.user_id
    WHERE c.user_id = ?
    ORDER BY c.created_at DESC
");
$stmt->execute([$user_id]);
$allCerts = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

// Group by exam title and keep only the latest certificate for each title
$certificates = [];
$seenTitles = [];
foreach ($allCerts as $cert) {
    $examTitle = trim((string)($cert['exam_title'] ?? ''));
    if ($examTitle === '') {
        $examTitle = 'Verified Certificate';
    }
    if (!in_array($examTitle, $seenTitles, true)) {
        $certificates[] = $cert;
        $seenTitles[] = $examTitle;
    }
}

$stmt = $pdo->prepare("
    SELECT DATE(completed_at) as date, SUM(xp_earned) as xp
    FROM user_progress
    WHERE user_id = ? AND language_code = ? AND level_number = ?
      AND completed_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(completed_at)
    ORDER BY date
");
$stmt->execute([$user_id, $active_lang, $active_level]);
$xp_week = $stmt->fetchAll();

$xp_data = array_fill(0, 7, 0);
$days_translations = [
    'en' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
    'rw' => ['Mbe', 'Kab', 'Gat', 'Kan', 'Gtn', 'Gtd', 'Cyu'],
    'sw' => ['Jum', 'Nam', 'Jem', 'Pam', 'Wis', 'Jar', 'Ahs']
];
$days = $days_translations[$ui_lang] ?? $days_translations['en'];
foreach ($xp_week as $row) {
    $day_index = date('N', strtotime($row['date'])) - 1;
    $xp_data[$day_index] = (int)$row['xp'];
}

$content_dir = "../content/{$folder}/level{$active_level}/";
$topics = [];
if (is_dir($content_dir)) {
    foreach (scandir($content_dir) as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) !== 'yaml') continue;
        $name         = pathinfo($file, PATHINFO_FILENAME);
        $yaml_content = file_get_contents($content_dir . $file);
        if (function_exists('yaml_parse')) {
            $data           = yaml_parse($yaml_content);
            $display_name   = $data['lesson']['name'] ?? t($name);
            $total_exercises= count($data['exercises'] ?? []);
        } else {
            $display_name    = t($name);
            $total_exercises = substr_count($yaml_content, '- id:');
        }
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM user_progress
            WHERE user_id=? AND language_code=? AND level_number=? AND topic_file=? AND completed=1
        ");
        $stmt->execute([$user_id, $active_lang, $active_level, $file]);
        $completed = (int)$stmt->fetchColumn();
        $pct = $total_exercises > 0 ? round(($completed / $total_exercises) * 100) : 0;
        $topics[] = [
            'name'      => $display_name,
            'file'      => $file,
            'pct'       => $pct,
            'total'     => $total_exercises,
            'completed' => $completed,
            'icon'      => get_icon_for_topic($name)
        ];
    }
}

// Build roadmap data for all levels
$all_levels_topics = [];
for ($lvl = 1; $lvl <= 6; $lvl++) {
    $level_dir = "../content/{$folder}/level{$lvl}/";
    if (!is_dir($level_dir)) {
        continue;
    }
    $level_topics = [];
    foreach (scandir($level_dir) as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) !== 'yaml') continue;
        $name = pathinfo($file, PATHINFO_FILENAME);
        $yaml_content = file_get_contents($level_dir . $file);
        if (function_exists('yaml_parse')) {
            $data = yaml_parse($yaml_content);
            $display_name = $data['lesson']['name'] ?? t($name);
            $total_exercises = count($data['exercises'] ?? []);
            $topic_description = $data['lesson']['description'] ?? $data['description'] ?? '';
        } else {
            $display_name = t($name);
            $total_exercises = substr_count($yaml_content, '- id:');
            $topic_description = '';
            if (preg_match('/^\s{2}description:\s*["\']?(.*?)["\']?\s*$/mi', $yaml_content, $description_match)) {
                $topic_description = trim($description_match[1]);
            }
        }
        $topic_guide = [];
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_progress WHERE user_id=? AND language_code=? AND level_number=? AND topic_file=? AND completed=1");
        $stmt->execute([$user_id, $active_lang, $lvl, $file]);
        $completed = (int)$stmt->fetchColumn();
        $pct = $total_exercises > 0 ? round(($completed / $total_exercises) * 100) : 0;
        $level_topics[] = [
            'name'      => $display_name,
            'file'      => $file,
            'pct'       => $pct,
            'total'     => $total_exercises,
            'completed' => $completed,
            'icon'      => get_icon_for_topic($name),
            'level'     => $lvl,
            'description' => $topic_description,
            'guide_intro' => is_string($topic_guide['intro'] ?? null) ? $topic_guide['intro'] : ''
        ];
    }
    usort($level_topics, fn($a, $b) => strcmp($a['name'], $b['name']));
    $all_levels_topics[$lvl] = $level_topics;
}

// ----------------- Guidebook generator (server-side) -----------------
function extract_guidebook_from_yaml($yamlPath) {
    if (!file_exists($yamlPath)) return null;
    $content = file_get_contents($yamlPath);
    $guide = [
        'title' => null,
        'overview' => null,
        'guidebook' => null,
        'xp_reward' => null,
        'tables' => [],
        'grammar_tips' => [],
        'pronunciation_tips' => [],
        'examples' => [],
        'goal' => null,
        'resources' => [],
        'key_phrases' => []
    ];

    $extract_manual_guidebook = function($yamlText) {
        $lines = preg_split('/\r\n|\r|\n/', $yamlText);
        $guidebookLines = [];
        $inGuidebook = false;

        foreach ($lines as $line) {
            if (!$inGuidebook) {
                if (preg_match('/^guidebook:\s*\|/', $line)) {
                    $inGuidebook = true;
                    if (preg_match('/^guidebook:\s*\|\s*(.+)$/', $line, $m)) {
                        $inline = trim($m[1]);
                        if ($inline !== '') {
                            $guidebookLines[] = $inline;
                        }
                    }
                }
                continue;
            }

            if (preg_match('/^exercises:\s*$/', $line)) {
                break;
            }

            if (preg_match('/^[A-Za-z0-9_.-]+:\s*$/', $line) && !preg_match('/^\s/', $line)) {
                break;
            }

            if (preg_match('/^(\s{2,})(.*)$/', $line, $m)) {
                $guidebookLines[] = $m[2];
            } elseif (trim($line) === '') {
                $guidebookLines[] = '';
            } else {
                $guidebookLines[] = trim($line);
            }
        }

        $text = trim(implode("\n", $guidebookLines));
        return $text !== '' ? $text : null;
    };

    $manualGuidebook = $extract_manual_guidebook($content);

    $is_action_word = function($word) {
        return preg_match('/\b(eat|eats|drink|drinks|sleep|sleeps|run|runs|fly|flies|swim|swims|play|plays|build|builds)\b/i', $word);
    };
    $is_food_word = function($word) {
        return preg_match('/\b(grass|water|meat|food|milk|rice|bread|fruit|vegetable)s?\b/i', $word);
    };

    $animals = [];
    $actions = [];
    $foods = [];
    $sentence_examples = [];
    $vocabulary = [];

    if (function_exists('yaml_parse')) {
        $data = @yaml_parse($content);
        if (!$data || !is_array($data)) return null;
        $guide['title'] = $data['lesson']['name'] ?? $data['title'] ?? $guide['title'];
        $guide['overview'] = $data['lesson']['description'] ?? $data['description'] ?? $guide['overview'];
        $guide['guidebook'] = $manualGuidebook;
        if (empty($guide['guidebook']) && !empty($data['guidebook'])) {
            $guide['guidebook'] = $data['guidebook'];
        }
        if (empty($guide['guidebook']) && !empty($data['guidebook_text'])) {
            $guide['guidebook'] = $data['guidebook_text'];
        }
        if (empty($guide['guidebook']) && !empty($data['guidebook_content'])) {
            $guide['guidebook'] = $data['guidebook_content'];
        }
        if (empty($guide['overview']) && !empty($guide['guidebook'])) {
            $guide['overview'] = trim($guide['guidebook']);
        }
        $guide['xp_reward'] = $data['lesson']['xp_reward'] ?? null;

        if (!empty($data['exercises']) && is_array($data['exercises'])) {
            foreach ($data['exercises'] as $ex) {
                if (!is_array($ex)) continue;

                if (isset($ex['type']) && $ex['type'] === 'translation' && !empty($ex['question']) && !empty($ex['answer'])) {
                    if (preg_match('/How do you say \'(.+)\' in Kinyarwanda\?/i', $ex['question'], $m)) {
                        $english = trim($m[1]);
                        $kinya = trim($ex['answer']);
                        if ($english && $kinya) {
                            $pair = ['en' => $english, 'rw' => $kinya];
                            if ($is_action_word($english)) {
                                $actions[] = $pair;
                            } elseif ($is_food_word($english)) {
                                $foods[] = $pair;
                            } else {
                                $animals[] = $pair;
                            }
                        }
                    }
                }

                if (!empty($ex['question']) && !empty($ex['translation'])) {
                    $sentence_examples[] = trim($ex['question']);
                    $vocabulary[] = ['en' => trim($ex['translation']), 'rw' => trim($ex['question'])];
                }

                if (!empty($ex['prompt']) && !empty($ex['translation'])) {
                    $vocabulary[] = ['en' => trim($ex['translation']), 'rw' => trim($ex['prompt'])];
                }

                if (!empty($ex['front']) && !empty($ex['back'])) {
                    $vocabulary[] = ['en' => trim($ex['back']), 'rw' => trim($ex['front'])];
                }

                if (isset($ex['left']) && isset($ex['right'])) {
                    $pair = ['en' => trim($ex['left']), 'rw' => trim($ex['right'])];
                    if ($is_action_word($ex['left']) || $is_action_word($ex['right'])) {
                        $actions[] = $pair;
                    } else {
                        $vocabulary[] = $pair;
                    }
                }

                if (!empty($ex['type']) && in_array($ex['type'], ['read_answer','sentence_scramble','fill_blank','word_bank','story','conversation','pronunciation','listen_and_type','listen_and_choose','multiple_choice'])) {
                    if (!empty($ex['question'])) $sentence_examples[] = trim($ex['question']);
                    if (!empty($ex['text'])) $sentence_examples[] = trim($ex['text']);
                    if (!empty($ex['sentence'])) $sentence_examples[] = trim($ex['sentence']);
                    if (!empty($ex['correct_sentence'])) $sentence_examples[] = trim($ex['correct_sentence']);
                    if (!empty($ex['translation'])) $sentence_examples[] = trim($ex['translation']);
                }

                if (!empty($ex['choices']) && is_array($ex['choices'])) {
                    foreach ($ex['choices'] as $choice) {
                        if (!is_array($choice) && trim($choice) !== '') {
                            $guide['grammar_tips'][] = trim($choice);
                        }
                    }
                }

                if (!empty($ex['tts_text']) && !empty($ex['translation'])) {
                    $sentence_examples[] = trim($ex['translation']);
                }
            }
        }

        if (!empty($data['flashcards']) && is_array($data['flashcards'])) {
            foreach ($data['flashcards'] as $card) {
                if (!empty($card['front']) && !empty($card['back'])) {
                    $vocabulary[] = ['en' => trim($card['back']), 'rw' => trim($card['front'])];
                }
            }
        }

        if (!empty($data['resources']) && is_array($data['resources'])) {
            foreach ($data['resources'] as $r) {
                if (is_array($r)) {
                    $guide['resources'][] = $r['url'] ?? $r['title'] ?? implode(' ', $r);
                } else {
                    $guide['resources'][] = $r;
                }
            }
        }
    } else {
        $parts = preg_split('/\r?\n\r?\n/', trim($content));
        if (!empty($parts)) $guide['overview'] = strip_tags($parts[0]);
        foreach (preg_split('/\r?\n/', $content) as $line) {
            $line = trim($line);
            if (preg_match('/^-\s*(.+)$/', $line, $m)) {
                $val = trim($m[1]);
                if (!preg_match('/^id:\s*\d+$/i', $val)) $sentence_examples[] = $val;
            }
        }
    }

    $unique_pairs = function(array $pairs) {
        $seen = [];
        $out = [];
        foreach ($pairs as $pair) {
            if (!is_array($pair) || empty($pair['en']) || empty($pair['rw'])) continue;
            $key = strtolower(trim($pair['en'])) . '|' . strtolower(trim($pair['rw']));
            if (isset($seen[$key])) continue;
            $seen[$key] = true;
            $out[] = ['en' => trim($pair['en']), 'rw' => trim($pair['rw'])];
        }
        return $out;
    };

    $animals = $unique_pairs($animals);
    $actions = $unique_pairs($actions);
    $foods = $unique_pairs($foods);
    $vocabulary = $unique_pairs($vocabulary);

    if (!empty($animals)) {
        $guide['tables'][] = ['title' => 'Animal Vocabulary', 'columns' => ['English', 'Kinyarwanda'], 'rows' => $animals];
    }
    if (!empty($actions)) {
        $guide['tables'][] = ['title' => 'Animal Actions', 'columns' => ['English', 'Kinyarwanda'], 'rows' => $actions];
    }
    if (!empty($foods)) {
        $guide['tables'][] = ['title' => 'Animal Food', 'columns' => ['English', 'Kinyarwanda'], 'rows' => $foods];
    }
    if (!empty($vocabulary)) {
        $guide['tables'][] = ['title' => 'Additional Vocabulary', 'columns' => ['English', 'Kinyarwanda'], 'rows' => $vocabulary];
    }

    $guide['examples'] = array_values(array_unique(array_filter(array_map('trim', $sentence_examples))));
    if (count($guide['examples']) > 15) {
        $guide['examples'] = array_slice($guide['examples'], 0, 15);
    }

    if (empty($guide['overview']) && !empty($guide['title'])) {
        $guide['overview'] = sprintf('This unit introduces %s vocabulary and practical exercises using real lesson content.', $guide['title']);
    }

    if (empty($guide['grammar_tips'])) {
        $guide['grammar_tips'][] = 'Simple Kinyarwanda statements often place the noun before the verb: subject + verb + object.';
        if (!empty($actions)) {
            $guide['grammar_tips'][] = 'Actions in this unit include: ' . implode(', ', array_unique(array_map(function($p){ return $p['en']; }, $actions))) . '.';
        }
    }

    if (empty($guide['pronunciation_tips'])) {
        $guide['pronunciation_tips'][] = 'Pronounce each Kinyarwanda word clearly: vowels are generally pure sounds and consonants are distinct.';
        $guide['pronunciation_tips'][] = 'Pay attention to consonant clusters like "nk", "ng", and "ny" when saying words such as Inka, Ingagi, and Inyoni.';
    }

    $guide['key_phrases'] = array_values(array_unique(array_filter(array_merge(
        array_slice(array_column($animals, 'en'), 0, 3),
        array_slice(array_column($actions, 'en'), 0, 3),
        array_slice(array_column($foods, 'en'), 0, 3)
    ))));

    $goalParts = [];
    if (!empty($animals)) $goalParts[] = 'learn '.count($animals).' animal words';
    if (!empty($actions)) $goalParts[] = 'use basic action verbs';
    if (!empty($foods)) $goalParts[] = 'name common animal food';
    $guide['goal'] = $goalParts ? 'By the end of this unit, you will be able to '.implode(', ', $goalParts).'.' : null;

    if ($guide['xp_reward']) {
        $guide['goal'] .= ' Total XP available: ' . $guide['xp_reward'] . '.';
    }

    return $guide;
}

// Build guidebooks for all roadmap topics (used by client-side renderer)
$guidebooks = [];
foreach ($all_levels_topics as $lvl => $topicsArr) {
    foreach ($topicsArr as $t) {
        $path = "../content/{$folder}/level{$lvl}/" . $t['file'];
        $gb = extract_guidebook_from_yaml($path);
        if ($gb) {
            if (!isset($guidebooks[$lvl])) $guidebooks[$lvl] = [];
            $guidebooks[$lvl][$t['file']] = $gb;
        }
    }
}

// ========== LOAD BUSINESS ENGLISH COURSES ==========
$business_english_courses = [];
$be_levels = [
    1 => ['name' => 'Foundations', 'icon' => '🤝', 'file' => 'foundations.yaml'],
    2 => ['name' => 'Workplace Communication', 'icon' => '☎️', 'file' => 'workplace-communication.yaml'],
    3 => ['name' => 'Professional Emails', 'icon' => '📧', 'file' => 'emails.yaml'],
    4 => ['name' => 'Meetings & Discussions', 'icon' => '🗣️', 'file' => 'meetings.yaml'],
    5 => ['name' => 'Presentations', 'icon' => '📊', 'file' => 'presentations.yaml'],
    6 => ['name' => 'Sales & Marketing', 'icon' => '📈', 'file' => 'Sales.yaml'],
    7 => ['name' => 'Customer Service', 'icon' => '😊', 'file' => 'Customer_Service.yaml'],
    8 => ['name' => 'Negotiations', 'icon' => '🤜', 'file' => 'Business_Negotiation.yaml'],
    9 => ['name' => 'Human Resources', 'icon' => '👥', 'file' => 'Human_Resources.yaml'],
    10 => ['name' => 'Finance & Accounting', 'icon' => '💰', 'file' => 'Finance_and_Accounting.yaml'],
    11 => ['name' => 'International Business', 'icon' => '🌍', 'file' => 'InternationalBusiness.yaml'],
    12 => ['name' => 'Entrepreneurship', 'icon' => '🚀', 'file' => 'EntrepreneurshipandStartups.yaml'],
    13 => ['name' => 'Management & Leadership', 'icon' => '👔', 'file' => 'ManagementandLeadership.yaml'],
    14 => ['name' => 'Advanced Business', 'icon' => '🎓', 'file' => 'AdvancedBusiness.yaml'],
    15 => ['name' => 'Real-World Business', 'icon' => '🏆', 'file' => 'Real-World_Business.yaml'],
];

foreach ($be_levels as $level => $course_info) {
    $be_file = "../content/BUSINESS-ENGLISH/level{$level}/" . $course_info['file'];
    $total_exercises = 0;
    
    if (file_exists($be_file)) {
        $yaml_content = file_get_contents($be_file);
        if (function_exists('yaml_parse')) {
            $data = yaml_parse($yaml_content);
            $total_exercises = count($data['exercises'] ?? []);
        } else {
            $total_exercises = substr_count($yaml_content, '- id:');
        }
    }
    
    // Get completed exercises for this Business English course
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM user_progress
        WHERE user_id=? AND language_code=? AND level_number=? AND topic_file=? AND completed=1
    ");
    $stmt->execute([$user_id, 'business-english', $level, $course_info['file']]);
    $completed = (int)$stmt->fetchColumn();
    
    $pct = $total_exercises > 0 ? round(($completed / $total_exercises) * 100) : 0;
    
    $business_english_courses[] = [
        'name'      => $course_info['name'],
        'file'      => $course_info['file'],
        'level'     => $level,
        'pct'       => $pct,
        'total'     => $total_exercises,
        'completed' => $completed,
        'icon'      => $course_info['icon']
    ];
}

$stmt = $pdo->prepare("
    SELECT up.level_number, up.topic_file,
           COUNT(up.id)       as exercises_done,
           SUM(up.xp_earned)  as xp_earned,
           MAX(up.completed_at) as last_attempt
    FROM user_progress up
    WHERE up.user_id=? AND up.language_code=? AND up.level_number=? AND up.completed=1
    GROUP BY up.level_number, up.topic_file
    ORDER BY last_attempt DESC LIMIT 3
");
$stmt->execute([$user_id, $active_lang, $active_level]);
$recent_lessons = $stmt->fetchAll();
foreach ($recent_lessons as &$lesson) {
    $lesson['lesson_name'] = get_lesson_name_safe($folder, $lesson['level_number'], $lesson['topic_file']);
}
unset($lesson);

$stmt = $pdo->prepare("
    SELECT session_date, exercises_completed FROM user_sessions
    WHERE user_id=? AND session_date >= DATE_SUB(CURDATE(), INTERVAL 91 DAY)
    ORDER BY session_date
");
$stmt->execute([$user_id]);
$activity_map = [];
foreach ($stmt->fetchAll() as $row) {
    $c = $row['exercises_completed'];
    $activity_map[$row['session_date']] = $c > 20 ? 3 : ($c > 10 ? 2 : ($c > 0 ? 1 : 0));
}

$stmt = $pdo->prepare("
    SELECT a.*, CASE WHEN ua.achievement_id IS NOT NULL THEN 1 ELSE 0 END as earned, ua.earned_at
    FROM achievements a
    LEFT JOIN user_achievements ua ON ua.achievement_id=a.id AND ua.user_id=?
    ORDER BY a.criteria_value LIMIT 4
");
$stmt->execute([$user_id]);
$achievements = $stmt->fetchAll();
if (empty($achievements)) {
    $achievements = [
        ['id'=>1,'name'=>'First Steps',    'earned'=>($stats['total_exercises']??0)>=1  ?1:0,'icon'=>'👣'],
        ['id'=>2,'name'=>'Getting Started','earned'=>($stats['total_exercises']??0)>=10 ?1:0,'icon'=>'🌱'],
        ['id'=>3,'name'=>'Week Warrior',   'earned'=>($streak['current_streak']??0)>=7  ?1:0,'icon'=>'🔥'],
        ['id'=>4,'name'=>'Word Collector', 'earned'=>($stats['words_learned']??0)>=5    ?1:0,'icon'=>'📝']
    ];
}

$current_level_num = floor(($stats['total_xp'] ?? 0) / 1000) + 1;
$next_level_xp     = $current_level_num * 1000;
$xp_progress       = ($stats['total_xp'] ?? 0) % 1000;
$xp_percent        = round(($xp_progress / 1000) * 100);

// Guidebook is temporarily disabled.
$guidebookHTML = '';
$guidebookHTMLMap = [];
$selectedGuidebookTopic = '';
$selectedGuidebookName = '';
?>
<!DOCTYPE html>
<html lang="<?= $ui_lang ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Playmates · <?= htmlspecialchars($current_lang_name) ?> <?= t('level') ?> <?= $active_level ?></title>
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons/css/flag-icons.min.css">

<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{
  --sand:#f5ede6;--sand-mid:#ede3d8;--sand-dark:#ddd0c2;
  --sage:#c6dfd5;--sage-mid:#8bbfad;--sage-dark:#3a7a68;--sage-deep:#225548;
  --lavender:#d4cfed;--butter:#f5e8b0;--peach:#f5d6c4;--blush:#f9d8cd;
  --ink:#1a1a18;--ink-mid:#4a4845;--muted:#8a8178;--soft:#b8b0a5;
  --surface:#faf8f5;--border:rgba(0,0,0,0.07);
  --radius-sm:8px;--radius:14px;--radius-lg:20px;
  --sidebar-w:228px;--sidebar-col:64px;--pad:16px;
  --transition:0.25s cubic-bezier(.4,0,.2,1);
}
html,body{height:100%;font-family:'DM Sans',sans-serif;font-size:14px;color:var(--ink);background:var(--sand)}
.dash{display:grid;grid-template-columns:var(--sidebar-w) 1fr;height:100vh;min-height:600px;transition:grid-template-columns var(--transition)}
.dash.collapsed{grid-template-columns:var(--sidebar-col) 1fr}
.sidebar{background:var(--sage-deep);display:flex;flex-direction:column;overflow-y:auto;overflow-x:hidden;position:relative;width:var(--sidebar-w);flex-shrink:0;transition:width var(--transition)}
.dash.collapsed .sidebar{width:var(--sidebar-col)}
.sidebar-toggle{position:absolute;top:22px;right:-13px;width:26px;height:26px;border-radius:50%;background:var(--sage-deep);border:2px solid rgba(255,255,255,0.2);color:rgba(255,255,255,0.7);font-size:11px;cursor:pointer;display:flex;align-items:center;justify-content:center;z-index:20;transition:transform var(--transition)}
.sidebar-toggle:hover{border-color:rgba(255,255,255,0.5)}
.dash.collapsed .sidebar-toggle{transform:rotate(180deg)}
.brand{padding:22px 18px 16px;border-bottom:1px solid rgba(255,255,255,0.08);overflow:hidden;white-space:nowrap;display:flex;flex-direction:column;transition:padding var(--transition)}
.dash.collapsed .brand{padding:16px 0;align-items:center}
.brand-name{font-family:'DM Serif Display',serif;font-size:20px;color:#fff;display:flex;align-items:center;gap:7px}
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
.nav-item.logout-item{margin-top:auto;border-top:1px solid rgba(255,255,255,0.08);border-radius:0;padding-top:14px;margin-top:12px}
.nav-item.logout-item:hover{background:rgba(255,100,100,0.2)}
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
.start-btn{font-size:12px;font-weight:500;background:var(--ink);color:#fff;border:none;border-radius:30px;padding:7px 16px;cursor:pointer;font-family:'DM Sans',sans-serif;transition:opacity 0.15s;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
.start-btn:hover{opacity:0.85}
.btn-primary{background:var(--sage-dark);color:#fff;border:none;padding:8px 16px;border-radius:40px;font-family:'DM Sans',sans-serif;font-size:13px;font-weight:500;cursor:pointer;display:inline-flex;align-items:center;gap:6px;transition:background 0.14s,transform 0.1s;text-decoration:none}
.btn-primary:hover{background:#2a5e4e;transform:translateY(-1px)}
.btn-outline{background:transparent;border:1px solid var(--sage-mid);color:var(--sage-deep);padding:7px 14px;border-radius:40px;font-family:'DM Sans',sans-serif;font-size:13px;font-weight:500;cursor:pointer;display:inline-flex;align-items:center;gap:5px;transition:background 0.14s}
.btn-outline:hover{background:var(--sage)}
.map-float-btn{position:fixed;right:20px;bottom:20px;z-index:1200;height:52px;display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:0 16px;border-radius:999px;border:1px solid var(--sage-mid);background:var(--surface);color:var(--ink);font-size:14px;font-weight:600;box-shadow:0 18px 40px rgba(10,15,10,0.15);cursor:pointer;transition:transform 0.2s ease,box-shadow 0.2s ease,background 0.2s ease}
.map-float-btn:hover{transform:translateY(-2px) scale(1.04);background:var(--sage);color:#fff}
@media(max-width:768px){.map-float-btn{right:14px;bottom:14px;height:46px;padding:0 13px;font-size:13px}}
.metrics{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:16px}
.metric{background:var(--surface);border-radius:var(--radius);padding:16px;border:1px solid var(--border)}
.metric-lbl{font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:6px}
.metric-val{font-family:'DM Serif Display',serif;font-size:28px;line-height:1;letter-spacing:-0.5px}
.metric-sub{font-size:10px;margin-top:5px;font-weight:400}
.m-green .metric-lbl,.m-green .metric-sub{color:var(--sage-dark)}.m-green .metric-val{color:var(--sage-dark)}
.m-amber .metric-lbl,.m-amber .metric-sub{color:#7a5c1e}.m-amber .metric-val{color:#a07820}
.m-coral .metric-lbl,.m-coral .metric-sub{color:#7a3020}.m-coral .metric-val{color:#b04428}
.m-purple .metric-lbl,.m-purple .metric-sub{color:#4a3e9a}.m-purple .metric-val{color:#6860c0}
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
.outline-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:14px}
.outline-item{display:flex;align-items:flex-start;gap:12px;padding:14px;background:var(--sand);border-radius:16px;border:1px solid var(--border)}
.outline-icon{width:42px;height:42px;border-radius:14px;background:var(--sage);display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0}
.outline-title{font-size:13px;font-weight:600;color:var(--ink);margin-bottom:4px}
.outline-desc{font-size:12px;color:var(--muted);line-height:1.5}
@media(max-width:768px){.outline-grid{grid-template-columns:1fr}} 
.hm-cell{aspect-ratio:1;border-radius:2px}
.achievements{display:grid;grid-template-columns:repeat(4,1fr);gap:10px}
.badge-item{display:flex;flex-direction:column;align-items:center;gap:5px}
.badge-icon{width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:18px}
.badge-lbl{font-size:9px;font-weight:500;text-align:center;color:var(--muted);line-height:1.3}
.badge-item.earned .badge-lbl{color:var(--ink)}
.badge-item.locked .badge-icon{filter:grayscale(1);opacity:0.3}
/* ── Certificate cards ── */
.cert-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px;margin-top:16px}
.cert-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:18px;display:flex;flex-direction:column;gap:12px;min-height:280px}
.cert-card-wrapper{position:relative;width:100%;height:140px;border-radius:14px;overflow:hidden;border:1px solid var(--border)}
.cert-card img{width:100%;height:100%;object-fit:cover;border-radius:0;border:none;background:#fff}
.cert-card-watermark{position:absolute;top:0;left:0;right:0;bottom:0;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.05);z-index:10;pointer-events:none}
.cert-card-watermark-text{font-size:32px;font-weight:900;color:rgba(255,0,0,0.3);text-transform:uppercase;letter-spacing:3px;transform:rotate(-45deg);text-align:center;line-height:1.2}
.cert-card-name-overlay{position:absolute;left:10px;right:10px;bottom:10px;z-index:12;padding:8px 10px;border-radius:10px;background:rgba(15,25,20,0.78);color:#fff;font-size:12px;font-weight:600;line-height:1.35;box-shadow:0 8px 20px rgba(15,25,20,0.18);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.cert-card h4{font-size:15px;color:var(--sage-deep);font-weight:600}
.cert-meta{font-size:12px;color:var(--muted);line-height:1.6;flex:1}
.cert-actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:10px}
.cert-actions .btn-outline,.cert-actions .btn-primary{white-space:nowrap}
@media(max-width:1080px){.cert-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:720px){.cert-grid{grid-template-columns:1fr}}
.bottom-grid{display:grid;grid-template-columns:2fr 1fr;gap:12px}
.recent-list{display:flex;flex-direction:column;gap:8px}
.recent-row{display:flex;align-items:center;gap:9px;padding:9px 11px;background:var(--sand);border-radius:var(--radius-sm)}
.recent-icon{width:32px;height:32px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0}
.recent-info{flex:1}
.recent-name{font-size:12px;font-weight:500;color:var(--ink)}
.recent-meta{font-size:10px;color:var(--muted);margin-top:1px}
.recent-xp{font-family:'DM Serif Display',serif;font-size:15px;color:var(--sage-dark)}
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
.lang-pick-card.is-current{border-color:var(--sage-dark);background:var(--sage)}
.lang-pick-name{font-size:13px;font-weight:500;color:var(--ink)}
.lang-pick-desc{font-size:10px;color:var(--muted)}
.lang-pick-added{font-size:10px;color:var(--sage-dark);font-weight:600}
.modal-note{font-size:10px;color:var(--muted);text-align:center;margin-top:14px;line-height:1.6}
.mob-topbar{display:none;position:fixed;top:0;left:0;right:0;z-index:90;background:var(--sage-deep);align-items:center;justify-content:space-between;padding:0 14px;height:52px;box-shadow:0 1px 0 rgba(255,255,255,0.05),0 2px 10px rgba(0,0,0,0.15)}
.mob-flag-trigger{display:flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:50%;background:none;cursor:pointer;font-size:20px;flex-shrink:0}
.mob-topbar-brand{font-family:'DM Serif Display',serif;font-size:17px;color:#fff;display:flex;align-items:center;gap:7px;flex:1;justify-content:center}
.mob-topbar-brand img{width:22px;height:22px;object-fit:contain;border-radius:4px}
.mob-hamburger{display:flex;flex-direction:column;justify-content:center;gap:5px;cursor:pointer;padding:7px;flex-shrink:0;background:rgba(255,255,255,0.08);border:1.5px solid rgba(255,255,255,0.15);border-radius:9px;width:38px;height:38px;align-items:center}
.mob-hamburger span{display:block;height:1.5px;background:rgba(255,255,255,0.85);border-radius:2px}
.mob-hamburger span:nth-child(1){width:16px}
.mob-hamburger span:nth-child(2){width:12px}
.mob-hamburger span:nth-child(3){width:8px}
.mob-overlay{display:none;position:fixed;inset:0;background:rgba(26,26,24,0.45);z-index:199}
.mob-overlay.open{display:block}
.mob-sidebar{position:fixed;top:0;left:0;bottom:0;width:270px;max-width:82vw;background:var(--sage-deep);z-index:200;display:flex;flex-direction:column;transform:translateX(-100%);transition:transform 0.28s cubic-bezier(.4,0,.2,1);overflow-y:auto;overflow-x:hidden}
.mob-sidebar.open{transform:translateX(0)}
.mob-sidebar-close{position:absolute;top:16px;right:14px;background:rgba(255,255,255,0.1);border:none;border-radius:50%;width:28px;height:28px;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,0.7)}
.mob-sidebar-section{padding:12px 14px;border-top:1px solid rgba(255,255,255,0.08)}
.mob-sidebar-section-lbl{font-size:9px;font-weight:500;text-transform:uppercase;letter-spacing:1.2px;color:rgba(255,255,255,0.3);margin-bottom:8px}
.mob-lang-rows{display:flex;flex-direction:column;gap:2px}
.mob-lang-row{display:flex;align-items:center;gap:9px;padding:7px 9px;border-radius:var(--radius-sm);cursor:pointer;background:transparent;width:100%;text-align:left}
.mob-lang-row.active{background:rgba(255,255,255,0.12)}
.mob-lang-row-name{font-size:13px;color:rgba(255,255,255,0.55)}
.mob-level-grid{display:flex;gap:5px;flex-wrap:wrap}
.mob-level-tile{display:flex;align-items:center;gap:4px;padding:5px 10px;border-radius:var(--radius-sm);background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);color:rgba(255,255,255,0.4);font-size:11px;cursor:pointer;text-decoration:none}
.mob-level-tile.active{background:var(--sage-dark);border-color:var(--sage-dark);color:#fff}
.mob-nav-list{padding:10px 10px 4px;display:flex;flex-direction:column;gap:1px}
.mob-nav-section-lbl{font-size:9px;font-weight:500;text-transform:uppercase;letter-spacing:1.3px;color:rgba(255,255,255,0.28);padding:0 8px;margin:8px 0 4px}
.mob-nav-item{display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:var(--radius-sm);cursor:pointer;border:none;background:transparent;width:100%;text-align:left;font-family:'DM Sans',sans-serif}
.mob-nav-item.active{background:rgba(255,255,255,0.13)}
.mob-nav-icon{width:30px;height:30px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.mob-nav-lbl{font-size:13px;font-weight:400;color:rgba(255,255,255,0.6)}
.mob-nav-item.active .mob-nav-lbl{color:#fff;font-weight:500}
.mob-nav-cnt{margin-left:auto;background:var(--peach);color:#7a3020;font-size:9px;font-weight:700;padding:2px 7px;border-radius:20px}
@media(max-width:768px){
  .sidebar,.topbar-right .lang-selector-group:first-child,.date-pill{display:none}
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
  .mob-sidebar .sidebar-footer{opacity:1!important;height:auto!important;padding:16px!important;display:block!important}
}

/* ===== ROADMAP REDESIGN: VERTICAL PROGRESS SPINE ===== */

/* Main container */
.roadmap-container {
  background: var(--surface);
  border-radius: var(--radius-lg);
  border: 1px solid var(--border);
  padding: 32px 32px 32px 24px;
  margin-top: 8px;
  overflow-x: auto;
  position: relative;
  box-shadow: 0 10px 30px rgba(26, 26, 24, 0.04);
  max-width: 1180px;
  width: 100%;
  margin: 0 auto;
}

/* Roadmap scroll - vertical spine container */
.roadmap-scroll {
  min-width: 100%;
  display: flex;
  flex-direction: column;
  position: relative;
  gap: 0;
  padding-left: 24px;
}

/* Vertical spine line (the backbone of the roadmap) */
.roadmap-scroll::before {
  content: '';
  position: absolute;
  left: 11px;
  top: 0;
  bottom: 0;
  width: 2px;
  background: linear-gradient(180deg, var(--sage-mid) 0%, var(--sage) 50%, var(--sand-mid) 100%);
  z-index: 1;
}

/* Level row - each level is a horizontal section */
.roadmap-row-level {
  display: flex;
  align-items: stretch;
  gap: 32px;
  padding: 28px 0 28px 0;
  position: relative;
  border-bottom: none;
  flex-wrap: nowrap;
}

.roadmap-row-level:last-child {
  padding-bottom: 0;
}

/* Level checkpoint on the spine */
.roadmap-row-level::before {
  content: '';
  position: absolute;
  left: 4px;
  top: 50%;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: var(--surface);
  border: 3px solid var(--sage-mid);
  transform: translateY(-50%);
  z-index: 2;
  box-shadow: 0 0 0 3px var(--surface);
}

/* Level label (left side) */
.level-label {
  min-width: 110px;
  display: flex;
  flex-direction: column;
  gap: 5px;
  margin-top: 0;
  flex-shrink: 0;
}

.level-chip {
  font-family: 'DM Serif Display', serif;
  font-size: 16px;
  font-weight: 700;
  color: var(--ink);
  letter-spacing: -0.3px;
  line-height: 1.1;
}

.level-meta {
  font-size: 11px;
  color: var(--sage-dark);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Lessons container (lessons flow horizontally for each level) */
.roadmap-nodes {
  display: flex;
  flex-wrap: wrap;
  gap: 14px;
  align-items: flex-start;
  flex: 1;
  justify-content: flex-start;
}

/* Individual lesson card */
.roadmap-node {
  display: flex;
  flex-direction: column;
  gap: 8px;
  cursor: pointer;
  transition: transform 0.18s cubic-bezier(.4,0,.2,1), box-shadow 0.18s ease;
  min-width: 140px;
  position: relative;
  flex-shrink: 0;
}

.roadmap-node:hover:not(.disabled) {
  transform: translateY(-4px);
}

.roadmap-node.selected {
  background: rgba(76, 217, 100, 0.12);
  border: 1px solid #52d000;
  box-shadow: 0 10px 25px rgba(45, 199, 0, 0.14);
}

.roadmap-node.selected .node-circle {
  box-shadow: 0 12px 28px rgba(45, 199, 0, 0.18);
}

.roadmap-node.disabled {
  opacity: 0.4;
  pointer-events: none;
}

/* Topic introduction overlay */
.topic-intro-overlay {
  display: none;
  position: fixed;
  inset: 0;
  z-index: 1100;
  background: rgba(26, 26, 24, 0.5);
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.topic-intro-overlay.active {
  display: flex;
}

.topic-intro-card {
  position: relative;
  width: min(520px, 100%);
  max-height: calc(100vh - 40px);
  overflow: auto;
  background: var(--sage-deep);
  border-radius: var(--radius-lg);
  padding: 32px 26px 28px;
  color: #fff;
  box-shadow: 0 30px 70px rgba(34, 85, 72, 0.24);
}
.topic-intro-card::before {
  content: '';
  position: absolute;
  top: -12px;
  left: 50%;
  width: 24px;
  height: 24px;
  background: var(--sage-deep);
  transform: translateX(-50%) rotate(45deg);
  border-radius: 4px;
}

.topic-intro-close {
  position: absolute;
  top: 16px;
  right: 16px;
  border: 0;
  background: rgba(255, 255, 255, 0.15);
  color: #fff;
  border-radius: 50%;
  width: 34px;
  height: 34px;
  font-size: 22px;
  line-height: 32px;
  cursor: pointer;
  transition: background 0.15s ease;
}

.topic-intro-close:hover {
  background: rgba(255, 255, 255, 0.25);
}

.topic-intro-badge {
  width: 116px;
  height: 116px;
  margin: -6px auto 20px;
  border: 10px solid rgba(255, 255, 255, 0.7);
  border-top-color: var(--butter);
  border-right-color: var(--butter);
  border-radius: 50%;
  background: #fff;
  padding: 10px;
}

.topic-intro-star {
  width: 100%;
  height: 100%;
  display: grid;
  place-items: center;
  border-radius: 50%;
  background: var(--sage-deep);
  box-shadow: inset 0 -10px rgba(34, 85, 72, 0.3);
  font-size: 44px;
  color: #fff;
}

.topic-intro-title {
  font-size: 30px;
  line-height: 1.05;
  font-weight: 800;
  margin: 0 0 6px;
  font-family: 'DM Serif Display', serif;
  letter-spacing: -0.5px;
}

.topic-intro-subtitle {
  font-size: 14px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  color: rgba(255, 255, 255, 0.85);
  margin: 0 0 16px;
}

.topic-intro-description {
  font-size: 16px;
  line-height: 1.6;
  margin: 0 0 20px;
  color: rgba(255, 255, 255, 0.95);
}

.topic-intro-guide {
  margin: 0 0 22px;
  padding: 18px 18px 16px;
  border-radius: var(--radius);
  background: rgba(255, 255, 255, 0.12);
  line-height: 1.6;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.topic-intro-guide strong {
  display: block;
  margin-bottom: 8px;
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--butter);
}

.topic-intro-guide span {
  display: block;
  color: rgba(255, 255, 255, 0.93);
  font-size: 14px;
}

.topic-intro-start {
  display: block;
  width: 100%;
  border: none;
  border-radius: var(--radius-lg);
  padding: 18px 24px;
  background: #fff;
  color: var(--sage-deep);
  font-size: 17px;
  font-weight: 900;
  letter-spacing: 0.02em;
  text-transform: uppercase;
  font-family: 'DM Sans', sans-serif;
  box-shadow: 0 10px 0 rgba(34, 85, 72, 0.2);
  cursor: pointer;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.topic-intro-start:hover {
  transform: translateY(-1px);
  box-shadow: 0 12px 0 rgba(34, 85, 72, 0.28);
}

/* Lesson card wrapper */
.node-circle {
  width: 100%;
  aspect-ratio: 1;
  border-radius: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 32px;
  line-height: 1;
  background: var(--sand-dark);
  border: 2px solid var(--border);
  transition: all 0.18s cubic-bezier(.4,0,.2,1);
  box-shadow: 6px 8px 20px rgba(26, 26, 24, 0.08);
  position: relative;
  overflow: hidden;
}

/* Completed lesson - sage palette */
.node-circle.completed {
  background: var(--sage-mid);
  border-color: var(--sage-dark);
  box-shadow: 6px 8px 24px rgba(58, 122, 104, 0.14);
}

/* In-progress lesson - butter accent with gentle animation */
.node-circle.in-progress {
  background: var(--butter);
  border-color: #d4a84c;
  animation: subtle-glow 2.8s ease-in-out infinite;
  box-shadow: 6px 8px 24px rgba(212, 184, 76, 0.12);
}

/* Locked lesson - muted and reduced */
.node-circle.locked {
  background: var(--sand);
  border-color: var(--border);
}

/* Checkmark badge for completed */
.node-circle .checkmark {
  position: absolute;
  top: -1px;
  right: -1px;
  background: var(--sage-dark);
  color: #fff;
  border-radius: 0;
  width: 28px;
  height: 28px;
  font-size: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  box-shadow: -2px 2px 6px rgba(0, 0, 0, 0.12);
}

/* Lesson label */
.node-label {
  font-size: 12px;
  font-weight: 500;
  text-align: center;
  color: var(--ink);
  line-height: 1.3;
  word-break: break-word;
  font-family: 'DM Sans', sans-serif;
}

/* Lesson metadata (completion %) */
.node-meta {
  font-size: 10px;
  color: var(--muted);
  font-weight: 600;
  text-align: center;
  text-transform: uppercase;
  letter-spacing: 0.4px;
}

/* Remove connector lines - they're not needed with the spine */
.connector-line {
  display: none;
}

/* Map legend */
.map-legend {
  display: flex;
  flex-wrap: wrap;
  gap: 18px;
  margin-top: 28px;
  padding-top: 20px;
  border-top: 1px solid var(--border);
}

.map-legend-item {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 12px;
  color: var(--ink-mid);
  font-weight: 500;
}

.legend-dot {
  width: 16px;
  height: 16px;
  border-radius: 0;
  border: 2px solid transparent;
  flex-shrink: 0;
}

.legend-dot.completed {
  background: var(--sage-mid);
  border-color: var(--sage-dark);
}

.legend-dot.in-progress {
  background: var(--butter);
  border-color: #d4a84c;
}

.legend-dot.locked {
  background: var(--sand-dark);
  border-color: var(--border);
}

/* Gentle pulse animation for in-progress */
@keyframes subtle-glow {
  0%, 100% {
    box-shadow: 6px 8px 24px rgba(212, 184, 76, 0.12);
  }
  50% {
    box-shadow: 6px 8px 32px rgba(212, 184, 76, 0.24);
  }
}

/* Map section styles (the full-screen overlay) */
.card.map-section {
  position: fixed;
  inset: 0;
  z-index: 500;
  background: var(--sand) !important;
  border-radius: 0 !important;
  border: none;
  transform: translateY(-100%);
  transition: transform 0.45s cubic-bezier(.4,0,.2,1);
  overflow-y: auto;
  padding: 20px 24px 32px;
  display: flex;
  flex-direction: column;
  box-shadow: none;
}

.card.map-section.open {
  transform: translateY(0);
}

.card.map-section .map-section-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  max-width: 1180px;
  width: 100%;
  margin: 0 auto 20px;
  padding: 16px 18px;
  border: 1px solid var(--border);
  border-radius: 0;
  background: var(--surface) !important;
  backdrop-filter: blur(10px);
  box-shadow: 4px 6px 16px rgba(26, 26, 24, 0.06);
}

.card.map-section .map-section-head > div:first-child {
  flex: 1 1 auto;
  min-width: 0;
}

.card.map-section .map-section-sub {
  font-size: 12px;
  color: var(--muted);
  margin-top: 6px;
  font-weight: 400;
}

.card.map-section .map-pair-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  align-items: stretch;
  gap: 8px;
  margin-top: 14px;
  max-width: 100%;
  max-height: 122px;
  overflow-y: auto;
  overflow-x: hidden;
  padding-right: 4px;
  scrollbar-gutter: stable;
}

.card.map-section .map-pair-list .lang-pill {
  width: 100%;
  min-width: 0;
  font-size: 13px;
  padding: 11px 14px;
  border-radius: 0;
  border: 1px solid var(--border);
  background: var(--surface) !important;
  color: var(--ink-mid);
  transition: all 0.15s ease;
  font-weight: 500;
  display: inline-flex;
  align-items: center;
  gap: 5px;
}

.card.map-section .map-pair-list .lang-pill:hover {
  border-color: var(--sage-mid);
  background: var(--sage) !important;
  transform: translateY(-1px);
  box-shadow: 2px 4px 12px rgba(58, 122, 104, 0.08);
}

.card.map-section .map-pair-list .lang-pill.active {
  background: var(--sage-mid) !important;
  color: #fff;
  border-color: var(--sage-dark);
  font-weight: 600;
}

.card.map-section .map-pair-list .map-pair-extra {
  display: none;
}

/* Guidebook Modal Styles */
.guidebook-modal-overlay {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(26, 26, 24, 0.6);
  z-index: 9999;
  align-items: center;
  justify-content: center;
  padding: 16px;
}

.guidebook-modal-overlay.active {
  display: flex;
}

.guidebook-modal {
  background: var(--surface);
  color: var(--ink);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  width: 100%;
  max-width: 450px;
  max-height: 85vh;
  overflow-y: auto;
  position: relative;
  box-shadow: 0 24px 60px rgba(26, 26, 24, 0.16);
}

.guidebook-modal-header {
  position: sticky;
  top: 0;
  background: var(--surface);
  padding: 12px 16px;
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  z-index: 10;
}

.guidebook-modal-header h1 {
  font-size: 16px;
  font-weight: 600;
  margin: 0;
  flex: 1;
  text-align: center;
  color: var(--ink);
  font-family: 'DM Serif Display', serif;
}

.guidebook-modal-close {
  background: var(--sand-mid);
  border: none;
  color: var(--ink);
  font-size: 22px;
  cursor: pointer;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
  flex-shrink: 0;
  border-radius: 50%;
  transition: background 0.15s;
}

.guidebook-modal-close:hover {
  background: var(--sand-dark);
}

.guidebook-modal-content {
  padding: 16px;
  color: var(--ink-mid);
}

.guidebook-top-label {
  font-size: 12px;
  font-weight: 700;
  color: #1c6d26;
  margin-bottom: 8px;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.guidebook-intro-text {
  color: #5a5d4f;
  font-size: 14px;
  line-height: 1.85;
  margin-bottom: 20px;
  text-align: left;
}

.guidebook-section-header {
  font-size: 11px;
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 12px;
  font-weight: 600;
}

/* Guidebook content sections */
.guidebook-section-header {
  font-size: 11px;
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 12px;
  font-weight: 600;
}

.guidebook-section-category {
  font-size: 13px;
  font-weight: 600;
  margin-top: 20px;
  margin-bottom: 10px;
  color: var(--ink);
  text-align: left;
}

.guidebook-vocab-table {
  width: 100%;
  border-collapse: collapse;
  background: var(--surface);
  border-radius: 8px;
  overflow: hidden;
  margin-bottom: 16px;
  font-size: 13px;
  color: var(--ink-mid);
  border: 1px solid var(--border);
}

.guidebook-vocab-table thead {
  background: var(--sand-mid);
}

.guidebook-vocab-table th {
  padding: 10px 12px;
  text-align: left;
  font-weight: 600;
  color: var(--ink);
  border-bottom: 1px solid var(--border);
  font-size: 12px;
}

.guidebook-vocab-table td {
  padding: 10px 12px;
  border-bottom: 1px solid var(--border);
  color: var(--ink-mid);
}

.guidebook-vocab-table tr:last-child td {
  border-bottom: none;
}

.guidebook-grammar-section {
  background: rgba(198, 223, 213, 0.28);
  padding: 12px;
  margin: 12px 0;
  text-align: left;
  color: var(--ink-mid);
  border-radius: 8px;
  border-left: 3px solid var(--sage-mid);
}

.guidebook-grammar-section h3 {
  color: var(--sage-deep);
  font-size: 14px;
  font-weight: 600;
  margin-bottom: 8px;
  font-family: 'DM Serif Display', serif;
}

.guidebook-grammar-section p {
  font-size: 13px;
  color: var(--ink-mid);
  line-height: 1.5;
  margin-bottom: 6px;
}

.guidebook-pronunciation-section {
  background: rgba(245, 232, 176, 0.24);
  padding: 12px;
  margin: 12px 0;
  text-align: left;
  color: var(--ink-mid);
  border-radius: 8px;
  border-left: 3px solid var(--butter);
}

.guidebook-pronunciation-section h3 {
  color: #7a5c1e;
  font-size: 14px;
  font-weight: 600;
  margin-bottom: 8px;
  font-family: 'DM Serif Display', serif;
}

.guidebook-pronunciation-section p {
  font-size: 13px;
  color: var(--ink-mid);
  line-height: 1.5;
  margin-bottom: 6px;
}

.guidebook-unit-goal-section {
  background: rgba(212, 207, 237, 0.32);
  padding: 12px;
  margin: 16px 0;
  text-align: left;
  color: var(--ink-mid);
  border-radius: 8px;
  border-left: 3px solid var(--lavender);
}

.guidebook-unit-goal-section p {
  font-size: 13px;
  color: var(--ink-mid);
  margin-bottom: 10px;
}

.guidebook-unit-goal-section ul {
  margin-left: 16px;
  font-size: 13px;
  color: var(--ink-mid);
}

.guidebook-unit-goal-section li {
  margin-bottom: 6px;
  line-height: 1.5;
}

/* Buttons */
.guidebook-btn {
  background: var(--sage-deep);
  color: #fff;
  border: none;
  padding: 10px 16px;
  border-radius: 0;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 4px 6px 16px rgba(34, 85, 72, 0.12);
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: all 0.15s ease;
  font-family: 'DM Sans', sans-serif;
  font-size: 13px;
}

.guidebook-btn:hover {
  background: #1a4a3f;
  transform: translateY(-2px);
  box-shadow: 4px 8px 20px rgba(34, 85, 72, 0.16);
}

.guidebook-btn:active {
  transform: translateY(0);
}

/* Responsive adjustments for roadmap */
@media(max-width: 768px) {
  .roadmap-container {
    padding: 24px 16px 24px 16px;
  }

  .roadmap-scroll {
    padding-left: 20px;
  }

  .roadmap-scroll::before {
    left: 9px;
    width: 2px;
  }

  .roadmap-row-level::before {
    left: 2px;
    width: 16px;
    height: 16px;
    border-width: 2px;
  }

  .roadmap-nodes {
    gap: 12px;
  }

  .roadmap-node {
    min-width: 120px;
  }

  .node-circle {
    font-size: 28px;
    box-shadow: 4px 6px 16px rgba(26, 26, 24, 0.06);
  }

  .node-label {
    font-size: 11px;
  }

  .node-meta {
    font-size: 9px;
  }

  .level-label {
    min-width: 90px;
  }

  .level-chip {
    font-size: 14px;
  }

  .map-section-head {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>

<script>
// Pass guidebook HTML and current lesson guidebooks from PHP to JavaScript
window.guidebookHTML = <?php echo json_encode($guidebookHTML, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
window.guidebookHTMLMap = <?php echo json_encode($guidebookHTMLMap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
window.selectedGuidebookLesson = {
  file: <?= json_encode($selectedGuidebookTopic, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
  name: <?= json_encode($selectedGuidebookName, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
};

// ── GUIDEBOOK MODAL FUNCTIONS ─────────────────────────────────────────────
function openGuidebook(){
  const overlay = document.getElementById('guidebookModalOverlay');
  if (!overlay) {
    console.error('Guidebook overlay not found');
    return;
  }
  overlay.classList.add('active');
  document.body.style.overflow = 'hidden';
  buildGuidebook();
}

function closeGuidebook(){
  const overlay = document.getElementById('guidebookModalOverlay');
  if (!overlay) return;
  overlay.classList.remove('active');
  document.body.style.overflow = 'auto';
}

function escapeHtml(str){
  if (!str) return '';
  return String(str).replace(/[&<>]/g, (m) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;'
  }[m] || m));
}

function buildGuidebook(){
  const content = document.getElementById('gbContent');
  const title = document.getElementById('gbTitle');
  if (!content || !title) {
    console.error('Guidebook elements not found');
    return;
  }
  
  const selected = window.selectedGuidebookLesson || { file: '', name: '' };
  title.textContent = selected.name ? `${selected.name} · Guidebook` : 'Study guide';

  let html = '';
  
  // Use selected lesson guidebook HTML when available
  if (selected.file && window.guidebookHTMLMap && window.guidebookHTMLMap[selected.file]) {
    html = window.guidebookHTMLMap[selected.file];
  } else if (window.guidebookHTML && window.guidebookHTML.trim()) {
    html = window.guidebookHTML;
  } else {
    const topics = <?= json_encode($topics) ?>;
    if (topics && topics.length > 0) {
      const firstTopic = topics[0];
      html = `<div class="guidebook-section-header">📖 ${escapeHtml(firstTopic.name)}</div>
              <p style="margin: 12px 0; color: var(--ink-mid);">Complete this lesson to unlock the guidebook with vocabulary, grammar tips, and practice exercises.</p>
              <div style="margin-top: 16px; padding: 12px; background: var(--sand); border-radius: 8px; border: 1px solid var(--border);">
                <div style="font-size: 12px; font-weight: 600; color: var(--sage-dark);">✨ Ready to start?</div>
                <div style="font-size: 11px; color: var(--muted); margin-top: 4px;">Click the lesson card to begin learning.</div>
              </div>`;
    } else {
      html = '<div class="guidebook-section-header">No guidebook available yet.</div>';
    }
  }

  content.innerHTML = html;
}

function highlightRoadmapNode(node) {
  document.querySelectorAll('.roadmap-node.selected').forEach((n) => n.classList.remove('selected'));
  if (node && node.classList) node.classList.add('selected');
}

function selectRoadmapLesson(file, name){
  if (!file) return;
  window.selectedGuidebookLesson = {
    file: file,
    name: name || window.selectedGuidebookLesson.name || 'Lesson'
  };
}

function getShortDescription(text) {
  if (!text) return 'Practice this lesson now.';
  const words = text.trim().split(/\s+/).filter(Boolean);
  if (words.length <= 6) return text;
  return words.slice(0, 6).join(' ') + '...';
}

// ── REDESIGNED ROADMAP: VERTICAL SPINE ────────────────────────────────────
function buildRoadmap() {
  const container = document.getElementById('roadmapScroll');
  if (!container) return;

  const roadmapData = <?= json_encode($all_levels_topics) ?>;
  const levelInfo = <?= json_encode($levels) ?>;

  if (!roadmapData || Object.keys(roadmapData).length === 0) {
    container.innerHTML = '<div style="color:var(--muted);padding:40px 20px;text-align:center;font-size:14px;">No roadmap data available.</div>';
    return;
  }

  const levels = Object.keys(roadmapData).map(Number).sort((a, b) => a - b);
  let html = '';

  levels.forEach((level) => {
    const items = roadmapData[level] || [];
    const info = levelInfo[level] || { name: 'Level ' + level, icon: '', tag: '' };
    const label = `${info.icon || ''} ${info.name}`.trim();

    html += `<div class="roadmap-row-level">`;
    
    // Level label (left side of spine)
    html += `<div class="level-label">
      <span class="level-chip">${escapeHtml(label)}</span>
      <span class="level-meta">${escapeHtml(info.tag || '')}</span>
    </div>`;

    // Lessons grid (horizontal flow to the right)
    html += `<div class="roadmap-nodes">`;

    items.forEach((node) => {
      const status = node.pct === 100 ? 'completed' : node.pct > 0 ? 'in-progress' : 'not-started';
      const checkmark = status === 'completed' ? `<span class="checkmark">✓</span>` : '';
      const icon = node.icon || '📚';
      const name = node.name || 'Lesson';
      const pct = node.pct || 0;
      const file = node.file || '';

      html += `
        <div class="roadmap-node" data-lesson-file="${escapeHtml(file)}" data-lesson-name="${escapeHtml(name)}" data-lesson-level="${level}" style="cursor:pointer" title="${escapeHtml(name)} (${pct}%)">
          <div class="node-circle ${status}">
            ${icon}
            ${checkmark}
          </div>
          <div class="node-label">${escapeHtml(name)}</div>
          <div class="node-meta">${pct}%</div>
        </div>
      `;
    });

    html += `</div>`;
    html += `</div>`;
  });

  container.innerHTML = html;
  container.querySelectorAll('.roadmap-node').forEach((node) => {
    node.addEventListener('pointerenter', () => {
      selectRoadmapLesson(node.dataset.lessonFile, node.dataset.lessonName);
    });
    node.addEventListener('click', () => {
      selectRoadmapLesson(node.dataset.lessonFile, node.dataset.lessonName);
      highlightRoadmapNode(node);
      openTopicIntroduction(node.dataset.lessonFile, node.dataset.lessonLevel);
    });
  });

  const selectedFile = window.selectedGuidebookLesson?.file;
  if (selectedFile) {
    const selectedNode = Array.from(container.querySelectorAll('.roadmap-node')).find((node) => node.dataset.lessonFile === selectedFile);
    if (selectedNode) {
      highlightRoadmapNode(selectedNode);
    }
  }
}

// ── Start lesson on click ──────────────────────────────────────────────────
function openTopicIntroduction(file, level) {
  if (!file) return;
  const roadmapData = <?= json_encode($all_levels_topics, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  const levelTopics = roadmapData[level] || [];
  const topicIndex = levelTopics.findIndex((item) => item.file === file);
  const topic = levelTopics[topicIndex] || null;
  if (!topic) return;

  selectRoadmapLesson(topic.file, topic.name);
  document.getElementById('topicIntroTitle').textContent = topic.name || 'Lesson';
  document.getElementById('topicIntroDescription').textContent = getShortDescription(topic.description || 'Practice this lesson now.');
  document.getElementById('topicIntroSubtitle').textContent = topicIndex >= 0 ? `Lesson ${topicIndex + 1} of ${levelTopics.length}` : '';

  const startButton = document.getElementById('topicIntroStart');
  startButton.textContent = 'Play';
  startButton.onclick = () => startLesson(file, level);
  document.getElementById('topicIntroOverlay').classList.add('active');
  document.body.style.overflow = 'hidden';
}

function closeTopicIntroduction() {
  document.getElementById('topicIntroOverlay').classList.remove('active');
  document.body.style.overflow = '';
}

function startLesson(file, level) {
  const pair = '<?= $active_pair ?>';
  const direction = '<?= $active_direction ?>';
  const lang = '<?= $active_lang ?>';
  window.location.href = 'lesson.php?pair=' + encodeURIComponent(pair)
    + '&direction=' + encodeURIComponent(direction)
    + '&lang=' + encodeURIComponent(lang)
    + '&level=' + encodeURIComponent(level)
    + '&topic=' + encodeURIComponent(file)
    + '&id=1&ui_lang=<?= $ui_lang ?>';
}

// ── Toggle map section ─────────────────────────────────────────────────────
function toggleMapSection() {
  const section = document.getElementById('dashboardMapSection');
  const btn = document.getElementById('dashboardMapCloseBtn');
  if (!section || !btn) return;
  
  const isOpen = section.classList.toggle('open');
  if (isOpen) {
    document.body.style.overflow = 'hidden';
    btn.textContent = 'Close ✕';
    buildRoadmap();
  } else {
    document.body.style.overflow = '';
    btn.textContent = 'View Map';
  }
}
// Setup modal event listeners after DOM is ready
document.addEventListener('DOMContentLoaded', function(){
  const overlay = document.getElementById('guidebookModalOverlay');
  if (overlay) {
    overlay.addEventListener('click', function(event){
      if (event.target === overlay) {
        closeGuidebook();
      }
    });
  }
  
  // Close modal with Escape key
  document.addEventListener('keydown', function(event){
    if (event.key === 'Escape') {
      closeGuidebook();
      closeTopicIntroduction();
    }
  });
  
  // Build roadmap automatically when page loads
  if (document.getElementById('dashboardMapSection').classList.contains('open')) {
    buildRoadmap();
  }
});
</script>

</head>
<body>

<div class="dash" id="dash">

  <!-- DESKTOP SIDEBAR -->
  <div class="sidebar" id="sidebar">
    <button class="sidebar-toggle" id="sidebarToggle" title="Toggle sidebar">‹</button>
    <div class="brand">
      <div class="brand-logo"><img src="https://res.cloudinary.com/franklinrw/image/upload/v1755169717/kjk_bnrbmp.png" alt="Playmates logo"></div>
      <div class="brand-name"><img src="https://res.cloudinary.com/franklinrw/image/upload/v1755169717/kjk_bnrbmp.png" alt="">Playmates</div>
      <div class="brand-sub"><?= t('language_platform') ?></div>
    </div>
    <div class="streak-pill">
      <span class="streak-icon-only">🔥</span>
      <span class="streak-num"><?= htmlspecialchars($streak['current_streak']) ?></span>
      <span class="streak-lbl"><?= t('day_streak') ?></span>
    </div>
    <nav class="nav">
      <div class="nav-section">Learn</div>
      <button type="button" class="nav-item active" data-page="dashboard" onclick="navigate('dashboard')">
        <div class="nav-icon"><svg width="15" height="15" fill="none" stroke="#fff" stroke-width="2"><rect x="1" y="1" width="5.5" height="5.5" rx="1.5"/><rect x="8.5" y="1" width="5.5" height="5.5" rx="1.5"/><rect x="1" y="8.5" width="5.5" height="5.5" rx="1.5"/><rect x="8.5" y="8.5" width="5.5" height="5.5" rx="1.5"/></svg></div>
        <span class="nav-label"><?= t('dashboard') ?></span>
      </button>
      <button type="button" class="nav-item" data-page="business-english" onclick="navigate('business-english')">
        <div class="nav-icon"><svg width="15" height="15" fill="none" stroke="rgba(255,255,255,0.55)" stroke-width="2"><path d="M3 4h9a1 1 0 011 1v6a1 1 0 01-1 1H3a1 1 0 01-1-1V5a1 1 0 011-1z"/><path d="M5 9h5M5 6h5"/></svg></div>
        <span class="nav-label">💼 Business English</span>
      </button>
      <div class="nav-section">Account</div>
      <button type="button" class="nav-item" data-page="profile" onclick="navigate('profile')">
        <div class="nav-icon"><svg width="15" height="15" fill="none" stroke="rgba(255,255,255,0.55)" stroke-width="2"><circle cx="7.5" cy="5.5" r="3"/><path d="M1 14c0-3.6 2.9-6.5 6.5-6.5S14 10.4 14 14"/></svg></div>
        <span class="nav-label"><?= t('profile') ?></span>
      </button>
      <button type="button" class="nav-item" data-page="certificates" onclick="navigate('certificates')">
        <div class="nav-icon"><svg width="15" height="15" fill="none" stroke="rgba(255,255,255,0.55)" stroke-width="2"><path d="M2 5h11v8H2z" stroke-width="1.5"/><path d="M7.5 10v3M5.5 10v2.5M9.5 10v2.5" stroke-width="1.2"/></svg></div>
        <span class="nav-label">Certificates</span>
        <?php if(count($certificates) > 0): ?><span class="nav-badge"><?= count($certificates) ?></span><?php endif; ?>
      </button>
      <a href="../logout.php" class="nav-item logout-item" style="text-decoration:none;">
        <div class="nav-icon"><svg width="15" height="15" fill="none" stroke="rgba(255,255,255,0.7)" stroke-width="2"><path d="M9 3L14 8L9 13M14 8H4"/></svg></div>
        <span class="nav-label"><?= t('sign_out') ?></span>
      </a>
    </nav>
    <div class="sidebar-footer">
      <div class="xp-label"><span><?= t('level') ?> <?= $current_level_num ?></span><span><?= number_format($stats['total_xp'] ?? 0) ?> / <?= $next_level_xp ?> XP</span></div>
      <div class="level-txt">→ <?= t('level') ?> <?= $current_level_num + 1 ?></div>
    </div>
  </div>

  <!-- MAIN -->
  <div class="main">

    <!-- DASHBOARD PAGE -->
    <div class="page active" id="page-dashboard">

      <div class="topbar">
        <div class="page-title">Muraho, <?= htmlspecialchars($user['full_name'] ?? $user['username']) ?> 👋</div>
        <div class="topbar-right">
          <!-- Language direction selector -->
          <div class="lang-selector-group">
            <?php foreach ($current_pair['directions'] as $dir_code => $direction): ?>
              <button class="lang-pill <?= $active_direction == $dir_code ? 'active' : '' ?>" onclick="changeDirection('<?= $dir_code ?>')">
                <span class="fi fi-<?= $direction['flag'] ?>"></span>
                <?= htmlspecialchars($direction['name']) ?>
              </button>
            <?php endforeach; ?>
          </div>
          
          <!-- UI LANGUAGE SWITCHER -->
          <div class="lang-selector-group">
            <button class="lang-pill <?= $ui_lang == 'en' ? 'active' : '' ?>" onclick="changeUILang('en')">
              <span class="fi fi-gb"></span> EN
            </button>
            <button class="lang-pill <?= $ui_lang == 'rw' ? 'active' : '' ?>" onclick="changeUILang('rw')">
              <span class="fi fi-rw"></span> RW
            </button>
            <button class="lang-pill <?= $ui_lang == 'sw' ? 'active' : '' ?>" onclick="changeUILang('sw')">
              <span class="fi fi-tz"></span> SW
            </button>
          </div>
          
          <div class="date-pill"><?= date('D, M j Y') ?></div>
          <button type="button" class="map-float-btn" onclick="toggleMapSection()" title="<?= htmlspecialchars(t('lessons')) ?>" aria-label="<?= htmlspecialchars(t('lessons')) ?>">📚 <span><?= htmlspecialchars(t('lessons')) ?></span></button>
          <a href="lesson.php?pair=<?= urlencode($active_pair) ?>&direction=<?= urlencode($active_direction) ?>&level=<?= $active_level ?>&topic=<?= urlencode($topics[0]['file'] ?? 'animals.yaml') ?>&id=1&ui_lang=<?= urlencode($ui_lang) ?>" class="start-btn"><?= t('start_lesson') ?></a>
          <?php
          // Determine exam topic based on direction
          $exam_topic = ($active_direction === 'en') ? 'ENGLISH.yaml' : 'French.yaml';
          ?>
          <a href="exam_selection.php?ui_lang=<?= $ui_lang ?>" 
             class="start-btn" 
             style="background:var(--sage-dark)">
             📝 <?= t('take_exam') ?>
          </a>
        </div>
      </div>
      <div class="metrics">
        <div class="metric m-green">
          <div class="metric-lbl"><?= t('xp_today') ?></div>
          <div class="metric-val"><?= number_format($stats['xp_today'] ?? 0) ?></div>
          <div class="metric-sub">↑ <?= t('keep_going') ?></div>
        </div>
        
        <div class="metric m-amber">
          <div class="metric-lbl"><?= t('accuracy') ?></div>
          <div class="metric-val"><?= round($stats['avg_accuracy'] ?? 0) ?>%</div>
          <div class="metric-sub"><?= t('last_50_exercises') ?></div>
        </div>
        <div class="metric m-coral">
          <div class="metric-lbl"><?= t('lessons_done') ?></div>
          <div class="metric-val"><?= $stats['lessons_completed'] ?? 0 ?></div>
          <div class="metric-sub"><?= t('in_level') ?> <?= $active_level ?></div>
        </div>
        <div class="metric m-purple">
          <div class="metric-lbl"><?= t('words_learned') ?></div>
          <div class="metric-val"><?= $stats['words_learned'] ?? 0 ?></div>
          <div class="metric-sub"><?= t('this_level') ?></div>
        </div>
      </div>

      <div class="grid2">
        <div class="card">
          <div class="card-head"><span class="card-title"><?= t('xp_this_week') ?></span></div>
          <div class="chart-wrap"><canvas id="xpChart"></canvas></div>
        </div>
        <div class="card">
          <div class="card-head"><span class="card-title"><?= t('topic_progress') ?></span></div>
          <div class="lessons-list" id="topicList"></div>
        </div>
      </div>

      <div class="card" style="margin-bottom:16px;">
        <div class="card-head"><span class="card-title"><?= t('curriculum_outline') ?></span></div>
        <div class="outline-grid">
          <div class="outline-item">
            <div class="outline-icon">👋</div>
            <div>
              <div class="outline-title"><?= t('lesson_plan_section_1') ?></div>
              <div class="outline-desc"><?= t('lesson_plan_section_1_desc') ?></div>
            </div>
          </div>
          <div class="outline-item">
            <div class="outline-icon">🏠</div>
            <div>
              <div class="outline-title"><?= t('lesson_plan_section_2') ?></div>
              <div class="outline-desc"><?= t('lesson_plan_section_2_desc') ?></div>
            </div>
          </div>
        </div>
      </div>

      <div class="bottom-grid">
        <div class="card">
          <div class="card-head"><span class="card-title"><?= t('activity_last_91_days') ?></span></div>
          <div class="heatmap" id="heatmap"></div>
          <div style="display:flex;gap:6px;align-items:center;margin-top:10px">
            <span style="font-size:10px;color:var(--muted);font-weight:700"><?= t('less') ?></span>
            <div style="width:11px;height:11px;border-radius:3px;background:#F1EFE8;border:0.5px solid var(--border)"></div>
            <div style="width:11px;height:11px;border-radius:3px;background:#9FE1CB"></div>
            <div style="width:11px;height:11px;border-radius:3px;background:#1D9E75"></div>
            <div style="width:11px;height:11px;border-radius:3px;background:#085041"></div>
            <span style="font-size:10px;color:var(--muted);font-weight:700"><?= t('more') ?></span>
          </div>
        </div>
        <div style="display:flex;flex-direction:column;gap:12px">
          <div class="card">
            <div class="card-head" style="margin-bottom:12px"><span class="card-title"><?= t('achievements') ?></span><button class="card-action" onclick="navigate('profile')"><?= t('all') ?></button></div>
            <div class="achievements" id="achievements"></div>
          </div>
          <div class="card">
            <div class="card-head" style="margin-bottom:12px"><span class="card-title"><?= t('recent_lessons') ?></span></div>
            <div class="recent-list" id="recentLessons"></div>
          </div>
        </div>
      </div>

      <!-- MAP SECTION -->
      <div class="card map-section" id="dashboardMapSection">
        <div class="map-section-head">
          <div>
            <div style="display:flex;align-items:center;gap:8px;font-size:12px;font-weight:500;text-transform:uppercase;letter-spacing:0.8px;color:var(--ink);">
              <span style="font-size:16px;">📚</span>
              <span><?= t('lessons') ?></span>
            </div>
            <div class="map-section-sub">Your learning path across levels, with lessons inside each stage.</div>
            <div class="map-pair-list">
              <?php foreach ($lang_pairs as $pair_code => $pair): ?>
                <?php
                  $map_language_codes = array_keys($pair['languages']);
                  $map_direction_code = $map_language_codes[0];
                  if ($map_direction_code === 'rw' && isset($map_language_codes[1])) {
                      $map_language_codes = array_reverse($map_language_codes);
                      $map_direction_code = $map_language_codes[0];
                  }
                  $isMapActive = $pair_code === $active_pair;
                    $map_flag_codes = $map_language_codes;
                    if ($isMapActive && isset($pair['languages'][$active_direction])) {
                      $map_flag_codes = [$active_direction];
                      foreach (array_keys($pair['languages']) as $language_code) {
                        if ($language_code !== $active_direction) {
                          $map_flag_codes[] = $language_code;
                        }
                      }
                    }
                    $map_first_flag = $get_language_flag($map_flag_codes[0]);
                    $map_second_flag = $get_language_flag($map_flag_codes[1]);
                  $compact_pair = strtoupper(implode('-', $map_language_codes));
                ?>
                <button type="button" class="lang-pill<?= $isMapActive ? ' active' : '' ?>" onclick="changePairDirection('<?= $pair_code ?>','<?= $map_direction_code ?>')" title="<?= htmlspecialchars($pair['name']) ?>">
                  <span class="fi fi-<?= $map_first_flag ?>" aria-label="<?= htmlspecialchars($pair['languages'][$map_language_codes[0]]) ?> flag"></span>
                  <span class="fi fi-<?= $map_second_flag ?>" aria-label="<?= htmlspecialchars($pair['languages'][$map_language_codes[1]]) ?> flag"></span>
                  <?= htmlspecialchars($compact_pair) ?>
                </button>
              <?php endforeach; ?>
            </div>
          </div>
          <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
            <button class="btn-outline" type="button" onclick="toggleMapSection()" id="dashboardMapCloseBtn">
              ✕ <?= t('close') ?>
            </button>
          </div>
        </div>

        <div id="topicIntroOverlay" class="topic-intro-overlay" onclick="if(event.target===this) closeTopicIntroduction()">
          <section class="topic-intro-card" role="dialog" aria-modal="true" aria-labelledby="topicIntroTitle">
            <button class="topic-intro-close" type="button" aria-label="Close" onclick="closeTopicIntroduction()">×</button>
            <div class="topic-intro-badge"><div class="topic-intro-star">★</div></div>
            <p class="topic-intro-subtitle" id="topicIntroSubtitle"></p>
            <h2 class="topic-intro-title" id="topicIntroTitle"></h2>
            <p class="topic-intro-description" id="topicIntroDescription"></p>
            <button class="topic-intro-start" id="topicIntroStart" type="button">Play</button>
          </section>
        </div>

        <!-- Roadmap container -->
        <div class="roadmap-container" id="dashboardRoadmapContainer">
          <div class="roadmap-scroll" id="roadmapScroll"></div>
          <div class="map-legend">
            <span class="map-legend-item">
              <span class="legend-dot completed"></span> <?= t('completed') ?>
            </span>
            <span class="map-legend-item">
              <span class="legend-dot in-progress"></span> <?= t('in_progress_badge') ?>
            </span>
            <span class="map-legend-item">
              <span class="legend-dot locked"></span> <?= t('not_started_badge') ?>
            </span>
            <span class="map-legend-item" style="opacity:0.65;margin-left:auto;">
              → <?= t('click_to_start') ?>
            </span>
          </div>
        </div>
      </div>
    </div><!-- /dashboard -->

    <!-- BUSINESS ENGLISH PAGE -->
    <div class="page" id="page-business-english">
      <div class="topbar">
        <div class="page-title">💼 Business English</div>
        <div class="topbar-right">
          <div class="lang-selector-group">
            <button class="lang-pill <?= $ui_lang == 'en' ? 'active' : '' ?>" onclick="changeUILang('en')">
              <span class="fi fi-gb"></span> EN
            </button>
            <button class="lang-pill <?= $ui_lang == 'rw' ? 'active' : '' ?>" onclick="changeUILang('rw')">
              <span class="fi fi-rw"></span> RW
            </button>
            <button class="lang-pill <?= $ui_lang == 'sw' ? 'active' : '' ?>" onclick="changeUILang('sw')">
              <span class="fi fi-tz"></span> SW
            </button>
          </div>
        </div>
      </div>
      <div class="lessons-grid" id="beGrid"></div>
    </div><!-- /business-english -->

    <!-- PROFILE PAGE -->
    <div class="page" id="page-profile">
      <div class="topbar">
        <div class="page-title"><?= t('profile') ?></div>
        <button class="start-btn" onclick="window.location.href='edit-profile.php?ui_lang=<?= $ui_lang ?>'"><?= t('edit_profile') ?></button>
      </div>
      <div class="profile-header">
        <div class="profile-avatar"><?= strtoupper(substr($user['full_name'] ?? $user['email'] ?? 'U', 0, 2)) ?></div>
        <div>
          <div class="profile-name"><?= htmlspecialchars($user['full_name'] ?? $user['username']) ?></div>
          <div class="profile-handle">@<?= strtolower(str_replace(' ','_',$user['full_name'] ?? $user['email'] ?? 'user')) ?> · <?= t('joined') ?> <?= date('M Y', strtotime($user['created_at'] ?? 'now')) ?></div>
          <div class="profile-tags">
            <span class="profile-tag" style="background:#E1F5EE;color:#0F6E56">🔥 <?= $streak['current_streak'] ?>-<?= t('day_streak') ?></span>
            <span class="profile-tag" style="background:#EEEDFE;color:#534AB7"><?= t('level') ?> <?= $current_level_num ?></span>
          </div>
        </div>
      </div>
      <div class="profile-stats">
        <div class="stat-box"><div class="stat-box-val"><?= number_format($stats['total_xp'] ?? 0) ?></div><div class="stat-box-lbl"><?= t('total_xp') ?></div></div>
        <div class="stat-box"><div class="stat-box-val"><?= $stats['lessons_completed'] ?? 0 ?></div><div class="stat-box-lbl"><?= t('lessons_completed') ?></div></div>
        <div class="stat-box"><div class="stat-box-val"><?= $stats['words_learned'] ?? 0 ?></div><div class="stat-box-lbl"><?= t('words_learned_total') ?></div></div>
        <div class="stat-box"><div class="stat-box-val"><?= round($stats['avg_accuracy'] ?? 0) ?>%</div><div class="stat-box-lbl"><?= t('avg_accuracy') ?></div></div>
      </div>
      <div class="profile-grid">
        <div class="card">
          <div class="card-head">
            <span class="card-title"><?= t('my_languages') ?></span>
          </div>
          <?php foreach ($user_pairs as $pair_code):
            $pair     = $lang_pairs[$pair_code];
            $isActive = $pair_code == $active_pair;
            $dir_keys = array_keys($pair['directions']);
            $flag1    = $pair['directions'][$dir_keys[0]]['flag'];
            $flag2    = $pair['directions'][$dir_keys[1]]['flag'];
          ?>
            <div class="lang-row" style="<?= $isActive ? 'background:var(--sage);border-radius:10px;padding:10px;margin-bottom:6px;' : '' ?>">
              <div class="lang-flag">
                <span class="fi fi-<?= $flag1 ?>"></span> ↔ 
                <span class="fi fi-<?= $flag2 ?>"></span>
              </div>
              <div class="lang-info">
                <div class="lang-name"><?= htmlspecialchars($pair['name']) ?></div>
                <div class="lang-level"><?= $isActive ? t('active') . ' · ' . t('level') . ' ' . $active_level : t('click_to_switch') ?></div>
              </div>
              <?php if ($isActive): ?>
                <div class="lang-xp"><?= t('active') ?></div>
              <?php else: ?>
                <a href="?pair=<?= $pair_code ?>&direction=en&level=1&ui_lang=<?= $ui_lang ?>" class="lang-xp" style="color:var(--sage-dark);text-decoration:none"><?= t('switch') ?></a>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="card">
          <div class="card-head"><span class="card-title"><?= t('all_achievements') ?></span></div>
          <div class="achievements" id="allAchievements"></div>
        </div>
      </div>
    </div><!-- /profile -->

    <!-- CERTIFICATES PAGE -->
    <div class="page" id="page-certificates">
      <div class="topbar">
        <div class="page-title">🎓 Your Certificates</div>
      </div>
      <?php if(count($certificates) > 0): ?>
        <div class="cert-grid">
          <?php foreach($certificates as $cert): ?>
            <?php $cstatus = strtolower($cert['status'] ?? 'pending'); ?>
            <?php $isVerified = ($cstatus === 'approved' || $cstatus === 'issued'); ?>
            <?php $holderName = trim((string)($cert['holder_name'] ?? $cert['student_name'] ?? $cert['full_name'] ?? 'Certificate Holder')); ?>
            <?php $examTitle = trim((string)($cert['exam_title'] ?? 'Verified Certificate')); ?>
            <?php $examKind = strtolower($examTitle); ?>
            <?php $certImage = SITE_URL . '/assets/images/Academic.svg'; ?>
            <?php if (strpos($examKind, 'academic') !== false): ?>
              <?php $certImage = SITE_URL . '/assets/images/Academic.svg'; ?>
            <?php elseif (strpos($examKind, 'business') !== false): ?>
              <?php $certImage = SITE_URL . '/assets/images/Business.svg'; ?>
            <?php endif; ?>
            <div class="cert-card">
              <div class="cert-card-wrapper">
                <img src="<?= htmlspecialchars($certImage) ?>" alt="Certificate" style="width:100%;height:100%;object-fit:cover">
                <div class="cert-card-name-overlay"><?= htmlspecialchars($holderName !== '' ? $holderName : 'Certificate Holder') ?></div>
                <?php if (!$isVerified): ?>
                  <div class="cert-card-watermark">
                    <div class="cert-card-watermark-text">Not Verified</div>
                  </div>
                <?php endif; ?>
              </div>
              <h4><?= htmlspecialchars($examTitle) ?></h4>
              <div class="cert-meta">
                <div><strong>Holder:</strong> <?= htmlspecialchars($holderName !== '' ? $holderName : 'Not Specified') ?></div>
                <div style="margin-top:6px"><strong>Score:</strong> <?= htmlspecialchars($cert['score'] ?? '—') ?>%</div>
                <div style="margin-top:6px"><strong>Issued:</strong> <?= date('M d, Y', strtotime($cert['created_at'])) ?></div>
                <div style="margin-top:6px"><strong>Certificate ID:</strong></div>
                <div style="font-family:monospace;font-size:11px;word-break:break-all;margin-top:2px"><?= htmlspecialchars($cert['certificate_id'] ?? 'N/A') ?></div>
                <?php if ($isVerified): ?>
                  <div style="margin-top:8px;color:var(--sage-dark);font-weight:600;cursor:pointer;padding:4px 8px;border-radius:4px;display:inline-block;transition:background 0.15s" onmouseover="this.style.background='rgba(58, 122, 104, 0.1)'" onmouseout="this.style.background='transparent'" onclick="viewCertificate('<?= addslashes($cert['certificate_id'] ?? '') ?>')">✓ Verified</div>
                <?php else: ?>
                  <div style="margin-top:8px;color:#c44;font-weight:600">⚠ Not Verified</div>
                <?php endif; ?>
              </div>
              <div class="cert-actions">
                <button class="btn-primary" onclick="viewCertificate('<?= addslashes($cert['certificate_id'] ?? '') ?>')">View Certificate</button>
                <button class="btn-outline" onclick="downloadCertificate('<?= addslashes($cert['certificate_id'] ?? '') ?>')">Download</button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="empty-state" style="text-align:center;padding:60px 20px">
          <div style="font-size:48px;margin-bottom:16px">🎓</div>
          <div style="font-size:18px;font-weight:500;margin-bottom:8px">No certificates yet</div>
          <div style="font-size:14px;color:var(--muted)">Take exams and pass with a high score to earn verified certificates</div>
        </div>
      <?php endif; ?>
    </div><!-- /certificates -->

  </div><!-- /main -->
</div><!-- /dash -->

<!-- MOBILE TOPBAR -->
<div class="mob-topbar" id="mobTopbar">
  <button class="mob-hamburger" onclick="openMobSidebar()" aria-label="Open menu"><span></span><span></span><span></span></button>
  <div class="mob-topbar-brand"><img src="https://res.cloudinary.com/franklinrw/image/upload/v1755169717/kjk_bnrbmp.png" alt="">PLAYMATES</div>
  <button class="mob-flag-trigger" onclick="openMobSidebar()" aria-label="Open menu">
    <span class="fi fi-<?= $current_pair['directions'][$active_direction]['flag'] ?? 'rw' ?>"></span>
  </button>
</div>

<!-- MOBILE SIDEBAR -->
<div class="mob-overlay" id="mobOverlay" onclick="closeMobSidebar()"></div>
<div class="mob-sidebar" id="mobSidebar">
  <button class="mob-sidebar-close" onclick="closeMobSidebar()">×</button>
  <div class="brand" style="padding:20px 16px 14px">
    <div class="brand-name"><img src="https://res.cloudinary.com/franklinrw/image/upload/v1755169717/kjk_bnrbmp.png" alt="">Playmates</div>
    <div class="brand-sub"><?= t('language_platform') ?></div>
  </div>
  <div class="streak-pill" style="margin:10px 12px 0">
    <span style="font-size:16px">🔥</span>
    <span class="streak-num"><?= $streak['current_streak'] ?></span>
    <span class="streak-lbl"><?= t('day_streak') ?></span>
  </div>
  <nav class="mob-nav-list">
    <div class="mob-nav-section-lbl">Learn</div>
    <button class="mob-nav-item active" data-mob-page="dashboard" onclick="navigate('dashboard');closeMobSidebar()">
      <div class="mob-nav-icon"><svg width="15" height="15" fill="none" stroke="#fff" stroke-width="2"><rect x="1" y="1" width="5.5" height="5.5" rx="1.5"/><rect x="8.5" y="1" width="5.5" height="5.5" rx="1.5"/><rect x="1" y="8.5" width="5.5" height="5.5" rx="1.5"/><rect x="8.5" y="8.5" width="5.5" height="5.5" rx="1.5"/></svg></div>
      <span class="mob-nav-lbl"><?= t('dashboard') ?></span>
    </button>
    <div class="mob-nav-section-lbl" style="margin-top:10px">Account</div>
    <button class="mob-nav-item" data-mob-page="profile" onclick="navigate('profile');closeMobSidebar()">
      <div class="mob-nav-icon"><svg width="15" height="15" fill="none" stroke="rgba(255,255,255,0.55)" stroke-width="2"><circle cx="7.5" cy="5.5" r="3"/><path d="M1 14c0-3.6 2.9-6.5 6.5-6.5S14 10.4 14 14"/></svg></div>
      <span class="mob-nav-lbl"><?= t('profile') ?></span>
    </button>
    <button class="mob-nav-item" data-mob-page="certificates" onclick="navigate('certificates');closeMobSidebar()">
      <div class="mob-nav-icon"><svg width="15" height="15" fill="none" stroke="rgba(255,255,255,0.55)" stroke-width="2"><path d="M2 5h11v8H2z" stroke-width="1.5"/><path d="M7.5 10v3M5.5 10v2.5M9.5 10v2.5" stroke-width="1.2"/></svg></div>
      <span class="mob-nav-lbl">Certificates</span>
      <?php if(count($certificates) > 0): ?><span class="mob-nav-cnt"><?= count($certificates) ?></span><?php endif; ?>
    </button>
    <a href="../logout.php" class="mob-nav-item" style="text-decoration:none;margin-top:8px;border-top:1px solid rgba(255,255,255,0.08);border-radius:0;padding-top:12px;">
      <div class="mob-nav-icon"><svg width="15" height="15" fill="none" stroke="rgba(255,255,255,0.7)" stroke-width="2"><path d="M9 3L14 8L9 13M14 8H4"/></svg></div>
      <span class="mob-nav-lbl"><?= t('sign_out') ?></span>
    </a>
  </nav>
  <div class="mob-sidebar-section">
    <div class="mob-sidebar-section-lbl">Language Pair</div>
    <div class="mob-lang-rows">
      <?php foreach ($user_pairs as $pair_code):
        $pair     = $lang_pairs[$pair_code];
        $isActive = $pair_code == $active_pair;
        $dir_keys = array_keys($pair['directions']);
        $flag1    = $pair['directions'][$dir_keys[0]]['flag'];
      ?>
        <button class="mob-lang-row <?= $isActive ? 'active' : '' ?>" onclick="changePair('<?= $pair_code ?>')">
          <span class="fi fi-<?= $flag1 ?>" style="font-size:15px;flex-shrink:0"></span>
          <span class="mob-lang-row-name"><?= htmlspecialchars($pair['name']) ?></span>
          <?php if ($isActive): ?><span style="margin-left:auto;font-size:10px">✓</span><?php endif; ?>
        </button>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="mob-sidebar-section">
    <div class="mob-sidebar-section-lbl"><?= t('level') ?></div>
    <div class="mob-level-grid">
      <?php for ($lvl = 1; $lvl <= 6; $lvl++): ?>
        <a href="?pair=<?= $active_pair ?>&direction=<?= $active_direction ?>&level=<?= $lvl ?>&ui_lang=<?= $ui_lang ?>" class="mob-level-tile <?= $active_level == $lvl ? 'active' : '' ?>">
          <?= htmlspecialchars($levels[$lvl]['icon'] ?? '📘') ?> <?= $lvl ?>
        </a>
      <?php endfor; ?>
    </div>
  </div>
  <div class="sidebar-footer" style="margin-top:auto">
    <div class="xp-label"><span><?= t('level') ?> <?= $current_level_num ?></span><span><?= number_format($stats['total_xp'] ?? 0) ?> / <?= $next_level_xp ?> XP</span></div>
    <div class="xp-bar"><div class="xp-fill" style="width:<?= $xp_percent ?>%"></div></div>
    <div class="level-txt">→ <?= t('level') ?> <?= $current_level_num + 1 ?></div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
<script>
// ── helpers ──────────────────────────────────────────────────────────────────
function escapeHtml(str){if(!str)return'';return str.replace(/[&<>]/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;'}[m]||m))}

// ── navigation ────────────────────────────────────────────────────────────────
function navigate(page){
  document.querySelectorAll('.page').forEach(p=>p.classList.remove('active'));
  document.querySelectorAll('.nav-item[data-page],.mob-nav-item[data-mob-page]').forEach(n=>n.classList.remove('active'));
  const target=document.getElementById('page-'+page);
  if(target)target.classList.add('active');
  document.querySelectorAll('[data-page="'+page+'"]').forEach(b=>b.classList.add('active'));
  document.querySelectorAll('[data-mob-page="'+page+'"]').forEach(b=>b.classList.add('active'));
  const mainContainer=document.querySelector('.main');
  if(mainContainer)mainContainer.scrollTop=0;
  window.location.hash=page;
}
function changeDirection(d){window.location.href='dashboard.php?pair=<?= $active_pair ?>&direction='+d+'&level=<?= $active_level ?>&ui_lang=<?= $ui_lang ?>'}
function changePair(code){
  const pairs=<?= json_encode($lang_pairs) ?>;
  const first=pairs[code]?Object.keys(pairs[code].directions)[0]:'en';
  window.location.href='dashboard.php?pair='+code+'&direction='+first+'&level=1&ui_lang=<?= $ui_lang ?>';
}
function changePairDirection(code,direction){
  window.location.href='dashboard.php?pair='+encodeURIComponent(code)+'&direction='+encodeURIComponent(direction)+'&level=1&ui_lang=<?= $ui_lang ?>';
}
function changeUILang(lang){
  const url = new URL(window.location.href);
  url.searchParams.set('ui_lang', lang);
  window.location.href = url.toString();
}

// ── sidebar collapse ──────────────────────────────────────────────────────────
const dash=document.getElementById('dash');
const toggleBtn=document.getElementById('sidebarToggle');
if(localStorage.getItem('sidebarCollapsed')==='true')dash.classList.add('collapsed');
if(toggleBtn)toggleBtn.addEventListener('click',()=>{dash.classList.toggle('collapsed');localStorage.setItem('sidebarCollapsed',dash.classList.contains('collapsed'));});

// ── mobile sidebar ────────────────────────────────────────────────────────────
function openMobSidebar(){document.getElementById('mobSidebar').classList.add('open');document.getElementById('mobOverlay').classList.add('open');document.body.style.overflow='hidden';}
function closeMobSidebar(){document.getElementById('mobSidebar').classList.remove('open');document.getElementById('mobOverlay').classList.remove('open');document.body.style.overflow='';}

// expose globals
window.navigate=navigate;window.changeDirection=changeDirection;window.changePair=changePair;window.changeUILang=changeUILang;
window.openMobSidebar=openMobSidebar;window.closeMobSidebar=closeMobSidebar;

// click handlers
document.querySelectorAll('.nav-item[data-page]').forEach(btn=>btn.addEventListener('click',()=>navigate(btn.dataset.page)));
document.addEventListener('keydown',e=>{if(e.key==='Escape'){closeMobSidebar();}});

// hash-based routing on load
const hash=window.location.hash.substring(1);
if(['dashboard','profile','certificates','business-english'].includes(hash))navigate(hash);

// ── XP chart ──────────────────────────────────────────────────────────────────
const xpData=<?= json_encode($xp_data) ?>;
const xpCtx=document.getElementById('xpChart');
if(xpCtx){
  if(xpData.some(v=>v>0)){
    new Chart(xpCtx,{type:'bar',data:{labels:<?= json_encode($days) ?>,datasets:[{data:xpData,backgroundColor:xpData.map((_,i)=>i===new Date().getDay()?'#1E9E6B':'#9FE1CB'),borderRadius:7,borderSkipped:false}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>' '+c.raw+' XP'}}},scales:{x:{grid:{display:false},border:{display:false},ticks:{font:{family:'DM Sans',size:11,weight:'500'},color:'#888780'}},y:{display:false}}}});
  } else {
    xpCtx.parentElement.innerHTML='<div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--muted);font-size:12px"><?= t('no_lessons_yet') ?></div>';
  }
}

// ── topic bars ────────────────────────────────────────────────────────────────
const topics=<?= json_encode($topics) ?>;
const tl=document.getElementById('topicList');
if(tl&&topics.length){
  topics.slice(0,5).forEach(t=>{
    const color=t.pct>75?'#1D9E75':t.pct>50?'#1E9E6B':t.pct>25?'#EF9F27':'#7F77DD';
    const dot=t.pct===0?'#888780':color;
    tl.innerHTML+='<div class="lesson-row"><div class="lesson-dot" style="background:'+dot+'"></div><div class="lesson-name" title="'+escapeHtml(t.name)+'">'+escapeHtml(t.name)+'</div><div class="lesson-bar-wrap"><div class="lesson-bar" style="width:'+t.pct+'%;background:'+dot+'"></div></div><div class="lesson-pct">'+t.pct+'%</div></div>';
  });
} else if(tl){
  tl.innerHTML='<div style="color:var(--muted);font-size:12px;padding:8px 0"><?= t('no_topics') ?></div>';
}

// ── heatmap ───────────────────────────────────────────────────────────────────
const hm=document.getElementById('heatmap');
if(hm){
  const greens=['#F1EFE8','#9FE1CB','#1D9E75','#085041'];
  const actMap=<?= json_encode($activity_map) ?>;
  const activityTexts = ['<?= t('no_activity') ?>', '<?= t('light_activity') ?>', '<?= t('moderate_activity') ?>', '<?= t('heavy_activity') ?>'];
  for(let i=0;i<91;i++){
    const d=new Date();d.setDate(d.getDate()-(90-i));
    const ds=d.toISOString().split('T')[0];
    const cell=document.createElement('div');
    cell.className='hm-cell';
    const level = actMap[ds] || 0;
    cell.style.background=greens[level];
    cell.title=activityTexts[level]+' — '+ds;
    hm.appendChild(cell);
  }
}

// ── business english courses ───────────────────────────────────────────────────
const beCourses=<?= json_encode($business_english_courses) ?>;
const beGrid=document.getElementById('beGrid');
if(beGrid&&beCourses.length){
  beGrid.innerHTML='';
  beCourses.forEach(c=>{
    const badge=c.pct===100?'<?= t('completed_badge') ?>':c.pct>0?'<?= t('in_progress_badge') ?>':'<?= t('not_started_badge') ?>';
    const bbg=c.pct===100?'#E1F5EE':c.pct>0?'#FAEEDA':'#F1EFE8';
    const bc=c.pct===100?'#0F6E56':c.pct>0?'#854F0B':'#5F5E5A';
    const fill=c.pct===0?'#888780':c.pct>75?'#1D9E75':c.pct>50?'#1E9E6B':c.pct>25?'#EF9F27':'#7F77DD';
    const card=document.createElement('div');
    card.className='lesson-card';
    card.innerHTML='<div class="lesson-card-icon">'+c.icon+'</div><span class="lesson-badge" style="background:'+bbg+';color:'+bc+'">'+badge+'</span><div class="lesson-card-name">'+escapeHtml(c.name)+'</div><div class="lesson-card-meta">'+c.total+' <?= t('exercises') ?></div><div class="lesson-card-bar"><div class="lesson-card-fill" style="width:'+c.pct+'%;background:'+fill+'"></div></div><div class="lesson-card-foot"><span class="lesson-card-pct">'+c.pct+'% <?= t('done') ?></span><span class="lesson-card-xp">'+(c.pct===100?'✓ <?= t('completed_badge') ?>':'<?= t('start') ?>')+'</span></div>';
    card.addEventListener('click',()=>window.location.href='lesson.php?pair=business-english&level='+c.level+'&topic='+encodeURIComponent(c.file)+'&id=1&ui_lang=<?= $ui_lang ?>');
    beGrid.appendChild(card);
  });
}

// ── achievements ──────────────────────────────────────────────────────────────
const achs=<?= json_encode($achievements) ?>;

// ── certificate download ──────────────────────────────────────────────────────
function getCertificateUrl(certId){
  const url = new URL('../certificate.php', window.location.href);
  url.searchParams.set('certificate_id', certId);
  return url.toString();
}
function viewCertificate(certId){
  if(!certId || certId.trim()===''){alert('Certificate ID is missing');return;}
  window.open(getCertificateUrl(certId),'_blank');
}
function downloadCertificate(certId){
  if(!certId || certId.trim()===''){alert('Certificate ID is missing');return;}
  window.open(getCertificateUrl(certId),'_blank');
}

// Open the map full-screen automatically on load
toggleMapSection();
</script>
</body>
</html>
