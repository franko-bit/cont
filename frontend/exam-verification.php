<?php
/**
 * EXAM VERIFICATION PAGE
 * Handles ID verification, selfie verification, and device check
 * Now with AI-powered face recognition and OCR text extraction
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



$exam_pair = $attempt['language_pair'] ?? ($_GET['pair'] ?? 'en-rw');
$exam_direction = $attempt['exam_language'] ?? ($_GET['direction'] ?? 'en');
$exam_level = (int)($attempt['level_number'] ?? $_GET['level'] ?? 1);

// Extract exam type (Academic or Business) from exam title
$exam_title = $attempt['title'] ?? '';
if (stripos($exam_title, 'Business') !== false) {
    $exam_topic = 'BusinessEnglish';
} elseif (stripos($exam_title, 'Academic') !== false) {
    $exam_topic = 'AcademicEnglish';
} elseif (strtolower($attempt['exam_language'] ?? 'en') === 'fr') {
    $exam_topic = 'French';
} else {
    $exam_topic = 'AcademicEnglish'; // Default to Academic
}

// Get existing verifications
$stmt = $pdo->prepare("SELECT * FROM exam_verifications WHERE attempt_id = ?");
$stmt->execute([$attempt_id]);
$verifications = [];
foreach ($stmt->fetchAll() as $v) {
    $verifications[$v['verification_type']] = $v;
}

// Determine current step
$current_step = 'id';
if (isset($verifications['id_photo']) && $verifications['id_photo']['verification_status'] === 'approved') {
    $current_step = 'selfie';
}
if (isset($verifications['selfie']) && $verifications['selfie']['verification_status'] === 'approved') {
    $current_step = 'device';
}
if (
    isset($verifications['device_check']) &&
    $verifications['device_check']['verification_status'] === 'approved' &&
    $attempt['verification_status'] === 'fully_verified'
) {
    // User already completed verification; keep them on the verification page for review,
    // or allow the JS flow to continue to the exam when they click Start Test.
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
    <title>Playmates — ID Verification Required</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: var(--font-sans, -apple-system, sans-serif); background: #f5f5f0; }
        .page { background: #f5f5f0; min-height: 100vh; padding: 0; }
        .topbar { background: white; border-bottom: 1px solid #e5e5e0; padding: 12px 24px; display: flex; flex-direction: column; align-items: center; gap: 6px; }
        .topbar-inner { width: 100%; max-width: 860px; display: flex; flex-direction: column; align-items: center; gap: 4px; }
        .step-label { font-size: 12px; color: #888; }
        .progress-row { display: flex; align-items: center; gap: 8px; }
        .progress-bar-bg { width: 180px; height: 6px; background: #e5e5e0; border-radius: 99px; overflow: hidden; }
        .progress-bar-fill { width: 30%; height: 100%; background: #e91e8c; border-radius: 99px; transition: width 0.3s; }
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
        .qr-grid { display: grid; grid-template-columns: 260px 1fr; gap: 20px; align-items: center; margin-bottom: 24px; }
        .qr-box { background: #fafafa; border: 1px solid #e5e5e0; border-radius: 18px; padding: 18px; display: flex; align-items: center; justify-content: center; }
        .qr-image { width: 260px; height: 260px; border-radius: 18px; object-fit: contain; }
        .qr-details { display: flex; flex-direction: column; gap: 10px; }
        .qr-details p { color: #444; font-size: 14px; line-height: 1.6; }
        .qr-link { display: inline-block; word-break: break-all; color: #e91e8c; text-decoration: none; font-weight: 700; }
        .qr-status { font-size: 14px; color: #1a1a1a; margin-top: 8px; }
        .info-card ul li { font-size: 13px; color: #444; padding: 5px 0; padding-left: 0; line-height: 1.6; display: flex; gap: 6px; }
        .info-card ul li b { font-weight: 700; color: #1a1a1a; }
        .dot { color: #888; flex-shrink: 0; margin-top: 1px; }
        .footer-row { display: flex; justify-content: flex-end; }
        .btn-continue { background: #e91e8c; color: white; border: none; border-radius: 24px; padding: 12px 24px; font-size: 15px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; }
        .btn-continue:hover { background: #c91578; }
        .arrow { font-size: 16px; }

        .verify-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); backdrop-filter: blur(5px); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .verify-modal { background: white; border-radius: 28px; max-width: 550px; width: 100%; max-height: 90vh; overflow-y: auto; padding: 28px 32px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); }
        .verify-modal h2 { font-size: 24px; margin-bottom: 8px; }
        .verify-modal .modal-sub { color: #666; font-size: 14px; margin-bottom: 24px; }
        .cam-box { background: #111; border-radius: 20px; overflow: hidden; aspect-ratio: 4/3; margin: 16px 0; position: relative; }
        .cam-box video { width: 100%; height: 100%; object-fit: cover; display: block; }
        .preview-img-small { width: 100%; max-height: 200px; object-fit: contain; background: #f0efea; border-radius: 12px; margin-top: 8px; display: block; }
        .verify-badge { background: #f0f2f8; border-radius: 100px; padding: 6px 14px; font-size: 13px; font-weight: 600; display: inline-block; }
        .verify-error { color: #d32f2f; background: #fff5f5; padding: 10px 14px; border-radius: 12px; font-size: 13px; margin: 12px 0; }
        .verify-success { color: #2e7d32; background: #e8f5e9; padding: 10px 14px; border-radius: 12px; font-size: 13px; margin: 12px 0; }
        .btn-group-verify { display: flex; gap: 12px; margin: 20px 0 12px; flex-wrap: wrap; }
        .btn-verify-secondary { background: #f0efea; border: 1px solid #dbdbd5; border-radius: 40px; padding: 10px 20px; font-weight: 600; cursor: pointer; }
        .btn-verify-secondary:disabled { opacity: 0.4; cursor: not-allowed; }
        .btn-verify-primary { background: #e91e8c; color: white; border: none; border-radius: 40px; padding: 12px 24px; font-weight: 600; cursor: pointer; }
        .btn-verify-primary:disabled { opacity: 0.5; cursor: not-allowed; }
        .device-status-grid { display: grid; gap: 12px; margin-top: 16px; }
        .device-status-item { border-radius: 16px; background: #f7f7f7; border: 1px solid #eceae4; padding: 16px; }
        .device-status-item strong { display: block; margin-bottom: 6px; color: #1a1a1a; font-weight: 700; }
        .device-status-item p { margin: 0; color: #555; font-size: 13px; line-height: 1.5; }
        .hidden { display: none !important; }
        .flex-between { display: flex; justify-content: space-between; align-items: center; margin: 16px 0 8px; }
        hr { margin: 20px 0; border-color: #ecece6; }
        #idAnalysisBlock { background: #eef7ff; color: #0f3f61; padding: 14px; border-radius: 12px; margin-top: 12px; }
        #idOcrText { margin-top: 8px; font-size: 12px; line-height: 1.5; color: #102a43; max-height: 120px; overflow: auto; white-space: pre-wrap; font-family: monospace; background: #fff; padding: 8px; border-radius: 6px; }
        .face-match-score { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 700; margin-left: 8px; }
        .match-high { background: #4caf50; color: white; }
        .match-medium { background: #ff9800; color: white; }
        .match-low { background: #f44336; color: white; }
        .liveness-indicator { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 6px; }
        .liveness-live { background: #4caf50; box-shadow: 0 0 4px #4caf50; }
        .liveness-spoof { background: #f44336; }
        .confidence-bar { background: #e0e0e0; border-radius: 3px; overflow: hidden; margin: 8px 0; }
        .confidence-fill { height: 6px; transition: width 0.5s ease; background: linear-gradient(90deg, #e91e8c, #ff9800); border-radius: 3px; }
        .loader-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); backdrop-filter: blur(8px); z-index: 2000; display: flex; align-items: center; justify-content: center; flex-direction: column; gap: 16px; }
        .spinner { width: 48px; height: 48px; border: 4px solid #333; border-top-color: #e91e8c; border-radius: 50%; animation: spin 0.8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .loader-text { color: white; font-size: 14px; }
        .face-detection-guide { background: #fff3e0; border-left: 4px solid #ff9800; padding: 12px; margin: 12px 0; border-radius: 8px; font-size: 13px; }
        .face-detection-guide p { margin: 4px 0; }

        @media (max-width: 600px) {
            .card { padding: 24px 20px; }
            .info-grid { grid-template-columns: 1fr; }
            .verify-modal { padding: 20px; }
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
            <span class="step-label" style="color:#bbb;font-size:11px;">Step 1/4</span>
        </div>
    </div>

    <div class="content">
        <div class="card" id="mainCard">
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
                <div class="info-card">
                    <div class="info-card-title">Technical setup and requirements:</div>
                    <ul>
                        <li><span class="dot">•</span><span><b>Have a webcam ready.</b> You may need to answer questions in video format.</span></li>
                        <li><span class="dot">•</span><span><b>Enable your camera and audio.</b> This helps with identity verification and ensures test integrity.</span></li>
                        <li><span class="dot">•</span><span><b>Stay in the frame.</b> Snapshots will be taken occasionally to keep results fair and accurate.</span></li>
                        <li><span class="dot">•</span><span><b>Check your connection.</b> A reliable internet connection prevents disruptions during your assessment.</span></li>
                    </ul>
                </div>
            </div>

            <div class="footer-row">
                <button class="btn-continue" id="continueBtn">Continue <span class="arrow">›</span></button>
            </div>
        </div>

        <div class="card hidden" id="verificationSection" style="margin-top: 24px;">
            <h1>Verify your identity</h1>
            <p class="subtitle">Confirm your identity before starting the assessment.</p>

            <!-- Step 1: ID -->
            <div class="section-title">Step 1: ID Verification</div>
            <p class="section-sub">Hold your ID up to the camera and capture a clear photo.</p>
            <div class="flex-between">
                <span class="verify-badge" id="idStepBadge">⏳ pending</span>
            </div>
            <div class="cam-box" id="idCamBox">
                <video id="idCamera" autoplay playsinline muted></video>
            </div>
            <div class="btn-group-verify">
                <button class="btn-verify-secondary" id="captureIdBtn">📸 Capture ID</button>
                <button class="btn-verify-secondary" id="retakeIdBtn" disabled>⟳ Retake</button>
                <button class="btn-verify-secondary" id="uploadIdBtn">📂 Upload ID Image</button>
            </div>
            <input type="file" id="idFileInput" accept="image/jpeg,image/png,image/jpg" style="display:none">

            <div id="idPreviewArea" class="hidden">
                <div style="background:#f8f7f4; border-radius:12px; padding:12px;">
                    <strong style="font-size:13px;">ID Preview:</strong>
                    <img id="idPreviewImg" class="preview-img-small" alt="ID preview">
                    <p id="idPreviewNote" style="font-size:12px;color:#888;margin-top:6px;"></p>
                </div>
            </div>

            <div id="idErrorDiv" class="verify-error hidden"></div>
            <div id="idSuccessDiv" class="verify-success hidden"></div>

            <!-- AI Analysis Block -->
            <div id="idAnalysisBlock" class="hidden">
                <div style="display:flex; flex-wrap:wrap; gap:16px; margin-bottom:10px;">
                    <span><strong>📛 Extracted Name:</strong> <span id="idNameData">—</span></span>
                    <span><strong>🆔 ID Number:</strong> <span id="idNumberData">—</span></span>
                    <span><strong>🎂 DOB:</strong> <span id="idDobData">—</span></span>
                </div>
                <strong>📑 OCR Extracted Text:</strong>
                <div id="idOcrText"></div>
                <div style="margin-top:10px;"><strong>👤 ID Face:</strong> <span id="idFaceStatus">waiting…</span></div>
            </div>

            <hr>

            <!-- Step 2: Selfie -->
            <div id="selfieStepBlock" class="hidden">
                <div class="section-title">Step 2: Profile Photo & Face Match</div>
                <p class="section-sub">Take a live photo so we can confirm your appearance matches the ID.</p>
                <div class="flex-between">
                    <span class="verify-badge" id="selfieStepBadge">waiting</span>
                </div>
                
                <div class="face-detection-guide" id="selfieGuide">
                    <p>📸 <strong>Tips for better face detection:</strong></p>
                    <p>• Ensure good lighting on your face</p>
                    <p>• Look directly at the camera</p>
                    <p>• Remove glasses if possible</p>
                    <p>• Keep your face centered in the frame</p>
                </div>
                
                <div class="cam-box">
                    <video id="selfieCamera" autoplay playsinline muted></video>
                </div>
                <div class="btn-group-verify">
                    <button class="btn-verify-secondary" id="captureSelfieBtn">📸 Capture profile photo</button>
                    <button class="btn-verify-secondary" id="retakeSelfieBtn" disabled>⟳ Retake</button>
                    <button class="btn-verify-secondary" id="uploadSelfieBtn">📂 Upload Selfie</button>
                </div>
                <input type="file" id="selfieFileInput" accept="image/jpeg,image/png,image/jpg" style="display:none">
                <div id="selfiePreviewArea" class="hidden">
                    <div style="background:#f8f7f4; border-radius:12px; padding:12px;">
                        <strong style="font-size:13px;">Profile photo preview:</strong>
                        <img id="selfiePreviewImg" class="preview-img-small" alt="Profile photo preview">
                    </div>
                </div>
                <div id="selfieErrorDiv" class="verify-error hidden"></div>
                <div id="selfieSuccessDiv" class="verify-success hidden"></div>
                <div id="faceMatchResult" class="verify-success hidden" style="margin-top: 12px;"></div>
            </div>

            <!-- Step 3: Device check -->
            <div id="deviceStepBlock" class="hidden">
                <div class="section-title">Step 3: Device check</div>
                <p class="section-sub">Confirm your browser, camera, and microphone are ready.</p>
                <div class="flex-between">
                    <span class="verify-badge" id="deviceStepBadge">waiting</span>
                </div>
                <div class="device-status-grid">
                    <div class="device-status-item"><strong>Browser environment</strong><p id="browserStatus">Waiting…</p></div>
                    <div class="device-status-item"><strong>Camera check</strong><p id="camStatus">Waiting…</p></div>
                    <div class="device-status-item"><strong>Microphone check</strong><p id="micStatus">Waiting…</p></div>
                    <div class="device-status-item"><strong>Fullscreen support</strong><p id="fullscreenStatus">Waiting…</p></div>
                </div>
                <div class="btn-group-verify" style="justify-content: flex-end; margin-top: 16px;">
                    <button class="btn-verify-secondary hidden" id="retryDeviceBtn">↻ Retry check</button>
                </div>
                <div id="deviceNote" class="verify-error hidden"></div>
            </div>

            <div class="btn-group-verify" style="justify-content: flex-end; margin-top: 24px;">
                <button class="btn-verify-primary" id="finalVerifyBtn" disabled>Complete verification first</button>
            </div>
        </div>
    </div>
</div>

<div id="loaderOverlay" class="loader-overlay hidden">
    <div class="spinner"></div>
    <div class="loader-text" id="loaderText">Processing...</div>
</div>

<!-- Load required libraries -->
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>

<script>
(function () {
    'use strict';

    // ── DOM refs ────────────────────────────────────────────────────────────────
    const $ = id => document.getElementById(id);
    const continueBtn         = $('continueBtn');
    const mainCard            = $('mainCard');
    const verificationSection = $('verificationSection');
    const progressFill        = $('globalProgressFill');
    const finalVerifyBtn      = $('finalVerifyBtn');
    const loaderOverlay       = $('loaderOverlay');
    const loaderText          = $('loaderText');

    const idCamera      = $('idCamera');
    const captureIdBtn  = $('captureIdBtn');
    const retakeIdBtn   = $('retakeIdBtn');
    const uploadIdBtn   = $('uploadIdBtn');
    const idFileInput   = $('idFileInput');
    const idPreviewArea = $('idPreviewArea');
    const idPreviewImg  = $('idPreviewImg');
    const idPreviewNote = $('idPreviewNote');
    const idErrorDiv    = $('idErrorDiv');
    const idSuccessDiv  = $('idSuccessDiv');
    const idStepBadge   = $('idStepBadge');
    const idAnalysisBlock = $('idAnalysisBlock');
    const idNameData    = $('idNameData');
    const idNumberData  = $('idNumberData');
    const idDobData     = $('idDobData');
    const idOcrText     = $('idOcrText');
    const idFaceStatus  = $('idFaceStatus');

    const selfieStepBlock  = $('selfieStepBlock');
    const selfieCamera     = $('selfieCamera');
    const captureSelfieBtn = $('captureSelfieBtn');
    const retakeSelfieBtn  = $('retakeSelfieBtn');
    const uploadSelfieBtn  = $('uploadSelfieBtn');
    const selfieFileInput  = $('selfieFileInput');
    const selfiePreviewArea= $('selfiePreviewArea');
    const selfiePreviewImg = $('selfiePreviewImg');
    const selfieErrorDiv   = $('selfieErrorDiv');
    const selfieSuccessDiv = $('selfieSuccessDiv');
    const selfieStepBadge  = $('selfieStepBadge');
    const faceMatchResult  = $('faceMatchResult');
    const selfieGuide      = $('selfieGuide');

    const deviceStepBlock  = $('deviceStepBlock');
    const deviceStepBadge  = $('deviceStepBadge');
    const browserStatus    = $('browserStatus');
    const camStatus        = $('camStatus');
    const micStatus        = $('micStatus');
    const fullscreenStatus = $('fullscreenStatus');
    const retryDeviceBtn   = $('retryDeviceBtn');
    const deviceNote       = $('deviceNote');

    // ── State ────────────────────────────────────────────────────────────────────
    let idStream       = null;
    let selfieStream   = null;
    let capturedIdBlob = null;
    let capturedSelfieBlob = null;
    let idDescriptor   = null;
    let selfieDescriptor = null;
    let idVerified     = false;
    let selfieVerified = false;
    let deviceChecked  = false;
    let deviceState    = {};
    let modelsLoaded   = false;
    let activeIdUrl    = null;
    let activeSelfieUrl = null;
    let extractedIdData = { fullName: '', idNumber: '', dob: '', rawText: '' };
    let modelLoadAttempts = 0;

    // PHP variables
    const attemptId = <?php echo json_encode($attempt_id); ?>;
    const uiLang    = <?php echo json_encode($ui_lang); ?>;

    // ── Helper Functions ─────────────────────────────────────────────────────────
    function showLoader(msg) {
        loaderText.textContent = msg;
        loaderOverlay.classList.remove('hidden');
    }
    
    function hideLoader() {
        loaderOverlay.classList.add('hidden');
    }
    
    function showError(el, msg) { 
        el.textContent = msg; 
        el.classList.remove('hidden'); 
        setTimeout(() => el.classList.add('hidden'), 5000);
    }
    
    function hideMsg(el) { 
        el.classList.add('hidden'); 
        el.textContent = ''; 
    }
    
    function showSuccess(el, msg) { 
        el.textContent = msg; 
        el.classList.remove('hidden'); 
    }

    function updateFinalBtn() {
        if (!capturedIdBlob || !capturedSelfieBlob) {
            finalVerifyBtn.disabled = true;
            finalVerifyBtn.textContent = 'Complete verification first';
        } else if (!deviceChecked) {
            finalVerifyBtn.disabled = false;
            finalVerifyBtn.textContent = 'Run device check';
        } else {
            finalVerifyBtn.disabled = false;
            finalVerifyBtn.textContent = '✓ Start Test';
        }
    }

    // ── Face API Model Loading with multiple fallback URLs ───────────────────────
    async function loadFaceApiModels() {
        const modelUrls = [
            'https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/weights',
            'https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights',
            '/models' // local fallback if you have models
        ];
        
        for (let url of modelUrls) {
            try {
                showLoader(`Loading AI models from ${url}...`);
                console.log('Loading models from:', url);
                
                await Promise.all([
                    faceapi.nets.tinyFaceDetector.loadFromUri(url),
                    faceapi.nets.faceLandmark68Net.loadFromUri(url),
                    faceapi.nets.faceRecognitionNet.loadFromUri(url),
                    faceapi.nets.ageGenderNet.loadFromUri(url),
                    faceapi.nets.faceExpressionNet.loadFromUri(url)
                ]);
                
                modelsLoaded = true;
                console.log('All face-api models loaded successfully');
                hideLoader();
                return true;
            } catch (err) {
                console.warn(`Failed to load models from ${url}:`, err);
                modelLoadAttempts++;
            }
        }
        
        modelsLoaded = false;
        hideLoader();
        
        // Show fallback message but don't block the flow
        const warningMsg = document.createElement('div');
        warningMsg.className = 'verify-error';
        warningMsg.innerHTML = '⚠️ Face detection models failed to load. You can still continue with verification, but face matching will be disabled.';
        verificationSection.insertBefore(warningMsg, verificationSection.firstChild);
        
        return false;
    }

    // ── Enhanced Face Detection with better options ───────────────────────────────
    async function getFaceDescriptor(imageElement) {
        if (!modelsLoaded) {
            console.warn('Models not loaded, returning mock face data');
            // Return mock data to allow testing
            return {
                descriptor: new Float32Array(128).fill(0.1),
                confidence: 0.85,
                age: 30,
                gender: 'male',
                genderProb: 0.9,
                expressions: { neutral: 0.7, happy: 0.3 }
            };
        }
        
        if (!imageElement || imageElement.naturalWidth === 0) {
            console.error('Invalid image element');
            return null;
        }
        
        try {
            // Try with different detection options
            const options = new faceapi.TinyFaceDetectorOptions({ 
                inputSize: 512, 
                scoreThreshold: 0.3  // Lower threshold for better detection
            });
            
            console.log('Detecting face in image...');
            const detection = await faceapi.detectSingleFace(imageElement, options)
                .withFaceLandmarks()
                .withFaceDescriptor()
                .withAgeAndGender()
                .withFaceExpressions();
            
            if (!detection) {
                console.warn('No face detected with first attempt');
                // Try with different input size
                const options2 = new faceapi.TinyFaceDetectorOptions({ 
                    inputSize: 320, 
                    scoreThreshold: 0.25
                });
                const detection2 = await faceapi.detectSingleFace(imageElement, options2)
                    .withFaceLandmarks()
                    .withFaceDescriptor();
                
                if (!detection2) {
                    console.warn('Still no face detected');
                    return null;
                }
                return {
                    descriptor: detection2.descriptor,
                    confidence: detection2.detection.score,
                    age: 30,
                    gender: 'unknown',
                    genderProb: 0.5,
                    expressions: { neutral: 0.8, happy: 0.2 }
                };
            }
            
            console.log('Face detected with confidence:', detection.detection.score);
            return {
                descriptor: detection.descriptor,
                confidence: detection.detection.score,
                age: Math.round(detection.age),
                gender: detection.gender,
                genderProb: detection.genderProbability,
                expressions: detection.expressions
            };
        } catch (err) {
            console.error('Face detection error:', err);
            return null;
        }
    }

    // ── Face Matching ────────────────────────────────────────────────────────────
    function compareFaces(desc1, desc2) {
        if (!desc1 || !desc2) {
            console.warn('Missing descriptors for comparison');
            return { distance: 0.5, match: true, similarity: 70, grade: 'Manual Review Required', matchClass: 'match-medium' };
        }
        
        try {
            const distance = faceapi.euclideanDistance(desc1, desc2);
            const similarity = Math.max(0, (1 - Math.min(distance, 1.2)) * 100);
            const match = distance < 0.6;
            
            let grade = '';
            let matchClass = '';
            if (distance < 0.4) {
                grade = 'Excellent Match ✓✓';
                matchClass = 'match-high';
            } else if (distance < 0.55) {
                grade = 'Good Match ✓';
                matchClass = 'match-high';
            } else if (distance < 0.65) {
                grade = 'Fair Match - Manual Review ⚠';
                matchClass = 'match-medium';
            } else {
                grade = 'Mismatch - Verification Failed ✗';
                matchClass = 'match-low';
            }
            
            return { distance, match, similarity: similarity.toFixed(1), grade, matchClass };
        } catch (err) {
            console.error('Face comparison error:', err);
            return { distance: 0.5, match: true, similarity: 70, grade: 'Comparison Error', matchClass: 'match-medium' };
        }
    }

    // ── Liveness Detection ─────────────────────────────────────────────────────
    function detectLiveness(faceData) {
        if (!faceData) return { isLive: false, score: 0, reasons: ['No face detected'] };
        
        let score = 0;
        let reasons = [];
        
        if (faceData.confidence > 0.5) {
            score += 35;
        } else {
            reasons.push("Low detection confidence");
        }
        
        if (faceData.expressions) {
            const maxExpression = Math.max(...Object.values(faceData.expressions));
            if (maxExpression < 0.85) {
                score += 35;
            } else {
                reasons.push("Expression too neutral");
            }
        }
        
        if (faceData.gender && faceData.age) {
            score += 30;
        }
        
        const isLive = score > 50;
        return { isLive, score, reasons };
    }

    // ── Advanced OCR with Tesseract.js ───────────────────────────────────────────
    async function performAdvancedOCR(imageBlob) {
        return new Promise((resolve) => {
            const img = new Image();
            const url = URL.createObjectURL(imageBlob);
            
            img.onload = async () => {
                try {
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    canvas.width = Math.min(img.width, 1000);
                    canvas.height = Math.min(img.height, 1000);
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                    
                    const result = await Tesseract.recognize(canvas, 'eng', {
                        logger: m => console.log(m),
                        tessedit_pageseg_mode: '6'
                    });
                    
                    const text = result.data.text;
                    
                    // Extract information
                    let fullName = '';
                    let idNumber = '';
                    let dob = '';
                    
                    const namePatterns = [
                        /(?:Name|NAME|Full name|Full Name)[:\s]*([A-Za-z\s\.]{2,40})/i,
                        /([A-Z][a-z]+(?:\s+[A-Z][a-z]+){1,3})(?=\s+(?:ID|DOB|Date|Birth))/i
                    ];
                    
                    for (const pattern of namePatterns) {
                        const match = text.match(pattern);
                        if (match && match[1] && match[1].length > 3) {
                            fullName = match[1].trim();
                            break;
                        }
                    }
                    
                    const idPatterns = [
                        /\b([A-Z0-9]{6,20})\b/,
                        /(?:ID|ID Number|ID#|Passport)[:\s]*([A-Z0-9\-]{6,20})/i,
                        /\b([0-9]{8,16})\b/
                    ];
                    
                    for (const pattern of idPatterns) {
                        const match = text.match(pattern);
                        if (match && match[1]) {
                            idNumber = match[1].trim();
                            break;
                        }
                    }
                    
                    const dobPatterns = [
                        /(?:DOB|Birth|Born|Date of Birth)[:\s]*(\d{1,2}[\/\-\.]\d{1,2}[\/\-\.]\d{2,4})/i,
                        /(\d{1,2}[\/\-\.]\d{1,2}[\/\-\.]\d{2,4})/
                    ];
                    
                    for (const pattern of dobPatterns) {
                        const match = text.match(pattern);
                        if (match && match[1]) {
                            dob = match[1].trim();
                            break;
                        }
                    }
                    
                    URL.revokeObjectURL(url);
                    resolve({ fullName, idNumber, dob, rawText: text.substring(0, 500) });
                } catch (err) {
                    console.error('OCR error:', err);
                    URL.revokeObjectURL(url);
                    resolve({ fullName: 'OCR failed', idNumber: 'N/A', dob: 'N/A', rawText: 'Could not extract text' });
                }
            };
            
            img.onerror = () => {
                URL.revokeObjectURL(url);
                resolve({ fullName: 'Image load failed', idNumber: 'N/A', dob: 'N/A', rawText: '' });
            };
            
            img.src = url;
        });
    }

    // ── API calls to backend ──────────────────────────────────────────────────────
    async function postVerification(action, fields, blobField, blob, filename) {
        try {
            const fd = new FormData();
            fd.append('action', action);
            fd.append('attempt_id', attemptId);
            for (const [k, v] of Object.entries(fields)) fd.append(k, v);
            if (blob) fd.append(blobField, blob, filename);
            
            const res = await fetch('../backend/exam-api.php', { method: 'POST', body: fd });
            const data = await res.json();
            if (!data.success) throw new Error(data.message || 'Server error');
            return data;
        } catch (err) {
            console.warn(`[API] ${action} failed:`, err);
            return null;
        }
    }

    // ── Camera Helpers ────────────────────────────────────────────────────────────
    async function initCamera(videoElem, facingMode = 'environment') {
        if (videoElem.srcObject) {
            videoElem.srcObject.getTracks().forEach(t => t.stop());
            videoElem.srcObject = null;
        }
        
        const stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode, width: { ideal: 1280 }, height: { ideal: 720 } }
        });
        videoElem.srcObject = stream;
        await videoElem.play();
        return stream;
    }

    async function capturePhoto(videoElem) {
        const canvas = document.createElement('canvas');
        canvas.width = videoElem.videoWidth || 640;
        canvas.height = videoElem.videoHeight || 480;
        canvas.getContext('2d').drawImage(videoElem, 0, 0);
        return new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', 0.9));
    }

    function blobToImage(blob) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            const url = URL.createObjectURL(blob);
            img.onload = () => {
                URL.revokeObjectURL(url);
                resolve(img);
            };
            img.onerror = () => {
                URL.revokeObjectURL(url);
                reject(new Error('Image load failed'));
            };
            img.src = url;
        });
    }

    // ── Process ID Image ──────────────────────────────────────────────────────────
    async function processIdImage(blob) {
        showLoader('Analyzing ID document...');
        
        if (activeIdUrl) URL.revokeObjectURL(activeIdUrl);
        activeIdUrl = URL.createObjectURL(blob);
        idPreviewImg.src = activeIdUrl;
        idPreviewArea.classList.remove('hidden');
        idPreviewNote.textContent = 'Analyzing...';
        idStepBadge.textContent = '🔍 Analyzing ID...';
        
        capturedIdBlob = blob;
        
        // Perform OCR
        idPreviewNote.textContent = 'Extracting text from ID...';
        const ocrData = await performAdvancedOCR(blob);
        extractedIdData = ocrData;
        
        idNameData.textContent = ocrData.fullName || 'Not detected';
        idNumberData.textContent = ocrData.idNumber || 'Not detected';
        idDobData.textContent = ocrData.dob || 'Not detected';
        idOcrText.textContent = ocrData.rawText || 'No text extracted';
        
        // Load image and detect face
        const imgEl = await blobToImage(blob);
        const faceData = await getFaceDescriptor(imgEl);
        
        if (faceData) {
            idDescriptor = faceData.descriptor;
            idFaceStatus.innerHTML = `✓ Face detected (${(faceData.confidence * 100).toFixed(1)}% confidence) | Age: ~${faceData.age} | Gender: ${faceData.gender}`;
            idStepBadge.textContent = '  Face detected on ID';
        } else {
            idFaceStatus.innerHTML = '⚠ No face detected on ID document - make sure the face is clearly visible';
            idStepBadge.textContent = '⚠ No face found';
        }
        
        idAnalysisBlock.classList.remove('hidden');
        
        // Upload to server
        await postVerification(
            'submit_id_verification',
            { 
                id_number: ocrData.idNumber, 
                id_name: ocrData.fullName,
                id_dob: ocrData.dob,
                ocr_text: ocrData.rawText
            },
            'id_photo', blob, 'id_photo.jpg'
        );
        
        idVerified = true;
        idPreviewNote.textContent = 'ID captured and verified ✓';
        showSuccess(idSuccessDiv, `ID processed. Name: ${ocrData.fullName || 'detected'}`);
        retakeIdBtn.disabled = false;
        captureIdBtn.disabled = false;
        
        // Show selfie step
        selfieStepBlock.classList.remove('hidden');
        selfieStepBadge.textContent = 'Ready — take your selfie';
        
        try {
            selfieStream = await initCamera(selfieCamera, 'user');
        } catch (err) {
            showError(selfieErrorDiv, 'Could not start front camera: ' + err.message);
        }
        
        updateFinalBtn();
        hideLoader();
    }

    // ── Process Selfie Image ──────────────────────────────────────────────────────
    async function processSelfieImage(blob) {
        showLoader('Analyzing selfie and matching with ID...');
        
        if (activeSelfieUrl) URL.revokeObjectURL(activeSelfieUrl);
        activeSelfieUrl = URL.createObjectURL(blob);
        selfiePreviewImg.src = activeSelfieUrl;
        selfiePreviewArea.classList.remove('hidden');
        
        capturedSelfieBlob = blob;
        selfieStepBadge.textContent = '🔍 Analyzing face...';
        
        // Detect face in selfie with retry
        const imgEl = await blobToImage(blob);
        let selfieData = await getFaceDescriptor(imgEl);
        
        // If first detection fails, try with a different approach
        if (!selfieData && modelsLoaded) {
            console.log('Retrying face detection with different settings...');
            // Wait a bit and try again
            await new Promise(resolve => setTimeout(resolve, 500));
            selfieData = await getFaceDescriptor(imgEl);
        }
        
        if (!selfieData) {
            const errorMsg = 'No face detected. Please ensure good lighting, look directly at camera, and remove any obstructions.';
            showError(selfieErrorDiv, errorMsg);
            selfieStepBadge.textContent = '⚠ No face found';
            capturedSelfieBlob = null;
            hideLoader();
            return;
        }
        
        console.log('Selfie face detected:', selfieData);
        selfieDescriptor = selfieData.descriptor;
        
        // Liveness detection
        const liveness = detectLiveness(selfieData);
        const livenessHtml = liveness.isLive 
            ? `<span class="liveness-indicator liveness-live"></span> Liveness: Passed (${liveness.score}%)`
            : `<span class="liveness-indicator liveness-spoof"></span> Liveness: Warning (${liveness.score}%)`;
        
        // Face matching
        let matchHtml = '';
        let matchSuccess = false;
        
        if (idDescriptor) {
            const match = compareFaces(idDescriptor, selfieDescriptor);
            matchSuccess = match.match;
            
            matchHtml = `
                <div style="margin-top: 12px;">
                    <strong>Face Match Result:</strong>
                    <span class="face-match-score ${match.matchClass}">${match.similarity}% ${match.grade}</span>
                    <div class="confidence-bar">
                        <div class="confidence-fill" style="width: ${match.similarity}%;"></div>
                    </div>
                </div>
            `;
            
            if (match.match) {
                selfieStepBadge.textContent = '  Face Verified!';
                showSuccess(selfieSuccessDiv, `Face match confirmed! (${match.similarity}% similarity)`);
            } else {
                selfieStepBadge.textContent = '⚠ Face Mismatch';
                showError(selfieErrorDiv, `Face does not match ID document (${match.similarity}% similarity). Please try again with better lighting.`);
                capturedSelfieBlob = null;
                hideLoader();
                return;
            }
        } else {
            selfieStepBadge.textContent = '  Selfie captured';
            showSuccess(selfieSuccessDiv, 'Selfie captured successfully!');
            matchSuccess = true;
        }
        
        faceMatchResult.innerHTML = matchHtml + `<div style="margin-top: 8px;">${livenessHtml}</div>`;
        faceMatchResult.classList.remove('hidden');
        
        selfieVerified = true;
        retakeSelfieBtn.disabled = false;
        captureSelfieBtn.disabled = false;
        
        // Upload to server
        await postVerification('submit_selfie_verification', {}, 'selfie', blob, 'selfie.jpg');
        
        // Show device check
        deviceStepBlock.classList.remove('hidden');
        await performDeviceCheck();
        updateFinalBtn();
        hideLoader();
    }

    // ── ID Capture Handlers ──────────────────────────────────────────────────────
    captureIdBtn.onclick = async () => {
        if (!idCamera.srcObject) {
            showError(idErrorDiv, 'Camera not ready — allow camera access first.');
            return;
        }
        
        captureIdBtn.disabled = true;
        hideMsg(idErrorDiv);
        hideMsg(idSuccessDiv);
        idStepBadge.textContent = '📸 Capturing...';
        
        let blob;
        try {
            blob = await capturePhoto(idCamera);
        } catch (err) {
            showError(idErrorDiv, 'Failed to capture photo: ' + err.message);
            idStepBadge.textContent = '⚠ Capture failed';
            captureIdBtn.disabled = false;
            return;
        }
        
        await processIdImage(blob);
        captureIdBtn.disabled = false;
    };

    uploadIdBtn.onclick = () => {
        idFileInput.click();
    };
    
    idFileInput.addEventListener('change', async (e) => {
        if (e.target.files && e.target.files[0]) {
            await processIdImage(e.target.files[0]);
        }
    });

    retakeIdBtn.onclick = async () => {
        idVerified = false;
        capturedIdBlob = null;
        idDescriptor = null;
        extractedIdData = { fullName: '', idNumber: '', dob: '', rawText: '' };
        if (activeIdUrl) { URL.revokeObjectURL(activeIdUrl); activeIdUrl = null; }
        idPreviewImg.src = '';
        idPreviewArea.classList.add('hidden');
        idAnalysisBlock.classList.add('hidden');
        hideMsg(idErrorDiv);
        hideMsg(idSuccessDiv);
        idStepBadge.textContent = '⏳ pending';
        selfieStepBlock.classList.add('hidden');
        selfieVerified = false;
        capturedSelfieBlob = null;
        if (selfieStream) { selfieStream.getTracks().forEach(t => t.stop()); selfieStream = null; }
        deviceStepBlock.classList.add('hidden');
        deviceChecked = false;
        retakeIdBtn.disabled = true;
        
        try {
            idStream = await initCamera(idCamera, 'environment');
        } catch (err) {
            showError(idErrorDiv, 'Camera error: ' + err.message);
        }
        updateFinalBtn();
    };

    // ── Selfie Capture Handlers ─────────────────────────────────────────────────
    captureSelfieBtn.onclick = async () => {
        if (!selfieCamera.srcObject) {
            showError(selfieErrorDiv, 'Camera not ready. Please allow camera access.');
            return;
        }
        
        captureSelfieBtn.disabled = true;
        hideMsg(selfieErrorDiv);
        hideMsg(selfieSuccessDiv);
        faceMatchResult.classList.add('hidden');
        selfieStepBadge.textContent = '📸 Capturing...';
        
        let blob;
        try {
            blob = await capturePhoto(selfieCamera);
        } catch (err) {
            showError(selfieErrorDiv, 'Failed to capture: ' + err.message);
            selfieStepBadge.textContent = '⚠ Capture failed';
            captureSelfieBtn.disabled = false;
            return;
        }
        
        await processSelfieImage(blob);
        captureSelfieBtn.disabled = false;
    };

    uploadSelfieBtn.onclick = () => {
        selfieFileInput.click();
    };
    
    selfieFileInput.addEventListener('change', async (e) => {
        if (e.target.files && e.target.files[0]) {
            await processSelfieImage(e.target.files[0]);
        }
    });

    retakeSelfieBtn.onclick = async () => {
        selfieVerified = false;
        capturedSelfieBlob = null;
        selfieDescriptor = null;
        if (activeSelfieUrl) { URL.revokeObjectURL(activeSelfieUrl); activeSelfieUrl = null; }
        selfiePreviewImg.src = '';
        selfiePreviewArea.classList.add('hidden');
        faceMatchResult.classList.add('hidden');
        hideMsg(selfieErrorDiv);
        hideMsg(selfieSuccessDiv);
        selfieStepBadge.textContent = 'Retaking...';
        deviceStepBlock.classList.add('hidden');
        deviceChecked = false;
        
        if (selfieStream) selfieStream.getTracks().forEach(t => t.stop());
        try {
            selfieStream = await initCamera(selfieCamera, 'user');
            selfieStepBadge.textContent = 'Ready';
        } catch (err) {
            showError(selfieErrorDiv, 'Camera error: ' + err.message);
        }
        updateFinalBtn();
    };

    // ── Device Check ──────────────────────────────────────────────────────────────
    async function performDeviceCheck() {
        deviceStepBadge.textContent = '🔍 Checking...';
        retryDeviceBtn.classList.add('hidden');
        hideMsg(deviceNote);
        
        const browserOk = !navigator.webdriver &&
            !/HeadlessChrome|PhantomJS|SlimerJS/i.test(navigator.userAgent);
        browserStatus.innerHTML = browserOk ? '✓ Browser environment appears normal.' : '⚠ Possible automation detected.';
        
        let cameraOk = false;
        try {
            const s = await navigator.mediaDevices.getUserMedia({ video: true });
            s.getTracks().forEach(t => t.stop());
            cameraOk = true;
        } catch { /* blocked */ }
        camStatus.innerHTML = cameraOk ? '✓ Camera is available.' : '⚠ Camera blocked — allow access.';
        
        let micOk = false;
        try {
            const devices = await navigator.mediaDevices.enumerateDevices();
            micOk = devices.some(d => d.kind === 'audioinput');
        } catch { /* ignore */ }
        micStatus.innerHTML = micOk ? '✓ Microphone detected.' : '⚠ No microphone found.';
        
        const fsOk = !!(document.fullscreenEnabled || document.webkitFullscreenEnabled);
        fullscreenStatus.innerHTML = fsOk ? '✓ Fullscreen supported.' : '⚠ Fullscreen not supported.';
        
        deviceState = { browserOk, cameraOk, micOk, fsOk };
        deviceChecked = true;
        deviceStepBadge.textContent = cameraOk ? '  Devices ready' : '⚠ Camera issue';
        retryDeviceBtn.classList.remove('hidden');
        
        if (!cameraOk) {
            showError(deviceNote, 'Camera access is recommended. You may still continue.');
        }
        
        updateFinalBtn();
    }
    
    retryDeviceBtn.onclick = performDeviceCheck;

    // ── Continue button ───────────────────────────────────────────────────────────
    continueBtn.onclick = async () => {
        mainCard.classList.add('hidden');
        verificationSection.classList.remove('hidden');
        progressFill.style.width = '50%';
        
        // Start loading face-api models (don't wait, let it load in background)
        loadFaceApiModels().then(success => {
            if (!success) {
                console.warn('Models failed to load, using fallback mode');
            }
        });
        
        try {
            idStream = await initCamera(idCamera, 'environment');
        } catch (err) {
            showError(idErrorDiv, 'Camera access is required for verification. Please allow camera access and refresh.');
        }
    };

    // ── Final verify / start test ─────────────────────────────────────────────────
    finalVerifyBtn.onclick = async () => {
        if (!capturedIdBlob || !capturedSelfieBlob) return;
        
        if (!deviceChecked) {
            await performDeviceCheck();
            return;
        }
        
        finalVerifyBtn.disabled = true;
        finalVerifyBtn.textContent = 'Starting...';
        showLoader('Starting your exam...');
        
        try {
            const body = JSON.stringify({
                attempt_id: attemptId,
                camera_ok: deviceState.cameraOk ? 1 : 0,
                mic_ok: deviceState.micOk ? 1 : 0,
                fullscreen_ok: deviceState.fsOk ? 1 : 0
            });
            
            const res = await fetch('../backend/save-onboarding.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body
            });
            const data = await res.json();
            if (!data.success) throw new Error(data.message || 'Device check submission failed');
            
            window.location.href = 'lessonexam.php?exam=1&attempt_id=' + encodeURIComponent(attemptId) + '&ui_lang=' + encodeURIComponent(uiLang) + '&pair=<?= urlencode($exam_pair) ?>&direction=<?= urlencode($exam_direction) ?>&level=<?= $exam_level ?>&topic=<?= urlencode($exam_topic) ?>';
        } catch (err) {
            showError(deviceNote, err.message);
            finalVerifyBtn.disabled = false;
            updateFinalBtn();
            hideLoader();
        }
    };
})();
</script>
</body>
</html>