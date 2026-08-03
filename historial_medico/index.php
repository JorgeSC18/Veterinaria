<?php

session_start();

if(!isset($_SESSION['id_usuario'])) {
    header("Location: ../login");
    exit();
}

include("../conexion.php");

$sql = "SELECT 
            HISTORIAL_MEDICO.*,
            MASCOTA.nombre AS nombre_mascota
        FROM HISTORIAL_MEDICO
        INNER JOIN MASCOTA
            ON HISTORIAL_MEDICO.MASCOTA_id_mascota = MASCOTA.id_mascota";

$resultado = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial Médico - VetVital</title>
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
                    <a href="../citas/index">
                        <i class="fa-solid fa-calendar-days"></i> Citas
                    </a>
                </li>
                <li>
                    <a href="index" class="active">
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
                <p class="user-name"><?php echo $_SESSION['nombre']; ?></p>
                <p class="user-role">Rol: <?php echo $_SESSION['rol']; ?></p>
            </div>
            <a href="../logout" class="logout-btn">
                <i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión
            </a>
        </div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <h1>Historial Clínico</h1>
            <div class="topbar-welcome">
                Administrador <i class="fa-solid fa-user-shield"></i>
            </div>
        </header>
        
        <div class="content-container">
            
            <div class="table-header-container">
                <h2>Registros Médicos</h2>
                <a href="nuevo" class="btn btn-add">
                    <i class="fa-solid fa-plus"></i> Nuevo Historial Médico
                </a>
            </div>

            <div class="table-card">
                <table class="main-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Mascota</th>
                            <th>Fecha</th>
                            <th>Diagnóstico</th>
                            <th>Tratamiento</th>
                            <th>Observaciones</th>
                            <th style="text-align: center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($fila = $resultado->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $fila['id_historial']; ?></td>
                                <td>
                                    <span class="badge badge-dog">
                                        <i class="fa-solid fa-shield-dog" style="margin-right: 4px;"></i> 
                                        <?php echo $fila['nombre_mascota']; ?>
                                    </span>
                                </td>
                                <td>
                                    <i class="fa-regular fa-calendar-check" style="color: var(--primary-color); margin-right: 4px;"></i>
                                    <strong><?php echo $fila['fecha']; ?></strong>
                                </td>
                                <td><span style="font-weight: 500; color: var(--text-color);"><?php echo $fila['diagnostico']; ?></span></td>
                                <td><em class="text-muted"><?php echo $fila['tratamiento']; ?></em></td>
                                <td><?php echo !empty($fila['observaciones']) ? $fila['observaciones'] : '<span class="text-muted">- Sin notas -</span>'; ?></td>
                                <td style="text-align: center;">
                                    <div style="display: flex; justify-content: center; gap: 8px;">
                                        <a href="editar?id=<?php echo $fila['id_historial']; ?>" class="btn btn-action btn-edit">
                                            <i class="fa-solid fa-pen"></i> Editar
                                        </a>
                                        <a href="eliminar?id=<?php echo $fila['id_historial']; ?>" class="btn btn-action btn-delete" onclick="return confirm('¿Estás seguro de que deseas eliminar este registro del historial clínico? Esta acción no se puede deshacer.');">
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