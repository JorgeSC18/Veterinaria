<?php

include("../conexion.php");

$id = $_POST['id_historial'];
$fecha = $_POST['fecha'];
$diagnostico = $_POST['diagnostico'];
$tratamiento = $_POST['tratamiento'];
$observaciones = $_POST['observaciones'];

$sql = "UPDATE HISTORIAL_MEDICO
        SET fecha='$fecha',
            diagnostico='$diagnostico',
            tratamiento='$tratamiento',
            observaciones='$observaciones'
        WHERE id_historial=$id";

if($conn->query($sql))
{
    header("Location: index");
}
else
{
    echo "Error al actualizar";
}

?>