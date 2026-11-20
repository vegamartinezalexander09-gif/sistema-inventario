<?php
// login.php
session_start();
require 'funciones/conecta.php';
$conexion = conecta();

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = mysqli_real_escape_string($conexion, trim($_POST['usuario']));
    $password = mysqli_real_escape_string($conexion, trim($_POST['contrasena']));

    $query = "SELECT id_usuario, rol, id_sucursal, password FROM usuarios WHERE nombre = '$usuario'";
    $resultado = mysqli_query($conexion, $query);

    if (mysqli_num_rows($resultado) > 0) {
        $datos = mysqli_fetch_assoc($resultado);
        
        if ($password == $datos['password']) {
        
            $_SESSION['id_usuario'] = $datos['id_usuario'];
            $_SESSION['rol'] = $datos['rol'];
            $_SESSION['id_sucursal'] = $datos['id_sucursal'];

            if ($datos['rol'] == 'admin') {
                header("Location: administrador.php");
            } else {
                header("Location: panel_empleado.php?id=" . $datos['id_sucursal']);
            }
            exit();
        } else {
            $error = "Contraseña incorrecta.";
        }
    } else {
        $error = "El usuario no existe.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <title>Charly Cake</title>
        <link rel="stylesheet" href="css/empleado.css">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    </head>
    <body class="vista-login">
        
        <form id="form01" name="form01" action="login.php" method="post">
        <h2 style="text-align: center; color: #6a3d9a; margin-top: 0; margin-bottom: 20px;">🧁 Charly Cake</h2>
            <label for="usuario">Usuario:</label><br>
            <input type="text" name="usuario" id="usuario">
            <br>
            
            <label for="contrasena">Contraseña:</label><br>
            <input type="password" name="contrasena" id="contrasena">
            <br><br>
            
            <button id="btnEnviar" name="btnEnviar" type="submit">Iniciar sesión</button>
            
        </form>

        <br>
        <div name="resultado" id="resultado" style="color: red; font-weight: bold;">
            <?php if($error != "") echo $error; ?>
        </div>

        <script>
            $('#btnEnviar').click(function(e) {
                var usuario = $('#usuario').val().trim();
                var contrasena = $('#contrasena').val().trim();
                var resultado = $('#resultado');

                if (usuario === "" || contrasena === "") {
                    e.preventDefault(); 
                    resultado.text("Por favor, complete todos los campos.");
                } 
            });
        </script>
    </body>
</html>