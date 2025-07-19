<?php
session_start();
include '../conexion.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit();
}

// Inicializar el carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = array();
}

// Mensajes
if (isset($_GET['mensaje'])) {
    $mensaje = '<div class="alert alert-success">' . htmlspecialchars($_GET['mensaje']) . '</div>';
}
if (isset($_GET['error'])) {
    $mensaje = '<div class="alert alert-danger">' . htmlspecialchars($_GET['error']) . '</div>';
} else {
    $mensaje = '';
}

// Número máximo de productos a mostrar por página
$productos_por_pagina = 5;

// Obtener el término de búsqueda si existe
$busqueda = $_GET['buscar'] ?? '';

// Consulta base - MODIFICADA para incluir teléfono
$sql = "SELECT p.id, p.nombre AS nombre_producto, p.precio, p.stock, prov.nombre AS nombre_proveedor, prov.telefono, p.descripcion
        FROM productos p
        LEFT JOIN proveedores prov ON p.proveedor_id = prov.id";

// Añadir condición de búsqueda si hay un término
if (!empty($busqueda)) {
    $sql .= " WHERE p.nombre LIKE :busqueda OR p.descripcion LIKE :busqueda OR prov.nombre LIKE :busqueda";
}

// Preparar la consulta para contar el total de productos
$stmt_count = $pdo->prepare(str_replace("SELECT p.id, p.nombre AS nombre_producto, p.precio, p.stock, prov.nombre AS nombre_proveedor, prov.telefono, p.descripcion", "SELECT COUNT(*)", $sql));

// Bindear el parámetro de búsqueda si existe
if (!empty($busqueda)) {
    $busqueda_param = "%" . $busqueda . "%";
    $stmt_count->bindParam(':busqueda', $busqueda_param, PDO::PARAM_STR);
}

// Ejecutar la consulta para contar
$stmt_count->execute();
$total_productos = $stmt_count->fetchColumn();

// Calcular el número total de páginas
$total_paginas = ceil($total_productos / $productos_por_pagina);

// Obtener la página actual
$pagina_actual = $_GET['pagina'] ?? 1;
$pagina_actual = max(1, min($pagina_actual, $total_paginas));

// Calcular el índice del primer producto a mostrar en la página actual
$indice_inicio = ($pagina_actual - 1) * $productos_por_pagina;

// Modificar la consulta para LIMIT y OFFSET
$sql .= " ORDER BY p.nombre LIMIT :inicio, :cantidad";
$stmt = $pdo->prepare($sql);

// Bindear los parámetros para la búsqueda (si existe)
if (!empty($busqueda)) {
    $busqueda_param = "%" . $busqueda . "%";
    $stmt->bindParam(':busqueda', $busqueda_param, PDO::PARAM_STR);
}
// Bindear los parámetros para LIMIT y OFFSET
$stmt->bindParam(':inicio', $indice_inicio, PDO::PARAM_INT);
$stmt->bindParam(':cantidad', $productos_por_pagina, PDO::PARAM_INT);

// Ejecutar la consulta con LIMIT y OFFSET
$stmt->execute();
$productos = $stmt->fetchAll();

// Procesar la adición al carrito
if (isset($_POST['agregar_carrito'])) {
    $producto_id = $_POST['producto_id'];
    $cantidad = intval($_POST['cantidad']);

    // Verificar si el producto existe y tiene suficiente stock
    $stmt_producto = $pdo->prepare("SELECT id, nombre, precio, stock FROM productos WHERE id = :id");
    $stmt_producto->bindParam(':id', $producto_id);
    $stmt_producto->execute();
    $producto = $stmt_producto->fetch();

    if ($producto && $cantidad > 0 && $producto['stock'] >= $cantidad) {
        if (isset($_SESSION['carrito'][$producto_id])) {
            $_SESSION['carrito'][$producto_id]['cantidad'] += $cantidad;
        } else {
            $_SESSION['carrito'][$producto_id] = array(
                'nombre' => $producto['nombre'],
                'precio' => $producto['precio'],
                'cantidad' => $cantidad
            );
        }
        header("Location: gestion_producto.php?mensaje=Producto+agregado+al+carrito");
        exit();
    } else {
        header("Location: gestion_producto.php?error=No+se+pudo+agregar+el+producto+al+carrito.+Verifique+el+stock.");
        exit();
    }
}

// Procesar la eliminación del carrito
if (isset($_GET['eliminar_carrito'])) {
    $producto_id_eliminar = $_GET['eliminar_carrito'];
    if (isset($_SESSION['carrito'][$producto_id_eliminar])) {
        unset($_SESSION['carrito'][$producto_id_eliminar]);
        header("Location: gestion_producto.php?mensaje=Producto+eliminado+del+carrito");
        exit();
    }
}

// Procesar la actualización del carrito
if (isset($_POST['actualizar_carrito'])) {
    foreach ($_POST['cantidad_carrito'] as $producto_id => $cantidad) {
        $cantidad = intval($cantidad);
        if ($cantidad > 0 && isset($_SESSION['carrito'][$producto_id])) {
            $_SESSION['carrito'][$producto_id]['cantidad'] = $cantidad;
        } elseif ($cantidad <= 0 && isset($_SESSION['carrito'][$producto_id])) {
            unset($_SESSION['carrito'][$producto_id]);
        }
    }
    header("Location: gestion_producto.php?mensaje=Carrito+actualizado");
    exit();
}

// Procesar la venta
if (isset($_POST['realizar_venta'])) {
    if (!empty($_SESSION['carrito'])) {
        try {
            $pdo->beginTransaction();

            // Insertar la venta en la tabla 'ventas'
            $stmt_venta = $pdo->prepare("INSERT INTO ventas (usuario_id, total_venta) VALUES (:usuario_id, :total_venta)");
            $stmt_venta->bindParam(':usuario_id', $_SESSION['usuario_id']);
            $total_venta = 0;
            foreach ($_SESSION['carrito'] as $item) {
                $total_venta += $item['precio'] * $item['cantidad'];
            }
            $stmt_venta->bindParam(':total_venta', $total_venta);
            $stmt_venta->execute();
            $venta_id = $pdo->lastInsertId();

            // Insertar los detalles de la venta en la tabla 'detalles_venta' y actualizar el stock
            $stmt_detalle = $pdo->prepare("INSERT INTO detalles_venta (venta_id, producto_id, cantidad, precio_unitario) VALUES (:venta_id, :producto_id, :cantidad, :precio_unitario)");
            $stmt_actualizar_stock = $pdo->prepare("UPDATE productos SET stock = stock - :cantidad WHERE id = :id");

            foreach ($_SESSION['carrito'] as $producto_id => $item) {
                $stmt_detalle->bindParam(':venta_id', $venta_id);
                $stmt_detalle->bindParam(':producto_id', $producto_id);
                $stmt_detalle->bindParam(':cantidad', $item['cantidad']);
                $stmt_detalle->bindParam(':precio_unitario', $item['precio']);
                $stmt_detalle->execute();

                $stmt_actualizar_stock->bindParam(':cantidad', $item['cantidad']);
                $stmt_actualizar_stock->bindParam(':id', $producto_id);
                $stmt_actualizar_stock->execute();
            }

            $pdo->commit();
            $_SESSION['carrito'] = array(); // Limpiar el carrito después de la venta
            header("Location: listar_ventas.php?mensaje=Venta+realizada+con+éxito");
            exit();

        } catch (PDOException $e) {
            $pdo->rollBack();
            header("Location: gestion_producto.php?error=Error+al+realizar+la+venta:+" . $e->getMessage());
            exit();
        }
    } else {
        header("Location: gestion_producto.php?error=El+carrito+está+vacío.");
        exit();
    }
}

// Calcular el total del carrito y la cantidad de items
$total_carrito = 0;
$cantidad_carrito = 0;
if (!empty($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $item) {
        $total_carrito += $item['precio'] * $item['cantidad'];
        $cantidad_carrito += $item['cantidad'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Productos | Variedades Juanmarc</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
        }

        body.jm-body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .jm-sidebar {
            width: 260px;
            height: 100vh;
            background: linear-gradient(180deg, #FF8C00 0%, #FFA500 100%);
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            padding: 20px;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            z-index: 1000;
        }

        .jm-sidebar-header {
            margin-bottom: 30px;
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
        }

        .jm-logo {
            font-size: 26px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 5px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .jm-menu {
            list-style: none;
            padding: 0;
            width: 100%;
        }

        .jm-menu li {
            margin-bottom: 5px;
        }

        .jm-menu-title {
            margin-top: 25px;
            margin-bottom: 10px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.8);
            padding-left: 15px;
            border-left: 3px solid #FFD700;
            display: flex;
            align-items: center;
            gap: 8px;
            letter-spacing: 1px;
        }

        .jm-menu-title img {
            width: 18px;
            height: 18px;
        }

        .jm-link {
            color: white;
            text-decoration: none;
            display: block;
            padding: 12px 18px;
            border-radius: 10px;
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 3px;
        }

        .jm-link:hover, .jm-link.active {
            background: rgba(255, 255, 255, 0.2);
            text-decoration: none;
            color: white;
            transform: translateX(5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .jm-main {
            margin-left: 260px;
            padding: 30px;
            min-height: 100vh;
        }

        .jm-navbar {
            background: linear-gradient(135deg, #FF8C00 0%, #FFA500 100%);
            color: white;
            padding: 20px 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(255, 165, 0, 0.3);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .jm-navbar h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }

        .jm-cart {
            position: relative;
            background: rgba(255, 255, 255, 0.2);
            padding: 10px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .jm-cart:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.05);
        }

        .jm-cart img {
            width: 28px;
            height: 28px;
        }

        .jm-cart-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #e74c3c;
            color: white;
            font-size: 11px;
            font-weight: 600;
            border-radius: 50%;
            padding: 4px 8px;
            min-width: 20px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(231, 76, 60, 0.4);
        }

        .jm-buscar {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .jm-buscar input {
            flex: 1;
            padding: 12px 18px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .jm-buscar input:focus {
            outline: none;
            border-color: #FFA500;
            background: white;
            box-shadow: 0 0 0 3px rgba(255, 165, 0, 0.1);
        }

        .jm-buscar button {
            padding: 12px 25px;
            background: linear-gradient(135deg, #FF8C00 0%, #FFA500 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 165, 0, 0.3);
        }

        .jm-buscar button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 165, 0, 0.4);
        }

        .jm-contenedor-botones {
            margin-bottom: 25px;
            text-align: right;
        }

        .btn-agregar {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 12px 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        .btn-agregar:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
            text-decoration: none;
            color: white;
        }

        /* TABLA MODERNA */
        .jm-tabla-container {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .jm-tabla {
            width: 100%;
            margin: 0;
            border-collapse: collapse;
            font-size: 14px;
        }

        .jm-tabla thead {
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
        }

        .jm-tabla th {
            padding: 20px 15px;
            text-align: center;
            color: #333;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 13px;
            letter-spacing: 1px;
            border: none;
        }

        .jm-tabla tbody tr {
            transition: all 0.3s ease;
            border-bottom: 1px solid #f1f3f4;
        }

        .jm-tabla tbody tr:hover {
            background: #f8f9fa;
            transform: scale(1.01);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        /* FILA CON STOCK BAJO - ROJO SUAVE */
        .jm-tabla tbody tr.stock-bajo-fila {
            background: linear-gradient(135deg, #ffebee 0%, #fce4ec 100%);
            border-left: 4px solid #e74c3c;
        }

        .jm-tabla tbody tr.stock-bajo-fila:hover {
            background: linear-gradient(135deg, #ffcdd2 0%, #f8bbd9 100%);
            transform: scale(1.01);
            box-shadow: 0 4px 20px rgba(231, 76, 60, 0.2);
        }

        .jm-tabla td {
            padding: 18px 15px;
            text-align: center;
            vertical-align: middle;
            border: none;
            font-weight: 500;
        }

        .jm-tabla td:first-child {
            font-weight: 700;
            color: #6c757d;
        }

        .jm-tabla td:nth-child(2) {
            text-align: left;
            font-weight: 600;
            color: #2c3e50;
        }

        .precio-cell {
            font-weight: 700;
            color: #27ae60;
            font-size: 15px;
        }

        .stock-numero {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 13px;
        }

        .stock-normal {
            background: #d4edda;
            color: #155724;
        }

        .stock-bajo {
            background: #f8d7da;
            color: #721c24;
        }

        .alerta-stock {
            color: #e74c3c;
            font-size: 14px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        /* BOTONES DE ACCIÓN */
        .jm-gestion-acciones {
            display: flex;
            gap: 8px;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
        }

        .btn-accion {
            padding: 8px 12px;
            border-radius: 8px;
            text-decoration: none;
            color: white;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .btn-editar {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3);
        }

        .btn-editar:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.4);
            color: white;
            text-decoration: none;
        }

        .btn-eliminar {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
        }

        .btn-eliminar:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
            color: white;
            text-decoration: none;
        }

        .btn-agregar-carrito {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
        }

        .btn-agregar-carrito:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
        }

        /* BOTÓN WHATSAPP MEJORADO */
        .btn-whatsapp {
            background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            box-shadow: 0 2px 8px rgba(37, 211, 102, 0.3);
        }

        .btn-whatsapp:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 211, 102, 0.4);
            color: white;
            text-decoration: none;
        }

        .btn-whatsapp:disabled {
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
            color: #6c757d;
            cursor: not-allowed;
            box-shadow: none;
            transform: none;
        }

        .btn-whatsapp:disabled:hover {
            transform: none;
            box-shadow: none;
        }

        .cantidad-input {
            width: 50px;
            padding: 4px 8px;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            text-align: center;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .cantidad-input:focus {
            outline: none;
            border-color: #FFA500;
            box-shadow: 0 0 0 2px rgba(255, 165, 0, 0.2);
        }

        /* CARRITO MEJORADO */
        .jm-carrito-resumen {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-top: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .jm-carrito-resumen h3 {
            color: #FFA500;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 700;
            font-size: 22px;
        }

        .jm-carrito-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f1f3f4;
            transition: all 0.3s ease;
        }

        .jm-carrito-item:hover {
            background: #f8f9fa;
            padding-left: 10px;
            border-radius: 10px;
        }

        .jm-carrito-total {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #FFA500;
            text-align: right;
            font-weight: 700;
            font-size: 18px;
            color: #2c3e50;
        }

        .jm-formulario-carrito {
            margin-top: 25px;
            text-align: right;
            display: flex;
            gap: 15px;
            justify-content: flex-end;
        }

        .jm-formulario-carrito button {
            background: linear-gradient(135deg, #FFA500 0%, #FF8C00 100%);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 12px 20px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 165, 0, 0.3);
        }

        .jm-formulario-carrito button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 165, 0, 0.4);
        }

        /* PAGINACIÓN MEJORADA */
        .pagination {
            margin-top: 25px;
            display: flex;
            justify-content: center;
            gap: 5px;
        }

        .pagination a {
            color: #FFA500;
            padding: 10px 15px;
            text-decoration: none;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            background: white;
        }

        .pagination a:hover {
            background: #FFA500;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 165, 0, 0.3);
        }

        .pagination a.active {
            background: linear-gradient(135deg, #FFA500 0%, #FF8C00 100%);
            color: white;
            border-color: #FFA500;
            box-shadow: 0 4px 15px rgba(255, 165, 0, 0.3);
        }

        /* FOOTER */
        .jm-footer {
            text-align: center;
            padding: 25px;
            margin-top: 40px;
            font-size: 14px;
            color: #6c757d;
            border-top: 2px solid #f1f3f4;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        /* ALERTAS MEJORADAS */
        .alert {
            border-radius: 15px;
            padding: 15px 20px;
            margin-bottom: 25px;
            border: none;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
        }

        .alert-danger {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .jm-sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .jm-main {
                margin-left: 0;
                padding: 15px;
            }
            
            .jm-gestion-acciones {
                flex-direction: column;
                gap: 5px;
            }
            
            .jm-tabla td, .jm-tabla th {
                padding: 10px 5px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body class="jm-body">
    <div class="jm-sidebar">
        <div class="jm-sidebar-header">
            <div class="jm-logo">Variedades Juanmarc</div>
        </div>
        <ul class="jm-menu">
            <li class="jm-menu-title">
                <img src="https://img.icons8.com/ios-filled/20/ffffff/user.png" alt="icono usuario">
                Usuario
            </li>
            <li><a href="datos_personales.php" class="jm-link"><i class="fas fa-user mr-2"></i> Datos personales</a></li>
            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
                <li><a href="gestion_usuario.php" class="jm-link"><i class="fas fa-cog mr-2"></i> Gestión usuario</a></li>
            <?php endif; ?>

            <li class="jm-menu-title">
                <img src="https://img.icons8.com/ios-filled/20/ffffff/sales-performance.png" alt="icono ventas">
                Ventas
            </li>
            <li><a href="listar_ventas.php" class="jm-link"><i class="fas fa-chart-line mr-2"></i> Listar Ventas</a></li>

            <li class="jm-menu-title">
                <img src="https://img.icons8.com/ios-filled/20/ffffff/warehouse.png" alt="icono almacen">
                Almacén
            </li>
            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
                <li><a href="gestion_producto.php" class="jm-link active"><i class="fas fa-box-open mr-2"></i> Gestión producto</a></li>
            <?php endif; ?>
            <li><a href="gestion_lote.php" class="jm-link"><i class="fas fa-cubes mr-2"></i> Gestión lote</a></li>

            <li class="jm-menu-title">
                <img src="https://img.icons8.com/ios-filled/20/ffffff/supplier.png" alt="icono compras">
                Compras
            </li>
            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
                <li><a href="gestion_proveedor.php" class="jm-link"><i class="fas fa-truck mr-2"></i> Gestión proveedor</a></li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="jm-main">
        <div class="jm-navbar">
            <h2>Gestión de Productos</h2>
            <div class="jm-cart">
                <img src="../imagenes/carrito.jpg"/>
                <span class="jm-cart-badge"><?php echo $cantidad_carrito; ?></span>
            </div>
        </div>

        <?php echo $mensaje; ?>

        <div class="jm-buscar">
            <input type="text" placeholder="Buscar por nombre o proveedor" name="buscar" value="<?php echo htmlspecialchars($busqueda); ?>">
            <button><i class="fas fa-search mr-2"></i> Buscar</button>
        </div>

        <div class="jm-contenedor-botones">
            <a href="../agregar/agregar_producto.php" class="btn-agregar">
                <i class="fas fa-plus"></i> Agregar Producto
            </a>
        </div>

        <div class="jm-tabla-container">
            <table class="jm-tabla">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Proveedor</th>
                        <th>Contacto</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($productos) > 0): ?>
                        <?php foreach ($productos as $producto): ?>
                            <tr class="<?php echo ($producto['stock'] <= 10) ? 'stock-bajo-fila' : ''; ?>">
                                <td><?php echo htmlspecialchars($producto['id']); ?></td>
                                <td style="text-align: left;"><?php echo htmlspecialchars($producto['nombre_producto']); ?></td>
                                <td class="precio-cell">$<?php echo htmlspecialchars(number_format($producto['precio'], 2)); ?></td>
                                <td>
                                    <span class="stock-numero <?php echo ($producto['stock'] <= 10) ? 'stock-bajo' : 'stock-normal'; ?>">
                                        <?php echo htmlspecialchars($producto['stock']); ?>
                                        <?php if ($producto['stock'] <= 10): ?>
                                            <i class="fas fa-exclamation-triangle alerta-stock" title="Stock bajo"></i>
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($producto['nombre_proveedor'] ?: 'N/A'); ?></td>
                                <td>
                                    <?php if ($producto['stock'] <= 10 && !empty($producto['telefono'])): ?>
                                        <a href="javascript:void(0)" 
                                           class="btn-whatsapp" 
                                           onclick="contactarWhatsApp('<?php echo htmlspecialchars($producto['telefono']); ?>', '<?php echo htmlspecialchars($producto['nombre_producto']); ?>')">
                                            <i class="fab fa-whatsapp"></i> WhatsApp
                                        </a>
                                    <?php else: ?>
                                        <button class="btn-whatsapp" disabled>
                                            <i class="fab fa-whatsapp"></i> WhatsApp
                                        </button>
                                    <?php endif; ?>
                                </td>
                                <td class="jm-gestion-acciones">
                                    <a href="editar_producto.php?id=<?php echo htmlspecialchars($producto['id']); ?>" class="btn-accion btn-editar">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <a href="eliminar_producto.php?id=<?php echo htmlspecialchars($producto['id']); ?>" class="btn-accion btn-eliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar este producto?')">
                                        <i class="fas fa-trash-alt"></i> Eliminar
                                    </a>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="producto_id" value="<?php echo htmlspecialchars($producto['id']); ?>">
                                        <input type="number" name="cantidad" value="1" min="1" class="cantidad-input">
                                        <button type="submit" name="../agregar/agregar_carrito" class="btn-accion btn-agregar-carrito">
                                            <i class="fas fa-shopping-cart"></i> Agregar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center" style="padding: 40px; color: #6c757d; font-style: italic;">No hay productos registrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_paginas > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination">
                    <?php if ($pagina_actual > 1): ?>
                        <li><a href="?pagina=<?php echo ($pagina_actual - 1) . (!empty($busqueda) ? '&buscar=' . htmlspecialchars($busqueda) : ''); ?>">Anterior</a></li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                        <li><a class="<?php echo ($i == $pagina_actual) ? 'active' : ''; ?>" href="?pagina=<?php echo $i . (!empty($busqueda) ? '&buscar=' . htmlspecialchars($busqueda) : ''); ?>"><?php echo $i; ?></a></li>
                    <?php endfor; ?>

                    <?php if ($pagina_actual < $total_paginas): ?>
                        <li><a href="?pagina=<?php echo ($pagina_actual + 1) . (!empty($busqueda) ? '&buscar=' . htmlspecialchars($busqueda) : ''); ?>">Siguiente</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>

        <?php if (!empty($_SESSION['carrito'])): ?>
            <div class="jm-carrito-resumen">
                <h3><i class="fas fa-shopping-cart mr-2"></i>Carrito de Compras</h3>
                <form method="post">
                    <ul class="jm-carrito-lista">
                        <?php foreach ($_SESSION['carrito'] as $id => $item): ?>
                            <li class="jm-carrito-item">
                                <span style="font-weight: 600;"><?php echo htmlspecialchars($item['nombre']); ?></span>
                                <div class="jm-carrito-cantidad">
                                    <input type="number" name="cantidad_carrito[<?php echo $id; ?>]" value="<?php echo htmlspecialchars($item['cantidad']); ?>" min="0" class="cantidad-input">
                                    <span style="font-weight: 600; color: #27ae60;">x $<?php echo htmlspecialchars(number_format($item['precio'], 2)); ?></span>
                                </div>
                                <div class="jm-carrito-acciones">
                                    <a href="?eliminar_carrito=<?php echo $id; ?>" style="color: #e74c3c; font-weight: 600;">
                                        <i class="fas fa-trash-alt"></i> Eliminar
                                    </a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="jm-carrito-total">
                        <strong>Total: $<?php echo htmlspecialchars(number_format($total_carrito, 2)); ?></strong>
                    </div>
                    <div class="jm-formulario-carrito">
                        <button type="submit" name="actualizar_carrito">
                            <i class="fas fa-sync-alt mr-2"></i> Actualizar Carrito
                        </button>
                        <button type="submit" name="realizar_venta">
                            <i class="fas fa-check mr-2"></i> Realizar Venta
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <a href="../index.php" class="btn-agregar" style="margin-top: 30px;">
            <i class="fas fa-arrow-left"></i> Volver
        </a>

        <footer class="jm-footer">
            <i class="fas fa-heart" style="color: #e74c3c;"></i> © 2025 Variedades Juanmarc. Todos los derechos reservados.
        </footer>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
        function contactarWhatsApp(telefono, nombreProducto) {
            // Limpiar el teléfono (quitar espacios y caracteres especiales)
            const telefonoLimpio = telefono.replace(/\s/g, '').replace(/\+57/, '');
            
            // Crear el mensaje
            const mensaje = `🚨 *ALERTA DE STOCK BAJO* 🚨\n\nHola, necesito reponer urgentemente el producto:\n\n📦 *${nombreProducto}*\n\nEl inventario está crítico (≤10 unidades) y necesitamos hacer un nuevo pedido lo antes posible.\n\n¡Gracias!`;
            
            // Crear la URL de WhatsApp
            const url = `https://wa.me/57${telefonoLimpio}?text=${encodeURIComponent(mensaje)}`;
            
            // Abrir WhatsApp en una nueva ventana
            window.open(url, '_blank');
        }

        // Efecto de búsqueda en tiempo real
        document.querySelector('.jm-buscar input').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.jm-tabla tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Animación de carga
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('.jm-tabla tbody tr');
            rows.forEach((row, index) => {
                row.style.opacity = '0';
                row.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    row.style.transition = 'all 0.5s ease';
                    row.style.opacity = '1';
                    row.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>
