<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Empleado - Charly Cake</title>

    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <h1>Panel de Empleado</h1>

    <div class="tarjeta-producto">
        <h2>Pastel de Chocolate</h2>
        <p>Fecha de ingreso: 26/10/2026</p>
        
        <div class="control-cantidad">
            <label>Cantidad:</label>
            <input type="number" value="1" min="1" class="input-cantidad">
        </div>

        <div class="botones-accion">
            <button class="btn-venta" onclick="return confirm('¿Confirmas la venta de este producto?')">Vendido</button>
            <button class="btn-merma" onclick="return confirm('¿Confirmas la merma de este producto?')">Tirado</button>
            <button class="btn-devolucion" onclick="return confirm('¿Confirmas la devolucion de este producto?')">Devolucion</button>
        </div>
    </div>

</body>
</html>