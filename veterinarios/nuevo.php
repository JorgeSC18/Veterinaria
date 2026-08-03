<?php
session_start();

// 1. Control de acceso
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login");
    exit();
}

include("../conexion.php");

$error = "";

// 2. Escuchar el envío del formulario por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // --- Datos para la tabla USUARIO ---
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $contrasena_plana = trim($_POST['contraseña']);
    $contrasena_encriptada = password_hash($contrasena_plana, PASSWORD_DEFAULT);
    $rol = 'veterinario'; // Rol fijo para este módulo de salud

    // --- Datos para la tabla VETERINARIO ---
    $especialidad = trim($_POST['especialidad']);
    $telefono = trim($_POST['telefono']);

    // Validar que los campos obligatorios del acceso estén listos
    if (!empty($nombre) && !empty($correo) && !empty($contrasena_plana)) {
        
        // INICIAMOS TRANSACCIÓN: Se guardan ambas tablas o ninguna
        $conn->begin_transaction();

        try {
            // PASO A: Insertar el perfil general de accesos en USUARIO
            $sql_user = "INSERT INTO USUARIO (nombre, correo, contraseña, rol) VALUES (?, ?, ?, ?)";
            $stmt_user = $conn->prepare($sql_user);
            $stmt_user->bind_param("ssss", $nombre, $correo, $contrasena_encriptada, $rol);
            $stmt_user->execute();
            
            // Capturamos el ID del usuario recién creado
            $id_usuario_nuevo = $conn->insert_id;
            $stmt_user->close();

            // PASO B: Insertar la especialidad médica usando el ID que acabamos de capturar
            // Revisa si en tu base de datos pusiste USUARIO_id_usuario con mayúsculas/minúsculas exactas
            $sql_vet = "INSERT INTO VETERINARIO (especialidad, telefono, USUARIO_id_usuario) VALUES (?, ?, ?)";
            $stmt_vet = $conn->prepare($sql_vet);
            $stmt_vet->bind_param("ssi", $especialidad, $telefono, $id_usuario_nuevo);
            $stmt_vet->execute();
            $stmt_vet->close();

            // Si llegamos aquí sin errores, confirmamos todos los inserts en la BD
            $conn->commit();

            // Redirigimos al index del CRUD de veterinarios
            header("Location: index?status=created");
            exit();

        } catch (Exception $e) {
            // Si algo sale mal (ej. correo duplicado), cancelamos todo para no dejar datos huérfanos
            $conn->rollback();
            $error = "Error al registrar el médico veterinario: " . $e->getMessage();
        }
    } else {
        $error = "Por favor, completa todos los campos de acceso obligatorios.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Veterinario - VetVital</title>
    <link rel="shortcut icon" type="image/x-icon" href="../img/favicon.ico">
    <link rel="stylesheet" href="../estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-logo">
            <i class="fa-solid fa-paw"></i> VetVital
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li><a href="../dashboard_admin"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
                <li><a href="../clientes/index"><i class="fa-solid fa-users"></i> Clientes</a></li>
                <li><a href="../mascotas/index"><i class="fa-solid fa-dog"></i> Mascotas</a></li>
                <li><a href="index" class="active"><i class="fa-solid fa-user-doctor"></i> Veterinarios</a></li>
                <li><a href="../citas/index"><i class="fa-solid fa-calendar-days"></i> Citas</a></li>
                <li><a href="../historial_medico/index"><i class="fa-solid fa-file-medical"></i> Historial Médico</a></li>
                <li><a href="../reportes/index"><i class="fa-solid fa-chart-line"></i> Reportes</a></li>
            </ul>
        </nav>
        <div class="sidebar-user">
            <div class="user-info">
                <p class="user-name"><?php echo htmlspecialchars($_SESSION['nombre']); ?></p>
                <p class="user-role">Rol: <?php echo htmlspecialchars($_SESSION['rol']); ?></p>
            </div>
            <a href="../logout" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión</a>
        </div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <h1>Agregar Nuevo Médico Veterinario</h1>
        </header>
        
        <div class="content-container">
            <div class="table-card" style="padding: 25px; max-width: 700px; margin: 0 auto;">
                
                <?php if(!empty($error)): ?>
                    <div style="margin-bottom: 20px; padding: 15px; background: #fee2e2; color: #991b1b; border-radius: 6px; font-weight: bold;">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form action="nuevo" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
                    
                    <div>
                        <h3 style="color: #115e59; margin-bottom: 10px;"><i class="fa-solid fa-key"></i> Credenciales de Acceso</h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div>
                                <label style="display:block; margin-bottom:5px; font-weight:bold;">Nombre Completo *</label>
                                <input type="text" name="nombre" placeholder="Dr/a. Nombre Apellido" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:6px;" required>
                            </div>
                            <div>
                                <label style="display:block; margin-bottom:5px; font-weight:bold;">Correo Electrónico *</label>
                                <input type="email" name="correo" placeholder="medico@vet.com" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:6px;" required>
                            </div>
                        </div>
                        <div style="margin-top: 15px;">
                            <label style="display:block; margin-bottom:5px; font-weight:bold;">Contraseña del Sistema *</label>
                            <input type="password" name="contraseña" placeholder="Asigna una contraseña segura" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:6px;" required>
                        </div>
                    </div>

                    <hr style="border: 0; border-top: 1px solid #e2e8f0;">

                    <div>
                        <h3 style="color: #115e59; margin-bottom: 10px;"><i class="fa-solid fa-user-tie"></i> Perfil Profesional</h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div>
                                <label style="display:block; margin-bottom:5px; font-weight:bold;">Especialidad Médica</label>
                                <input type="text" name="especialidad" placeholder="Ej. Cirugía, Caninos, Felinos" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:6px;">
                            </div>
                            <div>
                                <label style="display:block; margin-bottom:5px; font-weight:bold;">Teléfono / Extensión</label>
                                <input type="text" name="telefono" placeholder="Ej. Ext 102 o Celular" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:6px;">
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 15px; display: flex; gap: 10px;">
                        <button type="submit" style="background: #115e59; color: white; border: none; padding: 12px 25px; border-radius: 6px; cursor: pointer; font-weight: bold;">
                            <i class="fa-solid fa-floppy-disk"></i> Guardar Veterinario
                        </button>
                        <a href="index" style="background: #e2e8f0; color: #334155; text-decoration: none; padding: 12px 25px; border-radius: 6px; font-weight: bold; text-align: center;">
                            Cancelar
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </main>

</body>
</html>