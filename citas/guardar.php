<?php

session_start();

// 1. Validamos que el usuario esté logueado y sea administrador antes de operar
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login");
    exit();
}

if ($_SESSION['rol'] != 'admin') {
    echo("Acceso denegado.");
    exit();
}

include("../conexion.php");

// 2. Recogemos los datos del formulario
$fecha = $_POST['fecha'];
$hora = $_POST['hora'];
$motivo = $_POST['motivo'];
$mascota_id = intval($_POST['mascota_id']); // Forzamos a entero por seguridad
$veterinario_id = intval($_POST['veterinario_id']); // Forzamos a entero por seguridad

// 3. Usamos Prepared Statements. Nota que ajusté MASCOTA_id_mascota en mayúsculas como tus JOINs
$sql = "INSERT INTO CITA (fecha, hora, motivo, MASCOTA_id_mascota, VETERINARIO_id_veterinario) 
        VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if ($stmt) {
    // "sssii" significa: string, string, string, integer, integer
    $stmt->bind_param("sssii", $fecha, $hora, $motivo, $mascota_id, $veterinario_id);
    
    if ($stmt->execute()) {
        header("Location: index?status=success");
        exit();
    } else {
        echo "Error al guardar la cita: " . $stmt->error;
    }
    
    $stmt->close();
} else {
    echo "Error en la preparación de la consulta: " . $conn->error;
}

$conn->close();
?>