<?php
session_start();
include '../conexion.php';
if (!isset($_SESSION['usuario']) || (isset($_SESSION['rol']) && $_SESSION['rol'] !== 'admin')) {
    header("Location: ../index.php"); 
    exit();
}
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../gestion/gestion_lote.php?error=id_invalido");
    exit();
}

$lote_id = $_GET['id'];

try {
    $stmt_delete = $pdo->prepare("DELETE FROM lotes WHERE id = :id");
    $stmt_delete->bindParam(':id', $lote_id);
    $stmt_delete->execute();
    if ($stmt_delete->rowCount() > 0) {
        $mensaje = "Lote eliminado exitosamente.";
        header("Location: ../gestion/gestion_lote.php?mensaje=" . urlencode($mensaje));
    } else {
        $error_message = "No se encontró el lote con el ID especificado.";
        header("Location: ../gestion/gestion_lote.php?error=" . urlencode($error_message));
    }

    exit();

} catch (PDOException $e) {
    $error_message = "Error al eliminar el lote: " . $e->getMessage();
    header("Location: ../gestion/gestion_lote.php?error=" . urlencode($error_message));
    exit();
}
?>