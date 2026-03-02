<?php
session_start();
require_once "../backend/conexion/conexion.php";

$id = intval($_GET['id']);

/* Buscar el producto REAL desde la BD */
$stmt = $conexion->prepare(
  "SELECT id_producto, nombre, stock, estado, imagen
   FROM productos
   WHERE id_producto = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$producto = $stmt->get_result()->fetch_assoc();

if (!$producto) {
  header("Location: dashboard.php");
  exit();
}

/* Crear carrito si no existe */
if (!isset($_SESSION['carrito'])) {
  $_SESSION['carrito'] = [];
}

/* Agregar SOLO este producto */
if (isset($_SESSION['carrito'][$id])) {
  $_SESSION['carrito'][$id]['cantidad']++;
} else {
  $_SESSION['carrito'][$id] = [
    "id" => $producto['id_producto'],
    "nombre" => $producto['nombre'],
    "cantidad" => 1,
    "stock" => $producto['stock'],
    "estado" => $producto['estado'],
    "imagen" => $producto['imagen']
  ];
}

header("Location: carrito.php");
exit();
