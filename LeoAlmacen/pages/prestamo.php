<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: /LeoAlmacen/index.html");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$id = intval($_GET['id']);

if (!isset($_SESSION['carrito_prestamo'])) {
    $_SESSION['carrito_prestamo'] = [];
}

if (!in_array($id, $_SESSION['carrito_prestamo'])) {
    $_SESSION['carrito_prestamo'][] = $id;
}

header("Location: detail.php?id=" . $id);
exit();
