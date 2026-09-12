<?php
require_once '../backend/config.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'] ?? '/frontend/apply-popcorn.php';
    header('Location: signin.php');
    exit;
}

$ui_lang = $_GET['ui_lang'] ?? 'en';
?>
<!DOCTYPE html>
<html lang="<?= $ui_lang ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Apply for Popcorn · Playmates</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons/css/flag-icons.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{
  --sand:#f5ede6;--sand-mid:#ede3d8;--sand-dark:#ddd0c2;
  --sage:#c6dfd5;--sage-mid:#8bbfad;--sage-dark:#3a7a68;--sage-deep:#225548;
  --lavender:#d4cfed;--butter:#f5e8b0;--peach:#f5d6c4;--blush:#f9d8cd;
  --ink:#1a1a18;--ink-mid:#4a4845;--muted:#8a8178;--soft:#b8b0a5;
  --surface:#faf8f5;--border:rgba(0,0,0,0.07);
  --radius-sm:8px;--radius:14px;--radius-lg:20px;
  --transition:0.25s cubic-bezier(.4,0,.2,1);
}
html,body{
  min-height:100vh;
  font-family:'DM Sans',sans-serif;
  font-size:14px;
  color:var(--ink);
  background:var(--sand);
  display:flex;
  align-items:center;
  justify-content:center;
  padding:24px;
}

/* ── BACK LINK ── */
.back-link{
  display:inline-flex;
  align-items:center;
  gap:6px;
  font-size:12px;
  font-weight:500;
  color:var(--muted);
  text-decoration:none;
  margin-bottom:20px;
  transition:color var(--transition);
}
.back-link:hover{color:var(--sage-dark)}
.back-link svg{flex-shrink:0}

/* ── WRAPPER ── */
.wrapper{width:100%;max-width:520px}

/* ── CARD ── */
.card{
  background:var(--surface);
  border:1px solid var(--border);
  border-radius:var(--radius-lg);
  padding:32px;
  box-shadow:0 8px 40px rgba(26,26,24,0.07);
}

/* ── HEADER ── */
.card-header{margin-bottom:24px}
.brand-row{display:flex;align-items:center;gap:8px;margin-bottom:16px}
.brand-row img{width:28px;height:28px;border-radius:7px;object-fit:contain}
.brand-row span{font-family:'DM Serif Display',serif;font-size:16px;color:var(--sage-deep)}
.card-title{font-family:'DM Serif Display',serif;font-size:26px;color:var(--ink);letter-spacing:-0.3px;margin-bottom:6px}
.card-sub{font-size:13px;color:var(--muted);line-height:1.7}

/* ── DIVIDER ── */
.divider{height:1px;background:var(--border);margin:22px 0}

/* ── FORM ── */
.form-group{display:grid;gap:7px;margin-bottom:16px}
.form-group:last-of-type{margin-bottom:20px}
label{font-size:11px;font-weight:500;text-transform:uppercase;letter-spacing:0.8px;color:var(--ink-mid)}
input,select{
  width:100%;
  border:1px solid var(--sand-dark);
  border-radius:var(--radius-sm);
  padding:11px 14px;
  font-size:14px;
  font-family:'DM Sans',sans-serif;
  background:#fff;
  color:var(--ink);
  transition:border-color var(--transition),box-shadow var(--transition);
  outline:none;
  appearance:none;
  -webkit-appearance:none;
}
input::placeholder{color:var(--soft)}
input:focus,select:focus{
  border-color:var(--sage-dark);
  box-shadow:0 0 0 3px rgba(58,122,104,0.12);
}
.select-wrap{position:relative}
.select-wrap select{padding-right:34px}
.select-wrap::after{
  content:'';
  pointer-events:none;
  position:absolute;
  right:13px;
  top:50%;
  transform:translateY(-50%);
  width:0;height:0;
  border-left:4px solid transparent;
  border-right:4px solid transparent;
  border-top:5px solid var(--muted);
}

/* ── SUBMIT BTN ── */
.submit-btn{
  width:100%;
  padding:13px 16px;
  border:none;
  border-radius:var(--radius-sm);
  background:var(--ink);
  color:#fff;
  font-size:14px;
  font-weight:500;
  font-family:'DM Sans',sans-serif;
  cursor:pointer;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  gap:7px;
  transition:opacity var(--transition),transform var(--transition);
}
.submit-btn:hover{opacity:0.85}
.submit-btn:active{transform:scale(0.99)}
.submit-btn:disabled{opacity:0.55;cursor:not-allowed}

/* ── ALERT ── */
.alert{
  margin-top:16px;
  padding:12px 14px;
  border-radius:var(--radius-sm);
  font-size:13px;
  font-weight:500;
  display:none;
  border:1px solid transparent;
}
.alert.success{background:#e6f4f0;color:var(--sage-deep);border-color:var(--sage)}
.alert.error{background:#fdf0ed;color:#7a3020;border-color:var(--peach)}

/* ── NOTE ── */
.form-note{
  margin-top:14px;
  font-size:11px;
  color:var(--muted);
  text-align:center;
  line-height:1.7;
}

@media(max-width:480px){
  body{padding:16px}
  .card{padding:22px 18px}
  .card-title{font-size:22px}
}
</style>
</head>
<body>
<div class="wrapper">
  <!-- Back link -->
  <a href="dashboard.php?ui_lang=<?= $ui_lang ?>" class="back-link">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 15 15"><path d="M9 3L4 7.5L9 12"/></svg>
    Back to dashboard
  </a>

  <div class="card">
    <!-- Header -->
    <div class="card-header">
      <div class="brand-row">
        <img src="https://res.cloudinary.com/franklinrw/image/upload/v1755169717/kjk_bnrbmp.png" alt="Playmates">
        <span>Playmates</span>
      </div>
      <div class="card-title">Apply for Popcorn 🍿</div>
      <div class="card-sub">Enter your Popcorn code, school name, and applicant details to continue. You'll be redirected to the exam once your application is verified.</div>
    </div>

    <div class="divider"></div>

    <!-- Form -->
    <div class="form-group">
      <label for="popcornCode">Popcorn code</label>
      <input id="popcornCode" type="text" placeholder="Enter your popcorn code" autocomplete="off">
    </div>

    <div class="form-group">
      <label for="applicantName">Applicant name</label>
      <input id="applicantName" type="text" placeholder="Your full name">
    </div>

    <div class="form-group">
      <label for="schoolName">School name</label>
      <input id="schoolName" type="text" placeholder="School or institution name">
    </div>

    <div class="form-group">
      <label>Selected exam</label>
      <div id="selectedExamLabel" style="padding:10px 12px;border:1px solid var(--sand-dark);border-radius:8px;background:#fff;color:var(--ink);">Not selected</div>
      <input type="hidden" id="examType" value="">
      <div style="margin-top:10px;font-size:13px;color:var(--muted)">
        <a href="#" id="skipSuggestionsLink" style="color:var(--sage-dark);text-decoration:none;font-weight:600">Skip this page</a>
      </div>
    </div>

    <button class="submit-btn" id="applyBtn">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 15 15"><path d="M7.5 1.5l1.8 5h5l-4 3 1.5 5-4.3-3.1L3.2 14.5l1.5-5-4-3h5z"/></svg>
      Take Exam
    </button>

    <div id="message" class="alert"></div>

    <p class="form-note">Your application is saved securely.<br>Select your exam type and skip suggestions if needed.</p>
  </div>
</div>

<script>
const applyBtn       = document.getElementById('applyBtn');
const popcornCode    = document.getElementById('popcornCode');
const applicantName  = document.getElementById('applicantName');
const schoolName     = document.getElementById('schoolName');
const message        = document.getElementById('message');
const examTypeInput   = document.getElementById('examType');
const selectedExamLabel = document.getElementById('selectedExamLabel');
const skipSuggestionsLink = document.getElementById('skipSuggestionsLink');

// Read exam selection from querystring (exam=academic|business)
const qs = new URLSearchParams(window.location.search);
const examParam = (qs.get('exam') || '').toLowerCase();
if (examParam) {
  examTypeInput.value = examParam;
  const label = examParam === 'academic' ? 'Academic English' : (examParam === 'business' ? 'Business English' : examParam);
  selectedExamLabel.textContent = label;
} else {
  selectedExamLabel.textContent = 'Not selected';
}

skipSuggestionsLink.addEventListener('click', event => {
  event.preventDefault();
  const examTypeValue = examTypeInput.value || examParam;
  const qs = new URLSearchParams();
  if (examTypeValue) {
    qs.set('exam_type', examTypeValue);
  }
  const uiLang = (new URLSearchParams(window.location.search)).get('ui_lang');
  if (uiLang) {
    qs.set('ui_lang', uiLang);
  }
  window.location.href = 'onboard.php' + (qs.toString() ? '?' + qs.toString() : '');
});

function showMessage(text, success = true) {
  message.textContent = text;
  message.className   = 'alert ' + (success ? 'success' : 'error');
  message.style.display = 'block';
}

applyBtn.addEventListener('click', async () => {
  const code           = popcornCode.value.trim();
  const name           = applicantName.value.trim();
  const school         = schoolName.value.trim();
  const examTypeValue  = examTypeInput.value || examParam;

  if (!code || !name || !school || !examTypeValue) {
    showMessage('Please fill in all fields and select an exam type.', false);
    return;
  }

  applyBtn.disabled    = true;
  applyBtn.innerHTML   = '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 15 15"><circle cx="7.5" cy="7.5" r="6" stroke-dasharray="30" stroke-dashoffset="10" style="animation:spin 0.8s linear infinite"/></svg> Applying…';
  showMessage('Saving your application…', true);

  try {
    const res  = await fetch('../backend/apply-popcorn.php', {
      method : 'POST',
      headers: { 'Content-Type': 'application/json' },
      body   : JSON.stringify({
        popcorn_code: code,
        applicant_name: name,
        school_name: school,
        exam_type: examTypeValue
      })
    });
    const data = await res.json();

    if (!data.success) {
      showMessage(data.message || 'Unable to apply. Please try again.', false);
      applyBtn.disabled  = false;
      applyBtn.innerHTML = '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 15 15"><path d="M7.5 1.5l1.8 5h5l-4 3 1.5 5-4.3-3.1L3.2 14.5l1.5-5-4-3h5z"/></svg> Take Exam';
      return;
    }

    showMessage('Application saved! Redirecting to your exam…', true);
    setTimeout(() => { window.location.href = data.data.redirect; }, 800);
  } catch (err) {
    showMessage('A network error occurred. Please try again.', false);
    applyBtn.disabled  = false;
    applyBtn.innerHTML = '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 15 15"><path d="M7.5 1.5l1.8 5h5l-4 3 1.5 5-4.3-3.1L3.2 14.5l1.5-5-4-3h5z"/></svg> Take Exam';
  }
});

/* Spin animation for loading icon */
const style = document.createElement('style');
style.textContent = '@keyframes spin{to{stroke-dashoffset:0}}';
document.head.appendChild(style);
</script>
</body>
</html>