<?php
session_start();
require_once "../backend/conexion/conexion.php";

if (!isset($_SESSION['usuario']) || empty($_SESSION['carrito_prestamo'])) {
    header("Location: carrito.php");
    exit();
}

$usuario = $_SESSION['usuario'];
$ids = $_SESSION['carrito_prestamo'];

/* Insertar solicitud */
$stmt = $conexion->prepare(
    "INSERT INTO solicitudes (usuario) VALUES (?)"
);
$stmt->bind_param("s", $usuario);
$stmt->execute();

$id_solicitud = $stmt->insert_id;

/* Preparar detalle */
$stmtDetalle = $conexion->prepare(
    "INSERT INTO solicitud_detalle (id_solicitud, nombre_equipo, cantidad)
     VALUES (?, ?, ?)"
);

/* Obtener nombres desde la BD */
$sqlProducto = $conexion->prepare(
    "SELECT nombre FROM productos WHERE id_producto = ?"
);

foreach ($ids as $id_producto) {

    $sqlProducto->bind_param("i", $id_producto);
    $sqlProducto->execute();
    $res = $sqlProducto->get_result()->fetch_assoc();

    if (!$res) continue;

    $nombre = $res['nombre'];
    $cantidad = 1;

    $stmtDetalle->bind_param(
        "isi",
        $id_solicitud,
        $nombre,
        $cantidad
    );
    $stmtDetalle->execute();
}

/* Vaciar carrito */
unset($_SESSION['carrito_prestamo']);

/* Redirigir */
header("Location: solicitud.php?id=" . $id_solicitud);
exit();
