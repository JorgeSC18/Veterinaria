<?php
session_start();
include 'conexion.php'; 

// CONTROL DE ACCESO
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'cliente') {
    header("Location: login");
    exit();
}


$id_usuario = $_SESSION['id_usuario'];

// 1. Obtener el id_cliente asociado al usuario firmado
$sql_cliente = "SELECT id_cliente FROM CLIENTE WHERE USUARIO_id_usuario = '$id_usuario' LIMIT 1";
$res_cliente = $conn->query($sql_cliente);
$cliente_data = $res_cliente->fetch_assoc();
$id_cliente = $cliente_data['id_cliente'];

// Determinar qué sección/página interna renderizar
$page = isset($_GET['page']) ? $_GET['page'] : 'inicio';

$mensaje = "";
$tipo_mensaje = "";

// PROCESAR FORMULARIO: ACTUALIZAR PERFIL (INCLUYE CORREO AHORA)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['actualizar_perfil'])) {
    $nuevo_nombre = mysqli_real_escape_string($conn, $_POST['nombre']);
    $nuevo_correo = mysqli_real_escape_string($conn, $_POST['correo']);
    $nuevo_telefono = mysqli_real_escape_string($conn, $_POST['telefono']);
    $nueva_direccion = mysqli_real_escape_string($conn, $_POST['direccion']);

    // Actualizamos nombre y correo en la tabla USUARIO
    $sql_up_usuario = "UPDATE USUARIO SET nombre = '$nuevo_nombre', correo = '$nuevo_correo' WHERE id_usuario = '$id_usuario'";
    // Actualizamos teléfono y dirección en la tabla CLIENTE
    $sql_up_cliente = "UPDATE CLIENTE SET telefono = '$nuevo_telefono', direccion = '$nueva_direccion' WHERE id_cliente = '$id_cliente'";

    if ($conn->query($sql_up_usuario) && $conn->query($sql_up_cliente)) {
        $_SESSION['nombre'] = $nuevo_nombre; // Refrescar nombre en el menú lateral
        $mensaje = "Tu perfil y correo se han actualizado correctamente.";
        $tipo_mensaje = "success";
    } else {
        $mensaje = "Hubo un error al intentar guardar tus datos de perfil.";
        $tipo_mensaje = "error";
    }
}

// PROCESAR FORMULARIO: CAMBIAR CONTRASEÑA
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cambiar_password'])) {
    $clave_actual = mysqli_real_escape_string($conn, $_POST['clave_actual']);
    $clave_nueva = mysqli_real_escape_string($conn, $_POST['clave_nueva']);
    $clave_confirmar = mysqli_real_escape_string($conn, $_POST['clave_confirmar']);

    $sql_clv = "SELECT contrasena FROM USUARIO WHERE id_usuario = '$id_usuario' LIMIT 1";
    $res_clv = $conn->query($sql_clv);
    $usr_clv = $res_clv->fetch_assoc();

    if ($usr_clv['contrasena'] === $clave_actual) {
        if ($clave_nueva === $clave_confirmar) {
            $update_sql = "UPDATE USUARIO SET contrasena = '$clave_nueva' WHERE id_usuario = '$id_usuario'";
            if ($conn->query($update_sql)) {
                $mensaje = "Contraseña cambiada con éxito.";
                $tipo_mensaje = "success";
            } else {
                $mensaje = "Error de base de datos al cambiar la clave.";
                $tipo_mensaje = "error";
            }
        } else {
            $mensaje = "La confirmación no coincide con la nueva contraseña.";
            $tipo_mensaje = "error";
        }
    } else {
        $mensaje = "La contraseña actual es incorrecta.";
        $tipo_mensaje = "error";
    }
}

// Consultar los datos actualizados del usuario para rellenar los inputs del perfil
$sql_perfil = "SELECT u.nombre, u.correo, c.telefono, c.direccion 
               FROM USUARIO u 
               INNER JOIN CLIENTE c ON u.id_usuario = c.USUARIO_id_usuario 
               WHERE u.id_usuario = '$id_usuario' LIMIT 1";
$res_perfil = $conn->query($sql_perfil);
$datos_perfil = $res_perfil->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VetVital - Panel de Cliente</title>
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
        
        /* SIDEBAR */
        .sidebar { width: 260px; background: #ffffff; border-right: 1px solid var(--border-color); display: flex; flex-direction: column; justify-content: space-between; padding: 30px 20px; box-sizing: border-box; }
        .logo { font-size: 1.5rem; font-weight: bold; color: var(--brand-green); display: flex; align-items: center; gap: 10px; margin-bottom: 40px; }
        .menu-nav { list-style: none; padding: 0; margin: 0; }
        .menu-nav li { margin-bottom: 8px; }
        .menu-nav a { display: flex; align-items: center; gap: 12px; padding: 12px 15px; color: var(--text-muted); text-decoration: none; font-weight: 500; border-radius: 8px; transition: 0.2s; }
        .menu-nav a:hover, .menu-nav li.active a { background-color: var(--brand-green-light); color: var(--brand-green); }
        .sidebar-footer { border-top: 1px solid var(--border-color); padding-top: 20px; }
        .btn-logout { color: #ef4444; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 8px; }

        /* CONTENIDO GRAL */
        .main-content { flex: 1; padding: 40px; overflow-y: auto; box-sizing: border-box; }
        .welcome-card { background: #ffffff; border: 1px solid var(--border-color); border-radius: 12px; padding: 24px; margin-bottom: 30px; }
        .welcome-card h3 { margin: 0 0 8px 0; color: var(--brand-green); }
        
        /* COMPONENTES VISUALES */
        .grid-mascotas { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; }
        .card-mascota { background: white; border: 1px solid var(--border-color); border-radius: 10px; padding: 20px; text-align: center; }
        .card-mascota i { font-size: 2.5rem; color: var(--brand-green); margin-bottom: 10px; display: inline-block; }
        
        .data-table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; border: 1px solid var(--border-color); margin-bottom: 35px; }
        .data-table th, .data-table td { padding: 14px; text-align: left; border-bottom: 1px solid var(--border-color); }
        .data-table th { background-color: var(--brand-green-light); color: var(--brand-green); }
        
        .section-title { color: var(--brand-green); border-bottom: 2px solid var(--brand-green-light); padding-bottom: 8px; margin-top: 30px; margin-bottom: 15px; display: flex; align-items: center; gap: 10px; }

        /* FORMULARIOS */
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; color: var(--text-dark); }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; background: #fff; }
        .btn-submit { background: var(--brand-green); color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: 500; }
        .btn-submit:hover { background: #0f4e4a; }
        .alert { padding: 12px; border-radius: 6px; margin-bottom: 15px; font-weight: 500; }
        .alert-success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-top">
            <div class="logo"><i class="fa-solid fa-paw"></i> VetVital</div>
            <ul class="menu-nav">
                <li class="<?php echo $page == 'inicio' ? 'active' : ''; ?>"><a href="dashboard_cliente?page=inicio"><i class="fa-solid fa-house"></i> Inicio</a></li>
                <li class="<?php echo $page == 'mascotas' ? 'active' : ''; ?>"><a href="dashboard_cliente?page=mascotas"><i class="fa-solid fa-dog"></i> Mis Mascotas</a></li>
                <li class="<?php echo $page == 'citas' ? 'active' : ''; ?>"><a href="dashboard_cliente?page=citas"><i class="fa-solid fa-calendar-check"></i> Citas Médicas</a></li>
                <li class="<?php echo $page == 'historial' ? 'active' : ''; ?>"><a href="dashboard_cliente?page=historial"><i class="fa-solid fa-file-medical"></i> Historial Médico</a></li>
                <li class="<?php echo $page == 'perfil' ? 'active' : ''; ?>"><a href="dashboard_cliente?page=perfil"><i class="fa-solid fa-user-gear"></i> Mi Perfil</a></li>
                <li class="<?php echo $page == 'seguridad' ? 'active' : ''; ?>"><a href="dashboard_cliente?page=seguridad"><i class="fa-solid fa-lock"></i> Seguridad</a></li>
            </ul>
        </div>
        <div class="sidebar-footer">
            <div style="margin-bottom: 15px;">
                <h4 style="margin:0; color: var(--text-dark);"><?php echo htmlspecialchars($_SESSION['nombre']); ?></h4>
                <span style="font-size:0.8rem; color:var(--text-muted);">Cliente</span>
            </div>
            <a href="logout" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión</a>
        </div>
    </div>

    <div class="main-content">
        
        <?php if ($page == 'inicio'): ?>
            <div class="welcome-card">
                <h3>¡Bienvenido/a al portal de servicios, <?php echo htmlspecialchars($_SESSION['nombre']); ?>!</h3>
                <p>Monitorea los tratamientos médicos de tus mascotas, consulta programaciones de visitas o actualiza tus credenciales al instante.</p>
            </div>
            <div style="display: flex; gap: 20px;">
                <div class="welcome-card" style="flex: 1; text-align: center;">
                    <i class="fa-solid fa-paw" style="font-size: 2rem; color: var(--brand-green);"></i>
                    <h4>Revisar Historial Clínico</h4>
                    <a href="dashboard_cliente?page=historial" style="color: var(--brand-green); font-weight: 600; text-decoration:none;">Ir al Historial →</a>
                </div>
                <div class="welcome-card" style="flex: 1; text-align: center;">
                    <i class="fa-solid = fa-user" style="font-size: 2rem; color: var(--brand-green);"></i>
                    <h4>Administrar mi Cuenta</h4>
                    <a href="dashboard_cliente?page=perfil" style="color: var(--brand-green); font-weight: 600; text-decoration:none;">Modificar Perfil →</a>
                </div>
            </div>

        <?php elseif ($page == 'mascotas'): ?>
            <h2>Mis Mascotas Registradas</h2>
            <div class="grid-mascotas">
    <?php
    $sql_mascotas = "SELECT * FROM MASCOTA WHERE CLIENTE_id_cliente = '$id_cliente'";
    $res_mascotas = $conn->query($sql_mascotas);
    if ($res_mascotas->num_rows > 0) {
        while($pet = $res_mascotas->fetch_assoc()) {
            echo "<div class='card-mascota'>";
            
            // Aquí está el ícono de la huella aplicado para todos
            echo "<i class='fa-solid fa-paw'></i>"; 
            
            echo "<h3>".htmlspecialchars($pet['nombre'])."</h3>";
            echo "<p style='color:var(--text-muted); margin: 4px 0;'>".$pet['especie']." - ".$pet['raza']."</p>";
            echo "<p style='font-size: 0.9rem;'><b>Edad:</b> ".$pet['edad']." años | <b>Peso:</b> ".$pet['peso']." kg</p>";
            echo "</div>";
        }
    } else {
        echo "<p>No tienes mascotas en nuestra base de datos.</p>";
    }
    ?>
</div>

        <?php elseif ($page == 'citas'): ?>
            <h2>Próximas Citas Médicas Programadas</h2>
            <table class="data-table">
                <thead>
                    <tr><th>Paciente</th><th>Fecha de Cita</th><th>Horario</th><th>Motivo de Consulta</th></tr>
                </thead>
                <tbody>
                    <?php
                    $sql_citas = "SELECT c.fecha, c.hora, c.motivo, m.nombre AS mascota 
                                  FROM CITA c 
                                  INNER JOIN MASCOTA m ON c.MASCOTA_id_mascota = m.id_mascota 
                                  WHERE m.CLIENTE_id_cliente = '$id_cliente' ORDER BY c.fecha DESC";
                    $res_citas = $conn->query($sql_citas);
                    if ($res_citas->num_rows > 0) {
                        while($cita = $res_citas->fetch_assoc()) {
                            echo "<tr><td><b>".htmlspecialchars($cita['mascota'])."</b></td><td>".$cita['fecha']."</td><td>".$cita['hora']."</td><td>".htmlspecialchars($cita['motivo'])."</td></tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' style='text-align:center; color:var(--text-muted);'>No registras agendas médicas pendientes.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>

        <?php elseif ($page == 'historial'): ?>
            <h2>Historial Clínico de las Mascotas</h2>
            <p style="color: var(--text-muted); margin-bottom: 25px;">Aquí se despliegan las hojas de control médico subdivididas según el tipo de atención recibida en la veterinaria.</p>

            <?php
            // Inicializar contenedores de arreglos de categorías
            $vacunas = [];
            $desparasitaciones = [];
            $controles = [];

            // Consulta única uniendo el historial con los datos de especie y nombre de la mascota
            $sql_h = "SELECT h.*, m.nombre AS mascota_nombre, m.especie AS mascota_especie 
                      FROM HISTORIAL_MEDICO h
                      INNER JOIN MASCOTA m ON h.MASCOTA_id_mascota = m.id_mascota
                      WHERE m.CLIENTE_id_cliente = '$id_cliente'
                      ORDER BY h.fecha DESC";
            
            $res_h = $conn->query($sql_h);

            if ($res_h && $res_h->num_rows > 0) {
                while ($row = $res_h->fetch_assoc()) {
                    // Convertir texto a minúsculas para buscar palabras clave
                    $diag = mb_strtolower($row['diagnostico'], 'UTF-8');
                    $trat = mb_strtolower($row['tratamiento'], 'UTF-8');
                    $esp  = mb_strtolower($row['mascota_especie'], 'UTF-8');

                    // 1. CLASIFICACIÓN POR VACUNA
                    if (strpos($diag, 'vacuna') !== false || strpos($trat, 'vacuna') !== false) {
                        // Filtro de especie permitido para vacunas: perros, gatos, conejos
                        if (strpos($esp, 'perro') !== false || strpos($esp, 'gato') !== false || strpos($esp, 'conejo') !== false) {
                            $vacunas[] = $row;
                        }
                    } 
                    // 2. CLASIFICACIÓN POR DESPARASITACIÓN
                    elseif (strpos($diag, 'desparasit') !== false || strpos($trat, 'desparasit') !== false) {
                        // Filtro de especie permitido para desparasitación: perros, gatos
                        if (strpos($esp, 'perro') !== false || strpos($esp, 'gato') !== false) {
                            $desparasitaciones[] = $row;
                        }
                    } 
                    // 3. CLASIFICACIÓN POR CONTROL VETERINARIO (Todo lo demás o explícito)
                    else {
                        // Admite todas las especies (perros, gatos, conejos, tortugas, etc.)
                        $controles[] = $row;
                    }
                }
            }
            ?>

            <h3 class="section-title"><i class="fa-solid fa-syringe"></i> Control de Vacunación</h3>
            <table class="data-table">
                <thead>
                    <tr><th>Mascota / Especie</th><th>Fecha Aplicación</th><th>Diagnóstico/Detalle</th><th>Tratamiento/Dosis</th><th>Observaciones</th></tr>
                </thead>
                <tbody>
                    <?php if (count($vacunas) > 0): foreach ($vacunas as $v): ?>
                        <tr>
                            <td><b><?php echo htmlspecialchars($v['mascota_nombre']); ?></b><br><small style="color:var(--text-muted);"><?php echo $v['mascota_especie']; ?></small></td>
                            <td><?php echo $v['fecha']; ?></td>
                            <td><?php echo htmlspecialchars($v['diagnostico']); ?></td>
                            <td><?php echo htmlspecialchars($v['tratamiento']); ?></td>
                            <td><?php echo htmlspecialchars($v['observaciones']); ?></td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="5" style="text-align:center; color:var(--text-muted);">No hay reportes de vacunas registrados para tus mascotas aptas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <h3 class="section-title"><i class="fa-solid fa-droplet"></i> Desparasitación Interna/Externa</h3>
            <table class="data-table">
                <thead>
                    <tr><th>Mascota / Especie</th><th>Fecha</th><th>Diagnóstico</th><th>Tratamiento Aplicado</th><th>Observaciones</th></tr>
                </thead>
                <tbody>
                    <?php if (count($desparasitaciones) > 0): foreach ($desparasitaciones as $d): ?>
                        <tr>
                            <td><b><?php echo htmlspecialchars($d['mascota_nombre']); ?></b><br><small style="color:var(--text-muted);"><?php echo $d['mascota_especie']; ?></small></td>
                            <td><?php echo $d['fecha']; ?></td>
                            <td><?php echo htmlspecialchars($d['diagnostico']); ?></td>
                            <td><?php echo htmlspecialchars($d['tratamiento']); ?></td>
                            <td><?php echo htmlspecialchars($d['observaciones']); ?></td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="5" style="text-align:center; color:var(--text-muted);">No se encuentran registros recientes de desparasitación.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <h3 class="section-title"><i class="fa-solid fa-notes-medical"></i> Control Veterinario General</h3>
            <table class="data-table">
                <thead>
                    <tr><th>Mascota / Especie</th><th>Fecha Revisión</th><th>Diagnóstico Clínico</th><th>Tratamiento Recetado</th><th>Observaciones</th></tr>
                </thead>
                <tbody>
                    <?php if (count($controles) > 0): foreach ($controles as $c): ?>
                        <tr>
                            <td><b><?php echo htmlspecialchars($c['mascota_nombre']); ?></b><br><small style="color:var(--text-muted);"><?php echo $c['mascota_especie']; ?></small></td>
                            <td><?php echo $c['fecha']; ?></td>
                            <td><?php echo htmlspecialchars($c['diagnostico']); ?></td>
                            <td><?php echo htmlspecialchars($c['tratamiento']); ?></td>
                            <td><?php echo htmlspecialchars($c['observaciones']); ?></td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="5" style="text-align:center; color:var(--text-muted);">No hay consultas generales ni controles archivados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

        <?php elseif ($page == 'perfil'): ?>
            <h2>Mi Perfil De Usuario</h2>
            <div class="welcome-card" style="max-width: 550px;">
                <?php if(!empty($mensaje)): ?>
                    <div class="alert alert-<?php echo $tipo_mensaje; ?>"><?php echo $mensaje; ?></div>
                <?php endif; ?>
                
                <form action="dashboard_cliente?page=perfil" method="POST">
                    <div class="form-group">
                        <label>Nombre Completo</label>
                        <input type="text" name="nombre" value="<?php echo htmlspecialchars($datos_perfil['nombre']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Correo Electrónico (Editable)</label>
                        <input type="email" name="correo" value="<?php echo htmlspecialchars($datos_perfil['correo']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Teléfono de Contacto</label>
                        <input type="text" name="telefono" value="<?php echo htmlspecialchars($datos_perfil['telefono']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Dirección de Residencia</label>
                        <input type="text" name="direccion" value="<?php echo htmlspecialchars($datos_perfil['direccion']); ?>" required>
                    </div>
                    <button type="submit" name="actualizar_perfil" class="btn-submit"><i class="fa-solid fa-floppy-disk"></i> Guardar Cambios</button>
                </form>
            </div>

        <?php elseif ($page == 'seguridad'): ?>
            <h2>Configuración de Seguridad</h2>
            <div class="welcome-card" style="max-width: 500px;">
                <?php if(!empty($mensaje)): ?>
                    <div class="alert alert-<?php echo $tipo_mensaje; ?>"><?php echo $mensaje; ?></div>
                <?php endif; ?>
                <form action="dashboard_cliente?page=seguridad" method="POST">
                    <div class="form-group">
                        <label>Contraseña Actual</label>
                        <input type="password" name="clave_actual" required>
                    </div>
                    <div class="form-group">
                        <label>Nueva Contraseña</label>
                        <input type="password" name="clave_nueva" required>
                    </div>
                    <div class="form-group">
                        <label>Confirmar Nueva Contraseña</label>
                        <input type="password" name="clave_confirmar" required>
                    </div>
                    <button type="submit" name="cambiar_password" class="btn-submit">Actualizar Contraseña</button>
                </form>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>