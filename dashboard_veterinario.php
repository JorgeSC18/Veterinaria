<?php
session_start();
include 'conexion.php';

// Configura la zona horaria de tu país para las citas
date_default_timezone_set('America/Bogota'); 
$fecha_actual = date('Y-m-d'); // Variable global del día de hoy

// CONTROL DE ACCESO PARA VETERINARIOS
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'veterinario') {
    header("Location: login");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

// Obtener el id_veterinario asociado
$sql_vet = "SELECT id_veterinario, especialidad FROM VETERINARIO WHERE USUARIO_id_usuario = '$id_usuario' LIMIT 1";
$res_vet = $conn->query($sql_vet);
$vet_data = $res_vet->fetch_assoc();
$id_veterinario = $vet_data['id_veterinario'];

// Sistema de navegación
$page = isset($_GET['page']) ? $_GET['page'] : 'inicio';

$mensaje_agenda = "";
$tipo_mensaje_agenda = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['guardar_historial'])) {
    $id_cita_atender = mysqli_real_escape_string($conn, $_POST['id_cita']);
    $id_mascota = mysqli_real_escape_string($conn, $_POST['id_mascota']);
    $diagnostico = mysqli_real_escape_string($conn, $_POST['diagnostico']);
    $tratamiento = mysqli_real_escape_string($conn, $_POST['tratamiento']);
    $observaciones = mysqli_real_escape_string($conn, $_POST['observaciones']);

    // Paso 1: Insertar el registro en el Historial Médico
    $sql_insert = "INSERT INTO HISTORIAL_MEDICO (fecha, diagnostico, tratamiento, observaciones, MASCOTA_id_mascota) 
                   VALUES ('$fecha_actual', '$diagnostico', '$tratamiento', '$observaciones', '$id_mascota')";
    
    if ($conn->query($sql_insert)) {
        // Paso 2: Eliminar la cita de la agenda porque ya fue atendida
        $sql_delete = "DELETE FROM CITA WHERE id_cita = '$id_cita_atender'";
        $conn->query($sql_delete);
        
        $mensaje_agenda = "El historial se ha guardado correctamente y la cita fue finalizada.";
        $tipo_mensaje_agenda = "success";
        $page = 'inicio'; // Regresa al inicio
    } else {
        $mensaje_agenda = "Error de base de datos al guardar: " . $conn->error;
        $tipo_mensaje_agenda = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Veterinario - VetVital</title>
    <link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --brand-green: #115e59;
            --brand-green-light: #e6f4f2;
            --bg-main: #f8fafc;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
        }
        body { margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; background-color: var(--bg-main); display: flex; height: 100vh; }
        
        .sidebar { width: 260px; background: #ffffff; border-right: 1px solid var(--border-color); display: flex; flex-direction: column; justify-content: space-between; padding: 30px 20px; box-sizing: border-box; }
        .logo { font-size: 1.5rem; font-weight: bold; color: var(--brand-green); display: flex; align-items: center; gap: 10px; margin-bottom: 40px; }
        .menu-nav { list-style: none; padding: 0; margin: 0; }
        .menu-nav li { margin-bottom: 8px; }
        .menu-nav a { display: flex; align-items: center; gap: 12px; padding: 12px 15px; color: var(--text-muted); text-decoration: none; font-weight: 500; border-radius: 8px; transition: 0.2s; }
        .menu-nav a:hover, .menu-nav li.active a { background-color: var(--brand-green-light); color: var(--brand-green); }
        .sidebar-footer { border-top: 1px solid var(--border-color); padding-top: 20px; }
        .btn-logout { color: #ef4444; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 8px; }

        .main-content { flex: 1; padding: 40px; overflow-y: auto; box-sizing: border-box; }
        .welcome-card { background: #ffffff; border: 1px solid var(--border-color); border-radius: 12px; padding: 24px; margin-bottom: 30px; }
        
        .data-table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; border: 1px solid var(--border-color); margin-bottom: 35px; }
        .data-table th, .data-table td { padding: 14px; text-align: left; border-bottom: 1px solid var(--border-color); }
        .data-table th { background-color: var(--brand-green-light); color: var(--brand-green); }
        
        .btn-action { background: var(--brand-green); color: white; border: none; padding: 8px 14px; border-radius: 6px; cursor: pointer; text-decoration: none; font-size: 0.9rem; font-weight: bold; display: inline-flex; align-items: center; gap: 6px; }
        .btn-disabled { background: #cbd5e1; color: #64748b; border: none; padding: 8px 14px; border-radius: 6px; cursor: not-allowed; font-size: 0.9rem; font-weight: bold; display: inline-flex; align-items: center; gap: 6px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-top">
            <div class="logo"><i class="fa-solid fa-stethoscope"></i> VetVital Doc</div>
            <ul class="menu-nav">
                <li class="<?php echo $page == 'inicio' ? 'active' : ''; ?>"><a href="dashboard_veterinario?page=inicio"><i class="fa-solid fa-house-chimney-medical"></i> Inicio</a></li>
                <li class="<?php echo $page == 'agenda' ? 'active' : ''; ?>"><a href="dashboard_veterinario?page=agenda"><i class="fa-solid fa-calendar-day"></i> Mi Agenda</a></li>
            </ul>
        </div>
        <div class="sidebar-footer">
            <div style="margin-bottom: 15px;">
                <h4 style="margin:0; color: var(--text-dark);">Dr/a. <?php echo htmlspecialchars($_SESSION['nombre']); ?></h4>
                <span style="font-size:0.8rem; color:var(--text-muted);"><?php echo htmlspecialchars($vet_data['especialidad']); ?></span>
            </div>
            <a href="logout" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Salir</a>
        </div>
    </div>

    <div class="main-content">

        <?php if(!empty($mensaje_agenda)): ?>
            <div style="margin-bottom: 20px; padding: 15px; border-radius: 8px; font-weight: bold; background-color: <?php echo $tipo_mensaje_agenda == 'success' ? '#dcfce7' : '#fee2e2'; ?>; color: <?php echo $tipo_mensaje_agenda == 'success' ? '#166534' : '#991b1b'; ?>;">
                <?php echo $mensaje_agenda; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($page == 'inicio'): ?>
            <div class="welcome-card">
                <h3 style="color: var(--brand-green); margin-top:0;">¡Bienvenido, Dr/a. <?php echo htmlspecialchars($_SESSION['nombre']); ?>!</h3>
                <p style="margin: 0; color: var(--text-muted);">Aquí tienes las consultas programadas exclusivamente para el día de hoy.</p>
            </div>

            <h2>Pacientes de Hoy (<?php echo date('d/m/Y'); ?>)</h2>
            <table class="data-table">
                <thead>
                    <tr><th>Hora</th><th>Mascota</th><th>Motivo</th><th>Acción</th></tr>
                </thead>
                <tbody>
                    <?php
                    // SQL Filtrando estrictamente por la fecha actual (Hoy)
                    $sql_hoy = "SELECT c.id_cita, c.fecha, c.hora, c.motivo, m.nombre AS mascota 
                                FROM CITA c 
                                INNER JOIN MASCOTA m ON c.MASCOTA_id_mascota = m.id_mascota 
                                WHERE c.VETERINARIO_id_veterinario = '$id_veterinario' 
                                  AND c.fecha = '$fecha_actual'
                                ORDER BY c.hora ASC";
                    $res_hoy = $conn->query($sql_hoy);
                    
                    if ($res_hoy && $res_hoy->num_rows > 0) {
                        $momento_actual = new DateTime(); 

                        while($cita = $res_hoy->fetch_assoc()) {
                            $momento_cita = new DateTime($cita['fecha'] . ' ' . $cita['hora']);
                            $ya_se_puede_atender = ($momento_actual >= $momento_cita);

                            echo "<tr>";
                            echo "<td>".$cita['hora']."</td>";
                            echo "<td><b>".htmlspecialchars($cita['mascota'])."</b></td>";
                            echo "<td>".htmlspecialchars($cita['motivo'])."</td>";
                            echo "<td>";
                            if ($ya_se_puede_atender) {
                                echo "<a href='dashboard_veterinario?page=atender&id=".$cita['id_cita']."' class='btn-action'><i class='fa-solid fa-user-doctor'></i> Atender</a>";
                            } else {
                                echo "<button class='btn-disabled' title='Falta para la hora pactada (".$cita['hora'].")'><i class='fa-solid fa-lock'></i> Bloqueado</button>";
                            }
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' style='text-align:center; color: var(--text-muted); padding: 20px;'>No tienes citas agendadas para el día de hoy. ¡Buen día!</td></tr>";
                    }
                    ?>
                </tbody>
            </table>

        <?php elseif ($page == 'agenda'): ?>
          
            <h2>Mi Agenda Completa de Citas</h2>
            <p style="color:var(--text-muted); margin-bottom:20px;">Listado general de todas tus consultas programadas (Pasadas, presentes y futuras).</p>
            <table class="data-table">
                <thead>
                    <tr><th>Fecha</th><th>Hora</th><th>Mascota</th><th>Motivo</th><th>Acción</th></tr>
                </thead>
                <tbody>
                    <?php
                    // SQL que extrae absolutamente todas las citas del veterinario
                    $sql_todas = "SELECT c.id_cita, c.fecha, c.hora, c.motivo, m.nombre AS mascota 
                                  FROM CITA c 
                                  INNER JOIN MASCOTA m ON c.MASCOTA_id_mascota = m.id_mascota 
                                  WHERE c.VETERINARIO_id_veterinario = '$id_veterinario' 
                                  ORDER BY c.fecha ASC, c.hora ASC";
                    $res_citas = $conn->query($sql_todas); 
                    
                    if ($res_citas && $res_citas->num_rows > 0) {
                        $momento_actual = new DateTime(); 

                        while($cita = $res_citas->fetch_assoc()) {
                            $momento_cita = new DateTime($cita['fecha'] . ' ' . $cita['hora']);
                            $ya_se_puede_atender = ($momento_actual >= $momento_cita);

                            echo "<tr>";
                            echo "<td>".$cita['fecha']."</td>";
                            echo "<td>".$cita['hora']."</td>";
                            echo "<td><b>".htmlspecialchars($cita['mascota'])."</b></td>";
                            echo "<td>".htmlspecialchars($cita['motivo'])."</td>";
                            echo "<td>";
                            if ($ya_se_puede_atender) {
                                echo "<a href='dashboard_veterinario?page=atender&id=".$cita['id_cita']."' class='btn-action'><i class='fa-solid fa-user-doctor'></i> Atender</a>";
                            } else {
                                echo "<button class='btn-disabled' title='No puedes atender antes de la hora pactada (".$cita['hora'].")'><i class='fa-solid fa-lock'></i> Bloqueado</button>";
                            }
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' style='text-align:center;'>No registras citas en el sistema.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>

        <?php elseif ($page == 'atender'): ?>
            <?php
            $id_cita_actual = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : 0;
            
            $sql_info = "SELECT c.*, m.id_mascota, m.nombre AS mascota, m.especie, m.raza 
                         FROM CITA c 
                         INNER JOIN MASCOTA m ON c.MASCOTA_id_mascota = m.id_mascota 
                         WHERE c.id_cita = '$id_cita_actual' AND c.VETERINARIO_id_veterinario = '$id_veterinario'";
            $res_info = $conn->query($sql_info);
            
            if ($res_info && $res_info->num_rows > 0) {
                $info = $res_info->fetch_assoc();
            ?>
                <h2>Atender Paciente: <?php echo htmlspecialchars($info['mascota']); ?></h2>
                <div class="welcome-card" style="max-width: 650px;">
                    <div style="background: var(--brand-green-light); padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        <p style="margin: 0 0 5px 0;"><b>Especie y Raza:</b> <?php echo htmlspecialchars($info['especie'] . ' - ' . $info['raza']); ?></p>
                        <p style="margin: 0;"><b>Motivo de la cita:</b> <?php echo htmlspecialchars($info['motivo']); ?></p>
                    </div>
                    
                    <form action="dashboard_veterinario?page=atender" method="POST">
                        <input type="hidden" name="id_cita" value="<?php echo $info['id_cita']; ?>">
                        <input type="hidden" name="id_mascota" value="<?php echo $info['id_mascota']; ?>">
                        
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label style="font-weight: bold; margin-bottom: 8px; display: block;">Diagnóstico Clínico</label>
                            <input type="text" name="diagnostico" placeholder="Ej. Infección leve, Vacunación..." style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;" required>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label style="font-weight: bold; margin-bottom: 8px; display: block;">Tratamiento / Receta</label>
                            <input type="text" name="treatment" name="tratamiento" placeholder="Ej. Amoxicilina 500mg cada 12h..." style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;" required>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 25px;">
                            <label style="font-weight: bold; margin-bottom: 8px; display: block;">Observaciones Adicionales</label>
                            <textarea name="observaciones" rows="5" style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-family: inherit; resize: vertical;" placeholder="Detalla las recomendaciones médicas..." required></textarea>
                        </div>
                        
                        <button type="submit" name="guardar_historial" style="background: var(--brand-green); color: white; border: none; padding: 12px 24px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 1rem;"><i class="fa-solid fa-floppy-disk"></i> Guardar Historial y Finalizar Cita</button>
                        
                        <a href="dashboard_veterinario?page=inicio" style="margin-left: 15px; color: var(--text-muted); text-decoration: none; font-weight: 500;">Cancelar</a>
                    </form>
                </div>
            <?php 
            } else {
                echo "<div class='welcome-card' style='border-color: #fecaca; background: #fef2f2; color: #991b1b;'>Cita no encontrada o ya fue atendida.</div>";
                echo "<a href='dashboard_veterinario?page=inicio' style='color: var(--brand-green); font-weight: bold;'>← Volver al inicio</a>";
            }
            ?>
        <?php endif; ?>

    </div>
</body>
</html>