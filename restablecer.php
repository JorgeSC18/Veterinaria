<?php
session_start();
include 'config.php';
include 'conexion.php'; 

// Variable para controlar si mostramos el formulario o el cuadro de éxito
$cambio_exitoso = false;
$correo_usuario = isset($_SESSION['correo_verificado']) ? $_SESSION['correo_verificado'] : '';

// 🛡️ Seguridad: Si no ha pasado por el código de 6 dígitos, lo expulsamos (a menos que ya haya cambiado la clave con éxito)
if (!isset($_SESSION['correo_verificado']) && !isset($_GET['completado'])) {
    header("Location: login");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nueva_contrasena = trim($_POST['contrasena']);
    $confirmar_contrasena = trim($_POST['confirmar_contrasena']);

    if ($nueva_contrasena === $confirmar_contrasena) {
        
        // Encriptar la nueva contraseña
        $password_hash = password_hash($nueva_contrasena, PASSWORD_BCRYPT);

        // Guardar en la BD usando tu columna 'contraseña'
        $stmt = $conn->prepare("UPDATE usuario SET contraseña = ?, codigo_recuperacion = NULL, codigo_expiracion = NULL WHERE correo = ?");
        $stmt->bind_param("ss", $password_hash, $correo_usuario);

        if ($stmt->execute()) {
            // Destruimos la sesión de seguridad porque el proceso ya terminó con éxito
            unset($_SESSION['correo_verificado']);
            $cambio_exitoso = true;
        } else {
            echo "<script>alert('Hubo un error en la base de datos al actualizar la contraseña.');</script>";
        }
    } else {
        echo "<script>alert('Las contraseñas introducidas no coinciden.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña - VetVital</title>
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
        p { color: #64748b; font-size: 0.95rem; margin-bottom: 25px; line-height: 1.5; }
        .form-group { text-align: left; margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #334155; font-size: 0.9rem; font-weight: 500; }
        .input-box {
            width: 100%; padding: 12px; font-size: 1rem;
            border: 1px solid #e2e8f0; border-radius: 8px;
            background: #f8fafc; box-sizing: border-box;
        }
        .input-box:focus { outline: none; border-color: #115e59; background: #fff; }
        .btn {
            width: 100%; padding: 14px; background: #115e59; color: #fff;
            border: none; border-radius: 8px; font-size: 1rem; font-weight: 600;
            cursor: pointer; transition: background 0.2s; margin-top: 10px;
            display: inline-block; text-decoration: none; box-sizing: border-box;
        }
        .btn:hover { background: #0f4c48; }
        
        /* Estilos específicos para la vista de éxito */
        .success-icon { font-size: 4rem; color: #10b981; margin-bottom: 20px; animation: scaleUp 0.3s ease-in-out; }
        @keyframes scaleUp {
            0% { transform: scale(0.7); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>
</head>
<body>

    <div class="container">
        
        <?php if ($cambio_exitoso): ?>
            <div class="success-icon">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <h3>¡Contraseña Cambiada!</h3>
            <p>Tu clave de acceso ha sido actualizada con éxito en el sistema. Ya puedes iniciar sesión con tus nuevas credenciales.</p>
            
            <a href="login" class="btn">Ir al inicio de sesión</a>

        <?php else: ?>
            <div style="font-size: 3rem; color: #115e59; margin-bottom: 15px;">
                <i class="fa-solid fa-lock-open"></i>
            </div>
            <h3>Restablecer Contraseña</h3>
            <p>Crea una nueva contraseña segura para tu cuenta asociada a:<br><strong><?php echo htmlspecialchars($correo_usuario); ?></strong></p>

            <form action="restablecer" method="POST">
                <div class="form-group">
                    <label>Nueva Contraseña</label>
                    <input type="password" name="contrasena" class="input-box" placeholder="Mínimo 6 caracteres" required minlength="6">
                </div>

                <div class="form-group">
                    <label>Confirmar Nueva Contraseña</label>
                    <input type="password" name="confirmar_contrasena" class="input-box" placeholder="Repite tu contraseña" required minlength="6">
                </div>
                
                <button type="submit" class="btn">Actualizar Contraseña</button>
            </form>
        <?php endif; ?>

    </div>

</body>
</html>