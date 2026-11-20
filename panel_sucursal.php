<?php
//panel_sucursal.php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id']) && $_SESSION['rol'] == 'empleado' && $_SESSION['id_sucursal'] != $_GET['id']) {
    header("Location: panel_sucursal.php?id=" . $_SESSION['id_sucursal']);
    exit();
}

require 'funciones/conecta.php';
$conexion = conecta();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: administrador.php");
    exit();
}

$id_sucursal = $_GET['id'];

$query_sucursal = "SELECT nombre FROM sucursales WHERE id_sucursal = $id_sucursal";
$resultado_sucursal = mysqli_query($conexion, $query_sucursal);
$sucursal = mysqli_fetch_assoc($resultado_sucursal);
// Consultar el inventario
$query_inventario = "SELECT i.id_lote, p.nombre, p.precio, i.cantidad, i.fecha_ingreso 
                     FROM inventario_lotes i 
                     INNER JOIN productos p ON i.id_producto = p.id_producto 
                     WHERE i.id_sucursal = $id_sucursal
                     ORDER BY i.fecha_ingreso ASC, p.nombre ASC";
$resultado_inventario = mysqli_query($conexion, $query_inventario);
//Consultar el catálogo de productos para el menú desplegable
$query_productos = "SELECT id_producto, nombre FROM productos WHERE eliminado = 0 ORDER BY nombre ASC";
$resultado_productos = mysqli_query($conexion, $query_productos);

//Calcular las ventas de hoy
date_default_timezone_set('America/Mexico_City');
$fecha_hoy = date('Y-m-d');

// Sumar pedidos especiales entregados hoy
$query_ventas_pedidos = "SELECT SUM(precio_total) as total_pedidos 
                         FROM pedidos_especiales 
                         WHERE id_sucursal = $id_sucursal 
                         AND DATE(fecha_entrega) = '$fecha_hoy' 
                         AND estado = 'entregado'";
$resultado_ventas_pedidos = mysqli_query($conexion, $query_ventas_pedidos);
$datos_pedidos = mysqli_fetch_assoc($resultado_ventas_pedidos);
$total_pedidos = $datos_pedidos['total_pedidos'] ? $datos_pedidos['total_pedidos'] : 0.00;

//Sumar ventas de mostrador de hoy
$query_ventas_mostrador = "SELECT SUM(precio) as total_mostrador 
                           FROM ventas_mostrador 
                           WHERE id_sucursal = $id_sucursal 
                           AND DATE(fecha_venta) = '$fecha_hoy'";
$resultado_ventas_mostrador = mysqli_query($conexion, $query_ventas_mostrador);
$datos_mostrador = mysqli_fetch_assoc($resultado_ventas_mostrador);
$total_mostrador = $datos_mostrador['total_mostrador'] ? $datos_mostrador['total_mostrador'] : 0.00;

//Total general
$venta_hoy = $total_pedidos + $total_mostrador;

//Consultar solo los pedidos pendientes
$query_pedidos = "SELECT * FROM pedidos_especiales 
                  WHERE id_sucursal = $id_sucursal 
                  AND estado = 'pendiente' 
                  ORDER BY fecha_entrega ASC";
$resultado_pedidos = mysqli_query($conexion, $query_pedidos) or die("Error SQL en pedidos: " . mysqli_error($conexion));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel - <?php echo $sucursal['nombre']; ?></title>
</head>
<body>
    <h1>Sucursal: <?php echo $sucursal['nombre']; ?></h1>
    <a href="administrador.php">⬅ Volver a sucursales</a>
    <hr>

    <h2>Resumen de Operación</h2>
    <?php if ($_SESSION['rol'] == 'admin') { ?>
    <div style="background-color: #d4edda; padding: 15px; border: 1px solid #c3e6cb; margin-bottom: 20px;">
        <h3>Ventas Totales de Hoy: $<?php echo number_format($venta_hoy, 2); ?></h3>
        <small>(Mostrador: $<?php echo number_format($total_mostrador, 2); ?> | Pedidos Especiales: $<?php echo number_format($total_pedidos, 2); ?>)</small>
    </div>
<?php } ?>
    <hr>

    <!-- Formulario para Agregar Inventario -->
    <h2>Agregar Producto al Inventario</h2>
    <form action="guardar_inventario.php" method="POST">
        <input type="hidden" name="id_sucursal" value="<?php echo $id_sucursal; ?>">
        
        <label for="id_producto">Producto:</label>
        <select name="id_producto" id="id_producto" required>
            <option value="">-- Selecciona un producto --</option>
            <?php
            if (mysqli_num_rows($resultado_productos) > 0) {
                while($prod = mysqli_fetch_assoc($resultado_productos)) {
                    echo "<option value='" . $prod['id_producto'] . "'>" . $prod['nombre'] . "</option>";
                }
            }
            ?>
        </select>
        
        <label for="cantidad">Cantidad:</label>
        <input type="number" name="cantidad" id="cantidad" min="1" required>
        
        <button type="submit">Agregar al Inventario</button>
    </form>
    <hr>

    <!--Pedidos Especiales -->
    <h2>Registrar Pedido Especial</h2>
    <form action="guardar_pedido.php" method="POST">
        <input type="hidden" name="id_sucursal" value="<?php echo $id_sucursal; ?>">
        
        <label>Nombre del Cliente:</label> 
        <input type="text" name="nombre_cliente" required>
        
        <label>Descripción y Detalles (Diseño, sabor, etc.):</label> 
        <textarea name="descripcion" required></textarea>
        
        <label>Fecha de Entrega:</label> 
        <input type="datetime-local" name="fecha_entrega" required>
        
        <label>Costo Total ($):</label> 
        <input type="number" name="precio_total" step="0.01" min="1" required>
        
        <label>Anticipo Pagado ($):</label> 
        <input type="number" name="anticipo" step="0.01" min="0" required>
        
        <button type="submit">Registrar Pedido</button>
    </form>
    <hr>

    <h2>Inventario Disponible</h2>
<table border="1" cellpadding="8">
        <tr>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Precio Unitario</th>
            <th>Fecha de Ingreso</th>
            <th>Días en Vitrina</th>
            <th>Estado</th>
        </tr>
        <?php
        if (mysqli_num_rows($resultado_inventario) > 0) {
            date_default_timezone_set('America/Mexico_City');
            $fecha_actual = new DateTime();

            while($fila = mysqli_fetch_assoc($resultado_inventario)) {

                $fecha_ingreso = new DateTime($fila['fecha_ingreso']);
                $diferencia = $fecha_ingreso->diff($fecha_actual);
                $dias = $diferencia->days;

                if ($dias <= 2) {
                    $color = "#d4edda";
                    $texto_estado = "Fresco";
                } elseif ($dias <= 4) {
                    $color = "#fff3cd";
                    $texto_estado = "Vender pronto";
                } else {
                    $color = "#f8d7da"; 
                    $texto_estado = "Merma";
                }

                echo "<tr style='background-color: $color;'>";
                echo "<td>" . $fila['nombre'] . "</td>";
                echo "<td>" . $fila['cantidad'] . "</td>";
                echo "<td>$" . $fila['precio'] . "</td>";
                echo "<td>" . $fila['fecha_ingreso'] . "</td>";
                echo "<td>" . $dias . " días</td>";
                echo "<td>" . $texto_estado . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='6'>No hay inventario registrado en esta sucursal.</td></tr>";
        }
        ?>
    </table>

    <a href="historial_pedidos.php?id=<?php echo $id_sucursal; ?>">Ver Historial de Pedidos Entregados</a>
    <br><br>

    <h2>Lista de Pedidos Especiales</h2>
    <table border="1" cellpadding="8">
        <tr>
            <th>Cliente</th>
            <th>Detalles</th>
            <th>Fecha de Entrega</th>
            <th>Total</th>
            <th>Anticipo</th>
            <th>Saldo Pendiente</th>
            <th>Estado</th>
            <th>Acción</th>
        </tr>
        <?php
        if (mysqli_num_rows($resultado_pedidos) > 0) {
            while($pedido = mysqli_fetch_assoc($resultado_pedidos)) {
                $anticipo_pagado = $pedido['precio_total'] - $pedido['saldo_pendiente'];
                
                echo "<tr>";
                echo "<td>" . $pedido['nombre_cliente'] . "</td>";
                echo "<td>" . $pedido['descripcion'] . "</td>";
                echo "<td>" . $pedido['fecha_entrega'] . "</td>";
                echo "<td>$" . $pedido['precio_total'] . "</td>";
                echo "<td>$" . number_format($anticipo_pagado, 2) . "</td>";
                echo "<td>$" . $pedido['saldo_pendiente'] . "</td>";
                echo "<td>" . strtoupper($pedido['estado']) . "</td>";
                
                echo "<td>";
                if ($pedido['estado'] == 'pendiente') {
                    echo "<form action='liquidar_pedido.php' method='POST' style='margin:0;'>";
                    echo "<input type='hidden' name='id_pedido' value='" . $pedido['id_pedido'] . "'>";
                    echo "<input type='hidden' name='id_sucursal' value='" . $id_sucursal . "'>";
                    echo "<button type='submit'>Liquidar</button>";
                    echo "</form>";
                }
                echo "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='8'>No hay pedidos pendientes.</td></tr>";
        }
        ?>
    </table>
</body>
</html>