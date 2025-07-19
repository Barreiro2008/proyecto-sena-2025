<?php
session_start();
include '../conexion.php';
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php"); 
    exit();
}
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $usuario_id = $_GET['id'];
    if ($usuario_id == $_SESSION['usuario_id']) {
        header("Location: ../gestion/gestion_usuario.php?error=auto_eliminacion&mensaje=No puedes eliminar tu propia cuenta.");
        exit();
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = :id");
        $stmt->bindParam(':id', $usuario_id);
        $stmt->execute();
        header("Location: ../gestion/gestion_usuario.php?mensaje=usuario_eliminado");
        exit();

    } catch (PDOException $e) {
        header("Location: ../gestion/gestion_usuario.php?error=error_eliminar_usuario&mensaje=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: ../gestion/gestion_usuario.php?error=id_invalido");
    exit();
}
?>