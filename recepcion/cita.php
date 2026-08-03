<?php

session_start();

// Validamos la sesión y protegemos el rol (Recepcionista o Admin)
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login");
    exit();
}

if ($_SESSION['rol'] != 'recepcionista' && $_SESSION['rol'] != 'admin') {
    echo "Acceso denegado.";
    exit();
}

include("../conexion.php");

$mensaje_status = "";
$tipo_alerta = "";

// --- 1. PROCESAR EL FORMULARIO DE AGENDAMIENTO ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_mascota     = mysqli_real_escape_string($conn, $_POST['id_mascota']);
    $id_veterinario = mysqli_real_escape_string($conn, $_POST['id_veterinario']);
    $fecha          = mysqli_real_escape_string($conn, $_POST['fecha']);
    $hora           = mysqli_real_escape_string($conn, $_POST['hora']);
    $motivo         = mysqli_real_escape_string($conn, trim($_POST['motivo']));

    // Validación básica en el backend
    if (!empty($id_mascota) && !empty($id_veterinario) && !empty($fecha) && !empty($hora) && !empty($motivo)) {
        
        // Insertamos la nueva cita en la base de datos
        $sql_insert_cita = "INSERT INTO CITA (fecha, hora, motivo, MASCOTA_id_mascota, VETERINARIO_id_veterinario) 
                            VALUES ('$fecha', '$hora', '$motivo', '$id_mascota', '$id_veterinario')";

        if ($conn->query($sql_insert_cita)) {
            $mensaje_status = "¡Cita agendada correctamente! El paciente ha quedado programado.";
            $tipo_alerta = "success";
        } else {
            $mensaje_status = "Error al guardar la cita en la base de datos: " . $conn->error;
            $tipo_alerta = "error";
        }
    } else {
        $mensaje_status = "Por favor, complete todos los campos obligatorios del formulario.";
        $tipo_alerta = "error";
    }
}

// --- 2. CONSULTAR MASCOTAS PARA EL SELECT ---
// Traemos el nombre de la mascota junto con el nombre del dueño y su documento para una fácil identificación
$query_mascotas = "SELECT 
                    MASCOTA.id_mascota, 
                    MASCOTA.nombre AS nombre_mascota, 
                    USUARIO.nombre AS nombre_dueno, 
                    CLIENTE.documento 
                   FROM MASCOTA 
                   INNER JOIN CLIENTE ON MASCOTA.CLIENTE_id_cliente = CLIENTE.id_cliente 
                   INNER JOIN USUARIO ON CLIENTE.USUARIO_id_usuario = USUARIO.id_usuario 
                   ORDER BY MASCOTA.nombre ASC";
$res_mascotas = $conn->query($query_mascotas);

// --- 3. CONSULTAR VETERINARIOS PARA EL SELECT ---
$query_vets = "SELECT 
                VETERINARIO.id_veterinario, 
                USUARIO.nombre AS nombre_veterinario 
               FROM VETERINARIO 
               INNER JOIN USUARIO ON VETERINARIO.USUARIO_id_usuario = USUARIO.id_usuario 
               ORDER BY USUARIO.nombre ASC";
$res_vets = $conn->query($query_vets);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar Cita Médica - VetVital</title>
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
                <li>
                    <a href="../dashboard_recepcionista">
                        <i class="fa-solid fa-chart-pie"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="buscar">
                        <i class="fa-solid fa-magnifying-glass"></i> Buscar Clientes y Citas
                    </a>
                </li>
                <li>
                    <a href="registro">
                        <i class="fa-solid fa-user-plus"></i> Registro Unificado
                    </a>
                </li>
                <li>
                    <a href="cita" class="active">
                        <i class="fa-solid fa-calendar-plus"></i> Agendar Cita
                    </a>
                </li>
            </ul>
        </nav>

        <div class="sidebar-user">
            <div class="user-info">
                <p class="user-name"><?php echo htmlspecialchars($_SESSION['nombre']); ?></p>
                <p class="user-role">Rol: <?php echo htmlspecialchars($_SESSION['rol']); ?></p>
            </div>
            <a href="../logout" class="logout-btn">
                <i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión
            </a>
        </div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <h1>Asignación de Citas Médicas</h1>
            <div class="topbar-welcome">
                Recepción <i class="fa-solid fa-user-clock"></i>
            </div>
        </header>
        
        <div class="content-container" style="max-width: 800px; margin: 0 auto;">

            <?php if (!empty($mensaje_status)): ?>
                <?php if ($tipo_alerta == 'success'): ?>
                    <div style="padding: 15px; background: #dcfce7; color: #166534; border-radius: 8px; margin-bottom: 25px; font-weight: bold; border-left: 5px solid #16a34a;">
                        <i class="fa-solid fa-circle-check"></i> <?php echo $mensaje_status; ?>
                    </div>
                <?php else: ?>
                    <div style="padding: 15px; background: #fee2e2; color: #991b1b; border-radius: 8px; margin-bottom: 25px; font-weight: bold; border-left: 5px solid #ef4444;">
                        <i class="fa-solid fa-triangle-exclamation"></i> <?php echo $mensaje_status; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="table-card" style="padding: 30px;">
                <h2 style="color: #115e59; margin-bottom: 25px; border-bottom: 2px solid #e2e8f0; padding-bottom: 12px;">
                    <i class="fa-solid fa-calendar-check"></i> Programar Nueva Cita
                </h2>

                <form action="cita" method="POST">
                    
                    <div style="margin-bottom: 20px;">
                        <label style="display:block; margin-bottom:8px; font-weight:600;">Seleccionar Mascota (Paciente) *</label>
                        <select name="id_mascota" required style="width:100%; padding:12px; border:1px solid #cbd5e0; border-radius:6px; background:white; font-size:1rem;">
                            <option value="">-- Busque o seleccione la mascota --</option>
                            <?php 
                            if ($res_mascotas && $res_mascotas->num_rows > 0) {
                                while($row_m = $res_mascotas->fetch_assoc()) {
                                    echo "<option value='". $row_m['id_mascota'] ."'>
                                            🐾 ". htmlspecialchars($row_m['nombre_mascota']) ." [Dueño: ". htmlspecialchars($row_m['nombre_dueno']) ." - Doc: ". $row_m['documento'] ."]
                                          </option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display:block; margin-bottom:8px; font-weight:600;">Médico Veterinario Asignado *</label>
                        <select name="id_veterinario" required style="width:100%; padding:12px; border:1px solid #cbd5e0; border-radius:6px; background:white; font-size:1rem;">
                            <option value="">-- Seleccione el profesional médico --</option>
                            <?php 
                            if ($res_vets && $res_vets->num_rows > 0) {
                                while($row_v = $res_vets->fetch_assoc()) {
                                    echo "<option value='". $row_v['id_veterinario'] ."'>
                                            👨‍⚕️ Dr./a. ". htmlspecialchars($row_v['nombre_veterinario']) ."
                                          </option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                        <div>
                            <label style="display:block; margin-bottom:8px; font-weight:600;">Fecha de la Cita *</label>
                            <input type="date" name="fecha" required min="<?php echo date('Y-m-d'); ?>"
                                   style="width:100%; padding:11px; border:1px solid #cbd5e0; border-radius:6px; font-size:1rem;">
                        </div>
                        <div>
                            <label style="display:block; margin-bottom:8px; font-weight:600;">Hora de la Cita *</label>
                            <input type="time" name="hora" required
                                   style="width:100%; padding:11px; border:1px solid #cbd5e0; border-radius:6px; font-size:1rem;">
                        </div>
                    </div>

                    <div style="margin-bottom: 25px;">
                        <label style="display:block; margin-bottom:8px; font-weight:600;">Motivo de la Consulta *</label>
                        <textarea name="motivo" rows="4" required placeholder="Ej: Control de vacunas, dolor abdominal, limpieza dental, chequeo general..."
                                  style="width:100%; padding:12px; border:1px solid #cbd5e0; border-radius:6px; font-size:1rem; resize: vertical; font-family: inherit;"></textarea>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 15px; border-top: 1px solid #e2e8f0; padding-top: 20px;">
                        <a href="../dashboard_recepcionista" class="btn" style="background: #e2e8f0; color: #475569; padding: 12px 25px; border-radius: 6px; font-weight: bold; text-decoration: none; text-align: center;">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-add" style="margin:0; padding: 12px 35px; font-size: 1rem; border-radius: 6px;">
                            <i class="fa-solid fa-calendar-check"></i> Agendar Cita Éxito
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </main>

</body>
</html>