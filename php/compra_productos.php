<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

// Obtener todos los proveedores
$stmt_proveedores = $pdo->query("SELECT id, nombre FROM proveedores ORDER BY nombre");
$proveedores = $stmt_proveedores->fetchAll(PDO::FETCH_ASSOC);

// Función para obtener los lotes de un proveedor
function obtenerLotesProveedor($pdo, $proveedor_id) {
    $stmt_lotes = $pdo->prepare("SELECT l.id, p.nombre AS nombre_producto, l.precio_proveedor, l.cantidad, l.fecha_vencimiento
                                  FROM lotes l
                                  JOIN productos p ON l.producto_id = p.id
                                  WHERE l.proveedor_id = :proveedor_id
                                  ORDER BY p.nombre, l.fecha_vencimiento");
    $stmt_lotes->bindParam(':proveedor_id', $proveedor_id);
    $stmt_lotes->execute();
    return $stmt_lotes->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lotes por Proveedor | Variedades Juanmarc</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body.jm-body {
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            background: #f8f9fa;
        }

        .jm-sidebar {
            width: 200px;
            height: 100vh;
            background-color: #FFA500;
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            padding: 10px 15px;
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .jm-sidebar-header {
            margin-bottom: 18px;
            text-align: center;
            width: 100%;
        }

        .jm-logo {
            font-size: 24px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 3px;
        }

        .jm-menu {
            list-style: none;
            padding: 0;
            width: 100%;
        }

        .jm-menu li {
            padding: 6px 15px;
            color: white;
            cursor: pointer;
            border-radius: 6px;
            transition: background 0.3s;
            margin-bottom: 2px;
            width: 100%;
        }

        .jm-menu li:hover, .jm-menu li.active {
            background-color: #FFD700;
            color: #333;
        }

        .jm-menu-title {
            margin-top: 12px;
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            color: #fff;
            padding-left: 12px;
            border-left: 3px solid #FFD700;
            display: flex;
            align-items: center;
            gap: 6px;
            width: 100%;
        }

        .jm-menu-title img {
            width: 20px;
            height: 20px;
        }

        .jm-link {
            color: white;
            text-decoration: none;
            display: block;
            padding: 6px 16px;
            text-align: left;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 14px;
        }

        .jm-link:hover, .jm-link.active {
            background-color: rgba(255, 255, 255, 0.15);
            text-decoration: none;
        }

        .jm-main {
            margin-left: 200px;
            padding: 30px;
        }

        .jm-navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #FFA500;
            color: white;
            padding: 12px 20px;
            font-weight: bold;
            font-size: 18px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .jm-tabla-container {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            margin-top: 20px;
            border: 1px solid #dee2e6;
        }

        .jm-tabla-header {
            background-color: #FFD700;
            color: #333;
            padding: 10px 15px;
            font-weight: bold;
            text-align: left;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .jm-tabla-header i {
            margin-left: 10px;
        }

        .jm-tabla-body {
            padding: 15px;
        }

        .jm-tabla {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .jm-tabla th, .jm-tabla td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .jm-tabla th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .jm-footer {
            text-align: center;
            padding: 15px;
            margin-top: 20px;
            font-size: 1em;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
        }

        .jm-link-volver {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: background-color 0.3s;
        }

        .jm-link-volver:hover {
            background-color: #0056b3;
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
                <li><a href="gestion_producto.php" class="jm-link"><i class="fas fa-box-open mr-2"></i> Gestión producto</a></li>
            <?php endif; ?>
            <li><a href="gestion_lote.php" class="jm-link"><i class="fas fa-cubes mr-2"></i> Gestión lote</a></li>
            <li><a href="listar_lotes_proveedor.php" class="jm-link active"><i class="fas fa-list mr-2"></i> Lotes por Proveedor</a></li>

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
            <span>Lotes por Proveedor</span>
        </div>

        <?php if (empty($proveedores)): ?>
            <p class="alert alert-warning">No hay proveedores registrados.</p>
        <?php else: ?>
            <?php foreach ($proveedores as $proveedor): ?>
                <div class="jm-tabla-container">
                    <div class="jm-tabla-header" onclick="toggleLotes('<?php echo $proveedor['id']; ?>')">
                        <?php echo htmlspecialchars($proveedor['nombre']); ?>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="jm-tabla-body" id="lotes-<?php echo $proveedor['id']; ?>" style="display: none;">
                        <?php $lotes = obtenerLotesProveedor($pdo, $proveedor['id']); ?>
                        <?php if (empty($lotes)): ?>
                            <p class="alert alert-info">No hay lotes asociados a este proveedor.</p>
                        <?php else: ?>
                            <table class="jm-tabla">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Precio por Proveedor</th>
                                        <th>Cantidad</th>
                                        <th>Fecha de Vencimiento</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($lotes as $lote): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($lote['nombre_producto']); ?></td>
                                            <td>$<?php echo htmlspecialchars(number_format($lote['precio_proveedor'], 2)); ?></td>
                                            <td><?php echo htmlspecialchars($lote['cantidad']); ?></td>
                                            <td><?php echo htmlspecialchars($lote['fecha_vencimiento']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <a href="index.php" class="jm-link-volver"><i class="fas fa-arrow-left mr-2"></i> Volver</a>

        <footer class="jm-footer">
            © 2025 Variedades Juanmarc. Todos los derechos reservados.
        </footer>
    </div>

    <script>
        function toggleLotes(proveedorId) {
            var lotesDiv = document.getElementById('lotes-' + proveedorId);
            var icon = document.querySelector('#lotes-' + proveedorId).parentNode.querySelector('.jm-tabla-header i');
            if (lotesDiv.style.display === 'none') {
                lotesDiv.style.display = 'block';
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            } else {
                lotesDiv.style.display = 'none';
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        }
    </script>
</body>
</html>