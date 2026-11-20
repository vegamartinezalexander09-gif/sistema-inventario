<?php
// descontar_inventario.php
session_start();
require '../funciones/conecta.php';
$conexion = conecta();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_lote = (int)$_POST['id_lote'];
    $id_sucursal = (int)$_POST['id_sucursal'];
    
    date_default_timezone_set('America/Mexico_City');
    $fecha_venta = date('Y-m-d H:i:s');

    //Obtener datos del producto a partir del lote
    $query_info = "SELECT i.id_producto, p.precio 
                   FROM inventario_lotes i 
                   INNER JOIN productos p ON i.id_producto = p.id_producto 
                   WHERE i.id_lote = $id_lote";
    $resultado_info = mysqli_query($conexion, $query_info);
    
    if (mysqli_num_rows($resultado_info) > 0) {
        $info = mysqli_fetch_assoc($resultado_info);
        $id_producto = $info['id_producto'];
        $precio = $info['precio'];

        //Registrar la venta en el historial
        $query_venta = "INSERT INTO ventas_mostrador (id_sucursal, id_producto, precio, fecha_venta) 
                        VALUES ($id_sucursal, $id_producto, $precio, '$fecha_venta')";
        mysqli_query($conexion, $query_venta);

        //Restaral inventario fisico
        $query_restar = "UPDATE inventario_lotes SET cantidad = cantidad - 1 WHERE id_lote = $id_lote";
        mysqli_query($conexion, $query_restar);
        
        //Limpiar si llegp a cero
        $query_limpiar = "DELETE FROM inventario_lotes WHERE cantidad = 0 AND id_lote = $id_lote";
        mysqli_query($conexion, $query_limpiar);
    }

    header("Location: ../panel_sucursal.php?id=" . $id_sucursal);
    exit();
} else {
    header("Location: ../login.php");
    exit();
}
?>