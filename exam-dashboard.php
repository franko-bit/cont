<?php
require_once __DIR__ . '/backend/config.php';

if (empty($_SESSION['user_id'])) {
    $redirectPath = '/language-platform/exam-dashboard.php';
    header('Location: ' . SITE_URL . '/frontend/signin.php?redirect_to=' . urlencode($redirectPath));
    exit;
}

// Commented out institution check for testing purposes.
// if (empty($_SESSION['institution_id'])) {
//     header('Location: ' . SITE_URL . '/frontend/dashboard.php');
//     exit;
// }

$institutionId = isset($_SESSION['institution_id']) ? (int)$_SESSION['institution_id'] : 0;

function ensureColumnExists(PDO $pdo, string $table, string $column, string $definition) {
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE :column");
    $stmt->execute(['column' => $column]);
    if ($stmt->rowCount() === 0) {
        $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
    }
}

try {
    $pdo = getPDO();
    ensureColumnExists($pdo, 'institution_assessments', 'exam_language', "VARCHAR(32) DEFAULT 'English'");
    ensureColumnExists($pdo, 'institution_assessments', 'exam_title', "VARCHAR(255) DEFAULT ''");
    ensureColumnExists($pdo, 'institution_assessments', 'exam_pass_score', "INT DEFAULT 70");
    ensureColumnExists($pdo, 'institution_assessments', 'exam_duration', "INT DEFAULT 30");
    ensureColumnExists($pdo, 'institution_assessments', 'exam_topic', "VARCHAR(255) DEFAULT ''");

    $stmt = $pdo->prepare('SELECT * FROM institution_assessments WHERE institution_id = :institution_id ORDER BY created_at DESC');
    $stmt->execute(['institution_id' => $institutionId]);
    $exams = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare('SELECT * FROM institution_candidates WHERE institution_id = :institution_id ORDER BY created_at DESC');
    $stmt->execute(['institution_id' => $institutionId]);
    $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare('SELECT DISTINCT exam_language FROM institution_assessments WHERE institution_id = :institution_id');
    $stmt->execute(['institution_id' => $institutionId]);
    $languages = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    error_log('Exam dashboard error: ' . $e->getMessage());
    $exams = [];
    $candidates = [];
    $languages = [];
}

function escapeJson($value) {
    return json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Exam Dashboard</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Serif+Display:ital@0;1&family=DM+Mono:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
:root{
  color-scheme:light;
  color:#090f1a;
  background:#f4f6fb;
  font-family:'DM Sans',sans-serif;
  font-size:14px;
  line-height:1.5;
  --ink:#111827;
  --soft:#6b7280;
  --muted:#4b5563;
  --surface:#ffffff;
  --surface-alt:#f9fafb;
  --border:#e5e7eb;
  --border-soft:#f3f4f6;
  --radius:20px;
  --radius-lg:24px;
  --radius-sm:14px;
  --blue:#2563eb;
  --blue-dark:#1d4ed8;
  --sage-dark:#047857;
  --sage:#10b981;
  --sage-mid:#6ee7b7;
  --green-bg:#ecfdf5;
  --green-border:#a7f3d0;
  --red:#ef4444;
  --purple:#7c3aed;
  --purple-border:#c4b5fd;
  --sand:#f8fafc;
  --sand-dark:#e2e8f0;
  --sand-mid:#f3f4f6;
  --yellow:#f59e0b;
}
*{box-sizing:border-box}
body{margin:0;background:var(--surface-alt);color:var(--ink)}
button,select,input{font:inherit}
button{cursor:pointer}
a{text-decoration:none;color:inherit}
.dash{display:grid;grid-template-columns:320px 1fr;min-height:100vh;gap:24px;padding:24px;}
.sidebar{display:flex;flex-direction:column;gap:24px;background:linear-gradient(180deg,#ffffff 0%,#f8fafc 100%);padding:24px;border:1px solid var(--border);border-radius:32px;position:sticky;top:24px;align-self:flex-start;}
.brand{display:flex;flex-direction:column;gap:8px}
.brand-name{display:flex;align-items:center;gap:10px;font-size:19px;font-weight:700;letter-spacing:-.03em}
.brand-name img{width:36px;height:36px;border-radius:14px;object-fit:contain}
.brand-sub{font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:1px}
.inst-pill{background:var(--surface);border:1px solid var(--border);border-radius:24px;padding:16px}
.inst-label{font-size:10px;text-transform:uppercase;letter-spacing:1px;color:var(--muted);margin-bottom:8px}
.inst-name{font-size:16px;font-weight:700;color:var(--ink);margin-bottom:4px}
.inst-model{font-size:12px;color:var(--soft)}
.nav{display:flex;flex-direction:column;gap:8px}
.nav-section{font-size:11px;text-transform:uppercase;letter-spacing:1px;color:var(--muted);padding:8px 0}
.nav-item{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:14px 16px;border:1px solid transparent;border-radius:18px;background:transparent;color:var(--muted);font-weight:600;transition:all .14s}
.nav-item:hover{background:var(--sand);color:var(--ink)}
.nav-item.active{background:var(--blue);color:#fff;border-color:transparent}
.nav-icon{font-size:18px}
.nav-badge{font-size:11px;background:var(--surface);color:var(--muted);padding:2px 10px;border-radius:999px}
.sidebar-bottom{display:grid;gap:12px;padding:18px;background:var(--surface);border:1px solid var(--border);border-radius:24px}
.sidebar-score-label{font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.9px}
.pass-bar{background:var(--sand-dark);height:8px;border-radius:999px;overflow:hidden}
.pass-fill{height:100%;background:linear-gradient(90deg,#10b981,#047857)}
.pass-stats{display:flex;justify-content:space-between;font-size:12px;color:var(--muted)}
.main{display:flex;flex-direction:column;gap:24px}
.page-wrap{display:none}
.page-wrap.active{display:block}
.topbar{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;padding:24px 0}
.page-title{font-size:28px;font-weight:700;line-height:1.05}
.page-sub{font-size:13px;color:var(--muted);margin-top:8px;max-width:620px}
.topbar-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.btn{font-weight:700;border:none;border-radius:999px;display:inline-flex;align-items:center;gap:8px;justify-content:center;padding:12px 18px;transition:all .15s}
.btn-primary{background:var(--blue);color:#fff}
.btn-primary:hover{background:var(--blue-dark)}
.btn-outline{background:transparent;color:var(--ink);border:1px solid var(--border)}
.btn-ghost{background:transparent;color:var(--blue);border:1px solid var(--border);padding:8px 14px}
.btn-sm{font-size:12px;padding:10px 14px}
.card{background:var(--surface);border:1px solid var(--border);border-radius:28px;overflow:hidden;}
.card-head{display:flex;align-items:center;justify-content:space-between;padding:20px 24px;border-bottom:1px solid var(--border)}
.card-title{font-size:15px;font-weight:700}
.card-sub{font-size:12px;color:var(--muted);margin-top:6px}
.metrics{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}
.metric-card{background:var(--surface);border:1px solid var(--border);border-radius:24px;padding:20px;display:flex;flex-direction:column;gap:10px}
.metric-label{font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:1px}
.metric-value{font-size:28px;font-weight:700}
.grid2{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px}
.section-sep{height:1px;background:var(--border);margin:18px 0}
.page-tabs-wrap{display:flex;gap:10px;flex-wrap:wrap;padding:18px 24px 0}
.tab-pill{padding:9px 16px;border-radius:999px;border:1px solid var(--border);background:var(--surface);font-size:12px;font-weight:600;color:var(--muted);cursor:pointer}
.tab-pill.active{background:var(--blue);border-color:var(--blue);color:#fff}
.setting-select,.form-input{width:100%;border:1px solid var(--border);border-radius:16px;padding:12px 14px;background:var(--surface);font-size:13px;color:var(--ink)}
.modal-overlay{position:fixed;inset:0;background:rgba(15,23,42,.35);backdrop-filter:blur(2px);display:none;align-items:center;justify-content:center;padding:24px;z-index:50}
.modal-overlay.show{display:flex}
.modal-box{background:var(--surface);border-radius:32px;max-width:680px;width:100%;overflow:hidden;box-shadow:0 32px 80px rgba(15,23,42,.14)}
.modal-head{display:flex;align-items:center;justify-content:space-between;padding:24px;border-bottom:1px solid var(--border)}
.modal-title{font-size:20px;font-weight:700}
.modal-sub{font-size:13px;color:var(--muted);padding:0 24px 16px}
.modal-content{padding:0 24px 24px;display:grid;gap:18px}
.form-label{font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:8px}
.form-row{display:grid;gap:12px}
.modal-footer{display:flex;align-items:center;justify-content:flex-end;gap:10px;padding:20px 24px;border-top:1px solid var(--border)}
.modal-close{background:transparent;border:none;font-size:20px;color:var(--muted);cursor:pointer}
.hw-card{background:var(--surface);border:1px solid var(--border);border-radius:24px;overflow:hidden;display:flex;flex-direction:column;min-height:200px}
.hw-card-top{padding:24px;display:flex;align-items:flex-start;justify-content:space-between;gap:16px}
.hw-card-main{padding:0 24px 24px;display:flex;flex-direction:column;gap:12px}
.hw-tag{display:inline-flex;align-items:center;gap:8px;padding:7px 14px;border-radius:999px;font-size:11px;font-weight:700;background:var(--sand);color:var(--muted)}
.hw-title{font-size:18px;font-weight:700;line-height:1.1}
.hw-meta{font-size:12px;color:var(--muted)}
.hw-divider{height:1px;background:var(--border);margin:0}
.hw-progress-row{display:flex;align-items:center;gap:10px}
.hw-progress-bar{flex:1;height:6px;background:var(--sand-dark);border-radius:999px;overflow:hidden}
.hw-progress-fill{height:100%;background:var(--blue);border-radius:999px;transition:width .35s}
.hw-progress-pct{font-size:11px;font-weight:700;color:var(--muted);min-width:34px;text-align:right}
.hw-card-footer{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:18px 24px 24px}
.hw-footer-stats{display:flex;gap:16px;align-items:flex-end;flex-wrap:wrap}
.hw-stat-val{font-size:18px;font-weight:700}
.hw-stat-lbl{font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:1px}
.hw-copy-btn{width:36px;height:36px;border-radius:50%;border:1px solid var(--border);background:transparent;display:flex;align-items:center;justify-content:center;color:var(--muted);font-size:15px}
.hw-copy-btn:hover{background:var(--sand-mid)}
.hw-cta-btn{background:var(--blue);color:#fff;padding:11px 20px;border:none;border-radius:999px;font-weight:700}
.hw-cta-btn:hover{background:var(--blue-dark)}
.cand-row{display:flex;align-items:center;gap:12px;padding:16px 24px;border-bottom:1px solid var(--border)}
.cand-row:last-child{border-bottom:none}
.cand-info{flex:1;min-width:0}
.cand-name{font-size:13px;font-weight:700;color:var(--ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.cand-meta{font-size:11px;color:var(--muted);margin-top:5px}
.view-btn{background:var(--sage);color:#fff;border:none;border-radius:999px;padding:8px 16px;font-size:11px;font-weight:700}
.view-btn:hover{background:#059669}
.skill-result-row{display:flex;align-items:center;gap:10px;margin-bottom:10px}
.skill-result-name{font-size:12px;font-weight:700;color:var(--ink);width:90px;flex-shrink:0}
.skill-result-bar-wrap{flex:1;height:6px;background:var(--sand-dark);border-radius:999px;overflow:hidden}
.skill-result-fill{height:100%;border-radius:999px;background:var(--blue)}
.skill-result-pct{font-size:11px;font-weight:700;min-width:34px;text-align:right}
.success-banner{background:var(--green-bg);border:1px solid var(--green-border);border-radius:20px;padding:14px 18px;font-size:13px;color:var(--sage-dark);display:none}
.success-banner.show{display:block}
::-webkit-scrollbar{width:6px}
::-webkit-scrollbar-track{background:transparent}
::-webkit-scrollbar-thumb{background:var(--sand-dark);border-radius:999px}
</style>
</head>
<body>
<div class="dash">
  <aside class="sidebar">
    <div class="brand">
      <div class="brand-name">
        <img src="https://res.cloudinary.com/franklinrw/image/upload/v1755169717/kjk_bnrbmp.png" alt="logo">
        Playmates
      </div>
      <div class="brand-sub">Institution Hub</div>
    </div>
    <div class="inst-pill">
      <div class="inst-label">Current institution</div>
      <div class="inst-name">Central University</div>
      <div class="inst-model">Exam management · v1</div>
    </div>
    <nav class="nav">
      <div class="nav-section">Manage</div>
      <button class="nav-item active" data-tab="dashboard">
        <span class="nav-icon">📊</span> Dashboard
        <span class="nav-badge" id="badge-exams">0</span>
      </button>
      <button class="nav-item" data-tab="exams">
        <span class="nav-icon">📝</span> Exams
        <span class="nav-badge" id="badge-exam-count">0</span>
      </button>
      <button class="nav-item" data-tab="candidates">
        <span class="nav-icon">👥</span> Candidates
        <span class="nav-badge" id="badge-candidates">0</span>
      </button>
      <div class="nav-section">Reports</div>
      <button class="nav-item" data-tab="results">
        <span class="nav-icon">🏆</span> Results
      </button>
      <div class="nav-section">Admin</div>
      <button class="nav-item" data-tab="settings">
        <span class="nav-icon">⚙️</span> Settings
      </button>
    </nav>
    <div class="sidebar-bottom">
      <div class="sidebar-score-label">Pass rate this cycle</div>
      <div class="pass-bar"><div class="pass-fill" id="pass-fill-bar" style="width:0%"></div></div>
      <div class="pass-stats">
        <span id="sidebar-completed-label">— completed</span>
        <span id="sidebar-pass-pct">—%</span>
      </div>
    </div>
  </aside>
  <main class="main">
    <div id="tab-dashboard" class="tab-content page-wrap active">
      <div class="topbar">
        <div>
          <div class="page-title">Exam dashboard</div>
          <div class="page-sub">Central University · Manage exam sessions, candidate invites, and results.</div>
        </div>
        <div class="topbar-actions">
          <button class="btn btn-outline btn-sm" id="exportDashBtn">⬇ Export report</button>
          <button class="btn btn-primary" id="quickCreateBtn">＋ Create exam</button>
        </div>
      </div>
      <div class="metrics" id="dash-metrics"></div>
      <div class="grid2">
        <div class="card">
          <div class="card-head">
            <div><div class="card-title">Active exam links</div><div class="card-sub">Share with candidates to begin.</div></div>
            <button class="btn-ghost btn-sm" data-goto="exams">View all</button>
          </div>
          <div id="dash-links"></div>
        </div>
        <div class="card">
          <div class="card-head">
            <div><div class="card-title">Recent completions</div><div class="card-sub">Latest submitted exam sessions.</div></div>
            <button class="btn-ghost btn-sm" data-goto="results">View all</button>
          </div>
          <div id="dash-recent"></div>
        </div>
      </div>
      <div class="grid2">
        <div class="card">
          <div class="card-head">
            <div><div class="card-title">Top candidates</div><div class="card-sub">Ranked by latest score.</div></div>
            <span class="accent-pill pill-butter">This cycle</span>
          </div>
          <div id="dash-top"></div>
        </div>
        <div class="card">
          <div class="card-head">
            <div><div class="card-title">Score distribution</div><div class="card-sub">Completed exam results breakdown.</div></div>
          </div>
          <div id="dash-dist"></div>
          <div class="section-sep"></div>
          <div style="display:flex;gap:10px;flex-wrap:wrap">
            <div style="font-size:11px;color:var(--muted)"><span style="display:inline-block;width:8px;height:8px;background:var(--sage-dark);border-radius:2px;margin-right:4px"></span>Pass (≥70%)</div>
            <div style="font-size:11px;color:var(--muted)"><span style="display:inline-block;width:8px;height:8px;background:var(--red);border-radius:2px;margin-right:4px"></span>Fail (&lt;70%)</div>
          </div>
        </div>
      </div>
    </div>
    <div id="tab-exams" class="tab-content page-wrap">
      <div class="topbar">
        <div>
          <div class="page-title">Exams</div>
          <div class="page-sub">Create exams, manage exam metadata, and share invite links.</div>
        </div>
        <div class="topbar-actions">
          <button class="btn btn-outline btn-sm" id="downloadLinksBtn">⬇ Download all links</button>
          <button class="btn btn-primary" id="openCreateModalBtn">＋ New exam</button>
        </div>
      </div>
      <div class="success-banner" id="examCreatedBanner">  Exam created successfully! Invite link is ready to share.</div>
      <div id="exams-list"></div>
    </div>
    <div id="tab-candidates" class="tab-content page-wrap">
      <div class="topbar">
        <div>
          <div class="page-title">Candidates</div>
          <div class="page-sub">All invited candidates and their current exam status.</div>
        </div>
        <div class="topbar-actions">
          <button class="btn btn-outline btn-sm" id="bulkInviteBtn">✉ Resend invites</button>
          <button class="btn btn-primary" id="openCandidatesModalBtn">＋ Invite candidates</button>
        </div>
      </div>
      <div class="card">
        <div class="page-tabs-wrap" id="cand-filter-tabs"></div>
        <div id="candidates-table"></div>
      </div>
    </div>
    <div id="tab-results" class="tab-content page-wrap">
      <div class="topbar">
        <div>
          <div class="page-title">Exam results</div>
          <div class="page-sub">Review scores, completion rates, and candidate insights.</div>
        </div>
        <div class="topbar-actions">
          <button class="btn btn-outline btn-sm" id="exportResultsBtn">📎 Export CSV</button>
        </div>
      </div>
      <div class="metrics" style="grid-template-columns:repeat(3,1fr)" id="results-metrics"></div>
      <div class="card">
        <div class="card-head">
          <div><div class="card-title">All results</div><div class="card-sub">Track exam outcomes across your institution.</div></div>
          <select class="setting-select" id="results-filter" style="font-size:11px;padding:8px 12px">
            <option value="all">All exams</option>
          </select>
        </div>
        <div id="results-table"></div>
      </div>
    </div>
    <div id="tab-settings" class="tab-content page-wrap">
      <div class="topbar">
        <div>
          <div class="page-title">Institution settings</div>
          <div class="page-sub">Configure exam defaults and candidate notifications.</div>
        </div>
        <div class="topbar-actions">
          <button class="btn btn-primary" id="saveSettingsBtn">Save preferences</button>
        </div>
      </div>
      <div class="grid2">
        <div class="card">
          <div class="card-head"><div class="card-title">Exam defaults</div></div>
          <div class="modal-content">
            <div class="form-row"><label class="form-label">Default passing score</label><input class="form-input" type="number" value="70" min="0" max="100"></div>
            <div class="form-row"><label class="form-label">Default duration (min)</label><input class="form-input" type="number" value="30" min="1" max="180"></div>
            <div class="form-row"><label class="form-label">Max exam attempts</label><input class="form-input" type="number" value="2" min="1" max="10"></div>
          </div>
        </div>
        <div class="card">
          <div class="card-head"><div class="card-title">Notifications</div></div>
          <div class="modal-content">
            <div class="form-row"><div><div class="form-label">Email on completion</div></div><button class="toggle" onclick="this.querySelector('.toggle-track').classList.toggle('on')"><div class="toggle-track on"><div class="toggle-thumb"></div></div></button></div>
            <div class="form-row"><div><div class="form-label">Weekly digest</div></div><button class="toggle" onclick="this.querySelector('.toggle-track').classList.toggle('on')"><div class="toggle-track on"><div class="toggle-thumb"></div></div></button></div>
            <div class="form-row"><div><div class="form-label">Notify on failure</div></div><button class="toggle" onclick="this.querySelector('.toggle-track').classList.toggle('on')"><div class="toggle-track"><div class="toggle-thumb"></div></div></button></div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>
<div class="modal-overlay" id="examModal">
  <div class="modal-box">
    <div class="modal-head">
      <div><div class="modal-title">Create new exam</div></div>
      <button class="modal-close" id="closeExamModal">✕</button>
    </div>
    <div class="modal-sub">Choose a language focus, pick a topic from the exam content, then share the exam invite link.</div>
    <div class="modal-content">
      <label class="form-label">Exam title</label>
      <input class="form-input" id="examTitle" type="text" placeholder="e.g. Beginner English assessment">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
        <div>
          <label class="form-label">Exam language</label>
          <select class="setting-select" id="examLanguage" style="width:100%">
            <option value="English">English</option>
            <option value="French">French</option>
          </select>
        </div>
        <div>
          <label class="form-label">Total duration (minutes)</label>
          <input class="form-input" id="examDuration" type="number" min="5" value="30">
        </div>
      </div>
      <label class="form-label">Topic</label>
      <select class="setting-select" id="examTopic" style="width:100%;margin-bottom:12px">
        <option value="">Choose a lesson topic</option>
      </select>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px">
        <div>
          <label class="form-label">Passing score (%)</label>
          <input class="form-input" id="examPassScore" type="number" min="0" max="100" value="70">
        </div>
        <div>
          <label class="form-label">Attempts allowed</label>
          <input class="form-input" id="examAttempts" type="number" min="1" value="1">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;align-items:flex-end">
        <div>
          <label class="form-label">Content file</label>
          <input class="form-input" id="examFilePath" type="text" readonly value="content/EXAMS/ENGLISH.yaml">
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" id="cancelExamModal">Cancel</button>
      <button class="btn btn-primary" id="saveExamBtn">Create exam</button>
    </div>
  </div>
</div>
<div class="modal-overlay" id="inviteModal">
  <div class="modal-box">
    <div class="modal-head">
      <div><div class="modal-title">Invite candidates</div></div>
      <button class="modal-close" id="closeInviteModal">✕</button>
    </div>
    <div class="modal-sub">Paste candidate emails separated by commas or new lines.</div>
    <div class="modal-content">
      <label class="form-label">Candidate emails</label>
      <textarea class="form-input" id="candidateEmails" rows="6" placeholder="alice@example.com\nbob@example.com"></textarea>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" id="cancelInviteModal">Cancel</button>
      <button class="btn btn-primary" id="sendInvitesBtn">Send invites</button>
    </div>
  </div>
</div>
<script>
const exams = <?php echo escapeJson($exams); ?> || [];
const candidates = <?php echo escapeJson($candidates); ?> || [];
const examLanguages = <?php echo escapeJson($languages); ?> || [];
const navButtons = document.querySelectorAll('.nav-item');
const tabPages = document.querySelectorAll('.tab-content');
const badgeExams = document.getElementById('badge-exams');
const badgeExamCount = document.getElementById('badge-exam-count');
const badgeCandidates = document.getElementById('badge-candidates');
const passPctLabel = document.getElementById('sidebar-pass-pct');
const completedLabel = document.getElementById('sidebar-completed-label');
const passFillBar = document.getElementById('pass-fill-bar');
const dashMetrics = document.getElementById('dash-metrics');
const dashLinks = document.getElementById('dash-links');
const dashRecent = document.getElementById('dash-recent');
const dashTop = document.getElementById('dash-top');
const dashDist = document.getElementById('dash-dist');
const examsList = document.getElementById('exams-list');
const resultsMetrics = document.getElementById('results-metrics');
const resultsTable = document.getElementById('results-table');
const candidatesTable = document.getElementById('candidates-table');
const resultsFilter = document.getElementById('results-filter');
const examCreatedBanner = document.getElementById('examCreatedBanner');
const examModal = document.getElementById('examModal');
const inviteModal = document.getElementById('inviteModal');
const openCreateModalBtn = document.getElementById('openCreateModalBtn');
const cancelExamModal = document.getElementById('cancelExamModal');
const closeExamModal = document.getElementById('closeExamModal');
const saveExamBtn = document.getElementById('saveExamBtn');
const quickCreateBtn = document.getElementById('quickCreateBtn');
const examLanguage = document.getElementById('examLanguage');
const examFilePath = document.getElementById('examFilePath');
const examTopic = document.getElementById('examTopic');
const examTitle = document.getElementById('examTitle');
const examDuration = document.getElementById('examDuration');
const examPassScore = document.getElementById('examPassScore');
const examAttempts = document.getElementById('examAttempts');
const openCandidatesModalBtn = document.getElementById('openCandidatesModalBtn');
const closeInviteModal = document.getElementById('closeInviteModal');
const cancelInviteModal = document.getElementById('cancelInviteModal');
const sendInvitesBtn = document.getElementById('sendInvitesBtn');
const candidateEmails = document.getElementById('candidateEmails');
const candFilterTabs = document.getElementById('cand-filter-tabs');
const exportDashBtn = document.getElementById('exportDashBtn');
const downloadLinksBtn = document.getElementById('downloadLinksBtn');
const exportResultsBtn = document.getElementById('exportResultsBtn');

const topCandidates = [...candidates].sort((a,b)=>{
  const aScore = parseInt(a.score || 0, 10);
  const bScore = parseInt(b.score || 0, 10);
  return bScore - aScore;
}).slice(0, 4);

function switchTab(tabId) {
  tabPages.forEach(page => page.classList.toggle('active', page.id === 'tab-' + tabId));
  navButtons.forEach(btn => btn.classList.toggle('active', btn.dataset.tab === tabId));
}

navButtons.forEach(btn => btn.addEventListener('click', () => switchTab(btn.dataset.tab)));

function updateSidebarStats() {
  const totalExams = exams.length;
  const completedCount = candidates.filter(c => c.status === 'completed').length;
  const passCount = candidates.filter(c => Number(c.score) >= 70).length;
  const passPct = completedCount ? Math.round((passCount / completedCount) * 100) : 0;
  badgeExams.textContent = totalExams;
  badgeExamCount.textContent = totalExams;
  badgeCandidates.textContent = candidates.length;
  passPctLabel.textContent = passPct + '%';
  completedLabel.textContent = `${completedCount} completed`;
  passFillBar.style.width = `${passPct}%`;
}

function renderMetric(label, value) {
  return `<div class="metric-card"><div class="metric-label">${label}</div><div class="metric-value">${value}</div></div>`;
}

function renderDashboard() {
  const totalExams = exams.length;
  const totalCandidates = candidates.length;
  const completed = candidates.filter(c => c.status === 'completed').length;
  const avgScore = completed ? Math.round(candidates.filter(c=>c.status==='completed').reduce((sum,c)=>sum+Number(c.score||0),0)/completed) : 0;
  dashMetrics.innerHTML = renderMetric('Active exams', totalExams) + renderMetric('Invited candidates', totalCandidates) + renderMetric('Average completed score', `${avgScore}%`);

  dashLinks.innerHTML = exams.slice(0,3).map(exam => examCardHtml(exam)).join('') || '<div class="card-body" style="padding:24px;color:var(--muted)">No exams yet. Create one to get started.</div>';

  const recent = candidates.filter(c=>c.status==='completed').slice(0,4);
  dashRecent.innerHTML = recent.map(c => `<div class="cand-row"><div class="cand-info"><div class="cand-name">${c.first_name || c.email || 'Candidate'}</div><div class="cand-meta">${c.status || 'Completed'} · Score ${c.score || '0'}%</div></div><button class="view-btn">Review</button></div>`).join('') || '<div class="card-body" style="padding:24px;color:var(--muted)">No recent submissions.</div>';

  dashTop.innerHTML = topCandidates.map(c => `<div class="cand-row"><div class="cand-info"><div class="cand-name">${c.first_name || c.email || 'Candidate'}</div><div class="cand-meta">Score ${c.score || '0'}% · ${c.status || 'Pending'}</div></div></div>`).join('') || '<div class="card-body" style="padding:24px;color:var(--muted)">No top candidates yet.</div>';

  const passCount = candidates.filter(c=>Number(c.score) >= 70).length;
  const failCount = candidates.filter(c=>c.status==='completed' && Number(c.score) < 70).length;
  const passPct = passCount + failCount ? Math.round((passCount/(passCount+failCount))*100) : 0;
  dashDist.innerHTML = `<div class="metric-card" style="grid-column:span 1"><div class="metric-label">Pass rate</div><div class="metric-value">${passPct}%</div></div>`;
}

function examCardHtml(exam) {
  const link = `${window.location.origin}/frontend/lesson.php?invite=${encodeURIComponent(exam.invite_code || exam.id)}&topic=${encodeURIComponent(exam.exam_topic || exam.exam_title)}&level=all&pair=${encodeURIComponent(exam.exam_language || 'English')}&direction=${encodeURIComponent(exam.exam_language === 'French' ? 'fr-rw' : 'en-rw')}`;
  const topics = exam.exam_topic || 'Exam task';
  const duration = exam.exam_duration || 30;
  const passScore = exam.exam_pass_score || 70;
  const created = new Date(exam.created_at || Date.now()).toLocaleDateString();
  return `
    <div class="hw-card">
      <div class="hw-card-top">
        <div>
          <div class="hw-tag">${exam.exam_language || 'English'}</div>
          <div class="hw-title">${exam.exam_title || 'Untitled exam'}</div>
          <div class="hw-meta">Topic: ${topics} · Created ${created}</div>
        </div>
        <button class="hw-copy-btn" title="Copy invite link" onclick="copyLink('${encodeURIComponent(link)}')">⧉</button>
      </div>
      <div class="hw-card-main">
        <div class="hw-progress-row"><span class="hw-progress-pct">Pass score ${passScore}%</span><div class="hw-progress-bar"><div class="hw-progress-fill" style="width:${passScore}%;"></div></div></div>
        <div class="hw-progress-row"><span class="hw-progress-pct">Duration ${duration} min</span><div class="hw-progress-bar"><div class="hw-progress-fill" style="width:${Math.min(duration,60)}%;"></div></div></div>
      </div>
      <hr class="hw-divider">
      <div class="hw-card-footer">
        <div class="hw-footer-stats"><div><div class="hw-stat-val">${exam.candidate_count || 0}</div><div class="hw-stat-lbl">Invited</div></div><div><div class="hw-stat-val">${exam.completed_count || 0}</div><div class="hw-stat-lbl">Completed</div></div></div>
        <button class="hw-cta-btn" onclick="window.location.href='exam-dashboard.php?view=${exam.id}'">View exam</button>
      </div>
    </div>`;
}

function renderExams() {
  examsList.innerHTML = exams.map(exam => examCardHtml(exam)).join('') || '<div class="card-body" style="padding:24px;color:var(--muted)">No exams created yet.</div>';
}

function renderCandidates(filter = 'all') {
  candFilterTabs.innerHTML = ['all','pending','completed','invited'].map(status => `<button class="tab-pill ${filter===status?'active':''}" onclick="renderCandidates('${status}')">${status.charAt(0).toUpperCase()+status.slice(1)}</button>`).join('');
  const filtered = filter === 'all' ? candidates : candidates.filter(c => (c.status||'pending') === filter);
  candidatesTable.innerHTML = filtered.map(c => `<div class="cand-row"><div class="cand-info"><div class="cand-name">${c.first_name || c.email || 'Candidate'}</div><div class="cand-meta">${c.email || ''} · ${c.status || 'Pending'}</div></div><button class="view-btn">Details</button></div>`).join('') || '<div class="card-body" style="padding:24px;color:var(--muted)">No candidates found.</div>';
}

function renderResults() {
  const completed = candidates.filter(c => c.status === 'completed');
  const passCount = completed.filter(c => Number(c.score) >= 70).length;
  const failCount = completed.filter(c => Number(c.score) < 70).length;
  const avgScore = completed.length ? Math.round(completed.reduce((sum,c)=>sum+Number(c.score||0),0)/completed.length) : 0;
  resultsMetrics.innerHTML = renderMetric('Completed exams', completed.length) + renderMetric('Pass count', passCount) + renderMetric('Average score', `${avgScore}%`);
  resultsTable.innerHTML = completed.map(c => `<div class="cand-row"><div class="cand-info"><div class="cand-name">${c.first_name || c.email}</div><div class="cand-meta">Score ${c.score || '0'}% · ${c.exam_language || 'English'}</div></div><button class="view-btn">Review</button></div>`).join('') || '<div class="card-body" style="padding:24px;color:var(--muted)">No completed exam results yet.</div>';
}

function openModal(modal) {
  modal.classList.add('show');
}
function closeModal(modal) {
  modal.classList.remove('show');
}

function updateExamFilePath() {
  const lang = examLanguage.value;
  examFilePath.value = `content/EXAMS/${lang === 'French' ? 'French' : 'ENGLISH'}.yaml`;
}

function populateTopics() {
  const topics = examLanguages.includes(examLanguage.value) ? [examLanguage.value] : [examLanguage.value];
  examTopic.innerHTML = `<option value="">Choose a lesson topic</option>` + topics.map(topic => `<option value="${topic}">${topic}</option>`).join('');
}

function copyLink(link) {
  navigator.clipboard.writeText(decodeURIComponent(link)).then(() => {
    alert('Invite link copied.');
  });
}

function resetExamForm() {
  examTitle.value = '';
  examDuration.value = 30;
  examPassScore.value = 70;
  examAttempts.value = 1;
  examLanguage.value = 'English';
  updateExamFilePath();
  populateTopics();
}

openCreateModalBtn.addEventListener('click', () => {
  resetExamForm();
  openModal(examModal);
});
quickCreateBtn.addEventListener('click', () => openCreateModalBtn.click());
closeExamModal.addEventListener('click', () => closeModal(examModal));
cancelExamModal.addEventListener('click', () => closeModal(examModal));
examLanguage.addEventListener('change', () => {
  updateExamFilePath();
  populateTopics();
});

openCandidatesModalBtn.addEventListener('click', () => openModal(inviteModal));
closeInviteModal.addEventListener('click', () => closeModal(inviteModal));
cancelInviteModal.addEventListener('click', () => closeModal(inviteModal));

sendInvitesBtn.addEventListener('click', () => {
  const emails = candidateEmails.value.trim();
  if (!emails) return alert('Add candidate emails first.');
  alert('Candidate invites are queued.');
  candidateEmails.value = '';
  closeModal(inviteModal);
});

saveExamBtn.addEventListener('click', () => {
  const title = examTitle.value.trim();
  const topic = examTopic.value || examLanguage.value;
  if (!title) return alert('Enter exam title.');
  const newExam = {
    id: Date.now(),
    exam_title: title,
    exam_language: examLanguage.value,
    exam_topic: topic,
    exam_duration: examDuration.value,
    exam_pass_score: examPassScore.value,
    candidate_count: 0,
    completed_count: 0,
    created_at: new Date().toISOString().split('T')[0],
    invite_code: `exam-${Date.now()}`
  };
  exams.unshift(newExam);
  renderDashboard();
  renderExams();
  examCreatedBanner.classList.add('show');
  setTimeout(() => examCreatedBanner.classList.remove('show'), 3000);
  closeModal(examModal);
});

['exportDashBtn','downloadLinksBtn','exportResultsBtn'].forEach(id => {
  const btn = document.getElementById(id);
  if (btn) btn.addEventListener('click', () => alert('Export is not configured in this preview.'));
});

resultsFilter.addEventListener('change', () => renderResults());

populateTopics();
updateExamFilePath();
updateSidebarStats();
renderDashboard();
renderExams();
renderCandidates();
renderResults();

document.querySelectorAll('[data-goto]').forEach(btn => btn.addEventListener('click', () => switchTab(btn.dataset.goto)));
</script>
</body>
</html>
