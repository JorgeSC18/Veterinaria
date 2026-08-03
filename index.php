<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido - VetVital</title>
    <link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #16a34a;
            --primary-hover: #166534;
            --overlay-color: rgba(22, 101, 52, 0.75); 
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            color: white;
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
            background-color: #0f172a;
        }

        /* --- CAROUSEL DE FONDO DINÁMICO --- */
        .bg-slider {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            z-index: -2;
            background-color: #111;
        }

        .slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            opacity: 0;
            transition: opacity 1.8s ease-in-out;
        }

        .slide.active {
            opacity: 1;
        }

        .slider-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background: linear-gradient(135deg, rgba(22, 163, 74, 0.8) 0%, var(--overlay-color) 100%);
            z-index: -1;
        }

        /* --- ESTILO GLOBAL PARA LOGOS CIRCULARES --- */
        .logo-circular {
            border-radius: 50%;         /* Recorte perfectamente circular */
            object-fit: cover;          /* Evita distorsiones en imágenes rectangulares */
            background-color: #ffffff;  /* Fondo de seguridad blanco por si hay transparencias */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }

        /* --- NAVBAR SUPERIOR REORGANIZADA --- */
        .navbar {
            width: 100%;
            padding: 20px 5%;
            display: flex;
            justify-content: space-between; 
            align-items: center;
            position: absolute;
            top: 0;
            left: 0;
            z-index: 10;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: white;
        }

        /* Logo de la Navbar */
        .nav-logo-img {
            height: 45px;
            width: 45px; /* Proporciones 1:1 para el círculo */
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .nav-brand span {
            font-size: 1.6rem;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        /* Grupo de botones derecho */
        .nav-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .login-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background-color: white;
            color: var(--primary-hover);
            text-decoration: none;
            padding: 12px 26px;
            border-radius: 50px;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .login-btn:hover {
            background-color: var(--primary-hover);
            color: white;
            transform: translateY(-2px);
        }

        .contact-info {
            font-size: 1rem;
            font-weight: 600;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            padding: 12px 22px;
            border-radius: 30px;
            border: 1px solid var(--glass-border);
        }

        .contact-info i {
            color: #4ade80;
            animation: pulse-phone 1.5s infinite;
        }

        /* --- PORTADA PRINCIPAL (HERO) --- */
        .hero-section {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 0 20px;
            padding-top: 80px;
        }

        .hero-logo-container {
            margin-bottom: 20px;
            animation: bounce 3s infinite;
        }

        /* Logo central grande */
        .hero-logo-img {
            height: 120px;
            width: 120px; /* Proporciones 1:1 para el círculo */
            border: 4px solid rgba(255, 255, 255, 0.3);
            filter: drop-shadow(0px 4px 12px rgba(0,0,0,0.3));
        }

        .hero-section h1 {
            font-size: 3.5rem;
            font-weight: 800;
            max-width: 900px;
            line-height: 1.2;
            text-shadow: 0 4px 12px rgba(0,0,0,0.4);
            margin-bottom: 20px;
        }

        .hero-section p {
            font-size: 1.3rem;
            color: #f0fdf4;
            max-width: 700px;
            margin-bottom: 40px;
            text-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }

        /* Tarjetas de animales */
        .animal-container {
            display: flex;
            gap: 30px;
        }

        .animal-card {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            padding: 20px;
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            width: 130px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }

        .animal-card i { font-size: 2.5rem; color: white; }
        .animal-card span { font-weight: 600; font-size: 0.95rem; }

        .dog-card { animation: float-slow 4s infinite ease-in-out; }
        .cat-card { animation: float-delayed 4.5s infinite ease-in-out; }
        .dove-card { animation: float-slow 5s infinite ease-in-out; }
        .dog-card i { animation: wag 1.2s infinite ease-in-out; display: inline-block; transform-origin: bottom center; }

        /* --- SECCIÓN DE SERVICIOS (SCROLLABLE) --- */
        .services-section {
            padding: 100px 5% 80px 5%;
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-top: 1px solid var(--glass-border);
        }

        .services-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .services-header h2 {
            font-size: 2.8rem;
            font-weight: 800;
            margin-bottom: 15px;
            color: white;
        }

        .services-header p {
            font-size: 1.15rem;
            color: #cbd5e1;
            max-width: 600px;
            margin: 0 auto;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .service-box {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            padding: 35px 30px;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .service-box:hover {
            transform: translateY(-8px);
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(255, 255, 255, 0.25);
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        }

        .service-icon {
            width: 60px;
            height: 60px;
            background: rgba(34, 197, 94, 0.2);
            border-radius: 16px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.8rem;
            color: #4ade80;
            margin-bottom: 5px;
        }

        .service-box h3 {
            font-size: 1.4rem;
            font-weight: 700;
            color: white;
        }

        .service-box p {
            font-size: 1rem;
            color: #94a3b8;
            line-height: 1.6;
        }

        /* --- KEYFRAMES ANIMACIONES --- */
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        @keyframes float-slow {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-10px) rotate(2deg); }
        }
        @keyframes float-delayed {
            0%, 100% { transform: translateY(-8px) rotate(-1deg); }
            50% { transform: translateY(4px) rotate(1deg); }
        }
        @keyframes wag {
            0%, 100% { transform: rotate(-6deg); }
            50% { transform: rotate(8deg); }
        }
        @keyframes pulse-phone {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.08); }
        }

        @media (max-width: 992px) {
            .navbar { flex-direction: column; gap: 15px; padding-top: 20px; }
            .nav-actions { flex-direction: column; gap: 10px; width: 100%; }
            .login-btn, .contact-info { width: 100%; justify-content: center; }
            .hero-section h1 { font-size: 2.3rem; }
            .animal-container { flex-wrap: wrap; justify-content: center; }
        }
    </style>
</head>
<body>

    <div class="bg-slider">
        <div class="slide active" style="background-image: url('https://images.unsplash.com/photo-1628009368231-7bb7cfcb0def?q=80&w=1920')"></div>
        <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?q=80&w=1920')"></div>
        <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1614880353165-e56fac2ea9a8?q=80&w=1920')"></div>
        <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1612531386530-97286d97c2d2?q=80&w=1920')"></div>
        <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1596492784531-6e6eb5ea9993?q=80&w=1920')"></div>
        <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1599443015574-be5fe8a0f491?q=80&w=1920')"></div>
        <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1629909613654-28e377c37b09?q=80&w=1920')"></div>
        <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?q=80&w=1920')"></div>
    </div>

    <div class="slider-overlay"></div>

    <header class="navbar">
        <a href="#" class="nav-brand">
            <img src="img/logo.png" alt="VetVital" class="nav-logo-img logo-circular" onerror="this.src='https://cdn-icons-png.flaticon.com/512/616/616408.png';">
            <span>VetVital</span>
        </a>
        
        <div class="nav-actions">
            <div class="contact-info">
                <i class="fa-solid fa-phone"></i> Urgencias: +57 300 123 4567
            </div>
            <a href="login" class="login-btn">
                <i class="fa-solid fa-right-from-bracket"></i> Iniciar Sesión
            </a>
        </div>
    </header>

    <section class="hero-section">
        <div class="hero-logo-container">
            <img src="img/logo.png" alt="Logo grande" class="hero-logo-img logo-circular" onerror="this.src='https://cdn-icons-png.flaticon.com/512/616/616408.png';">
        </div>
        
        <h1>Bienvenido al Centro Clínico VetVital</h1>
        <p>Cuidado profesional, tecnología avanzada y amor por nuestros mejores amigos en un solo lugar.</p>

        <div class="animal-container">
            <div class="animal-card dog-card">
                <i class="fa-solid fa-dog"></i>
                <span>Caninos</span>
            </div>
            <div class="animal-card cat-card">
                <i class="fa-solid fa-cat"></i>
                <span>Felinos</span>
            </div>
            <div class="animal-card dove-card">
                <i class="fa-solid fa-dove"></i>
                <span>Exóticos</span>
            </div>
        </div>
    </section>

    <section class="services-section">
        <div class="services-header">
            <h2>Servicios Vetcare Destacados</h2>
            <p>Atención integral diseñada detalladamente para garantizar la salud y bienestar de cada miembro de tu familia.</p>
        </div>

        <div class="services-grid">
            <div class="service-box">
                <div class="service-icon"><i class="fa-solid fa-user-doctor"></i></div>
                <h3>Orientación médica veterinaria</h3>
                <p>Personal calificado disponible siempre para ti, guiándote en los primeros auxilios y cuidados iniciales de tu mascota.</p>
            </div>

            <div class="service-box">
                <div class="service-icon"><i class="fa-solid fa-house-medical"></i></div>
                <h3>Asistencias virtuales y en casa</h3>
                <p>Tu peludo debe estar listo para cualquier evento. Llevamos la atención médica profesional hasta la comodidad de tu hogar.</p>
            </div>

            <div class="service-box">
                <div class="service-icon"><i class="fa-solid fa-video"></i></div>
                <h3>Consulta Veterinaria ilimitada</h3>
                <p>Asesorías virtuales completas sin restricciones de agenda para resolver dudas de comportamiento, nutrición o síntomas primarios.</p>
            </div>

            <div class="service-box">
                <div class="service-icon"><i class="fa-solid fa-shield-dog"></i></div>
                <h3>Dos desparasitaciones internas</h3>
                <p>Control preventivo anual contra parásitos comunes para mantener fuerte el sistema inmunológico de tu compañero de vida.</p>
            </div>

            <div class="service-box">
                <div class="service-icon"><i class="fa-solid fa-microscope"></i></div>
                <h3>Cuadro hemático</h3>
                <p>Análisis integral de laboratorio clínico incluido al año para el monitoreo preventivo y detección temprana de patologías.</p>
            </div>

            <div class="service-box">
                <div class="service-icon"><i class="fa-solid fa-stethoscope"></i></div>
                <h3>Dos consultas presenciales al año</h3>
                <p>Revisiones físicas exhaustivas en nuestras instalaciones para un seguimiento detallado del desarrollo de tus mascotas.</p>
            </div>
        </div>
    </section>

    <script>
        const slides = document.querySelectorAll('.slide');
        let currentSlide = 0;
        const slideInterval = 5000;

        function nextSlide() {
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.add('active');
        }

        setInterval(nextSlide, slideInterval);
    </script>

</body>
</html>