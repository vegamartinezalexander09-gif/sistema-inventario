<?php
// guardar_pedido.php
if ($_SESSION['rol'] == 'admin') {
    header("Location: ../panel_sucursal.php?id=" . $id_sucursal);
} else {
    header("Location: ../panel_empleado.php?id=" . $id_sucursal);
}
exit();

require '../funciones/conecta.php';
$conexion = conecta();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_sucursal = (int)$_POST['id_sucursal'];
    $nombre = mysqli_real_escape_string($conexion, trim($_POST['nombre_cliente']));
    $descripcion = mysqli_real_escape_string($conexion, trim($_POST['descripcion']));
    $fecha_entrega = $_POST['fecha_entrega'];
    $precio_total = (float)$_POST['precio_total'];
    $anticipo = (float)$_POST['anticipo'];
    
    // Calcular el saldo restante
    $saldo_pendiente = $precio_total - $anticipo;
    
    // Si ya se pago completo, pasa a entregado (para que sume en la venta de hoy)
    $estado = ($saldo_pendiente <= 0) ? 'entregado' : 'pendiente';

    $query = "INSERT INTO pedidos_especiales 
              (id_sucursal, nombre_cliente, descripcion, fecha_entrega, precio_total, saldo_pendiente, estado) 
              VALUES ($id_sucursal, '$nombre', '$descripcion', '$fecha_entrega', $precio_total, $saldo_pendiente, '$estado')";

    if (mysqli_query($conexion, $query)) {
        header("Location: ../panel_sucursal.php?id=" . $id_sucursal);
        exit();
    } else {
        echo "Error guardando el pedido: " . mysqli_error($conexion);
    }
} else {
    header("Location: ../administrador.php");
    exit();
}
?>