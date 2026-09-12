<?php
require_once __DIR__ . '/config.php';

set_time_limit(0);
ignore_user_abort(true);

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
            $pool[$pIdx][] = $tr;
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
            } else {
                $pool[$pIdx][$key] = $val;
            }
        } elseif ($dash) {
            $pool[$pIdx][] = $tr;
        }
    }
    return $pool[0];
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

function generateAudioFile($text, $audioDir) {
    $key = sanitizeAudioKey($text);
    $audioFile = $key . '.mp3';
    $audioPath = $audioDir . '/' . $audioFile;

    if (file_exists($audioPath)) {
        return ['status' => 'cached', 'file' => $audioFile];
    }

    $apiKey = defined('ELEVENLABS_API_KEY') ? ELEVENLABS_API_KEY : '';
    $voiceId = defined('ELEVENLABS_VOICE_ID') ? ELEVENLABS_VOICE_ID : '';
    if ($apiKey === '' || $voiceId === '') {
        return ['status' => 'missing-config', 'file' => $audioFile];
    }

    $endpoint = 'https://api.elevenlabs.io/v1/text-to-speech/' . rawurlencode($voiceId);
    $payload = [
        'text' => $text,
        'model_id' => 'eleven_multilingual_v2',
        'voice_settings' => [
            'stability' => 0.6,
            'similarity_boost' => 0.9,
            'style' => 0.5
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

    if (!file_put_contents($audioPath, $response)) {
        return ['status' => 'write-error', 'file' => $audioFile];
    }

    usleep(300000);
    return ['status' => 'generated', 'file' => $audioFile];
}

function collectTextCandidates($value, &$texts) {
    if (is_array($value)) {
        foreach ($value as $k => $v) {
            if (is_string($k) && ($k === 'tts_text' || $k === 'audio' || $k === 'prompt')) {
                if (is_string($v) && trim($v) !== '') {
                    $texts[] = trim($v);
                }
            }
            if (is_array($v)) {
                collectTextCandidates($v, $texts);
            }
        }
        return;
    }
}

$audioDir = dirname(__DIR__) . '/audio/swahili';
if (!is_dir($audioDir)) {
    mkdir($audioDir, 0777, true);
}

$contentRoot = dirname(__DIR__) . '/content/FR-TO-SW';
if (!is_dir($contentRoot)) {
    echo "FR-TO-SW content folder not found.\n";
    exit;
}

$allTexts = [];
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

    $parsed = parse_yaml_manual($yamlContent);
    if (!is_array($parsed)) {
        continue;
    }

    collectTextCandidates($parsed, $allTexts);
}

$seen = [];
foreach ($allTexts as $text) {
    $norm = preg_replace('/\s+/', ' ', trim($text));
    if ($norm === '') {
        continue;
    }
    $key = strtolower($norm);
    if (!isset($seen[$key])) {
        $seen[$key] = $norm;
    }
}

$uniqueTexts = array_values($seen);

echo "Scanning FR-TO-SW YAML files...\n";
echo "Found " . count($uniqueTexts) . " unique texts to check/generate.\n\n";

$generated = 0;
$cached = 0;
$errors = 0;

foreach ($uniqueTexts as $text) {
    $result = generateAudioFile($text, $audioDir);
    
    if ($result['status'] === 'generated') {
        echo "  " . $text . " → " . $result['file'] . "\n";
        $generated++;
    } elseif ($result['status'] === 'cached') {
        $cached++;
    } else {
        echo "❌ " . $text . " → " . ($result['message'] ?? 'Error') . "\n";
        $errors++;
    }
}

echo "\n=== GENERATION COMPLETE ===\n";
echo "Generated: $generated\n";
echo "Cached: $cached\n";
echo "Errors: $errors\n";
