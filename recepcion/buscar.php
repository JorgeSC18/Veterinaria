<?php

session_start();

// Validamos la sesión y protegemos el rol (Recepcionista o Admin pueden ver esto)
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login");
    exit();
}

if ($_SESSION['rol'] != 'recepcionista' && $_SESSION['rol'] != 'admin') {
    echo "Acceso denegado.";
    exit();
}

include("../conexion.php");

$documento = "";
$cliente = null;
$resultado_citas = null;
$busqueda_realizada = false;

if (isset($_GET['documento']) && !empty(trim($_GET['documento']))) {
    $busqueda_realizada = true;
    $documento = mysqli_real_escape_string($conn, trim($_GET['documento']));

    // 1. Buscamos los datos del cliente uniendo la tabla CLIENTE con USUARIO (donde está el nombre)
    // NOTA: Si en tu base de datos el campo 'documento' está en USUARIO o varía de nombre, puedes ajustarlo aquí.
    $sql_cliente = "SELECT CLIENTE.*, USUARIO.nombre, USUARIO.correo 
                    FROM CLIENTE 
                    INNER JOIN USUARIO ON CLIENTE.USUARIO_id_usuario = USUARIO.id_usuario 
                    WHERE CLIENTE.documento = '$documento'";
    
    $res_cliente = $conn->query($sql_cliente);

    if ($res_cliente && $res_cliente->num_rows > 0) {
        $cliente = $res_cliente->fetch_assoc();
        $id_cliente = $cliente['id_cliente'];

        // 2. Buscamos todas las citas de las mascotas que le pertenecen a este cliente específico
        $sql_citas = "SELECT 
                        CITA.id_cita,
                        CITA.fecha,
                        CITA.hora,
                        CITA.motivo,
                        MASCOTA.nombre AS nombre_mascota,
                        U_VET.nombre AS nombre_veterinario
                     FROM CITA
                     INNER JOIN MASCOTA ON CITA.MASCOTA_id_mascota = MASCOTA.id_mascota
                     INNER JOIN CLIENTE ON MASCOTA.CLIENTE_id_cliente = CLIENTE.id_cliente
                     INNER JOIN VETERINARIO ON CITA.VETERINARIO_id_veterinario = VETERINARIO.id_veterinario
                     INNER JOIN USUARIO AS U_VET ON VETERINARIO.USUARIO_id_usuario = U_VET.id_usuario
                     WHERE CLIENTE.id_cliente = '$id_cliente'
                     ORDER BY CITA.fecha DESC, CITA.hora DESC";

        $resultado_citas = $conn->query($sql_citas);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Clientes y Citas - VetVital</title>
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
                    <a href="buscar" class="active">
                        <i class="fa-solid fa-magnifying-glass"></i> Buscar Clientes y Citas
                    </a>
                </li>
                <li>
                    <a href="registro">
                        <i class="fa-solid fa-user-plus"></i> Registro Unificado
                    </a>
                </li>
                <li>
                    <a href="cita">
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
            <h1>Buscador de Historial de Citas</h1>
            <div class="topbar-welcome">
                Recepción <i class="fa-solid fa-user-clock"></i>
            </div>
        </header>
        
        <div class="content-container">
            
            <!-- TARJETA DEL BUSCADOR -->
            <div class="welcome-card" style="background: #ffffff; border: 1px solid #e2e8f0; color: var(--text-color);">
                <h2 style="color: #115e59; margin-bottom: 10px;">Consulte el estado del Cliente</h2>
                <p class="text-muted" style="margin-bottom: 20px;">Ingrese el número de documento de identidad del propietario para comprobar sus datos y ver la agenda de sus mascotas.</p>
                
                <form action="buscar" method="GET" style="display: flex; gap: 10px; max-width: 500px;">
                    <div style="flex: 1; position: relative;">
                        <i class="fa-solid fa-id-card" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #a0aec0;"></i>
                        <input type="text" name="documento" placeholder="Ej: 102345678" value="<?php echo htmlspecialchars($documento); ?>" required 
                               style="width: 100%; padding: 12px 12px 12px 40px; border: 1px solid #cbd5e0; border-radius: 6px; font-size: 1rem;">
                    </div>
                    <button type="submit" class="btn btn-add" style="margin: 0; padding: 0 25px; height: auto;">
                        <i class="fa-solid fa-magnifying-glass"></i> Buscar
                    </button>
                </form>
            </div>

            <?php if ($busqueda_realizada): ?>
                <?php if ($cliente): ?>
                    
                    <!-- INFORMACIÓN DEL CLIENTE ENCONTRADO -->
                    <div style="background: #f8fafc; border-left: 5px solid #115e59; padding: 20px; border-radius: 8px; margin-top: 30px; margin-bottom: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <h3 style="color: #115e59; margin-bottom: 12px;"><i class="fa-solid fa-user-check"></i> Propietario Localizado</h3>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                            <p><strong>Nombre:</strong> <?php echo htmlspecialchars($cliente['nombre']); ?></p>
                            <p><strong>Documento:</strong> <?php echo htmlspecialchars($cliente['documento']); ?></p>
                            <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($cliente['telefono'] ?? '-'); ?></p>
                            <p><strong>Correo:</strong> <?php echo htmlspecialchars($cliente['correo']); ?></p>
                        </div>
                    </div>

                    <!-- TABLA DE CITAS DEL CLIENTE -->
                    <div class="table-header-container">
                        <h2>Historial de Citas Asociadas</h2>
                    </div>

                    <div class="table-card">
                        <table class="main-table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th>Mascota (Paciente)</th>
                                    <th>Veterinario Asignado</th>
                                    <th>Motivo de la Cita</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($resultado_citas && $resultado_citas->num_rows > 0): ?>
                                    <?php while($cita = $resultado_citas->fetch_assoc()) { ?>
                                        <tr>
                                            <td>
                                                <i class="fa-regular fa-calendar" style="color: var(--primary-color); margin-right: 5px;"></i>
                                                <strong><?php echo htmlspecialchars($cita['fecha']); ?></strong>
                                            </td>
                                            <td>
                                                <i class="fa-regular fa-clock" style="color: var(--secondary-color); margin-right: 5px;"></i>
                                                <?php echo date("H:i", strtotime($cita['hora'])); ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-dog">
                                                    <i class="fa-solid fa-paw"></i> <?php echo htmlspecialchars($cita['nombre_mascota']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <i class="fa-solid fa-user-doctor" style="color: var(--text-muted); margin-right: 5px;"></i>
                                                Dr./a. <?php echo htmlspecialchars($cita['nombre_veterinario']); ?>
                                            </td>
                                            <td><span class="text-muted"><?php echo htmlspecialchars($cita['motivo']); ?></span></td>
                                        </tr>
                                    <?php } ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" style="text-align: center; padding: 25px;" class="text-muted">
                                            <i class="fa-solid fa-calendar-xmark" style="font-size: 1.8rem; display: block; margin-bottom: 8px; color: #cbd5e0;"></i>
                                            Este cliente no tiene citas registradas en el sistema para ninguna de sus mascotas.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                <?php else: ?>
                    <!-- ALERTA SI NO EXISTE EL CLIENTE -->
                    <div style="padding: 15px; background: #fee2e2; color: #991b1b; border-radius: 8px; margin-top: 30px; font-weight: bold; border-left: 5px solid #ef4444;">
                        <i class="fa-solid fa-triangle-exclamation"></i> No se encontró ningún cliente registrado con el documento "<?php echo htmlspecialchars($documento); ?>". Por favor, verifique el número o proceda a registrarlo en la sección de Registro Unificado.
                    </div>
                <?php endif; ?>
            <?php endif; ?>

        </div>
    </main>

</body>
</html>