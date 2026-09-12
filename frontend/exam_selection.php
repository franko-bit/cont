
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  :root{
    --sand:#f5ede6;--sand-mid:#ede3d8;--sand-dark:#ddd0c2;
    --sage:#c6dfd5;--sage-mid:#8bbfad;--sage-dark:#3a7a68;--sage-deep:#225548;
    --ink:#1a1a18;--ink-mid:#4a4845;--muted:#8a8178;--soft:#b8b0a5;
    --surface:#faf8f5;--border:rgba(0,0,0,0.07);
    --radius-sm:8px;--radius:14px;--radius-lg:20px;
    --transition:0.25s cubic-bezier(.4,0,.2,1);
    --peach:#f5d6c4;
  }
  body{
    font-family:'DM Sans',sans-serif;
    font-size:14px;
    color:var(--ink);
    background:var(--sand);
    display:flex;
    align-items:center;
    justify-content:center;
    min-height:100vh;
    padding:24px;
  }
  .wrapper{width:100%;max-width:520px}
  .back-link{
    display:inline-flex;align-items:center;gap:6px;
    font-size:12px;font-weight:500;color:var(--muted);
    text-decoration:none;margin-bottom:20px;
    transition:color var(--transition);
  }
  .back-link:hover{color:var(--sage-dark)}
  .card{
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:var(--radius-lg);
    padding:32px;
    box-shadow:0 8px 40px rgba(26,26,24,0.07);
  }
  .card-header{margin-bottom:24px}
  .brand-row{display:flex;align-items:center;gap:8px;margin-bottom:16px}
  .brand-row img{width:28px;height:28px;border-radius:7px;object-fit:contain}
  .brand-row span{font-family:'DM Serif Display',serif;font-size:16px;color:var(--sage-deep)}
  .card-title{font-family:'DM Serif Display',serif;font-size:26px;color:var(--ink);letter-spacing:-0.3px;margin-bottom:6px}
  .card-sub{font-size:13px;color:var(--muted);line-height:1.7}
  .divider{height:1px;background:var(--border);margin:22px 0}

  .exam-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px}
  .exam-card{
    border:1.5px solid var(--sand-dark);
    border-radius:var(--radius);
    background:#fff;
    padding:20px 16px;
    cursor:pointer;
    transition:all var(--transition);
    position:relative;
    text-align:left;
    display:flex;flex-direction:column;gap:10px;
  }
  .exam-card:hover{border-color:var(--sage-mid);background:#f7faf9}
  .exam-card.selected{
    border-color:var(--sage-dark);
    background:#f0f8f5;
    box-shadow:0 0 0 3px rgba(58,122,104,0.12);
  }
  .exam-check{
    position:absolute;top:12px;right:12px;
    width:18px;height:18px;
    border-radius:50%;
    border:1.5px solid var(--sand-dark);
    background:#fff;
    display:flex;align-items:center;justify-content:center;
    transition:all var(--transition);
    flex-shrink:0;
  }
  .exam-card.selected .exam-check{
    background:var(--sage-dark);
    border-color:var(--sage-dark);
  }
  .exam-check svg{display:none}
  .exam-card.selected .exam-check svg{display:block}

  .exam-icon{
    width:38px;height:38px;border-radius:10px;
    display:flex;align-items:center;justify-content:center;
    font-size:20px;flex-shrink:0;
  }
  .exam-icon.academic{background:#e8f3f0;color:var(--sage-dark)}
  .exam-icon.business{background:#f5ede6;color:#a0603a}

  .exam-name{
    font-family:'DM Serif Display',serif;
    font-size:15px;color:var(--ink);
    letter-spacing:-0.1px;line-height:1.3;
  }
  .exam-desc{font-size:12px;color:var(--muted);line-height:1.6}

  .exam-meta{
    display:flex;flex-wrap:wrap;gap:5px;margin-top:4px;
  }
  .exam-tag{
    font-size:10px;font-weight:500;
    padding:3px 8px;border-radius:20px;
    letter-spacing:0.3px;text-transform:uppercase;
  }
  .exam-tag.sage{background:var(--sage);color:var(--sage-deep)}
  .exam-tag.sand{background:var(--sand-mid);color:var(--ink-mid)}
  .exam-tag.peach{background:var(--peach);color:#7a3020}

  .submit-btn{
    width:100%;padding:13px 16px;border:none;
    border-radius:var(--radius-sm);background:var(--ink);color:#fff;
    font-size:14px;font-weight:500;font-family:'DM Sans',sans-serif;
    cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:7px;
    transition:opacity var(--transition),transform var(--transition);
  }
  .submit-btn:hover:not(:disabled){opacity:0.85}
  .submit-btn:active:not(:disabled){transform:scale(0.99)}
  .submit-btn:disabled{opacity:0.45;cursor:not-allowed}
  .form-note{margin-top:14px;font-size:11px;color:var(--muted);text-align:center;line-height:1.7}

  @media(max-width:420px){
    .exam-grid{grid-template-columns:1fr}
    body{padding:16px}
    .card{padding:22px 18px}
    .card-title{font-size:22px}
  }
</style>

<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">

<body>
<div class="wrapper">
  <a href="#" class="back-link">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 15 15"><path d="M9 3L4 7.5L9 12"/></svg>
    Back to dashboard
  </a>

  <div class="card">
    <div class="card-header">
      <div class="brand-row">
        <img src="https://res.cloudinary.com/franklinrw/image/upload/v1755169717/kjk_bnrbmp.png" alt="Playmates">
        <span>Playmates</span>
      </div>
      <div class="card-title">Choose your exam 📝</div>
      <div class="card-sub">Select the exam type that matches your application. You can only choose one.</div>
    </div>

    <div class="divider"></div>

    <div class="exam-grid">
      <button type="button" class="exam-card" id="card-academic" onclick="selectExam('academic')">
        <div class="exam-check">
          <svg width="10" height="10" fill="none" stroke="#fff" stroke-width="2.5" viewBox="0 0 10 10"><path d="M1.5 5l2.5 2.5 4.5-4"/></svg>
        </div>
        <div class="exam-icon academic">
          <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 3L2 8l10 5 10-5-10-5z"/><path d="M2 8v6m20-6v6"/><path d="M7 10.5v5a5 5 0 0010 0v-5"/></svg>
        </div>
        <div>
          <div class="exam-name">Academic English</div>
          <div class="exam-desc">University and academic study readiness</div>
        </div>
        <div class="exam-meta">
          <span class="exam-tag peach">40 questions</span>
          <span class="exam-tag sand">60 min</span>
        </div>
      </button>

      <button type="button" class="exam-card" id="card-business" onclick="selectExam('business')">
        <div class="exam-check">
          <svg width="10" height="10" fill="none" stroke="#fff" stroke-width="2.5" viewBox="0 0 10 10"><path d="M1.5 5l2.5 2.5 4.5-4"/></svg>
        </div>
        <div class="exam-icon business">
          <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>
        </div>
        <div>
          <div class="exam-name">Business English</div>
          <div class="exam-desc">Professional and workplace communication</div>
        </div>
        <div class="exam-meta">
          <span class="exam-tag peach">40 questions</span>
          <span class="exam-tag sand">60 min</span>
        </div>
      </button>
    </div>

    <!-- NOTE: Only Academic English and Business English are available -->

    <button class="submit-btn" id="continueBtn" disabled onclick="handleContinue()">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 15 15"><path d="M6 3l5 4.5L6 12"/></svg>
      Continue to popcorn
    </button>
  </div>
</div>

<input type="hidden" id="selectedExam" value="">

<script>
function selectExam(type) {
  document.querySelectorAll('.exam-card').forEach(c => c.classList.remove('selected'));
  document.getElementById('card-' + type).classList.add('selected');
  document.getElementById('selectedExam').value = type;
  document.getElementById('continueBtn').disabled = false;
  document.getElementById('continueBtn').innerHTML =
    '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 15 15"><path d="M6 3l5 4.5L6 12"/></svg> Continue to popcorn';
}

function handleContinue() {
  const val = document.getElementById('selectedExam').value;
  if (!val) return;
  const btn = document.getElementById('continueBtn');
  btn.disabled = true;
  btn.innerHTML = 'Loading…';
  window.location.href = 'apply-popcorn.php?exam=' + encodeURIComponent(val);
}
</script>
</body>
