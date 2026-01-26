<?php
session_start();

// Forzar JSON sin importar qué
header('Content-Type: application/json; charset=utf-8');

// Desactivar errores PHP que se muestren
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once __DIR__ . '/../conexion/conexion.php';
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    if (!isset($_SESSION['usuario'])) {
        throw new Exception('No hay sesión activa');
    }

    $id_producto = intval($_POST['id_producto'] ?? 0);
    $comentario = trim($_POST['comentario'] ?? '');
    $usuario = $_SESSION['usuario'];

    if (!$id_producto || empty($comentario)) {
        throw new Exception('Faltan datos (id_producto o comentario)');
    }

    if (strlen($comentario) > 500) {
        throw new Exception('Comentario muy largo');
    }

    // Verificar que la tabla existe
    $checkTable = $conexion->query("SHOW TABLES LIKE 'comentarios'");
    if ($checkTable->num_rows === 0) {
        throw new Exception('Tabla comentarios no existe');
    }

    // Insertar comentario
    $sql = "INSERT INTO comentarios (id_producto, usuario, comentario) VALUES (?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Error prepare: ' . $conexion->error);
    }

    $stmt->bind_param("iss", $id_producto, $usuario, $comentario);
    
    if (!$stmt->execute()) {
        throw new Exception('Error execute: ' . $stmt->error);
    }

    echo json_encode(['success' => true, 'message' => 'Comentario guardado correctamente']);
    $stmt->close();

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conexion->close();
?>
