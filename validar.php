<?php

session_start();

include("conexion.php");

$correo = $_POST['correo'];
$contraseña = $_POST['contraseña'];

$sql = "SELECT * FROM USUARIO
        WHERE correo='$correo'
        AND contraseña='$contraseña'";

$resultado = $conn->query($sql);

if($resultado->num_rows > 0){

    $usuario = $resultado->fetch_assoc();

    $_SESSION['id_usuario'] = $usuario['id_usuario'];
    $_SESSION['nombre'] = $usuario['nombre'];
    $_SESSION['rol'] = $usuario['rol'];

    if($usuario['rol'] == 'admin') 
    {
        header("Location: dashboard_admin");
    }
    elseif($usuario['rol'] == 'veterinario') 
    {
        header("Location: dashboard_veterinario");
    }   
     elseif($usuario['rol'] == 'cliente') 
    {
        header("Location: dashboard_cliente");
    }   

}else{

    echo "Correo o contraseña incorrectos";

}

?>