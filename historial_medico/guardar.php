<?php

include("../conexion.php");

$fecha = $_POST['fecha'];
$diagnostico = $_POST['diagnostico'];
$tratamiento = $_POST['tratamiento'];
$observaciones = $_POST['observaciones'];
$mascota_id = $_POST['mascota_id'];

$sql = "INSERT INTO HISTORIAL_MEDICO
(fecha, diagnostico, tratamiento, observaciones, MASCOTA_id_mascota)

VALUES 

('$fecha',
 '$diagnostico',
 '$tratamiento',
  '$observaciones',
   $mascota_id)";

if($conn->query($sql)){
    header("Location: index");
} else {
    echo "Error al guardar: " . $conn->error;
}

?>