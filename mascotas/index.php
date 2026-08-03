<?php

session_start();

// 1. Añadimos el control de acceso que faltaba para proteger la vista
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login");
    exit();
}

if ($_SESSION['rol'] != 'admin') {
    echo("Acceso denegado.");
    exit();
}

include("../conexion.php");

$sql = "SELECT
         MASCOTA.*,
         USUARIO.nombre AS nombre_dueño
        FROM MASCOTA
        INNER JOIN CLIENTE
          ON MASCOTA.CLIENTE_id_cliente = CLIENTE.id_cliente
        INNER JOIN USUARIO
          ON CLIENTE.USUARIO_id_usuario = USUARIO.id_usuario";  

$resultado = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mascotas - VetVital</title>
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
                    <a href="index" class="active">
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
            <h1>Gestión de Mascotas</h1>
            <div class="topbar-welcome">
                Administrador <i class="fa-solid fa-user-shield"></i>
            </div>
        </header>
        
        <div class="content-container">
            
            <div class="table-header-container">
                <h2>Listado de Mascotas</h2>
                <a href="nuevo" class="btn btn-add">
                    <i class="fa-solid fa-plus"></i> Nueva Mascota
                </a>
            </div>

            <div class="table-card">
                <table class="main-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Especie</th>
                            <th>Raza</th>
                            <th>Edad / Sexo</th>
                            <th>Peso</th>
                            <th>Dueño/a</th>
                            <th style="text-align: center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($fila = $resultado->fetch_assoc()) { 
                            
                            $especie = strtolower($fila['especie']);
                            $clase_badge = 'badge-other'; 
                            
                            if (strpos($especie, 'perro') !== false || strpos($especie, 'canino') !== false) {
                                $clase_badge = 'badge-dog';
                            } elseif (strpos($especie, 'gato') !== false || strpos($especie, 'felino') !== false) {
                                $clase_badge = 'badge-cat';
                            }
                        ?>
                            <tr>
                                <td><?php echo intval($fila['id_mascota']); ?></td>
                                <td><strong><?php echo htmlspecialchars($fila['nombre']); ?></strong></td>
                                <td>
                                    <span class="badge <?php echo $clase_badge; ?>">
                                        <?php echo htmlspecialchars($fila['especie']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($fila['raza']); ?></td>
                                <td><?php echo intval($fila['edad']); ?> años / <?php echo htmlspecialchars($fila['sexo']); ?></td>
                                <td><?php echo floatval($fila['peso']); ?> kg</td>
                                <td><i class="fa-regular fa-user" style="color: var(--text-muted); margin-right: 5px;"></i> <?php echo htmlspecialchars($fila['nombre_dueño']); ?></td>
                                <td style="text-align: center;">
                                    <div style="display: flex; justify-content: center; gap: 8px;">
                                        <a href="editar?id=<?php echo intval($fila['id_mascota']); ?>" class="btn btn-action btn-edit">
                                            <i class="fa-solid fa-pen"></i> Editar
                                        </a>
                                        <a href="eliminar?id=<?php echo intval($fila['id_mascota']); ?>" class="btn btn-action btn-delete" onclick="return confirm('¿Estás seguro de que deseas eliminar esta mascota?');">
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