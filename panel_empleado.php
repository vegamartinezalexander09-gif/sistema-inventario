<?php
//panel_emoleado.php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'empleado') {
    header("Location: login.php");
    exit();
}
if (isset($_GET['id']) && $_SESSION['id_sucursal'] != $_GET['id']) {
    header("Location: panel_empleado.php?id=" . $_SESSION['id_sucursal']);
    exit();
}

require 'funciones/conecta.php';
$conexion = conecta();
$id_sucursal = (int)$_GET['id'];

//Obtener nombre de la sucursal
$query_sucursal = "SELECT nombre FROM sucursales WHERE id_sucursal = $id_sucursal";
$resultado_sucursal = mysqli_query($conexion, $query_sucursal);
$sucursal = mysqli_fetch_assoc($resultado_sucursal);

//Consultar el inventario para las tarjetas
$query_inventario = "SELECT i.id_lote, p.nombre, p.precio, i.cantidad, i.fecha_ingreso 
                     FROM inventario_lotes i 
                     INNER JOIN productos p ON i.id_producto = p.id_producto 
                     WHERE i.id_sucursal = $id_sucursal
                     ORDER BY i.fecha_ingreso ASC, p.nombre ASC";
                     
$resultado_inventario = mysqli_query($conexion, $query_inventario);

//Consultar los pedidos pendientes para que el empleado pueda entregarlos
$query_pedidos = "SELECT * FROM pedidos_especiales 
                  WHERE id_sucursal = $id_sucursal 
                  AND estado = 'pendiente' 
                  ORDER BY fecha_entrega ASC";
$resultado_pedidos = mysqli_query($conexion, $query_pedidos);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Empleado - <?php echo $sucursal['nombre']; ?></title>
    <link rel="stylesheet" href="css/empleado.css">
</head>
<body style="padding: 20px; font-family: Arial, sans-serif;">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #ccc; padding-bottom: 10px;">
        <h1>Sucursal: <?php echo $sucursal['nombre']; ?></h1>
        <a href="logout.php" style="color: red; font-weight: bold; text-decoration: none;">[ Cerrar Sesión ]</a>
    </div>
    
    <!--Pedidos especiales-->
    <h2 style="margin-top: 30px;">Registrar Pedido Especial</h2>
    <div style="background-color: #f9f9f9; padding: 15px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 30px;">
        <form action="guardar_pedido.php" method="POST" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
            <input type="hidden" name="id_sucursal" value="<?php echo $id_sucursal; ?>">
            
            <div>
                <label>Nombre del Cliente:</label><br>
                <input type="text" name="nombre_cliente" required style="padding: 5px;">
            </div>
            <div>
                <label>Descripción y Detalles:</label><br>
                <input type="text" name="descripcion" required style="padding: 5px; width: 250px;">
            </div>
            <div>
                <label>Fecha y Hora de Entrega:</label><br>
                <input type="datetime-local" name="fecha_entrega" required style="padding: 5px;">
            </div>
            <div>
                <label>Costo Total ($):</label><br>
                <input type="number" step="0.01" name="precio_total" required style="padding: 5px; width: 100px;">
            </div>
            <div>
                <label>Anticipo Pagado ($):</label><br>
                <input type="number" step="0.01" name="anticipo" style="padding: 5px; width: 100px;">
            </div>
            <div>
                <button type="submit" style="padding: 8px 15px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Registrar Pedido</button>
            </div>
        </form>
    </div>

    <!-- Inventario de mostrador -->
    <h2>Inventario Disponible</h2>
    <div style="display: flex; flex-wrap: wrap; gap: 20px;">
        <?php
        if (mysqli_num_rows($resultado_inventario) > 0) {
            date_default_timezone_set('America/Mexico_City');
            $fecha_actual = new DateTime();

            while($fila = mysqli_fetch_assoc($resultado_inventario)) {
                
                $fecha_ingreso = new DateTime($fila['fecha_ingreso']);
                $diferencia = $fecha_ingreso->diff($fecha_actual);
                $dias = $diferencia->days;

                if ($dias <= 2) {
                    $color_fondo = "#d4edda"; 
                } elseif ($dias <= 4) {
                    $color_fondo = "#fff3cd"; 
                } else {
                    $color_fondo = "#f8d7da"; 
                }
        ?>
            <!-- Tarjeta con color dinámico -->
            <div class="tarjeta-producto" style="background-color: <?php echo $color_fondo; ?>; border: 1px solid #ccc; padding: 15px; border-radius: 8px; width: 220px; box-shadow: 2px 2px 8px rgba(0,0,0,0.1);">
                <h3 style="margin-top: 0;"><?php echo $fila['nombre']; ?></h3>
                <p style="font-size: 14px; color: #555;">Ingreso: <?php echo $fila['fecha_ingreso']; ?></p>
                <p style="font-size: 12px;">(<?php echo $dias; ?> días en vitrina)</p>
                <p style="font-weight: bold; color: blue;">Stock actual: <?php echo $fila['cantidad']; ?></p>
                
                <form action="procesar_inventario.php" method="POST" style="margin: 0;">
                    <input type="hidden" name="id_lote" value="<?php echo $fila['id_lote']; ?>">
                    <input type="hidden" name="id_sucursal" value="<?php echo $id_sucursal; ?>">
                    
                    <div class="control-cantidad" style="margin-bottom: 15px;">
                        <label style="font-weight: bold;">Cantidad:</label>
                        <input type="number" name="cantidad_accion" value="1" min="1" max="<?php echo $fila['cantidad']; ?>" class="input-cantidad" required style="width: 60px; padding: 5px;">
                    </div>

                    <!-- Botones con colores fuertes para sobresalir -->
                    <div class="botones-accion" style="display: flex; gap: 10px;">
                        <button type="submit" name="tipo_accion" value="venta" style="background-color: #28a745; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; font-weight: bold;" onclick="return confirm('¿Confirmas la venta?')">Vendido</button>
                        
                        <button type="submit" name="tipo_accion" value="merma" style="background-color: #dc3545; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; font-weight: bold;" onclick="return confirm('¿Confirmas la merma?')">Tirado</button>
                    </div>
                </form>
            </div>
        <?php 
            }
        } else {
            echo "<p>No hay inventario registrado en esta sucursal.</p>";
        }
        ?>
    </div>

    <!-- Pedidos pendientes -->
    <h2 style="margin-top: 40px;">Pedidos Especiales por Entregar</h2>
    <table border="1" cellpadding="8" style="border-collapse: collapse; width: 100%;">
        <tr style="background-color: #eee;">
            <th>Cliente</th>
            <th>Detalles</th>
            <th>Fecha de Entrega</th>
            <th>Total</th>
            <th>Anticipo</th>
            <th>Saldo Pendiente</th>
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
                echo "<td style='color: red; font-weight: bold;'>$" . $pedido['saldo_pendiente'] . "</td>";
                
                echo "<td>";
                echo "<form action='liquidar_pedido.php' method='POST' style='margin:0;'>";
                echo "<input type='hidden' name='id_pedido' value='" . $pedido['id_pedido'] . "'>";
                echo "<input type='hidden' name='id_sucursal' value='" . $id_sucursal . "'>";
                echo "<button type='submit' style='background-color: #17a2b8; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;'>Cobrar y Entregar</button>";
                echo "</form>";
                echo "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='7'>No hay pedidos pendientes por entregar.</td></tr>";
        }
        ?>
    </table>

</body>
</html>