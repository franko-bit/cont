<?php
/**
 * Guidebook Content Loader
 * 
 * Loads and parses guidebook data from YAML lesson files.
 * This keeps guidebook content separate from dashboard code for better maintainability.
 */

function stripYamlAnchorMarkers($text) {
  $text = preg_replace('/(?<![A-Za-z0-9_])[&*][A-Za-z0-9_\-]+/', '', $text);
  $text = preg_replace('/^\s*-[ \t]*[&*][A-Za-z0-9_\-]+\s*$/m', '- ', $text);
  return $text;
}

function parseYamlScalar($value) {
  $value = trim($value);

  if ($value === '' || $value === '|' || $value === '>') {
    return '';
  }

  if ($value === 'true') {
    return true;
  }

  if ($value === 'false') {
    return false;
  }

  if ($value === 'null' || $value === '~') {
    return null;
  }

  if (preg_match('/^\[(.*)\]$/s', $value, $m)) {
    $inner = trim($m[1]);
    if ($inner === '') {
      return [];
    }

    $parts = preg_split('/\s*,\s*(?=(?:[^"\']*["\'][^"\']*["\'])*[^"\']*$)/', $inner);
    $result = [];
    foreach ($parts as $part) {
      $result[] = parseYamlScalar($part);
    }
    return $result;
  }

  if (preg_match('/^(["\'])(.*)\1$/s', $value, $m)) {
    return stripcslashes($m[2]);
  }

  if (is_numeric($value)) {
    return strpos($value, '.') !== false ? (float) $value : (int) $value;
  }

  return $value;
}

function parseGuidebookYAML($content) {
  $content = stripYamlAnchorMarkers(str_replace(["\r\n", "\r"], "\n", $content));
  $lines = explode("\n", $content);
  $result = [];
  $stack = [[-1, &$result]];

  foreach ($lines as $line) {
    if (trim($line) === '' || strpos(ltrim($line), '#') === 0) {
      continue;
    }

    $indent = strlen($line) - strlen(ltrim($line));
    $trimmed = rtrim($line);
    $text = trim(preg_replace('/\s+#[^"\']*$/', '', $trimmed));

    while (count($stack) > 1 && $stack[count($stack) - 1][0] >= $indent) {
      array_pop($stack);
    }

    $parent = &$stack[count($stack) - 1][1];

    if (preg_match('/^([A-Za-z0-9_\-]+):\s*(.*)$/', $text, $m)) {
      $key = $m[1];
      $value = trim($m[2]);
      if ($value === '' || $value === '|' || $value === '>') {
        $parent[$key] = [];
        $stack[] = [$indent, &$parent[$key]];
      } else {
        $parent[$key] = parseYamlScalar($value);
      }
    } elseif (preg_match('/^-\s*(.*)$/', $text, $m)) {
      $value = trim($m[1]);
      if ($value === '') {
        $parent[] = [];
        $item = &$parent[count($parent) - 1];
        $stack[] = [$indent, &$item];
      } elseif (preg_match('/^\-\s*(.*)$/', $value, $nestedM)) {
        $parent[] = [];
        $item = &$parent[count($parent) - 1];
        $nestedValue = trim($nestedM[1]);
        if ($nestedValue !== '') {
          $item[] = parseYamlScalar($nestedValue);
        }
        $stack[] = [$indent, &$item];
      } elseif (preg_match('/^([A-Za-z0-9_\-]+):\s*(.*)$/', $value, $qm)) {
        $parent[] = [];
        $item = &$parent[count($parent) - 1];
        $key = $qm[1];
        $childValue = trim($qm[2]);
        if ($childValue === '' || $childValue === '|' || $childValue === '>') {
          $item[$key] = [];
        } else {
          $item[$key] = parseYamlScalar($childValue);
        }
        $stack[] = [$indent, &$item];
      } else {
        $parent[] = parseYamlScalar($value);
      }
    }
  }

  return $result;
}

/**
 * Very small Markdown -> HTML converter used for guidebook intro text.
 * Supports headings (#), unordered lists (-), ordered lists (1.), and simple pipe tables.
 */
function markdownToHtml($md) {
  $md = guidebookText($md);
  $md = str_replace(["\r\n", "\r"], "\n", $md);
  $lines = explode("\n", $md);
  $out = '';
  $inUl = false;
  $inOl = false;
  $inTable = false;
  $tableRows = [];

  $flushTable = function() use (&$tableRows, &$out, &$inTable) {
    if (empty($tableRows)) { $inTable = false; return; }
    $header = array_shift($tableRows);
    $cols = array_map('trim', array_filter(explode('|', trim($header)), 'strlen'));
    $out .= '<table class="guidebook-markdown-table">';
    $out .= '<thead><tr>';
    foreach ($cols as $c) { $out .= '<th>' . htmlspecialchars($c) . '</th>'; }
    $out .= '</tr></thead>';
    if (!empty($tableRows)) {
      $out .= '<tbody>';
      foreach ($tableRows as $r) {
        $cells = array_map('trim', array_filter(explode('|', trim($r)), 'strlen'));
        $out .= '<tr>';
        foreach ($cells as $cell) { $out .= '<td>' . htmlspecialchars($cell) . '</td>'; }
        $out .= '</tr>';
      }
      $out .= '</tbody>';
    }
    $out .= '</table>';
    $tableRows = [];
    $inTable = false;
  };

  foreach ($lines as $line) {
    $trim = rtrim($line);
    if ($trim === '') {
      if ($inUl) { $out .= "</ul>"; $inUl = false; }
      if ($inOl) { $out .= "</ol>"; $inOl = false; }
      if ($inTable) { $flushTable(); }
      continue;
    }

    // Table detection (lines with pipes)
    if (strpos($trim, '|') !== false && preg_match('/\|.*\|/', $trim)) {
      $inTable = true;
      $tableRows[] = $trim;
      continue;
    }

    if ($inTable) { $flushTable(); }

    if (preg_match('/^#{1,6}\s*(.*)$/', $trim, $m)) {
      $level = strlen($m[0]) - strlen(trim($m[1]));
      // better: count hashes directly
      preg_match('/^(#+)\s*(.*)$/', $trim, $mm);
      $level = min(6, strlen($mm[1]));
      $out .= '<h' . $level . '>' . htmlspecialchars(trim($mm[2])) . '</h' . $level . '>';
      continue;
    }

    if (preg_match('/^\-\s+(.*)$/', $trim, $m)) {
      if (!$inUl) { $out .= '<ul>'; $inUl = true; }
      $out .= '<li>' . htmlspecialchars($m[1]) . '</li>';
      continue;
    }

    if (preg_match('/^\d+\.\s+(.*)$/', $trim, $m)) {
      if (!$inOl) { $out .= '<ol>'; $inOl = true; }
      $out .= '<li>' . htmlspecialchars($m[1]) . '</li>';
      continue;
    }

    // Paragraph
    $out .= '<p>' . nl2br(htmlspecialchars($trim)) . '</p>';
  }

  if ($inUl) { $out .= "</ul>"; }
  if ($inOl) { $out .= "</ol>"; }
  if ($inTable) { $flushTable(); }
  return $out;
}

/**
 * Converts the flexible values produced by guidebook YAML into display text.
 * Guidebook rows may contain fewer/more columns, and examples are often
 * represented as a two-item array rather than a single string.
 */
function guidebookText($value, $separator = ' — ') {
  if ($value === null) {
    return '';
  }

  if (is_array($value)) {
    $parts = [];
    foreach ($value as $part) {
      $text = guidebookText($part, $separator);
      if ($text !== '') {
        $parts[] = $text;
      }
    }
    return implode($separator, $parts);
  }

  if (is_bool($value)) {
    return $value ? 'true' : 'false';
  }

  return is_scalar($value) ? (string) $value : '';
}

function guidebookEscape($value) {
  return htmlspecialchars(guidebookText($value), ENT_QUOTES, 'UTF-8');
}

function isGuidebookActionWord($text) {
  return preg_match('/\b(eat|eats|drink|drinks|sleep|sleeps|run|runs|fly|flies|swim|swims|play|plays|go|goes|build|builds|learn|study)\b/i', $text);
}

function isGuidebookFoodWord($text) {
  return preg_match('/\b(grass|water|meat|rice|bread|beans|fruit|vegetable|milk|food|meal|drink)s?\b/i', $text);
}

function normalizeGuidebookPair($english, $kinyarwanda) {
  return [trim((string)$english), trim((string)$kinyarwanda)];
}

function generateGuidebookDataFromLesson($data) {
  if (empty($data) || !is_array($data)) {
    return null;
  }

  $header = $data['lesson']['name'] ?? $data['title'] ?? 'Study guide';
  $intro = $data['lesson']['description'] ?? $data['description'] ?? 'This lesson covers key vocabulary and example practice content.';
  $xpReward = $data['lesson']['xp_reward'] ?? null;

  $vocab = [];
  $actions = [];
  $foods = [];
  $examples = [];

  if (!empty($data['exercises']) && is_array($data['exercises'])) {
    foreach ($data['exercises'] as $exercise) {
      if (!is_array($exercise)) {
        continue;
      }

      $question = trim((string)($exercise['question'] ?? ''));
      $answer = trim((string)($exercise['answer'] ?? ''));
      $translation = trim((string)($exercise['translation'] ?? ''));
      $prompt = trim((string)($exercise['prompt'] ?? ''));
      $left = trim((string)($exercise['left'] ?? ''));
      $right = trim((string)($exercise['right'] ?? ''));
      $front = trim((string)($exercise['front'] ?? ''));
      $back = trim((string)($exercise['back'] ?? ''));

      if ($question !== '' && $answer !== '') {
        if (preg_match('/How do you say ["\'](.+?)["\'] in Kinyarwanda\?/i', $question, $m)) {
          $pair = normalizeGuidebookPair($m[1], $answer);
        } else {
          $pair = normalizeGuidebookPair($question, $answer);
        }

        if ($pair[0] !== '' && $pair[1] !== '') {
          if (isGuidebookActionWord($pair[0])) {
            $actions[] = $pair;
          } elseif (isGuidebookFoodWord($pair[0])) {
            $foods[] = $pair;
          } else {
            $vocab[] = $pair;
          }
        }
      }

      if ($prompt !== '' && $translation !== '') {
        $pair = normalizeGuidebookPair($prompt, $translation);
        if (isGuidebookActionWord($pair[0])) {
          $actions[] = $pair;
        } elseif (isGuidebookFoodWord($pair[0])) {
          $foods[] = $pair;
        } else {
          $vocab[] = $pair;
        }
      }

      if ($left !== '' && $right !== '') {
        $pair = normalizeGuidebookPair($left, $right);
        if (isGuidebookActionWord($pair[0]) || isGuidebookActionWord($pair[1])) {
          $actions[] = $pair;
        } elseif (isGuidebookFoodWord($pair[0]) || isGuidebookFoodWord($pair[1])) {
          $foods[] = $pair;
        } else {
          $vocab[] = $pair;
        }
      }

      if ($front !== '' && $back !== '') {
        $vocab[] = normalizeGuidebookPair($back, $front);
      }

      foreach (['question', 'text', 'sentence', 'correct_sentence'] as $field) {
        if (!empty($exercise[$field]) && is_string($exercise[$field])) {
          $examples[] = trim($exercise[$field]);
        }
      }

      if (!empty($exercise['translation']) && is_string($exercise['translation'])) {
        $examples[] = trim($exercise['translation']);
      }
    }
  }

  $vocab = array_values(array_unique($vocab, SORT_REGULAR));
  $actions = array_values(array_unique($actions, SORT_REGULAR));
  $foods = array_values(array_unique($foods, SORT_REGULAR));
  $examples = array_values(array_unique(array_filter($examples)));
  if (count($examples) > 12) {
    $examples = array_slice($examples, 0, 12);
  }

  $sections = [];
  if (!empty($vocab)) {
    $sections[] = ['category' => '🔤 Vocabulary', 'rows' => $vocab];
  }
  if (!empty($actions)) {
    $sections[] = ['category' => '🏃 Action words', 'rows' => $actions];
  }
  if (!empty($foods)) {
    $sections[] = ['category' => '🍽️ Food words', 'rows' => $foods];
  }

  $guidebookData = [
    'header' => $header,
    'intro'  => $intro,
    'vocabulary' => [
      [
        'title' => '📚 Lesson vocabulary',
        'sections' => $sections ?: [['category' => '🔤 Vocabulary', 'rows' => []]]
      ]
    ],
  ];

  if (!empty($actions)) {
    $guidebookData['verbs'] = ['title' => '🏃 Action verbs', 'rows' => $actions];
  }
  if (!empty($foods)) {
    $guidebookData['food'] = ['title' => '🍽️ Food vocabulary', 'rows' => $foods];
  }
  if (!empty($examples)) {
    $guidebookData['examples'] = ['title' => '📝 Example sentences', 'items' => $examples];
  }

  $goals = [
    'Review the lesson vocabulary and examples before completing exercises.',
  ];
  if (!empty($actions)) {
    $goals[] = 'Practice using the action verbs in simple sentences.';
  }
  if (!empty($foods)) {
    $goals[] = 'Use food and drink vocabulary naturally in context.';
  }
  if (empty($goals)) {
    $goals[] = 'Understand the main vocabulary from this lesson.';
  }

  $guidebookData['quick_reference'] = [
    'title' => '🔍 Quick Reference',
    'rows' => [
      ['Review the key words every day.', 'Repeat the example sentences out loud.'],
      ['Use the lesson vocabulary in short phrases.', 'Listen for the rhythm of each sentence.'],
      ['Practice speaking with confidence.', 'Start with translation exercises first.'],
    ],
  ];

  $unitGoal = [
    'title' => '🎯 Unit goal',
    'intro' => 'Use this guidebook to support your lesson practice.',
    'goals' => $goals,
  ];
  if (!empty($xpReward)) {
    $unitGoal['xp_available'] = 'Total XP Available: ' . $xpReward . ' XP';
  }
  $guidebookData['unit_goal'] = $unitGoal;
  $guidebookData['footer'] = 'This guidebook is generated from the lesson content so you can study without extra YAML authoring.';

  return $guidebookData;
}

function extractLegacyGuidebookText($content) {
  $lines = preg_split('/\r\n|\r|\n/', $content);
  $guidebookLines = [];
  $inGuidebook = false;
  $baseIndent = null;

  foreach ($lines as $line) {
    if (!$inGuidebook) {
      if (preg_match('/^guidebook:\s*\|\s*(.*)$/', $line, $m)) {
        $inGuidebook = true;
        $inline = trim($m[1]);
        if ($inline !== '') {
          $guidebookLines[] = $inline;
        }
        continue;
      }
      continue;
    }

    if (preg_match('/^[^\s]/', $line)) {
      break;
    }

    if (preg_match('/^(\s*)(.*)$/', $line, $m)) {
      $indent = strlen($m[1]);
      $text = $m[2];
      if ($baseIndent === null && $indent > 0) {
        $baseIndent = $indent;
      }
      if ($baseIndent !== null && $indent >= $baseIndent) {
        $text = substr($line, $baseIndent);
      }
      $guidebookLines[] = $text;
    }
  }

  $text = trim(implode("\n", $guidebookLines));
  return $text !== '' ? $text : null;
}

function loadGuidebookData($level, $lessonFile, $folder = 'EN-TO-RW') {
  $yamlPath = __DIR__ . "/../content/{$folder}/level{$level}/{$lessonFile}.yaml";
  
  if (!file_exists($yamlPath)) {
    return null;
  }
  
  $content = file_get_contents($yamlPath);
  
  $data = null;
  if (function_exists('yaml_parse')) {
    $data = yaml_parse($content);
    if (isset($data['guidebook_data'])) {
      return $data['guidebook_data'];
    }
  }

  $data = parseGuidebookYAML($content);
  if (isset($data['guidebook_data'])) {
    return $data['guidebook_data'];
  }

  $guidebookText = null;
  if (!empty($data['guidebook']) && is_string($data['guidebook'])) {
    $guidebookText = trim($data['guidebook']);
  } elseif (!empty($data['guidebook_text']) && is_string($data['guidebook_text'])) {
    $guidebookText = trim($data['guidebook_text']);
  } elseif (!empty($data['guidebook_content']) && is_string($data['guidebook_content'])) {
    $guidebookText = trim($data['guidebook_content']);
  }

  $legacyGuidebook = extractLegacyGuidebookText($content);
  if ($legacyGuidebook) {
    $guidebookText = trim($legacyGuidebook);
  }

  if ($guidebookText !== null) {
    $generated = generateGuidebookDataFromLesson($data);
    if (!empty($generated)) {
      $generated['header'] = $data['lesson']['name'] ?? $generated['header'] ?? 'Study guide';
      $generated['intro'] = $guidebookText;
      return $generated;
    }
    return [
      'header' => $data['lesson']['name'] ?? 'Study guide',
      'intro'  => $guidebookText
    ];
  }

  // Generate guidebook content automatically from lesson YAML fields when no explicit guidebook exists
  $generated = generateGuidebookDataFromLesson($data);
  if (!empty($generated)) {
    return $generated;
  }

  // Final fallback for lessons without explicit guidebook content
  $header = $data['lesson']['name'] ?? 'Study guide';
  $intro = $data['lesson']['description'] ?? 'Study guide content is not available for this lesson yet.';
  return [
    'header' => $header,
    'intro'  => $intro
  ];
}

/**
 * Renders guidebook HTML from structured data
 */
function renderGuidebookHTML($guidebookData) {
  if (!$guidebookData) {
    // Return empty string to use hardcoded HTML from JavaScript
    return '';
  }
  
  $html = '<div class="guidebook-top-label"> Guidebook</div>';
  
  // Header
  if (!empty($guidebookData['header'])) {
    $html .= '<div class="guidebook-section-header">' . guidebookEscape($guidebookData['header']) . '</div>';
  }
  
  // Intro (support lightweight Markdown)
  if (!empty($guidebookData['intro'])) {
    $html .= '<div class="guidebook-intro-text">' . markdownToHtml($guidebookData['intro']) . '</div>';
  }
  
  // Vocabulary sections
  if (!empty($guidebookData['vocabulary'])) {
    foreach ($guidebookData['vocabulary'] as $vocab) {
      if (!empty($vocab['title'])) {
        $html .= '<h2 style="font-size:16px;font-weight:600;color:#fff;margin:24px 0 16px;text-align:left;">' . guidebookEscape($vocab['title']) . '</h2>';
      }
      
      if (!empty($vocab['sections'])) {
        foreach ($vocab['sections'] as $section) {
          if (!empty($section['category'])) {
            $html .= '<div class="guidebook-section-category">' . guidebookEscape($section['category']) . '</div>';
          }
          
          if (!empty($section['rows'])) {
            $html .= '<table class="guidebook-vocab-table"><thead><tr><th>English</th><th>Kinyarwanda</th></tr></thead><tbody>';
            foreach ($section['rows'] as $row) {
              $html .= '<tr><td>' . guidebookEscape(is_array($row) && array_key_exists(0, $row) ? $row[0] : '') . '</td><td>' . guidebookEscape(is_array($row) && array_key_exists(1, $row) ? $row[1] : '') . '</td></tr>';
            }
            $html .= '</tbody></table>';
          }
        }
      }
    }
  }
  
  // Verbs
  if (!empty($guidebookData['verbs'])) {
    if (!empty($guidebookData['verbs']['title'])) {
      $html .= '<h2 style="font-size:16px;font-weight:600;color:#fff;margin:24px 0 16px;text-align:left;">' . guidebookEscape($guidebookData['verbs']['title']) . '</h2>';
    }
    if (!empty($guidebookData['verbs']['rows'])) {
      $html .= '<table class="guidebook-vocab-table"><thead><tr><th>English</th><th>Kinyarwanda</th></tr></thead><tbody>';
      foreach ($guidebookData['verbs']['rows'] as $row) {
        $html .= '<tr><td>' . guidebookEscape(is_array($row) && array_key_exists(0, $row) ? $row[0] : '') . '</td><td>' . guidebookEscape(is_array($row) && array_key_exists(1, $row) ? $row[1] : '') . '</td></tr>';
      }
      $html .= '</tbody></table>';
    }
  }
  
  // Food
  if (!empty($guidebookData['food'])) {
    if (!empty($guidebookData['food']['title'])) {
      $html .= '<h2 style="font-size:16px;font-weight:600;color:#fff;margin:24px 0 16px;text-align:left;">' . guidebookEscape($guidebookData['food']['title']) . '</h2>';
    }
    if (!empty($guidebookData['food']['rows'])) {
      $html .= '<table class="guidebook-vocab-table"><thead><tr><th>English</th><th>Kinyarwanda</th></tr></thead><tbody>';
      foreach ($guidebookData['food']['rows'] as $row) {
        $html .= '<tr><td>' . guidebookEscape(is_array($row) && array_key_exists(0, $row) ? $row[0] : '') . '</td><td>' . guidebookEscape(is_array($row) && array_key_exists(1, $row) ? $row[1] : '') . '</td></tr>';
      }
      $html .= '</tbody></table>';
    }
  }
  
  // Grammar
  if (!empty($guidebookData['grammar'])) {
    if (!empty($guidebookData['grammar']['title'])) {
      $html .= '<h2 style="font-size:16px;font-weight:600;color:#fff;margin:24px 0 16px;text-align:left;">' . guidebookEscape($guidebookData['grammar']['title']) . '</h2>';
    }
    
    if (!empty($guidebookData['grammar']['tips'])) {
      foreach ($guidebookData['grammar']['tips'] as $tip) {
        $html .= '<div class="guidebook-grammar-section">';
        if (!empty($tip['heading'])) {
          $html .= '<h3>' . guidebookEscape($tip['heading']) . '</h3>';
        }
        if (!empty($tip['text'])) {
          $html .= '<p>' . guidebookEscape($tip['text']) . '</p>';
        }
        
        if (!empty($tip['examples'])) {
          $html .= '<div style="background:#0a0a0a;padding:10px;margin:8px 0;border-radius:6px;font-size:13px;color:#ddd;line-height:1.5;">';
          foreach ($tip['examples'] as $example) {
            $html .= '<p>' . guidebookEscape($example) . '</p>';
          }
          $html .= '</div>';
        }
        
        if (!empty($tip['items'])) {
          $html .= '<ul>';
          foreach ($tip['items'] as $item) {
            $html .= '<li>' . guidebookEscape($item) . '</li>';
          }
          $html .= '</ul>';
        }
        $html .= '</div>';
      }
    }
  }
  
  // Pronunciation
  if (!empty($guidebookData['pronunciation'])) {
    if (!empty($guidebookData['pronunciation']['title'])) {
      $html .= '<h2 style="font-size:16px;font-weight:600;color:#fff;margin:24px 0 16px;text-align:left;">' . guidebookEscape($guidebookData['pronunciation']['title']) . '</h2>';
    }
    
    if (!empty($guidebookData['pronunciation']['tips'])) {
      foreach ($guidebookData['pronunciation']['tips'] as $tip) {
        $html .= '<div class="guidebook-pronunciation-section">';
        if (!empty($tip['heading'])) {
          $html .= '<h3>' . guidebookEscape($tip['heading']) . '</h3>';
        }
        if (!empty($tip['text'])) {
          $html .= '<p>' . guidebookEscape($tip['text']) . '</p>';
        }
        
        if (!empty($tip['items'])) {
          $html .= '<ul>';
          foreach ($tip['items'] as $item) {
            $html .= '<li>' . guidebookEscape($item) . '</li>';
          }
          $html .= '</ul>';
        }
        $html .= '</div>';
      }
    }
  }
  
  // Key Phrases
  if (!empty($guidebookData['key_phrases'])) {
    if (!empty($guidebookData['key_phrases']['title'])) {
      $html .= '<h2 style="font-size:16px;font-weight:600;color:#fff;margin:24px 0 16px;text-align:left;">' . guidebookEscape($guidebookData['key_phrases']['title']) . '</h2>';
    }
    if (!empty($guidebookData['key_phrases']['rows'])) {
      $html .= '<table class="guidebook-vocab-table"><thead><tr><th>English</th><th>Kinyarwanda</th></tr></thead><tbody>';
      foreach ($guidebookData['key_phrases']['rows'] as $row) {
        $html .= '<tr><td>' . guidebookEscape(is_array($row) && array_key_exists(0, $row) ? $row[0] : '') . '</td><td>' . guidebookEscape(is_array($row) && array_key_exists(1, $row) ? $row[1] : '') . '</td></tr>';
      }
      $html .= '</tbody></table>';
    }
  }
  
  // Unit Goal
  if (!empty($guidebookData['unit_goal'])) {
    if (!empty($guidebookData['unit_goal']['title'])) {
      $html .= '<h2 style="font-size:16px;font-weight:600;color:#fff;margin:24px 0 16px;text-align:left;">' . guidebookEscape($guidebookData['unit_goal']['title']) . '</h2>';
    }
    $html .= '<div class="guidebook-unit-goal-section">';
    if (!empty($guidebookData['unit_goal']['intro'])) {
      $html .= '<p><strong>' . guidebookEscape($guidebookData['unit_goal']['intro']) . '</strong></p>';
    }
    if (!empty($guidebookData['unit_goal']['goals'])) {
      $html .= '<ul>';
      foreach ($guidebookData['unit_goal']['goals'] as $goal) {
        $html .= '<li>  ' . guidebookEscape($goal) . '</li>';
      }
      $html .= '</ul>';
    }
    if (!empty($guidebookData['unit_goal']['xp_available'])) {
      $html .= '<div class="xp-badge">' . guidebookEscape($guidebookData['unit_goal']['xp_available']) . '</div>';
    }
    $html .= '</div>';
  }
  
  // Examples
  if (!empty($guidebookData['examples'])) {
    if (!empty($guidebookData['examples']['title'])) {
      $html .= '<h2 style="font-size:16px;font-weight:600;color:#fff;margin:24px 0 16px;text-align:left;">' . guidebookEscape($guidebookData['examples']['title']) . '</h2>';
    }
    if (!empty($guidebookData['examples']['items'])) {
      $html .= '<div style="background:#1e1e1e;padding:12px;border-radius:6px;margin:12px 0;text-align:left;font-size:13px;">';
      foreach ($guidebookData['examples']['items'] as $item) {
        $html .= '<p style="color:#ddd;margin-bottom:8px;line-height:1.5;">' . guidebookEscape($item) . '</p>';
      }
      $html .= '</div>';
    }
  }
  
  // Quick Reference
  if (!empty($guidebookData['quick_reference'])) {
    if (!empty($guidebookData['quick_reference']['title'])) {
      $html .= '<h2 style="font-size:16px;font-weight:600;color:#fff;margin:24px 0 16px;text-align:left;">' . guidebookEscape($guidebookData['quick_reference']['title']) . '</h2>';
    }
    if (!empty($guidebookData['quick_reference']['rows'])) {
      $html .= '<table class="guidebook-vocab-table"><thead><tr><th>Category</th><th>Examples</th></tr></thead><tbody>';
      foreach ($guidebookData['quick_reference']['rows'] as $row) {
        $html .= '<tr><td>' . guidebookEscape(is_array($row) && array_key_exists(0, $row) ? $row[0] : '') . '</td><td>' . guidebookEscape(is_array($row) && array_key_exists(1, $row) ? $row[1] : '') . '</td></tr>';
      }
      $html .= '</tbody></table>';
    }
  }
  
  // Footer
  if (!empty($guidebookData['footer'])) {
    $html .= '<div style="background:#1e1e1e;padding:16px;border-radius:6px;margin:24px 0 16px;text-align:center;border-top:1px solid #333;"><p style="font-size:13px;color:#ddd;line-height:1.5;"><strong>' . guidebookEscape($guidebookData['footer']) . '</strong></p></div>';
  }
  
  return $html;
}
?>
