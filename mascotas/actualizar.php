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

// Recogemos el ID de la mascota y sus datos modificados
$id_mascota = intval($_POST['id_mascota']);
$nombre = $_POST['nombre'];
$especie = $_POST['especie'];
$raza = $_POST['raza'];
$edad = intval($_POST['edad']);
$sexo = $_POST['sexo'];
$peso = floatval($_POST['peso']);

// Preparamos la actualización estructurada
$sql = "UPDATE MASCOTA 
        SET nombre = ?, 
            especie = ?, 
            raza = ?, 
            edad = ?, 
            sexo = ?, 
            peso = ? 
        WHERE id_mascota = ?";

$stmt = $conn->prepare($sql);

// nombre(s), especie(s), raza(s), edad(i), sexo(s), peso(d), id_mascota(i)
$stmt->bind_param("sssisdi", $nombre, $especie, $raza, $edad, $sexo, $peso, $id_mascota);

if($stmt->execute()){
    $stmt->close();
    header("Location: index?status=updated");
    exit();
} else {
    echo "Error al actualizar la mascota: " . $conn->error;
}

?>