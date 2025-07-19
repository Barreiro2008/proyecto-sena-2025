<?php
session_start();
include '../conexion.php';
if (!isset($_SESSION['usuario']) || (isset($_SESSION['rol']) && $_SESSION['rol'] !== 'admin')) {
    header("Location: ../index.php");
    exit();
}
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../gestion/gestion_proveedor.php?error=id_invalido");
    exit();
}

$proveedor_id = $_GET['id'];

try {
    $stmt_delete = $pdo->prepare("DELETE FROM proveedores WHERE id = :id");
    $stmt_delete->bindParam(':id', $proveedor_id);
    $stmt_delete->execute();
    if ($stmt_delete->rowCount() > 0) {
        $mensaje = "Proveedor eliminado exitosamente.";
        header("Location: ../gestion/gestion_proveedor.php?mensaje=" . urlencode($mensaje));
    } else {
        $error_message = "No se encontró el proveedor con el ID especificado.";
        header("Location: ../gestion/gestion_proveedor.php?error=" . urlencode($error_message));
    }

    exit();

} catch (PDOException $e) {
    $error_message = "Error al eliminar el proveedor: " . $e->getMessage();
    header("Location: ../gestion/gestion_proveedor.php?error=" . urlencode($error_message));
    exit();
}
?>