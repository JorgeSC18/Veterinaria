<?php
session_start();
include 'config.php';
include 'conexion.php';

// 🔍 1. CAPTURAR EL CORREO (Primero buscamos en la URL, si no, en la sesión)
$correo = isset($_GET['correo']) ? trim($_GET['correo']) : '';

if (empty($correo) && isset($_SESSION['correo_recuperar'])) {
    $correo = $_SESSION['correo_recuperar'];
}

// 🛡️ 2. SEGURIDAD: Si REALMENTE no hay ningún correo en proceso, ahí sí lo mandamos al Login
if (empty($correo)) {
    header("Location: login");
    exit;
}

// 🔐 3. PROCESAR EL CÓDIGO CUANDO EL USUARIO LE DE A "VERIFICAR"
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo_ingresado = trim($_POST['codigo']);

    // Buscamos en la BD si el código coincide y si aún no ha expirado (NOW())
    $stmt = $conn->prepare("SELECT * FROM usuario WHERE correo = ? AND codigo_recuperacion = ? AND codigo_expiracion > NOW()");
    $stmt->bind_param("ss", $correo, $codigo_ingresado);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        // ¡Código correcto! Creamos la sesión de autorización para el siguiente paso
        $_SESSION['correo_verificado'] = $correo;
        
        echo "<script>
            window.location.href = 'restablecer.php';
        </script>";
        exit;
    } else {
        echo "<script>alert('El código es incorrecto o ya ha expirado. Revisa tu Mailtrap.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Código - VetVital</title>
    <link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            margin: 0; font-family: 'Segoe UI', sans-serif;
            background-color: #f8fafc;
            display: flex; justify-content: center; align-items: center; height: 100vh;
        }
        .container {
            background: #ffffff; padding: 40px; border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            width: 100%; max-width: 400px; box-sizing: border-box; text-align: center;
        }
        h3 { color: #1e293b; margin-bottom: 8px; font-size: 1.6rem; }
        p { color: #64748b; font-size: 0.95rem; margin-bottom: 25px; }
        .input-box {
            width: 100%; padding: 14px; font-size: 1.2rem; letter-spacing: 4px; text-align: center;
            border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; box-sizing: border-box;
            font-weight: bold; color: #1e293b;
        }
        .input-box:focus { outline: none; border-color: #115e59; background: #fff; }
        .btn {
            width: 100%; padding: 14px; background: #115e59; color: #fff;
            border: none; border-radius: 8px; font-size: 1rem; font-weight: 600;
            cursor: pointer; transition: background 0.2s; margin-top: 20px;
        }
        .btn:hover { background: #0f4c48; }
        .back-link { display: block; margin-top: 20px; color: #64748b; text-decoration: none; font-size: 0.9rem; }
        .back-link:hover { color: #115e59; }
    </style>
</head>
<body>

    <div class="container">
        <div style="font-size: 3rem; color: #115e59; margin-bottom: 15px;">
            <i class="fa-solid fa-shield-envelope"></i>
        </div>
        <h3>Introduce el código</h3>
        <p>Hemos enviado un código de 6 dígitos a tu bandeja de Mailtrap para la cuenta:<br><strong><?php echo htmlspecialchars($correo); ?></strong></p>

        <form action="verificar_codigo?correo=<?php echo urlencode($correo); ?>" method="POST">
            <input type="text" name="codigo" class="input-box" placeholder="000000" required maxlength="6" autocomplete="off">
            <button type="submit" class="btn">Verificar Código</button>
        </form>

        <a href="login" class="back-link"><i class="fa-solid fa-arrow-left"></i> Volver al inicio de sesión</a>
    </div>

</body>
</html>