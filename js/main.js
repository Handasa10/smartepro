/**
 * Smart Energy - Main JavaScript
 * Professional interactions, animations, and functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const navbar = document.getElementById('navbar');
    const hamburger = document.getElementById('hamburger');
    const navMenu = document.getElementById('navMenu');
    const navLinks = document.querySelectorAll('.nav-link');
    const backToTop = document.getElementById('backToTop');
    const particlesContainer = document.getElementById('particles');
    const animatedElements = document.querySelectorAll('[data-animate]');
    const counters = document.querySelectorAll('.stat-number');
    const filterButtons = document.querySelectorAll('.filter-btn');
    const projectCards = document.querySelectorAll('.project-card');
    const contactForm = document.getElementById('contactForm');
    const formSuccess = document.getElementById('formSuccess');

    // ========================================
    // NAVBAR SCROLL EFFECT
    // ========================================
    function handleNavbarScroll() {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    }

    window.addEventListener('scroll', handleNavbarScroll, { passive: true });
    handleNavbarScroll();

    // ========================================
    // MOBILE MENU
    // ========================================
    hamburger.addEventListener('click', function() {
        hamburger.classList.toggle('active');
        navMenu.classList.toggle('active');
        document.body.style.overflow = navMenu.classList.contains('active') ? 'hidden' : '';
    });

    // Close mobile menu on link click
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            hamburger.classList.remove('active');
            navMenu.classList.remove('active');
            document.body.style.overflow = '';
            
            // Update active link
            navLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!navbar.contains(e.target) && navMenu.classList.contains('active')) {
            hamburger.classList.remove('active');
            navMenu.classList.remove('active');
            document.body.style.overflow = '';
        }
    });

    // ========================================
    // SMOOTH SCROLL FOR ANCHOR LINKS
    // ========================================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href.length < 2) return;
            const target = document.getElementById(href.slice(1));
            if (target) {
                e.preventDefault();
                const offset = navbar.offsetHeight;
                const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - offset;
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });

    // ========================================
    // ACTIVE NAV LINK ON SCROLL
    // ========================================
    const sections = document.querySelectorAll('section[id]');
    
    function updateActiveNav() {
        const scrollPosition = window.scrollY + navbar.offsetHeight + 100;
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.offsetHeight;
            const sectionId = section.getAttribute('id');
            
            if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                navLinks.forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('href') === '#' + sectionId) {
                        link.classList.add('active');
                    }
                });
            }
        });
    }

    window.addEventListener('scroll', updateActiveNav, { passive: true });

    // ========================================
    // PARTICLE EFFECT
    // ========================================
    function createParticles() {
        if (!particlesContainer) return;
        
        const particleCount = window.innerWidth < 768 ? 15 : 25;
        
        for (let i = 0; i < particleCount; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.width = (Math.random() * 3 + 2) + 'px';
            particle.style.height = particle.style.width;
            particle.style.animationDuration = (Math.random() * 10 + 10) + 's';
            particle.style.animationDelay = (Math.random() * 15) + 's';
            particle.style.opacity = Math.random() * 0.4 + 0.1;
            
            // Random colors between cyan and green
            const colors = ['#00d4ff', '#7ee787', '#00a8cc'];
            particle.style.background = colors[Math.floor(Math.random() * colors.length)];
            
            particlesContainer.appendChild(particle);
        }
    }
    
    createParticles();

    // ========================================
    // SCROLL ANIMATIONS (Intersection Observer)
    // ========================================
    const observerOptions = {
        root: null,
        rootMargin: '0px 0px -80px 0px',
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const delay = entry.target.dataset.delay || 0;
                setTimeout(() => {
                    entry.target.classList.add('animated');
                }, delay);
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    animatedElements.forEach(el => observer.observe(el));

    // ========================================
    // COUNTER ANIMATION
    // ========================================
    function animateCounter(counter) {
        const target = parseInt(counter.dataset.count);
        const duration = 2000;
        const step = target / (duration / 16);
        let current = 0;

        const updateCounter = () => {
            current += step;
            if (current < target) {
                counter.textContent = Math.floor(current);
                requestAnimationFrame(updateCounter);
            } else {
                counter.textContent = target;
            }
        };

        updateCounter();
    }

    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                counterObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    counters.forEach(counter => counterObserver.observe(counter));

    // ========================================
    // PROJECT FILTERING
    // ========================================
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filter = this.dataset.filter;
            
            // Update active button
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Filter projects
            projectCards.forEach(card => {
                const category = card.dataset.category;
                
                if (filter === 'all' || category === filter) {
                    card.style.display = 'block';
                    card.style.animation = 'fadeInUp 0.5s ease forwards';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    // ========================================
    // CONTACT FORM
    // ========================================
    if (contactForm) {
        const isArabic = document.documentElement.lang === 'ar';
        const formError = document.getElementById('formError');
        const text = isArabic ? {
            sending: 'جارٍ الإرسال...',
            failed: 'تعذّر إرسال رسالتك. يرجى المحاولة مرة أخرى أو التواصل معنا عبر الهاتف أو واتساب.'
        } : {
            sending: 'Sending...',
            failed: 'Your message could not be sent. Please try again, or reach us by phone or WhatsApp.'
        };

        function showFormError(message) {
            formError.textContent = message;
            formError.hidden = false;
        }

        contactForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            formError.hidden = true;

            const submitBtn = this.querySelector('.btn-submit');
            const originalContent = submitBtn.innerHTML;

            // Loading state
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + text.sending;
            submitBtn.disabled = true;

            try {
                const response = await fetch(contactForm.action, {
                    method: 'POST',
                    body: new FormData(contactForm),
                    headers: { 'Accept': 'application/json' }
                });
                const result = await response.json();

                if (result.success) {
                    formSuccess.classList.add('show');
                    contactForm.reset();

                    // Hide success message after 6 seconds
                    setTimeout(() => {
                        formSuccess.classList.remove('show');
                    }, 6000);
                } else {
                    showFormError(result.message || text.failed);
                }
            } catch (err) {
                showFormError(text.failed);
            } finally {
                submitBtn.innerHTML = originalContent;
                submitBtn.disabled = false;
            }
        });

        // Confirmation after a no-JavaScript submission (contact.php redirects back with ?sent=1)
        if (new URLSearchParams(window.location.search).get('sent') === '1') {
            formSuccess.classList.add('show');
        }
    }

    // ========================================
    // FOOTER YEAR
    // ========================================
    const yearEl = document.getElementById('year');
    if (yearEl) yearEl.textContent = new Date().getFullYear();

    // ========================================
    // BACK TO TOP BUTTON
    // ========================================
    function handleBackToTop() {
        if (window.scrollY > 600) {
            backToTop.classList.add('show');
        } else {
            backToTop.classList.remove('show');
        }
    }

    window.addEventListener('scroll', handleBackToTop, { passive: true });
    
    backToTop.addEventListener('click', function(e) {
        e.preventDefault();
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // ========================================
    // PARALLAX EFFECT FOR HERO
    // ========================================
    const heroBg = document.querySelector('.hero-bg img');
    
    if (heroBg && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const rate = scrolled * 0.4;
            heroBg.style.transform = `translateY(${rate}px) scale(1.1)`;
        }, { passive: true });
    }

    // ========================================
    // PROJECT CARD HOVER EFFECT
    // ========================================
    projectCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.querySelector('.project-image img').style.transform = 'scale(1.1)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.querySelector('.project-image img').style.transform = 'scale(1)';
        });
    });

    // ========================================
    // FORM INPUT FOCUS EFFECTS
    // ========================================
    const formInputs = document.querySelectorAll('.form-input');
    
    formInputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });

    // ========================================
    // ADD FADE IN UP KEYFRAME DYNAMICALLY
    // ========================================
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    `;
    document.head.appendChild(style);

    // ========================================
    // CANVAS ANIMATION RUNNER
    // Only animates while the canvas is on screen; draws a single
    // still frame for visitors who prefer reduced motion.
    // ========================================
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function animateWhenVisible(canvas, drawFrame) {
        if (reduceMotion) {
            drawFrame();
            window.addEventListener('resize', drawFrame);
            return;
        }

        let running = false;

        function loop() {
            if (!running) return;
            drawFrame();
            requestAnimationFrame(loop);
        }

        new IntersectionObserver(([entry]) => {
            const wasRunning = running;
            running = entry.isIntersecting;
            if (running && !wasRunning) requestAnimationFrame(loop);
        }).observe(canvas);
    }

    // ========================================
    // ELECTROMAGNETIC WAVE CANVAS ANIMATION
    // ========================================
    const waveCanvas = document.getElementById('waveCanvas');
    if (waveCanvas) {
        const ctx = waveCanvas.getContext('2d');
        let phase = 0;
        let phase2 = 0;

        function resizeCanvas() {
            waveCanvas.width = waveCanvas.parentElement.clientWidth;
            waveCanvas.height = waveCanvas.parentElement.clientHeight;
        }

        window.addEventListener('resize', resizeCanvas);
        resizeCanvas();

        function drawWave() {
            ctx.clearRect(0, 0, waveCanvas.width, waveCanvas.height);

            const width = waveCanvas.width;
            const height = waveCanvas.height;
            const centerY = height / 2;

            // Draw wave 1 (Primary: Lime Green, slower, higher amplitude)
            ctx.beginPath();
            ctx.strokeStyle = '#7ee787';
            ctx.lineWidth = 2.5;
            ctx.shadowBlur = 12;
            ctx.shadowColor = '#7ee787';

            for (let x = 0; x < width; x++) {
                const angle = (x / width) * Math.PI * 4 + phase;
                const modulation = Math.sin((x / width) * Math.PI * 1.5 + phase * 0.5);
                const y = centerY + Math.sin(angle) * 16 * modulation;

                if (x === 0) {
                    ctx.moveTo(x, y);
                } else {
                    ctx.lineTo(x, y);
                }
            }
            ctx.stroke();

            // Draw wave 2 (Secondary: Cyan, faster, lower amplitude)
            ctx.beginPath();
            ctx.strokeStyle = '#00d4ff';
            ctx.lineWidth = 1.5;
            ctx.shadowBlur = 8;
            ctx.shadowColor = '#00d4ff';

            for (let x = 0; x < width; x++) {
                const angle = (x / width) * Math.PI * 8 - phase2;
                const modulation = Math.cos((x / width) * Math.PI * 1 + phase2 * 0.2);
                const y = centerY + Math.sin(angle) * 10 * modulation;

                if (x === 0) {
                    ctx.moveTo(x, y);
                } else {
                    ctx.lineTo(x, y);
                }
            }
            ctx.stroke();

            // Reset shadows for next frame performance
            ctx.shadowBlur = 0;

            phase += 0.02;
            phase2 += 0.03;
        }

        animateWhenVisible(waveCanvas, drawWave);
    }

    // ========================================
    // ELECTROMAGNETIC WAVE CANVAS 2
    // ========================================
    const waveCanvas2 = document.getElementById('waveCanvas2');
    if (waveCanvas2) {
        const ctx2 = waveCanvas2.getContext('2d');
        let phase = 0;
        let phase2 = 0;

        function resizeCanvas2() {
            waveCanvas2.width = waveCanvas2.parentElement.clientWidth;
            waveCanvas2.height = waveCanvas2.parentElement.clientHeight;
        }

        window.addEventListener('resize', resizeCanvas2);
        resizeCanvas2();

        function drawWave2() {
            ctx2.clearRect(0, 0, waveCanvas2.width, waveCanvas2.height);

            const width = waveCanvas2.width;
            const height = waveCanvas2.height;
            const centerY = height / 2;

            // Draw wave 1 (High frequency carrier with envelope modulation - green)
            ctx2.beginPath();
            ctx2.strokeStyle = '#7ee787';
            ctx2.lineWidth = 2.0;
            ctx2.shadowBlur = 10;
            ctx2.shadowColor = '#7ee787';

            for (let x = 0; x < width; x++) {
                const carrier = Math.sin((x / width) * Math.PI * 24 + phase * 2);
                const envelope = Math.sin((x / width) * Math.PI * 2 + phase * 0.3);
                const y = centerY + carrier * 20 * Math.pow(Math.abs(envelope), 3) * Math.sign(envelope);

                if (x === 0) {
                    ctx2.moveTo(x, y);
                } else {
                    ctx2.lineTo(x, y);
                }
            }
            ctx2.stroke();

            // Draw wave 2 (Cyan background wave, phase shifted)
            ctx2.beginPath();
            ctx2.strokeStyle = '#00d4ff';
            ctx2.lineWidth = 1.0;
            ctx2.shadowBlur = 5;
            ctx2.shadowColor = '#00d4ff';

            for (let x = 0; x < width; x++) {
                const carrier = Math.sin((x / width) * Math.PI * 18 - phase2 * 1.5);
                const envelope = Math.cos((x / width) * Math.PI * 3 + phase2 * 0.4);
                const y = centerY + carrier * 12 * Math.pow(Math.abs(envelope), 2);

                if (x === 0) {
                    ctx2.moveTo(x, y);
                } else {
                    ctx2.lineTo(x, y);
                }
            }
            ctx2.stroke();

            ctx2.shadowBlur = 0;

            phase += 0.02;
            phase2 += 0.025;
        }

        animateWhenVisible(waveCanvas2, drawWave2);
    }

    // ========================================
    // PREFERS REDUCED MOTION
    // ========================================
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        animatedElements.forEach(el => {
            el.style.opacity = '1';
            el.style.transform = 'none';
        });
    }
});
