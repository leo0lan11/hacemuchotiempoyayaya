<?php
// backend/productos/agregar.php
header('Content-Type: application/json');
require_once __DIR__ . '/../conexion/conexion.php';

// Verificar que sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

// Recibir datos (axios/fetch envían JSON o FormData, aquí usaremos FormData standard)
$nombre = $_POST['nombre'] ?? '';
$codigo = $_POST['codigo'] ?? '';
$categoria = $_POST['id_categoria'] ?? '';
$stock = $_POST['stock'] ?? 0;
$ubicacion = $_POST['ubicacion'] ?? '';
$estado = 1; // Por defecto "Operativo" (ID 1 según tu BD)

if (empty($nombre) || empty($codigo)) {
    echo json_encode(['success' => false, 'error' => 'Faltan datos obligatorios']);
    exit;
}

// Preparar SQL
$sql = "INSERT INTO productos (codigo, nombre, stock, ubicacion, id_categoria, id_estado) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ssisii", $codigo, $nombre, $stock, $ubicacion, $categoria, $estado);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Producto agregado correctamente']);
} else {
    // Error común: Código duplicado
    if ($conexion->errno == 1062) {
        echo json_encode(['success' => false, 'error' => 'Error: El código ya existe']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al guardar en BD']);
    }
}

$stmt->close();
$conexion->close();
?>