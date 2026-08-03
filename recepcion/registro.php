<?php

session_start();

// Validamos la sesión y protegemos el rol (Recepcionista o Admin)
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login");
    exit();
}

if ($_SESSION['rol'] != 'recepcionista' && $_SESSION['rol'] != 'admin') {
    echo "Acceso denegado.";
    exit();
}

include("../conexion.php");

$mensaje_status = "";
$tipo_alerta = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // --- 1. CAPTURA Y SANITIZACIÓN DE DATOS ---
    // Datos del Cliente
    $nombre_cliente = mysqli_real_escape_string($conn, trim($_POST['nombre_cliente']));
    $documento      = mysqli_real_escape_string($conn, trim($_POST['documento']));
    $telefono       = mysqli_real_escape_string($conn, trim($_POST['telefono']));
    $correo         = mysqli_real_escape_string($conn, trim($_POST['correo']));
    
    // Datos de la Mascota
    $nombre_mascota = mysqli_real_escape_string($conn, trim($_POST['nombre_mascota']));
    $especie        = mysqli_real_escape_string($conn, trim($_POST['especie']));
    $raza           = mysqli_real_escape_string($conn, trim($_POST['raza']));
    $edad           = mysqli_real_escape_string($conn, trim($_POST['edad']));

    // --- 2. LOGICA DE INSERCIÓN INTELIGENTE ---
    // Verificamos si el cliente ya existe en el sistema
    $check_cliente = "SELECT id_cliente FROM CLIENTE WHERE documento = '$documento'";
    $res_check = $conn->query($check_cliente);

    // Iniciamos una transacción para asegurar que no se guarden datos a medias si algo falla
    $conn->begin_transaction();

    try {
        if ($res_check && $res_check->num_rows > 0) {
            // El cliente ya existe, recuperamos su ID
            $fila_cliente = $res_check->fetch_assoc();
            $id_cliente = $fila_cliente['id_cliente'];
            $notificacion_cliente = "Cliente existente identificado.";
        } else {
            // El cliente no existe, procedemos a crearlo paso a paso:
            
            // A) Insertar en la tabla base USUARIO (la contraseña por defecto será su propio documento)
            $password_hash = password_hash($documento, PASSWORD_DEFAULT);
            $sql_usuario = "INSERT INTO USUARIO (nombre, correo, contrasena, rol) 
                            VALUES ('$nombre_cliente', '$correo', '$password_hash', 'cliente')";
            
            if (!$conn->query($sql_usuario)) {
                throw new Exception("Error al crear el usuario base.");
            }
            $id_usuario_nuevo = $conn->insert_id;

            // B) Insertar en la tabla CLIENTE vinculando el USUARIO recién creado
            $sql_cliente = "INSERT INTO CLIENTE (documento, telefono, USUARIO_id_usuario) 
                            VALUES ('$documento', '$telefono', '$id_usuario_nuevo')";
            
            if (!$conn->query($sql_cliente)) {
                throw new Exception("Error al registrar los detalles del cliente.");
            }
            $id_cliente = $conn->insert_id;
            $notificacion_cliente = "Nuevo cliente registrado con éxito.";
        }

        // C) Insertar la Mascota utilizando el $id_cliente obtenido (ya sea nuevo o existente)
        // NOTA: Asegúrate de que el nombre del campo de la llave foránea sea exactamente el de tu base de datos (ej: CLIENTE_id_cliente)
        $sql_mascota = "INSERT INTO MASCOTA (nombre, especie, raza, edad, CLIENTE_id_cliente) 
                        VALUES ('$nombre_mascota', '$especie', '$raza', '$edad', '$id_cliente')";
        
        if (!$conn->query($sql_mascota)) {
            throw new Exception("Error al registrar la mascota.");
        }

        // Si todo salió bien, confirmamos los cambios en la base de datos
        $conn->commit();
        
        $mensaje_status = "¡Registro completado! $notificacion_cliente La mascota <strong>$nombre_mascota</strong> ha sido vinculada correctamente.";
        $tipo_alerta = "success";

    } catch (Exception $e) {
        // Si algo falla, revertimos cualquier inserción previa de este bloque
        $conn->rollback();
        $mensaje_status = "Hubo un error al procesar el registro unificado: " . $e->getMessage();
        $tipo_alerta = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Unificado - VetVital</title>
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
                    <a href="../dashboard_recepcionista">
                        <i class="fa-solid fa-chart-pie"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="buscar">
                        <i class="fa-solid fa-magnifying-glass"></i> Buscar Clientes y Citas
                    </a>
                </li>
                <li>
                    <a href="registro" class="active">
                        <i class="fa-solid fa-user-plus"></i> Registro Unificado
                    </a>
                </li>
                <li>
                    <a href="cita">
                        <i class="fa-solid fa-calendar-plus"></i> Agendar Cita
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
            <h1>Ingreso Rápido de Pacientes</h1>
            <div class="topbar-welcome">
                Recepción <i class="fa-solid fa-user-clock"></i>
            </div>
        </header>
        
        <div class="content-container">

            <!-- BLOQUE DE NOTIFICACIONES E ESTILOS CONGRUENTES -->
            <?php if (!empty($mensaje_status)): ?>
                <?php if ($tipo_alerta == 'success'): ?>
                    <div style="padding: 15px; background: #dcfce7; color: #166534; border-radius: 8px; margin-bottom: 25px; font-weight: bold; border-left: 5px solid #16a34a;">
                        <i class="fa-solid fa-circle-check"></i> <?php echo $mensaje_status; ?>
                    </div>
                <?php else: ?>
                    <div style="padding: 15px; background: #fee2e2; color: #991b1b; border-radius: 8px; margin-bottom: 25px; font-weight: bold; border-left: 5px solid #ef4444;">
                        <i class="fa-solid fa-triangle-exclamation"></i> <?php echo $mensaje_status; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <form action="registro" method="POST">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 25px;">
                    
                    <!-- SECCIÓN: DATOS DEL PROPIETARIO -->
                    <div class="table-card" style="padding: 25px;">
                        <h2 style="color: #115e59; margin-bottom: 20px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">
                            <i class="fa-solid fa-address-card"></i> Datos del Propietario (Dueño)
                        </h2>
                        
                        <div style="margin-bottom: 15px;">
                            <label style="display:block; margin-bottom:5px; font-weight:600;">Número de Documento / Cédula *</label>
                            <input type="text" name="documento" required placeholder="Ingrese documento de identidad"
                                   style="width:100%; padding:10px; border:1px solid #cbd5e0; border-radius:6px;">
                        </div>

                        <div style="margin-bottom: 15px;">
                            <label style="display:block; margin-bottom:5px; font-weight:600;">Nombre Completo *</label>
                            <input type="text" name="nombre_cliente" required placeholder="Nombre y apellidos del propietario"
                                   style="width:100%; padding:10px; border:1px solid #cbd5e0; border-radius:6px;">
                        </div>

                        <div style="margin-bottom: 15px;">
                            <label style="display:block; margin-bottom:5px; font-weight:600;">Teléfono de Contacto *</label>
                            <input type="tel" name="telefono" required placeholder="Ej: 3001234567"
                                   style="width:100%; padding:10px; border:1px solid #cbd5e0; border-radius:6px;">
                        </div>

                        <div style="margin-bottom: 15px;">
                            <label style="display:block; margin-bottom:5px; font-weight:600;">Correo Electrónico *</label>
                            <input type="email" name="correo" required placeholder="ejemplo@correo.com"
                                   style="width:100%; padding:10px; border:1px solid #cbd5e0; border-radius:6px;">
                        </div>
                        <small class="text-muted">* Si el documento ingresado ya existe, el sistema omitirá estos campos y asociará la mascota al propietario correspondiente de forma automática.</small>
                    </div>

                    <!-- SECCIÓN: DATOS DE LA MASCOTA -->
                    <div class="table-card" style="padding: 25px;">
                        <h2 style="color: #115e59; margin-bottom: 20px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">
                            <i class="fa-solid fa-paw"></i> Datos de la Mascota (Paciente)
                        </h2>

                        <div style="margin-bottom: 15px;">
                            <label style="display:block; margin-bottom:5px; font-weight:600;">Nombre de la Mascota *</label>
                            <input type="text" name="nombre_mascota" required placeholder="Nombre de la mascota"
                                   style="width:100%; padding:10px; border:1px solid #cbd5e0; border-radius:6px;">
                        </div>

                        <div style="margin-bottom: 15px;">
                            <label style="display:block; margin-bottom:5px; font-weight:600;">Especie *</label>
                            <select name="especie" required style="width:100%; padding:10px; border:1px solid #cbd5e0; border-radius:6px; background:white;">
                                <option value="">-- Seleccione --</option>
                                <option value="Canino">Canino (Perro)</option>
                                <option value="Felino">Felino (Gato)</option>
                                <option value="Ave">Ave</option>
                                <option value="Roedor">Roedor</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>

                        <div style="margin-bottom: 15px;">
                            <label style="display:block; margin-bottom:5px; font-weight:600;">Raza *</label>
                            <input type="text" name="raza" required placeholder="Ej: Criollo, Poodle, Siamés..."
                                   style="width:100%; padding:10px; border:1px solid #cbd5e0; border-radius:6px;">
                        </div>

                        <div style="margin-bottom: 15px;">
                            <label style="display:block; margin-bottom:5px; font-weight:600;">Edad / Tiempo Estimado *</label>
                            <input type="text" name="edad" required placeholder="Ej: 2 años, 5 meses"
                                   style="width:100%; padding:10px; border:1px solid #cbd5e0; border-radius:6px;">
                        </div>
                    </div>

                </div>

                <!-- BOTONERA DE ACCIÓN -->
                <div style="margin-top: 25px; display: flex; justify-content: flex-end; gap: 15px;">
                    <button type="reset" class="btn" style="background: #e2e8f0; color: #475569; padding: 12px 30px; border-radius: 6px; font-weight: bold; border:none; cursor:pointer;">
                        <i class="fa-solid fa-eraser"></i> Limpiar Formulario
                    </button>
                    <button type="submit" class="btn btn-add" style="margin:0; padding: 12px 40px; font-size: 1rem; border-radius: 6px;">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar Registro Completo
                    </button>
                </div>
            </form>

        </div>
    </main>

</body>
</html>