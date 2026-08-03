<?php

include("../conexion.php");

$sql = "DELETE FROM HISTORIAL_MEDICO
        WHERE id_historial = " . $_GET['id'];

if($conn->query($sql)){         
    header("Location: index");
}else{
    echo "Error al eliminar";
}

?>