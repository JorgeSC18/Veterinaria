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

$nombre = $_POST['nombre'];
$especie = $_POST['especie'];
$raza = $_POST['raza'];
$edad = intval($_POST['edad']);
$sexo = $_POST['sexo'];
$peso = floatval($_POST['peso']);
$cliente_id = intval($_POST['cliente_id']);

// Cambiamos a Prepared Statement seguro
$sql = "INSERT INTO MASCOTA (nombre, especie, raza, edad, sexo, peso, CLIENTE_id_cliente)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

// s = string, i = entero, d = doble/decimal
// nombre(s), especie(s), raza(s), edad(i), sexo(s), peso(d), cliente_id(i)
$stmt->bind_param("sssisdi", $nombre, $especie, $raza, $edad, $sexo, $peso, $cliente_id);

if($stmt->execute()){
    $stmt->close();
    header("Location: index?status=created");
    exit();
} else {
    echo "Error al guardar la mascota: " . $conn->error;
}

?>