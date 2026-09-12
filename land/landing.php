<?php
// Start session to track language
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get language from URL or session
$lang = isset($_GET['lang']) ? $_GET['lang'] : (isset($_SESSION['user_lang']) ? $_SESSION['user_lang'] : 'en');

// Validate language
if (!in_array($lang, ['en', 'rw', 'sw'])) {
    $lang = 'en';
}

// Store in session
$_SESSION['user_lang'] = $lang;

// Define translations
$translations = [
    'en' => [
        'nav_home' => 'Home',
        'nav_exams' => 'Certifications',
        'nav_about' => 'Our Story',
        'nav_lessons' => 'Play & Learn',
        'nav_download' => 'Get the App',
        'login_btn' => 'Sign In',
        'eyebrow_text' => 'Where Curiosity Meets Mastery',
        'hero_title' => 'Sharpen Your Skills. Play Your Way.',
        'hero_sub' => 'Playmates turns languages, coding, and professional skills into engaging game-based challenges you can fit around your real life.',
        'start_playing_btn' => 'Start Playing',
        'community_btn' => 'Join the Community',
        'video_label' => 'Live Sessions',
        'collection_eyebrow' => 'The Library',
        'collection_title' => 'A Universe of Learning',
        'collection_desc' => '111+ challenges spanning languages, coding, development goals, and core competencies — built for ambitious adults who learn best by doing.',
        'lang_label' => 'Languages',
        'lang_value' => '4 Global Languages',
        'dev_label' => 'Development',
        'dev_value' => '29 Strategy Challenges',
        'coding_label' => 'Coding',
        'coding_value' => '90 Logic Sets',
        'classroom_label' => 'Core Skills',
        'classroom_value' => '6 Subject Areas',
        'why_eyebrow' => 'Why Playmates Matters',
        'why_title' => 'Education That Adapts to You',
        'why_sub' => 'We believe learning should fit your life, not the other way around. Playmates combines the science of learning with the joy of play to create experiences that stick.',
        'why_point1_title' => 'For Every Learner',
        'why_point1_desc' => 'Whether you\'re a student, professional, or lifelong learner — our games adapt to your pace and level, making growth accessible to everyone.',
        'why_point2_title' => 'Skills That Last',
        'why_point2_desc' => 'We don\'t just teach facts. We build confidence, problem-solving abilities, and a love for learning that extends far beyond the screen.',
        'why_point3_title' => 'Real-World Impact',
        'why_point3_desc' => 'Every game is designed around practical skills — from speaking a new language to writing your first line of code. Learning that opens doors.',
        'quote_text' => '"I picked up conversational Swahili in two months. The game format kept me consistent in a way traditional apps never did."',
        'quote_author' => '— Playmates learner, Kigali',
        'stat_games' => 'CHALLENGES',
        'stat_subjects' => 'SKILL AREAS',
        'stat_langs' => 'LANGUAGES',
        'download_eyebrow' => 'Get Started',
        'download_title' => 'Your Next Skill Starts Here',
        'download_sub' => 'Available on iOS and Android. Free to begin, with new challenges added every week.',
        'start_playing_btn2' => 'Start Playing',
        'download_btn' => 'Download the App',
        'footer_games' => 'The Games',
        'footer_schools' => 'Playmates for Teams',
        'footer_about' => 'Our Story',
        'footer_download' => 'Get the App',
        'copyright' => '© 2026 Playmates'
    ],
    'rw' => [
        'nav_home' => 'Ahabanza',
        'nav_exams' => 'Impamyabumenyi',
        'nav_about' => 'Amateka Yacu',
        'nav_lessons' => 'Kina & Wige',
        'nav_download' => 'Yimanurire App',
        'login_btn' => 'Injira',
        'eyebrow_text' => 'Aho Amatsiko Ahurira n\'Ubumenyi Nyakuri',
        'hero_title' => 'Tyaza Ubumenyi Bwawe. Kina mu Buryo Bwawe.',
        'hero_sub' => 'Playmates ihindura indimi, gukora kodi, n\'ubumenyi bw\'akazi mo imikino ishimishije kandi ifite imbogamizi ushobora gukora ijyanye n\'ubuzima bwawe bwa buri munsi.',
        'start_playing_btn' => 'Tangira Gukina',
        'community_btn' => 'Yinjire mu Muryango',
        'video_label' => 'Imbonankubone',
        'collection_eyebrow' => 'Isomero',
        'collection_title' => 'Isi Nshya y\'Imyigire',
        'collection_desc' => 'Imbogamizi zirenga 111 zikubiyemo indimi, gukora kodi, intego z\'iterambere, n\'ubumenyi bw\'ibanze — byubakiwe abantu bakuru bafite intego barajwe ishinga no kwigira mu bikorwa.',
        'lang_label' => 'Indimi',
        'lang_value' => 'Indimi 4 z\'Isi',
        'dev_label' => 'Iterambere',
        'dev_value' => 'Imbogamizi 29 z\'Icyerekezo',
        'coding_label' => 'Kodi',
        'coding_value' => 'Uburyo bwa Logika 90',
        'classroom_label' => 'Ubumenyi bw\'Ibanze',
        'classroom_value' => 'Ibyiciro 6 by\'Amasomo',
        'why_eyebrow' => 'Impamvu Playmates ari ingenzi',
        'why_title' => 'Uburezi Buhuje n\'Ibyo Ukeneye',
        'why_sub' => 'Twizera ko uburezi bugomba guhujwa n\'ubuzima bwawe, si ko ubuzima bugahujwa n\'uburezi. Playmates ihuza ubumenyi bw\'uburezi n\'ishimiye ryo gukina kugira ngo habeho ibyigisho bikomeza.',
        'why_point1_title' => 'Ku Mwigishwa Wese',
        'why_point1_desc' => 'Waba uri umunyeshuri, umwuga, cyangwa wigisha ubwawe — imikino yacu ihuza n\'urugendo rwawe n\'ubumenyi bwawe, bigatuma gukura buba kw\'abantu bose.',
        'why_point2_title' => 'Ubumenyi Burambye',
        'why_point2_desc' => 'Ntabwo twigisha ibintu gusa. Tubaka icyizere, ubushobozi bwo gukemura ibibazo, n\'urukundo rw\'uburezi rurenze urugari rwa screen.',
        'why_point3_title' => 'Ingaruka ku Buzima Nyirizima',
        'why_point3_desc' => 'Buri mukino ugana ku bumenyi bufatika — kuva mu kuvuga ururimi rushya kugeza ku kwandika umurongo wa mbere wa code. Uburezi bufungura amarembo.',
        'quote_text' => '"Namenye Igiswahili cyo kuganira mu miezi ibiri gusa. Imiterere y\'umukino yamfashije guhozaho mu buryo amapfashanyigisho ya gakondo atigeze anyandikira."',
        'quote_author' => '— Umunyeshuri wa Playmates, Kigali',
        'stat_games' => 'IMBOGAMIZI',
        'stat_subjects' => 'URWEGO RW\'UBUMENYI',
        'stat_langs' => 'INDIMI',
        'download_eyebrow' => 'Tangira Ako Kanya',
        'download_title' => 'Ubumenyi Bwawe Bukurikira Butangirira Hano',
        'download_sub' => 'Iboneka kuri iOS na Android. Gutangira ni ubuntu, kandi imbogamizi nshya zongerwamo buri cyumweru.',
        'start_playing_btn2' => 'Tangira Gukina',
        'download_btn' => 'Yimanurire App',
        'footer_games' => 'Imikino',
        'footer_schools' => 'Playmates ku Matsinda',
        'footer_about' => 'Amateka Yacu',
        'footer_download' => 'Yimanurire App',
        'copyright' => '© 2026 Playmates'
    ],
    'sw' => [
        'nav_home' => 'Nyumbani',
        'nav_exams' => 'Vyeti',
        'nav_about' => 'Hadithi Yetu',
        'nav_lessons' => 'Cheza & Ujifunze',
        'nav_download' => 'Pata App',
        'login_btn' => 'Ingia',
        'eyebrow_text' => 'Ambapo Udadisi Unakutana na Umahiri',
        'hero_title' => 'Noa Ujuzi Wako. Cheza kwa Njia Yako.',
        'hero_sub' => 'Playmates inabadilisha lugha, usimbaji (coding), na ujuzi wa kitaalamu kuwa changamoto za kusisimua zinazozingatia michezo unazoweza kuzingatia katika maisha yako halisi.',
        'start_playing_btn' => 'Anza Kucheza',
        'community_btn' => 'Jiunge na Jumuiya',
        'video_label' => 'Vipindi Mubashara',
        'collection_eyebrow' => 'Maktaba',
        'collection_title' => 'Ulimwengu wa Jifunze',
        'collection_desc' => 'Zaidi ya changamoto 111+ zinazojumuisha lugha, usimbaji, malengo ya maendeleo, na ujuzi wa kimsingi — zilizojengwa kwa ajili ya watu wazima wenye malengo wanaojifunza vyema kwa vitendo.',
        'lang_label' => 'Lugha',
        'lang_value' => 'Lugha 4 za Kimataifa',
        'dev_label' => 'Maendeleo',
        'dev_value' => 'Changamoto 29 za Kimkakati',
        'coding_label' => 'Usimbaji',
        'coding_value' => 'Seti 90 za Mantiki',
        'classroom_label' => 'Ujuzi wa Msingi',
        'classroom_value' => 'Maeneo 6 ya Masomo',
        'why_eyebrow' => 'Kwa nini Playmates ni Muhimu',
        'why_title' => 'Elimu Inayokubadilika',
        'why_sub' => 'Tunaamini elimu inafaa kuendana na maisha yako, si maisha kuendana na elimu. Playmates inachanganya sayansi ya kujifunza na furaha ya kucheza kuunda uzoefu unaodumu.',
        'why_point1_title' => 'Kwa Kila Mwanafunzi',
        'why_point1_desc' => 'Iwe ni mwanafunzi, mtaalamu, au mwanafunzi wa maisha yote — michezo yetu inabadilika kulingana na kasi na kiwango chako, kufanya ukuaji uweze kufikiwa na kila mtu.',
        'why_point2_title' => 'Stadi za Kudumu',
        'why_point2_desc' => 'Hatufundishi ukweli tu. Tunajenga ujasiri, uwezo wa kutatua matatizo, na upendo wa kujifunza unaoenda mbali zaidi ya skrini.',
        'why_point3_title' => 'Athari Halisi',
        'why_point3_desc' => 'Kila mchezo umeundwa kuzingira stadi za vitendo — kutoka kuzungumza lugha mpya hadi kuandika mstari wako wa kwanza wa kodi. Kujifunza kunachanua milango.',
        'quote_text' => '"Nilijifunza Kiswahili cha mazungumzo katika miezi miwili. Mfumo wa mchezo ulinifanya niwe thabiti kwa njia ambayo programu za kawaida hazikuwahi kufanya."',
        'quote_author' => '— Mwanafunzi wa Playmates, Kigali',
        'stat_games' => 'CHANGAMOTO',
        'stat_subjects' => 'MAENEO YA UJUZI',
        'stat_langs' => 'LUGHA',
        'download_eyebrow' => 'Anza Sasa',
        'download_title' => 'Ujuzi Wako Unaofuata Unaanza Hapa',
        'download_sub' => 'Inapatikana kwenye iOS na Android. Ni bure kuanza, na changamoto mpya zinaongezwa kila wiki.',
        'start_playing_btn2' => 'Anza Kucheza',
        'download_btn' => 'Pakua App',
        'footer_games' => 'Michezo',
        'footer_schools' => 'Playmates kwa Timu',
        'footer_about' => 'Hadithi Yetu',
        'footer_download' => 'Pata App',
        'copyright' => '© 2026 Playmates'
    ]
];

// Helper function to get translation
function t($key) {
    global $translations, $lang;
    return isset($translations[$lang][$key]) ? $translations[$lang][$key] : (isset($translations['en'][$key]) ? $translations['en'][$key] : $key);
}

// Helper function to get language switcher HTML
function getLangSwitcher($currentLang) {
    $flags = [
        'en' => ['flag' => 'https://flagcdn.com/us.svg', 'label' => 'EN'],
        'rw' => ['flag' => 'https://flagcdn.com/rw.svg', 'label' => 'RW'],
        'sw' => ['flag' => 'https://flagcdn.com/tz.svg', 'label' => 'SW']
    ];
    $html = '<div class="lang-switch" role="group" aria-label="Language switcher">';
    foreach ($flags as $code => $data) {
        $active = $code === $currentLang ? ' active' : '';
        $html .= '<button class="lang-option' . $active . '" onclick="setLang(\'' . $code . '\')">';
        $html .= '<img src="' . $data['flag'] . '" alt="' . $code . ' flag" class="flag-icon"> ';
        $html .= $data['label'];
        $html .= '</button>';
    }
    $html .= '</div>';
    return $html;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
  <title>Playmates — Learn Through Play | Educational Game Collection</title>
  <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400;0,500;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --green-primary: #2c6e4f;
      --green-dark: #235a41;
      --green-soft: #4f8b6c;
      --green-mist: #e2f0ea;
      --white: #ffffff;
      --text-light: rgba(255,255,255,0.92);
      --border-light: rgba(255,255,255,0.45);
      --shadow-subtle: 0 12px 28px rgba(0,0,0,0.12);
      --card-bg: #ffffff;
      --card-border: rgba(44,110,79,0.18);
      --text-dark: #1e2f28;
      --text-muted: #4a5b52;
      --page-bg: #fafef7;
      --rey-bg: #1c1c1c;
      --rey-text: #f8f4ee;
      --rey-text-muted: rgba(248,244,238,0.6);
      --rey-text-faint: rgba(248,244,238,0.3);
      --rey-line: rgba(248,244,238,0.16);
      --rey-accent: #2c6e4f;
    }

    html, body {
      height: 100%;
      font-family: 'Inter', sans-serif;
      scroll-behavior: smooth;
      background-color: var(--page-bg);
      color: var(--text-dark);
      overflow-x: hidden;
    }

    .toast-message {
      position: fixed;
      bottom: 30px;
      left: 50%;
      transform: translateX(-50%);
      background: #1a2c2a;
      color: white;
      padding: 10px 24px;
      border-radius: 60px;
      font-size: 0.8rem;
      font-weight: 500;
      z-index: 10000;
      box-shadow: 0 8px 20px rgba(0,0,0,0.2);
      backdrop-filter: blur(8px);
      font-family: 'Inter', sans-serif;
      animation: fadeUp 0.25s ease;
      white-space: nowrap;
      pointer-events: none;
    }
    @keyframes fadeUp {
      from { opacity: 0; transform: translateX(-50%) translateY(20px); }
      to   { opacity: 1; transform: translateX(-50%) translateY(0); }
    }

    /* ── HERO ── */
    .hero {
      position: relative;
      width: 100%;
      height: 100vh;
      min-height: 680px;
      background-color: var(--green-primary);
      overflow: hidden;
      display: flex;
      flex-direction: column;
    }

    .hero::before {
      content: '';
      position: absolute;
      inset: 0;
      background: radial-gradient(ellipse 75% 80% at 60% 48%, #498d69 0%, #2c6e4f 45%, #1f573e 100%);
      z-index: 0;
    }

    nav {
      position: relative;
      z-index: 20;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      padding: 24px 44px;
    }

    .logo {
      font-family: 'Inter', sans-serif;
      font-weight: 500;
      font-size: 22px;
      letter-spacing: -0.4px;
      color: var(--white);
      padding: 4px 0;
      white-space: nowrap;
    }

    .logo-group {
      display: flex;
      align-items: center;
      gap: 10px;
      min-width: 0;
    }

    .logo-mark {
      width: 32px;
      height: 32px;
      border-radius: 8px;
      object-fit: cover;
      display: block;
      flex-shrink: 0;
    }

    .nav-links {
      display: flex;
      align-items: center;
      gap: 28px;
    }

    .nav-link {
      font-family: 'Inter', sans-serif;
      font-size: 13px;
      font-weight: 500;
      color: var(--text-light);
      text-decoration: none;
      letter-spacing: 0.02em;
      transition: color 0.2s ease;
      white-space: nowrap;
    }

    .nav-link:hover { color: var(--white); }

    .nav-actions {
      display: flex;
      align-items: center;
      gap: 14px;
      flex-shrink: 0;
    }

    .lang-switch {
      display: flex;
      align-items: center;
      gap: 2px;
      border: 1.2px solid var(--border-light);
      border-radius: 40px;
      padding: 3px;
      background: rgba(255,255,255,0.08);
      backdrop-filter: blur(4px);
    }

    .lang-option {
      font-family: 'Inter', sans-serif;
      font-size: 11px;
      font-weight: 500;
      letter-spacing: 0.02em;
      color: var(--text-light);
      background: transparent;
      border: none;
      border-radius: 30px;
      padding: 4px 10px;
      cursor: pointer;
      transition: all 0.2s ease;
      display: flex;
      align-items: center;
      gap: 4px;
    }

    .flag-icon {
      width: 18px;
      height: 12px;
      border-radius: 2px;
      object-fit: cover;
      display: inline-block;
      vertical-align: middle;
    }

    .lang-option.active {
      background: var(--white);
      color: #1f3b2c;
    }

    .lang-option:not(.active):hover { color: var(--white); }

    .login-link {
      font-family: 'Inter', sans-serif;
      font-size: 13px;
      font-weight: 500;
      color: var(--white);
      text-decoration: none;
      border: 1.2px solid var(--border-light);
      border-radius: 40px;
      padding: 8px 20px;
      transition: all 0.2s ease;
      white-space: nowrap;
      cursor: pointer;
    }

    .login-link:hover {
      background: rgba(255,255,255,0.18);
      border-color: rgba(255,255,255,0.7);
    }

    /* Mobile nav toggle button */
    .mobile-nav-toggle {
      display: none;
      align-items: center;
      gap: 8px;
      border: 1.2px solid var(--border-light);
      border-radius: 40px;
      background: rgba(255,255,255,0.08);
      backdrop-filter: blur(4px);
      color: var(--white);
      font-family: 'Inter', sans-serif;
      font-size: 13px;
      font-weight: 500;
      padding: 8px 18px 8px 14px;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .mobile-nav-toggle:hover {
      background: rgba(255,255,255,0.18);
      border-color: rgba(255,255,255,0.7);
    }

    .mobile-nav-toggle .plus { font-size: 20px; font-weight: 400; line-height: 1; }

    /* Mobile dropdown panel */
    .mobile-nav-panel {
      display: none;
      position: absolute;
      top: 78px;
      right: 24px;
      z-index: 9999;
      background: var(--green-dark);
      border: 1px solid rgba(255,255,255,0.15);
      border-radius: 10px;
      padding: 10px;
      flex-direction: column;
      gap: 4px;
      min-width: 220px;
      box-shadow: 0 16px 40px rgba(0,0,0,0.3);
    }

    .mobile-nav-panel.open {
      display: flex !important;
    }

    .mobile-nav-panel a {
      color: var(--white);
      font-size: 14px;
      font-weight: 500;
      text-decoration: none;
      padding: 10px 14px;
      border-radius: 6px;
      transition: background 0.2s ease;
      display: block;
    }

    .mobile-nav-panel a:hover {
      background: rgba(255,255,255,0.12);
    }

    .mobile-nav-panel .lang-switch {
      margin: 8px 14px 4px;
      align-self: flex-start;
    }

    .mobile-nav-panel .lang-option {
      font-size: 10px;
      padding: 3px 8px;
    }

    .mobile-nav-panel .flag-icon {
      width: 14px;
      height: 10px;
    }

    /* HERO CONTENT */
    .hero-content {
      position: relative;
      z-index: 12;
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 0 52px 90px;
      max-width: 520px;
    }

    .eyebrow {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      border: 1px solid var(--border-light);
      border-radius: 4px;
      padding: 5px 16px;
      font-size: 12px;
      font-weight: 500;
      color: var(--white);
      letter-spacing: 0.3px;
      margin-bottom: 24px;
      width: fit-content;
      background: rgba(0,0,0,0.15);
      backdrop-filter: blur(2px);
    }

    .eyebrow .material-icons { font-size: 14px; }

    .hero-title {
      font-family: 'EB Garamond', Georgia, serif;
      font-size: 50px;
      font-weight: 500;
      line-height: 1.05;
      color: var(--white);
      letter-spacing: -0.8px;
      margin-bottom: 22px;
    }

    .hero-sub {
      font-size: 15px;
      font-weight: 350;
      line-height: 1.55;
      color: var(--text-light);
      margin-bottom: 42px;
      max-width: 300px;
    }

    .cta-row {
      display: flex;
      gap: 16px;
      align-items: center;
      flex-wrap: wrap;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      border-radius: 2px;
      font-family: 'Inter', sans-serif;
      font-size: 12px;
      font-weight: 500;
      cursor: pointer;
      text-decoration: none;
      transition: all 0.2s ease;
      letter-spacing: 0.01em;
      white-space: nowrap;
      border: none;
    }

    .btn-primary {
      background: var(--white);
      color: #1f3b2c;
      padding: 9px 14px;
      box-shadow: var(--shadow-subtle);
    }

    .btn-primary:hover { background: #f9fbf7; transform: translateY(-2px); }

    .btn-secondary {
      background: rgba(255,255,255,0.12);
      backdrop-filter: blur(4px);
      color: var(--white);
      border: 1.2px solid var(--border-light);
      padding: 9px 18px;
    }

    .btn-secondary:hover { background: rgba(255,255,255,0.22); border-color: rgba(255,255,255,0.8); }

    .btn-icon {
      width: 22px;
      height: 22px;
      border-radius: 50%;
      border: 1.5px solid currentColor;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      background: transparent;
    }

    .btn-icon .material-icons { font-size: 13px; }

    /* HERO ILLUSTRATION */
    .hero-illustration {
      position: absolute;
      right: 0;
      bottom: 0;
      top: 0;
      width: 56%;
      z-index: 8;
      display: flex;
      align-items: flex-end;
      justify-content: flex-end;
      pointer-events: none;
    }

    .illustration-img {
      width: 100%;
      height: auto;
      max-height: 96%;
      object-fit: contain;
      object-position: bottom right;
      display: block;
      filter: drop-shadow(0 20px 30px rgba(0,0,0,0.15));
      animation: gentleAppear 0.9s cubic-bezier(0.12, 0.71, 0.33, 1) forwards;
    }

    @keyframes gentleAppear {
      0%   { opacity: 0; transform: translateX(18px) scale(0.98); }
      100% { opacity: 1; transform: translateX(0) scale(1); }
    }

    /* VIDEO CARD */
    .video-card {
      position: absolute;
      bottom: 92px;
      right: 18px;
      z-index: 18;
      width: 260px;
      background: rgba(255,255,255,0.98);
      border-radius: 3px;
      overflow: hidden;
      box-shadow: 0 20px 35px -8px rgba(0,0,0,0.25);
      cursor: pointer;
      transition: transform 0.2s ease, box-shadow 0.2s;
    }

    .video-card:hover { transform: scale(1.01); box-shadow: 0 24px 40px -12px rgba(0,0,0,0.3); }

    .video-thumb { width: 100%; background: #cfddcc; position: relative; }

    .video-thumb-img {
      width: 100%;
      height: 148px;
      object-fit: cover;
      display: block;
      transition: transform 0.3s ease;
    }

    .video-card:hover .video-thumb-img { transform: scale(1.02); }

    .video-footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 12px 16px;
      background: #ffffff;
    }

    .video-label {
      font-size: 13px;
      font-weight: 500;
      color: #1d392b;
      font-family: 'Inter', sans-serif;
      letter-spacing: -0.2px;
    }

    .video-footer .material-icons { font-size: 18px; color: var(--green-soft); }

    /* SECTIONS */
    .section {
      padding: 6rem 5% 5rem;
      border-top: 1px solid var(--card-border);
    }

    .section-header {
      max-width: 1240px;
      margin: 0 auto 3.5rem;
    }

    .section-eyebrow {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      border: 1px solid var(--card-border);
      border-radius: 4px;
      padding: 5px 16px;
      font-size: 12px;
      font-weight: 500;
      color: var(--green-dark);
      letter-spacing: 0.3px;
      margin-bottom: 18px;
      width: fit-content;
      background: var(--green-mist);
    }

    .section-eyebrow .material-icons { font-size: 14px; }

    .section-title {
      font-family: 'EB Garamond', Georgia, serif;
      font-size: 38px;
      font-weight: 500;
      letter-spacing: -0.8px;
      color: var(--text-dark);
      line-height: 1.1;
      margin-bottom: 12px;
      max-width: 520px;
    }

    .section-sub {
      font-size: 15px;
      font-weight: 350;
      line-height: 1.55;
      color: var(--text-muted);
      max-width: 460px;
    }

    /* COLLECTION */
    .featured-collection {
      background: var(--rey-bg);
      color: var(--rey-text);
      border-top: none;
    }

    .featured-collection .section-header {
      display: grid;
      grid-template-columns: 160px 1fr;
      gap: 0 2.5rem;
      align-items: start;
    }

    .rey-eyebrow {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 11px;
      font-weight: 500;
      letter-spacing: 0.18em;
      text-transform: uppercase;
      color: var(--rey-text-muted);
      padding-top: 10px;
    }

    .rey-eyebrow::before {
      content: '';
      display: block;
      width: 18px;
      height: 1px;
      background: var(--rey-text-muted);
    }

    .featured-collection .section-title {
      font-family: 'Inter', sans-serif;
      font-weight: 500;
      font-size: 38px;
      letter-spacing: -0.02em;
      color: var(--rey-text);
      max-width: 640px;
      margin-bottom: 14px;
    }

    .featured-collection .section-sub {
      color: var(--rey-text-muted);
      max-width: 520px;
    }

    .collection-grid {
      display: flex;
      flex-direction: column;
      max-width: 1240px;
      margin: 3.5rem auto 0;
      border-top: 1px solid var(--rey-line);
    }

    .collection-item {
      display: grid;
      grid-template-columns: 160px 1fr auto;
      gap: 2.5rem;
      align-items: center;
      padding: 1.6rem 0;
      border-bottom: 1px solid var(--rey-line);
      cursor: pointer;
      transition: opacity 0.2s ease;
    }

    .collection-item:hover { opacity: 0.7; }

    .item-thumb {
      width: 100%;
      height: 100px;
      border-radius: 2px;
      overflow: hidden;
      background: rgba(248,244,238,0.06);
    }

    .item-thumb img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
      transition: transform 0.4s ease;
      filter: grayscale(15%);
    }

    .collection-item:hover .item-thumb img { transform: scale(1.04); }

    .item-info {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }

    .item-label {
      font-family: 'Inter', sans-serif;
      font-weight: 500;
      font-size: 18px;
      letter-spacing: -0.01em;
      color: var(--rey-text);
    }

    .item-value {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 11px;
      font-weight: 500;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: var(--rey-text-muted);
    }

    .item-value .material-icons { font-size: 14px; color: var(--rey-text-faint); }

    .item-arrow {
      width: 32px;
      height: 32px;
      border: 1px solid var(--rey-line);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--rey-text);
      transition: border-color 0.2s ease, transform 0.2s ease;
      flex-shrink: 0;
    }

    .collection-item:hover .item-arrow {
      border-color: var(--rey-text-muted);
      transform: translateX(3px);
    }

    .item-arrow .material-icons { font-size: 16px; }

    /* WHY PLAYMATES MATTERS */
    .how-grid {
      display: flex;
      flex-wrap: wrap;
      gap: 2.5rem;
      max-width: 1240px;
      margin: 0 auto;
    }

    .how-step {
      flex: 1 1 220px;
      border-top: 1px solid var(--card-border);
      padding-top: 1.4rem;
    }

    .how-step .step-num {
      font-family: 'EB Garamond', Georgia, serif;
      font-size: 28px;
      font-style: italic;
      color: var(--green-soft);
      margin-bottom: 0.8rem;
      display: block;
    }

    .how-step h3 {
      font-size: 15px;
      font-weight: 500;
      color: var(--text-dark);
      margin-bottom: 0.5rem;
      letter-spacing: -0.2px;
    }

    .how-step p {
      font-size: 14px;
      line-height: 1.6;
      color: var(--text-muted);
      font-weight: 350;
    }

    /* TRUST BAND */
    .trust-band {
      background: var(--green-primary);
      color: var(--white);
      padding: 4.5rem 5%;
    }

    .trust-inner {
      max-width: 1240px;
      margin: 0 auto;
      display: flex;
      flex-wrap: wrap;
      gap: 3rem;
      align-items: center;
      justify-content: space-between;
    }

    .trust-quote { max-width: 560px; }

    .trust-quote p {
      font-family: 'EB Garamond', Georgia, serif;
      font-size: 26px;
      font-style: italic;
      line-height: 1.4;
      letter-spacing: -0.2px;
      margin-bottom: 1rem;
    }

    .trust-quote span {
      font-size: 13px;
      color: var(--text-light);
      font-weight: 500;
      letter-spacing: 0.02em;
    }

    .trust-stats { display: flex; gap: 2.5rem; flex-wrap: wrap; }

    .trust-stat .num {
      font-family: 'EB Garamond', Georgia, serif;
      font-size: 40px;
      font-weight: 500;
      letter-spacing: -1px;
      display: block;
    }

    .trust-stat .label {
      font-size: 12px;
      color: var(--text-light);
      letter-spacing: 0.03em;
      margin-top: 4px;
    }

    /* DOWNLOAD */
    .download-section {
      padding: 6rem 5%;
      text-align: center;
    }

    .download-section .section-header {
      display: flex;
      flex-direction: column;
      align-items: center;
      margin-bottom: 2.5rem;
    }

    .download-section .section-title,
    .download-section .section-sub {
      margin-left: auto;
      margin-right: auto;
    }

    .download-cta { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; }

    .download-cta .btn-primary {
      background: var(--green-primary);
      color: var(--white);
      padding: 11px 22px;
    }

    .download-cta .btn-primary:hover { background: var(--green-dark); }

    .download-cta .btn-secondary {
      background: transparent;
      color: var(--green-dark);
      border: 1.2px solid var(--card-border);
      padding: 11px 22px;
    }

    .download-cta .btn-secondary:hover { background: var(--green-mist); border-color: var(--green-soft); }

    /* FOOTER */
    footer {
      border-top: 1px solid var(--card-border);
      padding: 2.5rem 5%;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 1rem;
      font-size: 13px;
      color: var(--text-muted);
    }

    footer .logo { color: var(--green-dark); font-size: 18px; }

    .footer-links { display: flex; gap: 1.8rem; }
    .footer-links a { color: var(--text-muted); text-decoration: none; transition: color 0.2s; }
    .footer-links a:hover { color: var(--green-dark); }

    /* RESPONSIVE */
    @media (max-width: 1000px) {
      .nav-links { display: none; }
      .nav-actions .lang-switch { display: none; }
      .mobile-nav-toggle { display: flex; }
      .hero-content { padding: 0 36px 60px; max-width: 440px; }
      .hero-title { font-size: 48px; }
      .hero-illustration { width: 52%; opacity: 0.92; }
      .featured-collection .section-header { grid-template-columns: 1fr; gap: 0.8rem; }
      .collection-item { grid-template-columns: 96px 1fr auto; gap: 1.2rem; }
      .item-thumb { height: 64px; }
      .item-label { font-size: 16px; }
    }

    @media (max-width: 780px) {
      nav { padding: 18px 24px; }
      .hero { height: auto; min-height: 100vh; }
      .hero-content { padding: 24px 24px 0; max-width: 100%; }
      .hero-title { font-size: 38px; }
      .hero-illustration {
        position: static;
        width: 100%;
        max-width: 380px;
        opacity: 0.7;
        margin-top: 20px;
        align-self: center;
        pointer-events: none;
      }
      .logo {font-size: 14px;}
      .video-card { display: none; }
      .cta-row { gap: 8px; }
      .section { padding: 3.5rem 6% 3rem; }
      .featured-collection .section-title { font-size: 28px; }
      .collection-item { grid-template-columns: 80px 1fr auto; gap: 1rem; }
      .item-thumb { height: 56px; }
    }

    @media (max-width: 550px) {
      .hero-title { font-size: 32px; }
      .hero-sub { font-size: 14px; }
      .collection-item { grid-template-columns: 1fr; gap: 0.9rem; }
      .item-thumb { height: 130px; }
      .item-arrow { align-self: flex-end; }
    }
  </style>
</head>
<body>



<section class="hero">
  <nav>
    <div class="logo-group">
      <img class="logo-mark" src="https://res.cloudinary.com/franklinrw/image/upload/v1755169717/kjk_bnrbmp.png" alt="Playmates logo">
      <span class="logo">Playmates</span>
    </div>

    <div class="nav-links">
      <a href="#games" class="nav-link"><?php echo t('nav_home'); ?></a>
      <a href="Language_Exams.php?lang=<?php echo $lang; ?>" class="nav-link"><?php echo t('nav_exams'); ?></a>
      <a href="about.php?lang=<?php echo $lang; ?>" class="nav-link"><?php echo t('nav_about'); ?></a>
      <a href="#games" class="nav-link"><?php echo t('nav_lessons'); ?></a>
      <a href="#download" class="nav-link"><?php echo t('nav_download'); ?></a>
    </div>

    <div class="nav-actions">
      <?php echo getLangSwitcher($lang); ?>
      <a href="loginui.php?lang=<?php echo $lang; ?>" class="login-link"><?php echo t('login_btn'); ?></a>
      <button class="mobile-nav-toggle" id="mobileToggle" aria-label="Toggle menu" aria-expanded="false">
        <span class="plus">+</span> Menu
      </button>
    </div>

    <!-- Mobile dropdown panel -->
    <div class="mobile-nav-panel" id="mobileNavPanel">
      <a href="#games"><?php echo t('nav_home'); ?></a>
      <a href="Language_Exams.php?lang=<?php echo $lang; ?>"><?php echo t('nav_exams'); ?></a>
      <a href="about.php?lang=<?php echo $lang; ?>"><?php echo t('nav_about'); ?></a>
      <a href="#games"><?php echo t('nav_lessons'); ?></a>
      <a href="#download"><?php echo t('nav_download'); ?></a>
      <a href="loginui.php?lang=<?php echo $lang; ?>" class="login-link"><?php echo t('login_btn'); ?></a>
      <div class="lang-switch" role="group" aria-label="Mobile language switcher">
        <button class="lang-option <?php echo $lang === 'en' ? 'active' : ''; ?>" onclick="setLang('en')">
          <img src="https://flagcdn.com/us.svg" alt="US flag" class="flag-icon"> EN
        </button>
        <button class="lang-option <?php echo $lang === 'rw' ? 'active' : ''; ?>" onclick="setLang('rw')">
          <img src="https://flagcdn.com/rw.svg" alt="Rwandan flag" class="flag-icon"> RW
        </button>
        <button class="lang-option <?php echo $lang === 'sw' ? 'active' : ''; ?>" onclick="setLang('sw')">
          <img src="https://flagcdn.com/tz.svg" alt="Tanzanian flag" class="flag-icon"> SW
        </button>
      </div>
    </div>
  </nav>

  <div class="hero-content">
    <span class="eyebrow">
      <span class="material-icons">sports_esports</span>
      <span><?php echo t('eyebrow_text'); ?></span>
    </span>
    <h1 class="hero-title"><?php echo t('hero_title'); ?></h1>
    <p class="hero-sub"><?php echo t('hero_sub'); ?></p>
    <div class="cta-row">
      <a href="#games" class="btn btn-primary">
        <?php echo t('start_playing_btn'); ?>
        <span class="btn-icon"><span class="material-icons">arrow_forward</span></span>
      </a>
      <a href="#download" class="btn btn-secondary">
        <?php echo t('community_btn'); ?>
        <span class="btn-icon"><span class="material-icons">groups</span></span>
      </a>
    </div>
  </div>

  <div class="hero-illustration">
    <img class="illustration-img"
         src="https://res.cloudinary.com/franklinrw/image/upload/v1781385354/Untitled_Project_-_illustrationImage_17_hchv3s.png"
         alt="Playful learning illustration" loading="eager">
  </div>

  <div class="video-card" id="videoCard" role="button" tabindex="0" aria-label="Watch the video">
    <div class="video-thumb">
      <img class="video-thumb-img"
           src="https://res.cloudinary.com/franklinrw/image/upload/v1781545274/Untitled_Project_-_video_21_to4qje.gif"
           alt="Playmates video preview" loading="lazy">
    </div>
    <div class="video-footer">
      <span class="video-label"><?php echo t('video_label'); ?></span>
      <span class="material-icons">groups</span>
    </div>
  </div>
</section>

<!-- EDUCATIONAL GAME COLLECTION -->
<section id="games" class="section featured-collection">
  <div class="section-header">
    <span class="rey-eyebrow"><?php echo t('collection_eyebrow'); ?></span>
    <div>
      <h2 class="section-title"><?php echo t('collection_title'); ?></h2>
      <p class="section-sub"><?php echo t('collection_desc'); ?></p>
    </div>
  </div>
  <div class="collection-grid">
    <div class="collection-item" data-link="frontend/dashboard.php?lang=<?php echo $lang; ?>" data-requires-login="true">
      <div class="item-thumb">
        <img src="https://res.cloudinary.com/franklinrw/image/upload/v1774271275/Y2k_Streetwear_BrandBuilder_-_Kittl_Flows_-_video_online-video-cutter.com_rmd6wo.gif" alt="Language games">
      </div>
      <div class="item-info">
        <span class="item-label"><?php echo t('lang_label'); ?></span>
        <span class="item-value"><span class="material-icons">translate</span><?php echo t('lang_value'); ?></span>
      </div>
      <div class="item-arrow"><span class="material-icons">arrow_forward</span></div>
    </div>
    <div class="collection-item" data-link="https://www.sdg.playmates.games/" data-requires-login="true">
      <div class="item-thumb">
        <img src="https://res.cloudinary.com/franklinrw/image/upload/v1781453738/dvp_lhtua3.gif" alt="Development games">
      </div>
      <div class="item-info">
        <span class="item-label"><?php echo t('dev_label'); ?></span>
        <span class="item-value"><span class="material-icons">public</span><?php echo t('dev_value'); ?></span>
      </div>
      <div class="item-arrow"><span class="material-icons">arrow_forward</span></div>
    </div>
    <div class="collection-item" data-link="https://www.coding.playmates.games/" data-requires-login="true">
      <div class="item-thumb">
        <img src="https://res.cloudinary.com/franklinrw/image/upload/v1762721015/cod_odiog7.gif" alt="Coding games">
      </div>
      <div class="item-info">
        <span class="item-label"><?php echo t('coding_label'); ?></span>
        <span class="item-value"><span class="material-icons">code</span><?php echo t('coding_value'); ?></span>
      </div>
      <div class="item-arrow"><span class="material-icons">arrow_forward</span></div>
    </div>
    <div class="collection-item" data-link="https://www.class.playmates.games/" data-requires-login="true">
      <div class="item-thumb">
        <img src="https://res.cloudinary.com/franklinrw/image/upload/v1762707952/franko_bit_Afro-futuristic_illustration_of_a_smart_African_bo_613837b5-44c7-47ac-8c5d-62f803e2e517_2_xdsx69.gif" alt="Classroom games">
      </div>
      <div class="item-info">
        <span class="item-label"><?php echo t('classroom_label'); ?></span>
        <span class="item-value"><span class="material-icons">school</span><?php echo t('classroom_value'); ?></span>
      </div>
      <div class="item-arrow"><span class="material-icons">arrow_forward</span></div>
    </div>
  </div>
</section>

<!-- WHY PLAYMATES MATTERS -->
<section id="about" class="section">
  <div class="section-header">
    <span class="section-eyebrow">
      <span class="material-icons">star</span>
      <span><?php echo t('why_eyebrow'); ?></span>
    </span>
    <h2 class="section-title"><?php echo t('why_title'); ?></h2>
    <p class="section-sub"><?php echo t('why_sub'); ?></p>
  </div>
  <div class="how-grid">
    <div class="how-step">
      <span class="step-num">01</span>
      <h3><?php echo t('why_point1_title'); ?></h3>
      <p><?php echo t('why_point1_desc'); ?></p>
    </div>
    <div class="how-step">
      <span class="step-num">02</span>
      <h3><?php echo t('why_point2_title'); ?></h3>
      <p><?php echo t('why_point2_desc'); ?></p>
    </div>
    <div class="how-step">
      <span class="step-num">03</span>
      <h3><?php echo t('why_point3_title'); ?></h3>
      <p><?php echo t('why_point3_desc'); ?></p>
    </div>
  </div>
</section>

<!-- TRUST BAND -->
<section class="trust-band">
  <div class="trust-inner">
    <div class="trust-quote">
      <p><?php echo t('quote_text'); ?></p>
      <span><?php echo t('quote_author'); ?></span>
    </div>
    <div class="trust-stats">
      <div class="trust-stat"><span class="num">111</span><span class="label"><?php echo t('stat_games'); ?></span></div>
      <div class="trust-stat"><span class="num">6</span><span class="label"><?php echo t('stat_subjects'); ?></span></div>
      <div class="trust-stat"><span class="num">4</span><span class="label"><?php echo t('stat_langs'); ?></span></div>
    </div>
  </div>
</section>

<!-- DOWNLOAD CTA -->
<section id="download" class="section download-section">
  <div class="section-header">
    <span class="section-eyebrow">
      <span class="material-icons">phone_iphone</span>
      <span><?php echo t('download_eyebrow'); ?></span>
    </span>
    <h2 class="section-title"><?php echo t('download_title'); ?></h2>
    <p class="section-sub"><?php echo t('download_sub'); ?></p>
  </div>
  <div class="download-cta">
    <a href="#games" class="btn btn-primary">
      <?php echo t('start_playing_btn2'); ?>
      <span class="btn-icon"><span class="material-icons">arrow_forward</span></span>
    </a>
    <a href="#" class="btn btn-secondary" id="downloadAppBtn">
      <?php echo t('download_btn'); ?>
      <span class="btn-icon"><span class="material-icons">phone_iphone</span></span>
    </a>
  </div>
</section>

<footer>
  <span class="logo">Playmates</span>
  <div class="footer-links">
    <a href="#games"><?php echo t('footer_games'); ?></a>
    <a href="#"><?php echo t('footer_schools'); ?></a>
    <a href="#about"><?php echo t('footer_about'); ?></a>
    <a href="#download"><?php echo t('footer_download'); ?></a>
  </div>
  <span><?php echo t('copyright'); ?></span>
</footer>

<script>
// ── LANGUAGE SWITCH FUNCTION ────────────────────────────────────
function setLang(lang) {
    // Get current URL
    var url = new URL(window.location.href);
    // Set lang parameter
    url.searchParams.set('lang', lang);
    // Redirect to the new URL
    window.location.href = url.toString();
}

// ── MOBILE MENU ───────────────────────────────────────────────
var mobileToggle = document.getElementById('mobileToggle');
var mobilePanel  = document.getElementById('mobileNavPanel');

if (mobileToggle && mobilePanel) {
    mobileToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        var isOpen = mobilePanel.classList.toggle('open');
        mobileToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    document.addEventListener('click', function(e) {
        if (!mobilePanel.contains(e.target) && e.target !== mobileToggle) {
            mobilePanel.classList.remove('open');
            mobileToggle.setAttribute('aria-expanded', 'false');
        }
    });

    mobilePanel.querySelectorAll('a').forEach(function(link) {
        link.addEventListener('click', function() {
            mobilePanel.classList.remove('open');
            mobileToggle.setAttribute('aria-expanded', 'false');
        });
    });
}

// ── TOAST ─────────────────────────────────────────────────────
function showToast(msg) {
    var old = document.querySelector('.toast-message');
    if (old) old.remove();
    var t = document.createElement('div');
    t.className = 'toast-message';
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(function(){ t.remove(); }, 2500);
}

// ── VIDEO CARD ────────────────────────────────────────────────
var videoCard = document.getElementById('videoCard');
if (videoCard) {
    var lang = '<?php echo $lang; ?>';
    videoCard.addEventListener('click', function() {
        var msg = lang === 'rw' ? '▶️ Reba uburyo imikino ifasha mu burezi.'
                : lang === 'sw' ? '▶️ Tazama jinsi michezo ya kielimu inavyobadilisha udadisi.'
                : '▶️ Watch how game-based learning transforms curiosity into skill.';
        showToast(msg);
    });
    videoCard.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') videoCard.click();
    });
}

// ── DOWNLOAD APP BUTTON ───────────────────────────────────────
var downloadBtn = document.getElementById('downloadAppBtn');
if (downloadBtn) {
    var lang = '<?php echo $lang; ?>';
    downloadBtn.addEventListener('click', function(e) {
        e.preventDefault();
        var msg = lang === 'rw' ? '📱 App irahari vuba — fungura uyu murongo kuri terefone yawe.'
                : lang === 'sw' ? '📱 App inakuja hivi karibuni — fungua kiungo hiki kwenye simu yako.'
                : '📱 App coming soon — open this link on your phone to download.';
        showToast(msg);
    });
}

// ── GAME COLLECTION ITEMS ─────────────────────────────────────
document.querySelectorAll('.collection-item').forEach(function(item) {
    item.addEventListener('click', function() {
        var link = item.getAttribute('data-link');
        var needsLogin = item.getAttribute('data-requires-login') === 'true';
        var lang = '<?php echo $lang; ?>';
        if (needsLogin) {
            var msg = lang === 'rw'
                ? '🔐 Uyu mukino usaba konti ya Playmates.\nWifuza kwinjira / kwiyandikisha?'
                : lang === 'sw'
                ? '🔐 Mchezo huu unahitaji akaunti ya Playmates.\nUngependa kuingia / kujiunga?'
                : '🔐 This game requires a Playmates account.\nWould you like to log in or sign up?';
            if (confirm(msg) && link) {
                if (link.startsWith('http')) {
                    window.open(link, '_blank');
                } else {
                    showToast('Redirecting to ' + link);
                }
            }
        } else if (link) {
            if (link.startsWith('http')) window.open(link, '_blank');
            else showToast('Explore: ' + link);
        }
    });
});
</script>
</body>
</html>