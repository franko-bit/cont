<?php
// Start session to get language from landing page
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get language from URL (for switcher) or session
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    // Update session immediately when URL has lang
    $_SESSION['user_lang'] = $lang;
} else {
    $lang = isset($_SESSION['user_lang']) ? $_SESSION['user_lang'] : 'en';
}

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
    <title>About Us - PLAYMATES Educational Games</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f1eb;
            color: #333;
            overflow-x: hidden;
            line-height: 1.6;
        }

        /* Toast Notification */
        .toast-message {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            background: #1a2c2a;
            color: #fff;
            padding: 10px 24px;
            border-radius: 60px;
            font-size: 0.8rem;
            font-weight: 500;
            z-index: 10000;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
            backdrop-filter: blur(8px);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            animation: fadeUp 0.25s ease;
            white-space: nowrap;
            pointer-events: none;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateX(-50%) translateY(20px); }
            to { opacity: 1; transform: translateX(-50%) translateY(0); }
        }

        /* Language Switcher */
        .lang-switcher {
            display: flex;
            gap: 4px;
            align-items: center;
            background: rgba(255,255,255,0.9);
            padding: 4px;
            border-radius: 30px;
            border: 1px solid #e0ddd6;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            margin-left: auto;
        }
        .lang-btn {
            border: none;
            background: transparent;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
            cursor: pointer;
            color: #666;
            transition: all 0.2s ease;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .lang-btn .flag-icon {
            font-size: 14px;
        }
        .lang-btn:hover { color: #333; }
        .lang-btn.active {
            background: #006f4a;
            color: white;
            box-shadow: 0 1px 4px rgba(0,111,74,0.3);
        }

        /* Header - Centered navbar style */
        .header {
            backdrop-filter: blur(10px);
            padding: 16px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e0ddd6;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            opacity: 0;
            background: rgba(245, 241, 235, 0.85);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .back-button {
            height: 32px;
            width: 32px;
            background: #006f4a;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .back-button:hover {
            transform: scale(1.05);
            background: #005a3c;
        }

        .back-arrow {
            font-size: 16px;
        }

        /* Scroll Indicator */
        .scroll-indicator {
            position: fixed;
            top: 0;
            left: 0;
            height: 4px;
            background: linear-gradient(90deg, #006f4a, #667eea);
            z-index: 1001;
            transform-origin: left;
            transform: scaleX(0);
        }

        /* Main Container */
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 120px 40px 80px;
        }

        /* Section Styles */
        .section {
            margin-bottom: 120px;
        }

        .section-label {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #666;
            margin-bottom: 20px;
            opacity: 0;
            transform: translateY(20px);
        }

        /* Hero Section */
        .hero-section {
            margin-bottom: 140px;
        }

        .hero-title {
            font-size: 4rem;
            font-weight: 400;
            line-height: 1.1;
            margin-bottom: 40px;
            letter-spacing: -1px;
            opacity: 0;
            transform: translateY(50px);
            max-width: 800px;
        }

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 6fr;
            gap: 80px;
            margin-bottom: 80px;
        }

        .grid-left {
            opacity: 0;
            transform: translateX(-30px);
        }

        .grid-right {
            opacity: 0;
            transform: translateX(30px);
        }

        .grid-title {
            font-size: 1.5rem;
            font-weight: 400;
            line-height: 1.2;
            margin-bottom: 40px;
            letter-spacing: -1px;
        }

        /* Three Column Grid */
        .three-column-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 60px;
            margin-bottom: 100px;
        }

        /* Four Column Grid */
        .four-column-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 40px;
            margin-bottom: 100px;
        }

        .column {
            opacity: 0;
            transform: translateY(40px);
        }

        .column-label {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
            position: relative;
        }

        .column-label::before {
            content: '';
            position: absolute;
            left: 0;
            bottom: -5px;
            width: 30px;
            height: 2px;
            background: #006f4a;
        }

        .column-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 20px;
            line-height: 1.3;
        }

        .column-description {
            font-size: 1rem;
            line-height: 1.7;
            color: #555;
        }

        /* Two Column Grid */
        .two-column-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 80px;
            margin-bottom: 100px;
        }

        /* Categories Section */
        .categories-section {
            background: white;
            padding: 80px 60px;
            border-radius: 20px;
            margin-bottom: 100px;
            opacity: 0;
            transform: translateY(50px);
        }

        .categories-title {
            font-size: 2.5rem;
            font-weight: 400;
            text-align: center;
            margin-bottom: 60px;
            color: #333;
        }

        .category-item {
            display: flex;
            align-items: flex-start;
            gap: 30px;
            margin-bottom: 40px;
            padding-bottom: 40px;
            border-bottom: 1px solid #e0ddd6;
            opacity: 0;
            transform: translateX(-30px);
        }

        .category-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .category-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
            color: white;
            flex-shrink: 0;
        }

        .category-content h3 {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
        }

        .category-content p {
            font-size: 1.1rem;
            line-height: 1.7;
            color: #555;
        }

        /* Community Section */
        .community-section {
            background: linear-gradient(135deg, #667eea, #764ba2);
            padding: 80px 60px;
            border-radius: 20px;
            color: white;
            text-align: center;
            margin-bottom: 100px;
            opacity: 0;
            transform: translateY(50px);
        }

        .community-title {
            font-size: 2.5rem;
            font-weight: 400;
            margin-bottom: 30px;
        }

        .community-description {
            font-size: 1.2rem;
            line-height: 1.7;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Values List */
        .values-list {
            margin-bottom: 100px;
        }

        .value-item {
            display: grid;
            grid-template-columns: 60px 1fr;
            gap: 40px;
            margin-bottom: 60px;
            padding-bottom: 60px;
            border-bottom: 1px solid #e0ddd6;
            opacity: 0;
            transform: translateX(-30px);
        }

        .value-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .value-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #006f4a;
        }

        .value-content h3 {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 20px;
            line-height: 1.3;
        }

        .value-content p {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #555;
            max-width: 600px;
        }

        /* CTA Section */
        .cta-section {
            text-align: center;
            padding: 100px 0;
            background: linear-gradient(135deg, #006f4a, #667eea);
            border-radius: 30px;
            color: white;
            opacity: 0;
            transform: translateY(50px);
        }

        .cta-title {
            font-size: 2.5rem;
            font-weight: 400;
            margin-bottom: 30px;
            line-height: 1.2;
        }

        .cta-description {
            font-size: 1.2rem;
            margin-bottom: 40px;
            opacity: 0.9;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }

        .cta-button {
            background: white;
            color: #006f4a;
            padding: 18px 40px;
            border: none;
            border-radius: 30px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(255, 255, 255, 0.3);
        }

        /* Cards Grid */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            margin-top: 20px;
        }

        .category-card {
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 12px 12px rgba(0, 0, 0, 0.019);
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            background: white;
        }

        .category-card:hover {
            transform: translateY(-8px);
        }

        .category-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--accent-color, #006f4a);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }

        .category-card:hover::before {
            transform: scaleX(1);
        }

        .category-card.academics::before { background: #4A7C59; }
        .category-card.behavior::before { background: #F5A623; }
        .category-card.sdgs::before { background: #3B82C4; }
        .category-card.coding::before { background: #8B5CF6; }

        .card-title {
            font-size: 20px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 12px;
        }

        .card-description {
            font-size: 14px;
            line-height: 1.6;
            color: #666;
        }

        /* Responsive Design */
        @media (max-width: 968px) {
            .content-grid, .two-column-grid {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .three-column-grid {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .four-column-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 30px;
            }
        }

        @media (max-width: 768px) {
            .main-container {
                padding: 100px 20px 60px;
            }

            .header {
                padding: 12px 16px;
                flex-wrap: wrap;
                gap: 10px;
            }

            .lang-switcher {
                margin-left: 0;
            }

            .hero-title {
                font-size: 2.5rem;
            }

            .grid-title {
                font-size: 1.4rem;
            }

            .cta-title, .categories-title, .community-title {
                font-size: 2rem;
            }

            .categories-section, .community-section {
                padding: 60px 30px;
            }

            .four-column-grid {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .value-item, .category-item {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .back-button {
                height: 28px;
                width: 28px;
            }

            .lang-btn {
                padding: 3px 10px;
                font-size: 10px;
            }
        }

        @media (max-width: 480px) {
            .hero-title {
                font-size: 2rem;
            }

            .section {
                margin-bottom: 80px;
            }

            .cta-section, .community-section {
                padding: 60px 20px;
            }

            .cards-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Scroll Indicator -->
    <div class="scroll-indicator"></div>

    <!-- Header -->
    <header class="header">
        <div class="header-left">
            <a href="landing.php?lang=<?php echo $lang; ?>" class="back-button">
                <span class="back-arrow">←</span>
            </a>
        </div>
        <div class="lang-switcher" role="group" aria-label="Language switcher">
            <button class="lang-btn <?php echo $lang === 'en' ? 'active' : ''; ?>" data-lang="en">🇺🇸 EN</button>
            <button class="lang-btn <?php echo $lang === 'rw' ? 'active' : ''; ?>" data-lang="rw">🇷🇼 RW</button>
            <button class="lang-btn <?php echo $lang === 'sw' ? 'active' : ''; ?>" data-lang="sw">🇹🇿 SW</button>
        </div>
    </header>

    <div class="main-container">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="section-label" data-key="hero_label">Who We Are</div>
            <h1 class="hero-title" data-key="hero_title">We are pioneers in using interactive technology to create engaging, valuable, and transformative educational experiences for children everywhere.</h1>
        </section>

        <!-- What Makes Us Different Section -->
        <section class="content-grid">
            <div class="grid-left">
                <h2 class="grid-title" data-key="diff_title">What makes us different</h2>
            </div>
            <div class="grid-right">
                <div class="three-column-grid">
                    <div class="column">
                        <div class="column-label sustainability" data-key="diff1_label">Sustainability</div>
                        <h3 class="column-title" data-key="diff1_title">Supporting a sustainable learning ecosystem</h3>
                        <p class="column-description" data-key="diff1_desc">We create games that nurture environmental awareness and encourage responsible global citizenship.</p>
                    </div>
                    <div class="column">
                        <div class="column-label respect" data-key="diff2_label">Respect</div>
                        <h3 class="column-title" data-key="diff2_title">Inclusive experiences for all</h3>
                        <p class="column-description" data-key="diff2_desc">We design inclusive experiences that honor diverse learning styles, backgrounds, and abilities, while keeping education rigorous and meaningful.</p>
                    </div>
                    <div class="column">
                        <div class="column-label ethics" data-key="diff3_label">Ethics</div>
                        <h3 class="column-title" data-key="diff3_title">Privacy-focused platforms</h3>
                        <p class="column-description" data-key="diff3_desc">We build transparent, privacy-focused platforms that prioritize learners' well-being over data collection or engagement metrics.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Our Core Values Section -->
        <section class="content-grid">
            <div class="grid-left">
                <h2 class="grid-title" data-key="values_title">Our core values</h2>
            </div>
            <div class="grid-right">
                <div class="four-column-grid">
                    <div class="column">
                        <div class="column-label creativity" data-key="val1_label">Creativity</div>
                        <h3 class="column-title" data-key="val1_title">Innovation in education</h3>
                        <p class="column-description" data-key="val1_desc">We push the boundaries of traditional learning with innovative game design and cutting-edge educational technology.</p>
                    </div>
                    <div class="column">
                        <div class="column-label integrity" data-key="val2_label">Integrity</div>
                        <h3 class="column-title" data-key="val2_title">Educational excellence</h3>
                        <p class="column-description" data-key="val2_desc">We uphold the highest standards of educational integrity while making learning accessible, fun, and rewarding for everyone.</p>
                    </div>
                    <div class="column">
                        <div class="column-label empowerment" data-key="val3_label">Empowerment</div>
                        <h3 class="column-title" data-key="val3_title">Accessibility for all</h3>
                        <p class="column-description" data-key="val3_desc">Even children who cannot afford traditional education can access valuable learning experiences through our games, gaining skills across multiple domains.</p>
                    </div>
                    <div class="column">
                        <div class="column-label education" data-key="val4_label">Education for All</div>
                        <h3 class="column-title" data-key="val4_title">Universal opportunity</h3>
                        <p class="column-description" data-key="val4_desc">We believe in providing children everywhere with opportunities to learn, grow, and develop essential academic, behavioral, and life skills.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Core Focus Goals -->
        <section class="content-grid">
            <div class="grid-left">
                <h2 class="grid-title" data-key="goals_title">Core Focus Goals</h2>
            </div>
            <div class="grid-right">
                <div class="cards-grid">
                    <div class="category-card academics">
                        <h2 class="card-title" data-key="goal1_title">Academics</h2>
                        <p class="card-description" data-key="goal1_desc">Games that build knowledge in math, science, languages, and other school subjects through engaging, interactive experiences.</p>
                    </div>

                    <div class="category-card behavior">
                        <h2 class="card-title" data-key="goal2_title">Behavior & Life Skills</h2>
                        <p class="card-description" data-key="goal2_desc">Games that teach social skills, emotional intelligence, problem-solving, and teamwork in fun, practical ways.</p>
                    </div>

                    <div class="category-card sdgs">
                        <h2 class="card-title" data-key="goal3_title">SDGs (Sustainable Development Goals)</h2>
                        <p class="card-description" data-key="goal3_desc">Games that raise awareness about global challenges like the environment, health, equality, and responsible citizenship.</p>
                    </div>

                    <div class="category-card coding">
                        <h2 class="card-title" data-key="goal4_title">Coding & Technology</h2>
                        <p class="card-description" data-key="goal4_desc">Games that teach programming, logical thinking, and digital skills through fun, interactive challenges.</p>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
        // ── TRANSLATIONS ──────────────────────────────────────────────
        var translations = {
            en: {
                hero_label: 'Who We Are',
                hero_title: 'We are pioneers in using interactive technology to create engaging, valuable, and transformative educational experiences for children everywhere.',
                diff_title: 'What makes us different',
                diff1_label: 'Sustainability',
                diff1_title: 'Supporting a sustainable learning ecosystem',
                diff1_desc: 'We create games that nurture environmental awareness and encourage responsible global citizenship.',
                diff2_label: 'Respect',
                diff2_title: 'Inclusive experiences for all',
                diff2_desc: 'We design inclusive experiences that honor diverse learning styles, backgrounds, and abilities, while keeping education rigorous and meaningful.',
                diff3_label: 'Ethics',
                diff3_title: 'Privacy-focused platforms',
                diff3_desc: 'We build transparent, privacy-focused platforms that prioritize learners\' well-being over data collection or engagement metrics.',
                values_title: 'Our core values',
                val1_label: 'Creativity',
                val1_title: 'Innovation in education',
                val1_desc: 'We push the boundaries of traditional learning with innovative game design and cutting-edge educational technology.',
                val2_label: 'Integrity',
                val2_title: 'Educational excellence',
                val2_desc: 'We uphold the highest standards of educational integrity while making learning accessible, fun, and rewarding for everyone.',
                val3_label: 'Empowerment',
                val3_title: 'Accessibility for all',
                val3_desc: 'Even children who cannot afford traditional education can access valuable learning experiences through our games, gaining skills across multiple domains.',
                val4_label: 'Education for All',
                val4_title: 'Universal opportunity',
                val4_desc: 'We believe in providing children everywhere with opportunities to learn, grow, and develop essential academic, behavioral, and life skills.',
                goals_title: 'Core Focus Goals',
                goal1_title: 'Academics',
                goal1_desc: 'Games that build knowledge in math, science, languages, and other school subjects through engaging, interactive experiences.',
                goal2_title: 'Behavior & Life Skills',
                goal2_desc: 'Games that teach social skills, emotional intelligence, problem-solving, and teamwork in fun, practical ways.',
                goal3_title: 'SDGs (Sustainable Development Goals)',
                goal3_desc: 'Games that raise awareness about global challenges like the environment, health, equality, and responsible citizenship.',
                goal4_title: 'Coding & Technology',
                goal4_desc: 'Games that teach programming, logical thinking, and digital skills through fun, interactive challenges.'
            },
            rw: {
                hero_label: 'Abo Turi Bo',
                hero_title: 'Turi abanyamujyi mu gukoresha ikoranabuhanga rishya mu bumenyi busobanutse, bufite agaciro, kandi buhindura imyigire y\'abana aho bari bose.',
                diff_title: 'Ikitubandanya n\'abandi',
                diff1_label: 'Kurengera Ibidukikije',
                diff1_title: 'Gushyigikira urwunge rw\'imyigire rurambye',
                diff1_desc: 'Duhanga imikino ikundisha abana kurengera ibidukikije no kubatoza kuba abaturage b\'isi bafite inshingano.',
                diff2_label: 'Ubwubahane',
                diff2_title: 'Uburambe buha bose ikaze',
                diff2_desc: 'Duhanga imigendekere y\'imyigire ikubiyemo bose, iha agaciro uburyo butandukanye bwo kwiga, imiryango n\'ubushobozi bwiza bwiza, mu gihe uburezi buguma bufite ireme n\'agaciro.',
                diff3_label: 'Imyitwarire Mbonera',
                diff3_title: 'Imbuga zibanda ku mutekano w\'amakuru',
                diff3_desc: 'Mwubaka imbuga ziboneye kandi zizewe zishyira imbere imibereho myiza y\'abanyeshuri aho kwibanda ku gukusanya amakuru cyangwa imibare y\'abakoresha urubuga.',
                values_title: 'Indangagaciro zacu z\'ibanze',
                val1_label: 'Ubihanzu',
                val1_title: 'Udushya mu burezi',
                val1_desc: 'Durenga imbibi z\'imyigire ya gakondo binyuze mu guhanga imikino mishya n\'ikoranabuhanga rigezweho ry\'uburezi.',
                val2_label: 'Ubunyangamugayo',
                val2_title: 'Ireme ry\'uburezi ryo ku rwego rwo hejuru',
                val2_desc: 'Dusigasira indangagaciro z\'ubunyangamugayo mu burezi mu gihe tugira imyigire kureba buri wese, ishimishije, kandi ifite inyungu kuri bose.',
                val3_label: 'Gushoboza',
                val3_title: 'Uburyo bwo kwiga bukorera bose',
                val3_desc: 'Ndetse n\'abana badafite ubushobozi bwo kubona uburezi bwa gakondo bashobora kugera ku myigire ifite agaciro binyuze mu mikino yacu, bityo bakunguka ubumenyi mu nzego zitandukanye.',
                val4_label: 'Uburezi kuri Bose',
                val4_title: 'Mahirwe angana kuri bose',
                val4_desc: 'Twemera ko abana bose aho bari bose bakwiriye guhabwa amahirwe yo kwiga, gukura, no kwitwaza ubumenyi bw\'ibanze mu masomo, mu myitwarire, n\'ubuzima busanzwe.',
                goals_title: 'Intego z\'Ibanze Twibandaho',
                goal1_title: 'Amasomo yo mu Shuri',
                goal1_desc: 'Imikino yubaka ubumenyi mu mibare, siyanse, indimi, n\'andi masomo yo mu shuri binyuze mu myigire ishimishije kandi isabanya.',
                goal2_title: 'Imyitwarire n\'Ubumenyi bw\'Ubuzima',
                goal2_desc: 'Imikino yigisha ubumenyi mbanzirizamubano, ubwenge bw\'amarangamutima, gukemura ibibazo, n\'ubufatanye mu buryo bushimishije kandi bw\'ingiro.',
                goal3_title: 'SDGs (Intego z\'Iterambere Rirambye)',
                goal3_desc: 'Imikino ikangurira abantu kumenya ibibazo by\'isi yose nko kurengera ibidukikije, ubuzima, ubwuzuzanye, n\'ubuturage bufite inshingano.',
                goal4_title: 'Gukora Kodi & Ikoranabuhanga',
                goal4_desc: 'Imikino yigisha porogaramu za mudasobwa, gutekereza neza, n\'ubumenyi bw\'ikoranabuhanga binyuze mu mbogamizi zishimishije kandi zisabanya.'
            },
            sw: {
                hero_label: 'Sisi ni Nani',
                hero_title: 'Sisi ni waanzilishi katika kutumia teknolojia shirikishi kuunda uzoefu wa kielimu unaovutia, wenye thamani, na unaobadilisha maisha ya watoto kila mahali.',
                diff_title: 'Kile kinachotutofautisha',
                diff1_label: 'Uendelevu',
                diff1_title: 'Kusaidia mfumo endelevu wa kujifunza',
                diff1_desc: 'Tunatengeneza michezo inayokuza uelewa wa mazingira na kuhimiza uraia wa kimataifa wenye uwajibikaji.',
                diff2_label: 'Heshima',
                diff2_title: 'Uzoefu jumuishi kwa wote',
                diff2_desc: 'Tunaunda mazingira jumuishi yanayoheshimu mitindo mbalimbali ya kujifunza, asili, na uwezo, huku tukidumisha elimu yenye ubora na maana.',
                diff3_label: 'Maadili',
                diff3_title: 'Mifumo inayozingatia faragha',
                diff3_desc: 'Tunajenga mifumo ya uwazi inayozingatia faragha, ikipa kipaumbele ustawi wa wanafunzi badala ya ukusanyaji wa data au vipimo vya ushiriki.',
                values_title: 'Maadili yetu ya msingi',
                val1_label: 'Ubunifu',
                val1_title: 'Ubunifu katika elimu',
                val1_desc: 'Tunasukuma mipaka ya ujifunzaji wa kijadi kwa kutumia muundo wa michezo ya kibunifu na teknolojia ya kisasa ya kielimu.',
                val2_label: 'Uadilifu',
                val2_title: 'Ubora wa kielimu',
                val2_desc: 'Tunadumisha viwango vya juu zaidi vya uadilifu wa kielimu huku tukifanya ujifunzaji upatikane kwa urahisi, uwe wa kufurahisha, na wenye manufaa kwa kila mtu.',
                val3_label: 'Uwezeshaji',
                val3_title: 'Upatikanaji kwa wote',
                val3_desc: 'Hata watoto ambao hawawezi kumudu elimu ya kawaida wanaweza kupata mafunzo yenye thamani kupitia michezo yetu, wakijipatia ujuzi katika nyanja mbalimbali.',
                val4_label: 'Elimu kwa Wote',
                val4_title: 'Fursa ya ulimwengu wote',
                val4_desc: 'Tunaamini katika kuwapatia watoto kila mahali fursa za kujifunza, kukua, na kukuza ujuzi muhimu wa kitaaluma, kitabia, na wa maisha.',
                goals_title: 'Malengo Makuu ya Kuzingatiwa',
                goal1_title: 'Kitaaluma',
                goal1_desc: 'Michezo inayojenga ujuzi katika hisabati, sayansi, lugha, na masomo mengine ya shule kupitia uzoefu unaovutia na wa kushirikiana.',
                goal2_title: 'Tabia & Ujuzi wa Maisha',
                goal2_desc: 'Michezo inayofundisha ujuzi wa kijamii, akili ya kihisia, utatuzi wa matatizo, na kazi ya timu kwa njia za kufurahisha na za vitendo.',
                goal3_title: 'SDGs (Malengo ya Maendeleo Endelevu)',
                goal3_desc: 'Michezo inayoongeza uelewa kuhusu changamoto za kimataifa kama mazingira, afya, usawa, na uraia wenye uwajibikaji.',
                goal4_title: 'Usimbaji & Teknolojia',
                goal4_desc: 'Michezo inayofundisha upangaji programu (coding), fikra tunduizi, na ujuzi wa kidijitali kupitia changamoto za kufurahisha na shirikishi.'
            }
        };

        var currentLang = '<?php echo $lang; ?>';

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

        // ── APPLY TRANSLATIONS ────────────────────────────────────────
        function applyLang(lang) {
            var dict = translations[lang];
            if (!dict) return;
            document.documentElement.lang = lang;
            document.querySelectorAll('[data-key]').forEach(function(el) {
                var key = el.getAttribute('data-key');
                if (dict[key] !== undefined && !el.classList.contains('lang-btn')) {
                    el.textContent = dict[key];
                }
            });
            document.querySelectorAll('.lang-btn').forEach(function(btn) {
                btn.classList.toggle('active', btn.getAttribute('data-lang') === lang);
            });
        }

        // ── FIXED LANGUAGE SWITCH FUNCTION ──────────────────────────
        function setLanguage(lang) {
            if (!translations[lang]) return;
            
            // Save to localStorage
            try { localStorage.setItem('playmates_lang', lang); } catch(e){}
            
            // Redirect to the same page with the new language parameter
            var url = new URL(window.location.href);
            url.searchParams.set('lang', lang);
            window.location.href = url.toString();
        }

        // ── LANGUAGE BUTTONS ──────────────────────────────────────────
        document.querySelectorAll('.lang-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                var lang = this.getAttribute('data-lang');
                if (lang && (lang === 'en' || lang === 'rw' || lang === 'sw')) {
                    setLanguage(lang);
                }
            });
        });

        // ── INIT: apply language from PHP ────────────────────────────
        (function() {
            var phpLang = '<?php echo $lang; ?>';
            // Apply the language from PHP
            applyLang(phpLang);
            currentLang = phpLang;
            
            // Check if localStorage has a different language and redirect
            try {
                var saved = localStorage.getItem('playmates_lang');
                if (saved && translations[saved] && saved !== phpLang) {
                    var url = new URL(window.location.href);
                    url.searchParams.set('lang', saved);
                    window.location.href = url.toString();
                }
            } catch(e) {}
        })();

        // ── BACK BUTTON ───────────────────────────────────────────────
        function goBack() {
            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = 'landing.php?lang=<?php echo $lang; ?>';
            }
        }

        // ── GSAP ANIMATIONS ───────────────────────────────────────────
        gsap.registerPlugin(ScrollTrigger);

        // Header animation
        gsap.to(".header", {
            opacity: 1,
            duration: 1,
            delay: 0.5,
            ease: "power2.out"
        });

        // Hero section animations
        gsap.timeline({ delay: 0.8 })
            .to(".hero-section .section-label", {
                opacity: 1,
                y: 0,
                duration: 0.8,
                ease: "power2.out"
            })
            .to(".hero-title", {
                opacity: 1,
                y: 0,
                duration: 1.2,
                ease: "power2.out"
            }, "-=0.4");

        // Content grid animations
        gsap.utils.toArray(".content-grid").forEach(function(grid) {
            var left = grid.querySelector(".grid-left");
            var right = grid.querySelector(".grid-right");
            if (left) {
                gsap.to(left, {
                    opacity: 1,
                    x: 0,
                    duration: 1,
                    scrollTrigger: {
                        trigger: grid,
                        start: "top 75%",
                        toggleActions: "play none none reverse"
                    }
                });
            }
            if (right) {
                gsap.to(right, {
                    opacity: 1,
                    x: 0,
                    duration: 1,
                    delay: 0.2,
                    scrollTrigger: {
                        trigger: grid,
                        start: "top 75%",
                        toggleActions: "play none none reverse"
                    }
                });
            }
        });

        // Column animations
        gsap.to(".column", {
            opacity: 1,
            y: 0,
            duration: 0.8,
            stagger: 0.15,
            scrollTrigger: {
                trigger: ".three-column-grid",
                start: "top 75%",
                toggleActions: "play none none reverse"
            }
        });

        gsap.to(".four-column-grid .column", {
            opacity: 1,
            y: 0,
            duration: 0.8,
            stagger: 0.1,
            scrollTrigger: {
                trigger: ".four-column-grid",
                start: "top 75%",
                toggleActions: "play none none reverse"
            }
        });

        // Cards animation
        gsap.to(".category-card", {
            opacity: 1,
            y: 0,
            duration: 0.8,
            stagger: 0.15,
            scrollTrigger: {
                trigger: ".cards-grid",
                start: "top 80%",
                toggleActions: "play none none reverse"
            }
        });

        // Scroll progress indicator
        gsap.to(".scroll-indicator", {
            scaleX: 1,
            transformOrigin: "left center",
            ease: "none",
            scrollTrigger: {
                trigger: "body",
                start: "top top",
                end: "bottom bottom",
                scrub: true
            }
        });

        // Back button click animation
        document.querySelector('.back-button').addEventListener('click', function() {
            gsap.to(this, {
                scale: 0.95,
                duration: 0.1,
                yoyo: true,
                repeat: 1,
                ease: "power2.inOut"
            });
        });

        // Subtle parallax for sections
        gsap.utils.toArray('.section, .content-grid').forEach(function(section) {
            gsap.to(section, {
                y: -20,
                ease: "none",
                scrollTrigger: {
                    trigger: section,
                    start: "bottom bottom",
                    end: "top top",
                    scrub: 1
                }
            });
        });

        // Set initial states for cards (they start invisible)
        document.querySelectorAll('.category-card').forEach(function(card) {
            card.style.opacity = '0';
            card.style.transform = 'translateY(40px)';
        });

        // Re-run card animation after language change to ensure visibility
        function refreshCardAnimation() {
            document.querySelectorAll('.category-card').forEach(function(card) {
                card.style.opacity = '';
                card.style.transform = '';
            });
        }

        // Override applyLang to refresh card visibility
        var originalApplyLang = applyLang;
        applyLang = function(lang) {
            originalApplyLang(lang);
            setTimeout(refreshCardAnimation, 300);
        };
    </script>
</body>
</html>