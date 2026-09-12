<?php
// Start session to get language from landing page
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get language from session (set by landing page)
$lang = isset($_SESSION['user_lang']) ? $_SESSION['user_lang'] : 'en';

// Validate language
if (!in_array($lang, ['en', 'rw', 'sw'])) {
    $lang = 'en';
}

// Store in session
$_SESSION['user_lang'] = $lang;
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>How to Take an Exam · Playmates</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{
  --sand:#f5ede6;--sand-mid:#ede3d8;--sand-dark:#ddd0c2;
  --sage:#c6dfd5;--sage-mid:#8bbfad;--sage-dark:#3a7a68;--sage-deep:#225548;
  --butter:#f5e8b0;--peach:#f5d6c4;--blush:#f9d8cd;
  --ink:#1a1a18;--ink-mid:#4a4845;--muted:#8a8178;--soft:#b8b0a5;
  --surface:#faf8f5;--surface-raised:#ffffff;--border:rgba(0,0,0,0.07);
  --radius-sm:8px;--radius:14px;--radius-lg:20px;
  --transition:0.25s cubic-bezier(.4,0,.2,1);
  --amber-bg:#fef8ec;--amber-border:#f5c96a;--amber-text:#7a5c1e;--amber-deep:#5a3e08;
  --shadow-card:0 1px 3px rgba(0,0,0,0.06),0 4px 12px rgba(0,0,0,0.04);
  --shadow-hover:0 2px 8px rgba(0,0,0,0.08),0 8px 24px rgba(0,0,0,0.07);
}
html,body{
  min-height:100vh;font-family:'DM Sans',sans-serif;font-size:14px;
  color:var(--ink);background:var(--sand);
  display:flex;align-items:flex-start;justify-content:center;
  padding:32px 16px 72px;
}
.wrapper{width:100%;max-width:880px}

/* LANGUAGE SWITCHER */
.lang-switcher{
  display:flex;gap:4px;align-items:center;margin-bottom:16px;
  background:var(--surface-raised);padding:3px;border-radius:30px;
  border:1px solid var(--border);width:fit-content;
  box-shadow:var(--shadow-card);
}
.lang-btn{
  border:none;background:transparent;padding:5px 14px;
  border-radius:20px;font-size:11px;font-weight:500;
  cursor:pointer;color:var(--muted);transition:all var(--transition);
  font-family:'DM Sans',sans-serif;
}
.lang-btn:hover{color:var(--ink)}
.lang-btn.active{
  background:var(--sage);color:var(--sage-deep);
  box-shadow:0 1px 3px rgba(0,0,0,0.08);
}

/* BACK */
.back-link{
  display:inline-flex;align-items:center;gap:6px;
  font-size:12px;font-weight:500;color:var(--muted);
  text-decoration:none;margin-bottom:16px;
  transition:color var(--transition);
}
.back-link:hover{color:var(--sage-dark)}

/* BRAND */
.brand-row{display:flex;align-items:center;gap:8px;margin-bottom:16px}
.brand-row img{width:26px;height:26px;border-radius:6px;object-fit:contain}
.brand-row span{font-family:'DM Serif Display',serif;font-size:15px;color:var(--sage-deep)}

/* PAGE HEADER */
.page-title{font-family:'DM Serif Display',serif;font-size:26px;color:var(--ink);letter-spacing:-0.4px;margin-bottom:5px}
.page-sub{font-size:13px;color:var(--muted);line-height:1.6;margin-bottom:28px}

/* FLOW CARD */
.flow-card{
  background:var(--surface-raised);border:1px solid var(--border);
  border-radius:var(--radius-lg);padding:20px 22px;
  margin-bottom:36px;box-shadow:var(--shadow-card);
}
.flow-label{font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:1px;color:var(--soft);margin-bottom:14px;}
.flow-pills{display:flex;flex-wrap:wrap;gap:7px;list-style:none;align-items:center}
.flow-pill{
  display:inline-flex;align-items:center;gap:5px;
  font-size:12px;font-weight:500;padding:5px 12px 5px 8px;
  border-radius:30px;background:var(--sand-mid);border:1px solid var(--sand-dark);
  color:var(--ink-mid);cursor:default;
  transition:background var(--transition),border-color var(--transition),color var(--transition);
}
.flow-pill:hover{background:var(--sage);border-color:var(--sage-mid);color:var(--sage-deep)}
.flow-pill-n{
  width:17px;height:17px;border-radius:50%;background:var(--surface-raised);
  border:1px solid var(--sand-dark);display:flex;align-items:center;justify-content:center;
  font-size:9px;color:var(--soft);flex-shrink:0;
}
.flow-arrow{color:var(--soft);font-size:12px;line-height:1;flex-shrink:0}

/* SECTION */
.guide-section{margin-bottom:40px}
.section-header{display:flex;align-items:flex-start;gap:14px;margin-bottom:18px}
.section-step{
  width:30px;height:30px;flex-shrink:0;border-radius:50%;
  background:var(--surface-raised);border:1px solid var(--sand-dark);
  display:flex;align-items:center;justify-content:center;
  font-size:11px;font-weight:500;color:var(--muted);margin-top:2px;
  box-shadow:var(--shadow-card);
}
.section-step.opt{background:var(--butter);border-color:#e8d88a;color:#7a6620}
.section-eyebrow{font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:1px;color:var(--soft);margin-bottom:3px;}
.section-eyebrow.opt-tag{color:#b8960a}
.section-label{font-family:'DM Serif Display',serif;font-size:18px;color:var(--ink);letter-spacing:-0.2px;line-height:1.3}
.section-intro{font-size:13px;color:var(--muted);line-height:1.65;margin-top:4px}
.section-intro strong{color:var(--ink-mid);font-weight:500}

/* CARDS */
.card{background:var(--surface-raised);border:1px solid var(--border);border-radius:var(--radius);padding:20px;box-shadow:var(--shadow-card);}
.card-title{font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:1px;color:var(--soft);margin-bottom:14px;}
.card-amber{background:var(--amber-bg);border:1px solid var(--amber-border);border-radius:var(--radius);padding:20px;}
.card-amber .card-title{color:var(--amber-deep)}
.card-sage{background:#e6f4f0;border:1px solid var(--sage);border-radius:var(--radius);padding:20px;}
.card-sage .card-title{color:var(--sage-deep)}

/* GRID */
.grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px}
.span2{grid-column:1/-1}

/* RULE LIST */
.rule-list{display:flex;flex-direction:column;gap:9px}
.rule-item{display:flex;align-items:flex-start;gap:9px}
.rule-dot{width:17px;height:17px;flex-shrink:0;border-radius:50%;border:1px solid var(--sand-dark);display:flex;align-items:center;justify-content:center;margin-top:2px;}
.rule-dot-inner{width:6px;height:6px;border-radius:50%;background:var(--soft)}
.rule-text{font-size:13px;color:var(--muted);line-height:1.55;margin:0}
.rule-text strong{color:var(--ink-mid);font-weight:500}
.step-num{width:17px;height:17px;flex-shrink:0;border-radius:50%;border:1px solid var(--sand-dark);background:var(--surface-raised);display:flex;align-items:center;justify-content:center;margin-top:2px;font-size:9px;font-weight:500;color:var(--muted);}
.card-amber .rule-dot{border-color:var(--amber-border)}
.card-amber .rule-dot-inner{background:#c8921a}
.card-amber .rule-text{color:var(--amber-text)}
.card-sage .rule-dot{border-color:var(--sage-mid)}
.card-sage .rule-dot-inner{background:var(--sage-dark)}
.card-sage .rule-text{color:var(--sage-deep)}

/* PILLS */
.pill-row{display:flex;gap:7px;flex-wrap:wrap;margin-bottom:12px}
.pill{font-size:11px;font-weight:500;padding:3px 10px;border-radius:30px;background:var(--sand-mid);border:1px solid var(--sand-dark);color:var(--ink-mid);}

/* IMAGE BLOCK */
.img-block{margin-bottom:12px;border-radius:var(--radius);overflow:hidden;border:1px solid var(--sand-dark);box-shadow:var(--shadow-card);transition:box-shadow var(--transition);}
.img-block.clickable{cursor:zoom-in}
.img-block.clickable:hover{box-shadow:var(--shadow-hover)}
.img-block.clickable:hover .img-overlay{opacity:1}
.img-wrapper{position:relative;background:var(--sand-mid)}
.img-wrapper img{width:100%;display:block;border-bottom:1px solid var(--sand-dark);}
.img-placeholder{background:var(--sand-mid);min-height:180px;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:6px;border-bottom:1px solid var(--sand-dark);}
.img-placeholder svg{color:var(--soft);width:28px;height:28px}
.img-placeholder span{font-size:11px;color:var(--soft);font-style:italic}
.img-overlay{position:absolute;inset:0;background:rgba(34,85,72,0.12);display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity var(--transition);}
.img-overlay-icon{background:rgba(255,255,255,0.92);border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;}
.img-caption{padding:11px 14px;background:var(--surface-raised);font-size:12px;color:var(--muted);line-height:1.55;}
.img-caption strong{color:var(--ink-mid);font-weight:500}

/* NOTICE */
.notice{border-radius:var(--radius-sm);padding:11px 13px;font-size:13px;margin-bottom:12px;display:flex;align-items:flex-start;gap:9px;border:1px solid transparent;line-height:1.6;}
.notice-icon{flex-shrink:0;margin-top:1px}
.notice-sage{background:#e6f4f0;border-color:var(--sage);color:var(--sage-deep)}
.notice-amber{background:var(--amber-bg);border-color:var(--amber-border);color:var(--amber-text)}

/* DIVIDER */
.section-divider{border:none;border-top:1px solid var(--sand-dark);margin:36px 0;}

/* STATE CARDS (verified / not verified) */
.state-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px}
.state-card{border-radius:var(--radius);padding:18px;border:1px solid transparent;}
.state-card-verified{background:#e6f4f0;border-color:var(--sage)}
.state-card-pending{background:var(--amber-bg);border-color:var(--amber-border)}
.state-card-unverified{background:#fdf0ed;border-color:#e8b09a}
.state-badge{
  display:inline-flex;align-items:center;gap:5px;
  font-size:11px;font-weight:500;padding:3px 10px;border-radius:30px;
  margin-bottom:10px;
}
.state-badge-verified{background:#c6dfd5;border:1px solid var(--sage-mid);color:var(--sage-deep)}
.state-badge-pending{background:var(--butter);border:1px solid var(--amber-border);color:var(--amber-text)}
.state-badge-unverified{background:#fce4dc;border:1px solid #e8b09a;color:#7a3020}
.state-title{font-size:12px;font-weight:500;margin-bottom:5px}
.state-body{font-size:12px;line-height:1.55}
.state-card-verified .state-title{color:var(--sage-deep)}
.state-card-verified .state-body{color:var(--sage-deep)}
.state-card-pending .state-title{color:var(--amber-deep)}
.state-card-pending .state-body{color:var(--amber-text)}
.state-card-unverified .state-title{color:#7a3020}
.state-card-unverified .state-body{color:#7a3020}

/* CERT STATES */
.cert-state-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px}

/* FAQ */
.faq-list{display:flex;flex-direction:column;gap:8px}
.faq-item{background:var(--surface-raised);border:1px solid var(--border);border-radius:var(--radius-sm);overflow:hidden;box-shadow:var(--shadow-card);transition:box-shadow var(--transition);}
.faq-item:hover{box-shadow:var(--shadow-hover)}
.faq-q{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:12px 14px;cursor:pointer;font-size:13px;font-weight:500;color:var(--ink);user-select:none;}
.faq-q:hover{background:var(--sand-mid)}
.faq-chevron{flex-shrink:0;transition:transform var(--transition);color:var(--muted)}
.faq-a{font-size:13px;color:var(--muted);line-height:1.65;padding:0 14px;max-height:0;overflow:hidden;transition:max-height 0.3s ease,padding 0.3s ease;}
.faq-item.open .faq-a{max-height:200px;padding:0 14px 12px}
.faq-item.open .faq-chevron{transform:rotate(180deg)}

/* FOOTER */
.guide-footer{margin-top:40px;padding-top:20px;border-top:1px solid var(--sand-dark);text-align:center;font-size:12px;color:var(--muted);line-height:1.9;}
.guide-footer a{color:var(--sage-dark);text-decoration:none}
.guide-footer a:hover{text-decoration:underline}

/* LIGHTBOX */
.lightbox-overlay{
  position:fixed;inset:0;background:rgba(26,26,24,0.78);
  backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);
  z-index:1000;display:flex;align-items:center;justify-content:center;
  padding:24px;opacity:0;pointer-events:none;transition:opacity 0.22s ease;
}
.lightbox-overlay.visible{opacity:1;pointer-events:all}
.lightbox-inner{
  position:relative;max-width:860px;width:100%;
  border-radius:var(--radius-lg);overflow:hidden;
  background:var(--surface-raised);
  box-shadow:0 24px 80px rgba(0,0,0,0.4);
  transform:scale(0.96);transition:transform 0.22s ease;
}
.lightbox-overlay.visible .lightbox-inner{transform:scale(1)}
.lightbox-img{width:100%;display:block;max-height:70vh;object-fit:contain;background:var(--sand-mid)}
.lightbox-footer{padding:14px 18px 16px;display:flex;align-items:flex-start;justify-content:space-between;gap:14px;}
.lightbox-caption{font-size:13px;color:var(--muted);line-height:1.55;flex:1}
.lightbox-caption strong{color:var(--ink);font-weight:500}
.lightbox-close{
  flex-shrink:0;width:30px;height:30px;border-radius:50%;
  border:1px solid var(--sand-dark);background:var(--surface);
  display:flex;align-items:center;justify-content:center;
  cursor:pointer;color:var(--muted);
  transition:background var(--transition),color var(--transition);
}
.lightbox-close:hover{background:var(--sand-dark);color:var(--ink)}
.lightbox-step-label{
  position:absolute;top:14px;left:14px;
  background:rgba(26,26,24,0.6);border-radius:30px;
  padding:4px 12px;font-size:11px;font-weight:500;color:#fff;
  backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px);
}

/* TOAST */
.toast-message {
  position:fixed;bottom:30px;left:50%;transform:translateX(-50%);
  background:#1a2c2a;color:#fff;padding:10px 24px;border-radius:60px;
  font-size:0.8rem;font-weight:500;z-index:10000;
  box-shadow:0 8px 20px rgba(0,0,0,0.2);backdrop-filter:blur(8px);
  font-family:'DM Sans',sans-serif;animation:fadeUp 0.25s ease;white-space:nowrap;pointer-events:none;
}
@keyframes fadeUp{from{opacity:0;transform:translateX(-50%) translateY(20px)}to{opacity:1;transform:translateX(-50%) translateY(0)}}

@media(max-width:640px){
  .grid,.state-grid,.cert-state-grid{grid-template-columns:1fr}
  .span2{grid-column:1}
  .page-title{font-size:21px}
  .section-label{font-size:16px}
  .flow-arrow{display:none}
  .lang-switcher{width:100%;justify-content:center}
  .lang-btn{padding:5px 12px;font-size:10px}
}
</style>
</head>
<body>
<div class="wrapper">

  <!-- LANGUAGE SWITCHER -->
  <div class="lang-switcher" role="group" aria-label="Language switcher">
    <button class="lang-btn <?php echo $lang === 'en' ? 'active' : ''; ?>" data-lang="en">EN</button>
    <button class="lang-btn <?php echo $lang === 'rw' ? 'active' : ''; ?>" data-lang="rw">RW</button>
    <button class="lang-btn <?php echo $lang === 'sw' ? 'active' : ''; ?>" data-lang="sw">SW</button>
  </div>

  <a href="landing.php?lang=<?php echo $lang; ?>" class="back-link">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 15 15"><path d="M9 3L4 7.5L9 12"/></svg>
    <span data-key="back_text">Back to dashboard</span>
  </a>

  <div class="brand-row">
    <img src="https://res.cloudinary.com/franklinrw/image/upload/v1755169717/kjk_bnrbmp.png" alt="Playmates">
    <span>Playmates</span>
  </div>

  <div class="page-title" data-key="page_title">How to take an exam 📚</div>
  <div class="page-sub" data-key="page_sub">Step-by-step guide to taking language exams on Playmates. Click any screenshot to enlarge it.</div>

  <!-- FLOW OVERVIEW -->
  <div class="flow-card">
    <div class="flow-label" data-key="flow_label">Exam flow — 9 steps</div>
    <ul class="flow-pills">
      <li class="flow-pill"><span class="flow-pill-n">1</span><span data-key="step1_label">Homepage</span></li>
      <span class="flow-arrow">›</span>
      <li class="flow-pill"><span class="flow-pill-n">2</span><span data-key="step2_label">Log in</span></li>
      <span class="flow-arrow">›</span>
      <li class="flow-pill"><span class="flow-pill-n">3</span><span data-key="step3_label">Language games</span></li>
      <span class="flow-arrow">›</span>
      <li class="flow-pill"><span class="flow-pill-n">4</span><span data-key="step4_label">Choose exam type</span></li>
      <span class="flow-arrow">›</span>
      <li class="flow-pill"><span class="flow-pill-n">5</span><span data-key="step5_label">Popcorn code</span></li>
      <span class="flow-arrow">›</span>
      <li class="flow-pill"><span class="flow-pill-n">6</span><span data-key="step6_label">Review & verify</span></li>
      <span class="flow-arrow">›</span>
      <li class="flow-pill"><span class="flow-pill-n">7</span><span data-key="step7_label">Take the exam</span></li>
      <span class="flow-arrow">›</span>
      <li class="flow-pill"><span class="flow-pill-n">8</span><span data-key="step8_label">View certificate</span></li>
      <span class="flow-arrow">›</span>
      <li class="flow-pill"><span class="flow-pill-n">9</span><span data-key="step9_label">Download & share</span></li>
    </ul>
  </div>

  <!-- ── STEP 1 ── -->
  <div class="guide-section">
    <div class="section-header">
      <div class="section-step">1</div>
      <div class="section-text">
        <div class="section-eyebrow" data-key="s1_eyebrow">Step 1</div>
        <div class="section-label" data-key="s1_title">Access the homepage</div>
        <div class="section-intro" data-key="s1_desc">Visit the Playmates homepage. You'll see different game categories — click <strong>Languages</strong> to access language exams.</div>
      </div>
    </div>
    <div class="img-block clickable" onclick="openLightbox('exam-guide-assets/01-homepage.png','Step 1 — Homepage','The homepage shows course categories including Languages, Development Game, Coding Games, and Classroom Games. Click Languages to continue.')">
      <div class="img-wrapper">
        <img src="exam-guide-assets/01-homepage.png" alt="Homepage" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
        <div class="img-placeholder" style="display:none"><svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg><span>01-homepage.png</span></div>
        <div class="img-overlay"><div class="img-overlay-icon"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg></div></div>
      </div>
      <div class="img-caption"><strong data-key="s1_caption_strong">Homepage:</strong> <span data-key="s1_caption">Click "Languages" from the category grid to access all language exams and games.</span></div>
    </div>
  </div>

  <hr class="section-divider">

  <!-- ── STEP 2 ── -->
  <div class="guide-section">
    <div class="section-header">
      <div class="section-step">2</div>
      <div class="section-text">
        <div class="section-eyebrow" data-key="s2_eyebrow">Step 2</div>
        <div class="section-label" data-key="s2_title">Log in to your account</div>
        <div class="section-intro" data-key="s2_desc">Enter your email and password to access your account. New to Playmates? You can sign up from this page.</div>
      </div>
    </div>
    <div class="img-block clickable" onclick="openLightbox('exam-guide-assets/02-login.png','Step 2 — Login','Enter your email address and password, then click Login to proceed.')">
      <div class="img-wrapper">
        <img src="exam-guide-assets/02-login.png" alt="Login page" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
        <div class="img-placeholder" style="display:none"><svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg><span>02-login.png</span></div>
        <div class="img-overlay"><div class="img-overlay-icon"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg></div></div>
      </div>
      <div class="img-caption"><strong data-key="s2_caption_strong">Login page:</strong> <span data-key="s2_caption">Enter your email and password, then click "Login" to continue to your dashboard.</span></div>
    </div>
    <div class="notice notice-sage">
      <svg class="notice-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 15 15"><circle cx="7.5" cy="7.5" r="6"/><path d="M7.5 5v3M7.5 9.5v.5" stroke-linecap="round"/></svg>
      <span data-key="s2_notice">Keep your credentials safe. Never share your password with anyone.</span>
    </div>
  </div>

  <hr class="section-divider">

  <!-- ── STEP 3 ── -->
  <div class="guide-section">
    <div class="section-header">
      <div class="section-step">3</div>
      <div class="section-text">
        <div class="section-eyebrow" data-key="s3_eyebrow">Step 3</div>
        <div class="section-label" data-key="s3_title">Go to Language Games & click "Take Exam"</div>
        <div class="section-intro" data-key="s3_desc">After logging in, you'll land on your dashboard. Navigate to the Languages section and click <strong>"Take Exam"</strong> to begin the exam registration process.</div>
      </div>
    </div>
    <div class="img-block clickable" onclick="openLightbox('exam-guide-assets/03-language-dashboard.png','Step 3 — Language dashboard','Find the Take Exam button on the language dashboard to begin exam registration.')">
      <div class="img-wrapper">
        <img src="exam-guide-assets/03-language-dashboard.png" alt="Language dashboard" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
        <div class="img-placeholder" style="display:none"><svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg><span>03-language-dashboard.png</span></div>
        <div class="img-overlay"><div class="img-overlay-icon"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg></div></div>
      </div>
      <div class="img-caption"><strong data-key="s3_caption_strong">Language dashboard:</strong> <span data-key="s3_caption">Click the <strong>"Take Exam"</strong> button to start the exam registration flow. This takes you to Step 4 — choosing your exam type.</span></div>
    </div>
    <div class="notice notice-amber">
      <svg class="notice-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 15 15"><path d="M7.5 1.5l6 11H1.5l6-11z" stroke-linejoin="round"/><path d="M7.5 6v3" stroke-linecap="round"/><circle cx="7.5" cy="10.5" r=".6" fill="currentColor"/></svg>
      <span data-key="s3_notice">Review your accuracy and topic progress before starting — this helps you identify areas to brush up on first.</span>
    </div>
  </div>

  <hr class="section-divider">

  <!-- ── STEP 4 ── -->
  <div class="guide-section">
    <div class="section-header">
      <div class="section-step">4</div>
      <div class="section-text">
        <div class="section-eyebrow" data-key="s4_eyebrow">Step 4</div>
        <div class="section-label" data-key="s4_title">Choose your exam type</div>
        <div class="section-intro" data-key="s4_desc">Select the exam that matches your goals. Both options contain 40 questions and take 60 minutes to complete.</div>
      </div>
    </div>
    <div class="grid">
      <div class="card">
        <div class="card-title" data-key="s4_card1_title">Academic English 🎓</div>
        <div class="pill-row"><span class="pill" data-key="s4_pill_q">40 questions</span><span class="pill" data-key="s4_pill_time">60 min</span></div>
        <p style="font-size:13px;color:var(--muted);line-height:1.6" data-key="s4_card1_desc">For university and academic study. Tests comprehension, writing, and formal communication.</p>
      </div>
      <div class="card">
        <div class="card-title" data-key="s4_card2_title">Business English 💼</div>
        <div class="pill-row"><span class="pill" data-key="s4_pill_q">40 questions</span><span class="pill" data-key="s4_pill_time">60 min</span></div>
        <p style="font-size:13px;color:var(--muted);line-height:1.6" data-key="s4_card2_desc">For professional and workplace communication. Focuses on business terminology and professional writing.</p>
      </div>
    </div>
    <div class="img-block clickable" onclick="openLightbox('exam-guide-assets/04-choose-exam-type.png','Step 4 — Choose exam type','Select Academic English or Business English based on your goals.')">
      <div class="img-wrapper">
        <img src="exam-guide-assets/04-choose-exam-type.png" alt="Choose exam type" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
        <div class="img-placeholder" style="display:none"><svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg><span>04-choose-exam-type.png</span></div>
        <div class="img-overlay"><div class="img-overlay-icon"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg></div></div>
      </div>
      <div class="img-caption"><strong data-key="s4_caption_strong">Exam selection:</strong> <span data-key="s4_caption">Pick Academic or Business English. You can only choose one per session. After selecting, you'll be taken to enter your popcorn code (if applicable).</span></div>
    </div>
  </div>

  <hr class="section-divider">

  <!-- ── STEP 5 ── -->
  <div class="guide-section">
    <div class="section-header">
      <div class="section-step opt">5</div>
      <div class="section-text">
        <div class="section-eyebrow opt-tag" data-key="s5_eyebrow">Step 5 · Optional</div>
        <div class="section-label" data-key="s5_title">Apply with a popcorn code 🍿</div>
        <div class="section-intro" data-key="s5_desc">If your institution provided a popcorn code, enter it here. This links your exam to your institution's registration. Skip ahead if you don't have one.</div>
      </div>
    </div>
    <div class="grid">
      <div class="card">
        <div class="card-title" data-key="s5_card1_title">What you'll need</div>
        <div class="rule-list">
          <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text" data-key="s5_card1_item1"><strong>Popcorn code</strong> — provided by your school</p></div>
          <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text" data-key="s5_card1_item2"><strong>Full name</strong> — exactly as it should appear on your certificate</p></div>
          <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text" data-key="s5_card1_item3"><strong>School name</strong> — your institution's name</p></div>
        </div>
      </div>
      <div class="card">
        <div class="card-title" data-key="s5_card2_title">Good to know</div>
        <p style="font-size:13px;color:var(--muted);line-height:1.6" data-key="s5_card2_desc">The exam type is automatically linked to your code. Double-check your spelling — your full name will appear on your certificate exactly as entered.</p>
      </div>
    </div>
    <div class="img-block clickable" onclick="openLightbox('exam-guide-assets/10-exam-popcorn.png','Step 5 — Popcorn code application','Enter your popcorn code, full name, and school name to register through your institution.')">
      <div class="img-wrapper">
        <img src="exam-guide-assets/10-exam-popcorn.png" alt="Popcorn code form" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
        <div class="img-placeholder" style="display:none"><svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg><span>10-exam-popcorn.png</span></div>
        <div class="img-overlay"><div class="img-overlay-icon"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg></div></div>
      </div>
      <div class="img-caption"><strong data-key="s5_caption_strong">Popcorn application:</strong> <span data-key="s5_caption">Enter your code, full name, and school name. The exam type is linked automatically from your selection in Step 4.</span></div>
    </div>
  </div>

  <hr class="section-divider">

  <!-- ── STEP 6 ── -->
  <div class="guide-section">
    <div class="section-header">
      <div class="section-step">6</div>
      <div class="section-text">
        <div class="section-eyebrow" data-key="s6_eyebrow">Step 6</div>
        <div class="section-label" data-key="s6_title">Review your information & identity verification</div>
        <div class="section-intro" data-key="s6_desc">Before starting the exam, you'll see a summary of your details and your identity verification status. The page looks the same for everyone — but the status shown depends on whether your documents have been verified.</div>
      </div>
    </div>

    <!-- Identity status states -->
    <div class="state-grid" style="margin-bottom:14px">
      <div class="state-card state-card-verified">
        <div class="state-badge state-badge-verified">
          <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 15 15"><circle cx="7.5" cy="7.5" r="6"/><path d="M4.5 7.5l2 2 4-4" stroke-linecap="round"/></svg>
          <span data-key="s6_badge_verified">Identity verified</span>
        </div>
        <div class="state-title" data-key="s6_verified_title">You're ready to proceed</div>
        <div class="state-body" data-key="s6_verified_body">Your documents have been verified. The "Begin Exam" button is enabled — you can start immediately.</div>
      </div>
      <div class="state-card state-card-unverified">
        <div class="state-badge state-badge-unverified">
          <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 15 15"><path d="M7.5 1.5l6 11H1.5l6-11z" stroke-linejoin="round"/><path d="M7.5 6v3" stroke-linecap="round"/><circle cx="7.5" cy="10.5" r=".6" fill="currentColor"/></svg>
          <span data-key="s6_badge_unverified">Not verified</span>
        </div>
        <div class="state-title" data-key="s6_unverified_title">Verification required</div>
        <div class="state-body" data-key="s6_unverified_body">You'll need to complete identity verification before the "Begin Exam" button becomes active. Follow the on-screen instructions to submit your ID or passport.</div>
      </div>
    </div>

    <div class="img-block clickable" onclick="openLightbox('exam-guide-assets/11-exam-rules.png','Step 6 — Review & verify','The review page shows your exam details and identity verification status.')">
      <div class="img-wrapper">
        <img src="exam-guide-assets/11-exam-rules.png" alt="Review and verify screen" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
        <div class="img-placeholder" style="display:none"><svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg><span>11-exam-rules.png</span></div>
        <div class="img-overlay"><div class="img-overlay-icon"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg></div></div>
      </div>
      <div class="img-caption"><strong data-key="s6_caption_strong">Review page:</strong> <span data-key="s6_caption">Shows exam details, rules, and your identity verification status. The "Begin Exam" button is only active when your identity is verified.</span></div>
    </div>

    <!-- Rules summary -->
    <div class="grid">
      <div class="card">
        <div class="card-title" data-key="s6_rules_title">Exam rules</div>
        <div class="rule-list">
          <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text" data-key="s6_rule1">Each question may only be answered once.</p></div>
          <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text" data-key="s6_rule2">Passing score is <strong>70%</strong>. Results shown immediately.</p></div>
          <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text" data-key="s6_rule3">Retakes allowed after a <strong>7-day</strong> waiting period.</p></div>
        </div>
      </div>
      <div class="card">
        <div class="card-title" data-key="s6_conduct_title">Conduct during exam</div>
        <div class="rule-list">
          <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text" data-key="s6_conduct1">You must be alone — no other people in the room.</p></div>
          <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text" data-key="s6_conduct2">No notes, books, phones, or headphones.</p></div>
          <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text" data-key="s6_conduct3">Your face must be clearly visible at all times.</p></div>
        </div>
      </div>
    </div>
    <div class="card-amber">
      <div class="card-title" style="display:flex;align-items:center;gap:6px">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 15 15"><path d="M7.5 1.5l6 11H1.5l6-11z" stroke-linejoin="round"/><path d="M7.5 6v3" stroke-linecap="round"/><circle cx="7.5" cy="10.5" r=".6" fill="currentColor"/></svg>
        <span data-key="s6_break_title">If you break the rules</span>
      </div>
      <div class="rule-list">
        <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text" data-key="s6_break1">Your exam may be cancelled immediately.</p></div>
        <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text" data-key="s6_break2">Your result will not be certified.</p></div>
        <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text" data-key="s6_break3">Multiple violations may lead to account suspension.</p></div>
      </div>
    </div>
  </div>

  <hr class="section-divider">

  <!-- ── STEP 7 ── -->
  <div class="guide-section">
    <div class="section-header">
      <div class="section-step">7</div>
      <div class="section-text">
        <div class="section-eyebrow" data-key="s7_eyebrow">Step 7</div>
        <div class="section-label" data-key="s7_title">Take the exam</div>
        <div class="section-intro" data-key="s7_desc">Once you click "Begin Exam," the timer starts immediately. Answer each question carefully — each can only be answered once.</div>
      </div>
    </div>
    <div class="img-block clickable" onclick="openLightbox('exam-guide-assets/07-exam-in-progress.png','Step 7 — Exam in progress','Progress bar, countdown timer, question card, and navigation buttons are all shown during the exam.')">
      <div class="img-wrapper">
        <img src="exam-guide-assets/07-exam-in-progress.png" alt="Exam in progress" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
        <div class="img-placeholder" style="display:none"><svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg><span>07-exam-in-progress.png</span></div>
        <div class="img-overlay"><div class="img-overlay-icon"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg></div></div>
      </div>
      <div class="img-caption"><strong data-key="s7_caption_strong">Exam interface:</strong> <span data-key="s7_caption">Progress bar at the top, countdown timer, question card with answer options, and Previous/Next navigation. Timer turns red when 2 minutes remain.</span></div>
    </div>
    <div class="grid">
      <div class="card">
        <div class="card-title" data-key="s7_qtype_title">Question types</div>
        <div class="rule-list">
          <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text" data-key="s7_qtype1"><strong>Fixed answer</strong> — 10 questions</p></div>
          <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text" data-key="s7_qtype2"><strong>Constructed response</strong> — 6 questions</p></div>
          <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text" data-key="s7_qtype3"><strong>Speaking</strong> — 2 questions (repeat the prompt clearly)</p></div>
          <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text" data-key="s7_qtype4"><strong>Reading</strong> — 2 questions</p></div>
        </div>
      </div>
      <div class="card">
        <div class="card-title" data-key="s7_time_title">Time warnings</div>
        <div class="rule-list">
          <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text" data-key="s7_time1"><strong>Yellow</strong> — 10 minutes remaining</p></div>
          <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text" data-key="s7_time2"><strong>Red pulse</strong> — 2 minutes remaining</p></div>
          <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text" data-key="s7_time3"><strong>Auto-submit</strong> — when timer hits 0:00</p></div>
        </div>
      </div>
    </div>
    <div class="card-amber">
      <div class="card-title" style="display:flex;align-items:center;gap:6px">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 15 15"><path d="M7.5 1.5l6 11H1.5l6-11z" stroke-linejoin="round"/><path d="M7.5 6v3" stroke-linecap="round"/><circle cx="7.5" cy="10.5" r=".6" fill="currentColor"/></svg>
        <span data-key="s7_tab_title">Tab switch violations</span>
      </div>
      <p style="font-size:13px;color:var(--amber-text);line-height:1.6" data-key="s7_tab_desc">Switching to another tab triggers a warning. After <strong>7 violations</strong>, your exam is automatically dismissed. Keep the exam window active at all times.</p>
    </div>
  </div>

  <hr class="section-divider">

  <!-- ── STEP 8 ── -->
  <div class="guide-section">
    <div class="section-header">
      <div class="section-step">8</div>
      <div class="section-text">
        <div class="section-eyebrow" data-key="s8_eyebrow">Step 8</div>
        <div class="section-label" data-key="s8_title">View your certificate</div>
        <div class="section-intro" data-key="s8_desc">Score 70% or higher and a certificate is generated automatically. You can view it right away — but what you can do with it depends on whether it has been verified.</div>
      </div>
    </div>
    <div class="img-block clickable" onclick="openLightbox('exam-guide-assets/08-your-certificates.png','Step 8 — Your certificates','All completed exams appear as cards showing name, exam type, score, issue date, and verification status.')">
      <div class="img-wrapper">
        <img src="exam-guide-assets/08-your-certificates.png" alt="Certificates page" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
        <div class="img-placeholder" style="display:none"><svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg><span>08-your-certificates.png</span></div>
        <div class="img-overlay"><div class="img-overlay-icon"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg></div></div>
      </div>
      <div class="img-caption"><strong data-key="s8_caption_strong">Certificates section:</strong> <span data-key="s8_caption">Each card shows your name, exam type, score, issue date, and current verification status.</span></div>
    </div>

    <!-- Certificate states -->
    <div class="cert-state-grid">
      <div class="state-card state-card-verified">
        <div class="state-badge state-badge-verified">
          <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 15 15"><circle cx="7.5" cy="7.5" r="6"/><path d="M4.5 7.5l2 2 4-4" stroke-linecap="round"/></svg>
          <span data-key="s8_cert_verified_badge">Verified & approved</span>
        </div>
        <div class="state-title" data-key="s8_cert_verified_title">Full access</div>
        <div class="state-body" data-key="s8_cert_verified_body">You can view full certificate details, download a PDF, and share your certificate link or QR code with employers and institutions.</div>
      </div>
      <div class="state-card state-card-pending">
        <div class="state-badge state-badge-pending">
          <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 15 15"><circle cx="7.5" cy="7.5" r="6"/><path d="M7.5 4.5v3l2 1.5" stroke-linecap="round"/></svg>
          <span data-key="s8_cert_pending_badge">Pending verification</span>
        </div>
        <div class="state-title" data-key="s8_cert_pending_title">View only — no download yet</div>
        <div class="state-body" data-key="s8_cert_pending_body">You can view your certificate details, but the download and share options are not available until your institution approves it. Check back after 24–48 hours.</div>
      </div>
    </div>
    <div class="notice notice-amber">
      <svg class="notice-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 15 15"><path d="M7.5 1.5l6 11H1.5l6-11z" stroke-linejoin="round"/><path d="M7.5 6v3" stroke-linecap="round"/><circle cx="7.5" cy="10.5" r=".6" fill="currentColor"/></svg>
      <span data-key="s8_notice">If your certificate shows as <strong>Not Verified</strong>, please wait for your institution to review and approve it. No action is required on your end.</span>
    </div>
  </div>

  <hr class="section-divider">

  <!-- ── STEP 9 ── -->
  <div class="guide-section">
    <div class="section-header">
      <div class="section-step">9</div>
      <div class="section-text">
        <div class="section-eyebrow" data-key="s9_eyebrow">Step 9</div>
        <div class="section-label" data-key="s9_title">Download & share your certificate</div>
        <div class="section-intro" data-key="s9_desc">Once your certificate is verified and approved, you can download it as a PDF or share it via link or QR code.</div>
      </div>
    </div>
    <div class="img-block clickable" onclick="openLightbox('exam-guide-assets/12-exam-certificate-verify.png','Step 9 — Verified certificate','The VERIFIED & APPROVED banner appears alongside full certificate details and download options.')">
      <div class="img-wrapper">
        <img src="exam-guide-assets/12-exam-certificate-verify.png" alt="Verified certificate" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
        <div class="img-placeholder" style="display:none"><svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg><span>12-exam-certificate-verify.png</span></div>
        <div class="img-overlay"><div class="img-overlay-icon"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg></div></div>
      </div>
      <div class="img-caption"><strong data-key="s9_caption_strong">Verified certificate:</strong> <span data-key="s9_caption">Shows your name, exam type, score, date issued, and unique certificate ID (e.g. CERT-ENRW-2026-00045) with a QR code for verification.</span></div>
    </div>
    <div class="card">
      <div class="card-title" data-key="s9_download_title">How to download</div>
      <div class="rule-list">
        <div class="rule-item"><div class="step-num">1</div><p class="rule-text" data-key="s9_download1">Go to Certificates from your dashboard.</p></div>
        <div class="rule-item"><div class="step-num">2</div><p class="rule-text" data-key="s9_download2">Click the verified certificate you want to download.</p></div>
        <div class="rule-item"><div class="step-num">3</div><p class="rule-text" data-key="s9_download3">Click <strong>Download</strong> or <strong>Print</strong> to save as PDF.</p></div>
        <div class="rule-item"><div class="step-num">4</div><p class="rule-text" data-key="s9_download4">Share the link or QR code — recipients can verify it instantly.</p></div>
      </div>
    </div>
    <div class="notice notice-sage">
      <svg class="notice-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 15 15"><circle cx="7.5" cy="7.5" r="6"/><path d="M4.5 7.5l2 2 4-4" stroke-linecap="round"/></svg>
      <span data-key="s9_notice">Each verified certificate has a unique ID (e.g. <strong>CERT-ENRW-2026-00045</strong>). Anyone can scan the QR code on the certificate to confirm its authenticity.</span>
    </div>
  </div>

  <hr class="section-divider">

  <!-- ── TIPS ── -->
  <div class="guide-section">
    <div class="section-header">
      <div class="section-step" style="background:var(--butter);border-color:#e8d88a">
        <svg width="13" height="13" fill="none" stroke="#7a6620" stroke-width="2" viewBox="0 0 15 15"><circle cx="7.5" cy="6" r="3.5"/><path d="M6.5 9.5v2.5M8.5 9.5v2.5M6.5 12h2" stroke-linecap="round"/></svg>
      </div>
      <div class="section-text">
        <div class="section-eyebrow" data-key="tips_eyebrow">Pro tips</div>
        <div class="section-label" data-key="tips_title">Before, during & after</div>
        <div class="section-intro" data-key="tips_desc">A few things to set yourself up for success on exam day.</div>
      </div>
    </div>
    <div class="grid">
      <div class="card">
        <div class="card-title" data-key="tips_before_title">Before your exam</div>
        <div class="rule-list">
          <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text" data-key="tips_before1">Ensure a stable internet connection.</p></div>
          <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text" data-key="tips_before2">Test your microphone — speaking questions require it.</p></div>
          <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text" data-key="tips_before3">Close unnecessary tabs and applications.</p></div>
          <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text" data-key="tips_before4">Find a quiet, private space with good lighting.</p></div>
        </div>
      </div>
      <div class="card">
        <div class="card-title" data-key="tips_during_title">During your exam</div>
        <div class="rule-list">
          <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text" data-key="tips_during1">Read each question carefully before answering.</p></div>
          <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text" data-key="tips_during2">Don't spend too long on one question.</p></div>
          <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text" data-key="tips_during3">Stay calm — this tests your current knowledge level.</p></div>
          <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text" data-key="tips_during4">Keep the exam window in focus at all times.</p></div>
        </div>
      </div>
    </div>
    <div class="card-sage">
      <div class="card-title" data-key="tips_after_title">After your exam</div>
      <div class="rule-list">
        <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text" data-key="tips_after1">Your score is displayed <strong>immediately</strong> after submission.</p></div>
        <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text" data-key="tips_after2">Pass (≥70%) and your certificate is generated automatically.</p></div>
        <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text" data-key="tips_after3">Verification usually takes <strong>24–48 hours</strong>. Download and share become available once approved.</p></div>
        <div class="rule-item"><div class="rule-dot"><div class="rule-dot-inner"></div></div><p class="rule-text" data-key="tips_after4">Retake available after <strong>7 days</strong> if you don't pass.</p></div>
      </div>
    </div>
  </div>

  <hr class="section-divider">

  <!-- ── FAQ ── -->
  <div class="guide-section">
    <div class="section-header">
      <div class="section-step">?</div>
      <div class="section-text">
        <div class="section-eyebrow" data-key="faq_eyebrow">FAQ</div>
        <div class="section-label" data-key="faq_title">Frequently asked questions</div>
        <div class="section-intro" data-key="faq_desc">Quick answers to common questions about the exam process.</div>
      </div>
    </div>
    <div class="faq-list">
      <div class="faq-item">
        <div class="faq-q" onclick="toggleFaq(this)"><span data-key="faq_q1">What's the passing score?</span><svg class="faq-chevron" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 15 15"><path d="M3 5l4.5 4.5L12 5"/></svg></div>
        <div class="faq-a" data-key="faq_a1">You need to score 70% or higher to pass and receive a certificate.</div>
      </div>
      <div class="faq-item">
        <div class="faq-q" onclick="toggleFaq(this)"><span data-key="faq_q2">Can I pause the exam?</span><svg class="faq-chevron" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 15 15"><path d="M3 5l4.5 4.5L12 5"/></svg></div>
        <div class="faq-a" data-key="faq_a2">No. Once you start the exam you must complete it in one session. The timer runs continuously, so plan to be uninterrupted for the full 60 minutes.</div>
      </div>
      <div class="faq-item">
        <div class="faq-q" onclick="toggleFaq(this)"><span data-key="faq_q3">Can I retake the exam if I fail?</span><svg class="faq-chevron" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 15 15"><path d="M3 5l4.5 4.5L12 5"/></svg></div>
        <div class="faq-a" data-key="faq_a3">Yes. You can retake the exam after a 7-day waiting period if you score below 70%.</div>
      </div>
      <div class="faq-item">
        <div class="faq-q" onclick="toggleFaq(this)"><span data-key="faq_q4">What documents do I need?</span><svg class="faq-chevron" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 15 15"><path d="M3 5l4.5 4.5L12 5"/></svg></div>
        <div class="faq-a" data-key="faq_a4">You need a valid government-issued ID or passport for identity verification. Either is acceptable.</div>
      </div>
      <div class="faq-item">
        <div class="faq-q" onclick="toggleFaq(this)"><span data-key="faq_q5">How do speaking questions work?</span><svg class="faq-chevron" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 15 15"><path d="M3 5l4.5 4.5L12 5"/></svg></div>
        <div class="faq-a" data-key="faq_a5">A prompt will appear on screen for you to repeat or respond to. Click the microphone button and speak clearly. Make sure your microphone is working before starting the exam.</div>
      </div>
      <div class="faq-item">
        <div class="faq-q" onclick="toggleFaq(this)"><span data-key="faq_q6">Can I use external materials?</span><svg class="faq-chevron" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 15 15"><path d="M3 5l4.5 4.5L12 5"/></svg></div>
        <div class="faq-a" data-key="faq_a6">No. Using notes, books, phones, or any external resources during the exam may result in score invalidation and account suspension.</div>
      </div>
      <div class="faq-item">
        <div class="faq-q" onclick="toggleFaq(this)"><span data-key="faq_q7">Why can't I download my certificate yet?</span><svg class="faq-chevron" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 15 15"><path d="M3 5l4.5 4.5L12 5"/></svg></div>
        <div class="faq-a" data-key="faq_a7">Download and share options are only available after your institution verifies and approves your certificate. This usually takes 24–48 hours. You can still view your certificate details while waiting.</div>
      </div>
      <div class="faq-item">
        <div class="faq-q" onclick="toggleFaq(this)"><span data-key="faq_q8">Is the certificate valid forever?</span><svg class="faq-chevron" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 15 15"><path d="M3 5l4.5 4.5L12 5"/></svg></div>
        <div class="faq-a" data-key="faq_a8">Yes. Playmates certificates do not expire. Anyone can verify your certificate at any time by scanning the QR code on the document.</div>
      </div>
      <div class="faq-item">
        <div class="faq-q" onclick="toggleFaq(this)"><span data-key="faq_q9">Can I share my certificate?</span><svg class="faq-chevron" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 15 15"><path d="M3 5l4.5 4.5L12 5"/></svg></div>
        <div class="faq-a" data-key="faq_a9">Yes, once verified. You can share the certificate link directly or let recipients scan the QR code on the PDF to confirm its authenticity.</div>
      </div>
    </div>
  </div>

  <!-- FOOTER -->
  <div class="guide-footer">
    <p data-key="footer_platform">Playmates Language Learning Platform · Exam Taker's Guide</p>
    <p><a href="mailto:support@playmates.rw" data-key="footer_email">support@playmates.rw</a> · <a href="https://www.playmates.rw">www.playmates.rw</a></p>
    <p style="margin-top:6px;font-size:11px;color:var(--soft)" data-key="footer_copyright">© 2026 Playmates. All rights reserved.</p>
  </div>

</div>

<!-- LIGHTBOX -->
<div class="lightbox-overlay" id="lightbox" onclick="handleOverlayClick(event)">
  <div class="lightbox-inner" id="lightboxInner">
    <div class="lightbox-step-label" id="lightboxLabel"></div>
    <img class="lightbox-img" id="lightboxImg" src="" alt="">
    <div class="lightbox-footer">
      <div class="lightbox-caption" id="lightboxCaption"></div>
      <button class="lightbox-close" onclick="closeLightbox()" aria-label="Close">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 15 15"><path d="M3 3l9 9M12 3l-9 9"/></svg>
      </button>
    </div>
  </div>
</div>

<script>
// ── TRANSLATIONS ──────────────────────────────────────────────
var translations = {
  en: {
    back_text: 'Back to dashboard',
    page_title: 'How to take an exam 📚',
    page_sub: 'Step-by-step guide to taking language exams on Playmates. Click any screenshot to enlarge it.',
    flow_label: 'Exam flow — 9 steps',
    step1_label: 'Homepage', step2_label: 'Log in', step3_label: 'Language games',
    step4_label: 'Choose exam type', step5_label: 'Popcorn code', step6_label: 'Review & verify',
    step7_label: 'Take the exam', step8_label: 'View certificate', step9_label: 'Download & share',
    s1_eyebrow: 'Step 1', s1_title: 'Access the homepage', s1_desc: 'Visit the Playmates homepage. You\'ll see different game categories — click <strong>Languages</strong> to access language exams.',
    s1_caption_strong: 'Homepage:', s1_caption: 'Click "Languages" from the category grid to access all language exams and games.',
    s2_eyebrow: 'Step 2', s2_title: 'Log in to your account', s2_desc: 'Enter your email and password to access your account. New to Playmates? You can sign up from this page.',
    s2_caption_strong: 'Login page:', s2_caption: 'Enter your email and password, then click "Login" to continue to your dashboard.',
    s2_notice: 'Keep your credentials safe. Never share your password with anyone.',
    s3_eyebrow: 'Step 3', s3_title: 'Go to Language Games & click "Take Exam"', s3_desc: 'After logging in, you\'ll land on your dashboard. Navigate to the Languages section and click <strong>"Take Exam"</strong> to begin the exam registration process.',
    s3_caption_strong: 'Language dashboard:', s3_caption: 'Click the <strong>"Take Exam"</strong> button to start the exam registration flow. This takes you to Step 4 — choosing your exam type.',
    s3_notice: 'Review your accuracy and topic progress before starting — this helps you identify areas to brush up on first.',
    s4_eyebrow: 'Step 4', s4_title: 'Choose your exam type', s4_desc: 'Select the exam that matches your goals. Both options contain 40 questions and take 60 minutes to complete.',
    s4_card1_title: 'Academic English 🎓', s4_pill_q: '40 questions', s4_pill_time: '60 min',
    s4_card1_desc: 'For university and academic study. Tests comprehension, writing, and formal communication.',
    s4_card2_title: 'Business English 💼', s4_card2_desc: 'For professional and workplace communication. Focuses on business terminology and professional writing.',
    s4_caption_strong: 'Exam selection:', s4_caption: 'Pick Academic or Business English. You can only choose one per session. After selecting, you\'ll be taken to enter your popcorn code (if applicable).',
    s5_eyebrow: 'Step 5 · Optional', s5_title: 'Apply with a popcorn code 🍿', s5_desc: 'If your institution provided a popcorn code, enter it here. This links your exam to your institution\'s registration. Skip ahead if you don\'t have one.',
    s5_card1_title: 'What you\'ll need', s5_card1_item1: '<strong>Popcorn code</strong> — provided by your school', s5_card1_item2: '<strong>Full name</strong> — exactly as it should appear on your certificate', s5_card1_item3: '<strong>School name</strong> — your institution\'s name',
    s5_card2_title: 'Good to know', s5_card2_desc: 'The exam type is automatically linked to your code. Double-check your spelling — your full name will appear on your certificate exactly as entered.',
    s5_caption_strong: 'Popcorn application:', s5_caption: 'Enter your code, full name, and school name. The exam type is linked automatically from your selection in Step 4.',
    s6_eyebrow: 'Step 6', s6_title: 'Review your information & identity verification', s6_desc: 'Before starting the exam, you\'ll see a summary of your details and your identity verification status. The page looks the same for everyone — but the status shown depends on whether your documents have been verified.',
    s6_badge_verified: 'Identity verified', s6_verified_title: 'You\'re ready to proceed', s6_verified_body: 'Your documents have been verified. The "Begin Exam" button is enabled — you can start immediately.',
    s6_badge_unverified: 'Not verified', s6_unverified_title: 'Verification required', s6_unverified_body: 'You\'ll need to complete identity verification before the "Begin Exam" button becomes active. Follow the on-screen instructions to submit your ID or passport.',
    s6_caption_strong: 'Review page:', s6_caption: 'Shows exam details, rules, and your identity verification status. The "Begin Exam" button is only active when your identity is verified.',
    s6_rules_title: 'Exam rules', s6_rule1: 'Each question may only be answered once.', s6_rule2: 'Passing score is <strong>70%</strong>. Results shown immediately.', s6_rule3: 'Retakes allowed after a <strong>7-day</strong> waiting period.',
    s6_conduct_title: 'Conduct during exam', s6_conduct1: 'You must be alone — no other people in the room.', s6_conduct2: 'No notes, books, phones, or headphones.', s6_conduct3: 'Your face must be clearly visible at all times.',
    s6_break_title: 'If you break the rules', s6_break1: 'Your exam may be cancelled immediately.', s6_break2: 'Your result will not be certified.', s6_break3: 'Multiple violations may lead to account suspension.',
    s7_eyebrow: 'Step 7', s7_title: 'Take the exam', s7_desc: 'Once you click "Begin Exam," the timer starts immediately. Answer each question carefully — each can only be answered once.',
    s7_caption_strong: 'Exam interface:', s7_caption: 'Progress bar at the top, countdown timer, question card with answer options, and Previous/Next navigation. Timer turns red when 2 minutes remain.',
    s7_qtype_title: 'Question types', s7_qtype1: '<strong>Fixed answer</strong> — 10 questions', s7_qtype2: '<strong>Constructed response</strong> — 6 questions', s7_qtype3: '<strong>Speaking</strong> — 2 questions (repeat the prompt clearly)', s7_qtype4: '<strong>Reading</strong> — 2 questions',
    s7_time_title: 'Time warnings', s7_time1: '<strong>Yellow</strong> — 10 minutes remaining', s7_time2: '<strong>Red pulse</strong> — 2 minutes remaining', s7_time3: '<strong>Auto-submit</strong> — when timer hits 0:00',
    s7_tab_title: 'Tab switch violations', s7_tab_desc: 'Switching to another tab triggers a warning. After <strong>7 violations</strong>, your exam is automatically dismissed. Keep the exam window active at all times.',
    s8_eyebrow: 'Step 8', s8_title: 'View your certificate', s8_desc: 'Score 70% or higher and a certificate is generated automatically. You can view it right away — but what you can do with it depends on whether it has been verified.',
    s8_caption_strong: 'Certificates section:', s8_caption: 'Each card shows your name, exam type, score, issue date, and current verification status.',
    s8_cert_verified_badge: 'Verified & approved', s8_cert_verified_title: 'Full access', s8_cert_verified_body: 'You can view full certificate details, download a PDF, and share your certificate link or QR code with employers and institutions.',
    s8_cert_pending_badge: 'Pending verification', s8_cert_pending_title: 'View only — no download yet', s8_cert_pending_body: 'You can view your certificate details, but the download and share options are not available until your institution approves it. Check back after 24–48 hours.',
    s8_notice: 'If your certificate shows as <strong>Not Verified</strong>, please wait for your institution to review and approve it. No action is required on your end.',
    s9_eyebrow: 'Step 9', s9_title: 'Download & share your certificate', s9_desc: 'Once your certificate is verified and approved, you can download it as a PDF or share it via link or QR code.',
    s9_caption_strong: 'Verified certificate:', s9_caption: 'Shows your name, exam type, score, date issued, and unique certificate ID (e.g. CERT-ENRW-2026-00045) with a QR code for verification.',
    s9_download_title: 'How to download', s9_download1: 'Go to Certificates from your dashboard.', s9_download2: 'Click the verified certificate you want to download.', s9_download3: 'Click <strong>Download</strong> or <strong>Print</strong> to save as PDF.', s9_download4: 'Share the link or QR code — recipients can verify it instantly.',
    s9_notice: 'Each verified certificate has a unique ID (e.g. <strong>CERT-ENRW-2026-00045</strong>). Anyone can scan the QR code on the certificate to confirm its authenticity.',
    tips_eyebrow: 'Pro tips', tips_title: 'Before, during & after', tips_desc: 'A few things to set yourself up for success on exam day.',
    tips_before_title: 'Before your exam', tips_before1: 'Ensure a stable internet connection.', tips_before2: 'Test your microphone — speaking questions require it.', tips_before3: 'Close unnecessary tabs and applications.', tips_before4: 'Find a quiet, private space with good lighting.',
    tips_during_title: 'During your exam', tips_during1: 'Read each question carefully before answering.', tips_during2: 'Don\'t spend too long on one question.', tips_during3: 'Stay calm — this tests your current knowledge level.', tips_during4: 'Keep the exam window in focus at all times.',
    tips_after_title: 'After your exam', tips_after1: 'Your score is displayed <strong>immediately</strong> after submission.', tips_after2: 'Pass (≥70%) and your certificate is generated automatically.', tips_after3: 'Verification usually takes <strong>24–48 hours</strong>. Download and share become available once approved.', tips_after4: 'Retake available after <strong>7 days</strong> if you don\'t pass.',
    faq_eyebrow: 'FAQ', faq_title: 'Frequently asked questions', faq_desc: 'Quick answers to common questions about the exam process.',
    faq_q1: 'What\'s the passing score?', faq_a1: 'You need to score 70% or higher to pass and receive a certificate.',
    faq_q2: 'Can I pause the exam?', faq_a2: 'No. Once you start the exam you must complete it in one session. The timer runs continuously, so plan to be uninterrupted for the full 60 minutes.',
    faq_q3: 'Can I retake the exam if I fail?', faq_a3: 'Yes. You can retake the exam after a 7-day waiting period if you score below 70%.',
    faq_q4: 'What documents do I need?', faq_a4: 'You need a valid government-issued ID or passport for identity verification. Either is acceptable.',
    faq_q5: 'How do speaking questions work?', faq_a5: 'A prompt will appear on screen for you to repeat or respond to. Click the microphone button and speak clearly. Make sure your microphone is working before starting the exam.',
    faq_q6: 'Can I use external materials?', faq_a6: 'No. Using notes, books, phones, or any external resources during the exam may result in score invalidation and account suspension.',
    faq_q7: 'Why can\'t I download my certificate yet?', faq_a7: 'Download and share options are only available after your institution verifies and approves your certificate. This usually takes 24–48 hours. You can still view your certificate details while waiting.',
    faq_q8: 'Is the certificate valid forever?', faq_a8: 'Yes. Playmates certificates do not expire. Anyone can verify your certificate at any time by scanning the QR code on the document.',
    faq_q9: 'Can I share my certificate?', faq_a9: 'Yes, once verified. You can share the certificate link directly or let recipients scan the QR code on the PDF to confirm its authenticity.',
    footer_platform: 'Playmates Language Learning Platform · Exam Taker\'s Guide',
    footer_email: 'support@playmates.rw', footer_copyright: '© 2026 Playmates. All rights reserved.'
  },
  rw: {
    back_text: 'Subira kuri dashboard',
    page_title: 'Uko wakora ikizamini 📚',
    page_sub: 'Ubuyobozi bw\'intambwe ku yindi bwo gukora ibizamini by\'indimi kuri Playmates. Kanda ku ifoto iyo ari yo yose kugira ngo ibe nini.',
    flow_label: 'Inzira y\'ikizamini — intambwe 9',
    step1_label: 'Ahabanza', step2_label: 'Injira', step3_label: 'Imikino y\'indimi',
    step4_label: 'Hitamo ubwoko', step5_label: 'Popcorn code', step6_label: 'Genzura ubumenyi',
    step7_label: 'Kora ikizamini', step8_label: 'Reba impamyabumenyi', step9_label: 'Yimanure & Isangize',
    s1_eyebrow: 'Intambwe ya 1', s1_title: 'Gana ahabanza', s1_desc: 'Sura urubuga rwa Playmates ahabanza. Uzahabona ibyiciro bitandukanye by\'imikino — kanda kuri <strong>Indimi</strong> kugira ngo ugere ku bizamini by\'indimi.',
    s1_caption_strong: 'Ahabanza:', s1_caption: 'Kanda kuri "Indimi" mu rutonde ruri ku mbonerahamwe kugira ngo ugere ku bizamini n\'imikino byose.',
    s2_eyebrow: 'Intambwe ya 2', s2_title: 'Injira muri konti yawe', s2_desc: 'Shyiramo imeri na ijambo-banga byawe biba bikwemerera kwinjira. Ni ubwa mbere ukoresha Playmates? Wa kwiyandikisha unyuze kuri uru rupapuro.',
    s2_caption_strong: 'Ahabasubirizwa:', s2_caption: 'Shyiramo imeri na ijambo-banga, hanyuma ukande "Injira" kugira ngo ukomeze kuri dashboard yawe.',
    s2_notice: 'Bika umutekano w\'imyirondoro yawe. Ntukigere usangira ijambo-banga ryawe n\'undi muntu uwo ari we wose.',
    s3_eyebrow: 'Intambwe ya 3', s3_title: 'Gana ku Mikino y\'Indimi ukande "Kora Ikizamini"', s3_desc: 'Nyuma yo kwinjira, uragera kuri dashboard yawe. Gana mu gice cy\'Indimi hanyuma ukande <strong>"Kora Ikizamini"</strong> kugira ngo utangire kwiyandikisha.',
    s3_caption_strong: 'Dashboard y\'indimi:', s3_caption: 'Kanda buto ya <strong>"Kora Ikizamini"</strong> kugira ngo utangire. Ibi birakujyana ku ntambwe ya 4 — guhitamo ubwoko bw\'ikizamini.',
    s3_notice: 'Genzura iterambere n\'ubumenyi bwawe mbere yo gutangira — ibi bigufasha kumenya aho ugomba kongera kwimenyereza mbere.',
    s4_eyebrow: 'Intambwe ya 4', s4_title: 'Hitamo ubwoko bw\'ikizamini', s4_desc: 'Hitamo ikizamini kijyanye n\'intego zawe. Amahitamo yombi akubiyemo ibibazo 40 kandi bimara iminota 60.',
    s4_card1_title: 'Icyongereza cy\'Amasomo 🎓', s4_pill_q: 'Ibibazo 40', s4_pill_time: 'Iminota 60',
    s4_card1_desc: 'Ku biga muri kaminuza n\'ubushakashatsi. Gipima kumva, kwandika, n\'itumanaho rya kinyamwuga.',
    s4_card2_title: 'Icyongereza cy\'Ubucuruzi 💼', s4_card2_desc: 'Ku itumanaho ryo mu kazi n\'ubucuruzi. Cyo cyibanda ku magambo y\'ubucuruzi n\'inyandiko z\'akazi.',
    s4_caption_strong: 'Guhitamo ikizamini:', s4_caption: 'Hitamo Icyongereza cy\'Amasomo cyangwa icy\'Ubucuruzi. Ushobora guhitamo kimwe gusa. Nyuma yo guhitamo, urajya gushyiramo popcorn code yawe.',
    s5_eyebrow: 'Intambwe ya 5 · Ntabwo ari itegeko', s5_title: 'Koresha popcorn code 🍿', s5_desc: 'Niba ikigo cyawe cyaraguhaye popcorn code, yishubyemo hano. Ibi bihura ikizamini cyawe n\'ikigo ukoreramo. Reba imbere niba nta yo ufite.',
    s5_card1_title: 'Icyo ukeneye', s5_card1_item1: '<strong>Popcorn code</strong> — uhabwa n\'ishuri ryawe', s5_card1_item2: '<strong>Amazina yose</strong> — neza nk\'uko agomba kugaragara ku mpamyabumenyi yawe', s5_card1_item3: '<strong>Izina ry\'ishuri</strong> — izina ry\'ikigo cyawe',
    s5_card2_title: 'Icyo wamenya', s5_card2_desc: 'Ubwoko bw\'ikizamini buhita buhuzwa na code yawe. Genzura neza niba wanditse neza — amazina yawe azagaragara ku mpamyabumenyi nk\'uko uyanditse.',
    s5_caption_strong: 'Gushyiramo code:', s5_caption: 'Shyiramo code, amazina yawe yose, n\'izina ry\'ishuri. Ubwoko bw\'ikizamini buhita buhuzwa na byo.',
    s6_eyebrow: 'Intambwe ya 6', s6_title: 'Genzura amakuru yawe & kwemeza imyirondoro', s6_desc: 'Mbere yo gutangira ikizamini, urabona incamake y\'imyirondoro yawe n\'uko kwemeza umwirondoro bihagaze. Uru rupapuro rugaragara kimwe kuri bose — ariko imiterere ihinduka bitewe n\'uko inyandiko zawe zemejwe.',
    s6_badge_verified: 'Umwirondoro wemejwe', s6_verified_title: 'Witeguye gukomeza', s6_verified_body: 'Inyandiko zawe zaremejwe. Buto ya "Tangira Ikizamini" irafunguye — ushobora guhita utangira.',
    s6_badge_unverified: 'Ntabwo wemejwe', s6_unverified_title: 'Kwemeza umwirondoro birakenewe', s6_unverified_body: 'Ukeneye kubanza kwemeza umwirondoro wawe mbere y\'uko buto ya "Tangira Ikizamini" ikora. Kurikiza amabwiriza ku mbuga ucurange irangamuntu cyangwa pasiporo.',
    s6_caption_strong: 'Urupapuro rwo kugenzura:', s6_caption: 'Rwerekana ibyerekeye ikizamini, amabwiriza, n\'imiterere y\'umwirondoro wawe. Buto ya "Tangira Ikizamini" ikora gusa iyo umwirondoro wawe wemejwe.',
    s6_rules_title: 'Amabwiriza y\'ikizamini', s6_rule1: 'Buri kibazo gisubizwa inshuro imwe gusa.', s6_rule2: 'Amanota yo gutsinda ni <strong>70%</strong>. Ibisubizo bihinduka ako kanya.', s6_rule3: 'Gusubiramo ikizamini byemewe nyuma y\'iminsi <strong>7</strong> y\'ikiruhuko.',
    s6_conduct_title: 'Imyitwarire mu kizamini', s6_conduct1: 'Ugomba kuba uri wenyine — nta muntu n\'umwe wemewe mu cyumba.', s6_conduct2: 'Nta nyandiko, bitabo, terefone, cyangwa ekuteri byemewe.', s6_conduct3: 'Isura yawe igomba kugaragara neza igihe cyose.',
    s6_break_title: 'Niba urenze ku mabwiriza', s6_break1: 'Ikizamini cyawe gishobora guhagarikwa ako kanya.', s6_break2: 'Ibisubizo byawe ntibizahabwa icyatizwa.', s6_break3: 'Amakosa kenshi ashobora gutuma konti yawe ifungwa.',
    s7_eyebrow: 'Intambwe ya 7', s7_title: 'Kora ikizamini', s7_desc: 'Iyo umaze gukanda "Tangira Ikizamini," igihe gihita gitangira. Subiza buri kibazo witonze — gisubizwa inshuro imwe gusa.',
    s7_caption_strong: 'Imiterere y\'ikizamini:', s7_caption: 'Harimo akamenyetso k\'iterambere, igihe gisigaye, n\'ibibazo. Iyo hasigaye iminota 2, igihe gihinduka umutuku.',
    s7_qtype_title: 'Ubwoko bw\'ibibazo', s7_qtype1: '<strong>Guhitamo igisubizo</strong> — ibibazo 10', s7_qtype2: '<strong>Kwandika igisubizo</strong> — ibibazo 6', s7_qtype3: '<strong>Kuvuga</strong> — ibibazo 2 (subiramo amagambo uvuga neza)', s7_qtype4: '<strong>Gusoma</strong> — ibibazo 2',
    s7_time_title: 'Iburira ry\'igihe', s7_time1: '<strong>Umuhondo</strong> — hasigaye iminota 10', s7_time2: '<strong>Umutuku urabagirana</strong> — hasigaye iminota 2', s7_time3: '<strong>Kwiyandikisha kuryo</strong> — iyo igihe kigeze kuri 0:00',
    s7_tab_title: 'Guhindura urupapuro (Tab switches)', s7_tab_desc: 'Guhindura urupapuro rwa interineti bitera imburira. Nyuma y\'inshuro <strong>7</strong>, ikizamini gihita gihagarara. Guma kuri urwo rupa rwa interineti igihe cyose.',
    s8_eyebrow: 'Intambwe ya 8', s8_title: 'Reba impamyabumenyi yawe', s8_desc: 'Bona 70% cyangwa arenga, impamyabumenyi ihita ikorwa. Ushobora kuyireba ako kanya — ariko icyo wayikoresha gishingiye ku kuba yemejwe cyangwa itandukanye.',
    s8_caption_strong: 'Igice cy\'impamyabumenyi:', s8_caption: 'Buri karita yerekana izina ryawe, ubwoko bw\'ikizamini, amanota, itariki, n\'uko kwemeza bihagaze.',
    s8_cert_verified_badge: 'Yemejwe & yishimiwe', s8_cert_verified_title: 'Uburenganzira bwose', s8_cert_verified_body: 'Ushobora kureba incamake yose, kuyimanura nka PDF, no gusangira link cyangwa QR code n\'abakoresha cyangwa ibigo.',
    s8_cert_pending_badge: 'Itegereje kwemezwa', s8_cert_pending_title: 'Kureba gusa — ntuyimanura ubu', s8_cert_pending_body: 'Ushobora kureba impamyabumenyi yawe, ariko ntushobora kuyimanura kugeza igihe ikigo cyawe kizayemereza. Ongera urebe nyuma y\'amasaha 24–48.',
    s8_notice: 'Niba impamyabumenyi yawe igaragaza ko <strong>Itagifite icyemezo</strong>, utegereze ko ikigo cyawe kiyisuzuma kandi kikayemeza. Nta kindi usabwa gukora.',
    s9_eyebrow: 'Intambwe ya 9', s9_title: 'Yimanure & isangize impamyabumenyi yawe', s9_desc: 'Iyo impamyabumenyi imaze kwemezwa, ushobora kuyimanura nka PDF cyangwa ukayisangiza unyuze kuri link cyangwa QR code.',
    s9_caption_strong: 'Impamyabumenyi yemejwe:', s9_caption: 'Yerekana izina ryawe, amanota, itariki, na ID yihariye (urugero: CERT-ENRW-2026-00045) hamwe na QR code yo kwemeza.',
    s9_download_title: 'Uko wayimanura', s9_download1: 'Gana mu gice cy\'Impamyabumenyi kuri dashboard yawe.', s9_download2: 'Kanda ku mpamyabumenyi wifuza kumanura.', s9_download3: 'Kanda <strong>Yimanure</strong> cyangwa <strong>Capa</strong> kugira ngo uyibike nka PDF.', s9_download4: 'Sangiza link cyangwa QR code — abazakira barahita bayemeza ako kanya.',
    s9_notice: 'Buri mpamyabumenyi yemejwe ifite ID yihariye (urugero: <strong>CERT-ENRW-2026-00045</strong>). Umuntu wese ashobora gusanisha QR code iri ku mpamyabumenyi ye kugira ngo yemeze ukuri kwayo.',
    tips_eyebrow: 'Inama z\'inzobere', tips_title: 'Mbere, mu gihe, n\'nyuma', tips_desc: 'Ibintu bike byagufasha gutsinda ku munsi w\'ikizamini.',
    tips_before_title: 'Mbere y\'ikizamini', tips_before1: 'Iyumve ko ufite interineti ihagije.', tips_before2: 'Genzura mikoro yawe — ibibazo byo kuvuga birayikeneye.', tips_before3: 'Funga amapaji n\'izindi gahunda za mudasobwa zidakewe.', tips_before4: 'Shaka ahantu hatuje, hihariye kandi hari umucyo uhagije.',
    tips_during_title: 'Mu gihe cy\'ikizamini', tips_during1: 'Soma buri kibazo witonze mbere yo gusubiza.', tips_during2: 'Ntugakerere ku kibazo na kimwe.', tips_during3: 'Guma utuje — ibi bipima ubumenyi bwawe bw\'ubu.', tips_during4: 'Guma ku gupaji cy\'ikizamini igihe cyose.',
    tips_after_title: 'Nyuma y\'ikizamini', tips_after1: 'Amanota yawe ahita agaragara <strong>ako kanya</strong> umaze kohereza.', tips_after2: 'Gutsinda (≥70%) bituma impamyabumenyi yawe ihita ikorwa.', tips_after3: 'Kwemeza mubisanzwe bimara <strong>amasaha 24–48</strong>. Kuyimanura bishoboka gusa umaze kwemezwa.', tips_after4: 'Gusubiramo ikizamini bishoboka nyuma y\'iminsi <strong>7</strong> niba utatsinze.',
    faq_eyebrow: 'FAQ', faq_title: 'Ibibazo bikunze kubazwa', faq_desc: 'Ibisubizo byihuse ku bibazo bijyanye n\'ikizamini.',
    faq_q1: 'Amanota yo gutsinda ni ayahe?', faq_a1: 'Ukeneye gutsindira kuri 70% cyangwa arenga kugira ngo uhabwe impamyabumenyi.',
    faq_q2: 'Ese nshobora guhagarika ikizamini nkazagikomeza?', faq_a2: 'Oya. Iyo utangiye ikizamini ugomba kugikuriraho mu nshuro imwe. Igihe kiba kibara, bityo panga kugikora mu minota 60 idahagaritswe.',
    faq_q3: 'Ese nshobora gukora ikizamini kandi niba natsinzwe?', faq_a3: 'Ndiyo. Ushobora kugisubiramo nyuma y\'iminsi 7 niba wagize amanota ari munsi ya 70%.',
    faq_q4: 'Inyandiko zikenerwa ni izihe?', faq_a4: 'Ukeneye irangamuntu cyangwa pasiporo byemewe n\'amateka kugira ngo wemeze umwirondoro wawe.',
    faq_q5: 'Ibibazo byo kuvuga bikora bite?', faq_a5: 'Amagambo aza kugaragara ku mbuga kugira ngo uyasubiriremo. Kanda buto ya mikoro maze uvuge neza. Genzura ko mikoro yawe ikora neza mbere yo gutangira.',
    faq_q6: 'Ese nshobora gukoresha ibindi bikoresho?', faq_a6: 'Oya. Gukoresha inyandiko, bitabo, terefone, cyangwa ikindi kintu cyose byaganisha ku guhagarikwa kw\'ikizamini n\'izimira rya konti.',
    faq_q7: 'Kuki ntarashobora kumanura impamyabumenyi yanjye?', faq_a7: 'Kuyimanura no kuyisangiza bishoboka gusa iyo ikigo cyawe kimaze kwemeza impamyabumenyi yawe. Ibi bimara amasaha 24-48.',
    faq_q8: 'Ese impamyabumenyi ifite igihe izarangirira?', faq_a8: 'Oya. Impamyabumenyi za Playmates ntizirangira. Umuntu wese ashobora kuyemeza igihe cyose anyuze kuri QR code.',
    faq_q9: 'Ese nshobora gusangiza impamyabumenyi yanjye?', faq_a9: 'Ndiyo, umaze kwemezwa. Ushobora gusangiza link toke imbere cyangwa ukareka abantu bagasanisha QR code iri kuri PDF.',
    footer_platform: 'Urubuga rwa Playmates — Ubuyobozi bw\'Ukora Ikizamini',
    footer_email: 'support@playmates.rw', footer_copyright: '© 2026 Playmates. Uburenganzira bwose burasubitswe.'
  },
  sw: {
    back_text: 'Rudi kwenye dashboard',
    page_title: 'Jinsi ya kufanya mtihani 📚',
    page_sub: 'Mwongozo wa hatua kwa hatua wa kufanya mitihani ya lugha kwenye Playmates. Bonyeza picha yoyote ili kuikuza.',
    flow_label: 'Mchakato wa mtihani — hatua 9',
    step1_label: 'Nyumbani', step2_label: 'Ingia', step3_label: 'Michezo ya lugha',
    step4_label: 'Chagua aina', step5_label: 'Popcorn code', step6_label: 'Kagua & thibitisha',
    step7_label: 'Fanya mtihani', step8_label: 'Angalia cheti', step9_label: 'Pakua & shiriki',
    s1_eyebrow: 'Hatua ya 1', s1_title: 'Fikia ukurasa wa nyumbani', s1_desc: 'Tembelea ukurasa wa nyumbani wa Playmates. Utaona kategoria mbalimbali za michezo — bonyeza <strong>Lugha</strong> ili kufikia mitihani ya lugha.',
    s1_caption_strong: 'Ukurasa wa Nyumbani:', s1_caption: 'Bonyeza "Lugha" kwenye gridi ili kufikia mitihani na michezo yote ya lugha.',
    s2_eyebrow: 'Hatua ya 2', s2_title: 'Ingia kwenye akaunti yako', s2_desc: 'Weka barua pepe na nywila yako ili kufikia akaunti yako. Je, wewe ni mgeni Playmates? Unaweza kujiandikisha kupitia ukurasa huu.',
    s2_caption_strong: 'Ukurasa wa kuingia:', s2_caption: 'Weka barua pepe na nywila, kisha bonyeza "Ingia" ili kuendelea kwenye dashboard yako.',
    s2_notice: 'Weka siri taarifa zako za kuingia. Usishiriki nywila yako na mtu yeyote.',
    s3_eyebrow: 'Hatua ya 3', s3_title: 'Nenda kwenye Michezo ya Lugha & bonyeza "Fanya Mtihani"', s3_desc: 'Baada ya kuingia, utafika kwenye dashboard yako. Nenda kwenye sehemu ya Lugha na ubonyeze <strong>"Fanya Mtihani"</strong> ili kuanza mchakato wa usajili.',
    s3_caption_strong: 'Dashboard ya Lugha:', s3_caption: 'Bonyeza kitufe cha <strong>"Fanya Mtihani"</strong> kuanza. Hii itakupeleka Hatua ya 4 — kuchagua aina ya mtihani.',
    s3_notice: 'Kagua usahihi na maendeleo ya mada zako kabla ya kuanza — hii inakusaidia kutambua maeneo ya kurudia kwanza.',
    s4_eyebrow: 'Hatua ya 4', s4_title: 'Chagua aina ya mtihani', s4_desc: 'Chagua mtihani unaoendana na malengo yako. Chaguzi zote mbili zina maswali 40 na huchukua dakika 60 kukamilika.',
    s4_card1_title: 'Kiingereza cha Kitaaluma 🎓', s4_pill_q: 'Maswali 40', s4_pill_time: 'Dakika 60',
    s4_card1_desc: 'Kwa masomo ya chuo kikuu na kitaaluma. Hupima uelewa, uandishi, na mawasiliano rasmi.',
    s4_card2_title: 'Kiingereza cha Biashara 💼', s4_card2_desc: 'Kwa mawasiliano ya kitaalamu na mahali pa kazi. Huzingatia istilahi za biashara na uandishi vya kitaalamu.',
    s4_caption_strong: 'Chaguzi za mtihani:', s4_caption: 'Chagua Kiingereza cha Kitaaluma au cha Biashara. Unaweza kuchagua kimoja tu kwa kila kipindi. Baada ya kuchagua, utapelekwa kuingiza popcorn code yako.',
    s5_eyebrow: 'Hatua ya 5 · Hiari', s5_title: 'Tumia popcorn code 🍿', s5_desc: 'Ikiwa taasisi yako imetoa popcorn code, iweke hapa. Hii huunganisha mtihani wako na usajili wa taasisi yako. Ruka mbele ikiwa huna.',
    s5_card1_title: 'Unachohitaji', s5_card1_item1: '<strong>Popcorn code</strong> — inayotolewa na shule yako', s5_card1_item2: '<strong>Jina kamili</strong> — kama linavyopaswa kuonekana kwenye cheti chako', s5_card1_item3: '<strong>Jina la shule</strong> — jina la taasisi yako',
    s5_card2_title: 'Vyema kujua', s5_card2_desc: 'Aina ya mtihani inaunganishwa moja kwa moja na kodi yako. Hakikisha usahihi wa herufi — jina lako litaonekana kwenye cheti kama ulivyoingiza.',
    s5_caption_strong: 'Uwekaji wa kodi:', s5_caption: 'Ingiza kodi yako, jina kamili, na jina la shule. Aina ya mtihani inaunganishwa kiotomatiki kutoka kwa chaguo lako katika Hatua ya 4.',
    s6_eyebrow: 'Hatua ya 6', s6_title: 'Kagua taarifa zako & uthibitisho wa utambulisho', s6_desc: 'Kabla ya kuanza mtihani, utaona muhtasari wa maelezo yako na hali ya uthibitisho wa utambulisho wako. Ukurasa huu unaonekana sawa kwa kila mtu — lakini hali inategemea ikiwa hati zako zimethibitishwa.',
    s6_badge_verified: 'Utambulisho umethibitishwa', s6_verified_title: 'Uko tayari kuendelea', s6_verified_body: 'Nyaraka zako zimethibitishwa. Kitufe cha "Anza Mtihani" kimeamilishwa — unaweza kuanza mara moja.',
    s6_badge_unverified: 'Haijathibitishwa', s6_unverified_title: 'Uthibitisho unahitajika', s6_unverified_body: 'Utahitaji kukamilisha uthibitisho wa utambulisho kabla ya kitufe cha "Anza Mtihani" kuwa tayari. Fuata maelekezo ya skrini ili kuwasilisha kitambulisho au pasipoti.',
    s6_caption_strong: 'Ukurasa wa ukaguzi:', s6_caption: 'Inaonyesha maelezo ya mtihani, sheria, na hali yako ya uthibitisho. Kitufe cha "Anza Mtihani" hufanya kazi tu wakati utambulisho wako umethibitishwa.',
    s6_rules_title: 'Sheria za mtihani', s6_rule1: 'Kila swali linaweza kujibiwa mara moja tu.', s6_rule2: 'Alama za ufaulu ni <strong>70%</strong>. Matokeo yanaonekana mara moja.', s6_rule3: 'Kurudia mtihani kunaruhusiwa baada ya muda wa kusubiri wa siku <strong>7</strong>.',
    s6_conduct_title: 'Tabia wakati wa mtihani', s6_conduct1: 'Ni lazima uwe peke yako — hakuna watu wengine chumbani.', s6_conduct2: 'Hakuna madokezo, vitabu, simu, au vipokea sauti vinavyoruhusiwa.', s6_conduct3: 'Uso wako lazima uonekane wazi wakati wote.',
    s6_break_title: 'Ukivunja sheria', s6_break1: 'Mtihani wako unaweza kufutwa mara moja.', s6_break2: 'Matokeo yako hayatathibitishwa.', s6_break3: 'Ukiukaji wa mara kwa mara unaweza kusababisha kufungiwa kwa akaunti.',
    s7_eyebrow: 'Hatua ya 7', s7_title: 'Fanya mtihani', s7_desc: 'Mara tu unapobonyeza "Anza Mtihani," muda unaanza kuhesabiwa mara moja. Jibu kila swali kwa makini — kila swali hujibiwa mara moja tu.',
    s7_caption_strong: 'Mfumo wa mtihani:', s7_caption: 'Sehemu ya maendeleo iko juu, saa ya kuhesabu sekunde, kadi ya swali na chaguzi za majibu, na sehemu za kusogeza mbele/nyuma. Saa inakuwa nyekundu zikibaki dakika 2.',
    s7_qtype_title: 'Aina za maswali', s7_qtype1: '<strong>Jibu thabiti</strong> — maswali 10', s7_qtype2: '<strong>Jibu la kujenga</strong> — maswali 6', s7_qtype3: '<strong>Kuzungumza</strong> — maswali 2 (rudia kidokezo kwa wazi)', s7_qtype4: '<strong>Kusoma</strong> — maswali 2',
    s7_time_title: 'Maonyo ya muda', s7_time1: '<strong>Njano</strong> — zimebaki dakika 10', s7_time2: '<strong>Nyekundu inayowaka</strong> — zimebaki dakika 2', s7_time3: '<strong>Wasilisha kiotomatiki</strong> — wakati saa inapofika 0:00',
    s7_tab_title: 'Ukiukaji wa kubadili tab', s7_tab_desc: 'Kubadili kwenda tab nyingine kunaleta onyo. Baada ya <strong>ukiukaji 7</strong>, mtihani wako utafungwa kiotomatiki. Weka ukurasa wa mtihani wazi wakati wote.',
    s8_eyebrow: 'Hatua ya 8', s8_title: 'Angalia cheti chako', s8_desc: 'Pata alama 70% au zaidi na cheti kitatolewa kiotomatiki. Unaweza kukiangalia mara moja — lakini unachoweza kufanya nacho kinategemea ikiwa kimeidhinishwa.',
    s8_caption_strong: 'Sehemu ya vyeti:', s8_caption: 'Kila kadi inaonyesha jina lako, aina ya mtihani, alama, tarehe ya kutolewa, na hali ya sasa ya uthibitisho.',
    s8_cert_verified_badge: 'Imethibitishwa & imeidhinishwa', s8_cert_verified_title: 'Ufikiaji kamili', s8_cert_verified_body: 'Unaweza kuona maelezo kamili ya cheti, kupakua PDF, na kushiriki link ya cheti au QR code na waajiri au taasisi.',
    s8_cert_pending_badge: 'Uthibitisho unasubiriwa', s8_cert_pending_title: 'Kuangalia tu — hakuna kupakua bado', s8_cert_pending_body: 'Unaweza kuona maelezo ya cheti chako, lakini chaguzi za kupakua na kushiriki hazitapatikana hadi taasisi yako itakapoidhinisha. Angalia tena baada ya masaa 24–48.',
    s8_notice: 'Kama cheti chako kinaonyesha <strong>Haijathibitishwa</strong>, tafadhali subiri taasisi yako ikague na kuidhinisha. Hakuna hatua inayohitajika kutoka kwako.',
    s9_eyebrow: 'Hatua ya 9', s9_title: 'Pakua & shiriki cheti chako', s9_desc: 'Cheti chako kikishathibitishwa na kuidhinishwa, unaweza kukipakua kama PDF au kukishiriki kupitia link au QR code.',
    s9_caption_strong: 'Cheti kilichothibitishwa:', s9_caption: 'Inaonyesha jina lako, aina ya mtihani, alama, tarehe ya kutolewa, na ID ya kipekee ya cheti (k.m. CERT-ENRW-2026-00045) chenye QR code ya uthibitisho.',
    s9_download_title: 'Jinsi ya kupakua', s9_download1: 'Nenda kwenye Vyeti kutoka kwenye dashboard yako.', s9_download2: 'Bonyeza cheti kilichothibitishwa unachotaka kupakua.', s9_download3: 'Bonyeza <strong>Pakua</strong> au <strong>Chapisha</strong> ili kuhifadhi kama PDF.', s9_download4: 'Shiriki link au QR code — wapokeaji wanaweza kuthibitisha papo hapo.',
    s9_notice: 'Kila cheti kilichothibitishwa kina ID ya kipekee (k.m. <strong>CERT-ENRW-2026-00045</strong>). Mtu yeyote anaweza kuskeni QR code kwenye cheti ili kuthibitisha ukweli wake.',
    tips_eyebrow: 'Vidokezo vya kitaalamu', tips_title: 'Kabla, wakati & baada', tips_desc: 'Mambo machache ya kujiandaa kwa mafanikio siku ya mtihani.',
    tips_before_title: 'Kabla ya mtihani wako', tips_before1: 'Hakikisha una mtandao thabiti wa internet.', tips_before2: 'Jaribu maikrofoni yako — maswali ya kuzungumza yanaihitaji.', tips_before3: 'Funga tab na programu zisizo za lazima.', tips_before4: 'Tafuta mahali tulivu, pa faragha na penye mwanga mzuri.',
    tips_during_title: 'Wakati wa mtihani wako', tips_during1: 'Soma kila swali kwa makini kabla ya kujibu.', tips_during2: 'Usitumie muda mrefu sana kwenye swali moja.', tips_during3: 'Tulia — hii inapima kiwango chako cha sasa cha maarifa.', tips_during4: 'Weka ukurasa wa mtihani wazi kila wakati.',
    tips_after_title: 'Baada ya mtihani wako', tips_after1: 'Alama zako zinaonyeshwa <strong>papo hapo</strong> baada ya kuwasilisha.', tips_after2: 'Ufaulu (≥70%) na cheti chako kinatengenezwa kiotomatiki.', tips_after3: 'Uthibitisho kawaida huchukua <strong>masaa 24–48</strong>. Kupakua na kushiriki kunapatikana baada ya kuidhinishwa.', tips_after4: 'Kurudia mtihani kunapatikana baada ya siku <strong>7</strong> kama hukufaulu.',
    faq_eyebrow: 'FAQ', faq_title: 'Maswali yanayoulizwa mara kwa mara', faq_desc: 'Majibu ya haraka kwa maswali ya kawaida kuhusu mchakato wa mtihani.',
    faq_q1: 'Alama za ufaulu ni ngapi?', faq_a1: 'Unahitaji kupata 70% au zaidi ili kufaulu na kupata cheti.',
    faq_q2: 'Je, ninaweza kusimamisha mtihani?', faq_a2: 'Hapana. Mara tu unapoanza mtihani lazima ukamilishe kwa kipindi kimoja. Saa inahesabu mfululizo, kwa hivyo panga kutopingwa kwa dakika 60 kamili.',
    faq_q3: 'Je, ninaweza kurudia mtihani nikishindwa?', faq_a3: 'Ndiyo. Unaweza kurudia mtihani baada ya muda wa kusubiri wa siku 7 ikiwa ulipata chini ya 70%.',
    faq_q4: 'Nahitaji hati gani?', faq_a4: 'Unahitaji kitambulisho halali cha serikali au pasipoti kwa uthibitisho wa utambulisho. Kilichocho chote kinakubalika.',
    faq_q5: 'Maswali ya kuzungumza yanafanyaje kazi?', faq_a5: 'Kidokezo kitatokea kwenye skrini ili ukirudie au ukijibu. Bonyeza kitufe cha maikrofoni na uzungumze kwa wazi. Hakikisha maikrofoni yako inafanya kazi kabla ya kuanza.',
    faq_q6: 'Je, ninaweza kutumia vifaa vya nje?', faq_a6: 'Hapana. Kutumia madokezo, vitabu, simu, au nyenzo zozote za nje wakati wa mtihani kunaweza kusababisha kufutwa kwa matokeo na kufungiwa kwa akaunti.',
    faq_q7: 'Mbona siwezi kupakua cheti changu bado?', faq_a7: 'Chaguzi za kupakua na kushiriki zinapatikana tu baada ya taasisi yako kuthibitisha na kuidhinisha cheti chako. Hii kawaida huchukua masaa 24-48.',
    faq_q8: 'Je, cheti ni halali milele?', faq_a8: 'Ndiyo. Vyeti vya Playmates havina mwisho wa matumizi. Mtu yeyote anaweza kuthibitisha cheti chako wakati wowote kwa kuskeni QR code kwenye hati hiyo.',
    faq_q9: 'Je, ninaweza kushiriki cheti changu?', faq_a9: 'Ndiyo, kikishathibitishwa. Unaweza kushiriki link ya cheti moja kwa moja au kuruhusu wapokeaji kuskeni QR code kwenye PDF ili kuthibitisha ukweli wake.',
    footer_platform: 'Jukwaa la Mafunzo ya Lugha la Playmates · Mwongozo wa Mtahiniwa',
    footer_email: 'support@playmates.rw', footer_copyright: '© 2026 Playmates. Haki zote zimehifadhiwa.'
  }
};

// ── LANGUAGE STATE ──────────────────────────────────────────────
var currentLang = '<?php echo $lang; ?>';

function showToast(msg) {
  var old = document.querySelector('.toast-message');
  if (old) old.remove();
  var t = document.createElement('div');
  t.className = 'toast-message';
  t.textContent = msg;
  document.body.appendChild(t);
  setTimeout(function(){ t.remove(); }, 2500);
}

function applyLang(lang) {
  var dict = translations[lang];
  if (!dict) return;
  document.documentElement.lang = lang;
  document.querySelectorAll('[data-key]').forEach(function(el) {
    var key = el.getAttribute('data-key');
    if (dict[key] !== undefined && !el.classList.contains('lang-btn')) {
      if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
        el.placeholder = dict[key];
      } else {
        el.innerHTML = dict[key];
      }
    }
  });
  document.querySelectorAll('.lang-btn').forEach(function(btn) {
    btn.classList.toggle('active', btn.getAttribute('data-lang') === lang);
  });
  
  // Update PHP session via AJAX
  var xhr = new XMLHttpRequest();
  xhr.open('GET', 'set_lang.php?lang=' + lang, true);
  xhr.send();
}

function setLanguage(lang) {
  if (!translations[lang]) return;
  currentLang = lang;
  try { localStorage.setItem('playmates_lang', lang); } catch(e){}
  applyLang(lang);
  var msg = lang === 'rw' ? '🇷🇼 Imvugo yahinduwe mu Kinyarwanda'
          : lang === 'sw' ? '🇹🇿 Lugha imebadilishwa kwa Kiswahili'
          : '🇬🇧 Language switched to English';
  showToast(msg);
}

// ── LANGUAGE BUTTONS ────────────────────────────────────────────
document.querySelectorAll('.lang-btn').forEach(function(btn) {
  btn.addEventListener('click', function(e) {
    e.stopPropagation();
    setLanguage(btn.getAttribute('data-lang'));
  });
});

// ── INIT: load saved language ──────────────────────────────────
(function() {
  var saved;
  try { saved = localStorage.getItem('playmates_lang'); } catch(e){}
  var initial = (saved && translations[saved]) ? saved : '<?php echo $lang; ?>';
  applyLang(initial);
  currentLang = initial;
})();

// ── LIGHTBOX ────────────────────────────────────────────────────
function openLightbox(src, label, caption) {
  document.getElementById('lightboxImg').src = src;
  document.getElementById('lightboxLabel').textContent = label;
  document.getElementById('lightboxCaption').innerHTML = caption;
  document.getElementById('lightbox').classList.add('visible');
  document.body.style.overflow = 'hidden';
}
function closeLightbox() {
  document.getElementById('lightbox').classList.remove('visible');
  document.body.style.overflow = '';
}
function handleOverlayClick(e) {
  if (e.target === document.getElementById('lightbox')) closeLightbox();
}
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') closeLightbox();
});

// ── FAQ TOGGLE ──────────────────────────────────────────────────
function toggleFaq(el) {
  var item = el.closest('.faq-item');
  var isOpen = item.classList.contains('open');
  document.querySelectorAll('.faq-item.open').forEach(function(i) {
    i.classList.remove('open');
  });
  if (!isOpen) item.classList.add('open');
}
</script>
</body>
</html>