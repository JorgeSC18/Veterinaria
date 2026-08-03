<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login");
    exit();
}
include("../conexion.php");

$error = "";
$id_veterinario = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 1. Obtener los datos actuales del veterinario y su usuario
$sql_fetch = "SELECT v.*, u.nombre, u.correo, u.id_usuario 
              FROM VETERINARIO v 
              INNER JOIN USUARIO u ON v.USUARIO_id_usuario = u.id_usuario 
              WHERE v.id_veterinario = ?";
$stmt_fetch = $conn->prepare($sql_fetch);
$stmt_fetch->bind_param("i", $id_veterinario);
$stmt_fetch->execute();
$resultado = $stmt_fetch->get_result();
$vet = $resultado->fetch_assoc();
$stmt_fetch->close();

if (!$vet) {
    header("Location: index");
    exit();
}

// 2. Procesar la actualización al enviar el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $especialidad = trim($_POST['especialidad']);
    $telefono = trim($_POST['telefono']);
    $contrasena_plana = trim($_POST['contraseña']);
    $id_usuario = $vet['id_usuario'];

    if (!empty($nombre) && !empty($correo)) {
        $conn->begin_transaction();
        try {
            // Actualizar la tabla USUARIO (Padre)
            if (!empty($contrasena_plana)) {
                // Si escribió una nueva contraseña, la actualizamos
                $contrasena_encriptada = password_hash($contrasena_plana, PASSWORD_DEFAULT);
                $sql_user = "UPDATE USUARIO SET nombre = ?, correo = ?, contraseña = ? WHERE id_usuario = ?";
                $stmt_user = $conn->prepare($sql_user);
                $stmt_user->bind_param("sssi", $nombre, $correo, $contrasena_encriptada, $id_usuario);
            } else {
                // Si no escribió contraseña, no tocamos ese campo
                $sql_user = "UPDATE USUARIO SET nombre = ?, correo = ? WHERE id_usuario = ?";
                $stmt_user = $conn->prepare($sql_user);
                $stmt_user->bind_param("ssi", $nombre, $correo, $id_usuario);
            }
            $stmt_user->execute();
            $stmt_user->close();

            // Actualizar la tabla VETERINARIO (Hija)
            $sql_vet = "UPDATE VETERINARIO SET especialidad = ?, telefono = ? WHERE id_veterinario = ?";
            $stmt_vet = $conn->prepare($sql_vet);
            $stmt_vet->bind_param("ssi", $especialidad, $telefono, $id_veterinario);
            $stmt_vet->execute();
            $stmt_vet->close();

            $conn->commit();
            header("Location: index?status=updated");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error al actualizar los datos: " . $e->getMessage();
        }
    } else {
        $error = "El nombre y el correo son campos obligatorios.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Veterinario - VetVital</title>
    <link rel="shortcut icon" type="image/x-icon" href="../img/favicon.ico">
    <link rel="stylesheet" href="../estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-logo"><i class="fa-solid fa-paw"></i> VetVital</div>
        <nav class="sidebar-nav">
            <ul>
                <li><a href="../dashboard_admin"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
                <li><a href="../clientes/index"><i class="fa-solid fa-users"></i> Clientes</a></li>
                <li><a href="../mascotas/index"><i class="fa-solid fa-dog"></i> Mascotas</a></li>
                <li><a href="index" class="active"><i class="fa-solid fa-user-doctor"></i> Veterinarios</a></li>
                <li><a href="../citas/index"><i class="fa-solid fa-calendar-days"></i> Citas</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <header class="topbar"><h1>Modificar Perfil Médico</h1></header>
        <div class="content-container">
            <div class="table-card" style="padding: 25px; max-width: 700px; margin: 0 auto;">
                
                <?php if(!empty($error)): ?>
                    <div style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 6px; margin-bottom: 20px; font-weight: bold;"><?php echo $error; ?></div>
                <?php endif; ?>

                <form action="editar?id=<?php echo $id_veterinario; ?>" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
                    <div>
                        <h3 style="color: #115e59; margin-bottom: 10px;"><i class="fa-solid fa-key"></i> Cuenta de Acceso</h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div>
                                <label style="display:block; font-weight:bold; margin-bottom:5px;">Nombre Completo</label>
                                <input type="text" name="nombre" value="<?php echo htmlspecialchars($vet['nombre']); ?>" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:6px;" required>
                            </div>
                            <div>
                                <label style="display:block; font-weight:bold; margin-bottom:5px;">Correo Electrónico</label>
                                <input type="email" name="correo" value="<?php echo htmlspecialchars($vet['correo']); ?>" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:6px;" required>
                            </div>
                        </div>
                        <div style="margin-top: 15px;">
                            <label style="display:block; font-weight:bold; margin-bottom:5px;">Nueva Contraseña (Dejar en blanco para no cambiar)</label>
                            <input type="password" name="contraseña" placeholder="Escribe una nueva contraseña solo si deseas cambiarla" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:6px;">
                        </div>
                    </div>

                    <hr style="border: 0; border-top: 1px solid #e2e8f0;">

                    <div>
                        <h3 style="color: #115e59; margin-bottom: 10px;"><i class="fa-solid fa-user-tie"></i> Información Interna</h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div>
                                <label style="display:block; font-weight:bold; margin-bottom:5px;">Especialidad</label>
                                <input type="text" name="especialidad" value="<?php echo htmlspecialchars($vet['especialidad']); ?>" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:6px;">
                            </div>
                            <div>
                                <label style="display:block; font-weight:bold; margin-bottom:5px;">Teléfono</label>
                                <input type="text" name="telefono" value="<?php echo htmlspecialchars($vet['telefono']); ?>" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:6px;">
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <button type="submit" style="background: #115e59; color: white; border: none; padding: 12px 25px; border-radius: 6px; cursor: pointer; font-weight: bold;"><i class="fa-solid fa-floppy-disk"></i> Guardar Cambios</button>
                        <a href="index" style="background: #e2e8f0; color: #334155; padding: 12px 25px; border-radius: 6px; text-decoration: none; font-weight: bold;">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>