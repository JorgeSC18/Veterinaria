<?php

session_start();

if(!isset($_SESSION['id_usuario'])) {
    header("Location: ../login");
    exit();
}

if($_SESSION['rol'] != 'admin') {
    echo("Acceso denegado.");
    exit();
}

include("../conexion.php");

#Animales atendidos#

$sql_animales = "
SELECT COUNT(DISTINCT MASCOTA_id_mascota) AS total
FROM CITA";

$animales = $conn->query($sql_animales)->fetch_assoc();

 #Historiales médicos#

$sql_historiales = "
SELECT COUNT(*) AS total
FROM HISTORIAL_MEDICO";

$historiales = $conn->query($sql_historiales)->fetch_assoc();

#Especies registradas#

$sql_especies_registradas = "
SELECT especie, COUNT(*) AS cantidad
FROM MASCOTA
GROUP BY especie";

$especies_registradas = $conn->query($sql_especies_registradas);


 #Especies atendidas#

$sql_especies = "
SELECT MASCOTA.especie, COUNT(DISTINCT MASCOTA.id_mascota) AS cantidad
FROM MASCOTA
INNER JOIN CITA
    ON MASCOTA.id_mascota = CITA.MASCOTA_id_mascota
GROUP BY MASCOTA.especie";

$especies = $conn->query($sql_especies);

#Rendimiento veterinarios#

$sql_veterinarios = "
SELECT
    USUARIO.nombre,
    COUNT(CITA.id_cita) AS total_citas
FROM VETERINARIO
INNER JOIN USUARIO
    ON VETERINARIO.USUARIO_id_usuario = USUARIO.id_usuario
LEFT JOIN CITA
    ON VETERINARIO.id_veterinario = CITA.VETERINARIO_id_veterinario
    GROUP BY VETERINARIO.id_veterinario";

$veterinarios = $conn->query($sql_veterinarios);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes y Estadísticas - VetVital</title>
    <link rel="shortcut icon" type="image/x-icon" href="../img/favicon.ico">
    <link rel="stylesheet" href="../estilos.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .report-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }
        .report-card {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 1px solid #eef2f5;
        }
        .report-card h3 {
            font-size: 1.1rem;
            color: var(--text-color);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 2px solid #f4f6f9;
            padding-bottom: 8px;
        }
    </style>
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
                <li><a href="../veterinarios/index"><i class="fa-solid fa-user-doctor"></i> Veterinarios</a></li>
                <li><a href="../citas/index"><i class="fa-solid fa-calendar-days"></i> Citas</a></li>
                <li><a href="../historial_medico/index"><i class="fa-solid fa-file-medical"></i> Historial Médico</a></li>
                <li><a href="index" class="active"><i class="fa-solid fa-chart-line"></i> Reportes</a>
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
            <h1>Reportes y Estadísticas</h1>
            <div class="topbar-welcome">
                Administrador <i class="fa-solid fa-user-shield"></i>
            </div>
        </header>
        
        <div class="content-container">
            
            <div class="cards-grid">
                <div class="kpi-card">
                    <div class="kpi-icon" style="background-color: rgba(78, 115, 223, 0.1); color: var(--primary-color);">
                        <i class="fa-solid fa-hand-holding-heart"></i>
                    </div>
                    <div class="kpi-info">
                        <h3>Mascotas con Citas</h3>
                        <p class="kpi-number"><?php echo $animales['total']; ?></p>
                    </div>
                </div>

                <div class="kpi-card">
                    <div class="kpi-icon" style="background-color: rgba(28, 200, 138, 0.1); color: var(--success-color);">
                        <i class="fa-solid fa-folder-medical"></i>
                    </div>
                    <div class="kpi-info">
                        <h3>Historiales Clínicos</h3>
                        <p class="kpi-number"><?php echo $historiales['total']; ?></p>
                    </div>
                </div>
            </div>

            <div class="report-grid">
                
                <div class="report-card">
                    <h3><i class="fa-solid fa-calendar-check" style="color: var(--primary-color);"></i> Citas por Especie</h3>
                    <table class="main-table" style="margin-top: 0;">
                        <thead>
                            <tr>
                                <th>Especie</th>
                                <th style="text-align: center;">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($especies->num_rows > 0) {
                                while($fila = $especies->fetch_assoc()) { ?>
                                <tr>
                                    <td><i class="fa-solid fa-paw" style="color: #a0aec0; margin-right: 8px;"></i> <?php echo ucfirst(strtolower($fila['especie'])); ?></td>
                                    <td style="text-align: center;"><span class="badge badge-other"><?php echo $fila['cantidad']; ?></span></td>
                                </tr>
                                <?php } 
                            } else { ?>
                                <tr><td colspan="2" class="text-muted" style="text-align: center;">No hay citas registradas</td></tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <div class="report-card">
                    <h3><i class="fa-solid fa-dna" style="color: var(--secondary-color);"></i> Población Total por Especie</h3>
                    <table class="main-table" style="margin-top: 0;">
                        <thead>
                            <tr>
                                <th>Especie</th>
                                <th style="text-align: center;">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($fila = $especies_registradas->fetch_assoc()) { ?>
                            <tr>
                                <td><i class="fa-solid fa-tag" style="color: #a0aec0; margin-right: 8px;"></i> <?php echo ucfirst(strtolower($fila['especie'])); ?></td>
                                <td style="text-align: center;"><span class="badge badge-dog"><?php echo $fila['cantidad']; ?></span></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <div class="report-card" style="grid-column: 1 / -1;">
                    <h3><i class="fa-solid fa-user-doctor" style="color: var(--success-color);"></i> Total de Citas Asignadas por Profesional</h3>
                    <table class="main-table" style="margin-top: 0;">
                        <thead>
                            <tr>
                                <th>Nombre del Médico Veterinario</th>
                                <th style="text-align: center;">Total de Citas Asignadas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($fila = $veterinarios->fetch_assoc()) { ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <i class="fa-solid fa-circle-user" style="font-size: 1.2rem; color: var(--primary-color);"></i>
                                        <strong>Dr/a. <?php echo $fila['nombre']; ?></strong>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge" style="background-color: #f0f4f8; color: var(--text-color); font-weight: bold; padding: 6px 15px;">
                                        <?php echo $fila['total_citas']; ?> consultas
                                    </span>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    </main>

</body>
</html>