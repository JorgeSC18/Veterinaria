<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login");
    exit();
}
include("../conexion.php");

$id = $_POST['id_cliente'];
$cedula = $_POST['documento']; 
$direccion = $_POST['direccion'];
$telefono = $_POST['telefono'];

// Usamos Prepared Statements para proteger la consulta
// NOTA: Cambié 'cedula' por 'documento' porque así figuraba en tu index.php
$sql = "UPDATE CLIENTE 
        SET documento = ?, 
            direccion = ?, 
            telefono = ? 
        WHERE id_cliente = ?";

$stmt = $conn->prepare($sql);

// CORREGIDO: Ahora sí se envía el $telefono en el orden correcto (documento, direccion, telefono, id_cliente)
$stmt->bind_param("sssi", $cedula, $direccion, $telefono, $id);

if($stmt->execute()){
    $stmt->close();
    header("Location: index?status=updated");
    exit();
} else {
    echo "Error al actualizar cliente: " . $conn->error;
}
?>