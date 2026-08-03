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

$id = $_GET['id'];

$sql = "SELECT * FROM HISTORIAL_MEDICO
        WHERE id_historial = $id";

$resultado = $conn->query($sql);

$historial_medico = $resultado->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Historial - VetVital</title>
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
                <h2>Editar Historial Médico</h2>
                <a href="index" class="btn btn-cancel">
                    <i class="fa-solid fa-arrow-left"></i> Volver al listado
                </a>
            </div>

            <div class="form-card">
                <form action="actualizar" method="POST" class="form-grid">
                    
                    <input type="hidden" name="id_historial" value="<?php echo $historial_medico['id_historial']; ?>">

                    <div class="form-group">
                        <label for="fecha">Fecha</label>
                        <input type="date" id="fecha" name="fecha" class="form-control" value="<?php echo $historial_medico['fecha']; ?>" required>
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="diagnostico">Diagnóstico</label>
                        <input type="text" id="diagnostico" name="diagnostico" class="form-control" value="<?php echo $historial_medico['diagnostico']; ?>" required>
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="tratamiento">Tratamiento</label>
                        <input type="text" id="tratamiento" name="tratamiento" class="form-control" value="<?php echo $historial_medico['tratamiento']; ?>" required>
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="observaciones">Observaciones</label>
                        <textarea id="observaciones" name="observaciones" class="form-control" cols="200" rows="12" required><?php echo $historial_medico['observaciones']; ?></textarea>
                    </div>

                    <div class="form-actions" style="grid-column: 1 / -1;">
                        <button type="submit" class="btn btn-add">
                            <i class="fa-solid fa-pen-to-square"></i> Actualizar Historial
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </main>

</body>
</html>