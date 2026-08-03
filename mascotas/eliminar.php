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

// Aseguramos el ID de la URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    // Iniciamos transacción para limpiar dependencias sin romper la base de datos
    $conn->begin_transaction();

    try {
        // 1. Eliminamos citas de la mascota
        $sql_citas = "DELETE FROM cita WHERE MASCOTA_id_mascota = ?";
        $stmt_citas = $conn->prepare($sql_citas);
        $stmt_citas->bind_param("i", $id);
        $stmt_citas->execute();
        $stmt_citas->close();

        // 2. Eliminamos historial médico de la mascota
        $sql_historial = "DELETE FROM historial_medico WHERE MASCOTA_id_mascota = ?";
        $stmt_historial = $conn->prepare($sql_historial);
        $stmt_historial->bind_param("i", $id);
        $stmt_historial->execute();
        $stmt_historial->close();

        // 3. Finalmente, eliminamos la mascota
        $sql_mascota = "DELETE FROM MASCOTA WHERE id_mascota = ?";
        $stmt_mascota = $conn->prepare($sql_mascota);
        $stmt_mascota->bind_param("i", $id);
        $stmt_mascota->execute();
        $stmt_mascota->close();

        // Si todo funcionó, consolidamos los cambios
        $conn->commit();
        header("Location: index?status=deleted");
        exit();

    } catch (Exception $e) {
        // Si algo falla, revertimos para evitar corrupción de datos
        $conn->rollback();
        echo "Error al eliminar la mascota y sus registros asociados: " . $conn->error;
    }
} else {
    header("Location: index");
    exit();
}

?>