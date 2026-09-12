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
$userName = $isLoggedIn ? $_SESSION['full_name'] ?? 'User' : '';

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
        'nav_home' => 'Home',
        'nav_exams' => 'Certifications',
        'nav_about' => 'Our Story',
        'nav_lessons' => 'Learn & Practice',
        'nav_download' => 'Install App',
        'nav_careers' => 'Careers',
        'login_btn' => 'Sign In',
        'logout_btn' => 'Sign Out',
        'welcome_user' => 'Welcome',
        'eyebrow_text' => 'Learn Languages Through Play',
        'hero_title' => 'Speak Confidently. Learn Playing.',
        'hero_sub' => 'Master 27 languages through interactive challenges and real-time conversation rooms. Learn at your pace, speak with real people.',
        'start_playing_btn' => 'Start Learning',
        'community_btn' => 'Join a Speaking Room',
        'video_label' => 'Speaking Rooms',
        'collection_eyebrow' => 'Language Paths',
        'collection_title' => 'Learn Any Language',
        'collection_desc' => 'Choose from 27 languages with interactive lessons and challenges. Plus, connect with learners worldwide in our speaking rooms to practice real conversations.',
        'lang_label' => 'African Languages',
        'lang_value' => 'Kinyarwanda, Swahili, Zulu',
        'dev_label' => 'European Languages',
        'dev_value' => 'French, Polish, Swedish, Danish',
        'coding_label' => 'Asian Languages',
        'coding_value' => 'Japanese, Korean, Chinese, Hindi',
        'classroom_label' => 'World Languages',
        'classroom_value' => 'Arabic, Turkish, Portuguese & more',
        'why_eyebrow' => 'Why Language Learning Works Here',
        'why_title' => 'Conversation, Not Just Lessons',
        'why_sub' => 'Real language mastery comes from speaking. Playmates combines interactive challenges with our speaking rooms where you practice live conversations with learners worldwide.',
        'why_point1_title' => 'Learn Through Games',
        'why_point1_desc' => 'Interactive lessons make language learning engaging. Master vocabulary, pronunciation, and grammar through game-based challenges that feel natural.',
        'why_point2_title' => 'Speak with Real People',
        'why_point2_desc' => 'Join speaking rooms and practice conversations with other learners at your level. Build confidence and learn authentic language use that no app can teach.',
        'why_point3_title' => 'Build Real Fluency',
        'why_point3_desc' => 'Combine structured lessons with real speaking practice. Reach actual fluency by learning and immediately using language with supportive language partners.',
        'quote_text' => '"I spoke Swahili conversationally in three months. The combination of games and speaking rooms made it stick in a way textbooks never did."',
        'quote_author' => '— Playmates learner, Nairobi',
        'stat_games' => 'LESSONS',
        'stat_subjects' => 'LANGUAGES',
        'stat_langs' => 'LEARNERS',
        'download_eyebrow' => 'Ready to Learn?',
        'download_title' => 'Your Language Journey Starts Now',
        'download_sub' => 'Install Playmates as an app for the best learning experience.',
        'start_playing_btn2' => 'Start Learning',
        'download_btn' => 'Install App',
        'footer_games' => 'Learn Languages',
        'footer_schools' => 'Playmates for Teams',
        'footer_about' => 'Our Story',
        'footer_download' => 'Install App',
        'footer_careers' => 'Careers',
        'copyright' => '© 2026 Playmates',
        'login_required' => '🔐 Please login first to start learning!',
        'rooms_eyebrow' => 'Speaking Rooms',
        'rooms_title' => 'Practice With Real People',
        'rooms_sub' => 'Language learning isn\'t meant to be solo. Create or join a speaking room and practice live conversations with learners at your level.',
        'rooms_feature1' => 'Real-Time Conversation',
        'rooms_feature1_desc' => 'Practice speaking with other learners in live video rooms. No judgment, just supportive learning.',
        'rooms_feature2' => 'Level-Matched Groups',
        'rooms_feature2_desc' => 'Find rooms with learners at your exact proficiency level — beginner, intermediate, or advanced.',
        'rooms_feature3' => 'Structured Topics',
        'rooms_feature3_desc' => 'Each room has a theme — ordering food, job interviews, daily conversation. Focus your practice.',
        'rooms_cta' => 'Join a Room Now',
        'how_eyebrow' => 'Getting Started',
        'how_title' => 'Your Learning Path',
        'how_step1' => 'Choose Your Language',
        'how_step1_desc' => 'Pick from 27 languages. Start with interactive lessons that teach vocabulary and grammar through games.',
        'how_step2' => 'Complete Daily Challenges',
        'how_step2_desc' => 'Engage with 5-10 minute challenges. Pronunciation, listening, writing — everything you need for real fluency.',
        'how_step3' => 'Practice Live',
        'how_step3_desc' => 'Join a speaking room and use what you learned. Speak with real people. Make mistakes. Improve fast.',
        'testimonial1_text' => '"I\'ve tried every language app. Playmates is the first one where I actually speak. The rooms are game-changing."',
        'testimonial1_author' => 'Sarah M. · Learning French from Canada',
        'testimonial2_text' => '"Three months in and I can hold real conversations in Swahili. The games make it stick, the rooms make it real."',
        'testimonial2_author' => 'James K. · Learning Kiswahili from London',
        'testimonial3_text' => '"As a busy parent, I love that lessons are 5 minutes. But the speaking rooms are where real learning happens."',
        'testimonial3_author' => 'Maria R. · Learning Spanish from Mexico City',
        'all_languages' => 'All 27 Languages',
        'lang_list_intro' => 'Master any language on Playmates. From African languages to Asian tongues, European standards to world languages:',
        'start_free' => 'Start Learning Free',
        'no_credit_card' => 'No credit card required. Learn at your own pace.'
    ],
    'rw' => [
        'nav_home' => 'Ahabanza',
        'nav_exams' => 'Impamyabumenyi',
        'nav_about' => 'Amateka Yacu',
        'nav_lessons' => 'Wige & Gerageza',
        'nav_download' => 'Shyiramo App',
        'nav_careers' => 'Akazi',
        'login_btn' => 'Injira',
        'logout_btn' => 'Sohoka',
        'welcome_user' => 'Murakaza Neza',
        'eyebrow_text' => 'Wige Indimi Mu Buryo Bwa Kina',
        'hero_title' => 'Kuvuga Neza. Wige Mu Kina.',
        'hero_sub' => 'Ubwenge indimi 27 mu ibiganiro n\'impuguke z\'imikino. Wige mu kigero cyawe, kuvuga n\'abantu banyabyumuntu.',
        'start_playing_btn' => 'Tangira Kuiga',
        'community_btn' => 'Injire mu Nzira y\'Ibiganiro',
        'video_label' => 'Ibiganeye',
        'collection_eyebrow' => 'Inzira z\'Indimi',
        'collection_title' => 'Wige Indi Imi Yose',
        'collection_desc' => 'Hitamo mu ndimi 27 zifite impuguke n\'ibogamizi. Noneho, guhuza n\'uwigisha isi yose mu biko byacu bya kuvuga kugira ngo gerageze ibibazo byukuri.',
        'lang_label' => 'Indimi z\'Afurika',
        'lang_value' => 'Kinyarwanda, Kiswahili, Zulu',
        'dev_label' => 'Indimi z\'Uburayi',
        'dev_value' => 'Igifaransa, Igipolanyi, Igisuwedu',
        'coding_label' => 'Indimi z\'Aziya',
        'coding_value' => 'Kijapani, Koreya, Igicina, Kihindi',
        'classroom_label' => 'Indimi z\'Isi Yose',
        'classroom_value' => 'Kiarabu, Kituruki, Kiyorutigali, n\'izindi',
        'why_eyebrow' => 'Impamvu Uburezi Bw\'indimi Bufasha Hano',
        'why_title' => 'Ibibazo, Si Ibyiciro Gusa',
        'why_sub' => 'Ubwenge bw\'indimi nyabyumuntu buza kuganira. Playmates ihuja impuguke z\'umukino n\'ibiganeye byacu aho ubwenge bw\'indimi buzi n\'uwigisha isi yose.',
        'why_point1_title' => 'Wige Mu Mikino',
        'why_point1_desc' => 'Ibigisho bishimishije biguza uburezi bw\'indimi. Ubwenge ijambo, uko kuvuga, n\'uburezi mu biganiro byombi rushya.',
        'why_point2_title' => 'Kuvuga N\'Abantu Banyabyumuntu',
        'why_point2_desc' => 'Injire mu nzira y\'ibiganiro kandi gerageza ibibazo n\'abandi wigisha kuri kigero cyawe. Ubwenge n\'uburezi bw\'indimi byukuri.',
        'why_point3_title' => 'Ubwenge Bw\'indimi',
        'why_point3_desc' => 'Ihuja impuguke z\'umukino n\'ibiganiro byukuri. Ubwenge ubw\'indimi bwisi mu kuganira n\'abandi.',
        'quote_text' => '"Namenyekanye Igiswahili cyo kuganira mu miezi itatu. Ihuriro ry\'imikino n\'ibiganiro rwabafashije mu buryo ntabwo wacu bwigire."',
        'quote_author' => '— Umunyeshuri wa Playmates, Nairobi',
        'stat_games' => 'IBIGISHO',
        'stat_subjects' => 'INDIMI',
        'stat_langs' => 'ABIGISHO',
        'download_eyebrow' => 'Witeguye?',
        'download_title' => 'Inzira Yawe y\'Indimi Itangirira Hano',
        'download_sub' => 'Shyiramo Playmates nk\'app kugira ngo uhabwe uburambe bwiza.',
        'start_playing_btn2' => 'Tangira Kuiga',
        'download_btn' => 'Shyiramo App',
        'footer_games' => 'Wige Indimi',
        'footer_schools' => 'Playmates ku Matsinda',
        'footer_about' => 'Amateka Yacu',
        'footer_download' => 'Shyiramo App',
        'footer_careers' => 'Akazi',
        'copyright' => '© 2026 Playmates',
        'login_required' => '🔐 Kanda injira kugira utangire kuiga.',
        'rooms_eyebrow' => 'Inzira y\'Ibiganiro',
        'rooms_title' => 'Gerageza N\'Abantu Banyabyumuntu',
        'rooms_sub' => 'Uburezi bw\'indimi ntibishyirwaho kugira ngo bita nkubwite. Kurema cyangwa kwinjira mu nzira y\'ibiganiro kandi gerageza ibibazo byukuri n\'uwigisha isi yose.',
        'rooms_feature1' => 'Ibibazo Byukuri',
        'rooms_feature1_desc' => 'Gerageza kuganira n\'abandi wigisha mu nzira y\'ibiganiro ivyo nibiganiro byukuri.',
        'rooms_feature2' => 'Itsinda Rya Kigero',
        'rooms_feature2_desc' => 'Shaka nzira ifite uwigisha kuri kigero cyawe — gitangira, hafi, cyangwa yacu.',
        'rooms_feature3' => 'Ingingo Zishizwe',
        'rooms_feature3_desc' => 'Buri nzira ifite ingingo — gucunga ibiryo, ibibazo by\'akazi, ibibazo byubulogo.',
        'rooms_cta' => 'Injira mu Nzira Ku Mwanya',
        'how_eyebrow' => 'Kwigira',
        'how_title' => 'Inzira Yawe y\'Uburezi',
        'how_step1' => 'Hitamo Indi Imi Yawe',
        'how_step1_desc' => 'Hitamo mu ndimi 27. Tangira kuri impuguke z\'umukino zigusha ijambo n\'uburezi.',
        'how_step2' => 'Kuzuza Imbogamizi z\'Buri Munsi',
        'how_step2_desc' => 'Gerageza imbogamizi z\'iminota 5-10. Kuvuga, kumva, kwandika — ibicuruzwe byose bwomutwe w\'ubwenge.',
        'how_step3' => 'Gerageza Byukuri',
        'how_step3_desc' => 'Injira mu nzira y\'ibiganiro kandi gukoresha ibyakubwije. Kuvuga n\'abantu. Gukora amakosa. Kubuka vuba.',
        'testimonial1_text' => '"Namize ubwenge bwa app yose. Playmates ni icya mbere aho nakuvuga. Inzira y\'ibiganiro ni impamvu nyinshi."',
        'testimonial1_author' => 'Sarah M. · Wiga Igifaransa kuva Canada',
        'testimonial2_text' => '"Mu miezi itatu kandi njifuza kuganira mu Kiswahili. Imikino itunganya ubwenge, inzira y\'ibiganiro itunganya ibyukuri."',
        'testimonial2_author' => 'James K. · Wiga Kiswahili kuva London',
        'testimonial3_text' => '"Nkubwite kandi njyambere impuguke z\'iminota 5. Ariko inzira y\'ibiganiro ni aho ubwenge bw\'ibukuri bwitanira."',
        'testimonial3_author' => 'Maria R. · Wiga Igeresa kuva Mexico City',
        'all_languages' => 'Indimi 27 Zose',
        'lang_list_intro' => 'Ubwenge indi imi ijejwe mu Playmates. Kuva mu ndimi z\'Afurika kugeza kuri diphthonga z\'Aziya:',
        'start_free' => 'Tangira Kuiga Kumasimu',
        'no_credit_card' => 'Nta kadi ya ndahiro. Wige mu kigero cyawe.'
    ],
    'sw' => [
        'nav_home' => 'Nyumbani',
        'nav_exams' => 'Vyeti',
        'nav_about' => 'Hadithi Yetu',
        'nav_lessons' => 'Jifunze & Pratika',
        'nav_download' => 'Sakinisha App',
        'nav_careers' => 'Kazi',
        'login_btn' => 'Ingia',
        'logout_btn' => 'Toka',
        'welcome_user' => 'Karibu',
        'eyebrow_text' => 'Jifunza Lugha Kwa Kucheza',
        'hero_title' => 'Zungumza Kwa Imani. Jifunza Kwa Furaha.',
        'hero_sub' => 'Kufahamiana lugha 27 kupitia changamoto za kielektroniki na vyumba vya mazungumzo halisi. Jifunza kwa kasi yako, zungumza na watu halisi.',
        'start_playing_btn' => 'Anza Kujifunza',
        'community_btn' => 'Jiunge na Chumba cha Mazungumzo',
        'video_label' => 'Vyumba vya Mazungumzo',
        'collection_eyebrow' => 'Njia za Lugha',
        'collection_title' => 'Jifunza Lugha Yoyote',
        'collection_desc' => 'Chagua kutoka lugha 27 na masomo na changamoto. Pia, jiunga na waajifunza ulimwenguni katika vyumba vyetu vya mazungumzo kupraktika mazungumzo halisi.',
        'lang_label' => 'Lugha za Afrika',
        'lang_value' => 'Kinyarwanda, Kiswahili, Zulu',
        'dev_label' => 'Lugha za Uropa',
        'dev_value' => 'Kifarensa, Kipolski, Kiswidi',
        'coding_label' => 'Lugha za Asia',
        'coding_value' => 'Kijapani, Kikorea, Kichina, Kihindi',
        'classroom_label' => 'Lugha za Dunia',
        'classroom_value' => 'Kiarabu, Kituruki, Kireno & zaidi',
        'why_eyebrow' => 'Kwa Nini Kujifunza Lugha Hapa Kufanya Kazi',
        'why_title' => 'Mazungumzo, Si Masomo Tu',
        'why_sub' => 'Kufahamiana lugha halisi kunakuja kuzungumza. Playmates inachanganya changamoto za kucheza na vyumba vyetu vya mazungumzo ambapo unajifunza mazungumzo halisi na waajifunza ulimwenguni.',
        'why_point1_title' => 'Jifunza Kupitia Michezo',
        'why_point1_desc' => 'Masomo yanayofanya furaha hufanya kujifunza lugha kutaka kufanya. Kufahamiana maneno, matamshi, na sarufi kupitia michezo.',
        'why_point2_title' => 'Zungumza na Watu Halisi',
        'why_point2_desc' => 'Jiunge na vyumba vya mazungumzo na pratika mazungumzo na waajifunza wengine kwa kiwango chako. Jajifua ujasiri na jifunze lugha halisi.',
        'why_point3_title' => 'Jenga Fluensi Halisi',
        'why_point3_desc' => 'Changanya masomo ya miundo na mazungumzo halisi. Kufikia fluensi halisi kwa kujifunza na kutumia lugha mara moja na wote.',
        'quote_text' => '"Nilijifunza Kiswahili cha mazungumzo katika miezi mitatu. Kuchanganyanisha kwa michezo na vyumba vya mazungumzo kiliwa sababu ya ushindi."',
        'quote_author' => '— Mwanafunzi wa Playmates, Nairobi',
        'stat_games' => 'MASOMO',
        'stat_subjects' => 'LUGHA',
        'stat_langs' => 'WAAJIFUNZA',
        'download_eyebrow' => 'Umejifunza?',
        'download_title' => 'Safari Yako ya Lugha Inaanza Hapa',
        'download_sub' => 'Sakinisha Playmates kama app kwa uzoefu bora.',
        'start_playing_btn2' => 'Anza Kujifunza',
        'download_btn' => 'Sakinisha App',
        'footer_games' => 'Jifunza Lugha',
        'footer_schools' => 'Playmates kwa Timu',
        'footer_about' => 'Hadithi Yetu',
        'footer_download' => 'Sakinisha App',
        'footer_careers' => 'Kazi',
        'copyright' => '© 2026 Playmates',
        'login_required' => '🔐 Tafadhali ingia kwa akaunti yako kabla ya kuanza.',
        'rooms_eyebrow' => 'Vyumba vya Mazungumzo',
        'rooms_title' => 'Pratika na Watu Halisi',
        'rooms_sub' => 'Kujifunza lugha hakuna kwa ajili ya kuwa peke yako. Unda au jiunge na chumba cha mazungumzo na pratika mazungumzo halisi na waajifunza kwa kiwango chako.',
        'rooms_feature1' => 'Mazungumzo ya Moto',
        'rooms_feature1_desc' => 'Pratika kuzungumza na waajifunza wengine katika vyumba vya video mubashara.',
        'rooms_feature2' => 'Vikundi vya Kiwango Sawa',
        'rooms_feature2_desc' => 'Tafuta vyumba na waajifunza kwa kiwango chako — mwanzo, kati, au juu.',
        'rooms_feature3' => 'Mada Zenye Muundo',
        'rooms_feature3_desc' => 'Kila chumba kina mada — kula, mahojiano ya kazi, mazungumzo ya kila siku.',
        'rooms_cta' => 'Jiunge na Chumba Sasa',
        'how_eyebrow' => 'Kuanza',
        'how_title' => 'Njia Yako ya Kujifunza',
        'how_step1' => 'Chagua Lugha Yako',
        'how_step1_desc' => 'Chagua kutoka lugha 27. Anza na masomo ya kielektroniki yanayofundisha maneno na sarufi kupitia michezo.',
        'how_step2' => 'Kamata Changamoto za Kila Siku',
        'how_step2_desc' => 'Kamata changamoto za dakika 5-10. Matamshi, kusikiliza, kuandika — kila kitu unachohitaji kwa fluensi halisi.',
        'how_step3' => 'Pratika Moto',
        'how_step3_desc' => 'Jiunge na chumba cha mazungumzo kwa matumizi ya kile unachojifunza. Zungumza na watu. Kosa. Jiborezeeni haraka.',
        'testimonial1_text' => '"Njamia kila app ya lugha. Playmates ni ya kwanza ambapo nikazungumza halisi. Vyumba vya mazungumzo vinabadili sura."',
        'testimonial1_author' => 'Sarah M. · Kujifunza Kifarensa kutoka Canada',
        'testimonial2_text' => '"Katika miezi mitatu nakweza kuzungumza Kiswahili. Michezo inasanidi, vyumba vinafanya iwe halisi."',
        'testimonial2_author' => 'James K. · Kujifunza Kiswahili kutoka London',
        'testimonial3_text' => '"Kuwa mama wa kazi, nahuruta kuwa masomo ni dakika 5. Lakini vyumba vya mazungumzo ndiyo unapoelezwa halisi."',
        'testimonial3_author' => 'Maria R. · Kujifunza Kihispania kutoka Mexico City',
        'all_languages' => 'Lugha 27 Zote',
        'lang_list_intro' => 'Kufahamiana lugha yoyote kwenye Playmates. Kutoka lugha za Afrika hadi katika kielektroniki za Asia:',
        'start_free' => 'Anza Kujifunza Bure',
        'no_credit_card' => 'Hakuna kadi ya mkopo. Jifunze kwa kiwango chako.'
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
  <title>Playmates — Learn Languages Through Play | 27 Languages</title>
  <link rel="manifest" href="/manifest.json">
  <meta name="theme-color" content="#006f4a">
  <link rel="apple-touch-icon" href="https://res.cloudinary.com/franklinrw/image/upload/v1775217093/icon-192_pin0pv.png">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <meta name="apple-mobile-web-app-title" content="Playmates">
  
  <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400;0,500;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
  
  <style>
    /* ----- ANIMATIONS ----- */
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeDown {
      from { opacity: 0; transform: translateY(-20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @keyframes slideInLeft {
      from { opacity: 0; transform: translateX(-30px); }
      to { opacity: 1; transform: translateX(0); }
    }

    @keyframes slideInRight {
      from { opacity: 0; transform: translateX(30px); }
      to { opacity: 1; transform: translateX(0); }
    }

    @keyframes scaleIn {
      from { opacity: 0; transform: scale(0.95); }
      to { opacity: 1; transform: scale(1); }
    }

    @keyframes spring {
      0% { transform: scale(1); }
      50% { transform: scale(1.05); }
      100% { transform: scale(1); }
    }

    @keyframes pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.7; }
    }

    @keyframes hoverLift {
      from { transform: translateY(0); }
      to { transform: translateY(-8px); }
    }

    @keyframes shimmer {
      0% { background-position: -1000px 0; }
      100% { background-position: 1000px 0; }
    }

    /* ----- reset & base ----- */
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

    /* Scroll-triggered animation states */
    .reveal {
      opacity: 0;
      transform: translateY(40px);
      transition: opacity 0.8s ease, transform 0.8s ease;
    }

    .reveal.active {
      opacity: 1;
      transform: translateY(0);
    }

    .reveal-stagger {
      opacity: 0;
      transform: translateY(40px);
    }

    .reveal-stagger.active {
      opacity: 1;
      transform: translateY(0);
      transition: opacity 0.8s ease, transform 0.8s ease;
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

    #iosInstallBanner {
      display: none;
      position: fixed;
      bottom: 20px;
      left: 50%;
      transform: translateX(-50%);
      background: rgba(0,0,0,0.88);
      color: white;
      padding: 16px 22px;
      border-radius: 14px;
      font-size: 0.85rem;
      z-index: 9999;
      text-align: center;
      max-width: 320px;
      width: 90%;
      backdrop-filter: blur(10px);
      box-shadow: 0 8px 32px rgba(0,0,0,0.3);
      line-height: 1.6;
    }

    #iosInstallBanner .close-banner {
      position: absolute;
      top: 8px;
      right: 12px;
      background: none;
      border: none;
      color: white;
      font-size: 1.2rem;
      cursor: pointer;
      padding: 0;
    }

    /* ----- HERO ----- */
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
      animation: fadeDown 0.6s ease-out;
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
      animation: spring 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
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
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .login-link:hover {
      background: rgba(255,255,255,0.18);
      border-color: rgba(255,255,255,0.7);
      transform: translateY(-2px);
    }

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
      animation: slideInRight 0.3s ease-out;
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

    .mobile-nav-panel .user-info {
      color: var(--white);
      padding: 10px 14px;
      border-bottom: 1px solid rgba(255,255,255,0.1);
      font-size: 13px;
      font-weight: 500;
    }

    /* ----- HERO CONTENT ----- */
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
      animation: fadeUp 0.6s ease-out 0.1s both;
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
      animation: fadeUp 0.8s ease-out 0.2s both;
    }

    .hero-sub {
      font-size: 15px;
      font-weight: 350;
      line-height: 1.55;
      color: var(--text-light);
      margin-bottom: 42px;
      max-width: 300px;
      animation: fadeUp 0.8s ease-out 0.3s both;
    }

    .cta-row {
      display: flex;
      gap: 16px;
      align-items: center;
      flex-wrap: wrap;
      animation: fadeUp 0.8s ease-out 0.4s both;
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

    .btn:hover {
      animation: spring 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
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

    .btn-secondary:hover { background: rgba(255,255,255,0.22); border-color: rgba(255,255,255,0.8); transform: translateY(-2px); }

    .btn-install {
      background: #1a1a1a;
      color: white;
      border: 1.2px solid #1a1a1a;
      padding: 9px 18px;
      font-family: 'Inter', sans-serif;
      font-size: 12px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.2s ease;
      letter-spacing: 0.01em;
      white-space: nowrap;
      border-radius: 2px;
      display: inline-flex;
      align-items: center;
      gap: 7px;
    }

    .btn-install:hover {
      background: #333;
      border-color: #333;
      transform: translateY(-2px);
    }

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

    /* ----- HERO ILLUSTRATION ----- */
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
      animation: fadeUp 0.9s cubic-bezier(0.12, 0.71, 0.33, 1) forwards;
    }

    /* ----- VIDEO CARD (desktop) ----- */
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
      animation: slideInRight 0.8s ease-out 0.6s both;
    }

    .video-card:hover { 
      transform: scale(1.01) translateY(-4px); 
      box-shadow: 0 24px 40px -12px rgba(0,0,0,0.3); 
    }

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

    /* ----- MOBILE FAB CONTAINER ----- */
    .video-fab-wrapper {
      position: fixed;
      bottom: 28px;
      right: 28px;
      z-index: 1000;
      display: none;
      flex-direction: column;
      align-items: flex-end;
      gap: 12px;
      pointer-events: none;
    }

    .video-fab-wrapper * {
      pointer-events: auto;
    }

    .video-fab-expanded {
      width: 280px;
      background: rgba(255,255,255,0.98);
      border-radius: 3px;
      overflow: hidden;
      box-shadow: 0 20px 35px -8px rgba(0,0,0,0.35);
      transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
      transform-origin: bottom right;
      opacity: 0;
      transform: scale(0.8) translateY(20px);
      pointer-events: none;
    }

    .video-fab-expanded.visible {
      opacity: 1;
      transform: scale(1) translateY(0);
      pointer-events: auto;
      animation: scaleIn 0.3s ease-out;
    }

    .video-fab-expanded .video-thumb {
      width: 100%;
      background: #cfddcc;
      position: relative;
    }

    .video-fab-expanded .video-thumb img {
      width: 100%;
      height: 160px;
      object-fit: cover;
      display: block;
    }

    .video-fab-expanded .video-footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 12px 16px;
      background: #ffffff;
    }

    .video-fab-expanded .video-label {
      font-size: 13px;
      font-weight: 500;
      color: #1d392b;
      font-family: 'Inter', sans-serif;
      letter-spacing: -0.2px;
    }

    .video-fab-expanded .video-footer .material-icons {
      font-size: 18px;
      color: var(--green-soft);
    }

    .video-fab {
      width: 72px;
      height: 72px;
      border-radius: 50%;
      box-shadow: 0 8px 24px rgba(0,0,0,0.35);
      background: rgba(255,255,255,0.96);
      border: 2px solid var(--white);
      overflow: hidden;
      transition: transform 0.2s ease, box-shadow 0.2s;
      cursor: pointer;
      position: relative;
      flex-shrink: 0;
      animation: pulse 2s infinite;
    }

    .video-fab:hover {
      transform: scale(1.06);
      box-shadow: 0 12px 32px rgba(0,0,0,0.4);
      animation: none;
    }

    .video-fab img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }

    .video-fab .fab-badge {
      position: absolute;
      bottom: 4px;
      right: 4px;
      background: var(--green-primary);
      color: white;
      font-size: 10px;
      font-weight: 600;
      padding: 2px 8px;
      border-radius: 20px;
      letter-spacing: 0.3px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.2);
      animation: pulse 1.5s infinite;
    }

    /* ----- SECTIONS ----- */
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
      animation: fadeUp 0.6s ease-out;
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
      animation: fadeUp 0.8s ease-out 0.1s both;
    }

    .section-sub {
      font-size: 15px;
      font-weight: 350;
      line-height: 1.55;
      color: var(--text-muted);
      max-width: 460px;
      animation: fadeUp 0.8s ease-out 0.2s both;
    }

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
      transition: opacity 0.2s ease, transform 0.2s ease;
      opacity: 0;
      animation: fadeUp 0.6s ease-out forwards;
    }

    .collection-item:nth-child(1) { animation-delay: 0.1s; }
    .collection-item:nth-child(2) { animation-delay: 0.2s; }
    .collection-item:nth-child(3) { animation-delay: 0.3s; }
    .collection-item:nth-child(4) { animation-delay: 0.4s; }

    .collection-item:hover { 
      opacity: 0.7;
      transform: translateX(8px);
    }

    .item-thumb {
      width: 100%;
      height: 100px;
      border-radius: 2px;
      overflow: hidden;
      background: rgba(248,244,238,0.06);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      flex-wrap: wrap;
    }

    .item-thumb-flags {
      display: flex;
      gap: 6px;
      flex-wrap: wrap;
      align-items: center;
      justify-content: center;
      width: 100%;
      height: 100%;
      padding: 12px;
    }

    .item-flag {
      width: 36px;
      height: 28px;
      border-radius: 2px;
      object-fit: cover;
      display: block;
      transition: transform 0.3s ease;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .collection-item:hover .item-flag { transform: scale(1.08) translateY(-4px); }

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
      opacity: 0;
      animation: fadeUp 0.6s ease-out forwards;
    }

    .how-step:nth-child(1) { animation-delay: 0.1s; }
    .how-step:nth-child(2) { animation-delay: 0.2s; }
    .how-step:nth-child(3) { animation-delay: 0.3s; }

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

    .trust-quote { max-width: 560px; animation: fadeUp 0.8s ease-out; }

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

    .trust-stats { display: flex; gap: 2.5rem; flex-wrap: wrap; animation: fadeUp 0.8s ease-out 0.2s both; }

    .trust-stat .num {
      font-family: 'EB Garamond', Georgia, serif;
      font-size: 40px;
      font-weight: 500;
      letter-spacing: -1px;
      display: block;
      transition: all 0.3s ease;
    }

    .trust-stat .label {
      font-size: 12px;
      color: var(--text-light);
      letter-spacing: 0.03em;
      margin-top: 4px;
    }

    .trust-stat {
      opacity: 0;
      animation: fadeUp 0.6s ease-out forwards;
    }

    .trust-stat:nth-child(1) { animation-delay: 0.3s; }
    .trust-stat:nth-child(2) { animation-delay: 0.4s; }
    .trust-stat:nth-child(3) { animation-delay: 0.5s; }

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

    .download-cta { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; animation: fadeUp 0.8s ease-out 0.3s both; }

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
      animation: fadeUp 0.6s ease-out;
    }

    footer .logo { color: var(--green-dark); font-size: 18px; }

    .footer-links { display: flex; gap: 1.8rem; }
    .footer-links a { color: var(--text-muted); text-decoration: none; transition: color 0.2s; }
    .footer-links a:hover { color: var(--green-dark); }

    /* ----- ROOMS SECTION ----- */
    .rooms-section {
      background: linear-gradient(135deg, var(--green-mist) 0%, rgba(255,255,255,0.5) 100%);
      padding: 6rem 5%;
    }

    .rooms-header {
      max-width: 1240px;
      margin: 0 auto 3.5rem;
    }

    .rooms-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 2rem;
      max-width: 1240px;
      margin: 0 auto;
    }

    .rooms-card {
      background: white;
      border-radius: 4px;
      padding: 2rem;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
      transition: transform 0.2s, box-shadow 0.2s;
      opacity: 0;
      animation: fadeUp 0.6s ease-out forwards;
    }

    .rooms-card:nth-child(1) { animation-delay: 0.1s; }
    .rooms-card:nth-child(2) { animation-delay: 0.2s; }
    .rooms-card:nth-child(3) { animation-delay: 0.3s; }

    .rooms-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 12px 24px rgba(0,0,0,0.12);
    }

    .rooms-card-icon {
      font-size: 32px;
      margin-bottom: 1rem;
      animation: scaleIn 0.4s ease-out;
    }

    .rooms-card h3 {
      font-size: 16px;
      font-weight: 600;
      color: var(--text-dark);
      margin-bottom: 0.8rem;
      letter-spacing: -0.2px;
    }

    .rooms-card p {
      font-size: 14px;
      line-height: 1.6;
      color: var(--text-muted);
      font-weight: 350;
    }

    .rooms-cta-btn {
      display: block;
      text-align: center;
      background: var(--green-primary);
      color: white;
      padding: 14px 28px;
      border-radius: 2px;
      text-decoration: none;
      font-weight: 500;
      font-size: 13px;
      margin-top: 3rem;
      transition: all 0.2s;
      max-width: 1240px;
      margin-left: auto;
      margin-right: auto;
      animation: fadeUp 0.8s ease-out 0.4s both;
    }

    .rooms-cta-btn:hover {
      background: var(--green-dark);
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(44, 110, 79, 0.3);
    }

    /* ----- TESTIMONIALS ----- */
    .testimonials-section {
      padding: 6rem 5%;
      background: white;
    }

    .testimonials-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 2.5rem;
      max-width: 1240px;
      margin: 3.5rem auto 0;
    }

    .testimonial-card {
      background: var(--green-mist);
      border-left: 4px solid var(--green-primary);
      padding: 2rem;
      border-radius: 2px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      opacity: 0;
      animation: fadeUp 0.6s ease-out forwards;
      transition: transform 0.2s;
    }

    .testimonial-card:nth-child(1) { animation-delay: 0.1s; }
    .testimonial-card:nth-child(2) { animation-delay: 0.2s; }
    .testimonial-card:nth-child(3) { animation-delay: 0.3s; }

    .testimonial-card:hover {
      transform: translateY(-4px);
    }

    .testimonial-text {
      font-family: 'EB Garamond', Georgia, serif;
      font-size: 16px;
      font-style: italic;
      line-height: 1.6;
      color: var(--text-dark);
      margin-bottom: 1.5rem;
      flex-grow: 1;
    }

    .testimonial-author {
      font-size: 12px;
      font-weight: 600;
      color: var(--green-dark);
      letter-spacing: 0.5px;
    }

    /* ----- LANGUAGES SECTION ----- */
    .languages-section {
      padding: 6rem 5%;
      background: var(--page-bg);
    }

    .languages-header {
      max-width: 1240px;
      margin: 0 auto 3.5rem;
    }

    .languages-intro {
      font-size: 15px;
      font-weight: 350;
      line-height: 1.6;
      color: var(--text-muted);
      max-width: 600px;
      margin-bottom: 2rem;
      animation: fadeUp 0.6s ease-out;
    }

    .language-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
      gap: 1.2rem;
      max-width: 1240px;
      margin: 0 auto;
    }

    .language-tag {
      background: white;
      border: 1px solid var(--card-border);
      border-radius: 4px;
      padding: 12px 16px;
      text-align: center;
      cursor: pointer;
      transition: all 0.2s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      font-size: 13px;
      font-weight: 500;
      color: var(--text-dark);
      min-height: 50px;
      opacity: 0;
      animation: fadeUp 0.4s ease-out forwards;
    }

    .language-tag:nth-child(1) { animation-delay: 0.05s; }
    .language-tag:nth-child(2) { animation-delay: 0.1s; }
    .language-tag:nth-child(3) { animation-delay: 0.15s; }
    .language-tag:nth-child(4) { animation-delay: 0.2s; }
    .language-tag:nth-child(5) { animation-delay: 0.25s; }
    .language-tag:nth-child(6) { animation-delay: 0.3s; }
    .language-tag:nth-child(7) { animation-delay: 0.35s; }
    .language-tag:nth-child(8) { animation-delay: 0.4s; }
    .language-tag:nth-child(9) { animation-delay: 0.45s; }

    .language-tag:hover {
      background: var(--green-mist);
      border-color: var(--green-soft);
      transform: translateY(-3px);
      box-shadow: 0 6px 16px rgba(44, 110, 79, 0.1);
    }

    .language-flag {
      width: 20px;
      height: 15px;
      border-radius: 2px;
      object-fit: cover;
      transition: transform 0.2s;
    }

    .language-tag:hover .language-flag {
      transform: scale(1.1);
    }

    .cta-banner {
      background: var(--green-primary);
      color: white;
      padding: 4rem 5%;
      text-align: center;
      margin-top: 4rem;
      animation: fadeUp 0.8s ease-out;
    }

    .cta-banner-inner {
      max-width: 600px;
      margin: 0 auto;
    }

    .cta-banner h2 {
      font-family: 'EB Garamond', Georgia, serif;
      font-size: 32px;
      font-weight: 500;
      margin-bottom: 0.8rem;
      letter-spacing: -0.5px;
    }

    .cta-banner p {
      font-size: 14px;
      color: var(--text-light);
      margin-bottom: 2rem;
      line-height: 1.6;
    }

    .cta-banner .btn-primary {
      background: white;
      color: var(--green-primary);
      padding: 12px 28px;
      font-weight: 600;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .cta-banner .btn-primary:hover {
      background: #f5f5f5;
      transform: translateY(-2px);
    }

    /* ----- RESPONSIVE ----- */
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
      .video-fab-wrapper { display: flex; }
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
      .video-fab { width: 56px; height: 56px; bottom: 16px; right: 16px; }
      .video-fab-wrapper { bottom: 16px; right: 16px; }
      .video-fab-expanded { width: 240px; }
      .video-fab-expanded .video-thumb img { height: 140px; }
      .rooms-grid { grid-template-columns: 1fr; }
      .testimonials-grid { grid-template-columns: 1fr; }
      .language-grid { grid-template-columns: repeat(2, 1fr); }
      .cta-banner h2 { font-size: 24px; }
      .section-title { font-size: 28px; }
    }
  </style>
</head>
<body>

<!-- iOS Install Banner (Safari only) -->
<div id="iosInstallBanner">
  <button class="close-banner" onclick="document.getElementById('iosInstallBanner').style.display='none'">✕</button>
  <strong>Install Playmates</strong><br>
  Tap <strong>Share</strong> then <strong>"Add to Home Screen"</strong>
</div>

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
      <a href="careers.php?lang=<?php echo $lang; ?>" class="nav-link"><?php echo t('nav_careers'); ?></a>
      <a href="#" id="installBtn" class="nav-link"><?php echo t('nav_download'); ?></a>
    </div>

    <div class="nav-actions">
      <?php echo getLangSwitcher($lang); ?>
      
      <?php if ($isLoggedIn): ?>
        <a href="logout.php?lang=<?php echo $lang; ?>&redirect=<?php echo urlencode($currentPage); ?>" class="login-link" id="logoutBtn">
          <?php echo t('logout_btn'); ?>
        </a>
      <?php else: ?>
        <a href="loginui.php?lang=<?php echo $lang; ?>&redirect=<?php echo urlencode($currentPage); ?>" class="login-link">
          <?php echo t('login_btn'); ?>
        </a>
      <?php endif; ?>
      
      <button class="mobile-nav-toggle" id="mobileToggle" aria-label="Toggle menu" aria-expanded="false">
        <span class="plus">+</span> Menu
      </button>
    </div>

    <!-- Mobile dropdown panel -->
    <div class="mobile-nav-panel" id="mobileNavPanel">
      <?php if ($isLoggedIn): ?>
        <div class="user-info">👋 <?php echo t('welcome_user'); ?>, <?php echo htmlspecialchars($userName); ?></div>
      <?php endif; ?>
      
      <a href="#games"><?php echo t('nav_home'); ?></a>
      <a href="Language_Exams.php?lang=<?php echo $lang; ?>"><?php echo t('nav_exams'); ?></a>
      <a href="about.php?lang=<?php echo $lang; ?>"><?php echo t('nav_about'); ?></a>
      <a href="#games"><?php echo t('nav_lessons'); ?></a>
      <a href="careers.php?lang=<?php echo $lang; ?>"><?php echo t('nav_careers'); ?></a>
      <a href="#" id="installBtnMobile"><?php echo t('nav_download'); ?></a>
      
      <?php if ($isLoggedIn): ?>
        <a href="logout.php?lang=<?php echo $lang; ?>&redirect=<?php echo urlencode($currentPage); ?>" class="login-link" style="margin:4px 14px;text-align:center;">
          <?php echo t('logout_btn'); ?>
        </a>
      <?php else: ?>
        <a href="loginui.php?lang=<?php echo $lang; ?>&redirect=<?php echo urlencode($currentPage); ?>" class="login-link" style="margin:4px 14px;text-align:center;">
          <?php echo t('login_btn'); ?>
        </a>
      <?php endif; ?>
      
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
      <span class="material-icons">language</span>
      <span><?php echo t('eyebrow_text'); ?></span>
    </span>
    <h1 class="hero-title"><?php echo t('hero_title'); ?></h1>
    <p class="hero-sub"><?php echo t('hero_sub'); ?></p>
    <div class="cta-row">
      <a href="#games" class="btn btn-primary" id="playGamesBtn">
        <?php echo t('start_playing_btn'); ?>
        <span class="btn-icon"><span class="material-icons">arrow_forward</span></span>
      </a>
      <a href="room_dashboard.php?lang=<?php echo $lang; ?>" class="btn btn-secondary" id="communityBtn">
        <?php echo t('community_btn'); ?>
        <span class="btn-icon"><span class="material-icons">groups</span></span>
      </a>
      <button class="btn-install" id="installBtnHero">📲 <?php echo t('nav_download'); ?></button>
    </div>
  </div>

  <div class="hero-illustration">
    <img class="illustration-img"
         src="https://res.cloudinary.com/franklinrw/image/upload/v1781385354/Untitled_Project_-_illustrationImage_17_hchv3s.png"
         alt="Playful learning illustration" loading="eager">
  </div>

  <!-- DESKTOP VIDEO CARD -->
  <a href="room_dashboard.php?lang=<?php echo $lang; ?>" class="video-card" id="videoCard" aria-label="Join speaking rooms">
    <div class="video-thumb">
      <img class="video-thumb-img"
           src="https://res.cloudinary.com/franklinrw/image/upload/v1781545274/Untitled_Project_-_video_21_to4qje.gif"
           alt="Playmates video preview" loading="lazy">
    </div>
    <div class="video-footer">
      <span class="video-label"><?php echo t('video_label'); ?></span>
      <span class="material-icons">groups</span>
    </div>
  </a>

  <!-- MOBILE FAB WRAPPER -->
  <div class="video-fab-wrapper" id="videoFabWrapper">
    <div class="video-fab-expanded" id="videoFabExpanded">
      <div class="video-thumb">
        <img src="https://res.cloudinary.com/franklinrw/image/upload/v1781545274/Untitled_Project_-_video_21_to4qje.gif"
             alt="Speaking rooms preview" loading="lazy">
      </div>
      <div class="video-footer">
        <span class="video-label"><?php echo t('video_label'); ?></span>
        <span class="material-icons">groups</span>
      </div>
    </div>
    
    <div class="video-fab" id="videoFab" aria-label="Join speaking rooms">
      <img src="https://res.cloudinary.com/franklinrw/image/upload/v1781545274/Untitled_Project_-_video_21_to4qje.gif"
           alt="Speaking rooms" loading="lazy">
      <span class="fab-badge">LIVE</span>
    </div>
  </div>
</section>

<!-- LANGUAGE LEARNING PATHS -->
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
        <div class="item-thumb-flags">
          <img src="<?php echo getFlagUrl('rw'); ?>" alt="Kinyarwanda" class="item-flag" title="Kinyarwanda">
          <img src="<?php echo getFlagUrl('tz'); ?>" alt="Kiswahili" class="item-flag" title="Kiswahili">
          <img src="<?php echo getFlagUrl('za'); ?>" alt="Zulu" class="item-flag" title="Zulu">
        </div>
      </div>
      <div class="item-info">
        <span class="item-label"><?php echo t('lang_label'); ?></span>
        <span class="item-value"><span class="material-icons">public</span><?php echo t('lang_value'); ?></span>
      </div>
      <div class="item-arrow"><span class="material-icons">arrow_forward</span></div>
    </div>
    <div class="collection-item" data-link="frontend/dashboard.php?lang=<?php echo $lang; ?>" data-requires-login="true">
      <div class="item-thumb">
        <div class="item-thumb-flags">
          <img src="<?php echo getFlagUrl('fr'); ?>" alt="French" class="item-flag" title="French">
          <img src="<?php echo getFlagUrl('pl'); ?>" alt="Polish" class="item-flag" title="Polish">
          <img src="<?php echo getFlagUrl('se'); ?>" alt="Swedish" class="item-flag" title="Swedish">
          <img src="<?php echo getFlagUrl('dk'); ?>" alt="Danish" class="item-flag" title="Danish">
        </div>
      </div>
      <div class="item-info">
        <span class="item-label"><?php echo t('dev_label'); ?></span>
        <span class="item-value"><span class="material-icons">public</span><?php echo t('dev_value'); ?></span>
      </div>
      <div class="item-arrow"><span class="material-icons">arrow_forward</span></div>
    </div>
    <div class="collection-item" data-link="frontend/dashboard.php?lang=<?php echo $lang; ?>" data-requires-login="true">
      <div class="item-thumb">
        <div class="item-thumb-flags">
          <img src="<?php echo getFlagUrl('jp'); ?>" alt="Japanese" class="item-flag" title="Japanese">
          <img src="<?php echo getFlagUrl('kr'); ?>" alt="Korean" class="item-flag" title="Korean">
          <img src="<?php echo getFlagUrl('cn'); ?>" alt="Chinese" class="item-flag" title="Chinese">
          <img src="<?php echo getFlagUrl('in'); ?>" alt="Hindi" class="item-flag" title="Hindi">
        </div>
      </div>
      <div class="item-info">
        <span class="item-label"><?php echo t('coding_label'); ?></span>
        <span class="item-value"><span class="material-icons">public</span><?php echo t('coding_value'); ?></span>
      </div>
      <div class="item-arrow"><span class="material-icons">arrow_forward</span></div>
    </div>
    <div class="collection-item" data-link="frontend/dashboard.php?lang=<?php echo $lang; ?>" data-requires-login="true">
      <div class="item-thumb">
        <div class="item-thumb-flags">
          <img src="<?php echo getFlagUrl('sa'); ?>" alt="Arabic" class="item-flag" title="Arabic">
          <img src="<?php echo getFlagUrl('tr'); ?>" alt="Turkish" class="item-flag" title="Turkish">
          <img src="<?php echo getFlagUrl('pt'); ?>" alt="Portuguese" class="item-flag" title="Portuguese">
          <img src="<?php echo getFlagUrl('nl'); ?>" alt="Dutch" class="item-flag" title="Dutch">
        </div>
      </div>
      <div class="item-info">
        <span class="item-label"><?php echo t('classroom_label'); ?></span>
        <span class="item-value"><span class="material-icons">public</span><?php echo t('classroom_value'); ?></span>
      </div>
      <div class="item-arrow"><span class="material-icons">arrow_forward</span></div>
    </div>
  </div>
</section>

<!-- SPEAKING ROOMS SECTION -->
<section class="rooms-section">
  <div class="rooms-header">
    <span class="section-eyebrow">
      <span class="material-icons">groups</span>
      <span><?php echo t('rooms_eyebrow'); ?></span>
    </span>
    <h2 class="section-title"><?php echo t('rooms_title'); ?></h2>
    <p class="section-sub"><?php echo t('rooms_sub'); ?></p>
  </div>

  <div class="rooms-grid">
    <div class="rooms-card">
      <div class="rooms-card-icon">🎤</div>
      <h3><?php echo t('rooms_feature1'); ?></h3>
      <p><?php echo t('rooms_feature1_desc'); ?></p>
    </div>
    <div class="rooms-card">
      <div class="rooms-card-icon">🎯</div>
      <h3><?php echo t('rooms_feature2'); ?></h3>
      <p><?php echo t('rooms_feature2_desc'); ?></p>
    </div>
    <div class="rooms-card">
      <div class="rooms-card-icon">💬</div>
      <h3><?php echo t('rooms_feature3'); ?></h3>
      <p><?php echo t('rooms_feature3_desc'); ?></p>
    </div>
  </div>

  <a href="room_dashboard.php?lang=<?php echo $lang; ?>" class="rooms-cta-btn" id="roomsCTA">
    <?php echo t('rooms_cta'); ?>
    <span class="material-icons" style="display:inline-block; font-size:16px; margin-left:6px;">arrow_forward</span>
  </a>
</section>

<!-- HOW IT WORKS SECTION -->
<section class="section">
  <div class="section-header">
    <span class="section-eyebrow">
      <span class="material-icons">lightbulb</span>
      <span><?php echo t('how_eyebrow'); ?></span>
    </span>
    <h2 class="section-title"><?php echo t('how_title'); ?></h2>
  </div>
  <div class="how-grid">
    <div class="how-step">
      <span class="step-num">01</span>
      <h3><?php echo t('how_step1'); ?></h3>
      <p><?php echo t('how_step1_desc'); ?></p>
    </div>
    <div class="how-step">
      <span class="step-num">02</span>
      <h3><?php echo t('how_step2'); ?></h3>
      <p><?php echo t('how_step2_desc'); ?></p>
    </div>
    <div class="how-step">
      <span class="step-num">03</span>
      <h3><?php echo t('how_step3'); ?></h3>
      <p><?php echo t('how_step3_desc'); ?></p>
    </div>
  </div>
</section>

<!-- TESTIMONIALS SECTION -->
<section class="testimonials-section">
  <div class="section-header">
    <span class="section-eyebrow">
      <span class="material-icons">favorite</span>
      <span>LEARNER STORIES</span>
    </span>
    <h2 class="section-title">Loved by Learners Worldwide</h2>
  </div>
  
  <div class="testimonials-grid">
    <div class="testimonial-card">
      <p class="testimonial-text"><?php echo t('testimonial1_text'); ?></p>
      <span class="testimonial-author"><?php echo t('testimonial1_author'); ?></span>
    </div>
    <div class="testimonial-card">
      <p class="testimonial-text"><?php echo t('testimonial2_text'); ?></p>
      <span class="testimonial-author"><?php echo t('testimonial2_author'); ?></span>
    </div>
    <div class="testimonial-card">
      <p class="testimonial-text"><?php echo t('testimonial3_text'); ?></p>
      <span class="testimonial-author"><?php echo t('testimonial3_author'); ?></span>
    </div>
  </div>
</section>

<!-- ALL LANGUAGES SECTION -->
<section class="languages-section">
  <div class="languages-header">
    <h2 class="section-title"><?php echo t('all_languages'); ?></h2>
    <p class="languages-intro"><?php echo t('lang_list_intro'); ?></p>
  </div>

  <div class="language-grid">
    <div class="language-tag">
      <img src="<?php echo getFlagUrl('us'); ?>" alt="English" class="language-flag">
      <span>English</span>
    </div>
    <div class="language-tag">
      <img src="<?php echo getFlagUrl('rw'); ?>" alt="Kinyarwanda" class="language-flag">
      <span>Kinyarwanda</span>
    </div>
    <div class="language-tag">
      <img src="<?php echo getFlagUrl('fr'); ?>" alt="French" class="language-flag">
      <span>French</span>
    </div>
    <div class="language-tag">
      <img src="<?php echo getFlagUrl('tz'); ?>" alt="Kiswahili" class="language-flag">
      <span>Kiswahili</span>
    </div>
    <div class="language-tag">
      <img src="<?php echo getFlagUrl('id'); ?>" alt="Indonesian" class="language-flag">
      <span>Indonesian</span>
    </div>
    <div class="language-tag">
      <img src="<?php echo getFlagUrl('ua'); ?>" alt="Ukrainian" class="language-flag">
      <span>Ukrainian</span>
    </div>
    <div class="language-tag">
      <img src="<?php echo getFlagUrl('vn'); ?>" alt="Vietnamese" class="language-flag">
      <span>Vietnamese</span>
    </div>
    <div class="language-tag">
      <img src="<?php echo getFlagUrl('za'); ?>" alt="Zulu" class="language-flag">
      <span>Zulu</span>
    </div>
    <div class="language-tag">
      <img src="<?php echo getFlagUrl('gb-sct'); ?>" alt="Scottish Gaelic" class="language-flag">
      <span>Scottish Gaelic</span>
    </div>
    <div class="language-tag">
      <img src="<?php echo getFlagUrl('va'); ?>" alt="Latin" class="language-flag">
      <span>Latin</span>
    </div>
    <div class="language-tag">
      <img src="<?php echo getFlagUrl('gr'); ?>" alt="Greek" class="language-flag">
      <span>Greek</span>
    </div>
    <div class="language-tag">
      <img src="<?php echo getFlagUrl('il'); ?>" alt="Hebrew" class="language-flag">
      <span>Hebrew</span>
    </div>
    <div class="language-tag">
      <img src="<?php echo getFlagUrl('pl'); ?>" alt="Polish" class="language-flag">
      <span>Polish</span>
    </div>
    <div class="language-tag">
      <img src="<?php echo getFlagUrl('no'); ?>" alt="Norwegian" class="language-flag">
      <span>Norwegian</span>
    </div>
    <div class="language-tag">
      <img src="<?php echo getFlagUrl('dk'); ?>" alt="Danish" class="language-flag">
      <span>Danish</span>
    </div>
    <div class="language-tag">
      <img src="<?php echo getFlagUrl('fi'); ?>" alt="Finnish" class="language-flag">
      <span>Finnish</span>
    </div>
    <div class="language-tag">
      <img src="<?php echo getFlagUrl('cz'); ?>" alt="Czech" class="language-flag">
      <span>Czech</span>
    </div>
    <div class="language-tag">
      <img src="<?php echo getFlagUrl('sa'); ?>" alt="Arabic" class="language-flag">
      <span>Arabic</span>
    </div>
    <div class="language-tag">
      <img src="<?php echo getFlagUrl('ru'); ?>" alt="Russian" class="language-flag">
      <span>Russian</span>
    </div>
    <div class="language-tag">
      <img src="<?php echo getFlagUrl('in'); ?>" alt="Hindi" class="language-flag">
      <span>Hindi</span>
    </div>
    <div class="language-tag">
      <img src="<?php echo getFlagUrl('tr'); ?>" alt="Turkish" class="language-flag">
      <span>Turkish</span>
    </div>
    <div class="language-tag">
      <img src="<?php echo getFlagUrl('nl'); ?>" alt="Dutch" class="language-flag">
      <span>Dutch</span>
    </div>
    <div class="language-tag">
      <img src="<?php echo getFlagUrl('se'); ?>" alt="Swedish" class="language-flag">
      <span>Swedish</span>
    </div>
    <div class="language-tag">
      <img src="<?php echo getFlagUrl('ie'); ?>" alt="Irish" class="language-flag">
      <span>Irish</span>
    </div>
    <div class="language-tag">
      <img src="<?php echo getFlagUrl('pt'); ?>" alt="Portuguese" class="language-flag">
      <span>Portuguese</span>
    </div>
    <div class="language-tag">
      <img src="<?php echo getFlagUrl('jp'); ?>" alt="Japanese" class="language-flag">
      <span>Japanese</span>
    </div>
    <div class="language-tag">
      <img src="<?php echo getFlagUrl('kr'); ?>" alt="Korean" class="language-flag">
      <span>Korean</span>
    </div>
    <div class="language-tag">
      <img src="<?php echo getFlagUrl('cn'); ?>" alt="Chinese" class="language-flag">
      <span>Chinese</span>
    </div>
  </div>
</section>

<!-- CTA BANNER -->
<div class="cta-banner">
  <div class="cta-banner-inner">
    <h2><?php echo t('download_title'); ?></h2>
    <p><?php echo t('no_credit_card'); ?></p>
    <a href="#games" class="btn btn-primary">
      <?php echo t('start_free'); ?>
      <span class="btn-icon"><span class="material-icons">arrow_forward</span></span>
    </a>
  </div>
</div>

<!-- WHY PLAYMATES WORKS -->
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
      <div class="trust-stat"><span class="num">200+</span><span class="label"><?php echo t('stat_games'); ?></span></div>
      <div class="trust-stat"><span class="num">27</span><span class="label"><?php echo t('stat_subjects'); ?></span></div>
      <div class="trust-stat"><span class="num">50K+</span><span class="label"><?php echo t('stat_langs'); ?></span></div>
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
    <button class="btn btn-secondary" id="downloadAppBtn">
      <?php echo t('download_btn'); ?>
      <span class="btn-icon"><span class="material-icons">phone_iphone</span></span>
    </button>
  </div>
</section>

<footer>
  <span class="logo">Playmates</span>
  <div class="footer-links">
    <a href="#games"><?php echo t('footer_games'); ?></a>
    <a href="#"><?php echo t('footer_schools'); ?></a>
    <a href="#about"><?php echo t('footer_about'); ?></a>
    <a href="#download"><?php echo t('footer_download'); ?></a>
    <a href="careers.php?lang=<?php echo $lang; ?>"><?php echo t('footer_careers'); ?></a>
  </div>
  <span><?php echo t('copyright'); ?></span>
</footer>

<script>
// ── SCROLL REVEAL ANIMATIONS ────────────────────────────────────
function observeRevealElements() {
  const options = {
    threshold: 0.1,
    rootMargin: '0px 0px -60px 0px'
  };

  const observer = new IntersectionObserver(function(entries) {
    entries.forEach(function(entry) {
      if (entry.isIntersecting) {
        entry.target.classList.add('active');
        observer.unobserve(entry.target);
      }
    });
  }, options);

  // Observe collection items (already have staggered animations)
  // Rooms cards will animate on load

  // Observe language tags
  const languageTags = document.querySelectorAll('.language-tag');
  languageTags.forEach(function(tag) {
    observer.observe(tag);
  });

  // Observe testimonial cards
  const testimonials = document.querySelectorAll('.testimonial-card');
  testimonials.forEach(function(card) {
    observer.observe(card);
  });
}

// ── COUNTER ANIMATION FOR STATS ────────────────────────────────
function animateCounters() {
  const options = {
    threshold: 0.5,
    rootMargin: '0px'
  };

  const observer = new IntersectionObserver(function(entries) {
    entries.forEach(function(entry) {
      if (entry.isIntersecting && !entry.target.dataset.animated) {
        animateCounter(entry.target);
        entry.target.dataset.animated = 'true';
        observer.unobserve(entry.target);
      }
    });
  }, options);

  const stats = document.querySelectorAll('.trust-stat .num');
  stats.forEach(function(stat) {
    observer.observe(stat);
  });
}

function animateCounter(element) {
  const text = element.textContent.trim();
  const match = text.match(/(\d+)/);
  if (!match) return;

  const target = parseInt(match[1]);
  const duration = 1000;
  const start = Date.now();
  const initialText = text.replace(/\d+/, '');

  function update() {
    const elapsed = Date.now() - start;
    const progress = Math.min(elapsed / duration, 1);
    const current = Math.floor(target * progress);
    element.textContent = current + initialText;

    if (progress < 1) {
      requestAnimationFrame(update);
    }
  }

  update();
}

// ── LANGUAGE SWITCH ────────────────────────────────────────────
function setLang(lang) {
    var url = new URL(window.location.href);
    url.searchParams.set('lang', lang);
    window.location.href = url.toString();
}

// ── TOAST ─────────────────────────────────────────────────────
function showToast(msg, type) {
    type = type || 'info';
    var old = document.querySelector('.toast-message');
    if (old) old.remove();
    var t = document.createElement('div');
    t.className = 'toast-message';
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(function(){ t.remove(); }, 3000);
}

// ── LOGIN CHECK ────────────────────────────────────────────────
function requireLogin(link, redirectUrl) {
    var isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
    var lang = '<?php echo $lang; ?>';
    
    if (!isLoggedIn) {
        var msg = '<?php echo t('login_required'); ?>';
        showToast(msg);
        var redirect = redirectUrl || link || window.location.href;
        setTimeout(function() {
            window.location.href = 'loginui.php?redirect=' + encodeURIComponent(redirect) + '&lang=' + lang;
        }, 1500);
        return false;
    }
    return true;
}

// ==================== INSTALL APP ====================
const installBtn = document.getElementById('installBtn');
const installBtnMobile = document.getElementById('installBtnMobile');
const installBtnHero = document.getElementById('installBtnHero');
const downloadAppBtn = document.getElementById('downloadAppBtn');
let deferredPrompt = null;

function isIos() {
    return /iphone|ipad|ipod/i.test(navigator.userAgent);
}

function isInStandaloneMode() {
    return window.matchMedia('(display-mode: standalone)').matches
        || window.navigator.standalone === true;
}

// Hide install buttons if already in standalone mode
if (isInStandaloneMode()) {
    [installBtn, installBtnMobile, installBtnHero, downloadAppBtn].forEach(btn => {
        if (btn) btn.style.display = 'none';
    });
}

// Before install prompt event
window.addEventListener('beforeinstallprompt', function(e) {
    e.preventDefault();
    deferredPrompt = e;
    console.log('[PWA] Install prompt ready');
}, { passive: true });

// App installed event
window.addEventListener('appinstalled', function() {
    [installBtn, installBtnMobile, installBtnHero, downloadAppBtn].forEach(btn => {
        if (btn) btn.style.display = 'none';
    });
    deferredPrompt = null;
    showToast('App installed successfully! 🎉', 'success');
}, { passive: true });

// Installation handler
function handleInstallClick(e) {
    if (e) e.preventDefault();
    
    // iOS Safari
    if (isIos() && !isInStandaloneMode()) {
        var iosBanner = document.getElementById('iosInstallBanner');
        if (iosBanner) {
            iosBanner.style.display = 'block';
        }
        return;
    }
    
    // Chrome/Android - use the stored prompt
    if (deferredPrompt) {
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then(function(result) {
            deferredPrompt = null;
            if (result.outcome === 'accepted') {
                showToast('Installing app...', 'success');
            } else {
                showToast('Installation cancelled', 'info');
            }
        });
        return;
    }
    
    // Fallback for other browsers
    showToast('Please use Chrome on Android or Safari on iOS to install the app.', 'info');
}

// Wire up install buttons
[installBtn, installBtnMobile, installBtnHero, downloadAppBtn].forEach(btn => {
    if (btn) {
        btn.addEventListener('click', handleInstallClick, { passive: false });
    }
});

// Service Worker Registration
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/service-worker.js')
            .then(function(reg) { console.log('[SW] Registered:', reg.scope); })
            .catch(function(err) { console.log('[SW] Failed:', err); });
    }, { passive: true });
}

// ── PLAY GAMES BUTTON ────────────────────────────────────────
var playGamesBtn = document.getElementById('playGamesBtn');
if (playGamesBtn) {
    playGamesBtn.addEventListener('click', function(e) {
        e.preventDefault();
        if (<?php echo $isLoggedIn ? 'true' : 'false'; ?>) {
            document.getElementById('games').scrollIntoView({ behavior: 'smooth' });
        } else {
            showToast('Please login first to start learning!', 'warning');
            setTimeout(function() {
                window.location.href = 'loginui.php?redirect=' + encodeURIComponent(window.location.href) + '&lang=<?php echo $lang; ?>';
            }, 1500);
        }
    }, { passive: false });
}

// ── MOBILE MENU ────────────────────────────────────────────────
var mobileToggle = document.getElementById('mobileToggle');
var mobilePanel  = document.getElementById('mobileNavPanel');

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

    var mobileLinks = mobilePanel.querySelectorAll('a');
    for (var i = 0; i < mobileLinks.length; i++) {
        mobileLinks[i].addEventListener('click', function() {
            mobilePanel.classList.remove('open');
            mobileToggle.setAttribute('aria-expanded', 'false');
        }, { passive: true });
    }
}

// ── VIDEO CARD (desktop) ────────────────────────────────────
var videoCard = document.getElementById('videoCard');
if (videoCard) {
    videoCard.addEventListener('click', function(e) {
        e.preventDefault();
        var link = this.getAttribute('href');
        if (requireLogin(link, link)) {
            window.location.href = link;
        }
    }, { passive: false });
}

// ── MOBILE FAB BEHAVIOR ────────────────────────────────────
var videoFab = document.getElementById('videoFab');
var videoFabExpanded = document.getElementById('videoFabExpanded');
var fabWrapper = document.getElementById('videoFabWrapper');
var isExpanded = false;

if (videoFab && videoFabExpanded) {
    videoFab.addEventListener('click', function(e) {
        e.stopPropagation();
        isExpanded = !isExpanded;
        
        if (isExpanded) {
            videoFabExpanded.classList.add('visible');
            videoFab.style.transform = 'scale(0.95)';
            setTimeout(function() {
                videoFab.style.transform = '';
            }, 150);
        } else {
            videoFabExpanded.classList.remove('visible');
        }
    }, { passive: false });

    videoFabExpanded.addEventListener('click', function(e) {
        e.stopPropagation();
        var link = 'room_dashboard.php?lang=<?php echo $lang; ?>';
        if (requireLogin(link, link)) {
            window.location.href = link;
        }
        isExpanded = false;
        videoFabExpanded.classList.remove('visible');
    }, { passive: false });

    document.addEventListener('click', function(e) {
        if (isExpanded && !fabWrapper.contains(e.target)) {
            isExpanded = false;
            videoFabExpanded.classList.remove('visible');
        }
    }, { passive: true });
}

// ── COMMUNITY BUTTON ───────────────────────────────────────
var communityBtn = document.getElementById('communityBtn');
if (communityBtn) {
    communityBtn.addEventListener('click', function(e) {
        e.preventDefault();
        var link = this.getAttribute('href');
        if (requireLogin(link, link)) {
            window.location.href = link;
        }
    }, { passive: false });
}

// ── GAME COLLECTION ITEMS ─────────────────────────────────
var collectionItems = document.querySelectorAll('.collection-item');
for (var i = 0; i < collectionItems.length; i++) {
    collectionItems[i].addEventListener('click', function() {
        var link = this.getAttribute('data-link');
        var needsLogin = this.getAttribute('data-requires-login') === 'true';
        
        if (needsLogin) {
            if (requireLogin(link)) {
                if (link.startsWith('http')) {
                    window.open(link, '_blank');
                } else {
                    window.location.href = link;
                }
            }
        } else if (link) {
            if (link.startsWith('http')) {
                window.open(link, '_blank');
            } else {
                showToast('Explore: ' + link);
            }
        }
    }, { passive: true });
}

// ── ROOMS CTA BUTTON ──────────────────────────────────────
var roomsCTA = document.getElementById('roomsCTA');
if (roomsCTA) {
    roomsCTA.addEventListener('click', function(e) {
        e.preventDefault();
        var link = this.getAttribute('href');
        if (requireLogin(link, link)) {
            window.location.href = link;
        }
    }, { passive: false });
}

// ── INITIALIZE ANIMATIONS ON LOAD ─────────────────────────────
window.addEventListener('load', function() {
    observeRevealElements();
    animateCounters();
}, { passive: true });
</script>
<script>window._phpIsLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;</script>
<script src="/offline-auth.js"></script>
</body>
</html>