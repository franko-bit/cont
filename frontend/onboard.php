<?php
require_once '../backend/config.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    session_unset();
    session_destroy();
    session_start();
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    header("Location: signin.php");
    exit;
}

$userId     = $_SESSION['user_id'];
$attempt_id = (int)($_GET['attempt_id'] ?? 0);
$ui_lang    = $_GET['ui_lang'] ?? 'en';
$exam_type  = trim(strtolower($_GET['exam_type'] ?? ''));

if ($attempt_id <= 0 && !empty($exam_type)) {
    $pattern = $exam_type === 'academic' ? '%academic%' : ($exam_type === 'business' ? '%business%' : '%' . $exam_type . '%');
    $stmt = $pdo->prepare("SELECT * FROM exams WHERE LOWER(title) LIKE ? AND is_active = 1 ORDER BY level_number ASC LIMIT 1");
    $stmt->execute([$pattern]);
    $exam = $stmt->fetch();

    if (!$exam) {
        $stmt = $pdo->prepare("SELECT * FROM exams WHERE exam_language = 'en' AND is_active = 1 ORDER BY level_number ASC LIMIT 1");
        $stmt->execute();
        $exam = $stmt->fetch();
    }

    if ($exam) {
        $stmt = $pdo->prepare("SELECT * FROM exam_attempts WHERE user_id = ? AND exam_id = ? AND status IN ('pending','verification','in_progress') ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$userId, $exam['id']]);
        $existingAttempt = $stmt->fetch();

        if ($existingAttempt) {
            $attempt_id = $existingAttempt['id'];
        } else {
            $stmt = $pdo->prepare("INSERT INTO exam_attempts (user_id, exam_id, status, verification_status, ip_address, user_agent) VALUES (?, ?, 'verification', 'pending', ?, ?)");
            $stmt->execute([$userId, $exam['id'], $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']);
            $attempt_id = $pdo->lastInsertId();
        }
    }
}

if ($attempt_id <= 0 && !empty($_GET['pair']) && !empty($_GET['level']) && !empty($_GET['direction'])) {
    $exam_pair      = $_GET['pair'];
    $exam_direction = $_GET['direction'];
    $exam_level     = (int)$_GET['level'];

    $stmt = $pdo->prepare("SELECT * FROM exams WHERE language_pair = ? AND level_number = ? AND exam_language = ? AND is_active = 1 LIMIT 1");
    $stmt->execute([$exam_pair, $exam_level, $exam_direction]);
    $exam = $stmt->fetch();

    if ($exam) {
        $stmt = $pdo->prepare("SELECT * FROM exam_attempts WHERE user_id = ? AND exam_id = ? AND status IN ('pending','verification','in_progress') ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$userId, $exam['id']]);
        $existingAttempt = $stmt->fetch();

        if ($existingAttempt) {
            $attempt_id = $existingAttempt['id'];
        } else {
            $stmt = $pdo->prepare("INSERT INTO exam_attempts (user_id, exam_id, status, verification_status, ip_address, user_agent) VALUES (?, ?, 'verification', 'pending', ?, ?)");
            $stmt->execute([$userId, $exam['id'], $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']);
            $attempt_id = $pdo->lastInsertId();
        }
    }
}

$exam_params = null;
if ($attempt_id > 0) {
    $stmt = $pdo->prepare("SELECT e.language_pair, e.exam_language, e.level_number FROM exam_attempts ea JOIN exams e ON ea.exam_id = e.id WHERE ea.id = ? AND ea.user_id = ?");
    $stmt->execute([$attempt_id, $userId]);
    $exam = $stmt->fetch();
    if ($exam) {
        $exam_params = ['pair' => $exam['language_pair'], 'direction' => $exam['exam_language'], 'level' => $exam['level_number']];
    }
}

$popcornApplicationId = (int)($_GET['popcorn_application_id'] ?? 0);
$popcornApplication   = null;
if ($popcornApplicationId > 0) {
    try {
        $stmt = $pdo->prepare('SELECT * FROM popcorn_applications WHERE id = ? LIMIT 1');
        $stmt->execute([$popcornApplicationId]);
        $popcornApplication = $stmt->fetch();
        if ($popcornApplication && $popcornApplication['status'] === 'applied') {
            $pdo->prepare('UPDATE popcorn_applications SET status = ?, updated_at = NOW() WHERE id = ?')->execute(['in_progress', $popcornApplicationId]);
            $popcornApplication['status'] = 'in_progress';
        }
    } catch (Exception $e) { $popcornApplication = null; }
}

if ($attempt_id <= 0) {
    $stmt = $pdo->prepare("SELECT ea.id, e.language_pair, e.exam_language, e.level_number FROM exam_attempts ea JOIN exams e ON ea.exam_id = e.id WHERE ea.user_id = ? AND ea.verification_status IN ('fully_verified','id_verified','selfie_verified') ORDER BY ea.created_at DESC LIMIT 1");
    $stmt->execute([$userId]);
    $exam = $stmt->fetch();
    if ($exam) {
        $attempt_id  = (int)$exam['id'];
        $exam_params = ['pair' => $exam['language_pair'], 'direction' => $exam['exam_language'], 'level' => $exam['level_number']];
    }
}

$stmt = $pdo->prepare("SELECT verified, verification_status FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user               = $stmt->fetch();
$isVerified         = $user && $user['verified'] == 1;
$showVerificationBlock = !$isVerified;
?>
<!DOCTYPE html>
<html lang="<?= $ui_lang ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Exam Readiness · Playmates</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons/css/flag-icons.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{
  --sand:#f5ede6;--sand-mid:#ede3d8;--sand-dark:#ddd0c2;
  --sage:#c6dfd5;--sage-mid:#8bbfad;--sage-dark:#3a7a68;--sage-deep:#225548;
  --butter:#f5e8b0;--peach:#f5d6c4;--blush:#f9d8cd;
  --ink:#1a1a18;--ink-mid:#4a4845;--muted:#8a8178;--soft:#b8b0a5;
  --surface:#faf8f5;--border:rgba(0,0,0,0.07);
  --radius-sm:8px;--radius:14px;--radius-lg:20px;
  --transition:0.25s cubic-bezier(.4,0,.2,1);
  /* warning amber */
  --amber-bg:#fef8ec;--amber-border:#f5c96a;--amber-text:#7a5c1e;--amber-deep:#5a3e08;
}
html,body{
  min-height:100vh;
  font-family:'DM Sans',sans-serif;
  font-size:14px;
  color:var(--ink);
  background:var(--sand);
  display:flex;
  align-items:flex-start;
  justify-content:center;
  padding:28px 16px 40px;
}

/* ── BACK LINK ── */
.back-link{
  display:inline-flex;align-items:center;gap:6px;
  font-size:12px;font-weight:500;color:var(--muted);
  text-decoration:none;margin-bottom:20px;
  transition:color var(--transition);
}
.back-link:hover{color:var(--sage-dark)}

/* ── WRAPPER ── */
.wrapper{width:100%;max-width:860px}

/* ── PAGE HEADER ── */
.page-header{
  display:flex;flex-wrap:wrap;align-items:center;
  justify-content:space-between;gap:12px;
  margin-bottom:22px;
}
.page-header-left{display:flex;align-items:center;gap:12px}
.brand-row{display:flex;align-items:center;gap:8px;margin-bottom:14px}
.brand-row img{width:26px;height:26px;border-radius:6px;object-fit:contain}
.brand-row span{font-family:'DM Serif Display',serif;font-size:15px;color:var(--sage-deep)}
.page-title{font-family:'DM Serif Display',serif;font-size:24px;color:var(--ink);letter-spacing:-0.3px;margin-bottom:4px}
.page-sub{font-size:13px;color:var(--muted);line-height:1.6}

/* ── LOGOUT BTN ── */
.logout-btn{
  display:inline-flex;align-items:center;gap:6px;
  padding:8px 16px;font-size:12px;font-weight:500;
  font-family:'DM Sans',sans-serif;
  border-radius:30px;border:1px solid rgba(180,50,30,0.2);
  background:#fdf0ed;color:#7a3020;
  text-decoration:none;cursor:pointer;
  transition:opacity var(--transition);
}
.logout-btn:hover{opacity:0.8}

/* ── BANNERS ── */
.banner{
  border-radius:var(--radius-sm);padding:12px 14px;
  font-size:13px;font-weight:500;margin-bottom:18px;
  display:flex;align-items:center;gap:9px;
  border:1px solid transparent;
}
.banner-warning{background:#fef8ec;border-color:#f5c96a;color:var(--amber-text)}
.banner-success{background:#e6f4f0;border-color:var(--sage-mid);color:var(--sage-deep)}
.banner-info{background:#eef4fb;border-color:#a8c4e0;color:#2b4a6e}

/* ── GRID ── */
.grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.span2{grid-column:1 / -1}

/* ── CARD ── */
.card{
  background:var(--surface);border:1px solid var(--border);
  border-radius:var(--radius);padding:20px;
}
.card-title{
  font-size:11px;font-weight:500;text-transform:uppercase;
  letter-spacing:0.8px;color:var(--ink);margin-bottom:12px;
}

/* ── PILL TAGS ── */
.pill-row{display:flex;gap:7px;flex-wrap:wrap;margin-bottom:10px}
.pill{
  font-size:11px;font-weight:500;padding:3px 11px;
  border-radius:30px;background:var(--sand-mid);
  border:1px solid var(--sand-dark);color:var(--ink-mid);
}

/* ── RULE ITEM ── */
.rule-list{display:flex;flex-direction:column;gap:9px}
.rule-item{display:flex;align-items:flex-start;gap:9px}
.rule-dot{
  width:18px;height:18px;flex-shrink:0;border-radius:50%;
  border:1px solid var(--sand-dark);
  display:flex;align-items:center;justify-content:center;
  margin-top:1px;
}
.rule-dot-inner{width:7px;height:7px;border-radius:50%;background:var(--soft)}
.rule-text{font-size:13px;color:var(--muted);line-height:1.55;margin:0}

/* ── STEP ITEM (numbered) ── */
.step-num{
  width:18px;height:18px;flex-shrink:0;border-radius:50%;
  border:1px solid var(--sand-dark);
  display:flex;align-items:center;justify-content:center;
  margin-top:1px;font-size:10px;font-weight:500;color:var(--muted);
}

/* ── AMBER CARD ── */
.card-amber{
  background:var(--amber-bg);border:1px solid var(--amber-border);
  border-radius:var(--radius);padding:20px;
}
.card-amber .card-title{color:var(--amber-deep)}
.card-amber .rule-dot{border-color:var(--amber-border)}
.card-amber .rule-dot-inner{background:#c8921a}
.card-amber .rule-text{color:var(--amber-text)}

/* ── VERIFY BLOCK ── */
.verify-btn{
  display:inline-flex;align-items:center;gap:7px;
  padding:9px 18px;font-size:13px;font-weight:500;
  font-family:'DM Sans',sans-serif;
  border-radius:var(--radius-sm);
  background:var(--surface);border:1px solid var(--sand-dark);
  color:var(--ink);cursor:pointer;
  transition:border-color var(--transition),box-shadow var(--transition);
}
.verify-btn:hover{border-color:var(--sage-dark);box-shadow:0 0 0 3px rgba(58,122,104,0.1)}
.verify-status{
  display:none;margin-top:10px;
  padding:9px 12px;background:#e6f4f0;
  border-radius:var(--radius-sm);border:1px solid var(--sage);
  font-size:12px;font-weight:500;color:var(--sage-deep);
  align-items:center;gap:7px;
}

/* ── FOOTER ACTIONS ── */
.footer-actions{
  margin-top:20px;display:flex;
  align-items:center;justify-content:flex-end;
  flex-wrap:wrap;gap:10px;
}
.start-btn{
  display:inline-flex;align-items:center;gap:7px;
  padding:10px 22px;font-size:13px;font-weight:500;
  font-family:'DM Sans',sans-serif;
  border-radius:30px;border:none;
  background:var(--ink);color:#fff;
  cursor:pointer;text-decoration:none;
  transition:opacity var(--transition);
}
.start-btn:hover{opacity:0.85}
.start-btn:disabled,.start-btn.disabled{opacity:0.4;cursor:not-allowed;pointer-events:none}

/* ── DURING EXAM GRID ── */
.during-grid{display:grid;grid-template-columns:1fr 1fr;gap:9px}

@media(max-width:640px){
  .grid{grid-template-columns:1fr}
  .span2{grid-column:1}
  .during-grid{grid-template-columns:1fr}
  .page-title{font-size:20px}
}
</style>
</head>
<body>
<div class="wrapper">

  <!-- Back -->
  <a href="dashboard.php?ui_lang=<?= $ui_lang ?>" class="back-link">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 15 15"><path d="M9 3L4 7.5L9 12"/></svg>
    Back to dashboard
  </a>

  <!-- Brand + header -->
  <div class="brand-row">
    <img src="https://res.cloudinary.com/franklinrw/image/upload/v1755169717/kjk_bnrbmp.png" alt="Playmates">
    <span>Playmates</span>
  </div>

  <div class="page-header">
    <div>
      <div class="page-title">Before you begin 📋</div>
      <div class="page-sub">Complete identity verification and read all instructions before starting.</div>
    </div>
    <a href="logout.php" class="logout-btn">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 15 15"><path d="M9 3L14 8L9 13M14 8H4"/></svg>
      Sign out
    </a>
  </div>

  <!-- Popcorn banner -->
  <?php if ($popcornApplication): ?>
  <div class="banner banner-info">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 15 15"><circle cx="7.5" cy="7.5" r="6"/><path d="M7.5 5v3M7.5 10h.01" stroke-linecap="round"/></svg>
    Popcorn application loaded — <strong><?= htmlspecialchars($popcornApplication['applicant_name']) ?></strong> from <strong><?= htmlspecialchars($popcornApplication['school_name']) ?></strong> · code <strong><?= htmlspecialchars($popcornApplication['popcorn_code']) ?></strong>
  </div>
  <?php endif; ?>

  <!-- Verification status banner -->
  <?php if ($showVerificationBlock): ?>
  <div class="banner banner-warning">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 15 15"><path d="M7.5 1.5l6 11H1.5l6-11z" stroke-linejoin="round"/><path d="M7.5 6v3" stroke-linecap="round"/><circle cx="7.5" cy="10.5" r=".6" fill="currentColor"/></svg>
    You need to verify your identity before starting the exam.
  </div>
  <?php else: ?>
  <div class="banner banner-success">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 15 15"><circle cx="7.5" cy="7.5" r="6"/><path d="M4.5 7.5l2 2 4-4" stroke-linecap="round" stroke-linejoin="round"/></svg>
    Your identity is already verified. You can proceed with the exam.
  </div>
  <?php endif; ?>

  <!-- GRID -->
  <div class="grid">

    <!-- Identity verification (unverified only) -->
    <?php if ($showVerificationBlock): ?>
    <div class="card span2">
      <div class="card-title">Identity verification required</div>
      <p style="font-size:13px;color:var(--muted);margin-bottom:14px;line-height:1.6">
        Have a valid government-issued photo ID ready — passport, national ID, or driver's licence. Your ID will be scanned and matched against your registered profile.
      </p>
      <div class="rule-list" style="margin-bottom:18px">
        <div class="rule-item">
          <div class="step-num">1</div>
          <p class="rule-text">Click <strong>Verify ID</strong> and allow camera access when prompted.</p>
        </div>
        <div class="rule-item">
          <div class="step-num">2</div>
          <p class="rule-text">Hold your ID flat and visible in front of the camera — front side first.</p>
        </div>
        <div class="rule-item">
          <div class="step-num">3</div>
          <p class="rule-text">Take a quick selfie so your face can be matched to the ID photo.</p>
        </div>
      </div>
      <button class="verify-btn" id="verifyBtn" onclick="handleVerify()">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 14 14"><rect x="1.5" y="3" width="11" height="8" rx="1.5"/><circle cx="5" cy="6.5" r="1.2"/><path d="M8 5.5h2M8 7.5h2" stroke-linecap="round"/></svg>
        Verify ID
      </button>
      <div class="verify-status" id="verifyStatus">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 14 14"><circle cx="7" cy="7" r="5.5"/><path d="M4.5 7l1.5 1.5L9.5 5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Identity verified successfully.
      </div>
    </div>
    <?php endif; ?>

    <!-- Exam overview -->
    <div class="card">
      <div class="card-title">Exam overview</div>
      <div class="pill-row">
        <span class="pill">40 questions</span>
        <span class="pill">60 minutes</span>
        <span class="pill">Multiple choice</span>
      </div>
      <p style="font-size:13px;color:var(--muted);line-height:1.6">Your progress is saved automatically. The timer begins as soon as you start.</p>
    </div>

    <!-- Rules & conduct -->
    <div class="card">
      <div class="card-title">Rules &amp; conduct</div>
      <div class="rule-list">
        <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text">Each question may only be answered once. Review before submitting.</p></div>
        <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text">Passing score is 70%. Results are shown immediately after.</p></div>
        <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text">Retakes allowed after a 7-day waiting period.</p></div>
      </div>
    </div>

    <!-- During the exam -->
    <div class="card span2">
      <div class="card-title">During the exam</div>
      <div class="during-grid">
        <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text">Leaving the exam screen is not allowed.</p></div>
        <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text">Your face must be clearly visible at all times.</p></div>
        <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text">You must be alone — no other people in the room.</p></div>
        <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text">No headphones or earphones allowed.</p></div>
        <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text">No notes, books, or phones permitted.</p></div>
        <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text">No talking to anyone during the exam.</p></div>
      </div>
    </div>

    <!-- Behaviour rules -->
    <div class="card">
      <div class="card-title">Behaviour rules</div>
      <div class="rule-list">
        <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text">Don't look away from the screen too much.</p></div>
        <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text">Don't cover your face or block the camera.</p></div>
        <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text">Don't leave your seat during the exam.</p></div>
        <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text">Don't use another device while testing.</p></div>
      </div>
    </div>

    <!-- If you break the rules -->
    <div class="card-amber">
      <div class="card-title" style="display:flex;align-items:center;gap:7px">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 15 15"><path d="M7.5 1.5l6 11H1.5l6-11z" stroke-linejoin="round"/><path d="M7.5 6v3" stroke-linecap="round"/><circle cx="7.5" cy="10.5" r=".6" fill="currentColor"/></svg>
        If you break the rules
      </div>
      <p style="font-size:13px;color:var(--amber-text);margin-bottom:12px;line-height:1.6">Violations are monitored automatically. Breaking any rule may result in:</p>
      <div class="rule-list">
        <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text">Your exam being cancelled immediately.</p></div>
        <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text">Your result not being certified.</p></div>
      </div>
    </div>

    <!-- Academic integrity -->
    <div class="card span2">
      <div class="card-title">Academic integrity</div>
      <p style="font-size:13px;color:var(--muted);line-height:1.6">By starting the exam, you confirm this is entirely your own work. Any form of academic dishonesty will result in disqualification and may be reported to your institution.</p>
    </div>

  </div><!-- /grid -->

  <!-- Footer actions -->
  <div class="footer-actions">
    <?php if ($isVerified && $attempt_id > 0): ?>
      <a href="javascript:void(0)"
         onclick="startExamWithParams(<?= $attempt_id ?>, '<?= urlencode($ui_lang) ?>'); return false;"
         class="start-btn">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 15 15"><path d="M3 7.5h9M8.5 4l3.5 3.5L8.5 11" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Begin exam
      </a>
    <?php else: ?>
      <button id="startBtn"
              class="start-btn <?= $showVerificationBlock ? 'disabled' : '' ?>"
              <?= $showVerificationBlock ? 'disabled' : '' ?>
              onclick="handleStart()">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 15 15"><path d="M3 7.5h9M8.5 4l3.5 3.5L8.5 11" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Begin exam
      </button>
    <?php endif; ?>
  </div>

</div><!-- /wrapper -->

<script>
let verified              = <?= $isVerified ? 'true' : 'false' ?>;
let showVerificationBlock = <?= $showVerificationBlock ? 'true' : 'false' ?>;
let phpAttemptId          = <?= $attempt_id ?>;
let phpUiLang             = '<?= $ui_lang ?>';
let phpExamPair           = <?= json_encode($exam_params['pair']      ?? ($_GET['pair']      ?? 'en-rw')) ?>;
let phpExamDirection      = <?= json_encode($exam_params['direction'] ?? ($_GET['direction'] ?? 'en')) ?>;
let phpExamLevel          = <?= json_encode($exam_params['level']     ?? (int)($_GET['level'] ?? 1)) ?>;

function handleVerify() {
  if (!showVerificationBlock) return;
  const urlParams = new URLSearchParams(window.location.search);
  const attemptId = urlParams.get('attempt_id');
  window.location.href = 'verify.php' + (attemptId ? '?attempt_id=' + attemptId : '');
}

function startExamWithParams(attemptId, uiLang) {
  if (!attemptId || attemptId <= 0) { alert('Cannot start exam: no valid attempt ID found.'); return false; }
  fetch('../backend/start-exam.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ attempt_id: attemptId })
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      window.location.href = 'lessonexam.php?exam=1&attempt_id=' + attemptId
        + '&ui_lang=' + uiLang
        + '&pair='      + encodeURIComponent(phpExamPair)
        + '&direction=' + encodeURIComponent(phpExamDirection)
        + '&level='     + encodeURIComponent(phpExamLevel);
    } else {
      alert('Error starting exam: ' + (data.message || 'Unknown error'));
    }
  })
  .catch(() => alert('Error starting exam. Please try again.'));
  return false;
}

function handleStart() {
  const ready = !showVerificationBlock || verified;
  if (!ready) return;

  let attemptId = phpAttemptId;
  if (!attemptId || attemptId <= 0) {
    attemptId = new URLSearchParams(window.location.search).get('attempt_id');
  }

  if (attemptId && attemptId > 0) {
    fetch('../backend/start-exam.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ attempt_id: attemptId })
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        window.location.href = 'lessonexam.php?exam=1&attempt_id=' + attemptId
          + '&ui_lang='   + phpUiLang
          + '&pair='      + encodeURIComponent(phpExamPair)
          + '&direction=' + encodeURIComponent(phpExamDirection)
          + '&level='     + encodeURIComponent(phpExamLevel);
      } else {
        alert('Error starting exam: ' + (data.message || 'Unknown error'));
      }
    })
    .catch(() => alert('Error starting exam. Please try again.'));
  } else {
    const event = new CustomEvent('examStartTrigger', { detail: { message: 'Identity verified and rules accepted. I am ready to start the exam.' } });
    window.dispatchEvent(event);
  }
}

document.addEventListener('DOMContentLoaded', function () {
  const startBtn = document.getElementById('startBtn');
  if (startBtn && !showVerificationBlock) {
    startBtn.disabled = false;
    startBtn.classList.remove('disabled');
  }
});
</script>
</body>
</html>