<?php

session_start();

// Validamos la sesión y protegemos el rol de administrador
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login");
    exit();
}

if ($_SESSION['rol'] != 'admin') {
    echo("Acceso denegado.");
    exit();
}

include("../conexion.php");

date_default_timezone_set('America/Bogota');
$tiempo_limite = date('Y-m-d H:i:s', strtotime('-30 minutes'));
$sql_purga = "DELETE FROM CITA WHERE CONCAT(fecha, ' ', hora) < '$tiempo_limite'";
$conn->query($sql_purga);

$sql = "SELECT 
             CITA.*,
             MASCOTA.nombre AS nombre_mascota,
             USUARIO.nombre AS nombre_veterinario
        FROM CITA
        INNER JOIN MASCOTA
          ON CITA.MASCOTA_id_mascota = MASCOTA.id_mascota
        INNER JOIN VETERINARIO
          ON CITA.VETERINARIO_id_veterinario = VETERINARIO.id_veterinario
        INNER JOIN USUARIO
          ON VETERINARIO.USUARIO_id_usuario = USUARIO.id_usuario";  

$resultado = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citas - VetVital</title>
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
                    <a href="../dashboard_admin">
                        <i class="fa-solid fa-chart-pie"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="../clientes/index">
                        <i class="fa-solid fa-users"></i> Clientes
                    </a>
                </li>
                <li>
                    <a href="../mascotas/index">
                        <i class="fa-solid fa-dog"></i> Mascotas
                    </a>
                </li>
                <li>
                    <a href="../veterinarios/index">
                        <i class="fa-solid fa-user-doctor"></i> Veterinarios
                    </a>
                </li>
                <li>
                    <a href="index" class="active">
                        <i class="fa-solid fa-calendar-days"></i> Citas
                    </a>
                </li>
                <li>
                    <a href="../historial_medico/index">
                        <i class="fa-solid fa-file-medical"></i> Historial Médico
                    </a>
                </li>
                <li>
                    <a href="../reportes/index">
                        <i class="fa-solid fa-chart-line"></i> Reportes
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
            <h1>Gestión de Citas</h1>
            <div class="topbar-welcome">
                Administrador <i class="fa-solid fa-user-shield"></i>
            </div>
        </header>
        
        <div class="content-container">
            
            <div class="table-header-container">
                <h2>Citas Programadas</h2>
                <a href="nuevo" class="btn btn-add">
                    <i class="fa-solid fa-plus"></i> Nueva Cita
                </a>
            </div>

            <div class="table-card">
                <table class="main-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Motivo</th>
                            <th>Mascota</th>
                            <th>Veterinario</th>
                            <th style="text-align: center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($fila = $resultado->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo intval($fila['id_cita']); ?></td>
                                <td>
                                    <i class="fa-regular fa-calendar" style="color: var(--primary-color); margin-right: 5px;"></i> 
                                    <strong><?php echo htmlspecialchars($fila['fecha']); ?></strong>
                                </td>
                                <td>
                                    <i class="fa-regular fa-clock" style="color: var(--secondary-color); margin-right: 5px;"></i> 
                                    <?php echo htmlspecialchars($fila['hora']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($fila['motivo']); ?></td>
                                <td>
                                    <span class="badge badge-other">
                                        <i class="fa-solid fa-paw" style="margin-right: 4px;"></i> 
                                        <?php echo htmlspecialchars($fila['nombre_mascota']); ?>
                                    </span>
                                </td>
                                <td><i class="fa-solid fa-user-doctor" style="color: var(--text-muted); margin-right: 5px;"></i> <?php echo htmlspecialchars($fila['nombre_veterinario']); ?></td>
                                <td style="text-align: center;">
                                    <div style="display: flex; justify-content: center; gap: 8px;">
                                        <a href="editar?id=<?php echo intval($fila['id_cita']); ?>" class="btn btn-action btn-edit">
                                            <i class="fa-solid fa-pen"></i> Editar
                                        </a>
                                        <a href="eliminar?id=<?php echo intval($fila['id_cita']); ?>" class="btn btn-action btn-delete" onclick="return confirm('¿Estás seguro de que deseas cancelar/eliminar esta cita?');">
                                            <i class="fa-solid fa-trash"></i> Eliminar
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

        </div>
    </main>

</body>
</html>