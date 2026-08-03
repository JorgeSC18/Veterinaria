<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login");
    exit();
}
include("../conexion.php");

$id_usuario = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_usuario > 0) {
    // Iniciamos la transacción para el borrado seguro en cascada
    $conn->begin_transaction();
    try {
        // 1. Localizar el id_cliente real usando su id_usuario
        $sql_find = "SELECT id_cliente FROM CLIENTE WHERE USUARIO_id_usuario = ?";
        $stmt_find = $conn->prepare($sql_find);
        $stmt_find->bind_param("i", $id_usuario);
        $stmt_find->execute();
        $res_find = $stmt_find->get_result();
        $cli = $res_find->fetch_assoc();
        $stmt_find->close();

        if ($cli) {
            $id_cliente = $cli['id_cliente'];

            // 2. Eliminar primero las citas usando las mascotas del cliente
            $sql_citas = "DELETE FROM cita WHERE MASCOTA_id_mascota IN (SELECT id_mascota FROM MASCOTA WHERE CLIENTE_id_cliente = ?)";
            $stmt_citas = $conn->prepare($sql_citas);
            $stmt_citas->bind_param("i", $id_cliente);
            $stmt_citas->execute();
            $stmt_citas->close();

            // 3. Eliminar los historiales médicos usando las mascotas del cliente
            $sql_historial = "DELETE FROM historial_medico WHERE MASCOTA_id_mascota IN (SELECT id_mascota FROM MASCOTA WHERE CLIENTE_id_cliente = ?)";
            $stmt_historial = $conn->prepare($sql_historial);
            $stmt_historial->bind_param("i", $id_cliente);
            $stmt_historial->execute();
            $stmt_historial->close();

            // 4. Eliminar las mascotas asociadas a este cliente
            $sql_mascotas = "DELETE FROM MASCOTA WHERE CLIENTE_id_cliente = ?";
            $stmt_mascotas = $conn->prepare($sql_mascotas);
            $stmt_mascotas->bind_param("i", $id_cliente);
            $stmt_mascotas->execute();
            $stmt_mascotas->close();
        }

        // 5. Eliminar el registro de la tabla CLIENTE
        $sql_cli = "DELETE FROM CLIENTE WHERE USUARIO_id_usuario = ?";
        $stmt_cli = $conn->prepare($sql_cli);
        $stmt_cli->bind_param("i", $id_usuario);
        $stmt_cli->execute();
        $stmt_cli->close();

        // 6. Finalmente, eliminar su cuenta de acceso de la tabla USUARIO
        $sql_user = "DELETE FROM USUARIO WHERE id_usuario = ?";
        $stmt_user = $conn->prepare($sql_user);
        $stmt_user->bind_param("i", $id_usuario);
        $stmt_user->execute();
        $stmt_user->close();

        // Si todo se ejecutó sin errores, consolidamos los cambios
        $conn->commit();
        header("Location: index?status=deleted");
        exit();

    } catch (Exception $e) {
        // Si algo falla, revertimos para que la BD no quede con datos huérfanos
        $conn->rollback();
        echo "Error crítico al eliminar el cliente: " . $e->getMessage();
    }
} else {
    header("Location: index");
    exit();
}
?>