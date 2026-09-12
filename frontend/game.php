<?php
ini_set('display_errors', '0');
error_reporting(E_ALL);
session_start();

// Support both old ?lang= and new ?direction= + ?pair= params
$pair = $_GET['pair'] ?? $_SESSION['active_pair'] ?? 'en-rw';
$direction = $_GET['direction'] ?? $_SESSION['active_direction'] ?? 'en';
$level = (int)($_GET['level'] ?? 1);
$topic = $_GET['topic'] ?? 'animals.yaml';

// Fallback to old lang param if present
$lang = $_GET['lang'] ?? $direction;

$topic_base = pathinfo(basename($topic), PATHINFO_FILENAME);

// ========== Determine folder name based on pair + direction ==========
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
$mapped_folder = $pair_folder_map[$pair . ':' . $direction] ?? null;

// Fallback to old direction-only mapping for backwards compatibility
if (!$mapped_folder) {
    $lang_folder_map = [
        'en' => 'EN-TO-RW',
        'rw' => 'RW-TO-EN',
        'fr' => 'FR-TO-RW',
        'sw' => 'SW-TO-RW',
    ];
    $mapped_folder = $lang_folder_map[$lang] ?? 'EN-TO-RW';
}

$lang_folder = $mapped_folder;

// Try multiple path strategies
$paths_to_try = [
    "../content/$lang_folder/level$level/" . $topic_base . ".yaml",
    "../../content/$lang_folder/level$level/" . $topic_base . ".yaml",
    $_SERVER['DOCUMENT_ROOT'] . "/content/$lang_folder/level$level/" . $topic_base . ".yaml",
    dirname(__FILE__) . "/../content/$lang_folder/level$level/" . $topic_base . ".yaml",
    dirname(__FILE__) . "/../../content/$lang_folder/level$level/" . $topic_base . ".yaml",
];

$yaml_path   = null;
$yaml_found  = false;
$paths_tried = [];

foreach ($paths_to_try as $p) {
    $real = realpath($p);
    $paths_tried[] = $p . " → " . ($real ?: 'NOT FOUND');
    if ($real && file_exists($real)) {
        $yaml_path  = $real;
        $yaml_found = true;
        break;
    }
}

// Try to find alternative YAML files if the specific one doesn't exist
if (!$yaml_found) {
    $fallback_paths = [
        "../content/$lang_folder/" . $topic_base . ".yaml",
        "../../content/$lang_folder/" . $topic_base . ".yaml",
        "../content/$lang_folder/level1/" . $topic_base . ".yaml",
        "../content/EN-TO-RW/level$level/" . $topic_base . ".yaml",
        "../content/EN-TO-RW/" . $topic_base . ".yaml",
    ];
    
    foreach ($fallback_paths as $p) {
        $real = realpath($p);
        if ($real && file_exists($real)) {
            $yaml_path = $real;
            $yaml_found = true;
            $paths_tried[] = "FALLBACK: " . $p . " → FOUND";
            break;
        }
    }
}

$words        = [];
$word_images  = [];
$parse_method = 'none';
$parse_error  = '';

if ($yaml_found) {
    $content = file_get_contents($yaml_path);
    
    // Determine which language the user should speak based on direction
    $speak_language = $direction; // 'en', 'fr', 'rw', or 'sw'
    
    // ── Method 1: yaml_parse (if extension installed) ───────────
    if (function_exists('yaml_parse')) {
        $data = @yaml_parse($content);
        if ($data && isset($data['exercises'])) {
            $parse_method = 'yaml_parse';
            foreach ($data['exercises'] as $exercise) {
                $type = $exercise['type'] ?? '';
                if ($type === 'flashcards' && isset($exercise['flashcards'])) {
                    foreach ($exercise['flashcards'] as $card) {
                        // Extract FRONT word (convention: front = target language)
                        // The user speaks the target language in the voice game
                        $target_word = trim($card['front'] ?? '');
                        
                        if ($target_word) {
                            $words[] = strtoupper($target_word);
                            if (!empty($card['image'])) {
                                $word_images[strtoupper($target_word)] = trim($card['image']);
                            }
                        }
                    }
                }
            }
        } else {
            $parse_error = 'yaml_parse returned false or no exercises key';
        }
    }

    // ── Method 2: regex ──────────────────────────────────────────
    if (empty($words)) {
        $parse_method = 'regex';

        if (preg_match('/type:\s*flashcards.*?flashcards:(.*?)(?=\n\s*xp_reward:|\z)/s', $content, $block)) {
            $flashcard_block = $block[1];
            $cards = preg_split('/\n\s*-\s*(?=front:)/', $flashcard_block);

            foreach ($cards as $card) {
                if (empty(trim($card))) continue;

                $front = '';
                $back  = '';
                $image = '';

                if (preg_match('/front:\s*["\']?(.+?)["\']?\s*(?:\n|$)/', $card, $m))
                    $front = trim($m[1], " \"'\t\r");
                
                if (preg_match('/back:\s*["\']?(.+?)["\']?\s*(?:\n|$)/', $card, $m))
                    $back = trim($m[1], " \"'\t\r");

                if (preg_match('/image:\s*["\']?(https?:\/\/[^\s\'"]+)["\']?/', $card, $m))
                    $image = trim($m[1], " \"'\t\r");

                // Always extract FRONT (convention: front = target language)
                $target_word = strtoupper($front);
                
                if ($target_word) {
                    $words[] = $target_word;
                    if ($image) $word_images[$target_word] = $image;
                }
            }

            $words = array_values(array_unique(array_filter($words)));
        }
    }

    // ── Method 3: brute-force ────────────────────────────────────
    if (empty($words)) {
        $parse_method = 'brute_force';

        $start = strpos($content, 'type: flashcards');
        if ($start !== false) {
            $flashcard_section = substr($content, $start);
            if (preg_match('/\n\s*xp_reward:/', $flashcard_section, $m, PREG_OFFSET_CAPTURE, 10)) {
                $flashcard_section = substr($flashcard_section, 0, $m[0][1]);
            }

            $lines = explode("\n", $flashcard_section);
            $last_front = null;
            $last_back = null;
            
            foreach ($lines as $line) {
                if (preg_match('/^\s*front:\s*["\']?(.+?)["\']?\s*$/', $line, $m)) {
                    $last_front = strtoupper(trim($m[1], " \"'\t\r"));
                }
                if (preg_match('/^\s*back:\s*["\']?(.+?)["\']?\s*$/', $line, $m)) {
                    $last_back = strtoupper(trim($m[1], " \"'\t\r"));
                    // Always extract FRONT (convention: front = target language)
                    $target_word = $last_front;
                    if ($target_word) {
                        $words[] = $target_word;
                    }
                }
                if (isset($target_word) && $target_word && preg_match('/^\s*image:\s*["\']?(https?:\/\/[^\s\'"]+)["\']?/', $line, $m)) {
                    $word_images[$target_word] = trim($m[1], " \"'\t\r");
                    $target_word = null;
                }
            }
            $words = array_values(array_unique(array_filter($words)));
        }
    }
}

// ── Smart fallback based on topic ───────────────────────────────
if (empty($words)) {
    $parse_method = 'smart_fallback';
    
    $topic_fallbacks = [
        'animals' => ['DOG', 'CAT', 'BIRD', 'FISH', 'COW', 'GOAT', 'LION', 'ELEPHANT', 'MONKEY', 'SNAKE'],
        'colors' => ['RED', 'BLUE', 'GREEN', 'YELLOW', 'BLACK', 'WHITE', 'PURPLE', 'ORANGE', 'PINK', 'BROWN'],
        'numbers' => ['ONE', 'TWO', 'THREE', 'FOUR', 'FIVE', 'SIX', 'SEVEN', 'EIGHT', 'NINE', 'TEN'],
        'family' => ['MOTHER', 'FATHER', 'BROTHER', 'SISTER', 'GRANDMA', 'GRANDPA', 'UNCLE', 'AUNT', 'COUSIN'],
        'greetings' => ['HELLO', 'GOODBYE', 'THANK YOU', 'PLEASE', 'SORRY', 'WELCOME', 'GOOD MORNING'],
        'food' => ['RICE', 'BREAD', 'WATER', 'MILK', 'FRUIT', 'VEGETABLE', 'MEAT', 'FISH', 'EGG', 'CHEESE'],
        'body' => ['HEAD', 'EYE', 'NOSE', 'MOUTH', 'HAND', 'FOOT', 'ARM', 'LEG', 'EAR', 'HAIR'],
        'transport' => ['CAR', 'BUS', 'TRAIN', 'PLANE', 'BOAT', 'BIKE', 'MOTORCYCLE', 'TRUCK', 'TAXI'],
    ];
    
    $matched = false;
    foreach ($topic_fallbacks as $topic_key => $fallback_words) {
        if (strpos(strtolower($topic_base), $topic_key) !== false) {
            $words = $fallback_words;
            $matched = true;
            break;
        }
    }
    
    if (!$matched) {
        $words = [$topic_base, 'HELLO', 'WORLD', 'LEARN', 'SPEAK', 'WORDS', 'VOICE', 'GAME', 'LEVEL', 'STUDY'];
    }
    
    // Add placeholder images
    foreach ($words as $word) {
        if (!isset($word_images[$word])) {
            $word_images[$word] = "https://via.placeholder.com/400x300?text=" . urlencode($word);
        }
    }
}

// Set speech recognition language
$speech_lang = 'en-US';
if ($direction === 'rw') $speech_lang = 'rw-RW';
elseif ($direction === 'fr') $speech_lang = 'fr-FR';
elseif ($direction === 'sw') $speech_lang = 'sw-KE';
else $speech_lang = 'en-US';

$display_topic = ucwords(str_replace('-', ' ', $topic_base));
$display_direction = strtoupper($direction);
$direction_name = '';
if ($direction === 'en') $direction_name = 'English';
elseif ($direction === 'fr') $direction_name = 'French';
elseif ($direction === 'rw') $direction_name = 'Kinyarwanda';
elseif ($direction === 'sw') $direction_name = 'Swahili';

// Debug info
echo "<!-- PAIR: " . htmlspecialchars($pair) . " -->\n";
echo "<!-- DIRECTION: " . htmlspecialchars($direction) . " -->\n";
echo "<!-- SPEAK_LANGUAGE: " . htmlspecialchars($speak_language) . " -->\n";
echo "<!-- SPEECH_RECOG_LANG: " . htmlspecialchars($speech_lang) . " -->\n";
echo "<!-- FOLDER: " . htmlspecialchars($lang_folder) . " -->\n";
echo "<!-- YAML_PATH: " . ($yaml_path ?: 'NOT FOUND') . " -->\n";
echo "<!-- YAML_FOUND: " . ($yaml_found ? 'YES' : 'NO') . " -->\n";
echo "<!-- PARSE_METHOD: $parse_method -->\n";
if ($parse_error) echo "<!-- PARSE_ERROR: $parse_error -->\n";
echo "<!-- WORDS(" . count($words) . "): " . implode(', ', array_slice($words, 0, 10)) . " -->\n";
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($direction) ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title><?= htmlspecialchars($display_topic) ?> · Voice Game</title>
<link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700;800;900&family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<style>
:root {
  --sage:#4A7C59; --sage-d:#2F5E3E; --sage-l:#DCF0E2;
  --sun:#F5A623;  --sun-d:#D4841A;  --sun-l:#FFF2D8;
  --sky:#3B82C4;  --sky-d:#1E5A9C;  --sky-l:#D8EDFB;
  --coral:#E85D4A; --coral-d:#B33526; --coral-l:#FDECEA;
  --plum:#8B5CF6; --plum-l:#EDE9FE;
  --sand:#F9F4EE; --bark:#1C1A17; --stone:#6B6560;
  --border:#E5DDD5; --white:#FFFFFF;
  --font-display:'Baloo 2',cursive;
  --font-body:'Nunito',sans-serif;
  --shadow:0 2px 12px rgba(28,26,23,.08);
  --shadow-lg:0 8px 32px rgba(28,26,23,.14);
}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
html,body{width:100%;height:100%;overflow:hidden}
#video{position:fixed;inset:0;width:100%;height:100%;object-fit:cover;z-index:0;transform:scaleX(-1);filter:brightness(0.45) saturate(0.5) sepia(0.18)}
body::before{content:'';position:fixed;inset:0;z-index:1;pointer-events:none;background:radial-gradient(ellipse at 20% 15%,rgba(74,124,89,.18) 0%,transparent 55%),radial-gradient(ellipse at 80% 85%,rgba(245,166,35,.12) 0%,transparent 50%),linear-gradient(180deg,rgba(28,26,23,.55) 0%,rgba(28,26,23,.2) 40%,rgba(28,26,23,.65) 100%)}
#canvas{position:fixed;inset:0;z-index:2;pointer-events:none}
#pfx{position:fixed;inset:0;z-index:3;pointer-events:none}
#hud{position:fixed;inset:0;z-index:10;pointer-events:none;display:flex;flex-direction:column;justify-content:space-between;align-items:center;padding:20px 20px 36px;padding-top:max(env(safe-area-inset-top,20px),20px)}
#top-bar{width:100%;display:flex;justify-content:space-between;align-items:center;gap:12px}
.score-chip{display:flex;align-items:baseline;gap:5px;background:rgba(249,244,238,.92);backdrop-filter:blur(14px);border:2px solid var(--border);border-radius:50px;padding:7px 18px;box-shadow:0 4px 0 rgba(229,221,213,.8),var(--shadow)}
.score-num{font-family:var(--font-display);font-weight:900;font-size:1.45rem;line-height:1;color:var(--bark)}
.score-num.pop{animation:npop .25s cubic-bezier(.34,1.56,.64,1)}
@keyframes npop{50%{transform:scale(1.38)}}
.score-label{font-family:var(--font-display);font-size:.65rem;font-weight:800;color:var(--stone);text-transform:uppercase;letter-spacing:1.5px}
.xp-live{display:flex;align-items:center;gap:5px;background:var(--sun);border-radius:50px;padding:7px 14px;font-family:var(--font-display);font-weight:800;font-size:.82rem;color:#fff;box-shadow:0 4px 0 var(--sun-d),var(--shadow)}
#close-btn{width:38px;height:38px;border-radius:50%;background:rgba(249,244,238,.92);backdrop-filter:blur(14px);border:2px solid var(--coral);color:var(--coral-d);font-size:.9rem;font-weight:900;display:flex;align-items:center;justify-content:center;text-decoration:none;pointer-events:all;box-shadow:0 3px 0 var(--coral-d);transition:background .15s,color .15s,transform .1s}
#close-btn:hover{background:var(--coral);color:#fff;transform:translateY(-1px)}
#close-btn:active{transform:translateY(2px);box-shadow:0 1px 0 var(--coral-d)}
#word-display{font-family:var(--font-display);font-weight:900;font-size:clamp(2rem,8vw,3rem);letter-spacing:5px;text-transform:uppercase;color:var(--white);text-shadow:0 2px 16px rgba(28,26,23,.6),0 0 40px rgba(74,124,89,.4);text-align:center;background:rgba(28,26,23,.55);backdrop-filter:blur(12px);border:2px solid rgba(255,255,255,.12);border-radius:20px;padding:16px 32px;box-shadow:0 6px 0 rgba(0,0,0,.3),var(--shadow-lg);transition:color .2s,background .2s}
#word-display.fail{color:var(--coral-l);background:rgba(184,53,38,.5);border-color:rgba(232,93,74,.4);animation:shake .35s ease}
#word-display.win{color:var(--sage-l);background:rgba(47,94,62,.55);border-color:rgba(74,124,89,.4)}
@keyframes shake{25%{transform:translateX(-7px)}75%{transform:translateX(7px)}}
#combo-pop{position:fixed;top:22%;left:50%;transform:translateX(-50%);z-index:20;pointer-events:none;opacity:0;font-family:var(--font-display);font-weight:900;font-size:clamp(2.4rem,8vw,3.8rem);color:var(--sun);text-shadow:0 4px 16px rgba(245,166,35,.55),0 2px 8px rgba(28,26,23,.4);white-space:nowrap;transition:opacity .1s}
#combo-pop.show{opacity:1;animation:cpop .32s cubic-bezier(.34,1.56,.64,1)}
@keyframes cpop{from{transform:translateX(-50%) scale(.3)}to{transform:translateX(-50%) scale(1)}}
#heard{position:fixed;z-index:15;pointer-events:none;font-family:var(--font-body);font-weight:800;font-size:.75rem;letter-spacing:2px;text-transform:uppercase;color:var(--bark);background:rgba(249,244,238,.92);backdrop-filter:blur(10px);border:2px solid var(--border);padding:5px 14px;border-radius:50px;box-shadow:var(--shadow);transform:translate(-50%,0);opacity:0;transition:opacity .15s;white-space:nowrap}
#heard.show{opacity:1}
#heard.bad{color:var(--coral-d);border-color:var(--coral);background:var(--coral-l)}
#intro{position:fixed;inset:0;z-index:400;background:var(--sand);background-image:radial-gradient(circle at 15% 20%,rgba(74,124,89,.07) 0%,transparent 50%),radial-gradient(circle at 85% 80%,rgba(245,166,35,.06) 0%,transparent 50%);display:flex;flex-direction:column;align-items:center;justify-content:center;padding:32px}
.intro-card{max-width:360px;width:100%;background:var(--white);border-radius:24px;border:2px solid var(--border);box-shadow:0 8px 0 rgba(229,221,213,.8),var(--shadow-lg);padding:36px 28px 28px;display:flex;flex-direction:column;align-items:center;gap:20px;text-align:center;animation:slideUp .4s cubic-bezier(.34,1.56,.64,1)}
@keyframes slideUp{from{opacity:0;transform:translateY(20px) scale(.97)}to{opacity:1;transform:translateY(0) scale(1)}}
.intro-emoji{font-size:3rem;line-height:1;animation:bounce 2s infinite ease-in-out}
@keyframes bounce{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
.intro-tag{background:var(--sage-l);border:1.5px solid rgba(74,124,89,.3);border-radius:50px;padding:4px 14px;font-family:var(--font-display);font-weight:700;font-size:.7rem;letter-spacing:.8px;text-transform:uppercase;color:var(--sage-d)}
.intro-title{font-family:var(--font-display);font-weight:900;font-size:clamp(2.2rem,9vw,3.4rem);line-height:1;color:var(--bark)}
.intro-hint{font-family:var(--font-body);font-size:.88rem;font-weight:600;color:var(--stone);line-height:1.65;max-width:260px;background:var(--sand);border-radius:14px;padding:12px 16px;border:1.5px solid var(--border)}
.how-pills{display:flex;gap:8px;flex-wrap:wrap;justify-content:center}
.how-pill{background:var(--sky-l);border:1.5px solid rgba(59,130,196,.25);border-radius:50px;padding:5px 13px;font-family:var(--font-display);font-weight:700;font-size:.72rem;color:var(--sky-d)}
#go-btn{width:100%;padding:16px;font-family:var(--font-display);font-weight:800;font-size:1.1rem;letter-spacing:.3px;color:#fff;background:var(--sage);border:none;border-radius:14px;cursor:pointer;box-shadow:0 6px 0 var(--sage-d),0 8px 20px rgba(74,124,89,.25);transition:transform .1s,box-shadow .1s;position:relative;overflow:hidden}
#go-btn::after{content:'';position:absolute;inset:0;background:linear-gradient(180deg,rgba(255,255,255,.12) 0%,transparent 60%);pointer-events:none}
#go-btn:hover{transform:translateY(-2px);box-shadow:0 8px 0 var(--sage-d),0 12px 24px rgba(74,124,89,.3)}
#go-btn:active{transform:translateY(5px);box-shadow:0 1px 0 var(--sage-d)}
.nosrwarn{font-family:var(--font-body);font-size:.75rem;font-weight:700;color:var(--coral-d);background:var(--coral-l);border:1.5px solid var(--coral);border-radius:10px;padding:6px 14px;display:none}
#finished{position:fixed;inset:0;z-index:399;background:var(--sand);background-image:radial-gradient(circle at 15% 20%,rgba(74,124,89,.07) 0%,transparent 50%),radial-gradient(circle at 85% 80%,rgba(245,166,35,.06) 0%,transparent 50%);display:none;flex-direction:column;align-items:center;justify-content:center;padding:32px}
.finish-card{max-width:360px;width:100%;background:linear-gradient(140deg,var(--sage-d) 0%,var(--sage) 55%,#5B9E6E 100%);border-radius:24px;box-shadow:0 8px 0 rgba(47,94,62,.5),var(--shadow-lg);padding:36px 24px 28px;display:flex;flex-direction:column;align-items:center;gap:20px;text-align:center;color:#fff;position:relative;overflow:hidden;animation:slideUp .4s cubic-bezier(.34,1.56,.64,1)}
.finish-card::before{content:'';position:absolute;width:200px;height:200px;background:rgba(255,255,255,.06);border-radius:50%;top:-60px;right:-40px;pointer-events:none}
.finish-card::after{content:'';position:absolute;width:140px;height:140px;background:rgba(245,166,35,.15);border-radius:50%;bottom:-50px;right:60px;pointer-events:none}
.finish-emoji{font-size:3rem}
#finished h1{font-family:var(--font-display);font-weight:900;font-size:clamp(2rem,8vw,2.8rem);color:#fff;text-shadow:0 2px 8px rgba(0,0,0,.15)}
.finish-sub{font-family:var(--font-body);font-size:.9rem;font-weight:600;opacity:.85}
.finish-scores{display:grid;grid-template-columns:1fr 1fr;gap:10px;width:100%}
.fscore{background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.2);border-radius:16px;padding:16px 10px;text-align:center;backdrop-filter:blur(4px)}
.fscore-label{font-family:var(--font-display);font-size:.62rem;font-weight:800;letter-spacing:.8px;text-transform:uppercase;opacity:.75;display:block;margin-bottom:4px}
.fscore-val{font-family:var(--font-display);font-weight:900;font-size:2.2rem;line-height:1}
#replay-btn{width:100%;padding:15px;font-family:var(--font-display);font-weight:800;font-size:1.05rem;letter-spacing:.3px;color:#fff;background:var(--sun);border:none;border-radius:14px;cursor:pointer;box-shadow:0 5px 0 var(--sun-d);transition:transform .1s,box-shadow .1s;position:relative;overflow:hidden;z-index:1}
#replay-btn::after{content:'';position:absolute;inset:0;background:linear-gradient(180deg,rgba(255,255,255,.12) 0%,transparent 60%);pointer-events:none}
#replay-btn:hover{transform:translateY(-2px);box-shadow:0 7px 0 var(--sun-d)}
#replay-btn:active{transform:translateY(4px);box-shadow:0 1px 0 var(--sun-d)}
.back-link{font-family:var(--font-body);font-size:.78rem;font-weight:700;color:rgba(255,255,255,.65);text-decoration:none;transition:color .2s;z-index:1;position:relative}
.back-link:hover{color:#fff}
@media(max-width:480px){
  .intro-card,.finish-card{padding:24px 16px 20px}
  .intro-title{font-size:2.2rem}
  #go-btn,#replay-btn{font-size:1rem;padding:14px}
  #word-display{padding:12px 20px;font-size:clamp(1.5rem,7vw,2.4rem);letter-spacing:3px}
}
</style>
</head>
<body>

<video id="video" autoplay muted playsinline></video>
<canvas id="canvas"></canvas>
<canvas id="pfx"></canvas>
<div id="heard"></div>

<div id="hud">
    <div id="top-bar">
        <div class="score-chip">
            <span class="score-num" id="score-v">0</span>
            <span class="score-label">pts</span>
        </div>
        <div class="xp-live">⭐ <span id="combo-live">×1</span></div>
        <a id="close-btn" href="choose-topic.php?pair=<?= htmlspecialchars($pair) ?>&direction=<?= htmlspecialchars($direction) ?>&level=<?= $level ?>" title="Leave">✕</a>
    </div>
    <div id="word-display">—</div>
    <div></div>
</div>

<div id="combo-pop"></div>

<div id="finished">
    <div class="finish-card">
        <div class="finish-emoji">🏆</div>
        <h1>Wararangije!</h1>
        <p class="finish-sub">Lesson complete — great work!</p>
        <div class="finish-scores">
            <div class="fscore"><span class="fscore-label">Amanota</span><span class="fscore-val" id="fin-score">0</span></div>
            <div class="fscore"><span class="fscore-label">Max Combo</span><span class="fscore-val" id="fin-combo">0</span></div>
        </div>
        <button id="replay-btn">🔄 &nbsp;Ongera Gukina</button>
        <a href="choose-topic.php?pair=<?= htmlspecialchars($pair) ?>&direction=<?= htmlspecialchars($direction) ?>&level=<?= $level ?>" class="back-link">← Hitamo indi ngingo</a>
    </div>
</div>

<div id="intro">
    <div class="intro-card">
        <div class="intro-emoji">🎮</div>
        <span class="intro-tag">Level <?= $level ?> · <?= htmlspecialchars($display_topic) ?></span>
        <div class="intro-title">Speak<br><?= htmlspecialchars($direction_name) ?></div>
        <p class="intro-hint">Say the word shown above in <strong><?= htmlspecialchars($direction_name) ?></strong>!</p>
        <div class="how-pills">
            <span class="how-pill">🎤 Speak clearly</span>
            <span class="how-pill">🔥 Combo</span>
            <span class="how-pill">⭐ XP</span>
        </div>
        <div class="nosrwarn" id="no-sr">⚠ Use Chrome or Edge for voice recognition</div>
        <button id="go-btn">🎯 &nbsp;Start Game</button>
    </div>
</div>

<script>
const WORDS       = <?= json_encode(array_values($words)) ?>;
const WORD_IMAGES = <?= json_encode((object)$word_images) ?>;
const SPEECH_LANG = <?= json_encode($speech_lang) ?>;
const DIRECTION   = <?= json_encode($direction) ?>;
const PARSE_INFO  = <?= json_encode([
    'method'  => $parse_method,
    'found'   => $yaml_found,
    'path'    => $yaml_path ?: 'not found',
    'words'   => count($words),
    'images'  => count($word_images),
]) ?>;

console.log('Game init:', PARSE_INFO);
console.log('Speech language:', SPEECH_LANG);
console.log('Direction:', DIRECTION);
console.log('WORDS:', WORDS);

if (WORDS.length === 0) {
    console.error('No words loaded!');
    alert('Error: No words found. Please check content files.');
}

// ── Canvas setup ─────────────────────────────────────────────────
const C = document.getElementById("canvas"), ctx = C.getContext("2d");
const P = document.getElementById("pfx"), pctx = P.getContext("2d");

function resize() { 
    C.width = P.width = window.innerWidth; 
    C.height = P.height = window.innerHeight; 
}
resize(); 
window.addEventListener("resize", resize);

const ANG = -13 * Math.PI / 180;
const RDX = Math.cos(ANG), RDY = Math.sin(ANG), RNX = -RDY, RNY = RDX;
const ROAD_W = () => Math.min(C.width, C.height) * .50;
const ANCHOR = () => ({ x: C.width * .5, y: C.height * .78 });

function toScreen(along, across) {
    const a = ANCHOR();
    return { x: a.x + RDX*along + RNX*across, y: a.y + RDY*along + RNY*across };
}

function centerAlong() {
    const a = ANCHOR();
    return (C.width * .42 - a.x) / RDX;
}

if (!CanvasRenderingContext2D.prototype.roundRect) {
    CanvasRenderingContext2D.prototype.roundRect = function(x, y, w, h, r) {
        if (w < 2*r) r = w/2;
        if (h < 2*r) r = h/2;
        this.moveTo(x+r, y);
        this.lineTo(x+w-r, y);
        this.quadraticCurveTo(x+w, y, x+w, y+r);
        this.lineTo(x+w, y+h-r);
        this.quadraticCurveTo(x+w, y+h, x+w-r, y+h);
        this.lineTo(x+r, y+h);
        this.quadraticCurveTo(x, y+h, x, y+h-r);
        this.lineTo(x, y+r);
        this.quadraticCurveTo(x, y, x+r, y);
        return this;
    };
}

let dashOff = 0, glowT = 0;

function drawRoad() {
    const W = C.width, H = C.height, HALF = ROAD_W()/2, EXT = Math.max(W,H)*2;
    const a = ANCHOR();
    const pt = (al, ac) => ({ x: a.x + RDX*al + RNX*ac, y: a.y + RDY*al + RNY*ac });
    const A = pt(-EXT,-HALF), B = pt(EXT,-HALF), D = pt(EXT,HALF), E = pt(-EXT,HALF);
    glowT += .018;
    const ga = .5 + .18 * Math.sin(glowT);
    ctx.save();
    ctx.beginPath();
    ctx.rect(0,0,W,H);
    ctx.clip();
    ctx.beginPath();
    ctx.moveTo(A.x,A.y);
    ctx.lineTo(B.x,B.y);
    ctx.lineTo(D.x,D.y);
    ctx.lineTo(E.x,E.y);
    ctx.closePath();
    const rg = ctx.createLinearGradient(0, H*.55, 0, H);
    rg.addColorStop(0, "rgba(28,26,23,.82)");
    rg.addColorStop(1, "rgba(38,34,28,.96)");
    ctx.fillStyle = rg;
    ctx.fill();
    ctx.lineWidth = 3;
    ctx.strokeStyle = `rgba(74,124,89,${ga})`;
    ctx.shadowColor = `rgba(74,124,89,.7)`;
    ctx.shadowBlur = 18;
    ctx.beginPath();
    ctx.moveTo(A.x,A.y);
    ctx.lineTo(B.x,B.y);
    ctx.stroke();
    ctx.beginPath();
    ctx.moveTo(E.x,E.y);
    ctx.lineTo(D.x,D.y);
    ctx.stroke();
    ctx.shadowBlur = 0;
    ctx.lineWidth = 1.5;
    ctx.strokeStyle = "rgba(249,244,238,.12)";
    ctx.setLineDash([26,18]);
    ctx.lineDashOffset = -dashOff;
    ctx.beginPath();
    ctx.moveTo(a.x - RDX*EXT, a.y - RDY*EXT);
    ctx.lineTo(a.x + RDX*EXT, a.y + RDY*EXT);
    ctx.stroke();
    ctx.setLineDash([]);
    ctx.restore();
    dashOff = (dashOff + 1.5) % 44;
}

// ── Image cache ──────────────────────────────────────────────────
const imgCache = {};

function preload(word) {
    if (word in imgCache) return;
    const url = WORD_IMAGES[word] || null;
    if (!url) {
        imgCache[word] = null;
        return;
    }
    const img = new Image();
    img.onload = () => { imgCache[word] = img; };
    img.onerror = () => { imgCache[word] = null; };
    img.src = url;
}

// ── Game objects ─────────────────────────────────────────────────
let sphere = null, wordQueue = [];
const SR = () => Math.min(C.width, C.height) * .15;

function enqueue() {
    wordQueue = [...WORDS].sort(() => Math.random() - .5);
    wordQueue.forEach(preload);
}

function spawnNext() {
    if (!wordQueue.length) { doFinish(); return; }
    const word = wordQueue.shift();
    preload(word);
    sphere = { 
        word, 
        along: C.width*.95, 
        speed: 0, 
        state: "INCOMING", 
        pulse: 0, 
        shake: 0, 
        shakeT: 0, 
        gone: 0 
    };
    const wd = document.getElementById("word-display");
    wd.textContent = word;
    wd.className = "";
}

function updateSphere() {
    if (!sphere) return;
    const ca = centerAlong();
    if (sphere.state === "INCOMING") {
        const d = sphere.along - ca;
        sphere.speed = sphere.speed * .84 + Math.max(2, d * .046) * .16;
        sphere.along -= sphere.speed;
        if (sphere.along <= ca) {
            sphere.along = ca;
            sphere.speed = 0;
            sphere.state = "WAITING";
        }
    } else if (sphere.state === "WAITING") {
        sphere.pulse += .05;
    } else if (sphere.state === "LEAVING") {
        sphere.speed = Math.min(sphere.speed + 1.6, 24);
        sphere.along -= sphere.speed;
        sphere.gone += sphere.speed;
        if (sphere.gone > C.width * 1.4) {
            sphere = null;
            setTimeout(() => { if (gState === "PLAYING") spawnNext(); }, 320);
        }
    } else if (sphere.state === "FAIL") {
        sphere.shakeT += .38;
        sphere.shake = Math.sin(sphere.shakeT * 9) * 9 * Math.exp(-sphere.shakeT * .2);
        if (sphere.shakeT > 5) {
            sphere.state = "WAITING";
            sphere.shake = 0;
            sphere.shakeT = 0;
            sphere.along = centerAlong();
        }
    }
}

function drawSphere(cx, cy, R, state, pulse, word) {
    ctx.save();
    ctx.beginPath();
    ctx.ellipse(cx+R*.1, cy+R*.72, R*.88, R*.16, ANG, 0, Math.PI*2);
    ctx.fillStyle = "rgba(28,26,23,.45)";
    ctx.fill();
    const gc = state === "FAIL" ? `rgba(232,93,74,${.32+.12*Math.sin(pulse)})`
             : state === "LEAVING" ? "rgba(74,124,89,.42)"
             : state === "WAITING" ? `rgba(74,124,89,${.24+.16*Math.sin(pulse)})`
             : "rgba(74,124,89,.1)";
    const og = ctx.createRadialGradient(cx, cy, R*.6, cx, cy, R+18);
    og.addColorStop(0, gc);
    og.addColorStop(1, "transparent");
    ctx.beginPath();
    ctx.arc(cx, cy, R+18, 0, Math.PI*2);
    ctx.fillStyle = og;
    ctx.fill();
    const lx = cx - R*.38, ly = cy - R*.4;
    const g = ctx.createRadialGradient(lx, ly, R*.04, cx, cy, R);
    if (state === "FAIL") {
        g.addColorStop(0,"rgba(255,180,170,1)");
        g.addColorStop(.4,"rgba(232,93,74,.97)");
        g.addColorStop(.8,"rgba(179,53,38,.95)");
        g.addColorStop(1,"rgba(60,10,6,1)");
    } else if (state === "LEAVING") {
        g.addColorStop(0,"rgba(190,240,210,1)");
        g.addColorStop(.4,"rgba(74,124,89,.97)");
        g.addColorStop(.8,"rgba(47,94,62,.95)");
        g.addColorStop(1,"rgba(12,30,18,1)");
    } else {
        g.addColorStop(0,"rgba(200,230,255,1)");
        g.addColorStop(.28,"rgba(59,130,196,.97)");
        g.addColorStop(.65,"rgba(47,94,62,.92)");
        g.addColorStop(1,"rgba(12,20,38,1)");
    }
    ctx.beginPath();
    ctx.arc(cx, cy, R, 0, Math.PI*2);
    ctx.fillStyle = g;
    ctx.fill();
    const sg = ctx.createRadialGradient(lx, ly, 0, lx, ly, R*.46);
    sg.addColorStop(0,"rgba(255,255,255,.55)");
    sg.addColorStop(1,"rgba(255,255,255,0)");
    ctx.beginPath();
    ctx.arc(cx, cy, R, 0, Math.PI*2);
    ctx.fillStyle = sg;
    ctx.fill();
    const rc = state === "FAIL" ? "rgba(232,93,74,.9)"
             : state === "LEAVING" ? "rgba(74,124,89,.9)"
             : `rgba(154,220,185,${.5+.28*Math.sin(pulse)})`;
    ctx.shadowColor = rc;
    ctx.shadowBlur = state === "WAITING" ? 18 : 6;
    ctx.strokeStyle = rc;
    ctx.lineWidth = 2;
    ctx.beginPath();
    ctx.arc(cx, cy, R, 0, Math.PI*2);
    ctx.stroke();
    ctx.shadowBlur = 0;
    const fs = Math.max(10, Math.round(R * .36));
    ctx.font = `900 ${fs}px 'Baloo 2',cursive`;
    ctx.textAlign = "center";
    ctx.textBaseline = "middle";
    ctx.shadowColor = "rgba(0,0,0,.65)";
    ctx.shadowBlur = 5;
    ctx.fillStyle = state === "FAIL" ? "#ffc8c0" : state === "LEAVING" ? "#d0f5e4" : "#fff";
    ctx.fillText(state === "LEAVING" ? "✓" : word, cx, cy);
    ctx.restore();
}

function drawImageCard(cx, cy, R, state, pulse) {
    if (!sphere) return;
    const W = R*1.7, H = R*1.7;
    const x = cx - W/2;
    const bob = state === "WAITING" ? Math.sin(pulse * .8) * 4 : 0;
    const y = cy - R - H - 14 + bob;
    const r = 14, pad = 5;

    ctx.save();
    ctx.shadowColor = "rgba(28,26,23,.4)";
    ctx.shadowBlur = 20;
    ctx.shadowOffsetY = 6;
    ctx.beginPath();
    ctx.roundRect(x, y, W, H, r);
    ctx.fillStyle = "rgba(249,244,238,.96)";
    ctx.fill();
    ctx.shadowBlur = 0;
    ctx.shadowOffsetY = 0;

    const bc = state === "FAIL" ? "rgba(232,93,74,.7)"
             : state === "LEAVING" ? "rgba(74,124,89,.7)"
             : state === "WAITING" ? `rgba(74,124,89,${.35+.2*Math.sin(pulse)})`
             : "rgba(229,221,213,.8)";
    ctx.strokeStyle = bc;
    ctx.lineWidth = 2;
    ctx.shadowColor = bc;
    ctx.shadowBlur = state === "WAITING" ? 10 : 0;
    ctx.beginPath();
    ctx.roundRect(x, y, W, H, r);
    ctx.stroke();
    ctx.shadowBlur = 0;

    ctx.save();
    ctx.beginPath();
    ctx.roundRect(x+pad, y+pad, W-pad*2, H-pad*2, r-2);
    ctx.clip();

    const cached = imgCache[sphere.word];
    const img = (cached && cached !== 'loading') ? cached : null;

    if (img && img.complete && img.naturalWidth > 0) {
        try {
            ctx.drawImage(img, x+pad, y+pad, W-pad*2, H-pad*2);
            if (state === "FAIL") {
                ctx.fillStyle = "rgba(232,93,74,.18)";
                ctx.fillRect(x+pad, y+pad, W-pad*2, H-pad*2);
            }
            if (state === "LEAVING") {
                ctx.fillStyle = "rgba(74,124,89,.12)";
                ctx.fillRect(x+pad, y+pad, W-pad*2, H-pad*2);
            }
        } catch(e) {
            ctx.fillStyle = "rgba(249,244,238,1)";
            ctx.fillRect(x+pad, y+pad, W-pad*2, H-pad*2);
        }
    } else {
        ctx.fillStyle = "rgba(220,240,226,1)";
        ctx.fillRect(x+pad, y+pad, W-pad*2, H-pad*2);
        ctx.font = `900 ${Math.round(R*.5)}px 'Baloo 2',cursive`;
        ctx.textAlign = "center";
        ctx.textBaseline = "middle";
        ctx.fillStyle = "rgba(47,94,62,.35)";
        ctx.fillText(sphere.word.charAt(0), cx, y+H/2);
    }
    ctx.restore();

    ctx.strokeStyle = bc;
    ctx.lineWidth = 1.5;
    ctx.setLineDash([5,4]);
    ctx.globalAlpha = .35;
    ctx.beginPath();
    ctx.moveTo(cx, y+H);
    ctx.lineTo(cx, cy-R);
    ctx.stroke();
    ctx.setLineDash([]);
    ctx.globalAlpha = 1;
    ctx.restore();
}

function renderSphere() {
    if (!sphere) return;
    const pos = toScreen(sphere.along, 0);
    const cx = pos.x + (sphere.state === "FAIL" ? sphere.shake : 0), cy = pos.y, R = SR();
    if (sphere.state !== "INCOMING") drawImageCard(cx, cy, R, sphere.state, sphere.pulse);
    drawSphere(cx, cy, R, sphere.state, sphere.pulse, sphere.word);
}

// ── Particles ────────────────────────────────────────────────────
let parts = [];
const COLS = ["#4A7C59","#F5A623","#E85D4A","#3B82C4","#F9F4EE","#DCF0E2","#FFF2D8"];

function burst(x, y, n=22) {
    for (let i = 0; i < n; i++) {
        const a = (Math.PI*2/n)*i + Math.random()*.7, s = 2 + Math.random()*9;
        parts.push({ 
            x, y, 
            vx: Math.cos(a)*s, 
            vy: Math.sin(a)*s-2, 
            life: 1,
            dec: .018 + Math.random()*.014, 
            r: 2 + Math.random()*4,
            col: COLS[Math.floor(Math.random()*COLS.length)],
            sq: Math.random() > .5, 
            rot: 0, 
            rv: (Math.random()-.5)*.25 
        });
    }
}

function drawParts() {
    pctx.clearRect(0, 0, P.width, P.height);
    parts = parts.filter(p => {
        p.x += p.vx;
        p.y += p.vy;
        p.vy += .16;
        p.vx *= .99;
        p.life -= p.dec;
        p.rot += p.rv;
        if (p.life <= 0) return false;
        pctx.save();
        pctx.globalAlpha = p.life * p.life;
        pctx.translate(p.x, p.y);
        pctx.rotate(p.rot);
        pctx.fillStyle = p.col;
        if (p.sq) {
            pctx.fillRect(-p.r/2, -p.r/2, p.r, p.r);
        } else {
            pctx.beginPath();
            pctx.arc(0, 0, p.r, 0, Math.PI*2);
            pctx.fill();
        }
        pctx.restore();
        return true;
    });
}

// ── Audio ────────────────────────────────────────────────────────
let actx, mGain, lpf, beatTimer, beatN = 0;
const BPM = 100, BEAT = (60/BPM)*1000;

function buildAudio() {
    if (actx) actx.close();
    actx = new (window.AudioContext || window.webkitAudioContext)();
    mGain = actx.createGain();
    mGain.gain.value = .36;
    lpf = actx.createBiquadFilter();
    lpf.type = "lowpass";
    lpf.frequency.value = 22000;
    mGain.connect(lpf);
    lpf.connect(actx.destination);
    beatN = 0;
    clearInterval(beatTimer);
    beatTimer = setInterval(beat, BEAT);
}

function beat() {
    if (!actx) return;
    const t = actx.currentTime;
    if (beatN%4===1 || beatN%4===3) {
        const o = actx.createOscillator(), g = actx.createGain();
        o.connect(g);
        g.connect(mGain);
        o.type = "sine";
        o.frequency.setValueAtTime(130,t);
        o.frequency.exponentialRampToValueAtTime(32,t+.17);
        g.gain.setValueAtTime(.58,t);
        g.gain.exponentialRampToValueAtTime(.001,t+.24);
        o.start(t);
        o.stop(t+.25);
    }
    if (beatN%4===2 || beatN%4===0) {
        const b = actx.createBuffer(1, actx.sampleRate*.09, actx.sampleRate);
        const d = b.getChannelData(0);
        for (let i = 0; i < d.length; i++) d[i] = (Math.random()*2-1) * Math.exp(-i/(actx.sampleRate*.038));
        const s = actx.createBufferSource(), g = actx.createGain();
        s.buffer = b;
        s.connect(g);
        g.connect(mGain);
        g.gain.setValueAtTime(.35,t);
        g.gain.exponentialRampToValueAtTime(.001,t+.09);
        s.start(t);
    }
    beatN++;
}

function playOk() {
    if (!actx) return;
    const t = actx.currentTime;
    [0,.06,.12].forEach((d,i) => {
        const o = actx.createOscillator(), g = actx.createGain();
        o.connect(g);
        g.connect(mGain);
        o.type = "sine";
        o.frequency.value = [1046,1318,1568][i];
        g.gain.setValueAtTime(.24,t+d);
        g.gain.exponentialRampToValueAtTime(.001,t+d+.22);
        o.start(t+d);
        o.stop(t+d+.23);
    });
}

function playBad() {
    if (!actx) return;
    const t = actx.currentTime;
    const o = actx.createOscillator(), g = actx.createGain();
    o.connect(g);
    g.connect(mGain);
    o.type = "sawtooth";
    o.frequency.setValueAtTime(165,t);
    o.frequency.exponentialRampToValueAtTime(42,t+.25);
    g.gain.setValueAtTime(.3,t);
    g.gain.exponentialRampToValueAtTime(.001,t+.27);
    o.start(t);
    o.stop(t+.28);
}

function setMuffle(on) {
    if (!actx) return;
    lpf.frequency.setTargetAtTime(on ? 270 : 22000, actx.currentTime, .18);
    mGain.gain.setTargetAtTime(on ? .16 : .36, actx.currentTime, .18);
}

// ── Speech recognition ───────────────────────────────────────────
const SR_API = window.SpeechRecognition || window.webkitSpeechRecognition;
let recog = null, srOn = false;

function initSR() {
    if (!SR_API) return;
    recog = new SR_API();
    recog.continuous = true;
    recog.interimResults = true;
    recog.lang = SPEECH_LANG;
    
    recog.onresult = e => {
        for (let i = e.resultIndex; i < e.results.length; i++) {
            const txt = e.results[i][0].transcript.toUpperCase().trim();
            onHeard(txt, e.results[i].isFinal);
        }
    };
    recog.onend = () => { 
        srOn = false; 
        if (gState !== "IDLE") setTimeout(startSR, 300); 
    };
    recog.onerror = e => { 
        srOn = false; 
        if (e.error !== "no-speech" && e.error !== "aborted") setTimeout(startSR, 400); 
    };
}

function startSR() { 
    if (!recog || srOn) return; 
    try { recog.start(); srOn = true; } catch(e) {} 
}

function stopSR() { 
    if (!recog || !srOn) return; 
    try { recog.stop(); } catch(e) {} 
    srOn = false; 
}

function onHeard(text, final) {
    if (gState === "IDLE" || !sphere || sphere.state !== "WAITING") return;
    showHeard(text, false);
    const ws = text.split(/\s+/), tgt = sphere.word;
    if (ws.some(w => w === tgt || lev(w, tgt) <= 2)) correct();
    else if (final) wrong(text);
}

function lev(a, b) {
    const m = a.length, n = b.length;
    const dp = Array(m+1).fill(0).map(() => Array(n+1).fill(0));
    for (let i = 0; i <= m; i++) dp[i][0] = i;
    for (let j = 0; j <= n; j++) dp[0][j] = j;
    for (let i = 1; i <= m; i++)
        for (let j = 1; j <= n; j++)
            dp[i][j] = a[i-1] === b[j-1] ? dp[i-1][j-1] : 1 + Math.min(dp[i-1][j], dp[i][j-1], dp[i-1][j-1]);
    return dp[m][n];
}

// ── Game logic ───────────────────────────────────────────────────
let gState = "IDLE", score = 0, combo = 0, maxCombo = 0;

function correct() {
    if (!sphere || sphere.state !== "WAITING") return;
    sphere.state = "LEAVING";
    sphere.speed = 3;
    sphere.gone = 0;
    hideHeard();
    document.getElementById("word-display").className = "win";
    const pos = toScreen(sphere.along, 0);
    burst(pos.x, pos.y, 24);
    playOk();
    setMuffle(false);
    combo++;
    maxCombo = Math.max(maxCombo, combo);
    score += 10 + combo*5;
    updateScore();
    showCombo();
}

function wrong(heard) {
    if (!sphere || sphere.state !== "WAITING") return;
    sphere.state = "FAIL";
    sphere.shakeT = 0;
    showHeard(heard, true);
    document.getElementById("word-display").className = "fail";
    setTimeout(() => { 
        const wd = document.getElementById("word-display"); 
        if (wd.className === "fail") wd.className = ""; 
    }, 380);
    playBad();
    setMuffle(true);
    combo = 0;
    updateScore();
}

function updateScore() {
    const el = document.getElementById("score-v");
    el.textContent = score;
    el.classList.remove("pop");
    void el.offsetWidth;
    el.classList.add("pop");
    const cl = document.getElementById("combo-live");
    if (cl) cl.textContent = combo >= 2 ? `×${combo}` : "×1";
}

let comboT;

function showCombo() {
    if (combo < 2) return;
    const el = document.getElementById("combo-pop");
    el.textContent = combo >= 6 ? `🔥 ×${combo}` : `×${combo}`;
    el.classList.remove("show");
    void el.offsetWidth;
    el.classList.add("show");
    clearTimeout(comboT);
    comboT = setTimeout(() => el.classList.remove("show"), 1000);
}

const heardEl = document.getElementById("heard");
let heardT;

function showHeard(text, bad) {
    if (!sphere) return;
    const R = SR(), pos = toScreen(sphere.along, 0);
    heardEl.textContent = text;
    heardEl.style.left = pos.x + "px";
    heardEl.style.top = (pos.y + R + 26) + "px";
    heardEl.className = "show" + (bad ? " bad" : "");
    clearTimeout(heardT);
    heardT = setTimeout(hideHeard, 1500);
}

function hideHeard() { 
    heardEl.className = ""; 
}

function doFinish() {
    gState = "IDLE";
    stopSR();
    clearInterval(beatTimer);
    document.getElementById("fin-score").textContent = score;
    document.getElementById("fin-combo").textContent = maxCombo;
    document.getElementById("finished").style.display = "flex";
    burst(innerWidth/2, innerHeight/2, 28);
    setTimeout(() => burst(innerWidth*.3, innerHeight*.38, 16), 260);
    setTimeout(() => burst(innerWidth*.7, innerHeight*.38, 16), 460);
}

// ── Animation loop ───────────────────────────────────────────────
function loop() {
    ctx.clearRect(0, 0, C.width, C.height);
    drawRoad();
    updateSphere();
    renderSphere();
    drawParts();
    requestAnimationFrame(loop);
}

// ── Game initialization ──────────────────────────────────────────
function startGame() {
    score = 0;
    combo = 0;
    maxCombo = 0;
    gState = "PLAYING";
    sphere = null;
    wordQueue = [];
    parts = [];
    updateScore();
    hideHeard();
    document.getElementById("word-display").textContent = "—";
    document.getElementById("word-display").className = "";
    document.getElementById("finished").style.display = "none";
    enqueue();
    buildAudio();
    startSR();
    setTimeout(spawnNext, 600);
}

// ── Event listeners ──────────────────────────────────────────────
if (!SR_API) document.getElementById("no-sr").style.display = "block";

document.getElementById("replay-btn").addEventListener("click", startGame);
document.getElementById("go-btn").addEventListener("click", async () => {
    document.getElementById("intro").style.display = "none";
    try {
        const s = await navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" }, audio: true });
        document.getElementById("video").srcObject = s;
        s.getAudioTracks().forEach(t => t.stop());
    } catch(e) {
        console.log("Camera failed:", e);
        document.getElementById("video").style.display = "none";
    }
    initSR();
    if (!window._loopStarted) {
        requestAnimationFrame(loop);
        window._loopStarted = true;
    }
    startGame();
});

document.getElementById("close-btn").addEventListener("click", () => {
    if (recog && srOn) try { recog.stop(); } catch(e) {}
    if (beatTimer) clearInterval(beatTimer);
    if (actx) actx.close().catch(() => {});
});
</script>
</body>
</html>