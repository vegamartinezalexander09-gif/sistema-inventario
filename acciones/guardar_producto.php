<?php
// guardar_producto.php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login.php");
    exit();
}

require '../funciones/conecta.php';
$conexion = conecta();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Escapar el texto para evitar errores con comillas o caracteres especiales
    $nombre = mysqli_real_escape_string($conexion, trim($_POST['nombre']));
    $precio = (float)$_POST['precio'];
    
    // Insertar en el catalogo global
    $query = "INSERT INTO productos (nombre, precio) VALUES ('$nombre', $precio)";

    if (mysqli_query($conexion, $query)) {
        header("Location: ../administrador.php");
        exit();
    } else {
        echo "Error guardando el producto: " . mysqli_error($conexion);
    }
} else {
    header("Location: ../administrador.php");
    exit();
}
?>