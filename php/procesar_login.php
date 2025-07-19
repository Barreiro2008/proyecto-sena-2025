<?php
session_start();
include 'conexion.php';

$usuario = $_POST['usuario'] ?? '';
$clave = $_POST['password'] ?? ''; 

if (empty($usuario) || empty($clave)) {
    $_SESSION['error_login'] = 'Debes ingresar usuario y contraseña.';
    header("Location: login.php");
    exit();
}

$stmt = $pdo->prepare("SELECT id, usuario, password, rol FROM usuarios WHERE usuario = :usuario");
$stmt->bindParam(':usuario', $usuario);
$stmt->execute();
$usuario_encontrado = $stmt->fetch();

if ($usuario_encontrado && password_verify($clave, $usuario_encontrado['password'])) {
    $_SESSION['usuario_id'] = $usuario_encontrado['id'];
    $_SESSION['usuario'] = $usuario_encontrado['usuario'];
    $_SESSION['rol'] = $usuario_encontrado['rol'];
    header("Location: index.php");
    exit();
} else {
    $_SESSION['error_login'] = 'Credenciales incorrectas. Intenta de nuevo.';
    header("Location: login.php");
    exit();
}
?>