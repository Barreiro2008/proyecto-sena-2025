<?php
session_start();
include 'conexion.php';

// Verificar si el usuario ha iniciado sesión y es administrador
if (!isset($_SESSION['usuario']) || (isset($_SESSION['rol']) && $_SESSION['rol'] !== 'admin')) {
    header("Location: index.php"); // O una página de error de acceso denegado
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: gestion_producto.php?error=id_invalido");
    exit();
}

$producto_id = $_GET['id'];

try {
    $pdo->beginTransaction();

    $stmt_delete_detalles = $pdo->prepare("DELETE FROM detalles_venta WHERE producto_id = :id");
    $stmt_delete_detalles->bindParam(':id', $producto_id);
    $stmt_delete_detalles->execute();

    $stmt_delete_producto = $pdo->prepare("DELETE FROM productos WHERE id = :id");
    $stmt_delete_producto->bindParam(':id', $producto_id);
    $stmt_delete_producto->execute();

    if ($stmt_delete_producto->rowCount() > 0) {
        $pdo->commit();
        $mensaje = "Producto eliminado exitosamente.";
        header("Location: gestion_producto.php?mensaje=" . urlencode($mensaje));
    } else {
        $pdo->rollBack();
        $error_message = "No se encontró el producto con el ID especificado.";
        header("Location: gestion_producto.php?error=" . urlencode($error_message));
    }

    exit();

} catch (PDOException $e) {
    $pdo->rollBack();
    $error_message = "Error al eliminar el producto: " . $e->getMessage();
    header("Location: gestion_producto.php?error=" . urlencode($error_message));
    exit();
}
?>