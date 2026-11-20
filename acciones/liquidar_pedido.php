<?php
// liquidar_pedido.php
if ($_SESSION['rol'] == 'admin') {
    header("Location: ../panel_sucursal.php?id=" . $id_sucursal);
} else {
    header("Location: ../panel_empleado.php?id=" . $id_sucursal);
}
exit();

require '../funciones/conecta.php';
$conexion = conecta();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_pedido = (int)$_POST['id_pedido'];
    $id_sucursal = (int)$_POST['id_sucursal'];
    
    // Actualizar el saldo a 0 y cambiar el estado
    $query = "UPDATE pedidos_especiales 
              SET saldo_pendiente = 0, estado = 'entregado' 
              WHERE id_pedido = $id_pedido";

    if (mysqli_query($conexion, $query)) {
        header("Location: ../panel_sucursal.php?id=" . $id_sucursal);
        exit();
    } else {
        echo "Error actualizando el pedido: " . mysqli_error($conexion);
    }
} else {
    header("Location: ../administrador.php");
    exit();
}
?>