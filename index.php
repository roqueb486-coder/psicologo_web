<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'conexion.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Psicólogo Virtual - Tu Compañero de Bienestar Emocional</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb; /* Vibrant blue */
            --primary-dark: #1e40af;
            --secondary: #14b8a6; /* Teal for energy */
            --accent: #f59e0b; /* Warm yellow for pop */
            --success: #22c55e;
            --white: #ffffff;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-600: #4b5563;
            --gray-800: #1f2937;
            --gradient-primary: linear-gradient(135deg, #2563eb 0%, #60a5fa 100%);
            --gradient-hero: linear-gradient(135deg, #2563eb 0%, #14b8a6 50%, #f59e0b 100%);
            --gradient-navbar: linear-gradient(90deg, #2563eb 0%, #14b8a6 100%);
            --gradient-body: linear-gradient(135deg, #dbeafe 0%, #ccfbf1 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--gradient-body);
            color: var(--gray-800);
            line-height: 1.6;
            overflow-x: hidden;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Navbar */
        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            max-width: 1400px;
            background: var(--gradient-navbar);
            padding: 1rem 5%;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            z-index: 1000;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            animation: fadeInDown 0.8s ease-out;
        }

        .nav-links {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .nav-btn {
            padding: 0.8rem 1.8rem;
            background: var(--white);
            color: var(--primary);
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            transition: background 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .nav-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: var(--accent);
            transition: left 0.3s ease;
            z-index: -1;
        }

        .nav-btn:hover::before {
            left: 0;
        }

        .nav-btn:hover {
            color: var(--white);
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4);
        }

        /* Hero Section */
        .hero {
            min-height: 80vh;
            width: 100%;
            max-width: 1400px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            padding: 0 5%;
            text-align: center;
            background: url('https://images.unsplash.com/photo-1516321310764-8d8f6c0c77c0?auto=format&fit=crop&w=1920&q=80') center/cover no-repeat fixed;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--gradient-hero);
            opacity: 0.65;
            z-index: 1;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 1000px;
            animation: fadeInUp 1s ease-out;
        }

        .hero h1 {
            font-size: clamp(2.8rem, 6vw, 4.8rem);
            font-weight: 600;
            color: var(--white);
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .hero p {
            font-size: clamp(1.2rem, 2.5vw, 1.6rem);
            color: var(--gray-100);
            margin-bottom: 2rem;
            font-weight: 400;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        .hero-buttons {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .cta-primary {
            padding: 1rem 2.2rem;
            background: var(--accent);
            color: var(--white);
            font-weight: 600;
            font-size: 1.1rem;
            border-radius: 10px;
            text-decoration: none;
            transition: background 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
        }

        .cta-primary:hover {
            background: #d97706;
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4);
        }

        .cta-secondary {
            padding: 1rem 2.2rem;
            background: transparent;
            color: var(--white);
            font-weight: 500;
            font-size: 1.1rem;
            border: 2px solid var(--white);
            border-radius: 10px;
            text-decoration: none;
            transition: background 0.3s ease, transform 0.2s ease;
        }

        .cta-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: scale(1.1);
        }

        /* Main Container */
        .container {
            max-width: 1400px;
            width: 100%;
            margin: 0 auto;
            padding: 80px 5%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .section {
            width: 100%;
            padding: 60px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }

        .section-title {
            text-align: center;
            margin-bottom: 2rem;
            max-width: 600px;
        }

        .section-title h2 {
            font-size: clamp(2.2rem, 4vw, 3.2rem);
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .section-title p {
            font-size: 1.2rem;
            color: var(--gray-600);
            font-weight: 400;
        }

        /* Features Section */
        .features-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            background: url('https://images.unsplash.com/photo-1516321497487-e288fb19713f?auto=format&fit=crop&w=1920&q=80') center/cover no-repeat;
        }

        .features-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.85);
            z-index: 0;
        }

        .features-section > * {
            position: relative;
            z-index: 1;
        }

        .features-grid {
            display: flex;
            flex-direction: row;
            gap: 1.5rem;
            justify-content: center;
            width: 100%;
            max-width: 1200px;
        }

        .feature-card {
            background: var(--white);
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            flex: 1;
            max-width: 300px;
            animation: fadeInUp 0.8s ease-out;
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 1rem;
            background: var(--secondary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: var(--white);
            transition: transform 0.3s ease;
        }

        .feature-card:hover .feature-icon {
            transform: scale(1.1);
        }

        .feature-card h3 {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 0.8rem;
        }

        .feature-card p {
            color: var(--gray-600);
            font-size: 1rem;
            line-height: 1.5;
        }

        /* AI Process Section */
        .ai-process {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            background: url('https://images.unsplash.com/photo-1516321310764-8d8f6c0c77c0?auto=format&fit=crop&w=1920&q=80') center/cover no-repeat;
        }

        .ai-process::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.85);
            z-index: 0;
        }

        .ai-process > * {
            position: relative;
            z-index: 1;
        }

        .process-steps {
            display: flex;
            flex-direction: row;
            gap: 1.5rem;
            justify-content: center;
            width: 100%;
            max-width: 1200px;
        }

        .process-step {
            background: var(--white);
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            flex: 1;
            max-width: 300px;
            animation: fadeInUp 0.8s ease-out;
        }

        .process-step:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .step-number {
            width: 50px;
            height: 50px;
            background: var(--primary);
            color: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.3rem;
            margin: 0 auto 1rem;
        }

        .process-step h3 {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 0.8rem;
        }

        .process-step p {
            color: var(--gray-600);
            font-size: 1rem;
            line-height: 1.5;
        }

        /* Stats Section */
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            width: 100%;
            max-width: 1200px;
            text-align: center;
        }

        .stat-item {
            background: var(--white);
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            animation: fadeInUp 0.8s ease-out;
        }

        .stat-item:hover {
            transform: translateY(-8px);
        }

        .stat-number {
            font-size: 2.8rem;
            font-weight: 700;
            color: var(--primary);
            display: block;
        }

        .stat-label {
            color: var(--gray-600);
            font-weight: 500;
            margin-top: 0.5rem;
            font-size: 1.1rem;
        }

        /* Footer */
        .footer {
            background: var(--gray-800);
            color: var(--gray-100);
            padding: 3rem 5%;
            text-align: center;
            width: 100%;
            max-width: 1400px;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-section h3 {
            color: var(--white);
            margin-bottom: 1rem;
            font-size: 1.3rem;
            font-weight: 600;
        }

        .footer-section p,
        .footer-section a {
            color: var(--gray-200);
            text-decoration: none;
            line-height: 1.8;
            font-size: 1rem;
            transition: color 0.3s ease;
        }

        .footer-section a:hover {
            color: var(--accent);
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 1.5rem;
            color: var(--gray-200);
            font-size: 0.9rem;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-on-scroll {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        .animate-on-scroll.animated {
            opacity: 1;
            transform: translateY(0);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .features-section,
            .ai-process {
                flex-direction: column;
                align-items: center;
            }

            .features-grid,
            .process-steps {
                flex-direction: column;
                align-items: center;
            }

            .feature-card,
            .process-step {
                max-width: 400px;
            }
        }

        @media (max-width: 768px) {
            .hero {
                background-attachment: scroll;
                min-height: 60vh;
            }

            .hero h1 {
                font-size: clamp(2.2rem, 5vw, 3.5rem);
            }

            .hero p {
                font-size: clamp(1rem, 2vw, 1.3rem);
            }

            .hero-buttons {
                flex-direction: column;
                gap: 1rem;
            }

            .nav-links {
                gap: 1rem;
            }

            .stats {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .nav-btn {
                padding: 0.6rem 1.2rem;
                font-size: 0.9rem;
            }

            .cta-primary, .cta-secondary {
                padding: 0.8rem 1.5rem;
                font-size: 0.9rem;
            }

            .feature-card, .process-step, .stat-item {
                padding: 1.5rem;
            }

            .section-title h2 {
                font-size: clamp(1.8rem, 3.5vw, 2.5rem);
            }

            .section-title p {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-links">
            <a href="login.php" class="nav-btn"><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</a>
            <a href="register.php" class="nav-btn"><i class="fas fa-user-plus"></i> Registrarse</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Psicólogo Virtual</h1>
            <p>Tu compañero de <strong>bienestar emocional</strong> impulsado por inteligencia artificial súper cool.</p>
            <div class="hero-buttons">
                <a href="register.php" class="cta-primary"><i class="fas fa-rocket"></i> ¡Empieza Gratis!</a>
                <a href="#cómo-funciona" class="cta-secondary"><i class="fas fa-play"></i> Mira Cómo Funciona</a>
            </div>
        </div>
    </section>

    <!-- Main Container -->
    <div class="container">
        <!-- Features Section -->
        <section class="section features-section animate-on-scroll">
            <div class="section-title">
                <h2>Transforma Tu Bienestar</h2>
                <p>Funcionalidades diseñadas para apoyarte en cada paso de tu viaje emocional</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <h3>Monitoreo Emocional</h3>
                    <p>Evalúa tus emociones en tiempo real con alertas codificadas por colores para un apoyo proactivo.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <h3>Historial Completo</h3>
                    <p>Accede a tus conversaciones, rastrea tu progreso y descubre patrones para crecer.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h3>Privacidad Absoluta</h3>
                    <p>Tus datos están protegidos con encriptación de nivel militar para máxima confidencialidad.</p>
                </div>
            </div>
        </section>

        <!-- AI Process Section -->
        <section class="section ai-process animate-on-scroll" id="cómo-funciona">
            <div class="section-title">
                <h2>Inteligencia Artificial Avanzada</h2>
                <p>Descubre cómo nuestra tecnología cuida tu bienestar emocional</p>
            </div>
            <div class="process-steps">
                <div class="process-step">
                    <div class="step-number">1</div>
                    <i class="fas fa-comment-dots" style="font-size: 2rem; color: var(--primary); margin-bottom: 1rem;"></i>
                    <h3>Conversación Natural</h3>
                    <p>Habla con libertad, nuestra IA comprende emociones, contexto y matices.</p>
                </div>
                <div class="process-step">
                    <div class="step-number">2</div>
                    <i class="fas fa-brain" style="font-size: 2rem; color: var(--primary); margin-bottom: 1rem;"></i>
                    <h3>Análisis Emocional</h3>
                    <p>Evaluamos tu estado emocional en tiempo real con alertas inteligentes.</p>
                </div>
                <div class="process-step">
                    <div class="step-number">3</div>
                    <i class="fas fa-heartbeat" style="font-size: 2rem; color: var(--primary); margin-bottom: 1rem;"></i>
                    <h3>Respuesta Personalizada</h3>
                    <p>Recibe consejos empáticos adaptados a tus necesidades únicas.</p>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="section stats animate-on-scroll">
            <div class="stat-item">
                <span class="stat-number">24/7</span>
                <div class="stat-label">Disponibilidad</div>
            </div>
            <div class="stat-item">
                <span class="stat-number">+97%</span>
                <div class="stat-label">Precisión Emocional</div>
            </div>
            <div class="stat-item">
                <span class="stat-number">100%</span>
                <div class="stat-label">Confidencial</div>
            </div>
            <div class="stat-item">
                <span class="stat-number">0₿</span>
                <div class="stat-label">Costo Inicial</div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3><i class="fas fa-brain"></i> Psicólogo Virtual</h3>
                <p>Tu compañero de bienestar emocional, siempre listo para ayudarte.</p>
            </div>
            <div class="footer-section">
                <h3>Enlaces Rápidos</h3>
                <p><a href="login.php">Iniciar Sesión</a></p>
                <p><a href="register.php">Registrarse</a></p>
                <p><a href="#cómo-funciona">Cómo Funciona</a></p>
            </div>
            <div class="footer-section">
                <h3>Soporte</h3>
                <p><a href="mailto:support@psicologovirtual.com">support@psicologovirtual.com</a></p>
                <p><a href="#">Política de Privacidad</a></p>
                <p><a href="#">Términos de Servicio</a></p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 Psicólogo Virtual. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Scroll animations with Intersection Observer
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.querySelectorAll('.animate-on-scroll').forEach(el => {
            observer.observe(el);
        });

        // Navbar shadow on scroll
        window.addEventListener('scroll', () => {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.boxShadow = '0 6px 25px rgba(0, 0, 0, 0.2)';
            } else {
                navbar.style.boxShadow = '0 4px 15px rgba(0, 0, 0, 0.2)';
            }
        });

        document.querySelectorAll('.nav-btn, .cta-primary, .cta-secondary').forEach(el => {
            el.addEventListener('mouseenter', () => {
                el.style.transform = 'scale(1.1)';
            });
            el.addEventListener('mouseleave', () => {
                el.style.transform = 'scale(1)';
            });
        });

        document.querySelectorAll('.feature-card, .process-step').forEach(el => {
            el.addEventListener('mouseenter', () => {
                el.style.transform = 'translateY(-8px)';
            });
            el.addEventListener('mouseleave', () => {
                el.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>