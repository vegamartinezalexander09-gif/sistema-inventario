<?php
// procesar_inventario.php
session_start();
require 'funciones/conecta.php';
$conexion = conecta();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_lote = (int)$_POST['id_lote'];
    $id_sucursal = (int)$_POST['id_sucursal'];
    $cantidad_accion = (int)$_POST['cantidad_accion'];
    $tipo_accion = $_POST['tipo_accion']; 
    
    date_default_timezone_set('America/Mexico_City');
    $fecha_actual = date('Y-m-d H:i:s');

    //Obtener datos del producto
    $query_info = "SELECT i.id_producto, p.precio, i.cantidad 
                   FROM inventario_lotes i 
                   INNER JOIN productos p ON i.id_producto = p.id_producto 
                   WHERE i.id_lote = $id_lote";
    $resultado_info = mysqli_query($conexion, $query_info);
    
    if (mysqli_num_rows($resultado_info) > 0) {
        $info = mysqli_fetch_assoc($resultado_info);
        $id_producto = $info['id_producto'];
        
        //Evitar que el sistema reste mas productos de los que existen
        if ($cantidad_accion > $info['cantidad']) {
            $cantidad_accion = $info['cantidad']; 
        }

        //Si la accion fue VENTA, calcular el total y guardarlo en ventas_mostrador
        if ($tipo_accion == 'venta') {
            $precio_total = $info['precio'] * $cantidad_accion;
            $query_venta = "INSERT INTO ventas_mostrador (id_sucursal, id_producto, precio, fecha_venta) 
                            VALUES ($id_sucursal, $id_producto, $precio_total, '$fecha_actual')";
            mysqli_query($conexion, $query_venta);
        }

        //Restar la cantidad ingresada al inventario fisico
        $query_restar = "UPDATE inventario_lotes 
                         SET cantidad = cantidad - $cantidad_accion 
                         WHERE id_lote = $id_lote";
        mysqli_query($conexion, $query_restar);
        
        //Limpiar el lote si llego a 0
        $query_limpiar = "DELETE FROM inventario_lotes WHERE cantidad <= 0 AND id_lote = $id_lote";
        mysqli_query($conexion, $query_limpiar);
    }

    if ($_SESSION['rol'] == 'admin') {
        // Si haces una venta, regresas a la vista administrativa
        header("Location: panel_sucursal.php?id=" . $id_sucursal); 
    } else {
        // Si el empleado hace una venta, regresa a su vista de tarjetas
        header("Location: panel_empleado.php?id=" . $id_sucursal);
    }
    exit();
} else {
    header("Location: login.php");
    exit();
}
?>