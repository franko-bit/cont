<?php
require_once __DIR__ . "/config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Method not allowed');
}

$user_id = validateSession();
$input = json_decode(file_get_contents('php://input'), true);
$candidate_id = isset($input['candidate_id']) ? intval($input['candidate_id']) : 0;
$student_name = trim($input['student_name'] ?? '');
$assessment_id = isset($input['assessment_id']) ? intval($input['assessment_id']) : 0;

if (!$candidate_id || !$assessment_id || !$student_name) {
    sendResponse(false, 'Candidate, assessment, and student name are required');
}

$pdo = getPDO();

function ensureCertificateStructure($pdo) {
    $column = $pdo->query("SHOW COLUMNS FROM certificates LIKE 'student_name'")->fetch();
    if (!$column) {
        $pdo->exec("ALTER TABLE certificates ADD COLUMN student_name VARCHAR(255) NULL AFTER certificate_id");
    }
    $column = $pdo->query("SHOW COLUMNS FROM institution_candidates LIKE 'cert_status'")->fetch();
    if (!$column) {
        $pdo->exec("ALTER TABLE institution_candidates ADD COLUMN cert_status VARCHAR(30) NULL");
    }
    $column = $pdo->query("SHOW COLUMNS FROM institution_candidates LIKE 'cert_issued_at'")->fetch();
    if (!$column) {
        $pdo->exec("ALTER TABLE institution_candidates ADD COLUMN cert_issued_at TIMESTAMP NULL");
    }
}

function buildCertificateId($pdo) {
    $year = date('Y');
    $prefix = sprintf('CERT-%s-', $year);
    $stmt = $pdo->prepare('SELECT certificate_id FROM certificates WHERE certificate_id LIKE ? ORDER BY certificate_id DESC LIMIT 1');
    $stmt->execute([$prefix . '%']);
    $lastId = $stmt->fetchColumn();
    $sequence = 1;
    if ($lastId && preg_match('/(\d+)$/', $lastId, $matches)) {
        $sequence = intval($matches[1]) + 1;
    }
    return sprintf('%s%05d', $prefix, $sequence);
}

try {
    ensureCertificateStructure($pdo);
    $stmt = $pdo->prepare('SELECT * FROM institution_candidates WHERE id = ? LIMIT 1');
    $stmt->execute([$candidate_id]);
    $candidate = $stmt->fetch();
    if (!$candidate) {
        sendResponse(false, 'Candidate not found');
    }

    if (($candidate['status'] ?? '') !== 'completed') {
        sendResponse(false, 'Certificate can only be issued for completed candidates');
    }

    $stmt = $pdo->prepare('SELECT language_pair, exam_level, passing_score FROM institution_assessments WHERE id = ? LIMIT 1');
    $stmt->execute([$assessment_id]);
    $assessment = $stmt->fetch();
    if (!$assessment) {
        sendResponse(false, 'Assessment not found');
    }

    $userId = $candidate['user_id'];
    if (!$userId) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$candidate['email']]);
        $userId = $stmt->fetchColumn();
    }

    if (!$userId) {
        sendResponse(false, 'Candidate must be linked to a registered user to issue a certificate.');
    }

    $examAttemptId = $candidate['exam_attempt_id'];
    if (!$examAttemptId) {
        sendResponse(false, 'Candidate exam attempt record is required.');
    }

    $certificateId = buildCertificateId($pdo);
    $score = $candidate['score'] !== null ? (float)$candidate['score'] : 0;
    $passingScore = $assessment['passing_score'] !== null ? (int)$assessment['passing_score'] : 70;

    $pdo->beginTransaction();
    $stmt = $pdo->prepare('INSERT INTO certificates (certificate_id, student_name, user_id, exam_attempt_id, language_pair, level_number, score, passing_score, issued_at, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');
    $stmt->execute([
        $certificateId,
        $student_name,
        $userId,
        $examAttemptId,
        $assessment['language_pair'] ?? '',
        $assessment['exam_level'] ?? 0,
        $score,
        $passingScore,
    ]);

    $updateCandidate = $pdo->prepare('UPDATE institution_candidates SET cert_status = ?, cert_issued_at = NOW() WHERE id = ?');
    $updateCandidate->execute(['issued', $candidate_id]);

    $pdo->commit();

    sendResponse(true, 'Certificate created successfully', [
        'certificate_id' => $certificateId,
        'candidate_id' => $candidate_id,
        'student_name' => $student_name,
        'issued_at' => date('Y-m-d H:i:s'),
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    sendResponse(false, 'Failed to create certificate: ' . $e->getMessage());
}
