<?php
$lang  = $_GET['lang']  ?? 'en';
$level = $_GET['level'] ?? 1;
$topic = $_GET['topic'] ?? '';

$yaml_path   = "../content/level$level/" . basename($topic);
$words       = [];
$word_images = [];

if (file_exists($yaml_path)) {
    $content = file_get_contents($yaml_path);

    // Try yaml_parse first
    if (function_exists('yaml_parse')) {
        $data = yaml_parse($content);
        if (isset($data['exercises'])) {
            foreach ($data['exercises'] as $exercise) {
                if (in_array($exercise['type'] ?? '', ['translation','multiple_choice'])) {
                    $words[] = strtoupper($exercise['answer']);
                } elseif (($exercise['type'] ?? '') === 'flashcards' && isset($exercise['flashcards'])) {
                    foreach ($exercise['flashcards'] as $card) {
                        $key = strtoupper(trim($card['back']));
                        $words[] = $key;
                        if (!empty($card['image'])) {
                            $word_images[$key] = trim($card['image']);
                        }
                    }
                }
            }
        }
    }

    // Manual fallback — scan line by line
    if (empty($words)) {
        $lines    = explode("\n", $content);
        $cur_back = null;
        foreach ($lines as $line) {
            // back: "Inka"  or  back: Inka
            if (preg_match('/^\s*back:\s*["\']?(.+?)["\']?\s*$/', $line, $m)) {
                $cur_back = strtoupper(trim($m[1], " \"'\t\r"));
                $words[]  = $cur_back;
            }
            // image: "https://..."  — grab everything after "image:" strip quotes
            if ($cur_back && preg_match('/^\s*image:\s*["\']?(https?:\/\/.+?)["\']?\s*$/', $line, $m)) {
                $word_images[$cur_back] = trim($m[1], " \"'\t\r");
                $cur_back = null;
            }
        }
        $words = array_values(array_unique($words));
    }
}

if (empty($words)) {
    $topic_name = pathinfo($topic, PATHINFO_FILENAME);
    switch ($topic_name) {
        case 'animals':   $words = ["IMBWA","INJANGWE","INKA","IHENE","INKOKO","INTARE"]; break;
        case 'greetings': $words = ["MURAHO","MWARAMUTSE","URAKOZE","MURABEHO","AMAKURU"]; break;
        case 'numbers':   $words = ["RIMWE","KABIRI","GATATU","KANE","GATANU","GATANDATU","INDWI","UMUNANI","ICYENDA","ICUMI"]; break;
        case 'family':    $words = ["MAMA","PAPA","MUVANDIMWE","MUSHIKI","UMWANA","NYOGOKURU"]; break;
        case 'colors':    $words = ["UMUTUKU","UBURURU","UMUHONDO","ICYATSI","UMWERU","UMUKARA"]; break;
        default:          $words = ["MURAHO","IMBWA","RIMWE","MAMA","UMUTUKU"];
    }
}

$topic_name    = pathinfo($topic, PATHINFO_FILENAME);
$display_topic = ucwords(str_replace('-', ' ', $topic_name));
echo "<!-- WORDS: " . implode(', ', $words) . " -->\n";
echo "<!-- IMAGES: " . json_encode($word_images) . " -->\n";
echo "<!-- YAML_PATH: " . $yaml_path . " | EXISTS: " . (file_exists($yaml_path)?'YES':'NO') . " -->\n";
echo "<!-- YAML_PARSE: " . (function_exists('yaml_parse')?'YES':'NO') . " -->\n";
?>
<!DOCTYPE html>
<html lang="rw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title><?= $display_topic ?> · PLAYMATES</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<style>
:root {
    --neon:   #00ffa3;
    --gold:   #ffd166;
    --danger: #ff4d6d;
    --ink:    #ffffff;
    --dim:    rgba(255,255,255,0.4);
}

*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
html, body { width:100%; height:100%; overflow:hidden; background:#080810; }

#video {
    position:fixed; inset:0; width:100%; height:100%;
    object-fit:cover; z-index:0;
    transform:scaleX(-1);
    filter:brightness(0.5) saturate(0.7);
}

#canvas { position:fixed; inset:0; z-index:2; pointer-events:none; }
#pfx    { position:fixed; inset:0; z-index:3; pointer-events:none; }

/* HUD */
#hud {
    position:fixed; inset:0; z-index:10; pointer-events:none;
    display:flex; flex-direction:column;
    justify-content:space-between; align-items:center;
    padding:20px 20px 36px;
    padding-top: max(env(safe-area-inset-top, 20px), 20px);
}

#top-bar {
    width:100%; display:flex;
    justify-content:space-between; align-items:center;
}

.score-chip {
    display:flex; align-items:baseline; gap:5px;
    background:rgba(0,0,0,0.4); backdrop-filter:blur(12px);
    border:1px solid rgba(255,255,255,0.08);
    border-radius:100px; padding:6px 16px;
}
.score-num {
    font-family:'Syne', sans-serif; font-weight:800;
    font-size:1.45rem; line-height:1; color:var(--ink);
}
.score-num.pop { animation:npop .25s cubic-bezier(.34,1.56,.64,1); }
@keyframes npop { 50%{transform:scale(1.38)} }
.score-label {
    font-family:'DM Sans', sans-serif; font-size:.62rem;
    font-weight:500; color:var(--dim);
    text-transform:uppercase; letter-spacing:1.5px;
}

#close-btn {
    width:36px; height:36px; border-radius:50%;
    background:rgba(0,0,0,0.4); backdrop-filter:blur(12px);
    border:1px solid rgba(255,255,255,0.08);
    color:var(--dim); font-size:.85rem;
    display:flex; align-items:center; justify-content:center;
    text-decoration:none; pointer-events:all;
    transition:color .2s;
}
#close-btn:hover { color:var(--ink); }

/* word */
#word-display {
    font-family:'Syne', sans-serif; font-weight:800;
    font-size:clamp(2rem,8vw,3rem);
    letter-spacing:5px; text-transform:uppercase;
    color:var(--ink); text-align:center;
    transition:color .2s;
}
#word-display.fail { color:var(--danger); animation:shake .35s ease; }
#word-display.win  { color:var(--neon); }
@keyframes shake {
    25%{transform:translateX(-7px)} 75%{transform:translateX(7px)}
}

/* combo */
#combo-pop {
    position:fixed; top:22%; left:50%; transform:translateX(-50%);
    z-index:20; pointer-events:none; opacity:0;
    font-family:'Syne', sans-serif; font-weight:800;
    font-size:clamp(2.4rem,8vw,3.8rem); color:var(--gold);
    text-shadow:0 0 28px rgba(255,209,102,0.55);
    white-space:nowrap; transition:opacity .1s;
}
#combo-pop.show { opacity:1; animation:cpop .32s cubic-bezier(.34,1.56,.64,1); }
@keyframes cpop { from{transform:translateX(-50%) scale(.3)} to{transform:translateX(-50%) scale(1)} }

/* heard */
#heard {
    position:fixed; z-index:15; pointer-events:none;
    font-family:'DM Sans', sans-serif; font-weight:500;
    font-size:.75rem; letter-spacing:2px; text-transform:uppercase;
    color:var(--dim);
    background:rgba(0,0,0,0.55); backdrop-filter:blur(8px);
    border:1px solid rgba(255,255,255,0.09);
    padding:4px 13px; border-radius:100px;
    transform:translate(-50%,0); opacity:0; transition:opacity .15s;
    white-space:nowrap;
}
#heard.show { opacity:1; }
#heard.bad  { color:var(--danger); }

/* INTRO */
#intro {
    position:fixed; inset:0; z-index:400; background:#080810;
    display:flex; flex-direction:column;
    align-items:center; justify-content:center; padding:32px;
}
.intro-wrap {
    max-width:340px; width:100%;
    display:flex; flex-direction:column; align-items:center; gap:22px;
    text-align:center;
}
.intro-topic {
    font-family:'DM Sans', sans-serif; font-weight:500;
    font-size:.68rem; letter-spacing:3px; text-transform:uppercase;
    color:var(--neon); opacity:.65;
    animation:fadeup .4s ease both;
}
.intro-title {
    font-family:'Syne', sans-serif; font-weight:800;
    font-size:clamp(3rem,12vw,5rem); line-height:.88;
    color:var(--ink);
    animation:fadeup .4s .06s ease both;
}
.intro-hint {
    font-family:'DM Sans', sans-serif; font-size:.83rem;
    color:var(--dim); line-height:1.65; max-width:250px;
    animation:fadeup .4s .12s ease both;
}
#go-btn {
    width:100%; padding:15px;
    font-family:'Syne', sans-serif; font-weight:800;
    font-size:.88rem; letter-spacing:3px; text-transform:uppercase;
    color:#080810; background:var(--neon);
    border:none; border-radius:12px; cursor:pointer;
    box-shadow:0 0 28px rgba(0,255,163,0.2);
    transition:opacity .15s, transform .1s;
    animation:fadeup .4s .18s ease both;
}
#go-btn:active { opacity:.82; transform:scale(.98); }
.nosrwarn {
    font-family:'DM Sans', sans-serif; font-size:.72rem;
    color:var(--danger); display:none;
}
@keyframes fadeup { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:translateY(0)} }

/* FINISHED */
#finished {
    position:fixed; inset:0; z-index:399; background:#080810;
    display:none; flex-direction:column;
    align-items:center; justify-content:center; padding:32px;
}
.finish-wrap {
    max-width:340px; width:100%;
    display:flex; flex-direction:column; align-items:center; gap:22px;
    text-align:center;
}
.finish-emoji { font-size:3.2rem; }
#finished h1 {
    font-family:'Syne', sans-serif; font-weight:800;
    font-size:clamp(2.2rem,8vw,3.4rem); color:var(--ink);
}
.finish-scores { display:flex; gap:14px; width:100%; }
.fscore {
    flex:1; padding:14px 10px; text-align:center;
    background:rgba(255,255,255,0.04);
    border:1px solid rgba(255,255,255,0.07);
    border-radius:14px;
}
.fscore-label {
    font-family:'DM Sans', sans-serif; font-size:.58rem;
    font-weight:500; letter-spacing:2px; text-transform:uppercase;
    color:var(--dim); display:block; margin-bottom:3px;
}
.fscore-val {
    font-family:'Syne', sans-serif; font-weight:800;
    font-size:2.4rem; line-height:1; color:var(--ink);
}
#replay-btn {
    width:100%; padding:15px;
    font-family:'Syne', sans-serif; font-weight:800;
    font-size:.88rem; letter-spacing:3px; text-transform:uppercase;
    color:#080810; background:var(--neon);
    border:none; border-radius:12px; cursor:pointer;
    box-shadow:0 0 24px rgba(0,255,163,0.18);
    transition:opacity .15s;
}
#replay-btn:active { opacity:.82; }
.back-link {
    font-family:'DM Sans', sans-serif; font-size:.73rem;
    font-weight:500; color:var(--dim); text-decoration:none;
    transition:color .2s;
}
.back-link:hover { color:var(--ink); }
</style>
</head>
<body>

<video id="video" autoplay muted playsinline></video>
<canvas id="canvas"></canvas>
<canvas id="pfx"></canvas>
<div id="heard"></div>

<div id="hud">
    <div id="top-bar">
        <div class="score-chip">
            <span class="score-num" id="score-v">0</span>
            <span class="score-label">pts</span>
        </div>
        <a id="close-btn" href="choose-topic.php?lang=<?= $lang ?>&level=<?= $level ?>">✕</a>
    </div>
    <div id="word-display">—</div>
    <div></div>
</div>

<div id="combo-pop"></div>

<!-- FINISHED -->
<div id="finished">
    <div class="finish-wrap">
        <div class="finish-emoji">🏆</div>
        <h1>Wararangije!</h1>
        <div class="finish-scores">
            <div class="fscore">
                <span class="fscore-label">Amanota</span>
                <span class="fscore-val" id="fin-score">0</span>
            </div>
            <div class="fscore">
                <span class="fscore-label">Max Combo</span>
                <span class="fscore-val" id="fin-combo">0</span>
            </div>
        </div>
        <button id="replay-btn">Ongera Gukina</button>
        <a href="choose-topic.php?lang=<?= $lang ?>&level=<?= $level ?>" class="back-link">← Hitamo indi ngingo</a>
    </div>
</div>

<!-- INTRO -->
<div id="intro">
    <div class="intro-wrap">
        <span class="intro-topic"><?= htmlspecialchars($display_topic) ?> · Level <?= $level ?></span>
        <div class="intro-title">Vuga<br>Ijambo.</div>
        <p class="intro-hint">Impira izaza ikwegera. Ihagarara. Vuga ijambo — izakomeza.</p>
        <div class="nosrwarn" id="no-sr">⚠ Koresha Chrome cyangwa Edge</div>
        <button id="go-btn">Tangira</button>
    </div>
</div>

<script>
const WORDS       = <?= json_encode(array_values($words)) ?>;
const WORD_IMAGES = <?= json_encode((object)$word_images) ?>;

// Canvas
const C=document.getElementById("canvas"), ctx=C.getContext("2d");
const P=document.getElementById("pfx"),    pctx=P.getContext("2d");
function resize(){ C.width=P.width=window.innerWidth; C.height=P.height=window.innerHeight; }
resize(); window.addEventListener("resize",resize);

// Road
const ANG = -13*Math.PI/180;
const RDX=Math.cos(ANG), RDY=Math.sin(ANG), RNX=-RDY, RNY=RDX;
const ROAD_W = ()=>Math.min(C.width,C.height)*.50;
const ANCHOR = ()=>({x:C.width*.5, y:C.height*.78});

function toScreen(along,across){
    const a=ANCHOR(); return {x:a.x+RDX*along+RNX*across, y:a.y+RDY*along+RNY*across};
}
function centerAlong(){
    const a=ANCHOR(); return (C.width*.42-a.x)/RDX;
}

let dashOff=0, glowT=0;

function drawRoad(){
    const W=C.width,H=C.height,HALF=ROAD_W()/2,EXT=Math.max(W,H)*2;
    const a=ANCHOR();
    const pt=(al,ac)=>({x:a.x+RDX*al+RNX*ac, y:a.y+RDY*al+RNY*ac});
    const A=pt(-EXT,-HALF),B=pt(EXT,-HALF),D=pt(EXT,HALF),E=pt(-EXT,HALF);
    glowT+=.018;
    const ga=.5+.18*Math.sin(glowT);
    ctx.save();
    ctx.beginPath(); ctx.rect(0,0,W,H); ctx.clip();
    // asphalt
    ctx.beginPath();
    ctx.moveTo(A.x,A.y); ctx.lineTo(B.x,B.y); ctx.lineTo(D.x,D.y); ctx.lineTo(E.x,E.y);
    ctx.closePath();
    const rg=ctx.createLinearGradient(0,H*.55,0,H);
    rg.addColorStop(0,"rgba(8,8,18,.76)"); rg.addColorStop(1,"rgba(12,12,26,.92)");
    ctx.fillStyle=rg; ctx.fill();
    // edge glow
    ctx.lineWidth=3; ctx.strokeStyle=`rgba(0,255,163,${ga})`;
    ctx.shadowColor="rgba(0,255,163,.65)"; ctx.shadowBlur=14;
    ctx.beginPath(); ctx.moveTo(A.x,A.y); ctx.lineTo(B.x,B.y); ctx.stroke();
    ctx.beginPath(); ctx.moveTo(E.x,E.y); ctx.lineTo(D.x,D.y); ctx.stroke();
    // center dashes
    ctx.shadowBlur=0; ctx.lineWidth=1.5;
    ctx.strokeStyle="rgba(255,255,255,.12)";
    ctx.setLineDash([26,18]); ctx.lineDashOffset=-dashOff;
    ctx.beginPath();
    ctx.moveTo(a.x-RDX*EXT,a.y-RDY*EXT); ctx.lineTo(a.x+RDX*EXT,a.y+RDY*EXT);
    ctx.stroke(); ctx.setLineDash([]);
    ctx.restore();
    dashOff=(dashOff+1.5)%44;
}

// Image cache — uses image URL from YAML, no fallback to random images
const imgCache={};
function preload(word){
    if(imgCache[word]) return;
    const url = WORD_IMAGES[word] || null;
    if(!url) return; // no image for this word, card stays blank
    const img=new Image();
    img.src=url;
    imgCache[word]=img;
}

// Sphere
let sphere=null, wordQueue=[];
const SR=()=>Math.min(C.width,C.height)*.15;

function enqueue(){
    wordQueue=[...WORDS].sort(()=>Math.random()-.5);
    wordQueue.forEach(preload);
}
function spawnNext(){
    if(!wordQueue.length){ doFinish(); return; }
    const word=wordQueue.shift(); preload(word);
    sphere={word,along:C.width*.95,speed:0,state:"INCOMING",pulse:0,shake:0,shakeT:0,gone:0};
    const wd=document.getElementById("word-display");
    wd.textContent=word; wd.className="";
}

function updateSphere(){
    if(!sphere) return;
    const ca=centerAlong();
    if(sphere.state==="INCOMING"){
        const d=sphere.along-ca;
        sphere.speed=sphere.speed*.84+Math.max(2,d*.046)*.16;
        sphere.along-=sphere.speed;
        if(sphere.along<=ca){sphere.along=ca;sphere.speed=0;sphere.state="WAITING";}
    } else if(sphere.state==="WAITING"){
        sphere.pulse+=.05;
    } else if(sphere.state==="LEAVING"){
        sphere.speed=Math.min(sphere.speed+1.6,24);
        sphere.along-=sphere.speed; sphere.gone+=sphere.speed;
        if(sphere.gone>C.width*1.4){
            sphere=null;
            setTimeout(()=>{if(gState==="PLAYING") spawnNext();},320);
        }
    } else if(sphere.state==="FAIL"){
        sphere.shakeT+=.38;
        sphere.shake=Math.sin(sphere.shakeT*9)*9*Math.exp(-sphere.shakeT*.2);
        if(sphere.shakeT>5){sphere.state="WAITING";sphere.shake=0;sphere.shakeT=0;sphere.along=centerAlong();}
    }
}

function drawSphere(cx,cy,R,state,pulse,word){
    ctx.save();
    // shadow
    ctx.beginPath(); ctx.ellipse(cx+R*.1,cy+R*.72,R*.88,R*.16,ANG,0,Math.PI*2);
    ctx.fillStyle="rgba(0,0,0,.38)"; ctx.fill();
    // glow
    const gc=state==="FAIL"?`rgba(255,77,109,${.3+.12*Math.sin(pulse)})`
            :state==="LEAVING"?"rgba(0,255,163,.38)"
            :state==="WAITING"?`rgba(0,255,163,${.22+.15*Math.sin(pulse)})`
            :"rgba(0,255,163,.12)";
    const og=ctx.createRadialGradient(cx,cy,R*.6,cx,cy,R+18);
    og.addColorStop(0,gc); og.addColorStop(1,"transparent");
    ctx.beginPath(); ctx.arc(cx,cy,R+18,0,Math.PI*2); ctx.fillStyle=og; ctx.fill();
    // body
    const lx=cx-R*.38,ly=cy-R*.4;
    const g=ctx.createRadialGradient(lx,ly,R*.04,cx,cy,R);
    if(state==="FAIL"){
        g.addColorStop(0,"rgba(255,165,165,1)"); g.addColorStop(.4,"rgba(215,45,75,.95)");
        g.addColorStop(.8,"rgba(105,0,22,.95)"); g.addColorStop(1,"rgba(42,0,8,1)");
    } else if(state==="LEAVING"){
        g.addColorStop(0,"rgba(175,255,215,1)"); g.addColorStop(.4,"rgba(0,220,125,.95)");
        g.addColorStop(.8,"rgba(0,90,50,.95)");  g.addColorStop(1,"rgba(0,25,16,1)");
    } else {
        g.addColorStop(0,"rgba(175,255,240,1)"); g.addColorStop(.28,"rgba(0,205,170,.95)");
        g.addColorStop(.65,"rgba(38,65,185,.9)"); g.addColorStop(1,"rgba(12,6,50,1)");
    }
    ctx.beginPath(); ctx.arc(cx,cy,R,0,Math.PI*2); ctx.fillStyle=g; ctx.fill();
    // specular
    const sg=ctx.createRadialGradient(lx,ly,0,lx,ly,R*.46);
    sg.addColorStop(0,"rgba(255,255,255,.58)"); sg.addColorStop(1,"rgba(255,255,255,0)");
    ctx.beginPath(); ctx.arc(cx,cy,R,0,Math.PI*2); ctx.fillStyle=sg; ctx.fill();
    // ring
    const rc=state==="FAIL"?"rgba(255,77,109,.85)"
            :state==="LEAVING"?"rgba(0,255,163,.85)"
            :`rgba(130,255,225,${.45+.28*Math.sin(pulse)})`;
    ctx.shadowColor=rc; ctx.shadowBlur=state==="WAITING"?16:6;
    ctx.strokeStyle=rc; ctx.lineWidth=1.8;
    ctx.beginPath(); ctx.arc(cx,cy,R,0,Math.PI*2); ctx.stroke();
    ctx.shadowBlur=0;
    // word text
    const fs=Math.max(10,Math.round(R*.36));
    ctx.font=`800 ${fs}px 'Syne',sans-serif`;
    ctx.textAlign="center"; ctx.textBaseline="middle";
    ctx.shadowColor="rgba(0,0,0,.75)"; ctx.shadowBlur=5;
    ctx.fillStyle=state==="FAIL"?"#ffb0c0":state==="LEAVING"?"#aaffd8":"#fff";
    ctx.fillText(state==="LEAVING"?"✓":word,cx,cy);
    ctx.restore();
}

// Image card — clean, just the photo
function drawImageCard(cx,cy,R,state,pulse){
    if(!sphere) return;
    const W=R*1.7, H=R*1.7;
    const x=cx-W/2;
    const bob=state==="WAITING"?Math.sin(pulse*.8)*4:0;
    const y=cy-R-H-14+bob;
    const r=12;
    ctx.save();
    // shadow
    ctx.shadowColor="rgba(0,0,0,.45)"; ctx.shadowBlur=18; ctx.shadowOffsetY=5;
    ctx.beginPath(); ctx.roundRect(x,y,W,H,r);
    ctx.fillStyle="#0c0c1c"; ctx.fill();
    ctx.shadowBlur=0; ctx.shadowOffsetY=0;
    // border
    const bc=state==="FAIL"?"rgba(255,77,109,.6)"
            :state==="LEAVING"?"rgba(0,255,163,.6)"
            :state==="WAITING"?`rgba(0,255,163,${.28+.18*Math.sin(pulse)})`
            :"rgba(255,255,255,.08)";
    ctx.strokeStyle=bc; ctx.lineWidth=1.5;
    ctx.shadowColor=bc; ctx.shadowBlur=state==="WAITING"?8:0;
    ctx.beginPath(); ctx.roundRect(x,y,W,H,r); ctx.stroke();
    ctx.shadowBlur=0;
    // image
    const pad=5;
    ctx.save();
    ctx.beginPath(); ctx.roundRect(x+pad,y+pad,W-pad*2,H-pad*2,r-2); ctx.clip();
    const img=imgCache[sphere.word];
    if(img&&img.complete&&img.naturalWidth>0){
        ctx.drawImage(img,x+pad,y+pad,W-pad*2,H-pad*2);
        if(state==="FAIL"){ctx.fillStyle="rgba(255,60,90,.2)"; ctx.fillRect(x+pad,y+pad,W-pad*2,H-pad*2);}
    } else {
        ctx.fillStyle="#141428"; ctx.fillRect(x+pad,y+pad,W-pad*2,H-pad*2);
    }
    ctx.restore();
    // connector
    ctx.strokeStyle=bc; ctx.lineWidth=1.2;
    ctx.setLineDash([4,4]); ctx.globalAlpha=.38;
    ctx.beginPath(); ctx.moveTo(cx,y+H); ctx.lineTo(cx,cy-R);
    ctx.stroke(); ctx.setLineDash([]); ctx.globalAlpha=1;
    ctx.restore();
}

function renderSphere(){
    if(!sphere) return;
    const pos=toScreen(sphere.along,0);
    const cx=pos.x+(sphere.state==="FAIL"?sphere.shake:0), cy=pos.y, R=SR();
    if(sphere.state!=="INCOMING") drawImageCard(cx,cy,R,sphere.state,sphere.pulse);
    drawSphere(cx,cy,R,sphere.state,sphere.pulse,sphere.word);
}

// Particles
let parts=[];
const COLS=["#00ffa3","#ffd166","#ff3cac","#a0f0ff","#fff"];
function burst(x,y,n=20){
    for(let i=0;i<n;i++){
        const a=(Math.PI*2/n)*i+Math.random()*.7, s=2+Math.random()*8;
        parts.push({x,y,vx:Math.cos(a)*s,vy:Math.sin(a)*s-2,life:1,
            dec:.018+Math.random()*.014, r:2+Math.random()*4,
            col:COLS[Math.floor(Math.random()*COLS.length)],
            sq:Math.random()>.5, rot:0, rv:(Math.random()-.5)*.25});
    }
}
function drawParts(){
    pctx.clearRect(0,0,P.width,P.height);
    parts=parts.filter(p=>{
        p.x+=p.vx; p.y+=p.vy; p.vy+=.16; p.vx*=.99;
        p.life-=p.dec; p.rot+=p.rv;
        if(p.life<=0) return false;
        pctx.save(); pctx.globalAlpha=p.life*p.life;
        pctx.translate(p.x,p.y); pctx.rotate(p.rot); pctx.fillStyle=p.col;
        p.sq?pctx.fillRect(-p.r/2,-p.r/2,p.r,p.r):(pctx.beginPath(),pctx.arc(0,0,p.r,0,Math.PI*2),pctx.fill());
        pctx.restore(); return true;
    });
}

// Audio
let actx,mGain,lpf,beatTimer,beatN=0;
const BPM=100,BEAT=(60/BPM)*1000;
function buildAudio(){
    if(actx) actx.close();
    actx=new(window.AudioContext||window.webkitAudioContext)();
    mGain=actx.createGain(); mGain.gain.value=.36;
    lpf=actx.createBiquadFilter(); lpf.type="lowpass"; lpf.frequency.value=22000;
    mGain.connect(lpf); lpf.connect(actx.destination);
    beatN=0; clearInterval(beatTimer); beatTimer=setInterval(beat,BEAT);
}
function beat(){
    beatN++; const t=actx.currentTime;
    if(beatN%4===1||beatN%4===3){
        const o=actx.createOscillator(),g=actx.createGain();
        o.connect(g);g.connect(mGain);o.type="sine";
        o.frequency.setValueAtTime(130,t);o.frequency.exponentialRampToValueAtTime(32,t+.17);
        g.gain.setValueAtTime(.58,t);g.gain.exponentialRampToValueAtTime(.001,t+.24);
        o.start(t);o.stop(t+.25);
    }
    if(beatN%4===2||beatN%4===0){
        const b=actx.createBuffer(1,actx.sampleRate*.09,actx.sampleRate);
        const d=b.getChannelData(0);
        for(let i=0;i<d.length;i++) d[i]=(Math.random()*2-1)*Math.exp(-i/(actx.sampleRate*.038));
        const s=actx.createBufferSource(),g=actx.createGain();
        s.buffer=b;s.connect(g);g.connect(mGain);
        g.gain.setValueAtTime(.35,t);g.gain.exponentialRampToValueAtTime(.001,t+.09);
        s.start(t);
    }
}
function playOk(){
    if(!actx) return; const t=actx.currentTime;
    [0,.06,.12].forEach((d,i)=>{
        const o=actx.createOscillator(),g=actx.createGain();
        o.connect(g);g.connect(mGain);o.type="sine";
        o.frequency.value=[1046,1318,1568][i];
        g.gain.setValueAtTime(.24,t+d);g.gain.exponentialRampToValueAtTime(.001,t+d+.22);
        o.start(t+d);o.stop(t+d+.23);
    });
}
function playBad(){
    if(!actx) return; const t=actx.currentTime;
    const o=actx.createOscillator(),g=actx.createGain();
    o.connect(g);g.connect(mGain);o.type="sawtooth";
    o.frequency.setValueAtTime(165,t);o.frequency.exponentialRampToValueAtTime(42,t+.25);
    g.gain.setValueAtTime(.3,t);g.gain.exponentialRampToValueAtTime(.001,t+.27);
    o.start(t);o.stop(t+.28);
}
function setMuffle(on){
    if(!actx) return;
    lpf.frequency.setTargetAtTime(on?270:22000,actx.currentTime,.18);
    mGain.gain.setTargetAtTime(on?.16:.36,actx.currentTime,.18);
}

// Speech
const SR_API=window.SpeechRecognition||window.webkitSpeechRecognition;
let recog=null,srOn=false;
function initSR(){
    if(!SR_API) return;
    recog=new SR_API();
    recog.continuous=true;recog.interimResults=true;recog.lang="rw-RW";
    recog.onresult=e=>{
        for(let i=e.resultIndex;i<e.results.length;i++){
            const txt=e.results[i][0].transcript.toUpperCase().trim();
            onHeard(txt,e.results[i].isFinal);
        }
    };
    recog.onend=()=>{srOn=false;if(gState!=="IDLE") setTimeout(startSR,300);};
    recog.onerror=e=>{srOn=false;if(e.error!=="no-speech"&&e.error!=="aborted") setTimeout(startSR,400);};
}
function startSR(){if(!recog||srOn) return;try{recog.start();srOn=true;}catch(e){}}
function stopSR(){if(!recog||!srOn) return;try{recog.stop();}catch(e){}srOn=false;}
function onHeard(text,final){
    if(gState==="IDLE"||!sphere||sphere.state!=="WAITING") return;
    showHeard(text,false);
    const ws=text.split(/\s+/),tgt=sphere.word;
    if(ws.some(w=>w===tgt||lev(w,tgt)<=2)) correct();
    else if(final) wrong(text);
}
function lev(a,b){
    const m=a.length,n=b.length,dp=Array(m+1).fill(0).map(()=>Array(n+1).fill(0));
    for(let i=0;i<=m;i++) dp[i][0]=i;
    for(let j=0;j<=n;j++) dp[0][j]=j;
    for(let i=1;i<=m;i++) for(let j=1;j<=n;j++)
        dp[i][j]=a[i-1]===b[j-1]?dp[i-1][j-1]:1+Math.min(dp[i-1][j],dp[i][j-1],dp[i-1][j-1]);
    return dp[m][n];
}

// Game
let gState="IDLE",score=0,combo=0,maxCombo=0;
function correct(){
    if(!sphere||sphere.state!=="WAITING") return;
    sphere.state="LEAVING";sphere.speed=3;sphere.gone=0;
    hideHeard();
    document.getElementById("word-display").className="win";
    const pos=toScreen(sphere.along,0); burst(pos.x,pos.y,22);
    playOk();setMuffle(false);
    combo++;maxCombo=Math.max(maxCombo,combo);
    score+=10+combo*5;updateScore();showCombo();
}
function wrong(heard){
    if(!sphere||sphere.state!=="WAITING") return;
    sphere.state="FAIL";sphere.shakeT=0;
    showHeard(heard,true);
    document.getElementById("word-display").className="fail";
    setTimeout(()=>{const wd=document.getElementById("word-display");if(wd.className==="fail")wd.className="";},380);
    playBad();setMuffle(true);combo=0;updateScore();
}
function updateScore(){
    const el=document.getElementById("score-v");
    el.textContent=score;
    el.classList.remove("pop");void el.offsetWidth;el.classList.add("pop");
}
let comboT;
function showCombo(){
    if(combo<2) return;
    const el=document.getElementById("combo-pop");
    el.textContent=combo>=6?`🔥 ×${combo}`:{2:"×2",3:"×3",4:"🔥 ×4",5:"🔥🔥 ×5"}[combo]||`×${combo}`;
    el.classList.remove("show");void el.offsetWidth;el.classList.add("show");
    clearTimeout(comboT);comboT=setTimeout(()=>el.classList.remove("show"),1000);
}
const heardEl=document.getElementById("heard");
let heardT;
function showHeard(text,bad){
    if(!sphere) return;
    const R=SR(),pos=toScreen(sphere.along,0);
    heardEl.textContent=text;
    heardEl.style.left=pos.x+"px";
    heardEl.style.top=(pos.y+R+26)+"px";
    heardEl.className="show"+(bad?" bad":"");
    clearTimeout(heardT);heardT=setTimeout(hideHeard,1500);
}
function hideHeard(){heardEl.className="";}
function doFinish(){
    gState="IDLE";stopSR();clearInterval(beatTimer);
    document.getElementById("fin-score").textContent=score;
    document.getElementById("fin-combo").textContent=maxCombo;
    document.getElementById("finished").style.display="flex";
    burst(innerWidth/2,innerHeight/2,28);
    setTimeout(()=>burst(innerWidth*.3,innerHeight*.38,16),260);
    setTimeout(()=>burst(innerWidth*.7,innerHeight*.38,16),460);
    fetch('../backend/progressService.php',{method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'action=save_game&score='+score+'&combo='+maxCombo+'&topic=<?= $topic ?>'}).catch(()=>{});
}

// Loop
function loop(){
    ctx.clearRect(0,0,C.width,C.height);
    drawRoad();updateSphere();renderSphere();drawParts();
    requestAnimationFrame(loop);
}

// Boot
function startGame(){
    score=0;combo=0;maxCombo=0;gState="PLAYING";
    sphere=null;wordQueue=[];parts=[];
    updateScore();hideHeard();
    document.getElementById("word-display").textContent="—";
    document.getElementById("word-display").className="";
    document.getElementById("finished").style.display="none";
    enqueue();buildAudio();startSR();
    setTimeout(spawnNext,600);
}

document.getElementById("replay-btn").addEventListener("click",startGame);
if(!SR_API) document.getElementById("no-sr").style.display="block";

document.getElementById("go-btn").addEventListener("click",async()=>{
    document.getElementById("intro").style.display="none";
    try{
        const s=await navigator.mediaDevices.getUserMedia({video:{facingMode:"user"},audio:true});
        document.getElementById("video").srcObject=s;
        s.getAudioTracks().forEach(t=>t.stop());
    }catch(e){
        document.getElementById("video").style.display="none";
        try{const m=await navigator.mediaDevices.getUserMedia({audio:true});m.getTracks().forEach(t=>t.stop());}catch(e2){}
    }
    initSR();requestAnimationFrame(loop);startGame();
});
</script>
</body>
</html>