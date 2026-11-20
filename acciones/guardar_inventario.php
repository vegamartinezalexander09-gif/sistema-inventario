<?php
//guardar_inventario.php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login.php");
    exit();
}

require '../funciones/conecta.php';
$conexion = conecta();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_sucursal = (int)$_POST['id_sucursal'];
    $id_producto = (int)$_POST['id_producto'];
    $cantidad = (int)$_POST['cantidad'];
    
    date_default_timezone_set('America/Mexico_City'); 
    $fecha_ingreso = date('Y-m-d H:i:s');

    $query = "INSERT INTO inventario_lotes (id_sucursal, id_producto, cantidad, fecha_ingreso) 
              VALUES ($id_sucursal, $id_producto, $cantidad, '$fecha_ingreso')";

    if (mysqli_query($conexion, $query)) {
        header("Location: ../panel_sucursal.php?id=" . $id_sucursal);
        exit();
    } else {
        echo "Error guardando el lote: " . mysqli_error($conexion);
    }
} else {
    header("Location: ../administrador.php");
    exit();
}
?>