<?php
// Start session to track language and login status
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

// ─── CHECK IF USER IS LOGGED IN ──────────────────────────────────
$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$userName = $isLoggedIn ? ($_SESSION['full_name'] ?? 'User') : '';

// Get current page URL for redirect
$currentPage = $_SERVER['REQUEST_URI'];

// Language metadata with flag codes
$languageFlags = [
    'English' => 'us',
    'Kinyarwanda' => 'rw',
    'French' => 'fr',
    'Kiswahili' => 'tz',
    'Indonesian' => 'id',
    'Ukrainian' => 'ua',
    'Vietnamese' => 'vn',
    'Zulu' => 'za',
    'Scottish Gaelic' => 'gb-sct',
    'Latin' => 'va',
    'Greek' => 'gr',
    'Hebrew' => 'il',
    'Polish' => 'pl',
    'Norwegian' => 'no',
    'Danish' => 'dk',
    'Finnish' => 'fi',
    'Czech' => 'cz',
    'Arabic' => 'sa',
    'Russian' => 'ru',
    'Hindi' => 'in',
    'Turkish' => 'tr',
    'Dutch' => 'nl',
    'Swedish' => 'se',
    'Irish' => 'ie',
    'Portuguese' => 'pt',
    'Japanese' => 'jp',
    'Korean' => 'kr',
    'Chinese' => 'cn',
];

// Define translations
$translations = [
    'en' => [
        // NAVBAR
        'nav_languages'   => 'Languages',
        'nav_practice'    => 'Practice',
        'nav_support'     => 'Support',
        'nav_login'       => 'Log in',
        'nav_start'       => 'Start',
        'nav_download'    => 'Download',
        'nav_menu'        => 'Menu',

        // HERO
        'eyebrow_text'    => 'Learn Languages Through Play',
        'hero_title_1'    => 'Learn Languages',
        'hero_title_2'    => 'and Speak With Confidence',
        'hero_sub'        => 'Study up to 27 languages using a language you already understand, build real conversation skills, and practice speaking with others.',
        'start_playing_btn' => 'Start learning',

        // SECTION HEADINGS
        'collection_eyebrow' => 'Language Paths',
        'collection_title'   => 'Learn Any Language',
        'collection_desc'    => 'Choose from 27 languages with interactive lessons and challenges. Plus, connect with learners worldwide in our speaking rooms to practice real conversations.',

        'lang_label'      => 'African Languages',
        'lang_value'      => 'Kinyarwanda, Swahili, Zulu',
        'dev_label'       => 'European Languages',
        'dev_value'       => 'French, Polish, Swedish, Danish',
        'coding_label'    => 'Asian Languages',
        'coding_value'    => 'Japanese, Korean, Chinese, Hindi',
        'classroom_label' => 'World Languages',
        'classroom_value' => 'Arabic, Turkish, Portuguese & more',

        // MISSION
        'mission_lead' => 'Learning a language is about understanding, communicating, and speaking with confidence.',
        'mission_body' => 'Our platform helps learners study up to 27 languages using a language they already know, making it easier to build fluency step by step.',

        // HOW IT WORKS
        'hiw_eyebrow' => 'How It Works',
        'hiw_title'   => 'Four Steps to Fluency',

        'hiw_1_label' => 'Listen & Type',
        'hiw_1_title_1' => 'Train your ear,',
        'hiw_1_title_2' => 'one phrase at a time.',
        'hiw_1_desc' => 'You can learn how to listen by listening and typing what you hear, or by choosing the correct answer from the options. Each repetition sharpens your ear for real conversations.',

        'hiw_2_label' => 'See & Choose',
        'hiw_2_title_1' => 'Match words',
        'hiw_2_title_2' => 'to what you see.',
        'hiw_2_desc' => 'Visually, you get images and choose the correct answer based on what you see. It connects new vocabulary to real-world objects and scenes, so words stick faster.',

        'hiw_3_label' => 'Instant Feedback',
        'hiw_3_title_1' => 'Know right away',
        'hiw_3_title_2' => 'if you got it right.',
        'hiw_3_desc' => 'If your answer is correct or wrong, a visual communicator — such as an animation — tells you whether you got it right or wrong, so you can learn and adjust in the moment.',

        'hiw_4_label' => 'Listen & Speak',
        'hiw_4_title_1' => 'Repeat, practice,',
        'hiw_4_title_2' => 'and speak aloud.',
        'hiw_4_desc' => 'Another part is listen and speak: you listen to a phrase and repeat what you hear to practice speaking. It builds pronunciation, rhythm, and confidence in your own voice.',

        // FEATURES
        'features_title_1' => 'Everything you need,',
        'features_title_2' => 'all in one place.',
        'features_sub' => 'From learning in your own language to practicing real conversations, Playmates gives you the tools to build fluency with confidence.',

        'feat_lang_label'  => 'Languages',
        'feat_lang_title'  => 'Learn in Your Language',
        'feat_lang_sub'    => 'Learn using a language you already understand.',

        'feat_off_label'   => 'Offline',
        'feat_off_title'   => 'Learn With Limited Internet',
        'feat_off_sub'     => 'Keep learning even with limited internet access.',

        'feat_prac_label'  => 'Practice',
        'feat_prac_title'  => 'Practice Real Conversations',
        'feat_prac_sub'    => 'Join rooms and practice speaking with other learners.',

        // CTA
        'cta_title' => 'Your Language Journey Starts Now',
        'cta_sub'   => 'No credit card required. Learn at your own pace.',
        'cta_btn'   => 'Start Learning Free',

        // FOOTER
        'footer_desc' => 'Learn languages and speak with confidence. Study up to 27 languages, practice real conversations, and build fluency step by step.',
        'footer_learn' => 'Learn',
        'footer_languages' => 'Languages',
        'footer_lessons' => 'Lessons',
        'footer_rooms' => 'Speaking Rooms',
        'footer_offline' => 'Offline Mode',
        'footer_company' => 'Company',
        'footer_about' => 'About Us',
        'footer_careers' => 'Careers',
        'footer_blog' => 'Blog',
        'footer_press' => 'Press',
        'footer_support' => 'Support',
        'footer_help' => 'Help Center',
        'footer_contact' => 'Contact Us',
        'footer_community' => 'Community',
        'footer_privacy' => 'Privacy Policy',
        'copyright' => '© 2026 Playmates. All rights reserved.',
        'footer_terms' => 'Terms',
        'footer_privacy_link' => 'Privacy',
        'footer_cookies' => 'Cookies',

        // TOASTS
        'login_required' => 'Please login first to start learning!',
        'welcome_user' => 'Welcome',
    ],
    'rw' => [
        'nav_languages'   => 'Indimi',
        'nav_practice'    => 'Kwiga',
        'nav_support'     => 'Ubufasha',
        'nav_login'       => 'Injira',
        'nav_start'       => 'Tangira',
        'nav_download'    => 'Kumanura',
        'nav_menu'        => 'Ibikubiye',

        'eyebrow_text'    => 'Wige Indimi Mu Buryo Bwa Kina',
        'hero_title_1'    => 'Wige Indimi',
        'hero_title_2'    => 'kandi Vuga Ufite Icyizere',
        'hero_sub'        => 'Wige indimi 27 ukoresheje ururimi usanzwe uzi, wubake ubumenyi bwo kuvuga, kandi wige kuvuga n\'abandi.',
        'start_playing_btn' => 'Tangira kwiga',

        'collection_eyebrow' => 'Inzira z\'Indimi',
        'collection_title'   => 'Wige Ururimi Rwose',
        'collection_desc'    => 'Hitamo mu ndimi 27 zifite amasomo n\'imbogamizi. Nanone, huza n\'abandi biga mu isi yose mu byumba byacu byo kuvuga.',

        'lang_label'      => 'Indimi z\'Afurika',
        'lang_value'      => 'Kinyarwanda, Kiswahili, Zulu',
        'dev_label'       => 'Indimi z\'Uburayi',
        'dev_value'       => 'Igifaransa, Igipolonye, Igisuwede, Ikidanwa',
        'coding_label'    => 'Indimi z\'Aziya',
        'coding_value'    => 'Kijapani, Kikoreya, Igishinwa, Kihindi',
        'classroom_label' => 'Indimi z\'Isi Yose',
        'classroom_value' => 'Icyarabu, Igiturukiya, Igiporutigali n\'izindi',

        'mission_lead' => 'Kwiga ururimi ni ugusobanukirwa, kuvugana, no kuvuga ufite icyizere.',
        'mission_body' => 'Urubuga rwacu rufasha abiga kwiga indimi 27 bakoresheje ururimi basanzwe bazi, bigatuma byoroha kubaka ubumenyi bwo kuvuga neza.',

        'hiw_eyebrow' => 'Uko Bikorwa',
        'hiw_title'   => 'Intambwe Zine zo Kwiga',

        'hiw_1_label' => 'Umva & Andika',
        'hiw_1_title_1' => 'Toza ugutwi kwawe,',
        'hiw_1_title_2' => 'interuro ku yindi.',
        'hiw_1_desc' => 'Ushobora kwiga kumva wumva ukandika ibyo wumva, cyangwa ugahitamo igisubizo cyiza mu byo wahisemo. Buri gusubiramo byongera ugutwi kwawe mu biganiro nyakuri.',

        'hiw_2_label' => 'Reba & Hitamo',
        'hiw_2_title_1' => 'Huza amagambo',
        'hiw_2_title_2' => 'n\'ibyo ubona.',
        'hiw_2_desc' => 'Uhabwa amafoto ugahitamo igisubizo cyiza ukurikije ibyo ubona. Bihuza amagambo mashya n\'ibintu nyakuri, bigatuma yibukwa vuba.',

        'hiw_3_label' => 'Igisubizo Ako Kanya',
        'hiw_3_title_1' => 'Menya ako kanya',
        'hiw_3_title_2' => 'niba wabitse.',
        'hiw_3_desc' => 'Niba igisubizo cyawe ari cyiza cyangwa kibi, ikimenyetso kigaragaza — nk\'ifoto igenda — kikubwira niba wabitse cyangwa watesheje, kugira ngo wige kandi uhindure ako kanya.',

        'hiw_4_label' => 'Umva & Vuga',
        'hiw_4_title_1' => 'Subiramo, jya usubiramo,',
        'hiw_4_title_2' => 'kandi uvuge mu ijwi riranguruye.',
        'hiw_4_desc' => 'Ikindi ni ukumva no kuvuga: wumva interuro ugasubiramo ibyo wumvise kugira ngo wige kuvuga. Bibaka imvugo, injyana, n\'icyizere mu ijwi ryawe bwite.',

        'features_title_1' => 'Ibintu byose ukeneye,',
        'features_title_2' => 'ahantu hamwe.',
        'features_sub' => 'Kuva mu kwiga mu rurimi rwawe kugera mu kwiga ibiganiro nyakuri, Playmates iguha ibikoresho byo kubaka ubumenyi ufite icyizere.',

        'feat_lang_label'  => 'Indimi',
        'feat_lang_title'  => 'Wiga mu Rurimi Rwawe',
        'feat_lang_sub'    => 'Wiga ukoresheje ururimi usanzwe uzi.',

        'feat_off_label'   => 'Nta Murandasi',
        'feat_off_title'   => 'Wiga N\'umurandasi Muke',
        'feat_off_sub'     => 'Komeza kwiga nubwo umurandasi waba muke.',

        'feat_prac_label'  => 'Kwiga',
        'feat_prac_title'  => 'Gerageza Ibiganiro Nyakuri',
        'feat_prac_sub'    => 'Injira mu byumba kandi wige kuvuga n\'abandi biga.',

        'cta_title' => 'Urugendo rwawe rw\'Ururimi Rutangira Ubu',
        'cta_sub'   => 'Nta karita y\'inguzanyo isabwa. Wiga ku kigero cyawe.',
        'cta_btn'   => 'Tangira Kwiga Ubuntu',

        'footer_desc' => 'Wige indimi kandi uvuge ufite icyizere. Wiga indimi 27, wige ibiganiro nyakuri, kandi wubake ubumenyi buhoro buhoro.',
        'footer_learn' => 'Kwiga',
        'footer_languages' => 'Indimi',
        'footer_lessons' => 'Amasomo',
        'footer_rooms' => 'Byumba byo Kuvuga',
        'footer_offline' => 'Nta Murandasi',
        'footer_company' => 'Ikigo',
        'footer_about' => 'Amateka Yacu',
        'footer_careers' => 'Akazi',
        'footer_blog' => 'Blog',
        'footer_press' => 'Itangazamakuru',
        'footer_support' => 'Ubufasha',
        'footer_help' => 'Ikigo cy\'Ubufasha',
        'footer_contact' => 'Twandikire',
        'footer_community' => 'Umuryango',
        'footer_privacy' => 'Politiki y\'Ibanga',
        'copyright' => '© 2026 Playmates. Uburenganzira bwose burafitwe.',
        'footer_terms' => 'Amabwiriza',
        'footer_privacy_link' => 'Ibanga',
        'footer_cookies' => 'Cookies',

        'login_required' => 'Banza winjire kugira utangire kwiga!',
        'welcome_user' => 'Murakaza Neza',
    ],
    'sw' => [
        'nav_languages'   => 'Lugha',
        'nav_practice'    => 'Pratika',
        'nav_support'     => 'Msaada',
        'nav_login'       => 'Ingia',
        'nav_start'       => 'Anza',
        'nav_download'    => 'Pakua',
        'nav_menu'        => 'Menyu',

        'eyebrow_text'    => 'Jifunza Lugha Kwa Kucheza',
        'hero_title_1'    => 'Jifunza Lugha',
        'hero_title_2'    => 'na Zungumza kwa Imani',
        'hero_sub'        => 'Jifunza lugha 27 ukitumia lugha unayoifahamu, jenga ujuzi wa mazungumzo halisi, na ujifunze kuzungumza na wengine.',
        'start_playing_btn' => 'Anza kujifunza',

        'collection_eyebrow' => 'Njia za Lugha',
        'collection_title'   => 'Jifunza Lugha Yoyote',
        'collection_desc'    => 'Chagua kutoka lugha 27 zenye masomo na changamoto. Pia, unganisha na wanafunzi ulimwenguni katika vyumba vyetu vya mazungumzo.',

        'lang_label'      => 'Lugha za Afrika',
        'lang_value'      => 'Kinyarwanda, Kiswahili, Zulu',
        'dev_label'       => 'Lugha za Ulaya',
        'dev_value'       => 'Kifaransa, Kipolandi, Kiswidi, Kidenmaki',
        'coding_label'    => 'Lugha za Asia',
        'coding_value'    => 'Kijapani, Kikorea, Kichina, Kihindi',
        'classroom_label' => 'Lugha za Dunia',
        'classroom_value' => 'Kiarabu, Kituruki, Kireno na zaidi',

        'mission_lead' => 'Kujifunza lugha ni kuhusu kuelewa, kuwasiliana, na kuzungumza kwa imani.',
        'mission_body' => 'Jukwaa letu linasaidia wanafunzi kujifunza lugha 27 kwa kutumia lugha wanaijua, kurahisisha kujenga ufasaha hatua kwa hatua.',

        'hiw_eyebrow' => 'Jinsi Inavyofanya Kazi',
        'hiw_title'   => 'Hatua Nne za Ufasaha',

        'hiw_1_label' => 'Sikiliza & Andika',
        'hiw_1_title_1' => 'Funza sikio lako,',
        'hiw_1_title_2' => 'kifungu kimoja kwa wakati.',
        'hiw_1_desc' => 'Unaweza kujifunza kusikiliza kwa kusikiliza na kuandika unachosikia, au kwa kuchagua jibu sahihi. Kila marudio huimarisha sikio lako kwa mazungumzo halisi.',

        'hiw_2_label' => 'Ona & Chagua',
        'hiw_2_title_1' => 'Linganisha maneno',
        'hiw_2_title_2' => 'na unachokiona.',
        'hiw_2_desc' => 'Kwa kuona, unapata picha na kuchagua jibu sahihi kulingana na unachokiona. Inaunganisha msamiati mpya na vitu vya ulimwengu halisi.',

        'hiw_3_label' => 'Maoni ya Papo Hapo',
        'hiw_3_title_1' => 'Jua mara moja',
        'hiw_3_title_2' => 'kama ulipata sahihi.',
        'hiw_3_desc' => 'Ikiwa jibu lako ni sahihi au si sahihi, kiashiria cha kuona — kama uhuishaji — kinakuambia ikiwa ulipata sahihi au la, ili ujifunze na kurekebisha papo hapo.',

        'hiw_4_label' => 'Sikiliza & Zungumza',
        'hiw_4_title_1' => 'Rudia, fanya mazoezi,',
        'hiw_4_title_2' => 'na zungumza kwa sauti.',
        'hiw_4_desc' => 'Sehemu nyingine ni kusikiliza na kuzungumza: unasikiliza kifungu na kurudia unachosikia kufanya mazoezi ya kuzungumza. Inajenga matamshi, mdundo, na imani katika sauti yako mwenyewe.',

        'features_title_1' => 'Kila kitu unachohitaji,',
        'features_title_2' => 'mahali pamoja.',
        'features_sub' => 'Kutoka kujifunza kwa lugha yako hadi kufanya mazungumzo halisi, Playmates inakupa zana za kujenga ufasaha kwa imani.',

        'feat_lang_label'  => 'Lugha',
        'feat_lang_title'  => 'Jifunza kwa Lugha Yako',
        'feat_lang_sub'    => 'Jifunza ukitumia lugha unayoifahamu.',

        'feat_off_label'   => 'Nje ya Mtandao',
        'feat_off_title'   => 'Jifunza na Mtandao Mdogo',
        'feat_off_sub'     => 'Endelea kujifunza hata mtandao ukiwa mdogo.',

        'feat_prac_label'  => 'Mazoezi',
        'feat_prac_title'  => 'Fanya Mazungumzo Halisi',
        'feat_prac_sub'    => 'Jiunge na vyumba na ufanye mazoezi ya kuzungumza na wanafunzi wengine.',

        'cta_title' => 'Safari Yako ya Lugha Inaanza Sasa',
        'cta_sub'   => 'Hakuna kadi ya mkopo inayohitajika. Jifunze kwa kasi yako.',
        'cta_btn'   => 'Anza Kujifunza Bure',

        'footer_desc' => 'Jifunza lugha na zungumza kwa imani. Jifunza lugha 27, fanya mazungumzo halisi, na jenga ufasaha hatua kwa hatua.',
        'footer_learn' => 'Jifunze',
        'footer_languages' => 'Lugha',
        'footer_lessons' => 'Masomo',
        'footer_rooms' => 'Vyumba vya Mazungumzo',
        'footer_offline' => 'Nje ya Mtandao',
        'footer_company' => 'Kampuni',
        'footer_about' => 'Hadithi Yetu',
        'footer_careers' => 'Kazi',
        'footer_blog' => 'Blogu',
        'footer_press' => 'Vyombo vya Habari',
        'footer_support' => 'Msaada',
        'footer_help' => 'Kituo cha Msaada',
        'footer_contact' => 'Wasiliana Nasi',
        'footer_community' => 'Jumuiya',
        'footer_privacy' => 'Sera ya Faragha',
        'copyright' => '© 2026 Playmates. Haki zote zimehifadhiwa.',
        'footer_terms' => 'Masharti',
        'footer_privacy_link' => 'Faragha',
        'footer_cookies' => 'Cookies',

        'login_required' => 'Tafadhali ingia kwanza kuanza kujifunza!',
        'welcome_user' => 'Karibu',
    ],
];

// Helper function to get translation
function t($key) {
    global $translations, $lang;
    return isset($translations[$lang][$key]) ? $translations[$lang][$key] : (isset($translations['en'][$key]) ? $translations['en'][$key] : $key);
}

// Helper function to get language switcher HTML (used inside mobile menu)
function getLangSwitcher($currentLang) {
    $flags = [
        'en' => ['flag' => 'https://flagcdn.com/us.svg', 'label' => 'EN'],
        'rw' => ['flag' => 'https://flagcdn.com/rw.svg', 'label' => 'RW'],
        'sw' => ['flag' => 'https://flagcdn.com/tz.svg', 'label' => 'SW'],
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

// Helper to get flag URL
function getFlagUrl($code) {
    $code = strtolower(str_replace('_', '-', $code));
    return "https://flagcdn.com/$code.svg";
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
  <title>Playmates — Learn Languages and Speak With Confidence</title>
  <link rel="manifest" href="/manifest.json">
  <meta name="theme-color" content="#006f4a">
  <link rel="apple-touch-icon" href="https://res.cloudinary.com/franklinrw/image/upload/v1775217093/icon-192_pin0pv.png">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <meta name="apple-mobile-web-app-title" content="Playmates">

  <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,500;0,9..144,600;1,9..144,500&family=Caveat:wght@600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">

<style>
  :root{
    --cream:#F4F8F3;
    --burgundy:#006f4a;
    --pink:#DDEEE2;
    --olive:#2D785B;
    --lemon:#E2D59A;
    --ink:#173B30;
    --mist:#EDF4EF;
    --page-bg:#ffffff;
    --card-border:#e0e0e0;
    --text-muted:#4a5b52;
  }

  *{margin:0;padding:0;box-sizing:border-box;}
  html, body{
    max-width:100%;
    overflow-x:hidden;
    scroll-behavior:smooth;
  }
  body{
    background:var(--page-bg);
    color:var(--ink);
    font-family:'Inter',sans-serif;
  }

  /* ============================================================
     REVEAL ANIMATIONS
     ============================================================ */
  @keyframes fadeUp{
    0%{ opacity:0; transform:translateY(18px); }
    100%{ opacity:1; transform:translateY(0); }
  }
  @keyframes drift{
    0%{ transform:scale(1) translate3d(0,0,0); }
    50%{ transform:scale(1.06) translate3d(-8px, 8px, 0); }
    100%{ transform:scale(1.02) translate3d(10px,-6px,0); }
  }
  @keyframes floatY{
    0%, 100%{ transform:translateY(0); }
    50%{ transform:translateY(-6px); }
  }

  .reveal{
    opacity:0;
    transform:translateY(24px);
    transition:opacity 0.8s ease, transform 0.8s ease;
  }
  .reveal.visible{ opacity:1; transform:translateY(0); }

  /* ============================================================
     NAVBAR  — matches official Playmates layout
     Left: Languages / Practice / Support
     Center: Playmates wordmark
     Right: Log in / Start + language switcher
     ============================================================ */
  .top-bar{
    position:absolute;
    top:0; left:0; right:0;
    height:3px;
    background:var(--olive);
    z-index:6;
  }

  nav.site-nav{
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:20px 48px;
    position:relative;
    z-index:5;
    flex-shrink:0;
  }

  .nav-left{
    display:flex;
    gap:32px;
    font-weight:600;
    font-size:14px;
    letter-spacing:0.03em;
  }
  .nav-left a{
    cursor:pointer;
    color:var(--cream);
    text-decoration:none;
    transition:transform .25s ease, opacity .25s ease;
    display:inline-block;
  }
  .nav-left a:hover{
    transform:translateY(-2px);
    opacity:0.9;
  }

  /* Logo group: mark + wordmark, absolutely centered */
  .logo{
    position:absolute;
    left:50%;
    transform:translateX(-50%);
    display:flex;
    align-items:center;
    gap:10px;
    text-decoration:none;
    line-height:1;
  }
  .logo-mark{
    width:36px;
    height:36px;
    border-radius:8px;
    object-fit:cover;
    display:block;
    flex-shrink:0;
    box-shadow:0 2px 6px rgba(0,0,0,0.15);
  }
  .logo-text{
    font-family:'Caveat',cursive;
    font-size:40px;
    font-weight:700;
    color:var(--cream);
    line-height:1;
  }

  .nav-right{
    display:flex;
    gap:12px;
    align-items:center;
  }

  .pill-btn{
    border:1.5px solid var(--cream);
    background:transparent;
    border-radius:0;
    padding:10px 20px;
    font-size:13px;
    font-weight:600;
    letter-spacing:0.03em;
    color:var(--cream);
    cursor:pointer;
    display:flex;
    align-items:center;
    gap:8px;
    text-decoration:none;
    font-family:inherit;
    transition:transform .25s ease, background .25s ease, color .25s ease, box-shadow .25s ease;
  }
  .pill-btn:hover{
    transform:translateY(-2px);
    box-shadow:0 10px 20px rgba(23,59,48,0.12);
    background:rgba(244,248,243,0.1);
  }
  .pill-btn.solid{
    background:var(--cream);
    color:var(--ink);
    border-color:var(--cream);
  }
  .pill-btn.solid:hover{
    background:#ffffff;
  }

  /* Language switcher (used in nav-right on desktop, and mobile panel) */
  .lang-switch{
    display:flex;
    align-items:center;
    gap:2px;
    border:1.5px solid rgba(244,248,243,0.45);
    border-radius:40px;
    padding:3px;
    background:rgba(244,248,243,0.08);
    backdrop-filter:blur(4px);
  }
  .lang-option{
    font-family:'Inter',sans-serif;
    font-size:11px;
    font-weight:600;
    letter-spacing:0.02em;
    color:var(--cream);
    background:transparent;
    border:none;
    border-radius:30px;
    padding:4px 9px;
    cursor:pointer;
    transition:all 0.2s ease;
    display:flex;
    align-items:center;
    gap:4px;
  }
  .lang-option.active{
    background:var(--cream);
    color:var(--ink);
  }
  .lang-option:not(.active):hover{ color:#ffffff; }
  .flag-icon{
    width:16px;
    height:11px;
    border-radius:2px;
    object-fit:cover;
    display:inline-block;
  }

  /* Mobile nav toggle */
  .mobile-nav-toggle{
    display:none;
    align-items:center;
    gap:8px;
    border:1.5px solid var(--cream);
    border-radius:0;
    background:transparent;
    color:var(--cream);
    font-family:'Inter',sans-serif;
    font-size:13px;
    font-weight:600;
    letter-spacing:0.03em;
    padding:10px 16px;
    cursor:pointer;
    transition:all 0.2s ease;
  }
  .mobile-nav-toggle:hover{ background:rgba(244,248,243,0.1); }
  .mobile-nav-toggle .plus{ font-size:18px; line-height:1; font-weight:400; }

  .mobile-nav-panel{
    display:none;
    position:absolute;
    top:78px;
    right:24px;
    z-index:9999;
    background:var(--ink);
    border:1px solid rgba(244,248,243,0.15);
    padding:10px;
    flex-direction:column;
    gap:4px;
    min-width:230px;
    box-shadow:0 16px 40px rgba(0,0,0,0.3);
  }
  .mobile-nav-panel.open{ display:flex !important; }
  .mobile-nav-panel a{
    color:var(--cream);
    font-size:14px;
    font-weight:500;
    text-decoration:none;
    padding:10px 14px;
    transition:background 0.2s ease;
    display:block;
  }
  .mobile-nav-panel a:hover{ background:rgba(244,248,243,0.12); }
  .mobile-nav-panel .user-info{
    color:var(--cream);
    padding:10px 14px;
    border-bottom:1px solid rgba(244,248,243,0.12);
    font-size:13px;
    font-weight:600;
  }
  .mobile-nav-panel .lang-switch{
    margin:8px 14px 4px;
    align-self:flex-start;
  }

  /* ============================================================
     HERO
     ============================================================ */
  html, body{ height:100%; }
  .page{
    position:relative;
    display:flex;
    flex-direction:column;
    height:100vh;
    overflow:hidden;
  }
  .hero-bg{
    position:absolute;
    inset:0;
    background-image:url('https://res.cloudinary.com/franklinrw/image/upload/v1789122307/Untitled_Project_-_artboard-1_1_k2hvni.png');
    background-size:cover;
    background-position:center;
    z-index:0;
    animation:drift 16s ease-in-out infinite alternate;
    contain:layout paint style;
  }
  .hero-bg::after{
    content:"";
    position:absolute;
    inset:0;
    background:linear-gradient(180deg, rgba(0,0,0,0.35) 0%, rgba(0,0,0,0.02) 22%, rgba(0,0,0,0.05) 55%, rgba(0,0,0,0.45) 100%);
  }
  .hero{
    position:relative;
    flex:1;
    min-height:0;
    display:flex;
    align-items:flex-end;
    overflow:hidden;
    padding:0 48px 56px;
    z-index:1;
    animation:fadeUp .85s cubic-bezier(.2,.7,.3,1) both;
  }
  .hero-row{
    display:flex;
    width:100%;
    align-items:flex-end;
    justify-content:space-between;
    gap:40px;
  }
  h1.headline{
    font-family:'Fraunces',serif;
    font-weight:500;
    font-size:clamp(28px, 5.2vw, 72px);
    line-height:1.02;
    color:var(--cream);
    text-align:left;
    max-width:700px;
    animation:fadeUp .9s cubic-bezier(.2,.7,.3,1) both;
  }
  h1.headline em{
    font-style:italic;
    font-weight:400;
  }
  .hero-side{
    display:flex;
    flex-direction:column;
    align-items:flex-start;
    gap:20px;
    max-width:280px;
    flex-shrink:0;
  }
  .subhead{
    font-size:clamp(12px, 1.1vw, 15px);
    line-height:1.5;
    color:var(--cream);
    max-width:280px;
    animation:fadeUp 1s cubic-bezier(.2,.7,.3,1) both;
  }
  .cta-main{
    background:var(--cream);
    color:var(--ink);
    border:none;
    border-radius:6px;
    padding:14px 22px;
    font-weight:600;
    font-size:14px;
    cursor:pointer;
    display:flex;
    align-items:center;
    gap:10px;
    text-decoration:none;
    font-family:inherit;
    transition:transform .25s ease, box-shadow .25s ease;
    animation:fadeUp 1.1s cubic-bezier(.2,.7,.3,1) both;
  }
  .cta-main:hover{
    transform:translateY(-2px);
    box-shadow:0 10px 20px rgba(23,59,48,0.18);
  }

  /* ============================================================
     LANGUAGE STRIP
     ============================================================ */
  .languages-strip{
    background:#e9e3df;
    padding:18px 0;
    border-top:1px solid rgba(43,18,16,0.08);
    border-bottom:1px solid rgba(43,18,16,0.08);
    overflow:hidden;
    contain:layout paint;
  }
  .languages-track{
    display:flex;
    width:max-content;
    align-items:center;
    gap:28px;
    will-change:transform;
  }
  .languages-strip.is-visible .languages-track{
    animation:scrollLanguages 28s linear infinite;
  }
  .lang-item{
    display:flex;
    align-items:center;
    gap:10px;
    min-width:max-content;
    font-size:13px;
    font-weight:700;
    letter-spacing:0.08em;
    text-transform:uppercase;
    color:rgba(43,18,16,0.7);
  }
  .lang-item img{
    width:26px;
    height:18px;
    object-fit:cover;
    border-radius:2px;
    box-shadow:0 0 0 1px rgba(43,18,16,0.08);
    display:block;
  }
  @keyframes scrollLanguages{
    from{ transform:translateX(0); }
    to{ transform:translateX(-50%); }
  }

  /* ============================================================
     MISSION
     ============================================================ */
  .mission{
    background:var(--mist);
    display:flex;
    align-items:center;
    gap:40px;
    padding:88px 48px;
    overflow:hidden;
    animation:fadeUp .9s cubic-bezier(.2,.7,.3,1) both;
    contain:layout paint;
  }
  .mission-text{
    flex:1;
    max-width:420px;
  }
  .mission-lead{
    font-family:'Fraunces',serif;
    font-weight:500;
    font-size:clamp(20px, 2.4vw, 28px);
    line-height:1.35;
    color:var(--ink);
    margin-bottom:22px;
  }
  .mission-body{
    font-size:14px;
    line-height:1.7;
    color:var(--ink);
    opacity:0.7;
    max-width:360px;
  }
  .mission-visual{
    flex:1;
    display:flex;
    align-items:flex-end;
    justify-content:flex-end;
    gap:14px;
    height:340px;
    transform:rotate(-6deg) translateX(30px);
    contain:layout paint;
  }
  .bar{
    width:38px;
    border-radius:4px 4px 0 0;
    background:var(--cream);
    border:1px solid rgba(43,18,16,0.15);
    transform-origin:bottom center;
  }
  .mission.is-visible .bar{ animation:floatY 3s ease-in-out infinite; }
  .mission.is-visible .bar:nth-child(2){ animation-delay:.08s; }
  .mission.is-visible .bar:nth-child(3){ animation-delay:.16s; }
  .mission.is-visible .bar:nth-child(4){ animation-delay:.24s; }
  .mission.is-visible .bar:nth-child(5){ animation-delay:.32s; }
  .mission.is-visible .bar:nth-child(6){ animation-delay:.4s; }
  .mission.is-visible .bar:nth-child(7){ animation-delay:.48s; }
  .bar:nth-child(1){ height:52%; }
  .bar:nth-child(2){ height:68%; background:var(--ink); }
  .bar:nth-child(3){ height:80%; }
  .bar:nth-child(4){ height:64%; background:var(--olive); }
  .bar:nth-child(5){ height:92%; }
  .bar:nth-child(6){ height:74%; background:var(--ink); }
  .bar:nth-child(7){ height:100%; background:var(--burgundy); }

  /* ============================================================
     HOW IT WORKS
     ============================================================ */
  .hiw-top{
    display:grid;
    grid-template-columns:260px 1fr;
    align-items:stretch;
    border-bottom:1px solid var(--card-border);
  }
  .hiw-label{
    display:flex;
    flex-direction:column;
    align-items:flex-start;
    justify-content:flex-start;
    padding:48px 40px 48px 48px;
    border-right:1px solid var(--card-border);
  }
  .hiw-badge{
    display:inline-flex;
    align-items:center;
    gap:8px;
    background:var(--ink);
    color:#ffffff;
    padding:6px 14px;
    font-size:11px;
    font-weight:700;
    letter-spacing:0.8px;
    text-transform:uppercase;
    margin-bottom:24px;
    line-height:1;
  }
  .hiw-headline-wrap{
    display:flex;
    flex-direction:column;
    justify-content:center;
    padding:48px 48px 48px 56px;
  }
  .hiw-headline{
    font-family:'Fraunces',serif;
    font-weight:500;
    font-size:clamp(28px, 3.6vw, 52px);
    line-height:1.12;
    letter-spacing:-0.5px;
    color:var(--ink);
    max-width:700px;
  }
  .hiw-sub{
    font-size:15px;
    line-height:1.7;
    color:var(--ink);
    opacity:0.68;
    max-width:560px;
    margin-top:18px;
  }
  .hiw-grid{
    display:grid;
    grid-template-columns:repeat(2, 1fr);
    border-bottom:1px solid var(--card-border);
  }
  .hiw-card{
    display:flex;
    flex-direction:column;
    border-right:1px solid var(--card-border);
    border-bottom:1px solid var(--card-border);
    overflow:hidden;
    background:#ffffff;
    contain:layout paint;
  }
  .hiw-card:nth-child(2n){ border-right:none; }
  .hiw-card:nth-child(3),
  .hiw-card:nth-child(4){ border-bottom:none; }

  .hiw-image{
    position:relative;
    width:100%;
    aspect-ratio:16 / 10;
    overflow:hidden;
    background:#e3ebe6;
    isolation:isolate;
    contain:layout paint;
  }
  .hiw-image canvas{
    position:absolute;
    inset:0;
    width:100%;
    height:100%;
    display:block;
    z-index:1;
    cursor:crosshair;
    background:#e3ebe6;
  }
  .hiw-image img{
    position:absolute;
    inset:0;
    width:100%;
    height:100%;
    object-fit:cover;
    display:block;
    z-index:1;
  }
  .hiw-feedback{
    position:absolute;
    bottom:16px;
    right:16px;
    display:flex;
    gap:8px;
    z-index:3;
    pointer-events:none;
  }
  .hiw-pill{
    width:42px;
    height:42px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:21px;
    color:#ffffff;
    box-shadow:0 4px 14px rgba(20,20,15,0.22);
  }
  .hiw-section.is-visible .hiw-pill{
    animation:feedbackPulse 2.4s ease-in-out infinite;
  }
  .hiw-pill-correct{ background:var(--burgundy); }
  .hiw-pill-wrong{ background:#c4483a; animation-delay:1.2s; }
  @keyframes feedbackPulse{
    0%, 100%{ transform:scale(1); opacity:1; }
    50%{ transform:scale(1.12); opacity:0.92; }
  }
  .hiw-body{
    padding:32px 40px 40px 48px;
    display:flex;
    flex-direction:column;
    gap:12px;
    flex:1;
  }
  .hiw-label-small{
    display:inline-flex;
    align-items:center;
    gap:8px;
    font-size:11px;
    font-weight:700;
    letter-spacing:0.12em;
    text-transform:uppercase;
    color:var(--olive);
  }
  .hiw-label-small i{ font-size:15px; }
  .hiw-title{
    font-family:'Fraunces',serif;
    font-weight:500;
    font-size:26px;
    line-height:1.2;
    letter-spacing:-0.3px;
    color:var(--ink);
  }
  .hiw-title .orange{ color:var(--burgundy); }
  .hiw-desc{
    font-size:14px;
    line-height:1.7;
    color:var(--ink);
    opacity:0.7;
    max-width:460px;
  }

  /* ============================================================
     FEATURES
     ============================================================ */
  .features-section{
    background:#ffffff;
    border-bottom:1px solid var(--card-border);
    contain:layout paint;
  }
  .features-headline-wrap{
    display:flex;
    flex-direction:column;
    justify-content:center;
    padding:80px 48px 56px;
    max-width:1000px;
    margin:0 auto;
    text-align:left;
  }
  .features-headline{
    font-family:'Fraunces',serif;
    font-weight:500;
    font-size:clamp(28px, 3.6vw, 52px);
    line-height:1.12;
    letter-spacing:-0.5px;
    color:var(--ink);
    max-width:700px;
  }
  .features-headline .orange{ color:var(--burgundy); }
  .features-sub{
    font-size:15px;
    line-height:1.7;
    color:var(--ink);
    opacity:0.68;
    max-width:560px;
    margin-top:18px;
  }

  /* Cards */
  /* ============================================================
     FEATURE CARDS  — matched to reference design
     ============================================================ */
  .cards-container{
    max-width:1180px;
    margin:0 auto;
    padding:0 2rem 4rem;
    contain:layout paint;
  }
  .cards-grid{
    display:grid;
    grid-template-columns:repeat(3, 1fr);
    gap:1.5rem;
    align-items:stretch;
  }
  .feature-card{
    background:#ffffff;
    border:1px solid #ececec;
    border-radius:20px;
    box-shadow:0 1px 2px rgba(20,20,15,0.03);
    display:flex;
    flex-direction:column;
    padding:1.75rem;
    opacity:0;
    transform:translateY(14px);
    animation:riseCards 0.6s cubic-bezier(.2,.7,.3,1) forwards;
    transition:box-shadow .3s ease, transform .3s ease;
    contain:layout paint;
  }
  .feature-card:hover{
    box-shadow:0 10px 24px rgba(20,20,15,0.09);
    transform:translateY(-3px);
  }
  .feature-card:nth-of-type(1){ animation-delay:.05s; }
  .feature-card:nth-of-type(2){ animation-delay:.18s; }
  .feature-card:nth-of-type(3){ animation-delay:.31s; }
  @keyframes riseCards{ to{ opacity:1; transform:translateY(0); } }

  /* ----- Header row: green icon + uppercase label ----- */
  .feature-header{
    display:flex;
    align-items:center;
    gap:1rem;
    margin-bottom:1.5rem;
  }
  .feature-icon-wrap{
    width:48px;
    height:48px;
    border-radius:14px;
    background:#0a6b4d;
    color:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:22px;
    flex-shrink:0;
  }
  .cards-container.is-visible .feature-icon-wrap{
    animation:glowCards 2.6s ease-in-out infinite;
  }
  .feature-label{
    font-size:12px;
    font-weight:700;
    letter-spacing:0.14em;
    text-transform:uppercase;
    color:#0a6b4d;
    line-height:1;
  }
  @keyframes glowCards{
    0%{ box-shadow:0 0 0 0 rgba(10,107,77,0.35); }
    70%{ box-shadow:0 0 0 12px rgba(10,107,77,0); }
    100%{ box-shadow:0 0 0 0 rgba(10,107,77,0); }
  }

  /* ----- Big grey visual panel (same in all 3 cards) ----- */
  .feature-visual{
    background:#f4f1ea;
    border-radius:16px;
    padding:1.5rem 1.25rem;
    height:180px;
    margin-bottom:1.75rem;
    display:flex;
    align-items:center;
    justify-content:center;
    position:relative;
    overflow:hidden;
  }

  /* ----- Card title + subtitle ----- */
  .feature-title{
    font-size:20px;
    font-weight:600;
    letter-spacing:-0.01em;
    line-height:1.25;
    margin-bottom:0.5rem;
    color:#0f2a22;
  }
  .feature-subtitle{
    font-size:14px;
    color:rgba(23,23,21,0.62);
    line-height:1.5;
  }
  .feature-card-cta{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    align-self:flex-start;
    margin-top:1rem;
    padding:0.8rem 1.15rem;
    border-radius:999px;
    background:#0a6b4d;
    color:#ffffff;
    font-size:14px;
    font-weight:700;
    line-height:1;
    text-decoration:none;
    box-shadow:0 10px 20px rgba(10,107,77,0.18);
    transition:transform .2s ease, box-shadow .2s ease;
  }
  .feature-card-cta:hover{
    transform:translateY(-1px);
    box-shadow:0 14px 24px rgba(10,107,77,0.22);
  }

  /* ============ CARD 1: Language chips ============ */
  .chip-field{
    display:flex;
    flex-wrap:wrap;
    gap:0.6rem;
    justify-content:center;
    align-content:center;
    width:100%;
  }
  .chip{
    background:#fff;
    border:1px solid #e8e4dc;
    color:#0f2a22;
    font-size:13px;
    font-weight:500;
    padding:0.55rem 0.95rem;
    border-radius:999px;
    display:inline-flex;
    align-items:center;
    gap:0.4rem;
    line-height:1;
    white-space:nowrap;
  }
  .chip i{
    color:#0a6b4d;
    font-size:14px;
    margin:0;
  }
  .cards-container.is-visible .chip{
    animation:chipCycle 3.6s ease-in-out infinite;
  }
  .chip:nth-child(1){ animation-delay:0s; }
  .chip:nth-child(2){ animation-delay:0.55s; }
  .chip:nth-child(3){ animation-delay:1.1s; }
  .chip:nth-child(4){ animation-delay:1.65s; }
  .chip:nth-child(5){ animation-delay:2.2s; }
  @keyframes chipCycle{
    0%, 100%{ background:#fff; color:#0f2a22; border-color:#e8e4dc; }
    12%{ background:#0a6b4d; color:#fff; border-color:#0a6b4d; transform:scale(1.04); }
    24%{ background:#fff; color:#0f2a22; border-color:#e8e4dc; transform:scale(1); }
  }

  /* ============ CARD 2: Download lesson rows ============ */
  .download-list{
    width:100%;
    display:flex;
    flex-direction:column;
    gap:1rem;
    justify-content:center;
  }
  .dl-row{
    display:flex;
    align-items:center;
    gap:0.85rem;
    width:100%;
  }
  .dl-label{
    font-size:14px;
    font-weight:500;
    width:72px;
    flex-shrink:0;
    color:#0f2a22;
    line-height:1;
  }
  .dl-track{
    flex:1;
    height:8px;
    background:#e4e1da;
    border-radius:999px;
    overflow:hidden;
    min-width:0;
  }
  .dl-fill{
    height:100%;
    background:#0a6b4d;
    border-radius:999px;
    width:100%;
  }
  .dl-row:nth-child(1) .dl-fill{ width:100%; }
  .dl-row:nth-child(2) .dl-fill{ width:100%; }
  .dl-row:nth-child(3) .dl-fill{ width:62%; }
  .cards-container.is-visible .dl-row:nth-child(1) .dl-fill{
    animation:fillBar 2.8s ease-in-out infinite alternate;
  }
  .cards-container.is-visible .dl-row:nth-child(2) .dl-fill{
    animation:fillBar 2.8s ease-in-out infinite alternate;
    animation-delay:0.4s;
  }
  .cards-container.is-visible .dl-row:nth-child(3) .dl-fill{
    animation:fillBar 2.8s ease-in-out infinite alternate;
    animation-delay:0.8s;
  }
  @keyframes fillBar{
    0%{ width:6%; }
    100%{ width:100%; }
  }
  .dl-check{
    width:22px;
    height:22px;
    border-radius:50%;
    background:#0a6b4d;
    color:#fff;
    font-size:12px;
    flex-shrink:0;
    display:flex;
    align-items:center;
    justify-content:center;
    opacity:1;
  }
  .dl-row:nth-child(1) .dl-check{ background:#0a6b4d; color:#fff; }
  .dl-row:nth-child(2) .dl-check{ background:rgba(10,107,77,0.15); color:rgba(10,107,77,0.35); }
  .dl-row:nth-child(3) .dl-check{ background:rgba(10,107,77,0.15); color:transparent; }

  /* ============ CARD 3: Speaking room ============ */
  .room{
    position:relative;
    width:100%;
    height:100%;
    max-width:240px;
    margin:0 auto;
  }
  /* Dashed ring, centered */
  .ring{
    position:absolute;
    top:50%; left:50%;
    width:150px; height:150px;
    border:1.5px dashed rgba(10,107,77,0.35);
    border-radius:50%;
    transform:translate(-50%,-50%);
    opacity:1;
  }
  .cards-container.is-visible .ring{
    animation:pulseRing 3s ease-out infinite;
  }
  .cards-container.is-visible .ring.r2{ animation-delay:1s; }
  @keyframes pulseRing{
    0%{ width:100px; height:100px; opacity:0.7; }
    100%{ width:180px; height:180px; opacity:0; }
  }

  /* Green mic circle, centered */
  .room-center{
    position:absolute;
    top:50%; left:50%;
    transform:translate(-50%,-50%);
    width:52px;
    height:52px;
    border-radius:50%;
    background:#0a6b4d;
    color:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:22px;
    z-index:3;
    box-shadow:0 4px 12px rgba(10,107,77,0.35);
  }

  /* Avatars spread in a circle around the mic */
  .avatar-bubble{
    position:absolute;
    top:50%; left:50%;
    width:38px;
    height:38px;
    border-radius:50%;
    background-size:cover;
    background-position:center;
    border:2px solid #fff;
    box-shadow:0 2px 6px rgba(20,20,15,0.15);
    z-index:4;
    margin:-19px 0 0 -19px;
  }
  .a1{ transform:translate(-58px, -42px); }
  .a2{ transform:translate( 58px, -42px); }
  .a3{ transform:translate(-62px,  48px); }
  .a4{ transform:translate( 62px,  48px); }

  .cards-container.is-visible .avatar-bubble{
    animation:floatBob 2.8s ease-in-out infinite;
  }
  .a1{ animation-delay:0s; }
  .a2{ animation-delay:0.5s; }
  .a3{ animation-delay:1s; }
  .a4{ animation-delay:1.5s; }
  @keyframes floatBob{
    0%, 100%{ transform:translateY(0); }
    50%     { transform:translateY(-6px); }
  }
  .cards-container.is-visible .a1{ animation-name:float1; }
  .cards-container.is-visible .a2{ animation-name:float2; }
  .cards-container.is-visible .a3{ animation-name:float3; }
  .cards-container.is-visible .a4{ animation-name:float4; }
  @keyframes float1{
    0%,100%{ transform:translate(-58px, -42px); }
    50%    { transform:translate(-58px, -48px); }
  }
  @keyframes float2{
    0%,100%{ transform:translate( 58px, -42px); }
    50%    { transform:translate( 58px, -48px); }
  }
  @keyframes float3{
    0%,100%{ transform:translate(-62px,  48px); }
    50%    { transform:translate(-62px,  42px); }
  }
  @keyframes float4{
    0%,100%{ transform:translate( 62px,  48px); }
    50%    { transform:translate( 62px,  42px); }
  }

  /* Three dots below the ring */
  .typing-dots{
    position:absolute;
    bottom:-4px; left:50%;
    transform:translateX(-50%);
    display:flex;
    gap:5px;
    z-index:5;
  }
  .typing-dots span{
    width:7px; height:7px;
    border-radius:50%;
    background:rgba(10,107,77,0.25);
  }
  .cards-container.is-visible .typing-dots span{
    animation:dotBounce 1.4s ease-in-out infinite;
  }
  .cards-container.is-visible .typing-dots span:nth-child(2){ animation-delay:0.15s; }
  .cards-container.is-visible .typing-dots span:nth-child(3){ animation-delay:0.3s; }
  @keyframes dotBounce{
    0%, 60%, 100%{ transform:translateY(0); opacity:0.5; }
    30%{ transform:translateY(-3px); opacity:1; }
  }

  /* ============ Responsive ============ */
  @media (max-width:900px){
    .cards-grid{ grid-template-columns:1fr; }
    .cards-container{ padding:0 1rem 3rem; }
    .feature-visual{ height:170px; }
  }

  /* ============================================================
     SIMPLE CTA
     ============================================================ */
  .simple-cta{
    background:var(--burgundy);
    padding:100px 48px;
    text-align:center;
  }
  .simple-cta-inner{
    max-width:720px;
    margin:0 auto;
    display:flex;
    flex-direction:column;
    align-items:center;
  }
  .simple-cta-title{
    font-family:'Fraunces',serif;
    font-weight:500;
    font-size:clamp(30px, 4vw, 54px);
    line-height:1.12;
    letter-spacing:-0.5px;
    color:#ffffff;
    margin-bottom:16px;
  }
  .simple-cta-sub{
    font-size:15px;
    line-height:1.7;
    color:rgba(255,255,255,0.78);
    margin-bottom:36px;
    max-width:420px;
  }
  .simple-cta-btn{
    background:#ffffff;
    color:var(--burgundy);
    border:none;
    border-radius:4px;
    padding:16px 32px;
    font-size:14px;
    font-weight:700;
    letter-spacing:0.02em;
    cursor:pointer;
    display:inline-flex;
    align-items:center;
    gap:12px;
    font-family:inherit;
    text-decoration:none;
    transition:transform .25s ease, box-shadow .25s ease, background .25s ease;
    box-shadow:0 4px 14px rgba(0,0,0,0.12);
  }
  .simple-cta-btn:hover{
    transform:translateY(-2px);
    box-shadow:0 10px 26px rgba(0,0,0,0.22);
    background:#f4f8f3;
  }
  .simple-cta-btn .arrow-circle{
    width:26px;
    height:26px;
    border-radius:50%;
    border:1.5px solid var(--burgundy);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:14px;
    flex-shrink:0;
  }

  /* ============================================================
     FOOTER
     ============================================================ */
  .site-footer{
    background:#0f2a22;
    color:rgba(244,248,243,0.7);
    padding:64px 48px 32px;
    border-top:1px solid rgba(255,255,255,0.06);
  }
  .footer-grid{
    display:grid;
    grid-template-columns:2fr 1fr 1fr 1fr;
    gap:48px;
    padding-bottom:48px;
    border-bottom:1px solid rgba(255,255,255,0.08);
  }
  .footer-brand{ max-width:320px; }
  .footer-logo{
    font-family:'Caveat',cursive;
    font-size:40px;
    font-weight:700;
    color:var(--cream);
    margin-bottom:14px;
    line-height:1;
  }
  .footer-desc{
    font-size:13px;
    line-height:1.7;
    color:rgba(244,248,243,0.6);
    margin-bottom:20px;
  }
  .footer-social{
    display:flex;
    gap:10px;
  }
  .footer-social a{
    width:38px;
    height:38px;
    border-radius:50%;
    border:1px solid rgba(255,255,255,0.16);
    display:flex;
    align-items:center;
    justify-content:center;
    color:var(--cream);
    text-decoration:none;
    font-size:17px;
    transition:background .25s ease, transform .25s ease;
  }
  .footer-social a:hover{
    background:var(--burgundy);
    border-color:var(--burgundy);
    transform:translateY(-2px);
  }
  .footer-col h4{
    font-size:12px;
    font-weight:700;
    letter-spacing:0.1em;
    text-transform:uppercase;
    color:var(--cream);
    margin-bottom:18px;
  }
  .footer-col ul{
    list-style:none;
    display:flex;
    flex-direction:column;
    gap:10px;
  }
  .footer-col ul li a{
    font-size:13px;
    color:rgba(244,248,243,0.65);
    text-decoration:none;
    transition:color .25s ease, transform .25s ease;
    display:inline-block;
  }
  .footer-col ul li a:hover{
    color:var(--cream);
    transform:translateX(3px);
  }
  .footer-bottom{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding-top:28px;
    gap:20px;
    flex-wrap:wrap;
  }
  .footer-bottom p{
    font-size:12px;
    color:rgba(244,248,243,0.45);
  }
  .footer-bottom-links{
    display:flex;
    gap:24px;
  }
  .footer-bottom-links a{
    font-size:12px;
    color:rgba(244,248,243,0.55);
    text-decoration:none;
    transition:color .25s ease;
  }
  .footer-bottom-links a:hover{ color:var(--cream); }

  /* ============================================================
     TOAST
     ============================================================ */
  .toast-message{
    position:fixed;
    bottom:30px;
    left:50%;
    transform:translateX(-50%);
    background:#173B30;
    color:#ffffff;
    padding:12px 24px;
    border-radius:60px;
    font-size:0.85rem;
    font-weight:500;
    z-index:10000;
    box-shadow:0 8px 20px rgba(0,0,0,0.2);
    font-family:'Inter', sans-serif;
    animation:fadeUp 0.25s ease;
    white-space:nowrap;
    pointer-events:none;
  }

  /* ============================================================
     RESPONSIVE
     ============================================================ */
  @media (max-width:900px){
    nav.site-nav{
      flex-wrap:wrap;
      gap:8px;
      padding:12px 16px;
      row-gap:4px;
    }
    .logo{
      position:static;
      transform:none;
      order:0;
      width:auto;
      justify-content:flex-start;
      margin-bottom:0;
    }
    .logo-mark{
      width:28px;
      height:28px;
      border-radius:6px;
    }
    .logo-text{
      font-size:28px;
    }
    .nav-left{ display:none; }
    .nav-right{
      display:flex;
      align-items:center;
      gap:8px;
      margin-left:auto;
    }
    .nav-right .pill-btn{
      display:flex;
      padding:10px 18px;
      border-radius:999px;
      font-size:13px;
    }
    .nav-right .pill-btn.solid{ display:none; }
    .nav-right .lang-switch{ display:none; }
    .mobile-nav-toggle{
      display:flex;
      margin-left:0;
      border-radius:999px;
      padding:10px 18px;
      font-size:13px;
    }
    .hero{ padding:0 24px 32px; }
    .hero-row{
      flex-direction:column;
      align-items:flex-start;
      gap:20px;
    }
    h1.headline{
      font-size:clamp(26px, 8vw, 44px);
      max-width:100%;
    }
    .hero-side{ max-width:100%; }
    .subhead{ max-width:100%; }

    .mission{
      flex-direction:column;
      align-items:flex-start;
      padding:56px 24px;
      gap:40px;
    }
    .mission-text{ max-width:100%; }
    .mission-visual{
      width:100%;
      height:220px;
      transform:rotate(-6deg) translateX(10px);
    }

    .hiw-top{ grid-template-columns:1fr; }
    .hiw-label{
      padding:32px 24px;
      border-right:none;
      border-bottom:1px solid var(--card-border);
    }
    .hiw-headline-wrap{ padding:32px 24px; }
    .hiw-grid{ grid-template-columns:1fr; }
    .hiw-card{
      border-right:none;
      border-bottom:1px solid var(--card-border);
    }
    .hiw-card:last-child{ border-bottom:none; }
    .hiw-body{ padding:28px 24px 32px; }
    .hiw-title{ font-size:21px; }

    .features-headline-wrap{ padding:56px 24px 32px; }
    .cards-grid{ grid-template-columns:1fr; }
    .cards-container{ padding:2rem 1rem; }

    .simple-cta{ padding:72px 24px; }
    .simple-cta-title{ font-size:28px; }
    .simple-cta-sub{ font-size:14px; margin-bottom:28px; }
    .simple-cta-btn{ padding:14px 26px; font-size:13px; }

    .site-footer{ padding:48px 24px 24px; }
    .footer-grid{
      grid-template-columns:1fr 1fr;
      gap:32px;
    }
    .footer-brand{ grid-column:1 / -1; max-width:100%; }
    .footer-bottom{
      flex-direction:column;
      align-items:flex-start;
      gap:14px;
    }
  }

  @media (max-width:480px){
    .footer-grid{ grid-template-columns:1fr; }
  }
</style>
</head>
<body>

  <!-- ============================================================
       HERO / PAGE
       ============================================================ -->
  <div class="page">
    <div class="hero-bg"></div>
    <div class="top-bar"></div>

    <!-- ============================================================
         NAVBAR  — mirrors official Playmates layout
         ============================================================ -->
    <nav class="site-nav">
      <div class="nav-left">
        <a href="#languages"><?php echo t('nav_languages'); ?></a>
        <a href="frontend/dashboard.php?lang=<?php echo $lang; ?>"><?php echo t('nav_practice'); ?></a>
        <a href="#support"><?php echo t('nav_support'); ?></a>
      </div>

      <a href="/?lang=<?php echo $lang; ?>" class="logo" aria-label="Playmates home">
        <img
          class="logo-mark"
          src="https://res.cloudinary.com/franklinrw/image/upload/v1755169717/kjk_bnrbmp.png"
          alt="Playmates logo"
          width="36"
          height="36"
          loading="eager"
          decoding="async"
        >
        <span class="logo-text">Playmates</span>
      </a>

      <div class="nav-right">
        <?php echo getLangSwitcher($lang); ?>

        <?php if ($isLoggedIn): ?>
          <a href="logout.php?lang=<?php echo $lang; ?>&redirect=<?php echo urlencode($currentPage); ?>" class="pill-btn">
            <?php echo t('logout_btn'); ?>
          </a>
        <?php else: ?>
          <a href="loginui.php?lang=<?php echo $lang; ?>&redirect=<?php echo urlencode($currentPage); ?>" class="pill-btn">
            <?php echo t('nav_login'); ?>
          </a>
        <?php endif; ?>

        <a href="frontend/dashboard.php?lang=<?php echo $lang; ?>" class="pill-btn solid">
          <?php echo t('nav_start'); ?>
        </a>

        <button class="mobile-nav-toggle" id="mobileToggle" aria-label="Toggle menu" aria-expanded="false">
          <span class="plus">+</span> <?php echo t('nav_menu'); ?>
        </button>
      </div>

      <!-- Mobile dropdown -->
      <div class="mobile-nav-panel" id="mobileNavPanel">
        <?php if ($isLoggedIn): ?>
          <div class="user-info">👋 <?php echo t('welcome_user'); ?>, <?php echo htmlspecialchars($userName); ?></div>
        <?php endif; ?>

        <a href="#languages"><?php echo t('nav_languages'); ?></a>
        <a href="frontend/dashboard.php?lang=<?php echo $lang; ?>"><?php echo t('nav_practice'); ?></a>
        <a href="#support"><?php echo t('nav_support'); ?></a>
        <a href="about.php?lang=<?php echo $lang; ?>"><?php echo t('footer_about'); ?></a>
        <a href="careers.php?lang=<?php echo $lang; ?>"><?php echo t('footer_careers'); ?></a>

        <?php if ($isLoggedIn): ?>
          <a href="logout.php?lang=<?php echo $lang; ?>&redirect=<?php echo urlencode($currentPage); ?>">
            <?php echo t('logout_btn'); ?>
          </a>
        <?php else: ?>
          <a href="loginui.php?lang=<?php echo $lang; ?>&redirect=<?php echo urlencode($currentPage); ?>">
            <?php echo t('nav_login'); ?>
          </a>
        <?php endif; ?>

        <a href="frontend/dashboard.php?lang=<?php echo $lang; ?>">
          <?php echo t('nav_start'); ?> →
        </a>

        <?php echo getLangSwitcher($lang); ?>
      </div>
    </nav>

    <!-- HERO CONTENT -->
    <section class="hero reveal">
      <div class="hero-row">
        <h1 class="headline">
          <?php echo t('hero_title_1'); ?><br>
          <em><?php echo t('hero_title_2'); ?></em>
        </h1>
        <div class="hero-side">
          <p class="subhead"><?php echo t('hero_sub'); ?></p>
          <a href="frontend/dashboard.php?lang=<?php echo $lang; ?>" class="cta-main">
            <?php echo t('start_playing_btn'); ?> ↗
          </a>
        </div>
      </div>
    </section>
  </div>

  <!-- ============================================================
       LANGUAGE STRIP
       ============================================================ -->
  <section class="languages-strip" aria-label="Language options">
    <div class="languages-track">
      <?php
      // Duplicated for seamless infinite scroll
      $flagsLoop = [
          ['es','Spanish'], ['fr','French'], ['de','German'], ['it','Italian'],
          ['pt','Portuguese'], ['jp','Japanese'], ['kr','Korean'], ['cn','Mandarin'],
          ['sa','Arabic'], ['ru','Russian'], ['in','Hindi'], ['tr','Turkish'],
      ];
      for ($pass = 0; $pass < 2; $pass++):
          foreach ($flagsLoop as $f):
      ?>
        <div class="lang-item">
          <img loading="lazy" decoding="async" src="https://flagcdn.com/w80/<?php echo $f[0]; ?>.png" alt="<?php echo $f[1]; ?> flag">
          <span><?php echo $f[1]; ?></span>
        </div>
      <?php endforeach; endfor; ?>
    </div>
  </section>

  <!-- ============================================================
       FEATURES HEADLINE
       ============================================================ -->
  <section class="features-section reveal">
    <div class="features-headline-wrap">
      <h2 class="features-headline">
        <?php echo t('features_title_1'); ?>
        <span class="orange"><?php echo t('features_title_2'); ?></span>
      </h2>
      <p class="features-sub"><?php echo t('features_sub'); ?></p>
    </div>
  </section>

  <!-- ============================================================
       FEATURE CARDS
       ============================================================ -->
  <div class="cards-container reveal">
    <div class="cards-grid">
      <div class="feature-card reveal">
        <div class="feature-header">
          <div class="feature-icon-wrap"><i class="ti ti-language"></i></div>
          <span class="feature-label"><?php echo t('feat_lang_label'); ?></span>
        </div>
        <div class="feature-visual">
          <div class="chip-field">
            <span class="chip"><i class="ti ti-flag"></i>English</span>
            <span class="chip"><i class="ti ti-flag"></i>Español</span>
            <span class="chip"><i class="ti ti-flag"></i>Français</span>
            <span class="chip"><i class="ti ti-flag"></i>中文</span>
            <span class="chip"><i class="ti ti-flag"></i>العربية</span>
          </div>
        </div>
        <h3 class="feature-title"><?php echo t('feat_lang_title'); ?></h3>
        <p class="feature-subtitle"><?php echo t('feat_lang_sub'); ?></p>
      </div>

      <div class="feature-card reveal">
        <div class="feature-header">
          <div class="feature-icon-wrap"><i class="ti ti-wifi-off"></i></div>
          <span class="feature-label"><?php echo t('feat_off_label'); ?></span>
        </div>
        <div class="feature-visual">
          <div class="download-list">
            <div class="dl-row">
              <span class="dl-label">Greetings</span>
              <div class="dl-track"><div class="dl-fill"></div></div>
              <div class="dl-check"><i class="ti ti-check"></i></div>
            </div>
            <div class="dl-row">
              <span class="dl-label">Shopping</span>
              <div class="dl-track"><div class="dl-fill"></div></div>
              <div class="dl-check"><i class="ti ti-check"></i></div>
            </div>
            <div class="dl-row">
              <span class="dl-label">Travel</span>
              <div class="dl-track"><div class="dl-fill"></div></div>
              <div class="dl-check"><i class="ti ti-check"></i></div>
            </div>
          </div>
        </div>
        <h3 class="feature-title"><?php echo t('feat_off_title'); ?></h3>
        <p class="feature-subtitle"><?php echo t('feat_off_sub'); ?></p>
      </div>

      <div class="feature-card reveal">
        <div class="feature-header">
          <div class="feature-icon-wrap"><i class="ti ti-messages"></i></div>
          <span class="feature-label"><?php echo t('feat_prac_label'); ?></span>
        </div>
        <div class="feature-visual">
          <div class="room">
            <div class="ring r1"></div>
            <div class="ring r2"></div>
            <div class="room-center"><i class="ti ti-microphone"></i></div>
            <div class="avatar-bubble a1" style="background-image:url('https://i.pravatar.cc/64?img=32')"></div>
            <div class="avatar-bubble a2" style="background-image:url('https://i.pravatar.cc/64?img=47')"></div>
            <div class="avatar-bubble a3" style="background-image:url('https://i.pravatar.cc/64?img=15')"></div>
            <div class="avatar-bubble a4" style="background-image:url('https://i.pravatar.cc/64?img=5')"></div>
            <div class="typing-dots"><span></span><span></span><span></span></div>
          </div>
        </div>
        <h3 class="feature-title"><?php echo t('feat_prac_title'); ?></h3>
        <p class="feature-subtitle"><?php echo t('feat_prac_sub'); ?></p>
        <a href="room_dashboard.php?lang=<?php echo $lang; ?>" class="feature-card-cta">Join Practice</a>
      </div>
    </div>
  </div>

  <!-- ============================================================
       MISSION
       ============================================================ -->
  <section class="mission reveal">
    <div class="mission-text">
      <p class="mission-lead"><?php echo t('mission_lead'); ?></p>
      <p class="mission-body"><?php echo t('mission_body'); ?></p>
    </div>
    <div class="mission-visual">
      <div class="bar"></div>
      <div class="bar"></div>
      <div class="bar"></div>
      <div class="bar"></div>
      <div class="bar"></div>
      <div class="bar"></div>
      <div class="bar"></div>
    </div>
  </section>

  <!-- ============================================================
       HOW IT WORKS
       ============================================================ -->
  <section class="hiw-section">
    <div class="hiw-top">
      <div class="hiw-label">
        <span class="hiw-badge">
          <span class="material-icons" style="font-size:14px;">school</span>
          <?php echo t('hiw_eyebrow'); ?>
        </span>
      </div>
      <div class="hiw-headline-wrap">
        <h2 class="hiw-headline"><?php echo t('hiw_title'); ?></h2>
      </div>
    </div>

    <div class="hiw-grid">
      <div class="hiw-card">
        <div class="hiw-image">
          <canvas class="ripple-canvas" data-src="https://res.cloudinary.com/franklinrw/image/upload/v1789045359/Mockuuups_Free_Walking_with_iPhone_X_mockup_mlnci0.jpg"></canvas>
        </div>
        <div class="hiw-body">
          <span class="hiw-label-small"><i class="ti ti-headphones"></i> <?php echo t('hiw_1_label'); ?></span>
          <h3 class="hiw-title"><?php echo t('hiw_1_title_1'); ?> <span class="orange"><?php echo t('hiw_1_title_2'); ?></span></h3>
          <p class="hiw-desc"><?php echo t('hiw_1_desc'); ?></p>
        </div>
      </div>

      <div class="hiw-card">
        <div class="hiw-image">
          <canvas class="ripple-canvas" data-src="https://res.cloudinary.com/franklinrw/image/upload/v1789045081/Mockuuups_Free_iPhone_17_Pro_mockup_with_hand_on_blue_jacket_mzzs8w.jpg"></canvas>
        </div>
        <div class="hiw-body">
          <span class="hiw-label-small"><i class="ti ti-photo"></i> <?php echo t('hiw_2_label'); ?></span>
          <h3 class="hiw-title"><?php echo t('hiw_2_title_1'); ?> <span class="orange"><?php echo t('hiw_2_title_2'); ?></span></h3>
          <p class="hiw-desc"><?php echo t('hiw_2_desc'); ?></p>
        </div>
      </div>

      <div class="hiw-card">
        <div class="hiw-image">
          <canvas class="ripple-canvas" data-src="https://res.cloudinary.com/franklinrw/image/upload/v1789044927/Mockuuups_iPhone_16_mockup_in_a_woman_s_hand_with_concrete_backdrop_h9p6vw.jpg"></canvas>
          <div class="hiw-feedback">
            <div class="hiw-pill hiw-pill-correct"><i class="ti ti-check"></i></div>
            <div class="hiw-pill hiw-pill-wrong"><i class="ti ti-x"></i></div>
          </div>
        </div>
        <div class="hiw-body">
          <span class="hiw-label-small"><i class="ti ti-mood-smile"></i> <?php echo t('hiw_3_label'); ?></span>
          <h3 class="hiw-title"><?php echo t('hiw_3_title_1'); ?> <span class="orange"><?php echo t('hiw_3_title_2'); ?></span></h3>
          <p class="hiw-desc"><?php echo t('hiw_3_desc'); ?></p>
        </div>
      </div>

      <div class="hiw-card">
        <div class="hiw-image">
          <canvas class="ripple-canvas" data-src="https://res.cloudinary.com/franklinrw/image/upload/v1789044924/Mockuuups_Free_Holding_Samsung_S20_mockup_in_front_of_a_bike_jlqxye.jpg"></canvas>
        </div>
        <div class="hiw-body">
          <span class="hiw-label-small"><i class="ti ti-microphone"></i> <?php echo t('hiw_4_label'); ?></span>
          <h3 class="hiw-title"><?php echo t('hiw_4_title_1'); ?> <span class="orange"><?php echo t('hiw_4_title_2'); ?></span></h3>
          <p class="hiw-desc"><?php echo t('hiw_4_desc'); ?></p>
        </div>
      </div>
    </div>
  </section>

  <!-- ============================================================
       SIMPLE CTA
       ============================================================ -->
  <section class="simple-cta reveal">
    <div class="simple-cta-inner">
      <h2 class="simple-cta-title"><?php echo t('cta_title'); ?></h2>
      <p class="simple-cta-sub"><?php echo t('cta_sub'); ?></p>
      <a href="frontend/dashboard.php?lang=<?php echo $lang; ?>" class="simple-cta-btn">
        <?php echo t('cta_btn'); ?>
        <span class="arrow-circle">↗</span>
      </a>
    </div>
  </section>

  <!-- ============================================================
       FOOTER
       ============================================================ -->
  <footer class="site-footer" id="support">
    <div class="footer-grid">
      <div class="footer-brand">
        <div class="footer-logo">Playmates</div>
        <p class="footer-desc"><?php echo t('footer_desc'); ?></p>
        <div class="footer-social">
          <a href="#" aria-label="Twitter"><i class="ti ti-brand-x"></i></a>
          <a href="#" aria-label="Instagram"><i class="ti ti-brand-instagram"></i></a>
          <a href="#" aria-label="YouTube"><i class="ti ti-brand-youtube"></i></a>
          <a href="#" aria-label="LinkedIn"><i class="ti ti-brand-linkedin"></i></a>
        </div>
      </div>

      <div class="footer-col">
        <h4><?php echo t('footer_learn'); ?></h4>
        <ul>
          <li><a href="#languages"><?php echo t('footer_languages'); ?></a></li>
          <li><a href="frontend/dashboard.php?lang=<?php echo $lang; ?>"><?php echo t('footer_lessons'); ?></a></li>
          <li><a href="room_dashboard.php?lang=<?php echo $lang; ?>"><?php echo t('footer_rooms'); ?></a></li>
          <li><a href="#offline"><?php echo t('footer_offline'); ?></a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4><?php echo t('footer_company'); ?></h4>
        <ul>
          <li><a href="about.php?lang=<?php echo $lang; ?>"><?php echo t('footer_about'); ?></a></li>
          <li><a href="careers.php?lang=<?php echo $lang; ?>"><?php echo t('footer_careers'); ?></a></li>
          <li><a href="blog.php?lang=<?php echo $lang; ?>"><?php echo t('footer_blog'); ?></a></li>
          <li><a href="press.php?lang=<?php echo $lang; ?>"><?php echo t('footer_press'); ?></a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4><?php echo t('footer_support'); ?></h4>
        <ul>
          <li><a href="help.php?lang=<?php echo $lang; ?>"><?php echo t('footer_help'); ?></a></li>
          <li><a href="contact.php?lang=<?php echo $lang; ?>"><?php echo t('footer_contact'); ?></a></li>
          <li><a href="community.php?lang=<?php echo $lang; ?>"><?php echo t('footer_community'); ?></a></li>
          <li><a href="privacy.php?lang=<?php echo $lang; ?>"><?php echo t('footer_privacy'); ?></a></li>
        </ul>
      </div>
    </div>

    <div class="footer-bottom">
      <p><?php echo t('copyright'); ?></p>
      <div class="footer-bottom-links">
        <a href="terms.php?lang=<?php echo $lang; ?>"><?php echo t('footer_terms'); ?></a>
        <a href="privacy.php?lang=<?php echo $lang; ?>"><?php echo t('footer_privacy_link'); ?></a>
        <a href="cookies.php?lang=<?php echo $lang; ?>"><?php echo t('footer_cookies'); ?></a>
      </div>
    </div>
  </footer>

  <!-- ============================================================
       JAVASCRIPT
       ============================================================ -->
  <script>
    /* ============ Reveal on scroll ============ */
    const revealItems = document.querySelectorAll('.reveal');
    if ('IntersectionObserver' in window) {
      const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            const el = entry.target;
            el.style.willChange = 'opacity, transform';
            el.classList.add('visible');
            const cleanup = () => {
              el.style.willChange = 'auto';
              el.removeEventListener('transitionend', cleanup);
            };
            el.addEventListener('transitionend', cleanup, { once: true });
            observer.unobserve(el);
          }
        });
      }, { threshold: 0.18, rootMargin: '0px 0px -30px 0px' });
      revealItems.forEach((item) => observer.observe(item));
    } else {
      revealItems.forEach((item) => item.classList.add('visible'));
    }

    /* ============ Section-level visibility ============ */
    const animationSections = document.querySelectorAll(
      '.mission, .hiw-section, .cards-container, .languages-strip'
    );
    if ('IntersectionObserver' in window) {
      const sectionObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) entry.target.classList.add('is-visible');
          else entry.target.classList.remove('is-visible');
        });
      }, { rootMargin: '100px 0px 100px 0px', threshold: 0 });
      animationSections.forEach((s) => sectionObserver.observe(s));
    } else {
      animationSections.forEach((s) => s.classList.add('is-visible'));
    }

    /* ============ Language switcher ============ */
    function setLang(lang) {
      var url = new URL(window.location.href);
      url.searchParams.set('lang', lang);
      window.location.href = url.toString();
    }

    /* ============ Toast helper ============ */
    function showToast(msg) {
      var old = document.querySelector('.toast-message');
      if (old) old.remove();
      var t = document.createElement('div');
      t.className = 'toast-message';
      t.textContent = msg;
      document.body.appendChild(t);
      setTimeout(function(){ t.remove(); }, 3000);
    }

    /* ============ Login guard ============ */
    function requireLogin(link, redirectUrl) {
      var isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
      var lang = '<?php echo $lang; ?>';
      if (!isLoggedIn) {
        showToast('<?php echo addslashes(t('login_required')); ?>');
        var redirect = redirectUrl || link || window.location.href;
        setTimeout(function() {
          window.location.href = 'loginui.php?redirect=' + encodeURIComponent(redirect) + '&lang=' + lang;
        }, 1500);
        return false;
      }
      return true;
    }

    /* ============ Mobile menu ============ */
    var mobileToggle = document.getElementById('mobileToggle');
    var mobilePanel = document.getElementById('mobileNavPanel');
    if (mobileToggle && mobilePanel) {
      mobileToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        var isOpen = mobilePanel.classList.toggle('open');
        mobileToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      }, { passive: false });
      document.addEventListener('click', function(e) {
        if (!mobilePanel.contains(e.target) && e.target !== mobileToggle) {
          mobilePanel.classList.remove('open');
          mobileToggle.setAttribute('aria-expanded', 'false');
        }
      }, { passive: true });
      mobilePanel.querySelectorAll('a').forEach(function(a) {
        a.addEventListener('click', function() {
          mobilePanel.classList.remove('open');
          mobileToggle.setAttribute('aria-expanded', 'false');
        }, { passive: true });
      });
    }

    /* ============================================================
       WebGL Water-Ripple Image (with all perf fixes)
       ============================================================ */
    const VERT_SRC = `
      attribute vec2 aPosition;
      varying vec2 vUv;
      void main() {
        vUv = aPosition * 0.5 + 0.5;
        vUv.y = 1.0 - vUv.y;
        gl_Position = vec4(aPosition, 0.0, 1.0);
      }
    `;

    const FRAG_SRC = `
      precision mediump float;
      varying vec2 vUv;
      uniform sampler2D uTexture;
      uniform vec2  uResolution;
      uniform float uTime;
      uniform float uCanvasAspect;
      uniform float uTextureAspect;

      #define MAX_RIPPLES 4
      uniform int   uRippleCount;
      uniform vec2  uRipplePos[MAX_RIPPLES];
      uniform float uRippleAge[MAX_RIPPLES];
      uniform float uRippleStrength[MAX_RIPPLES];

      void main() {
        vec2 uv = vUv;
        vec2 sampleUv = uv;
        if (uCanvasAspect > uTextureAspect) {
          sampleUv.y = (uv.y - 0.5) * (uTextureAspect / max(uCanvasAspect, 0.001)) + 0.5;
        } else {
          sampleUv.x = (uv.x - 0.5) * (uCanvasAspect / max(uTextureAspect, 0.001)) + 0.5;
        }
        sampleUv = clamp(sampleUv, 0.0, 1.0);

        float aspect = uResolution.x / max(uResolution.y, 1.0);
        vec2 p = (uv - 0.5) * vec2(aspect, 1.0);

        vec2 totalOffset = vec2(0.0);

        for (int i = 0; i < MAX_RIPPLES; i++) {
          if (i >= uRippleCount) break;
          vec2 rp = (uRipplePos[i] - 0.5) * vec2(aspect, 1.0);
          float age = uRippleAge[i];
          float strength = uRippleStrength[i];

          float radius = age * 0.55;
          vec2 diff = p - rp;
          float d = length(diff);

          float band = 1.0 - clamp(abs(d - radius) * 10.0, 0.0, 1.0);
          float life = clamp(1.0 - age * 0.7, 0.0, 1.0);
          float wave = sin((d - radius) * 26.0) * band * life * strength;

          totalOffset += diff * (wave * 0.02 / max(d, 0.05));
        }

        vec2 distortedUv = clamp(sampleUv + totalOffset, 0.001, 0.999);
        vec3 color = texture2D(uTexture, distortedUv).rgb;

        float highlight = clamp(length(totalOffset) * 22.0, 0.0, 0.35);
        color += vec3(highlight);

        gl_FragColor = vec4(color, 1.0);
      }
    `;

    function createShader(gl, type, src) {
      const s = gl.createShader(type);
      gl.shaderSource(s, src);
      gl.compileShader(s);
      if (!gl.getShaderParameter(s, gl.COMPILE_STATUS)) {
        console.error(gl.getShaderInfoLog(s));
        gl.deleteShader(s);
        return null;
      }
      return s;
    }

    function createProgram(gl, vs, fs) {
      const p = gl.createProgram();
      gl.attachShader(p, vs);
      gl.attachShader(p, fs);
      gl.linkProgram(p);
      if (!gl.getProgramParameter(p, gl.LINK_STATUS)) {
        console.error(gl.getProgramInfoLog(p));
        return null;
      }
      return p;
    }

    const MAX_RIPPLES = 4;
    const RIPPLE_LIFETIME = 1.1;
    const SPAWN_INTERVAL = 0.18;
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const useRippleEffect = !prefersReducedMotion;

    document.querySelectorAll('.ripple-canvas').forEach((canvas) => {
      const fallbackToImage = () => {
        const img = document.createElement('img');
        img.src = canvas.dataset.src;
        img.alt = '';
        img.loading = 'lazy';
        img.decoding = 'async';
        canvas.replaceWith(img);
      };

      if (!useRippleEffect) { fallbackToImage(); return; }

      const gl = canvas.getContext('webgl', {
        premultipliedAlpha: false,
        alpha: false,
        powerPreference: 'low-power',
        preserveDrawingBuffer: false,
      }) || canvas.getContext('experimental-webgl', {
        premultipliedAlpha: false,
        alpha: false,
        powerPreference: 'low-power',
      });

      if (!gl) { fallbackToImage(); return; }

      const vs = createShader(gl, gl.VERTEX_SHADER, VERT_SRC);
      const fs = createShader(gl, gl.FRAGMENT_SHADER, FRAG_SRC);
      const program = createProgram(gl, vs, fs);
      if (!program) { fallbackToImage(); return; }
      gl.useProgram(program);

      const quad = new Float32Array([
        -1, -1,   1, -1,   -1, 1,
        -1,  1,   1, -1,    1, 1,
      ]);
      const buf = gl.createBuffer();
      gl.bindBuffer(gl.ARRAY_BUFFER, buf);
      gl.bufferData(gl.ARRAY_BUFFER, quad, gl.STATIC_DRAW);

      const aPosition = gl.getAttribLocation(program, 'aPosition');
      gl.enableVertexAttribArray(aPosition);
      gl.vertexAttribPointer(aPosition, 2, gl.FLOAT, false, 0, 0);

      const uTexture       = gl.getUniformLocation(program, 'uTexture');
      const uResolution    = gl.getUniformLocation(program, 'uResolution');
      const uTime          = gl.getUniformLocation(program, 'uTime');
      const uCanvasAspect  = gl.getUniformLocation(program, 'uCanvasAspect');
      const uTextureAspect = gl.getUniformLocation(program, 'uTextureAspect');
      const uRippleCount   = gl.getUniformLocation(program, 'uRippleCount');
      const uRipplePos     = gl.getUniformLocation(program, 'uRipplePos');
      const uRippleAge     = gl.getUniformLocation(program, 'uRippleAge');
      const uRippleStrength= gl.getUniformLocation(program, 'uRippleStrength');

      const ripplePos = new Float32Array(MAX_RIPPLES * 2);
      const rippleAge = new Float32Array(MAX_RIPPLES);
      const rippleStrength = new Float32Array(MAX_RIPPLES);

      let activeRipples = 0;
      let isHovering = false;
      let lastSpawn = 0;
      let startTime = performance.now();
      let textureReady = false;
      let imageAspect = 1.6;

      let running = false;
      let rafId = null;
      let lastFrameTime = 0;

      const img = new Image();
      img.crossOrigin = 'anonymous';
      img.decoding = 'async';
      img.onload = () => {
        imageAspect = (img.naturalWidth || 1) / (img.naturalHeight || 1);
        const tex = gl.createTexture();
        gl.bindTexture(gl.TEXTURE_2D, tex);
        gl.pixelStorei(gl.UNPACK_FLIP_Y_WEBGL, false);
        gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, img);
        gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
        gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
        gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR);
        gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
        gl.uniform1i(uTexture, 0);
        textureReady = true;
        resize();
        renderOnce();
      };
      img.onerror = () => {
        console.warn('Failed to load ripple texture:', canvas.dataset.src);
        fallbackToImage();
      };
      img.src = canvas.dataset.src;

      function resize() {
        const rect = canvas.getBoundingClientRect();
        if (rect.width === 0 || rect.height === 0) return;
        const DPR_CAP = 2;
        const MAX_PIXELS = 2000000;
        const dpr = Math.min(window.devicePixelRatio || 1, DPR_CAP);
        const pixelScale = Math.sqrt(MAX_PIXELS / (rect.width * rect.height));
        const scale = Math.min(dpr, pixelScale);
        const w = Math.max(1, Math.round(rect.width * scale));
        const h = Math.max(1, Math.round(rect.height * scale));
        if (canvas.width !== w || canvas.height !== h) {
          canvas.width = w;
          canvas.height = h;
        }
        gl.viewport(0, 0, canvas.width, canvas.height);
      }
      resize();
      if ('ResizeObserver' in window) {
        new ResizeObserver(() => { resize(); renderOnce(); }).observe(canvas);
      } else {
        window.addEventListener('resize', () => { resize(); renderOnce(); });
      }

      function spawnRipple(x, y, strength = 1) {
        if (activeRipples >= MAX_RIPPLES) {
          for (let i = 0; i < MAX_RIPPLES - 1; i++) {
            ripplePos[i*2]   = ripplePos[(i+1)*2];
            ripplePos[i*2+1] = ripplePos[(i+1)*2+1];
            rippleAge[i]     = rippleAge[i+1];
            rippleStrength[i]= rippleStrength[i+1];
          }
          activeRipples = MAX_RIPPLES - 1;
        }
        const i = activeRipples;
        ripplePos[i*2]   = x;
        ripplePos[i*2+1] = y;
        rippleAge[i]     = 0;
        rippleStrength[i]= strength;
        activeRipples++;
      }

      function uploadUniforms(elapsed) {
        gl.uniform2f(uResolution, canvas.width, canvas.height);
        gl.uniform1f(uTime, elapsed);
        gl.uniform1f(uCanvasAspect, canvas.width / Math.max(canvas.height, 1));
        gl.uniform1f(uTextureAspect, imageAspect || 1.6);
        gl.uniform1i(uRippleCount, activeRipples);
        if (activeRipples > 0) {
          gl.uniform2fv(uRipplePos, ripplePos);
          gl.uniform1fv(uRippleAge, rippleAge);
          gl.uniform1fv(uRippleStrength, rippleStrength);
        }
      }

      function renderOnce() {
        if (!textureReady) return;
        const elapsed = (performance.now() - startTime) / 1000;
        uploadUniforms(elapsed);
        gl.drawArrays(gl.TRIANGLES, 0, 6);
      }

      canvas.addEventListener('pointerenter', (e) => {
        isHovering = true;
        const rect = canvas.getBoundingClientRect();
        const x = (e.clientX - rect.left) / rect.width;
        const y = (e.clientY - rect.top) / rect.height;
        spawnRipple(x, y, 1.2);
        lastSpawn = performance.now();
        start();
      });

      canvas.addEventListener('pointermove', (e) => {
        const rect = canvas.getBoundingClientRect();
        const nx = (e.clientX - rect.left) / rect.width;
        const ny = (e.clientY - rect.top) / rect.height;
        const now = performance.now();
        if (isHovering && (now - lastSpawn) / 1000 > SPAWN_INTERVAL) {
          spawnRipple(nx, ny, 1.0);
          lastSpawn = now;
          start();
        }
      });

      canvas.addEventListener('pointerleave', () => { isHovering = false; });

      function frame(now) {
        if (!running) return;
        if (!lastFrameTime) lastFrameTime = now;
        const dt = Math.min((now - lastFrameTime) / 1000, 0.05);
        lastFrameTime = now;

        if (textureReady) {
          let write = 0;
          for (let i = 0; i < activeRipples; i++) {
            rippleAge[i] += dt;
            if (rippleAge[i] < RIPPLE_LIFETIME) {
              if (write !== i) {
                ripplePos[write*2]    = ripplePos[i*2];
                ripplePos[write*2+1]  = ripplePos[i*2+1];
                rippleAge[write]      = rippleAge[i];
                rippleStrength[write] = rippleStrength[i];
              }
              write++;
            }
          }
          activeRipples = write;
          renderOnce();
          if (activeRipples === 0 && !isHovering) {
            running = false;
            rafId = null;
            return;
          }
        }
        rafId = requestAnimationFrame(frame);
      }

      function start() {
        if (running) return;
        running = true;
        lastFrameTime = 0;
        rafId = requestAnimationFrame(frame);
      }

      function stop() {
        running = false;
        if (rafId !== null) {
          cancelAnimationFrame(rafId);
          rafId = null;
        }
      }

      if ('IntersectionObserver' in window) {
        const io = new IntersectionObserver((entries) => {
          entries.forEach((entry) => {
            if (entry.isIntersecting) {
              renderOnce();
              if (activeRipples > 0 || isHovering) start();
            } else {
              stop();
            }
          });
        }, { rootMargin: '120px 0px 120px 0px', threshold: 0 });
        io.observe(canvas);
      } else {
        renderOnce();
      }

      document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
          stop();
        } else {
          const rect = canvas.getBoundingClientRect();
          const inView = rect.bottom > -120 && rect.top < window.innerHeight + 120;
          if (inView) {
            renderOnce();
            if (activeRipples > 0 || isHovering) start();
          }
        }
      });
    });
  </script>
</body>
</html>