<?php
require_once __DIR__ . '/config.php';

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

function pdfEscape($text) {
    return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
}

function writePdf($path, $pages) {
    $objects = [];
    $pagesIds = [];
    $objects[1] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
    $objects[2] = "2 0 obj\n<< /Type /Pages /Kids [] /Count 0 >>\nendobj\n";
    $objects[3] = "3 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";
    $nextId = 4;

    foreach ($pages as $page) {
        $text = '';
        $y = 820;
        $lines = explode("\n", trim($page['text']));
        foreach ($lines as $index => $line) {
            $line = pdfEscape($line);
            if ($index === 0) {
                $text .= "BT /F1 12 Tf 40 {$y} Td ({$line}) Tj ";
            } else {
                $y -= 16;
                $text .= "0 -16 Td ({$line}) Tj ";
            }
        }
        $text .= "ET\n";

        $xobjectEntries = [];
        $imageY = 680;
        $imagePositions = [];
        if (!empty($page['images']) && is_array($page['images'])) {
            foreach ($page['images'] as $imgIndex => $imageData) {
                $imageId = $nextId++;
                $imageName = '/Im' . ($imgIndex + 1);
                $imageSize = getimagesizefromstring($imageData);
                if (!$imageSize) {
                    continue;
                }
                list($width, $height) = $imageSize;
                $imageObject = "{$imageId} 0 obj\n<< /Type /XObject /Subtype /Image /Width {$width} /Height {$height} /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length " . strlen($imageData) . " >>\nstream\n" . $imageData . "\nendstream\nendobj\n";
                $objects[$imageId] = $imageObject;
                $xobjectEntries[] = "{$imageName} {$imageId} 0 R";
                $col = $imgIndex % 3;
                $row = intdiv($imgIndex, 3);
                $x = 40 + ($col * 180);
                $yPos = 620 - ($row * 140);
                $imagePositions[] = [
                    'name' => $imageName,
                    'x' => $x,
                    'y' => $yPos,
                    'w' => 160,
                    'h' => 120,
                ];
            }
        }

        foreach ($imagePositions as $pos) {
            $text .= "q 1 0 0 1 {$pos['x']} {$pos['y']} cm {$pos['w']} 0 0 {$pos['h']} 0 0 cm {$pos['name']} Do Q ";
        }

        $contentId = $nextId++;
        $objects[$contentId] = "{$contentId} 0 obj\n<< /Length " . strlen($text) . " >>\nstream\n{$text}\nendstream\nendobj\n";

        $resources = "<< /Font << /F1 3 0 R >>";
        if (!empty($xobjectEntries)) {
            $resources .= " /XObject << " . implode(' ', $xobjectEntries) . " >>";
        }
        $resources .= " >>";

        $pageId = $nextId++;
        $objects[$pageId] = "{$pageId} 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources {$resources} /Contents {$contentId} 0 R >>\nendobj\n";
        $pagesIds[] = $pageId;
    }

    $kids = implode(' ', array_map(function ($id) { return "{$id} 0 R"; }, $pagesIds));
    $objects[2] = "2 0 obj\n<< /Type /Pages /Kids [{$kids}] /Count " . count($pagesIds) . " >>\nendobj\n";

    $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
    $offsets = [];
    foreach ($objects as $id => $content) {
        $offsets[$id] = strlen($pdf);
        $pdf .= $content;
    }

    $xref = "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";
    for ($i = 1; $i <= count($objects); $i++) {
        $xref .= sprintf("%010d 00000 n \n", $offsets[$i]);
    }

    $pdf .= $xref;
    $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n" . strlen($pdf) . "\n%%EOF";

    file_put_contents($path, $pdf);
}

function createReportPdf($path, $input, $recordKey = null) {
    $lines = [];
    $lines[] = 'Exam Monitoring Report';
    $lines[] = '=====================';
    $lines[] = '';
    $lines[] = 'Exam ID: ' . ($recordKey ?: 'unknown');
    $lines[] = 'Date: ' . date('Y-m-d H:i:s');
    $lines[] = 'Total Questions: ' . count($input['questions']);
    $lines[] = 'Video attached: ' . ($recordKey ? 'Yes' : 'No');
    $lines[] = '';

    foreach ($input['questions'] as $idx => $question) {
        $qNum = $question['questionNum'] ?? ($idx + 1);
        $lines[] = 'Question ' . $qNum . ':';
        $lines[] = '  Time Taken: ' . round($question['timeTaken'], 1) . 's';
        $lines[] = '  Captured Images: ' . count($question['images']);
        foreach ($question['images'] as $imgIndex => $img) {
            $lines[] = '    - Snapshot ' . ($imgIndex + 1) . ' at ' . ($img['timestamp'] ?? 'unknown');
        }
        $lines[] = '';
    }

    $pageText = implode("\n", $lines);
    writePdf($path, [['text' => $pageText, 'images' => []]]);
}

function createEvidencePdf($path, $input) {
    $pages = [];
    foreach ($input['questions'] as $idx => $question) {
        $images = [];
        if (!empty($question['images']) && is_array($question['images'])) {
            foreach (array_slice($question['images'], 0, 8) as $img) {
                $imageData = $img['data'] ?? '';
                if (strpos($imageData, 'data:image') === 0) {
                    list(, $imageData) = explode(',', $imageData, 2);
                    $decoded = base64_decode($imageData);
                    if ($decoded !== false) {
                        $images[] = $decoded;
                    }
                }
            }
        }

        if (!count($images)) {
            continue;
        }

        $questionNumber = $question['questionNum'] ?? ($idx + 1);
        $questionType = $question['questionType'] ?? 'unknown';
        $questionTopic = $question['questionTopic'] ?? 'general';
        $header = 'Question ' . $questionNumber . ' evidence (' . $questionType . ', ' . $questionTopic . ')';
        $pages[] = [
            'text' => $header . "\n" . 'Captured images during response',
            'images' => $images
        ];
    }

    if (!count($pages)) {
        writePdf($path, [['text' => 'No evidence images were captured.', 'images' => []]]);
        return;
    }

    writePdf($path, $pages);
}

try {
    $userId = $_SESSION['user_id'];
    $recordKey = uniqid('exam_');
    $examDir = __DIR__ . '/../uploads/exam_records/' . $recordKey;
    if (!is_dir(__DIR__ . '/../uploads/exam_records')) {
        mkdir(__DIR__ . '/../uploads/exam_records', 0755, true);
    }
    if (!is_dir($examDir)) {
        mkdir($examDir, 0755, true);
    }

    $reportText = "EXAM MONITORING REPORT\n";
    $reportText .= "======================\n\n";
    $reportText .= "Exam ID: " . $recordKey . "\n";
    $reportText .= "Date: " . date('Y-m-d H:i:s') . "\n";
    $reportText .= "Total Questions: " . count($input['questions']) . "\n\n";

    foreach ($input['questions'] as $idx => $question) {
        $qNum = $question['questionNum'] ?? ($idx + 1);
        $qType = $question['questionType'] ?? 'unknown';
        $qTopic = $question['questionTopic'] ?? 'general';
        $timeTaken = round($question['timeTaken'], 1);
        $reportText .= "Question " . $qNum . " (" . $qType . ", " . $qTopic . ")\n";
        $reportText .= "Time Taken: " . $timeTaken . " seconds\n";
        $reportText .= "Captured Snapshots: " . count($question['images']) . "\n";
        foreach ($question['images'] as $imgIdx => $image) {
            $timestamp = $image['timestamp'] ?? '';
            $reportText .= "  Snapshot " . ($imgIdx + 1) . " at " . $timestamp . "\n";
        }
        $reportText .= "\n";
    }

    file_put_contents($examDir . '/report.txt', $reportText);
    createReportPdf($examDir . '/report.pdf', $input, $recordKey);
    createEvidencePdf($examDir . '/evidence.pdf', $input);

    $pdo = getPDO();
    $stmt = $pdo->prepare('INSERT INTO exam_records (user_id, exam_id, record_path, created_at) VALUES (?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE record_path = VALUES(record_path), created_at = NOW()');
    $stmt->execute([$userId, $recordKey, $examDir]);

    echo json_encode(['success' => true, 'examId' => $recordKey, 'recordPath' => $examDir]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
