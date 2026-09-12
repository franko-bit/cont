<?php
session_start();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['full_name'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLAYMATES - Educational Games Platform</title>
    <link rel="stylesheet" href="styl.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollToPlugin.min.js"></script>
</head>
<body>
    <!-- Scroll Indicator -->
    <div class="scroll-indicator"></div>

    <!-- Header -->
    <header class="header">
        <div class="nav-logo">
            <a href="index.php" class="logo-link">
                <img src="https://res.cloudinary.com/franklinrw/image/upload/v1755169717/kjk_bnrbmp.png" alt="PLAYMATES Logo" class="logo-image">
            </a>
        </div>
        <div class="nav-actions">
            <!--<a href="subscription.html" class="nav-btn">SUBSCRIBE</a>-->
            <?php if ($isLoggedIn): ?>
                
                <a href="logout.php" class="nav-btn login" id="authBtn">LOGOUT</a>
            <?php else: ?>
                <a href="loginui.php" class="nav-btn login" id="authBtn">LOGIN</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Hero Section -->
    <section id="hero" class="section hero-section">
        <div class="hero-content">
            <div class="hero-text">
                <h1>PLAYMATES</h1>
                <p class="hero-subtitle">
                   Making learning fun through interactive educational games. Discover engaging experiences that teach valuable skills while you play, from sustainable development to coding fundamentals.
                </p>
            </div>

            <div class="cta-buttons">
                <button class="btn-primary" id="playGamesBtn">🎮 PLAY GAMES</button>
                <a href="about.php"> <button class="btn-secondary">ABOUT US</button></a>
            </div>
        </div>

        <img src="https://res.cloudinary.com/franklinrw/image/upload/v1762722362/iu_paf7hg.gif" alt="Educational Game Preview" class="featured-game" id="featuredGame">
    </section>

    <!-- Featured Games Collection -->
    <section id="games" class="section featured-collection">
        <div class="collection-header">
            <h2>EDUCATIONAL GAME COLLECTION</h2>
        </div>
        
        <div class="collection-meta">
            <h3 class="collection-subtitle">Learn Through Play</h3>
            <div class="collection-stats">111 Games and Challenges Available, Educational Content</div>
        </div>

        <div class="collection-grid">
            <div class="collection-item" data-link="https://www.sdg.playmates.games/" data-requires-login="true">
                <div class="item-info">
                    <div class="creator-info">
                        <div class="creator-avatar"><img src="Logo-SDGs.png" alt="SDG Logo"></div>
                        <span>Development Game</span>
                    </div>
                    <span class="price">29 Games</span>
                </div>
            </div>
            
           <div class="collection-item" data-link="https://www.coding.playmates.games/" data-requires-login="true">
              <div class="item-info">
                <div class="creator-info">
                  <div class="creator-avatar"><img src="https://res.cloudinary.com/franklinrw/image/upload/v1756461848/1000_F_116064418_tEuuAaPygdFyrt1vUIDGhdtN0bwlsfoc_zzrvrr.jpg" alt="Coding Icon"></div>
                  <span>Coding Games</span>
                </div>
                <span id="countdown" class="price">90 Sets</span>
              </div>
            </div>

            <div class="collection-item" data-link="https://www.class.playmates.games/" data-requires-login="true">
                <div class="item-info">
                    <div class="creator-info">
                        <div class="creator-avatar"><img src="https://res.cloudinary.com/franklinrw/image/upload/v1755169717/kjk_bnrbmp.png" alt="Classroom Icon"></div>
                        <span>Classroom Games</span>
                    </div>
                    <span class="price">6 Subjects</span>
                </div>
            </div>

            <!-- New Language Games Card -->
            <div class="collection-item" data-link="https://www.language.playmates.games/" data-requires-login="true">
                <div class="item-info">
                    <div class="creator-info">
                        <div class="creator-avatar"><img src="https://res.cloudinary.com/franklinrw/image/upload/v1734567890/language-icon_qwerty.jpg" alt="Language Icon"></div>
                        <span>Language Games</span>
                    </div>
                    <span class="price">12 Languages</span>
                </div>
            </div>
        </div>

        <div class="see-collection">
            More educational games to come...
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer-container">
        <div class="hand-illustration">
            <svg viewBox="0 0 120 180" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M60 20 L70 30 L65 40 L70 50 L65 60 L70 70 L65 80 L60 90 L55 100 L50 110 L45 120 L40 130 L35 140 L40 150 L45 155 L50 150 L55 145 L60 150 L65 155 L70 150 L75 145 L70 140 L65 135 L70 130 L75 125 L70 120 L65 115 L70 110 L75 105 L70 100 L65 95 L70 90 L75 85 L80 80 L75 75 L70 70 L75 65 L80 60 L75 55 L70 50 L75 45 L80 40 L85 35 L80 30 L75 25 L70 20 L65 15 L60 10 L55 15 L50 20 L45 15 L40 20 L35 25 L40 30 L45 35 L40 40 L35 45 L40 50 L45 55 L40 60 L35 65 L40 70 L45 75 L40 80 L35 85 L40 90 L45 95" 
                stroke="#1a1a1a" stroke-width="0.5" fill="none"/>
                <ellipse cx="60" cy="140" rx="20" ry="25" fill="none" stroke="#1a1a1a" stroke-width="0.5"/>
                <line x1="50" y1="140" x2="70" y2="140" stroke="#1a1a1a" stroke-width="0.3"/>
                <line x1="52" y1="145" x2="68" y2="145" stroke="#1a1a1a" stroke-width="0.3"/>
                <line x1="54" y1="150" x2="66" y2="150" stroke="#1a1a1a" stroke-width="0.3"/>
            </svg>
        </div>

        <div class="footer-content">
            <h1 class="hero-text-footer">Let's make learning an adventure together.</h1>

            <div class="footer-columns">
                <div class="footer-column">
                    <h3>Educational Games for Everyone</h3>
                    <ul>
                        <li>Individuals</li>
                        <li>Students</li>
                        <li>Schools</li>
                        <li>Educators</li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h3>Learn More</h3>
                    <ul>
                        <li><a href="about.php">About Us</a></li>
                        <!--<li><a href="subscription.html">Subscription Plans</a></li>-->
                    </ul>
                </div>

                <div class="footer-column">
                    <h3>Connect</h3>
                    <ul>
                        <li><a href="https://www.linkedin.com/in/playmates-games-892ba2263/" target="_blank">LinkedIn</a></li>
                    </ul>
                </div>

                <div class="footer-column contact">
                    <h3>Get In Touch</h3>
                    <ul>
                        <li><a href="mailto:hello@playmates.games">hello@playmates.games</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <div class="copyright">
                    <div>© COPYRIGHT 2025 PLAYMATES</div>
                    <div>LEARNING THROUGH PLAY</div>
                </div>

                <div class="location">
                    <div>KIGALI, RWANDA</div>
                    <div class="time" id="time">06:06:34</div>
                </div>

                <div class="tagline">
                    EMPOWERING YOUNG MINDS<span class="pattern">////////////////////////////////////////////////////////</span>
                </div>
            </div>
        </div>
    </footer>

    
    <script>
        // Pass PHP login status to JavaScript
        const isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;

        // ==================== PLAY GAMES BUTTON ====================
        document.getElementById('playGamesBtn').addEventListener('click', function() {
            if (isLoggedIn) {
                // Scroll to games section
                const element = document.getElementById('games');
                const headerHeight = document.querySelector('.header').offsetHeight;
                const targetPosition = element.offsetTop - headerHeight;
                
                gsap.to(window, {
                    duration: 1.5,
                    scrollTo: targetPosition,
                    ease: "power2.inOut"
                });
            } else {
                showToast('Please login first to play games!', 'warning');
                setTimeout(() => {
                    window.location.href = 'loginui.php';
                }, 1500);
            }
        });

        // ==================== GAME COLLECTION ITEMS ====================
        document.querySelectorAll('.collection-item').forEach(item => {
            const link = item.dataset.link;
            const requiresLogin = item.dataset.requiresLogin === 'true';
            
            if (link) {
                item.addEventListener('click', function() {
                    if (requiresLogin && !isLoggedIn) {
                        showToast('Please login to access this game!', 'warning');
                        setTimeout(() => {
                            window.location.href = 'loginui.php';
                        }, 1500);
                    } else {
                        window.open(link, '_blank');
                    }
                });
            }
        });

        // ==================== FEATURED GAME CLICK ====================
        document.getElementById('featuredGame').addEventListener('click', function() {
            if (isLoggedIn) {
                const element = document.getElementById('games');
                const headerHeight = document.querySelector('.header').offsetHeight;
                const targetPosition = element.offsetTop - headerHeight;
                
                gsap.to(window, {
                    duration: 1.5,
                    scrollTo: targetPosition,
                    ease: "power2.inOut"
                });
            } else {
                showToast('Please login to explore games!', 'warning');
                setTimeout(() => {
                    window.location.href = 'loginui.php';
                }, 1500);
            }
        });

        // ==================== TOAST NOTIFICATION ====================
        function showToast(message, type = 'info') {
            const existingToast = document.querySelector('.toast-notification');
            if (existingToast) {
                existingToast.remove();
            }

            const toast = document.createElement('div');
            toast.className = `toast-notification toast-${type}`;
            toast.textContent = message;
            
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'error' ? '#f8d7da' : type === 'success' ? '#d4edda' : type === 'warning' ? '#fff3cd' : '#cce7ff'};
                color: ${type === 'error' ? '#721c24' : type === 'success' ? '#155724' : type === 'warning' ? '#856404' : '#004085'};
                padding: 12px 20px;
                border-radius: 8px;
                border: 1px solid ${type === 'error' ? '#f5c6cb' : type === 'success' ? '#c3e6cb' : type === 'warning' ? '#ffeeba' : '#b3d7ff'};
                z-index: 10000;
                animation: slideInRight 0.3s ease, fadeOut 0.3s ease 2.7s forwards;
                max-width: 300px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                font-weight: 500;
            `;

            const style = document.createElement('style');
            style.textContent = `
                @keyframes slideInRight {
                    from {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
                @keyframes fadeOut {
                    to {
                        opacity: 0;
                        transform: translateX(100%);
                    }
                }
            `;
            document.head.appendChild(style);

            document.body.appendChild(toast);

            setTimeout(() => {
                if (toast.parentNode) {
                    toast.remove();
                }
                if (style.parentNode) {
                    style.remove();
                }
            }, 3000);
        }

        // ==================== TIME UPDATE ====================
        function updateTime() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('time').textContent = `${hours}:${minutes}:${seconds}`;
        }

        updateTime();
        setInterval(updateTime, 1000);

        // ==================== GSAP ANIMATIONS ====================
        gsap.registerPlugin(ScrollTrigger, ScrollToPlugin);

        // Header animation
        gsap.to(".header", {
            opacity: 1,
            duration: 1,
            delay: 0.5,
            ease: "power2.out"
        });

        // Hero section animations
        gsap.to(".hero-content", {
            opacity: 1,
            x: 0,
            duration: 1.2,
            delay: 0.8,
            ease: "power2.out"
        });

        // Featured game animation
        gsap.timeline()
            .to(".featured-game", {
                opacity: 1,
                x: 0,
                duration: 1.2,
                delay: 1.2,
                ease: "power2.out"
            })
            .to(".featured-game", {
                width: "100%",
                duration: 1,
                delay: 0.3,
                ease: "power2.out"
            });

        // Collection animations
        gsap.to(".collection-header", {
            opacity: 1,
            y: 0,
            duration: 1,
            scrollTrigger: {
                trigger: ".collection-header",
                start: "top 80%",
                toggleActions: "play none none reverse"
            }
        });

        gsap.to(".collection-subtitle", {
            opacity: 1,
            x: 0,
            duration: 0.8,
            scrollTrigger: {
                trigger: ".collection-subtitle",
                start: "top 85%",
                toggleActions: "play none none reverse"
            }
        });

        gsap.to(".collection-stats", {
            opacity: 1,
            x: 0,
            duration: 0.8,
            delay: 0.2,
            scrollTrigger: {
                trigger: ".collection-stats",
                start: "top 85%",
                toggleActions: "play none none reverse"
            }
        });

        gsap.to(".collection-item", {
            opacity: 1,
            y: 0,
            duration: 0.8,
            stagger: 0.2,
            scrollTrigger: {
                trigger: ".collection-grid",
                start: "top 75%",
                toggleActions: "play none none reverse"
            }
        });

        gsap.to(".see-collection", {
            opacity: 1,
            y: 0,
            duration: 0.8,
            scrollTrigger: {
                trigger: ".see-collection",
                start: "top 85%",
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

        // Button click animations
        document.querySelectorAll('button').forEach(btn => {
            btn.addEventListener('click', function() {
                gsap.to(this, {
                    scale: 0.95,
                    duration: 0.1,
                    yoyo: true,
                    repeat: 1,
                    ease: "power2.inOut"
                });
            });
        });

        // Hover animations for collection items
        document.querySelectorAll('.collection-item').forEach(item => {
            item.addEventListener('mouseenter', function() {
                gsap.to(this, {
                    scale: 1.02,
                    duration: 0.3,
                    ease: "power2.out"
                });
            });
            
            item.addEventListener('mouseleave', function() {
                gsap.to(this, {
                    scale: 1,
                    duration: 0.3,
                    ease: "power2.out"
                });
            });
        });

        // Subtle hover effect on featured game
        document.getElementById('featuredGame').addEventListener('mouseenter', function() {
            gsap.to(this, {
                scale: 1.02,
                duration: 0.3,
                ease: "power2.out"
            });
        });

        document.getElementById('featuredGame').addEventListener('mouseleave', function() {
            gsap.to(this, {
                scale: 1,
                duration: 0.3,
                ease: "power2.out"
            });
        });
    </script>
    <style>
        /* ============================================
           HOMEPAGE SPECIFIC STYLES (not in shared.css)
           ============================================ */
        
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

        /* Hero Section */
        .hero-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 130px;
            align-items: center;
            margin-left: 4em;
            margin-right: 2rem;
            margin-bottom: 1rem;
            min-height: 100vh;
        }

        .hero-content {
            opacity: 0;
            transform: translateX(-50px);
        }

        .hero-text h1 {
            font-size: 4.5rem;
            font-weight: 300;
            line-height: 0.85;
            margin-bottom: 40px;
            letter-spacing: -3px;
        }

        .hero-subtitle {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 40px;
            opacity: 0.8;
            max-width: 450px;
        }

        .cta-buttons {
            display: flex;
            gap: 0;
        }

        .btn-primary {
            background: transparent;
            border: 2px solid #333;
            padding: 15px 30px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            border-right: none;
            color: #333;
            border-radius: 0;
        }

        .btn-primary:hover {
            background: #333;
            color: white;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #006f4a;
            color: white;
            border: 2px solid #006f4a;
            padding: 15px 30px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            border-radius: 0;
        }

        .btn-secondary:hover {
            background: #005a3c;
            border-color: #005a3c;
            transform: translateY(-2px);
        }

        /* Featured Game Image */
        .featured-game {
            opacity: 0;
            transform: translateX(50px);
            width: 120%;
            height: 600px;
            object-fit: contain;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.5s ease;
        }

        /* Featured Games Collection */
        .featured-collection {
            padding: 0px 40px;
            background: #f5f1eb;
        }

        .collection-header {
            margin-bottom: 60px;
            opacity: 0;
            transform: translateY(30px);
        }

        .collection-header h2 {
            font-size: 1.5rem;
            font-weight: 400;
            margin-bottom: 30px;
            letter-spacing: 1px;
        }

        .collection-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .collection-subtitle {
            font-size: 1.2rem;
            font-weight: 500;
            opacity: 0;
            transform: translateX(-30px);
        }

        .collection-stats {
            font-size: 0.95rem;
            opacity: 0.7;
            transform: translateX(30px);
        }

        .collection-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
            margin-bottom: 40px;
        }

        .collection-item {
            aspect-ratio: 0.9;
            background: #fff;
            border: 1px solid #e0ddd6;
            border-radius: 10px;
            position: relative;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateY(50px);
        }

        .collection-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(
                to bottom,
                transparent 0%,
                transparent 60%,
                rgba(0,0,0,0.3) 100%
            );
            z-index: 1;
            pointer-events: none;
        }

        .collection-item:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        .collection-item:nth-child(1) {
            background-image: url('dvp.gif');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .collection-item:nth-child(2) {
            background-image: url('https://res.cloudinary.com/franklinrw/image/upload/v1762721015/cod_odiog7.gif');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .collection-item:nth-child(3) {
            background-image: url('https://res.cloudinary.com/franklinrw/image/upload/v1762707952/franko_bit_Afro-futuristic_illustration_of_a_smart_African_bo_613837b5-44c7-47ac-8c5d-62f803e2e517_2_xdsx69.gif');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        /* Background for Language Games card */
        .collection-item:nth-child(4) {
            background-image: url('https://res.cloudinary.com/franklinrw/image/upload/v1762722362/iu_paf7hg.gif');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .item-info {
            position: absolute;
            bottom: 15px;
            left: 15px;
            right: 15px;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(10px);
            padding: 15px;
            border-radius: 8px;
            color: white;
            z-index: 2;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .creator-info {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
        }

        .creator-avatar {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            overflow: hidden;
        }

        .creator-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .price {
            font-weight: 700;
            color: #00ff66;
            font-size: 0.8rem;
        }

        .see-collection {
            text-align: center;
            margin-top: 40px;
            margin-bottom: 60px;
            font-size: 1rem;
            opacity: 0.8;
            transition: all 0.3s ease;
            transform: translateY(30px);
        }

        .see-collection:hover {
            opacity: 1;
            transform: translateY(25px);
        }

        /* Nav Actions */
        .nav-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .nav-btn {
            background: transparent;
            border: 2px solid #333;
            padding: 10px 20px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            color: #333;
            text-decoration: none;
            border-radius: 4px;
        }

        .nav-btn:hover {
            background: #333;
            color: white;
            transform: translateY(-2px);
        }

        .nav-btn.login {
            background: #006f4a;
            color: white;
            border: 2px solid #006f4a;
        }

        .nav-btn.login:hover {
            background: #005a3c;
            border-color: #005a3c;
        }

        #authBtn.logged-in {
            background-color: #ff4b5c;
            color: white;
            border-color: #ff4b5c;
        }
        
        #authBtn.logged-in:hover {
            background-color: #e03b4a;
            border-color: #e03b4a;
        }

        /* Footer */
        .footer-container {
            max-width: 100%;
            margin: 0;
            background: #f5f1eb;
            padding: 80px 40px 40px;
            position: relative;
            overflow: hidden;
            border-top: 1px solid #e0ddd6;
        }

        .hand-illustration {
            position: absolute;
            left: 40px;
            top: 50%;
            transform: translateY(-50%);
            width: 120px;
            height: 180px;
            z-index: 1;
        }

        .hand-illustration svg {
            width: 100%;
            height: 100%;
        }

        .footer-content {
            position: relative;
            z-index: 2;
            margin-left: 160px;
        }

        .hero-text-footer {
            font-size: 3.5rem;
            font-weight: 300;
            line-height: 1.1;
            margin-bottom: 60px;
            color: #333;
            max-width: 800px;
            letter-spacing: -1px;
        }

        .footer-columns {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 60px;
            margin-bottom: 80px;
        }

        .footer-column h3 {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 20px;
            color: #333;
            padding-bottom: 12px;
            border-bottom: 1px solid #e0ddd6;
        }

        .footer-column ul {
            list-style: none;
        }

        .footer-column ul li {
            margin-bottom: 12px;
        }

        .footer-column ul li a {
            color: #333;
            text-decoration: none;
            font-size: 1.1rem;
            font-weight: 400;
            transition: all 0.3s ease;
            position: relative;
            padding-bottom: 2px;
        }

        .footer-column ul li a:hover {
            color: #006f4a;
        }

        .footer-column ul li a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 1px;
            bottom: 0;
            left: 0;
            background-color: #006f4a;
            transition: width 0.3s ease;
        }

        .footer-column ul li a:hover::after {
            width: 100%;
        }

        .footer-column.contact a {
            font-size: 1.1rem;
        }

        .footer-bottom {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding-top: 40px;
            border-top: 1px solid #e0ddd6;
        }

        .copyright {
            font-size: 11px;
            letter-spacing: 0.5px;
            color: #333;
            text-transform: uppercase;
        }

        .location {
            font-size: 11px;
            letter-spacing: 0.5px;
            color: #333;
            font-family: 'Courier New', monospace;
        }

        .location .time {
            display: block;
            margin-top: 4px;
        }

        .tagline {
            font-size: 11px;
            letter-spacing: 0.5px;
            color: #333;
            text-transform: uppercase;
        }

        .pattern {
            margin-left: 10px;
            letter-spacing: 2px;
        }

        /* Responsive Styles for Homepage */
        @media (max-width: 900px) {
            .collection-meta {
                flex-direction: column;
                gap: 12px;
                align-items: center;
                text-align: center;
            }

            .collection-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 1024px) {
            .hero-text-footer {
                font-size: 2.5rem;
            }

            .footer-columns {
                grid-template-columns: repeat(2, 1fr);
                gap: 40px;
            }
            
            .footer-content {
                margin-left: 120px;
            }
            
            .hero-section {
                gap: 60px;
                margin-left: 2em;
                margin-right: 2em;
            }
        }

        @media (max-width: 768px) {
            .header {
                padding: 15px 20px;
            }

            .nav-actions {
                gap: 10px;
            }

            .nav-btn {
                padding: 8px 15px;
                font-size: 0.8rem;
            }

            .hero-section {
                grid-template-columns: 1fr;
                gap: 40px;
                padding: 100px 20px 0px;
                text-align: center;
                margin-left: 1rem;
                margin-right: 1rem;
            }
            
            .hero-text h1 {
                font-size: 2.5rem;
            }

            .hero-subtitle {
                max-width: 100%;
                font-size: 1rem;
            }

            .featured-game {
                height: 500px;
                width: 100%;
                order: -1;
            }

            .logo-image {
                height: 30px;
                max-width: 120px;
            }

            .collection-header h2 {
                font-size: 1.8rem;
            }

            .featured-collection {
                padding: 60px 20px;
            }

            .cta-buttons {
                justify-content: center;
            }

            .footer-container {
                padding: 60px 30px 30px;
            }

            .footer-content {
                margin-left: 0;
            }

            .hand-illustration {
                width: 80px;
                height: 120px;
                left: 20px;
            }

            .hero-text-footer {
                font-size: 2rem;
            }

            .footer-columns {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .footer-column ul li a {
                font-size: 1rem;
            }

            .footer-bottom {
                flex-direction: column;
                align-items: flex-start;
                gap: 20px;
            }

            .tagline .pattern {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .hero-text h1 {
                font-size: 2rem;
            }

            .hero-subtitle {
                font-size: 0.95rem;
            }

            .collection-header h2 {
                font-size: 1.5rem;
            }

            .logo-image {
                height: 30px;
                max-width: 100px;
            }

            .hero-section {
                margin-left: 0.5rem;
                margin-right: 0.5rem;
            }

            .featured-game {
                width: 100%;
                height: 400px;
            }
            
            .hero-text-footer {
                font-size: 1.8rem;
            }

            .nav-actions {
                flex-direction: column;
                gap: 5px;
            }

            .nav-btn {
                padding: 6px 12px;
                font-size: 0.7rem;
            }
            
            .cta-buttons {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn-primary, .btn-secondary {
                width: 100%;
                border-right: 2px solid #333;
            }
            
            .btn-primary {
                border-bottom: none;
            }
        }
    </style>
</body>
</html>