<?php
// administrador.php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'admin') {
    header("Location: login.php");
    exit();
}
require 'funciones/conecta.php';
$conexion = conecta(); 

$query = "SELECT id_sucursal, nombre FROM sucursales";
$resultado = mysqli_query($conexion, $query);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administrador - Charly Cake</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <h1>Bienvenido Administrador</h1>
    <a href="logout.php" style="color: red; font-weight: bold;">[ Cerrar Sesión ]</a>

    <h2>Catálogo Global: Nuevo Producto</h2>
    <form action="guardar_producto.php" method="POST">
        <label for="nombre">Nombre (Ej. Gelatina de Mosaico):</label>
        <input type="text" name="nombre" id="nombre" required>
        
        <label for="precio">Precio Base ($):</label>
        <input type="number" name="precio" id="precio" step="0.01" min="1" required>
        
        <button type="submit">Guardar en Catálogo</button>
    </form>
    <hr>
    <h2>Selecciona una sucursal para operar:</h2>

    <div class="sucursales-container">
        <?php

        if ($resultado->num_rows > 0) {
            while($fila = $resultado->fetch_assoc()) {

                echo "<a href='panel_sucursal.php?id=" . $fila['id_sucursal'] . "'>";
                echo "<button>" . $fila['nombre'] . "</button>";
                echo "</a><br><br>";
            }
        } else {
            echo "<p>No hay sucursales registradas aún.</p>";
        }
        ?>
    </div>
</body>
</html>