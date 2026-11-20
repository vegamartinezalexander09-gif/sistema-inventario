<?php
function conecta() {
    $host = "tu_servidor_mysql";        
    $usuario = "tu_usuario";          
    $password = "tu_contrasena";    
    $bd = "tu_base_de_datos";     

    $conexion = mysqli_connect($host, $usuario, $password, $bd);

    if (!$conexion) {
        die("Error de conexión: " . mysqli_connect_error());
    }
    
    mysqli_set_charset($conexion, "utf8mb4");
    return $conexion;
}
?>