<?php
// backend/productos/productos.php
header('Content-Type: application/json');
require_once __DIR__ . '/../conexion/conexion.php';

// Consulta uniendo las tablas para obtener los NOMBRES de categoría y estado, no solo los IDs
$sql = "SELECT 
            p.id_producto, 
            p.nombre, 
            p.stock, 
            c.nombre AS categoria, 
            e.nombre AS estado 
        FROM productos p
        LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
        LEFT JOIN estados e ON p.id_estado = e.id_estado";

$resultado = $conexion->query($sql);

$productos = [];

if ($resultado->num_rows > 0) {
    while($row = $resultado->fetch_assoc()) {
        $productos[] = $row;
    }
}

echo json_encode($productos);
$conexion->close();
?>