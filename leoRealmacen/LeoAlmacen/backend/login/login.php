<?php
// backend/login/login.php
session_start();
require_once __DIR__ . '/../conexion/conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $usuario_form = trim($_POST['usuario']);
    $password_form = trim($_POST['password']);

    $sql = "SELECT * FROM usuarios WHERE usuario = ?";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $usuario_form);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $fila = $resultado->fetch_assoc();
        
        // --- CAMBIO IMPORTANTE AQUÍ ---
        // Usamos password_verify() en lugar de ===
        // Esto compara la contraseña escrita ("11") con el hash de la BD
        if (password_verify($password_form, $fila['password'])) {
            
            $_SESSION['id_usuario'] = $fila['id'];
            $_SESSION['usuario'] = $fila['usuario'];
            $_SESSION['rol'] = $fila['rol'];

            header("Location: /leoalmacen/pages/dashboard.php"); 
            exit();

        } else {
            echo "<script>alert('Contraseña incorrecta'); window.location.href='/leoalmacen/pages/login.html';</script>";
        }
        // ------------------------------

    } else {
        echo "<script>alert('El usuario no existe'); window.location.href='/leoalmacen/pages/login.html';</script>";
    }
    
    $stmt->close();
    $conexion->close();

} else {
    header("Location: /leoalmacen/pages/login.html");
}
?>