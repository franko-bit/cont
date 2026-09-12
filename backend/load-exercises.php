<?php
header('Content-Type: application/json');

function sendResponse($success, $message, $data = null) {
    echo json_encode(["success" => $success, "message" => $message, "data" => $data]);
    exit;
}

function _yaml_scalar($v) {
    $v = trim($v);
    if (strlen($v) >= 2) {
        $f = $v[0];
        $l = $v[strlen($v) - 1];
        if (($f === '"' && $l === '"') || ($f === "'" && $l === "'")) {
            return substr($v, 1, -1);
        }
    }
    $lower = strtolower($v);
    if ($lower === 'true') {
        return true;
    }
    if ($lower === 'false') {
        return false;
    }
    if ($lower === 'null' || $v === '~') {
        return null;
    }
    if (is_numeric($v)) {
        return $v + 0;
    }
    return $v;
}

function _yaml_inline_seq($str) {
    $str = trim($str);
    if (!$str || $str[0] !== '[') {
        return [];
    }
    $inner = substr($str, 1, strrpos($str, ']') - 1);
    $items = [];
    $depth = 0;
    $cur = '';
    $inq = false;
    $qc = '';
    $len = strlen($inner);
    for ($i = 0; $i < $len; $i++) {
        $c = $inner[$i];
        if (!$inq && ($c === '"' || $c === "'")) {
            $inq = true;
            $qc = $c;
            $cur .= $c;
        } elseif ($inq && $c === $qc) {
            $inq = false;
            $cur .= $c;
        } elseif (!$inq && $c === '[') {
            $depth++;
            $cur .= $c;
        } elseif (!$inq && $c === ']') {
            $depth--;
            $cur .= $c;
        } elseif (!$inq && $c === ',' && $depth === 0) {
            $items[] = _yaml_scalar(trim($cur));
            $cur = '';
        } else {
            $cur .= $c;
        }
    }
    if (trim($cur) !== '') {
        $items[] = _yaml_scalar(trim($cur));
    }
    return $items;
}

function parse_yaml_manual($yaml) {
    $yaml = str_replace(["\r\n", "\r"], "\n", $yaml);
    $lines = explode("\n", $yaml);
    $n = count($lines);
    $pool = [[]];
    $pi = 1;
    $stk = [[-1, 0]];
    $i = 0;

    while ($i < $n) {
        $raw = $lines[$i];
        $i++;
        $t = rtrim($raw);
        $tr = trim($t);
        if ($tr === '' || $tr[0] === '#') {
            continue;
        }
        $ind = strlen($t) - strlen(ltrim($t));
        if ($tr[0] !== '"' && $tr[0] !== "'") {
            $tr = trim(preg_replace("/\\s+#[^\"']*$/", '', $tr));
        }
        if ($tr === '') {
            continue;
        }

        while (count($stk) > 1 && $stk[count($stk) - 1][0] >= $ind) {
            array_pop($stk);
        }
        $si = count($stk) - 1;
        $pIdx = $stk[$si][1];

        $dash = false;
        if ($tr[0] === '-' && (strlen($tr) === 1 || $tr[1] === ' ' || $tr[1] === "\t")) {
            $dash = true;
            $tr = strlen($tr) > 1 ? trim(substr($tr, 2)) : '';
        }

        if ($tr === '' && $dash) {
            $pool[$pi] = [];
            $pool[$pIdx][] = &$pool[$pi];
            array_push($stk, [$ind, $pi]);
            $pi++;
            continue;
        }

        if (strlen($tr) > 0 && $tr[0] === '[' && $dash) {
            $pool[$pIdx][] = _yaml_inline_seq($tr);
            continue;
        }

        if (preg_match('/^([^:]+):\s*(.*)$/', $tr, $m)) {
            $key = trim($m[1]);
            $val = trim($m[2]);
            if (strlen($key) >= 2) {
                $f = $key[0];
                $l = $key[strlen($key) - 1];
                if (($f === '"' && $l === '"') || ($f === "'" && $l === "'")) {
                    $key = substr($key, 1, -1);
                }
            }
            if ($val !== '' && $val[0] !== '"' && $val[0] !== "'") {
                $val = trim(preg_replace("/\\s+#[^\"']*$/", '', $val));
            }

            if ($dash) {
                $pool[$pi] = [];
                $pool[$pIdx][] = &$pool[$pi];
                $itemIdx = $pi++;
                array_push($stk, [$ind, $itemIdx]);
                $si = count($stk) - 1;
                $pIdx = $itemIdx;
            }

            if ($val === '' || $val === '|' || $val === '>') {
                if ($val === '|' || $val === '>') {
                    $block = '';
                    $base = -1;
                    while ($i < $n) {
                        $bl = $lines[$i];
                        if (trim($bl) === '') {
                            $block .= "\n";
                            $i++;
                            continue;
                        }
                        $bi = strlen($bl) - strlen(ltrim($bl));
                        if ($base < 0) {
                            $base = $bi;
                        }
                        if ($bi < $base) {
                            break;
                        }
                        $block .= substr($bl, $base) . "\n";
                        $i++;
                    }
                    $pool[$pIdx][$key] = rtrim($block);
                } else {
                    $pool[$pi] = [];
                    $pool[$pIdx][$key] = &$pool[$pi];
                    array_push($stk, [$ind, $pi]);
                    $pi++;
                }
            } elseif (strlen($val) > 0 && $val[0] === '[') {
                $pool[$pIdx][$key] = _yaml_inline_seq($val);
            } else {
                $pool[$pIdx][$key] = _yaml_scalar($val);
            }
        } elseif ($dash) {
            $pool[$pIdx][] = _yaml_scalar($tr);
        }
    }
    return $pool[0];
}

function parse_yaml_content($content) {
    if (function_exists('yaml_parse')) {
        $data = @yaml_parse($content);
        if ($data !== false && is_array($data)) {
            return $data;
        }
    }
    return parse_yaml_manual($content);
}

function normalizeType($type) {
    return str_replace(['-', ' '], '_', strtolower(trim($type)));
}

function normalizeTopic($topic) {
    return preg_replace('/[^a-z0-9]+/', '-', strtolower(trim($topic)));
}

function matchTopic($topic, $filename, $lessonName) {
    $topic = normalizeTopic($topic);
    if ($topic === '') {
        return true;
    }
    $base = normalizeTopic(pathinfo($filename, PATHINFO_FILENAME));
    $lesson = normalizeTopic($lessonName);
    return strpos($base, $topic) !== false || strpos($lesson, $topic) !== false;
}

$direction = strtolower(trim($_GET['direction'] ?? 'en-rw'));
$totalXp = max(1, intval($_GET['total_xp'] ?? 1000));
$levelsArg = strtolower(trim($_GET['levels'] ?? 'all'));
$topicsArg = trim($_GET['topics'] ?? '');
$typesArg = trim($_GET['types'] ?? 'translation,multiple_choice,listen_and_type');

$directionMap = [
    'en-rw' => 'EN-TO-RW',
    'fr-rw' => 'FR-TO-RW',
    'rw-en' => 'RW-TO-EN',
    'rw-fr' => 'RW-TO-FR',
    'en-sw' => 'EN-TO-SW',
    'sw-en' => 'SW-TO-EN',
    'fr-sw' => 'FR-TO-SW',
    'sw-fr' => 'SW-TO-FR',
];

if (!isset($directionMap[$direction])) {
    sendResponse(false, 'Unsupported translation direction. Use en-rw, fr-rw, rw-en, rw-fr, en-sw, sw-en, fr-sw, or sw-fr.');
}

$folder = $directionMap[$direction];

if ($levelsArg === 'all') {
    $levels = range(1, 6);
} else {
    $levels = array_filter(array_map('intval', explode(',', $levelsArg)), fn($value) => $value >= 1 && $value <= 6);
    $levels = array_values(array_unique($levels));
    if (empty($levels)) {
        sendResponse(false, 'Invalid levels provided. Use all or a list of 1-6.');
    }
}

$requestedTopics = [];
if ($topicsArg !== '') {
    foreach (explode(',', $topicsArg) as $topic) {
        $topic = trim($topic);
        if ($topic !== '') {
            $requestedTopics[] = $topic;
        }
    }
}

$requestedTypes = [];
foreach (explode(',', $typesArg) as $type) {
    $type = normalizeType($type);
    if ($type !== '') {
        $requestedTypes[] = $type;
    }
}
if (empty($requestedTypes)) {
    $requestedTypes = ['translation', 'multiple_choice', 'listen_and_type'];
}

$contentRoot = dirname(__DIR__) . "/content/{$folder}";
if (!is_dir($contentRoot)) {
    sendResponse(false, 'Content directory does not exist for the selected translation direction.');
}

$exercises = [];
$availableXp = 0;

foreach ($levels as $level) {
    $levelDir = "$contentRoot/level{$level}";
    if (!is_dir($levelDir)) {
        continue;
    }
    $files = scandir($levelDir);
    foreach ($files as $file) {
        if (empty($file) || pathinfo($file, PATHINFO_EXTENSION) !== 'yaml') {
            continue;
        }
        $lessonPath = "$levelDir/$file";
        $yamlContent = @file_get_contents($lessonPath);
        if ($yamlContent === false) {
            continue;
        }
        $parsed = parse_yaml_content($yamlContent);
        if (!is_array($parsed) || !isset($parsed['exercises']) || !is_array($parsed['exercises'])) {
            continue;
        }
        $lessonName = $parsed['lesson']['name'] ?? pathinfo($file, PATHINFO_FILENAME);
        if (!empty($requestedTopics)) {
            $matches = false;
            foreach ($requestedTopics as $topic) {
                if (matchTopic($topic, $file, $lessonName)) {
                    $matches = true;
                    break;
                }
            }
            if (!$matches) {
                continue;
            }
        }

        foreach ($parsed['exercises'] as $exercise) {
            if (!is_array($exercise) || !isset($exercise['type'])) {
                continue;
            }
            $exerciseType = normalizeType($exercise['type']);
            if (!in_array($exerciseType, $requestedTypes, true)) {
                continue;
            }
            $xpReward = isset($exercise['xp_reward']) ? intval($exercise['xp_reward']) : 0;
            $availableXp += $xpReward;
            $exercises[] = [
                'level' => $level,
                'topic' => $lessonName,
                'topic_file' => $file,
                'question_id' => $exercise['id'] ?? null,
                'type' => $exerciseType,
                'question' => $exercise['question'] ?? '',
                'answer' => $exercise['answer'] ?? null,
                'options' => $exercise['options'] ?? null,
                'xp_reward' => $xpReward,
                'source' => "{$folder}/level{$level}/{$file}",
            ];
        }
    }
}

if (empty($exercises)) {
    sendResponse(false, 'No exercises were found for the selected direction, levels, topics, or exercise types.');
}

// Sort by level and type, then preserve the file order.
usort($exercises, function ($a, $b) {
    if ($a['level'] !== $b['level']) {
        return $a['level'] - $b['level'];
    }
    return strcmp($a['topic'], $b['topic']);
});

$selected = [];
$selectedXp = 0;
foreach ($exercises as $exercise) {
    if ($selectedXp >= $totalXp) {
        break;
    }
    if ($selectedXp + $exercise['xp_reward'] > $totalXp) {
        continue;
    }
    $selected[] = $exercise;
    $selectedXp += $exercise['xp_reward'];
}

// If exact match is not possible, pick the smallest remaining question to exceed the target.
if ($selectedXp < $totalXp) {
    foreach ($exercises as $exercise) {
        if (in_array($exercise, $selected, true)) {
            continue;
        }
        if ($selectedXp + $exercise['xp_reward'] >= $totalXp) {
            $selected[] = $exercise;
            $selectedXp += $exercise['xp_reward'];
            break;
        }
    }
}

$data = [
    'direction' => $direction,
    'levels' => $levels,
    'topics' => $requestedTopics,
    'types' => $requestedTypes,
    'total_xp_target' => $totalXp,
    'available_xp' => $availableXp,
    'selected_xp' => $selectedXp,
    'selected_questions' => count($selected),
    'selected_exercises' => $selected,
];

sendResponse(true, 'Exercises loaded successfully.', $data);
