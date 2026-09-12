<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Method not allowed');
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$applicantName = trim($input['applicant_name'] ?? '');
$schoolName = trim($input['school_name'] ?? '');
$popcornCode = trim($input['popcorn_code'] ?? '');
$examId = isset($input['exam_id']) ? (int)$input['exam_id'] : 0;
$examType = trim(strtolower($input['exam_type'] ?? ''));
$skipSuggestions = !empty($input['skip_suggestions']);
$userId = $_SESSION['user_id'] ?? null;

if ($applicantName === '') {
    sendResponse(false, 'Applicant name is required');
}
if ($schoolName === '') {
    sendResponse(false, 'School name is required');
}
if ($popcornCode === '') {
    sendResponse(false, 'Popcorn code is required');
}
if ($examType === '' && $examId === 0) {
    sendResponse(false, 'Please select an exam before applying for popcorn');
}

$pdo = getPDO();

function ensurePopcornApplicationStructure($pdo) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS popcorn_applications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        popcorn_id INT NULL,
        popcorn_code VARCHAR(100) NOT NULL,
        institution_id INT NULL,
        applicant_name VARCHAR(255) NOT NULL,
        school_name VARCHAR(255) NOT NULL,
        user_id INT NULL,
        attempt_id INT NULL,
        status VARCHAR(50) NOT NULL DEFAULT 'applied',
        applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        refunded_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL,
        UNIQUE KEY uniq_popcorn_application (popcorn_code)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

try {
    ensurePopcornApplicationStructure($pdo);

    $stmt = $pdo->prepare('SELECT p.id, p.popcorn_code, p.institution_id, p.school_name, i.name AS institution_name FROM institution_popcorns p LEFT JOIN institutions i ON p.institution_id = i.id WHERE p.popcorn_code = ? LIMIT 1');
    $stmt->execute([$popcornCode]);
    $popcornRow = $stmt->fetch();

    if (!$popcornRow) {
        sendResponse(false, 'Invalid popcorn code');
    }

    $checkStmt = $pdo->prepare('SELECT id FROM popcorn_applications WHERE popcorn_code = ? LIMIT 1');
    $checkStmt->execute([$popcornCode]);
    if ($checkStmt->fetch()) {
        sendResponse(false, 'This popcorn code has already been claimed');
    }

    $attemptId = null;
    if ($userId) {
        $exam = false;
        if ($examId > 0) {
            $stmt = $pdo->prepare('SELECT id FROM exams WHERE id = ? AND is_active = 1 LIMIT 1');
            $stmt->execute([$examId]);
            $exam = $stmt->fetch();
        } elseif ($examType !== '') {
            $pattern = $examType === 'academic' ? '%academic%' : ($examType === 'business' ? '%business%' : '%' . $examType . '%');
            $stmt = $pdo->prepare('SELECT id FROM exams WHERE LOWER(title) LIKE ? AND is_active = 1 ORDER BY level_number ASC LIMIT 1');
            $stmt->execute([$pattern]);
            $exam = $stmt->fetch();
            if (!$exam) {
                $stmt = $pdo->prepare('SELECT id FROM exams WHERE exam_language = ? AND is_active = 1 ORDER BY level_number ASC LIMIT 1');
                $stmt->execute(['en']);
                $exam = $stmt->fetch();
            }
        }

        if ($exam) {
            $stmt = $pdo->prepare('SELECT id FROM exam_attempts WHERE user_id = ? AND exam_id = ? AND status IN ("pending", "verification", "in_progress") ORDER BY created_at DESC LIMIT 1');
            $stmt->execute([$userId, $exam['id']]);
            $existingAttempt = $stmt->fetch();
            if ($existingAttempt) {
                $attemptId = (int)$existingAttempt['id'];
            } else {
                $stmt = $pdo->prepare('INSERT INTO exam_attempts (user_id, exam_id, status, verification_status, ip_address, user_agent) VALUES (?, ?, "verification", "pending", ?, ?)');
                $stmt->execute([$userId, $exam['id'], $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']);
                $attemptId = (int)$pdo->lastInsertId();
            }
        }
    }

    $insertStmt = $pdo->prepare('INSERT INTO popcorn_applications (popcorn_id, popcorn_code, institution_id, applicant_name, school_name, user_id, attempt_id, status, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())');
    $insertStmt->execute([
        $popcornRow['id'],
        $popcornCode,
        $popcornRow['institution_id'],
        $applicantName,
        $schoolName,
        $userId,
        $attemptId,
        'applied',
    ]);

    $applicationId = (int)$pdo->lastInsertId();

    // Also insert into institution_candidates to show in dashboard
    $institutionId = $popcornRow['institution_id'];
    if ($institutionId && $userId && $attemptId) {
        try {
            // Link user to institution in institution_users table (so session gets institution_id)
            $linkStmt = $pdo->prepare('INSERT IGNORE INTO institution_users (institution_id, user_id) VALUES (?, ?)');
            $linkStmt->execute([$institutionId, $userId]);
            error_log("[POPCORN] Linked user $userId to institution $institutionId");
            
            // Find or create an assessment record for this institution
            $assessmentStmt = $pdo->prepare('SELECT id FROM institution_assessments WHERE institution_id = ? LIMIT 1');
            $assessmentStmt->execute([$institutionId]);
            $assessment = $assessmentStmt->fetch();
            $assessmentId = $assessment ? (int)$assessment['id'] : null;

            $userStmt = $pdo->prepare('SELECT email FROM users WHERE id = ? LIMIT 1');
            $userStmt->execute([$userId]);
            $userRow = $userStmt->fetch();
            $userEmail = $userRow ? $userRow['email'] : '';

            if ($assessmentId) {
                // Insert into institution_candidates
                $candStmt = $pdo->prepare('INSERT INTO institution_candidates (institution_id, assessment_id, user_id, full_name, email, exam_attempt_id, status, invited_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), NOW()) ON DUPLICATE KEY UPDATE updated_at = NOW()');
                $candStmt->execute([
                    $institutionId,
                    $assessmentId,
                    $userId,
                    $applicantName,
                    $userEmail,
                    $attemptId,
                    'started',
                ]);
            } else {
                // No assessment found, try to insert with NULL assessment_id
                $candStmt = $pdo->prepare('INSERT INTO institution_candidates (institution_id, user_id, full_name, email, exam_attempt_id, status, invited_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW(), NOW())');
                $candStmt->execute([
                    $institutionId,
                    $userId,
                    $applicantName,
                    $userEmail,
                    $attemptId,
                    'started',
                ]);
            }
        } catch (Exception $candEx) {
            // Silently fail - don't break popcorn application if candidate insert fails
            error_log("Failed to insert institution_candidate: " . $candEx->getMessage());
        }
    }
    $redirect = 'onboard.php?popcorn_application_id=' . $applicationId;
    if ($attemptId) {
        $redirect .= '&attempt_id=' . $attemptId;
    }
    if ($examType !== '') {
        $redirect .= '&exam_type=' . urlencode($examType);
    }
    if ($skipSuggestions) {
        $redirect .= '&skip_suggestions=1';
    }

    sendResponse(true, 'Popcorn application saved successfully', [
        'application_id' => $applicationId,
        'redirect' => $redirect,
        'attempt_id' => $attemptId,
    ]);
} catch (Exception $e) {
    sendResponse(false, 'Failed to apply for popcorn: ' . $e->getMessage());
}
