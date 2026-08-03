<?php

session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login");
    exit();
}

include("../conexion.php");

$sql = "SELECT 
            VETERINARIO.*,
            USUARIO.nombre AS nombre_veterinario
        FROM VETERINARIO
        INNER JOIN USUARIO
            ON VETERINARIO.USUARIO_id_usuario = USUARIO.id_usuario";


$resultado = $conn->query($sql);

?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Veterinarios - VetVital</title>
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
                    <a href="index" class="active">
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
            <h1>Gestión de Veterinarios</h1>
            <div class="topbar-welcome">
                Administrador <i class="fa-solid fa-user-shield"></i>
            </div>
        </header>
        
        <div class="content-container">
            
            <?php if (isset($_GET['status']) && $_GET['status'] == 'created'): ?>
                <div style="padding: 15px; background: #dcfce7; color: #166534; border-radius: 8px; margin-bottom: 20px; font-weight: bold; border-left: 5px solid #16a34a;">
                    <i class="fa-solid fa-circle-check"></i> ¡Médico Veterinario y cuenta de usuario creados exitosamente!
                </div>
            <?php endif; ?>


            <?php if (isset($_GET['status']) && $_GET['status'] == 'updated'): ?>
                <div style="padding: 15px; background: #dcfce7; color: #166534; border-radius: 8px; margin-bottom: 20px; font-weight: bold; border-left: 5px solid #16a34a;">
                     <i class="fa-solid fa-circle-check"></i> ¡Registro actualizado correctamente en el sistema!
                </div>

                
            <?php elseif (isset($_GET['status']) && $_GET['status'] == 'deleted'): ?>
                <div style="padding: 15px; background: #fef08a; color: #854d0e; border-radius: 8px; margin-bottom: 20px; font-weight: bold; border-left: 5px solid #ca8a04;">
                    <i class="fa-solid fa-circle-check"></i> El registro y su cuenta de acceso han sido eliminados de la base de datos.
                </div>
            <?php endif; ?>


            <div class="table-header-container">
                <h2>Listado de Veterinarios</h2>
                <a href="nuevo" class="btn btn-add">
                    <i class="fa-solid fa-plus"></i> Nuevo Veterinario
                </a> </div>       

            <div class="table-card">
                <table class="main-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Veterinario</th>
                            <th>Especialidad</th>
                            <th>Teléfono</th>
                            <th style="text-align: center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($fila = $resultado->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $fila['id_veterinario']; ?></td>
                                <td><strong><?php echo htmlspecialchars($fila['nombre_veterinario']); ?></strong></td>
                                <td>
                                    <span class="badge badge-dog">
                                        <i class="fa-solid fa-stethoscope" style="margin-right: 4px;"></i> 
                                        <?php echo htmlspecialchars($fila['especialidad']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($fila['telefono']); ?></td>
                                <td style="text-align: center;">
                                    <div style="display: flex; justify-content: center; gap: 8px;">
                                        <a href="editar?id=<?php echo $fila['id_veterinario']; ?>" class="btn btn-action btn-edit">
                                            <i class="fa-solid fa-pen"></i> Editar
                                        </a>
                                        <a href="eliminar?id=<?php echo $fila['USUARIO_id_usuario']; ?>" 
                                           class="btn btn-action btn-delete" 
                                           onclick="return confirm('¿Está seguro de eliminar a este veterinario? Tenga en cuenta que si tiene citas pendientes asociadas, estas se borrarán por completo del sistema.');">
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