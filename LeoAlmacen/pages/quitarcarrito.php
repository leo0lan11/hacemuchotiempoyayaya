<?php
session_start();

if (!isset($_POST['id'])) {
    header("Location: carrito.php");
    exit();
}

$id = (int) $_POST['id'];

if (!isset($_SESSION['carrito_prestamo'])) {
    header("Location: carrito.php");
    exit();
}

$_SESSION['carrito_prestamo'] = array_values(
    array_filter(
        $_SESSION['carrito_prestamo'],
        fn($producto) => $producto != $id
    )
);

header("Location: carrito.php");
exit();
