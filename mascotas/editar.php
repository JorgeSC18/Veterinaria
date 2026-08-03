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

// 1. Limpiamos y aseguramos que el ID sea un número entero
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: index");
    exit();
}

// 2. Cambiamos a Prepared Statement para evitar inyección SQL
$sql = "SELECT * FROM MASCOTA WHERE id_mascota = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$mascota = $resultado->fetch_assoc();
$stmt->close();

// Si por alguna razón intentan editar un ID que no existe
if (!$mascota) {
    header("Location: index");
    exit();
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Mascota - VetVital</title>
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
                <li><a href="index" class="active"><i class="fa-solid fa-dog"></i> Mascotas</a></li>
                <li><a href="../veterinarios/index"><i class="fa-solid fa-user-doctor"></i> Veterinarios</a></li>
                <li><a href="../citas/index"><i class="fa-solid fa-calendar-days"></i> Citas</a></li>
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
            <h1>Gestión de Mascotas</h1>
            <div class="topbar-welcome">Administrador <i class="fa-solid fa-user-shield"></i></div>
        </header>
        
        <div class="content-container">
            <div class="table-header-container">
                <h2>Editar Ficha del Paciente</h2>
                <a href="index" class="btn btn-cancel">
                    <i class="fa-solid fa-arrow-left"></i> Volver al listado
                </a>
            </div>

            <div class="form-card">
                <form action="actualizar" method="POST" class="form-grid">
                    
                    <input type="hidden" name="id_mascota" value="<?php echo intval($mascota['id_mascota']); ?>">

                    <div class="form-group">
                        <label for="nombre">Nombre de la Mascota</label>
                        <input type="text" id="nombre" name="nombre" class="form-control" value="<?php echo htmlspecialchars($mascota['nombre']); ?>" required>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label for="especie">Especie</label>
                            <input type="text" id="especie" name="especie" class="form-control" value="<?php echo htmlspecialchars($mascota['especie']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="raza">Raza</label>
                            <input type="text" id="raza" name="raza" class="form-control" value="<?php echo htmlspecialchars($mascota['raza']); ?>" required>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label for="edad">Edad (Años)</label>
                            <input type="number" id="edad" name="edad" class="form-control" value="<?php echo intval($mascota['edad']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="sexo">Sexo</label>
                            <input type="text" id="sexo" name="sexo" class="form-control" value="<?php echo htmlspecialchars($mascota['sexo']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="peso">Peso (kg)</label>
                            <input type="number" step="0.01" id="peso" name="peso" class="form-control" value="<?php echo floatval($mascota['peso']); ?>" required>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-add">
                            <i class="fa-solid fa-pen-to-square"></i> Actualizar Cambios
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </main>

</body>
</html>