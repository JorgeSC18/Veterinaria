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

$documento = $_POST['documento'];
$telefono = $_POST['telefono'];
$direccion = $_POST['direccion'];
$usuario_id = $_POST['usuario_id'];

$sql = "INSERT INTO CLIENTE
(documento, telefono, direccion, USUARIO_id_usuario)

VALUES

('$documento',
 '$telefono',
 '$direccion',
 '$usuario_id')";

if($conn->query($sql)){
    header("Location: index");
}else{
    echo "Error: " . $conn->error;
}

?>