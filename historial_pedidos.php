<?php
//historial de pedidos.php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'admin') {
    header("Location: login.php");
    exit();
}
require 'funciones/conecta.php';
$conexion = conecta();

date_default_timezone_set('America/Mexico_City');
$mes_consulta = isset($_GET['mes']) ? $_GET['mes'] : date('Y-m');

//Calcular ingresos del mes seleccionado
$q_pedidos_mes = "SELECT SUM(precio_total) as total FROM pedidos_especiales WHERE DATE_FORMAT(fecha_entrega, '%Y-%m') = '$mes_consulta' AND estado = 'entregado'";
$r_pedidos_mes = mysqli_query($conexion, $q_pedidos_mes);
$total_pedidos_mes = mysqli_fetch_assoc($r_pedidos_mes)['total'] ?? 0;

$q_mostrador_mes = "SELECT SUM(precio) as total FROM ventas_mostrador WHERE DATE_FORMAT(fecha_venta, '%Y-%m') = '$mes_consulta'";
$r_mostrador_mes = mysqli_query($conexion, $q_mostrador_mes);
$total_mostrador_mes = mysqli_fetch_assoc($r_mostrador_mes)['total'] ?? 0;

$ingreso_mensual = $total_pedidos_mes + $total_mostrador_mes;

//Obtener la agenda de pedidos del mes seleccionado
$q_agenda = "SELECT * FROM pedidos_especiales WHERE DATE_FORMAT(fecha_entrega, '%Y-%m') = '$mes_consulta' ORDER BY fecha_entrega ASC";
$r_agenda = mysqli_query($conexion, $q_agenda);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial Mensual - Charly Cake</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body style="padding: 20px; font-family: Arial, sans-serif;">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #ccc; padding-bottom: 10px;">
        <h1>Historial y Finanzas Mensuales</h1>
        <a href="administrador.php" style="background-color: #6c757d; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;">⬅ Volver al Panel de Hoy</a>
    </div>

    <!-- Buscador / Selector de Mes -->
    <form action="historial_pedidos.php" method="GET" style="margin-top: 20px; background: #f8f9fa; padding: 15px; border: 1px solid #ddd; border-radius: 8px;">
        <label style="font-weight: bold; margin-right: 10px;">Consultar otro mes:</label>
        <input type="month" name="mes" value="<?php echo $mes_consulta; ?>" style="padding: 5px; font-size: 16px;">
        <button type="submit" style="padding: 6px 15px; background-color: #007bff; color: white; border: none; border-radius: 4px; margin-left: 10px; cursor: pointer;">Buscar</button>
    </form>

    <div style="background-color: #e2e3e5; padding: 20px; border-radius: 8px; margin-top: 30px;">
        <h2 style="margin-top: 0; color: #333;">📊 Resumen del Mes (<?php echo $mes_consulta; ?>)</h2>
        <h2 style="color: #28a745; margin-bottom: 5px;">Ingreso Bruto: $<?php echo number_format($ingreso_mensual, 2); ?></h2>
        <p style="margin-top: 0; color: #555;">(Ventas de Mostrador: $<?php echo number_format($total_mostrador_mes, 2); ?> | Pedidos Entregados: $<?php echo number_format($total_pedidos_mes, 2); ?>)</p>
        
        <h3 style="margin-top: 30px;">📅 Agenda de Pedidos</h3>
        <table border="1" cellpadding="8" style="border-collapse: collapse; width: 100%; background-color: white;">
            <tr style="background-color: #343a40; color: white;">
                <th>Fecha y Hora</th>
                <th>Cliente</th>
                <th>Detalles</th>
                <th>Sucursal ID</th>
                <th>Estado</th>
            </tr>
            <?php
            if (mysqli_num_rows($r_agenda) > 0) {
                while($cita = mysqli_fetch_assoc($r_agenda)) {
                    $color_estado = ($cita['estado'] == 'pendiente') ? 'red' : 'green';
                    
                    echo "<tr>";
                    echo "<td><strong>" . date('d/m/Y h:i A', strtotime($cita['fecha_entrega'])) . "</strong></td>";
                    echo "<td>" . $cita['nombre_cliente'] . "</td>";
                    echo "<td>" . $cita['descripcion'] . "</td>";
                    echo "<td>" . $cita['id_sucursal'] . "</td>";
                    echo "<td style='color: $color_estado; font-weight: bold; text-transform: uppercase;'>" . $cita['estado'] . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No hay pedidos registrados para este mes.</td></tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>