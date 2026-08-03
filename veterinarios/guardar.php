<?php

include("../conexion.php");


$especialidad = $_POST['especialidad'];
$telefono = $_POST['telefono'];
$usuario_id = $_POST['usuario_id'];

$sql = "INSERT INTO VETERINARIO
(especialidad, telefono, USUARIO_id_usuario)

VALUES 

('$especialidad', 
'$telefono', 
'$usuario_id')";

if($conn->query($sql)){
    header("Location: index");
} else {
    echo "Error al guardar: " . $conn->error;
}

?>