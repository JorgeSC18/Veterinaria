<?php

session_start();

// Validamos seguridad de acceso y rol
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login");
    exit();
}

if ($_SESSION['rol'] != 'admin') {
    echo("Acceso denegado.");
    exit();
}

include("../conexion.php");

// Recogemos y sanitizamos los datos entrantes
$id = isset($_POST['id_cita']) ? intval($_POST['id_cita']) : 0;
$fecha = $_POST['fecha'];
$hora = $_POST['hora'];
$motivo = $_POST['motivo'];
$mascota_id = isset($_POST['mascota_id']) ? intval($_POST['mascota_id']) : 0;
$veterinario_id = isset($_POST['veterinario_id']) ? intval($_POST['veterinario_id']) : 0;

if ($id > 0 && $mascota_id > 0 && $veterinario_id > 0) {
    // Consulta usando Prepared Statements con los nombres de columna exactos de tu DB
    $sql = "UPDATE CITA 
            SET fecha = ?, 
                hora = ?, 
                motivo = ?, 
                MASCOTA_id_mascota = ?, 
                VETERINARIO_id_veterinario = ? 
            WHERE id_cita = ?";
            
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        // "sssiii" -> 3 strings y 3 enteros
        $stmt->bind_param("sssiii", $fecha, $hora, $motivo, $mascota_id, $veterinario_id, $id);
        
        if ($stmt->execute()) {
            header("Location: index?status=updated");
            exit();
        } else {
            echo "Error al actualizar la cita: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error en la preparación: " . $conn->error;
    }
} else {
    echo "Datos del formulario inválidos.";
}

$conn->close();
?>