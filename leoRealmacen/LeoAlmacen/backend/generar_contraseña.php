<?php
// backend/generar_clave.php
$password_real = "09876"; // La contraseña que quieres usar
$hash = password_hash($password_real, PASSWORD_DEFAULT);
echo "Tu nuevo hash para la base de datos es: <br><strong>" . $hash . "</strong>";
?>