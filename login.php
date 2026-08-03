<?php
session_start();
#1justo aqui se pone: echo password_hash, contraseña creada normal en mysql model o phpmyadmin 123456.
#2introducimos esto: echo password_hash("123456", PASSWORD_DEFAULT); exit; y automaticamente nos da la contrasena hash en la pagina, copias la contrasena y hash y lo pegas en la base datos msql model o phpmyadmin, despues elimanas todo el codigo puesto echo..
#3 vuelve a recargar la pagina igresas normalmente tu contrasena 123456 y te dejará ingresar a la pagina.
include 'conexion.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    #Ya no necesitamos mysqli_real_escape_string con consultas preparadas
    $usuario_input = $_POST['usuario_input'];
    $contrasena = $_POST['contrasena'];

    if (!empty($usuario_input) && !empty($contrasena)) {
        
        # Cambiaremos las variables directas por signos de interrogación '?'
        $sql = "SELECT usr.*, cli.documento 
                FROM USUARIO usr 
                LEFT JOIN CLIENTE cli ON usr.id_usuario = cli.USUARIO_id_usuario 
                WHERE usr.correo = ? OR cli.documento = ? 
                LIMIT 1";
        
        # preparamos la consulta en el servidor
        #en vez de $resultado = $conn->query($sql): que se le cambia asi:
        $stmt = $conn->prepare($sql);

        #Vinculamos los datosde forma segura.
        #Usamos "ss" porque pasamos dos cadenas de texto (para correo y para documento)
        $stmt->bind_param("ss", $usuario_input, $usuario_input); 

        #Ejecutamos y obtenemos el resultado
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado && $resultado->num_rows > 0) {
            $usuario = $resultado->fetch_assoc();

        #En lugar de '===', usumos password_verify para validar el hash encriptado    
            if (password_verify($contrasena, $usuario['contraseña'])) {

                $_SESSION['id_usuario'] = $usuario['id_usuario'];
                $_SESSION['nombre'] = $usuario['nombre'];
                $_SESSION['rol'] = strtolower($usuario['rol']);

                switch ($_SESSION['rol']) {
                    case 'admin':
                        header("Location: dashboard_admin");
                        break;
                    case 'veterinario':
                        header("Location: dashboard_veterinario");
                        break;
                    case 'cliente':
                        header("Location: dashboard_cliente");
                        break;                               
                    default:
                        $error = "El rol asignado no cuenta con un panel autorizado.";
                        break;
                }
                exit();
            } else {
                $error = "La contraseña ingresada es incorrecta.";
            }
        } else {
            $error = "Las credenciales ingresadas no corresponden a ningún usuario.";
        }

        $stmt->close();

    } else {
        $error = "Por favor, rellene todos los campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - VetVital</title>

    <link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --brand-green: #115e59; 
            --brand-green-hover: #0f4c48;
            --bg-light: #f8fafc;
            --text-dark: #1e293b;
            --text-muted: #64748b;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-light);
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* --- CONTENEDOR IZQUIERDO CON SLIDER --- */
        .login-sidebar {
            flex: 1;
            background-color: var(--brand-green);
            color: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 60px;
            position: relative;
            overflow: hidden;
            box-sizing: border-box;
        }

        /* Forzamos al Logo y a los Dots a estar siempre arriba del Slider */
        .sidebar-brand, .sidebar-footer-dots {
            position: relative;
            z-index: 10;
        }

        /* 🟢 NUEVO: Contenedor del enlace del logo */
        .brand-link {
            display: flex;
            align-items: center;
            gap: 14px;
            text-decoration: none; /* Quita el subrayado azul por defecto */
            color: #ffffff;
            transition: opacity 0.2s ease;
        }

        .brand-link:hover {
            opacity: 0.9; /* Efecto visual sutil al pasar el mouse */
        }

        /* 🟢 NUEVO: Forzado a círculo perfecto para tu logo */
        .circular-logo {
            width: 50px;
            height: 50px;
            border-radius: 50%; /* Lo hace redondo */
            object-fit: cover;   /* Evita que la imagen se deforme o estire */
            background-color: #ffffff; /* Fondo blanco interno por si el logo tiene transparencias */
            border: 2px solid rgba(255, 255, 255, 0.3); /* Borde sutil elegante */
        }

        /* 🟢 NUEVO: Agrupador del texto al lado del logo */
        .brand-text-wrapper {
            display: flex;
            flex-direction: column;
        }

        .sidebar-brand .logo {
            font-size: 1.8rem;
            font-weight: bold;
            line-height: 1.1; /* Ajuste de línea para que quede simétrico con el logo */
        }

        .sidebar-brand p {
            margin: 4px 0 0 0;
            font-size: 0.9rem;
            opacity: 0.7;
        }

        /* Contenedor maestro de las diapositivas */
        .slider-wrapper {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        /* Cada diapositiva individual */
        .slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 1.2s ease-in-out; /* Efecto Fade suave */
            background-size: cover;
            background-position: center;
            display: flex;
            flex-direction: column;
            justify-content: flex-end; /* Empuja el texto hacia abajo */
            padding: 60px;
            box-sizing: border-box;
        }

        /* Capa oscura/verde translúcida sobre la imagen para legibilidad */
        .slide::before {
            content: "";
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(135deg, rgba(17, 94, 89, 0.92) 0%, rgba(11, 45, 43, 0.85) 100%);
            z-index: 2;
        }

        /* Clase activa controlada por JavaScript */
        .slide.active {
            opacity: 1;
        }

        .slide-content {
            position: relative;
            z-index: 3;
            max-width: 500px;
            margin-bottom: 40px; /* Despegado de los botones inferiores */
        }

        .slide-content h2 {
            font-size: 1.75rem;
            font-weight: 500;
            line-height: 1.4;
            margin-bottom: 20px;
        }

        .author-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .author-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #ffffff33;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .author-text p {
            margin: 0;
            font-size: 0.95rem;
        }

        .author-text span {
            font-size: 0.8rem;
            opacity: 0.7;
        }

        /* Indicadores inferiores (Puntitos) */
        .sidebar-footer-dots {
            display: flex;
            gap: 8px;
        }

        .dot {
            width: 24px;
            height: 4px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 2px;
            transition: background-color 0.3s;
            cursor: pointer;
        }

        .dot.active {
            background: #ffffff;
        }

        /* --- PANEL DERECHO (FORMULARIO) --- */
        .login-content {
            flex: 1;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 40px;
            align-items: center;
        }

        .form-wrapper {
            margin: auto;
            width: 100%;
            max-width: 380px;
        }

        .form-wrapper h3 {
            font-size: 1.8rem;
            color: var(--text-dark);
            margin: 0 0 8px 0;
        }

        .form-wrapper .subtitle {
            color: var(--text-muted);
            margin: 0 0 30px 0;
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .input-container input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            border-radius: 8px;
            font-size: 0.95rem;
            box-sizing: border-box;
            transition: all 0.2s;
        }

        .input-container input:focus {
            outline: none;
            border-color: var(--brand-green);
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(17, 94, 89, 0.1);
        }

        .password-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .forgot-link {
            font-size: 0.85rem;
            color: var(--brand-green);
            text-decoration: none;
            font-weight: 500;
        }

        .options-row {
            display: flex;
            align-items: center;
            margin-top: 15px;
            margin-bottom: 25px;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            color: var(--text-dark);
            cursor: pointer;
        }

        .checkbox-label input {
            accent-color: var(--brand-green);
            width: 16px;
            height: 16px;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background-color: var(--brand-green);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn-submit:hover {
            background-color: var(--brand-green-hover);
        }

        .footer-credits {
            font-size: 0.8rem;
            color: var(--text-muted);
            text-align: center;
        }

        .alert-error {
            background-color: #fff5f5;
            color: #c53030;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            border: 1px solid #fed7d7;
        }

        @media (max-width: 900px) {
            .login-sidebar {
                display: none;
            }
        }
    </style>
</head>
<body>

    <div class="login-sidebar">
        <div class="sidebar-brand">
            <a href="./" class="brand-link">
                <img src="img/logo.png" alt="Logo VetVital" class="circular-logo" onerror="this.src='https://cdn-icons-png.flaticon.com/512/616/616408.png';">
                <div class="brand-text-wrapper">
                    <div class="logo">VetVital</div>
                    <p>Centro Clínico Veterinario</p>
                </div>
            </a>
        </div>

        <div class="slider-wrapper">
            <div class="slide active" style="background-image: url('https://images.unsplash.com/photo-1581888227599-779811939961?q=80&w=1200');">
                <div class="slide-content">
                    <h2>"Cuidando a tus mascotas con el amor y la profesionalidad que merecen."</h2>
                </div>
            </div>

            <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1543466835-00a7907e9de1?q=80&w=1200');">
                <div class="slide-content">
                    <h2>"Comprometidos al cuidado de la mascota las 24 horas del día, los 7 días de la semana."</h2>
                </div>
            </div>

            <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?q=80&w=1200');">
                <div class="slide-content">
                    <h2>"Tecnología médica avanzada y un equipo humano enfocado en su bienestar integral."</h2>
                </div>
            </div>
        </div>

        <div class="sidebar-footer-dots">
            <div class="dot active"></div>
            <div class="dot"></div>
            <div class="dot"></div>
        </div>
    </div>

    <div class="login-content">
        <div class="form-wrapper">
            <h3>Bienvenido de vuelta</h3>
            <p class="subtitle">Ingresa tus credenciales para continuar</p>

            <?php if (!empty($error)): ?>
                <div class="alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="login" method="POST">
                <div class="form-group">
                    <label for="usuario_input">Identificación o Correo electrónico</label>
                    <div class="input-container">
                        <input type="text" id="usuario_input" name="usuario_input" placeholder="Correo electrónico o Documento de identidad" required autocomplete="off">
                    </div>
                </div>

                <div class="form-group">
                    <div class="password-header">
                        <label for="contrasena">Contraseña</label>
                        <a href="recuperar" class="forgot-link">¿Olvidaste tu contraseña?</a>
                    </div>
                    <div class="input-container">
                        <input type="password" id="contrasena" name="contrasena" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="options-row">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember"> Recordar sesión
                    </label>
                </div>

                <button type="submit" class="btn-submit">Iniciar sesión</button>
            </form>
        </div>

        <div class="footer-credits">
            VetVital © 2026 — Centro Clínico Veterinario
        </div>
    </div>

    <script>
        const slides = document.querySelectorAll('.slide');
        const dots = document.querySelectorAll('.dot');
        let currentSlide = 0;
        const slideInterval = 5000; // Tiempo en milisegundos (5 segundos por foto)

        function nextSlide() {
            slides[currentSlide].classList.remove('active');
            dots[currentSlide].classList.remove('active');
            
            currentSlide = (currentSlide + 1) % slides.length;
            
            slides[currentSlide].classList.add('active');
            dots[currentSlide].classList.add('active');
        }

        setInterval(nextSlide, slideInterval);
    </script>

</body>
</html>