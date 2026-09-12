<?php
/**
 * EXAM RULES PAGE
 * Shows exam rules and instructions before verification
 */
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
session_start();

require_once '../backend/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$attempt_id = (int)($_GET['attempt_id'] ?? 0);
$ui_lang = $_GET['ui_lang'] ?? $_SESSION['ui_lang'] ?? 'en';

// If no attempt_id is provided, create or resume a verification attempt for the selected exam
if ($attempt_id <= 0 && !empty($_GET['pair']) && !empty($_GET['level']) && !empty($_GET['direction'])) {
    $exam_pair = $_GET['pair'];
    $exam_direction = $_GET['direction'];
    $exam_level = (int)$_GET['level'];

    $stmt = $pdo->prepare("SELECT * FROM exams WHERE language_pair = ? AND level_number = ? AND exam_language = ? AND is_active = 1 LIMIT 1");
    $stmt->execute([$exam_pair, $exam_level, $exam_direction]);
    $exam = $stmt->fetch();

    if ($exam) {
        $stmt = $pdo->prepare("SELECT * FROM exam_attempts WHERE user_id = ? AND exam_id = ? AND status IN ('pending', 'verification', 'in_progress') ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$user_id, $exam['id']]);
        $existingAttempt = $stmt->fetch();

        if ($existingAttempt) {
            $attempt_id = $existingAttempt['id'];
        } else {
            $stmt = $pdo->prepare("INSERT INTO exam_attempts (user_id, exam_id, status, verification_status, ip_address, user_agent) VALUES (?, ?, 'verification', 'pending', ?, ?)");
            $stmt->execute([$user_id, $exam['id'], $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']);
            $attempt_id = $pdo->lastInsertId();
        }
    }
}

$_SESSION['ui_lang'] = $ui_lang;

// Get attempt details
$stmt = $pdo->prepare("
    SELECT ea.*, e.title, e.description, e.exam_language, e.duration_minutes, e.passing_score, e.total_questions, e.language_pair, e.level_number
    FROM exam_attempts ea
    JOIN exams e ON ea.exam_id = e.id
    WHERE ea.id = ? AND ea.user_id = ?
");
$stmt->execute([$attempt_id, $user_id]);
$attempt = $stmt->fetch();

if (!$attempt) {
    header("Location: dashboard.php?error=invalid_attempt");
    exit;
}

function t($key) {
    global $ui_lang;
    $translations = [
        'en' => [
            'verify_identity' => 'Verify Your Identity',
            'step_id' => 'Step 1: ID Verification',
            'step_selfie' => 'Step 2: Selfie Verification',
            'step_device' => 'Step 3: Device Check',
            'id_instruction' => 'Upload or capture a photo of your ID (passport, national ID, or driver\'s license)',
            'selfie_instruction' => 'Take a selfie to verify it\'s really you',
            'device_instruction' => 'Check that your camera and microphone work properly',
            'upload_id' => 'Upload ID Photo',
            'take_selfie' => 'Take Selfie',
            'check_devices' => 'Check Devices',
            'continue' => 'Continue',
            'cancel' => 'Cancel',
            'exam_title' => 'Exam',
            'back_to_portal' => '← Back to Portal',
            'id_number' => 'ID Number',
            'full_name' => 'Full Name (as on ID)',
            'submit_id' => 'Submit ID',
            'capture' => 'Capture',
            'retake' => 'Retake',
            'camera_ok' => 'Camera Working',
            'mic_ok' => 'Microphone Working',
            'fullscreen_ok' => 'Fullscreen Mode',
            'all_good' => 'All checks passed!',
            'something_wrong' => 'Some checks failed. Please try again.',
            'processing' => 'Processing...',
            'approved' => '✓ Approved',
            'pending' => 'Pending...',
            'face_match' => 'Face Match',
            'face_mismatch' => 'Face Mismatch',
            'liveness_pass' => 'Liveness Passed',
            'liveness_fail' => 'Liveness Check Failed',
        ],
        'rw' => [
            'verify_identity' => 'Emeza Identite yawe',
            'step_id' => 'Igikubi cya 1: Emeza ID',
            'step_selfie' => 'Igikubi cya 2: Selfie',
            'step_device' => 'Igikubi cya 3: Genzura Apparate',
            'id_instruction' => 'Pakura cyangwa fata ifoto y\'ID yawe (pasiporo, ID y\'igihugu, cyangwa permit)',
            'selfie_instruction' => 'Fata selfie wemeze ko ari wowe',
            'device_instruction' => 'Genzura ko kamera na microphone bikora neza',
            'upload_id' => 'Pakura Ifoto y\'ID',
            'take_selfie' => 'Fata Selfie',
            'check_devices' => 'Genzura Apparate',
            'continue' => 'Komeza',
            'cancel' => 'Hagarika',
            'exam_title' => 'Ikizamini',
            'back_to_portal' => '← Subira kuri Portal',
            'id_number' => 'Nimero y\'ID',
            'full_name' => 'Amazina Yose (nkuko ari kuri ID)',
            'submit_id' => 'Tanga ID',
            'capture' => 'Fata',
            'retake' => 'Subiza',
            'camera_ok' => 'Kamera Ikora',
            'mic_ok' => 'Microphone Ikora',
            'fullscreen_ok' => 'Modi Fullscreen Ikora',
            'all_good' => 'Byose byagenze neza!',
            'something_wrong' => 'Hari ibintu bidasanzwe. Ongera ugerageze.',
            'processing' => 'Birakomeza...',
            'approved' => '✓ Byemejwe',
            'pending' => 'Birategereje...',
            'face_match' => 'Isura Irahuye',
            'face_mismatch' => 'Isura Ntihuye',
            'liveness_pass' => 'Genzura Liveness Yatsinze',
            'liveness_fail' => 'Genzura Liveness Yananiranye',
        ],
    ];
    return $translations[$ui_lang][$key] ?? $key;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes, viewport-fit=cover">
    <title>Playmates — Exam Rules</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: var(--font-sans, -apple-system, sans-serif); background: #f5f5f0; }
        .page { background: #f5f5f0; min-height: 100vh; padding: 0; }
        .topbar { background: white; border-bottom: 1px solid #e5e5e0; padding: 12px 24px; display: flex; flex-direction: column; align-items: center; gap: 6px; }
        .topbar-inner { width: 100%; max-width: 860px; display: flex; flex-direction: column; align-items: center; gap: 4px; }
        .step-label { font-size: 12px; color: #888; }
        .progress-row { display: flex; align-items: center; gap: 8px; }
        .progress-bar-bg { width: 180px; height: 6px; background: #e5e5e0; border-radius: 99px; overflow: hidden; }
        .progress-bar-fill { width: 0%; height: 100%; background: #e91e8c; border-radius: 99px; transition: width 0.3s; }
        .logo { display: flex; align-items: center; gap: 8px; position: absolute; left: 24px; top: 14px; }
        .logo-icon { width: 32px; height: 32px; }
        .logo-text { font-size: 16px; font-weight: 600; color: #1a1a1a; }
        .content { max-width: 860px; margin: 32px auto; padding: 0 20px; }
        .card { background: white; border-radius: 12px; border: 1px solid #e5e5e0; padding: 40px 48px; }
        h1 { font-size: 22px; font-weight: 700; color: #1a1a1a; margin-bottom: 8px; }
        .subtitle { font-size: 14px; color: #666; margin-bottom: 20px; }
        .alert-box { background: #fff8e6; border: 1px solid #f5d580; border-radius: 8px; padding: 14px 18px; font-size: 14px; color: #5a4200; margin-bottom: 28px; line-height: 1.6; }
        .alert-box b { font-weight: 700; }
        .steps-label { font-size: 14px; color: #333; margin-bottom: 20px; }
        .stepper { display: flex; align-items: flex-start; gap: 0; margin-bottom: 36px; }
        .step-item { display: flex; flex-direction: column; align-items: center; flex: 1; }
        .step-connector { flex: 1; height: 2px; background: #d5d5d0; margin-top: 18px; }
        .step-circle { width: 36px; height: 36px; border-radius: 50%; border: 2px solid #d5d5d0; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 600; color: #888; background: white; }
        .step-circle.done { background: white; border-color: #d5d5d0; }
        .step-circle.done svg { display: block; }
        .step-circle.active { border-color: #e91e8c; color: #e91e8c; background: white; }
        .step-name { font-size: 13px; font-weight: 700; color: #1a1a1a; margin-top: 8px; text-align: center; }
        .step-detail { font-size: 12px; color: #888; text-align: center; line-height: 1.7; }
        .section-title { font-size: 15px; font-weight: 700; color: #1a1a1a; margin-bottom: 6px; }
        .section-sub { font-size: 14px; color: #555; margin-bottom: 12px; }
        .bullet-list { list-style: none; margin-bottom: 28px; }
        .bullet-list li { font-size: 14px; color: #444; padding: 4px 0; padding-left: 16px; position: relative; line-height: 1.6; }
        .bullet-list li::before { content: "·"; position: absolute; left: 4px; color: #888; font-size: 18px; line-height: 1.2; }
        .bullet-list b { font-weight: 700; color: #1a1a1a; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 32px; }
        .info-card { background: #f0f2f8; border-radius: 10px; padding: 18px 20px; }
        .info-card-title { font-size: 14px; font-weight: 700; color: #1a1a1a; margin-bottom: 10px; }
        .info-card ul { list-style: none; }
        .info-card ul li { font-size: 13px; color: #444; padding: 5px 0; padding-left: 0; line-height: 1.6; display: flex; gap: 6px; }
        .info-card ul li b { font-weight: 700; color: #1a1a1a; }
        .dot { color: #888; flex-shrink: 0; margin-top: 1px; }
        .footer-row { display: flex; justify-content: flex-end; }
        .btn-continue { background: #e91e8c; color: white; border: none; border-radius: 24px; padding: 12px 24px; font-size: 15px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; }
        .btn-continue:hover { background: #c91578; }
        .arrow { font-size: 16px; }

        @media (max-width: 600px) {
            .card { padding: 24px 20px; }
            .info-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="page">
    <div class="topbar" style="position:relative;">
        <div class="logo">
            <svg class="logo-icon" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="16" cy="16" r="16" fill="#e91e8c"/>
                <circle cx="16" cy="13" r="5" fill="white" opacity="0.9"/>
                <ellipse cx="16" cy="24" rx="8" ry="5" fill="white" opacity="0.7"/>
            </svg>
            <span class="logo-text">Playmates</span>
        </div>
        <div class="topbar-inner">
            <span class="step-label">Playmates set up</span>
            <div class="progress-row">
                <div class="progress-bar-bg"><div class="progress-bar-fill" id="globalProgressFill"></div></div>
            </div>
            <span class="step-label" style="color:#bbb;font-size:11px;">Step 1/3</span>
        </div>
    </div>

    <div class="content">
        <div class="card">
            <h1>Hello! Ready to showcase your skills?</h1>
            <p class="subtitle">Complete verification to move on to your assessment.</p>

            <div class="alert-box">
                You get <b>1 attempt</b> per test. Your score will be added to your profile and the assessment cannot be retaken for the next 3 months.<br>Make sure you're ready and focused before you start.
            </div>

            <p class="steps-label">This test includes the following steps:</p>

            <div class="stepper">
                <div class="step-item">
                    <div class="step-circle done">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="7" stroke="#ccc" stroke-width="1.5"/><path d="M5 8.5l2 2 4-4" stroke="#999" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                    <div class="step-name">Practice test</div>
                    <div class="step-detail">5 questions<br>5 mins</div>
                </div>
                <div class="step-connector"></div>
                <div class="step-item">
                    <div class="step-circle active">1</div>
                    <div class="step-name">Actual test</div>
                    <div class="step-detail">20 questions<br>15 mins</div>
                </div>
                <div class="step-connector"></div>
                <div class="step-item">
                    <div class="step-circle" style="border-color:#e5e5e0;">
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2 7c0 2.76 2.24 5 5 5s5-2.24 5-5-2.24-5-5-5-5 2.24-5 5z" stroke="#ccc" stroke-width="1.2"/><circle cx="5" cy="6" r="1" fill="#ccc"/><circle cx="9" cy="6" r="1" fill="#ccc"/><path d="M5 9.5c.5.7 1.5.7 2 0" stroke="#ccc" stroke-width="1" stroke-linecap="round"/></svg>
                    </div>
                    <div class="step-name">Results</div>
                    <div class="step-detail">Score<br>-</div>
                </div>
            </div>

            <div class="section-title">Test attempts and retakes</div>
            <p class="section-sub">Maintain the integrity of your verified skills with these guidelines:</p>
            <ul class="bullet-list">
                <li>Give it your best shot! You have <b>1 attempt per test</b> and can try again in 3 months.</li>
                <li>See your results. Your test score will be available shortly after you complete the test.</li>
                <li>Challenge yourself! Take up to <b>3 different tests</b> within 24 hours.</li>
                <li>Stay sharp! Keep your skills verified by retaking this test <b>every 24 months</b>.</li>
            </ul>

            <div class="info-grid">
                <div class="info-card">
                    <div class="info-card-title">Here's what to know before taking the test:</div>
                    <ul>
                        <li><span class="dot">•</span><span><b>Stay mindful of time.</b> Each test is timed, and you'll see a timer for each section or question.</span></li>
                        <li><span class="dot">•</span><span><b>Commit to one sitting.</b> Once you start, you won't be able to pause or take breaks.</span></li>
                        <li><span class="dot">•</span><span><b>Use the right tools.</b> A pen, paper, and calculator are allowed.</span></li>
                        <li><span class="dot">•</span><span><b>Keep it fair.</b> Avoid using AI or other external tools.</span></li>
                    </ul>
                </div>
            </div>

            <div class="footer-row">
                <button class="btn-continue" id="continueBtn">
                    Continue to Verification ›
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    const continueBtn = document.getElementById('continueBtn');

    continueBtn.onclick = () => {
        // Redirect to QR page
        const urlParams = new URLSearchParams(window.location.search);
        window.location.href = 'exam-qr.php?' + urlParams.toString();
    };
})();
</script>

</body>
</html>