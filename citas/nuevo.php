<?php

session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login");
    exit();
}

if ($_SESSION['rol'] != 'admin') {
    echo("Acceso denegado.");
    exit();
}

include("../conexion.php");

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Cita - VetVital</title>
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
                <li><a href="../veterinarios/index"><i class="fa-solid fa-user-doctor"></i> Veterinarios</a></li>
                <li><a href="index" class="active"><i class="fa-solid fa-calendar-days"></i> Citas</a></li>
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
            <h1>Gestión de Citas</h1>
            <div class="topbar-welcome">Administrador <i class="fa-solid fa-user-shield"></i></div>
        </header>
        
        <div class="content-container">
            <div class="table-header-container">
                <h2>Programar Nueva Cita</h2>
                <a href="index" class="btn btn-cancel">
                    <i class="fa-solid fa-arrow-left"></i> Volver al listado
                </a>
            </div>

            <div class="form-card">
                <form action="guardar" method="POST" class="form-grid">
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label for="fecha">Fecha de la Cita</label>
                            <input type="date" id="fecha" name="fecha" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="hora">Hora de la Cita</label>
                            <input type="time" id="hora" name="hora" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="mascota_id">Paciente (Mascota)</label>
                        <select id="mascota_id" name="mascota_id" class="form-control" required>
                            <option value="">-- Selecciona a la mascota --</option>
                            <?php
                            $sql_mascotas = "SELECT id_mascota, nombre FROM MASCOTA";
                            $res_mascotas = $conn->query($sql_mascotas);
                            while($mascota = $res_mascotas->fetch_assoc()) {
                                echo "<option value='".intval($mascota['id_mascota'])."'>".htmlspecialchars($mascota['nombre'])."</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="veterinario_id">Veterinario a Cargo</label>
                        <select id="veterinario_id" name="veterinario_id" class="form-control" required>
                            <option value="">-- Selecciona al veterinario --</option>
                            <?php
                            $sql_vet = "SELECT v.id_veterinario, u.nombre, v.especialidad 
                                        FROM VETERINARIO v
                                        INNER JOIN USUARIO u ON v.USUARIO_id_usuario = u.id_usuario";
                            $res_vet = $conn->query($sql_vet);
                            while($vet = $res_vet->fetch_assoc()) {
                                echo "<option value='".intval($vet['id_veterinario'])."'>Dr/a. ".htmlspecialchars($vet['nombre'])." (".htmlspecialchars($vet['especialidad']).")</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="motivo">Motivo de la Consulta</label>
                        <input type="text" id="motivo" name="motivo" class="form-control" placeholder="Ej. Vacunación, Consulta general, Control..." required>
                    </div>

                    <div class="form-actions" style="grid-column: 1 / -1;">
                        <button type="submit" class="btn btn-add">
                            <i class="fa-solid fa-floppy-disk"></i> Agendar Cita
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </main>

</body>
</html>