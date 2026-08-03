PHP
<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login");
    exit();
}
include("../conexion.php");

$id_usuario = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_usuario > 0) {
    $conn->begin_transaction();
    try {
        // 1. Buscar el id_veterinario usando el id_usuario antes de borrar
        $sql_find = "SELECT id_veterinario FROM VETERINARIO WHERE USUARIO_id_usuario = ?";
        $stmt_find = $conn->prepare($sql_find);
        $stmt_find->bind_param("i", $id_usuario);
        $stmt_find->execute();
        $res_find = $stmt_find->get_result();
        $vet = $res_find->fetch_assoc();
        $stmt_find->close();

        // Si encontramos al veterinario, limpiamos sus citas pendientes primero
        if ($vet) {
            $id_veterinario = $vet['id_veterinario'];

            // 2. Eliminar las citas asociadas a este médico (Evita el error de Foreign Key)
            // Nota: Se usa 'cita' en minúsculas tal como lo reportó el error de tu MySQL
            $sql_citas = "DELETE FROM cita WHERE VETERINARIO_id_veterinario = ?";
            $stmt_citas = $conn->prepare($sql_citas);
            $stmt_citas->bind_param("i", $id_veterinario);
            $stmt_citas->execute();
            $stmt_citas->close();
        }

        // 3. Ahora sí, eliminar de la tabla hija (VETERINARIO)
        $sql_vet = "DELETE FROM VETERINARIO WHERE USUARIO_id_usuario = ?";
        $stmt_vet = $conn->prepare($sql_vet);
        $stmt_vet->bind_param("i", $id_usuario);
        $stmt_vet->execute();
        $stmt_vet->close();

        // 4. Finalmente, eliminar de la tabla padre (USUARIO)
        $sql_user = "DELETE FROM USUARIO WHERE id_usuario = ?";
        $stmt_user = $conn->prepare($sql_user);
        $stmt_user->bind_param("i", $id_usuario);
        $stmt_user->execute();
        $stmt_user->close();

        // Confirmamos la transacción completa
        $conn->commit();
        header("Location: index?status=deleted");
        exit();
    } catch (Exception $e) {
        // Si algo falla, revertimos para proteger la base de datos
        $conn->rollback();
        echo "Error al eliminar el registro: " . $e->getMessage();
    }
} else {
    header("Location: index");
    exit();
}
?>