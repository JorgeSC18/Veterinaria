<?php

session_start();

// Validamos la sesión y protegemos el rol de Recepcionista
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login");
    exit();
}

if ($_SESSION['rol'] != 'recepcionista' && $_SESSION['rol'] != 'admin') {
    echo "Acceso denegado. No tienes permisos para este panel.";
    exit();
}

include 'conexion.php';

// --- CONTROL DE VISTAS (Navegación interna) ---
$page = isset($_GET['page']) ? $_GET['page'] : 'inicio';

// --- CONSULTA 1: Total de Clientes ---
$query_clientes = "SELECT COUNT(*) AS total FROM CLIENTE"; 
$res_clientes = mysqli_query($conn, $query_clientes);
$data_clientes = mysqli_fetch_assoc($res_clientes);
$total_clientes = $data_clientes['total'];

// --- CONSULTA 2: Total de Mascotas ---
$query_mascotas = "SELECT COUNT(*) AS total FROM MASCOTA"; 
$res_mascotas = mysqli_query($conn, $query_mascotas);
$data_mascotas = mysqli_fetch_assoc($res_mascotas);
$total_mascotas = $data_mascotas['total'];

// --- CONSULTA 3: Citas para el día de hoy ---
$query_citas = "SELECT COUNT(*) AS total FROM CITA WHERE fecha = CURDATE()"; 
$res_citas = mysqli_query($conn, $query_citas);
if ($res_citas) {
    $data_citas = mysqli_fetch_assoc($res_citas);
    $total_citas = $data_citas['total'];
} 

# Consulta para obtener detalles de las citas de hoy
$query_citas_hoy = "SELECT
    CITA.id_cita,
    CITA.fecha,
    CITA.hora,
    CITA.motivo,
    MASCOTA.nombre AS nombre_mascota,
    USUARIO.nombre AS nombre_veterinario
    FROM CITA
    INNER JOIN MASCOTA ON CITA.MASCOTA_id_mascota = MASCOTA.id_mascota
    INNER JOIN VETERINARIO ON CITA.VETERINARIO_id_veterinario = VETERINARIO.id_veterinario
    INNER JOIN USUARIO ON VETERINARIO.USUARIO_id_usuario = USUARIO.id_usuario
    WHERE CITA.fecha = CURDATE()
    ORDER BY CITA.hora ASC";

$res_citas_hoy = $conn->query($query_citas_hoy);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Recepción - VetVital</title>
    <link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico">
    <link rel="stylesheet" href="estilos.css">
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
                    <a href="dashboard_recepcionista?page=inicio" class="<?php echo $page == 'inicio' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-chart-pie"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="recepcion/buscar">
                        <i class="fa-solid fa-magnifying-glass"></i> Buscar Clientes y Citas
                    </a>
                </li>
                <li>
                    <a href="recepcion/registro">
                        <i class="fa-solid fa-user-plus"></i> Registro Unificado
                    </a>
                </li>
                <li>
                    <a href="recepcion/cita">
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
            <a href="logout" class="logout-btn">
                <i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión
            </a>
        </div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <h1>Panel de Recepción</h1>
            <div class="topbar-welcome">
                ¡Hola, <?php echo htmlspecialchars($_SESSION['nombre']); ?>! <i class="fa-solid fa-user-clock"></i>
            </div>
        </header>
        
        <div class="content-container">

            <?php if ($page == 'inicio'): ?>
                <div class="welcome-card">
                    <h2>Bienvenido/a al Control de Flujo Clínico</h2>
                    <p>Desde este panel puedes buscar el estado de las citas de los clientes, registrar dueños junto a sus mascotas simultáneamente y agendar citas de forma rápida.</p>
                </div>

                <div class="metrics-grid">
                    <div class="stat-card">
                        <div class="stat-icon icon-clientes">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Clientes Totales</h3>
                            <p class="stat-number"><?php echo $total_clientes; ?></p> 
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon icon-mascotas">
                            <i class="fa-solid fa-dog"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Mascotas Registradas</h3>
                            <p class="stat-number"><?php echo $total_mascotas; ?></p> 
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon icon-citas">
                            <i class="fa-solid fa-calendar-check"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Citas para Hoy</h3>
                            <p class="stat-number"><?php echo $total_citas; ?></p> 
                        </div>
                    </div>
                </div> 

                <div class="table-header-container" style="margin-top: 40px; margin-bottom: 15px;">
                    <h2><i class="fa-solid fa-calendar-day" style="color: #115e59; margin-right: 8px;"></i> Citas del Día (Monitoreo de llegada)</h2>
                </div>

                <div class="table-card">
                    <table class="main-table" style="margin-top: 0; width:100%; border-collapse:collapse;">
                        <thead>
                            <tr style="background:#e6f4f2; color:#115e59; text-align:left;">
                                <th style="padding:12px;">Hora</th>
                                <th style="padding:12px;">Mascota (Paciente)</th>
                                <th style="padding:12px;">Médico Veterinario</th>
                                <th style="padding:12px;">Motivo de Consulta</th>
                                <th style="padding:12px; text-align: center;">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if ($res_citas_hoy && $res_citas_hoy->num_rows > 0) {
                                while($cita = $res_citas_hoy->fetch_assoc()) { 
                            ?>
                                <tr style="border-bottom:1px solid #e2e8f0;">
                                    <td style="padding:12px;">
                                        <strong style="color: #115e59; font-size: 1.05rem;">
                                            <i class="fa-regular fa-clock"></i> <?php echo date("H:i", strtotime($cita['hora'])); ?>
                                        </strong>
                                    </td>
                                    <td style="padding:12px;">
                                        <span class="badge badge-dog">
                                            <i class="fa-solid fa-paw"></i> <?php echo htmlspecialchars($cita['nombre_mascota']); ?>
                                        </span>
                                    </td>
                                    <td style="padding:12px;">
                                        <i class="fa-solid fa-user-doctor" style="color: #a0aec0; margin-right: 5px;"></i>
                                        Dr./a. <?php echo htmlspecialchars($cita['nombre_veterinario']); ?>
                                    </td>
                                    <td style="padding:12px;"><span class="text-muted"><?php echo htmlspecialchars($cita['motivo']); ?></span></td>
                                    <td style="padding:12px; text-align: center;">
                                        <span class="badge badge-success">Programada</span>
                                    </td>
                                </tr>
                            <?php 
                                } 
                            } else { 
                            ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 30px;" class="text-muted">
                                        <i class="fa-solid fa-calendar-check" style="font-size: 2rem; color: #cbd5e0; display: block; margin-bottom: 10px;"></i>
                                        No hay citas programadas para el día de hoy.
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>   
            <?php endif; ?>

        </div> 
    </main> 
</body>
</html>