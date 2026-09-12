<?php
require_once __DIR__ . '/config.php';

set_time_limit(0);
ignore_user_abort(true);

function jsonError($message, $code = 400) {
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'message' => $message,
    ]);
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

function sanitizeAudioKey($text) {
    $text = trim((string)$text);
    if ($text === '') {
        return 'audio';
    }

    $text = preg_replace('/[^\p{L}\p{N}]+/u', '_', $text);
    $text = trim((string)$text, '_');
    $text = preg_replace('/_+/', '_', $text);
    return strtolower((string)$text);
}

function ensureAudioDir() {
    $audioDir = dirname(__DIR__) . '/audio/swahili';
    if (!is_dir($audioDir) && !mkdir($audioDir, 0777, true) && !is_dir($audioDir)) {
        return false;
    }
    return $audioDir;
}

function generateAudioFile($text, $audioDir) {

    $key = sanitizeAudioKey($text);
    $audioFile = $key . '.mp3';
    $audioPath = $audioDir . '/' . $audioFile;

    if (file_exists($audioPath)) {
        return [
            'status' => 'cached',
            'file' => $audioFile,
        ];
    }

    $apiKey = defined('ELEVENLABS_API_KEY') ? ELEVENLABS_API_KEY : '';
    $voiceId = defined('ELEVENLABS_VOICE_ID') ? ELEVENLABS_VOICE_ID : '';

    if ($apiKey === '' || $voiceId === '') {
        return [
            'status' => 'missing-config',
            'file' => $audioFile,
        ];
    }

    $endpoint = 'https://api.elevenlabs.io/v1/text-to-speech/' . rawurlencode($voiceId);

    /* ✔ FIXED PAYLOAD (CLEAN + SWAHILI SAFE) */
    $payload = [
        'text' => $text,
        'model_id' => 'eleven_multilingual_v2',
        'voice_settings' => [
            'stability' => 0.6,
            'similarity_boost' => 0.9,
            'style' => 0.4,
            'use_speaker_boost' => true
        ],
    ];

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'xi-api-key: ' . $apiKey,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === false || $httpCode !== 200) {
        return [
            'status' => 'error',
            'file' => $audioFile,
            'message' => ($curlError ?: 'HTTP ' . $httpCode),
        ];
    }

    file_put_contents($audioPath, $response);

    return [
        'status' => 'generated',
        'file' => $audioFile,
    ];
}
// Extract tts_text and audio from exercises with specific types
function collectTextCandidates($value, &$texts) {
    if (is_array($value)) {
        // Check if this is an exercise object with a type field
        if (isset($value['type'])) {
            $exerciseType = $value['type'];
            
            // Only process exercises with these types
            $allowedTypes = [
                'listen_and_type',   // Listen and Type
                'listen_and_choose', // Listen and Choose
                'tap_hear',          // Tap What You Hear
                'speaking',          // Speaking Exercises
                'pronunciation'      // Pronunciation Exercises
            ];
            
            // Extract tts_text (Level 1)
            if (isset($value['tts_text']) && in_array($exerciseType, $allowedTypes)) {
                $ttsText = $value['tts_text'];
                if (is_string($ttsText) && trim($ttsText) !== '') {
                    $texts[] = trim($ttsText);
                }
            }
            
            // Extract audio field (Levels 2-6)
            if (isset($value['audio']) && in_array($exerciseType, $allowedTypes)) {
                $audioText = $value['audio'];
                if (is_string($audioText) && trim($audioText) !== '') {
                    $texts[] = trim($audioText);
                }
            }
        }
        
        // Recursively process nested arrays
        foreach ($value as $k => $v) {
            if (is_array($v)) {
                collectTextCandidates($v, $texts);
            }
        }
    }
}

function getSwahiliContentRoots($contentRoot) {
    $roots = [];
    if (!is_dir($contentRoot)) {
        return $roots;
    }

    foreach (scandir($contentRoot) as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }
        $path = $contentRoot . '/' . $entry;
        if (!is_dir($path)) {
            continue;
        }

        // Only process EN-TO-SW (English to Kiswahili) content
        if ($entry === 'EN-TO-SW') {
            $roots[] = $path;
        }
    }

    return $roots;
}

function scanContentForSwahiliTexts($contentRoot) {
    $texts = [];
    $seen = [];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($contentRoot, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if (!$file->isFile() || strtolower($file->getExtension()) !== 'yaml') {
            continue;
        }

        $yamlContent = @file_get_contents($file->getPathname());
        if ($yamlContent === false) {
            continue;
        }

        $parsed = parse_yaml_content($yamlContent);
        if (!is_array($parsed)) {
            continue;
        }

        collectTextCandidates($parsed, $texts);
    }

    foreach ($texts as $text) {
        $norm = preg_replace('/\s+/', ' ', trim($text));
        if ($norm === '') {
            continue;
        }
        $key = strtolower($norm);
        if (!isset($seen[$key])) {
            $seen[$key] = $norm;
        }
    }

    return array_values($seen);
}

$audioDir = ensureAudioDir();
if ($audioDir === false) {
    jsonError('Could not create audio cache directory.');
}

$contentRoot = dirname(__DIR__) . '/content';
$swRootList = getSwahiliContentRoots($contentRoot);
if (empty($swRootList)) {
    jsonError('No EN-TO-SW (English to Kiswahili) content folders were found.');
}

$allTexts = [];
foreach ($swRootList as $swRoot) {
    $allTexts = array_merge($allTexts, scanContentForSwahiliTexts($swRoot));
}

$results = [
    'scanned' => count($allTexts),
    'generated' => 0,
    'cached' => 0,
    'errors' => 0,
    'details' => [],
];

foreach ($allTexts as $text) {
    $result = generateAudioFile($text, $audioDir);
    $results['details'][] = [
        'text' => $text,
        'status' => $result['status'],
        'file' => $result['file'] ?? null,
        'message' => $result['message'] ?? null,
    ];

    if ($result['status'] === 'generated') {
        $results['generated']++;
    } elseif ($result['status'] === 'cached') {
        $results['cached']++;
    } elseif ($result['status'] !== 'missing-config') {
        $results['errors']++;
    }
}

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'Batch generation finished.',
    'data' => $results,
]);