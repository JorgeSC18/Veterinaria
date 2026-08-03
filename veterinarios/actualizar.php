<?php

include("../conexion.php");

$id = $_POST['id_veterinario'];
$especialidad = $_POST['especialidad'];
$telefono = $_POST['telefono'];

// Convertimos a Prepared Statement para evitar inyecciones SQL y mantener tu estilo limpio
$sql = "UPDATE VETERINARIO
        SET especialidad = ?,
            telefono = ?
        WHERE id_veterinario = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $especialidad, $telefono, $id);

if($stmt->execute()){
    $stmt->close();
    header("Location: index?status=updated");
    exit();
} else {
    echo "Error al actualizar: " . $conn->error; 
}   

?>