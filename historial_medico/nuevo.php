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
    <title>Nuevo Historial - VetVital</title>
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
                <li><a href="../citas/index"><i class="fa-solid fa-calendar-days"></i> Citas</a></li>
                <li><a href="index" class="active"><i class="fa-solid fa-file-medical"></i> Historial Médico</a></li>
                <li><a href="../reportes/index"><i class="fa-solid fa-chart-line"></i> Reportes</a></li>
            </ul>
        </nav>
        <div class="sidebar-user">
            <div class="user-info">
                <p class="user-name"><?php echo $_SESSION['nombre']; ?></p>
                <p class="user-role">Rol: <?php echo $_SESSION['rol']; ?></p>
            </div>
            <a href="../logout" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión</a>
        </div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <h1>Historial Clínico</h1>
            <div class="topbar-welcome">Administrador <i class="fa-solid fa-user-shield"></i></div>
        </header>
        
        <div class="content-container">
            <div class="table-header-container">
                <h2>Registrar Historial Médico</h2>
                <a href="index" class="btn btn-cancel">
                    <i class="fa-solid fa-arrow-left"></i> Volver al listado
                </a>
            </div>

            <div class="form-card">
                <form action="guardar" method="POST" class="form-grid">
                    
                    <div class="form-group">
                        <label for="mascota_id">Paciente (Mascota)</label>
                        <select id="mascota_id" name="mascota_id" class="form-control" required>
                            <option value="">-- Selecciona el paciente --</option>
                            <?php
                            $sql_mascotas = "SELECT id_mascota, nombre FROM MASCOTA";
                            $res_mascotas = $conn->query($sql_mascotas);
                            while($mascota = $res_mascotas->fetch_assoc()) {
                                echo "<option value='".$mascota['id_mascota']."'>".$mascota['nombre']."</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="fecha">Fecha del Registro</label>
                        <input type="date" id="fecha" name="fecha" class="form-control" required>
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="diagnostico">Diagnóstico Médico</label>
                        <input type="text" id="diagnostico" name="diagnostico" class="form-control" placeholder="Ej. Otitis bilateral, Deshidratación leve..." required>
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="tratamiento">Tratamiento Asignado</label>
                        <input type="text" id="tratamiento" name="tratamiento" class="form-control" placeholder="Ej. Limpieza ótica c/12h y gotas antibióticas por 7 días..." required>
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="observaciones">Observaciones Adicionales</label>
                        <textarea id="observaciones" name="observaciones" class="form-control" cols="200" rows="12" placeholder="Notas sobre el comportamiento del paciente, dieta recomendada o fechas de revisión..." required></textarea>
                    </div>

                    <div class="form-actions" style="grid-column: 1 / -1;">
                        <button type="submit" class="btn btn-add">
                            <i class="fa-solid fa-floppy-disk"></i> Guardar Registro Clínico
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </main>

</body>
</html>