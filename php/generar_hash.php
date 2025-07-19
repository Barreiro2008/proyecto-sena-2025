<?php
$contrasena_plana = '1084923568'; 
$hash = password_hash($contrasena_plana, PASSWORD_DEFAULT);
echo "Contraseña plana: " . htmlspecialchars($contrasena_plana) . "<br>";
echo "Hash generado: " . htmlspecialchars($hash);
?>