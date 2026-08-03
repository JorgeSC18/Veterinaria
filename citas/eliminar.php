<?php

session_start();

// Validamos seguridad de acceso
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login");
    exit();
}

if ($_SESSION['rol'] != 'admin') {
    echo("Acceso denegado.");
    exit();
}

include("../conexion.php");

// Forzamos a que el ID sea un número entero limpio
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    $sql = "DELETE FROM CITA WHERE id_cita = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            header("Location: index?status=deleted");
            exit();
        } else {
            echo "Error al eliminar la cita: " . $stmt->error;
        }
        
        $stmt->close();
    } else {
        echo "Error en la preparación de la consulta: " . $conn->error;
    }
} else {
    header("Location: index");
    exit();
}

$conn->close();
?>