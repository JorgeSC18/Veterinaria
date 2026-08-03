<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login");
    exit();
}
include("../conexion.php");

$error = "";
$id_cliente = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 1. Obtener datos del cliente y su cuenta de usuario vinculada
$sql_fetch = "SELECT c.*, u.nombre, u.correo, u.id_usuario 
              FROM cliente c 
              INNER JOIN USUARIO u ON c.USUARIO_id_usuario = u.id_usuario 
              WHERE c.id_cliente = ?";
$stmt_fetch = $conn->prepare($sql_fetch);
$stmt_fetch->bind_param("i", $id_cliente);
$stmt_fetch->execute();
$resultado = $stmt_fetch->get_result();
$cliente = $resultado->fetch_assoc();
$stmt_fetch->close();

if (!$cliente) {
    header("Location: index");
    exit();
}

// 2. Procesar los datos editados
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $cedula = trim($_POST['cedula']); 
    $direccion = trim($_POST['direccion']);
    $telefono = trim($_POST['telefono']);
    $contrasena_plana = trim($_POST['contraseña']);
    $id_usuario = $cliente['id_usuario'];

    if (!empty($nombre) && !empty($correo) && !empty($cedula)) {
        $conn->begin_transaction();
        try {
            // Actualizar USUARIO
            if (!empty($contrasena_plana)) {
                $contrasena_encriptada = password_hash($contrasena_plana, PASSWORD_DEFAULT);
                $sql_user = "UPDATE USUARIO SET nombre = ?, correo = ?, contraseña = ? WHERE id_usuario = ?";
                $stmt_user = $conn->prepare($sql_user);
                $stmt_user->bind_param("sssi", $nombre, $correo, $contrasena_encriptada, $id_usuario);
            } else {
                $sql_user = "UPDATE USUARIO SET nombre = ?, correo = ? WHERE id_usuario = ?";
                $stmt_user = $conn->prepare($sql_user);
                $stmt_user->bind_param("ssi", $nombre, $correo, $id_usuario);
            }
            $stmt_user->execute();
            $stmt_user->close();

            // Actualizar CLIENTE
            // CORREGIDO: Cambiado 'cedula = ?' por 'documento = ?' para que coincida con tu MySQL
            $sql_cli = "UPDATE cliente SET documento = ?, direccion = ?, telefono = ? WHERE id_cliente = ?";
            $stmt_cli = $conn->prepare($sql_cli);
            $stmt_cli->bind_param("sssi", $cedula, $direccion, $telefono, $id_cliente);
            $stmt_cli->execute();
            $stmt_cli->close();

            $conn->commit();
            header("Location: index?status=updated");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error al guardar los cambios: " . $e->getMessage();
        }
    } else {
        $error = "Los campos Nombre, Correo y Cédula son completamente obligatorios.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Cliente - VetVital</title>
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
                <li><a href="index" class="active"><i class="fa-solid fa-users"></i> Clientes</a></li>
                <li><a href="../mascotas/index"><i class="fa-solid fa-dog"></i> Mascotas</a></li>
                <li><a href="../veterinarios/index"><i class="fa-solid fa-user-doctor"></i> Veterinarios</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <header class="topbar"><h1>Modificar Perfil de Cliente</h1></header>
        <div class="content-container">
            <div class="table-card" style="padding: 25px; max-width: 700px; margin: 0 auto;">
                
                <?php if(!empty($error)): ?>
                    <div style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 6px; margin-bottom: 20px; font-weight: bold;"><?php echo $error; ?></div>
                <?php endif; ?>

                <form action="editar?id=<?php echo $id_cliente; ?>" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
                    <div>
                        <h3 style="color: #115e59; margin-bottom: 10px;"><i class="fa-solid fa-user-lock"></i> Acceso al Portal</h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div>
                                <label style="display:block; font-weight:bold; margin-bottom:5px;">Nombre Completo</label>
                                <input type="text" name="nombre" value="<?php echo htmlspecialchars($cliente['nombre']); ?>" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:6px;" required>
                            </div>
                            <div>
                                <label style="display:block; font-weight:bold; margin-bottom:5px;">Correo de Ingreso</label>
                                <input type="email" name="correo" value="<?php echo htmlspecialchars($cliente['correo']); ?>" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:6px;" required>
                            </div>
                        </div>
                        <div style="margin-top: 15px;">
                            <label style="display:block; font-weight:bold; margin-bottom:5px;">Cambiar Contraseña (Dejar vacío para conservar la actual)</label>
                            <input type="password" name="contraseña" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:6px;">
                        </div>
                    </div>

                    <hr style="border: 0; border-top: 1px solid #e2e8f0;">

                    <div>
                        <h3 style="color: #115e59; margin-bottom: 10px;"><i class="fa-solid fa-address-card"></i> Ficha Personal</h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div>
                                <label style="display:block; font-weight:bold; margin-bottom:5px;">Cédula / ID</label>
                                <input type="text" name="cedula" value="<?php echo htmlspecialchars($cliente['documento']); ?>" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:6px;" required>
                            </div>
                            <div>
                                <label style="display:block; font-weight:bold; margin-bottom:5px;">Teléfono</label>
                                <input type="text" name="telefono" value="<?php echo htmlspecialchars($cliente['telefono']); ?>" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:6px;">
                            </div>
                        </div>
                        <div style="margin-top: 15px;">
                            <label style="display:block; font-weight:bold; margin-bottom:5px;">Dirección de Residencia</label>
                            <input type="text" name="direccion" value="<?php echo htmlspecialchars($cliente['direccion']); ?>" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:6px;">
                        </div>
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <button type="submit" style="background: #115e59; color: white; border: none; padding: 12px 25px; border-radius: 6px; cursor: pointer; font-weight: bold;"><i class="fa-solid fa-floppy-disk"></i> Actualizar Cliente</button>
                        <a href="index" style="background: #e2e8f0; color: #334155; padding: 12px 25px; border-radius: 6px; text-decoration: none; font-weight: bold;">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
</html>