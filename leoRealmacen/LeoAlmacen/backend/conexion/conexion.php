<?php
// backend/conexion/conexion.php

$host = "localhost";
$user = "root";
$pass = "";
$db   = "almacen_cruz_roja"; // <--- AQUÍ ESTABA EL ERROR

$conexion = new mysqli($host, $user, $pass, $db);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$conexion->set_charset("utf8");
?>