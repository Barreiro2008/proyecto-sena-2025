<?php
session_start();
include '../conexion.php';
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: listar_ventas.php?error=id_invalido");
    exit();
}

$venta_id = $_GET['id'];
$stmt_venta = $pdo->prepare("
    SELECT
        v.id AS venta_id,
        v.fecha_venta,
        v.total_venta,
        u.usuario AS nombre_usuario
    FROM ventas v
    JOIN usuarios u ON v.usuario_id = u.id
    WHERE v.id = :id
");
$stmt_venta->bindParam(':id', $venta_id);
$stmt_venta->execute();
$venta = $stmt_venta->fetch();

if (!$venta) {
    header("Location: listar_ventas.php?error=venta_no_encontrada");
    exit();
}
$stmt_detalles = $pdo->prepare("
    SELECT
        dv.cantidad,
        dv.precio_unitario,
        p.nombre AS nombre_producto
    FROM detalles_venta dv
    JOIN productos p ON dv.producto_id = p.id
    WHERE dv.venta_id = :venta_id
");
$stmt_detalles->bindParam(':venta_id', $venta_id);
$stmt_detalles->execute();
$detalles = $stmt_detalles->fetchAll();

// Función para calcular el subtotal
function calcularSubtotal($cantidad, $precio_unitario) {
    return $cantidad * $precio_unitario;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalles de Venta | Variedades Juanmarc</title>
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

        /* TARJETA DE INFORMACIÓN DE VENTA */
        .jm-venta-info {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        .jm-venta-info::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #FF8C00, #FFA500, #FFD700);
        }

        .jm-venta-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .jm-venta-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 35px;
            color: white;
            box-shadow: 0 10px 30px rgba(52, 152, 219, 0.4);
            position: relative;
            overflow: hidden;
        }

        .jm-venta-icon::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            animation: shine 3s infinite;
        }

        .jm-venta-titulo {
            font-size: 28px;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
        }

        .jm-venta-subtitle {
            color: #7f8c8d;
            margin-top: 8px;
            font-size: 16px;
        }

        .jm-venta-detalles {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .jm-detalle-item {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            border-left: 4px solid;
            transition: all 0.3s ease;
        }

        .jm-detalle-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .jm-detalle-item.fecha {
            border-left-color: #3498db;
        }

        .jm-detalle-item.usuario {
            border-left-color: #9b59b6;
        }

        .jm-detalle-item.total {
            border-left-color: #27ae60;
        }

        .jm-detalle-icon {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .jm-detalle-item.fecha .jm-detalle-icon {
            color: #3498db;
        }

        .jm-detalle-item.usuario .jm-detalle-icon {
            color: #9b59b6;
        }

        .jm-detalle-item.total .jm-detalle-icon {
            color: #27ae60;
        }

        .jm-detalle-label {
            font-size: 12px;
            font-weight: 600;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .jm-detalle-value {
            font-size: 18px;
            font-weight: 700;
            color: #2c3e50;
        }

        /* TABLA DE PRODUCTOS MODERNA */
        .jm-productos-section {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .jm-productos-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f1f3f4;
        }

        .jm-productos-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
        }

        .jm-productos-titulo {
            font-size: 22px;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
        }

        .jm-tabla-container {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
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
            padding: 18px 15px;
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
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            transform: scale(1.01);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .jm-tabla td {
            padding: 15px;
            text-align: center;
            vertical-align: middle;
            border: none;
            font-weight: 500;
        }

        .jm-tabla td:first-child {
            text-align: left;
            font-weight: 600;
            color: #2c3e50;
        }

        .precio-cell {
            font-weight: 700;
            color: #27ae60;
            font-size: 15px;
        }

        .cantidad-cell {
            font-weight: 700;
            color: #3498db;
            font-size: 15px;
        }

        .subtotal-cell {
            font-weight: 700;
            color: #e74c3c;
            font-size: 16px;
        }

        /* RESUMEN TOTAL */
        .jm-resumen-total {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 20px 30px;
            border-radius: 15px;
            margin-top: 20px;
            text-align: right;
        }

        .jm-resumen-label {
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 5px;
        }

        .jm-resumen-valor {
            font-size: 24px;
            font-weight: 700;
        }

        /* BOTÓN VOLVER */
        .jm-btn-volver {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 15px 30px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
        }

        .jm-btn-volver:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
            color: white;
            text-decoration: none;
        }

        /* ESTADO VACÍO */
        .jm-estado-vacio {
            text-align: center;
            padding: 40px 20px;
            color: #7f8c8d;
        }

        .jm-estado-vacio-icon {
            font-size: 60px;
            color: #bdc3c7;
            margin-bottom: 15px;
        }

        .jm-estado-vacio-mensaje {
            font-size: 16px;
            font-style: italic;
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

        /* ANIMACIONES */
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes shine {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }

        .jm-venta-info {
            animation: slideInUp 0.6s ease;
        }

        .jm-productos-section {
            animation: slideInUp 0.8s ease;
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
            
            .jm-venta-detalles {
                grid-template-columns: 1fr;
            }

            .jm-tabla td, .jm-tabla th {
                padding: 10px 5px;
                font-size: 12px;
            }

            .jm-venta-info, .jm-productos-section {
                padding: 20px;
            }
        }
    </style>
</head>

<body class="jm-body">
    <?php include 'sidebar.php'; ?>

    <div class="jm-main">
        <div class="jm-navbar">
            <h2><i class="fas fa-receipt mr-3"></i>Detalles de Venta</h2>
            
        </div>

        <!-- Información de la venta -->
        <div class="jm-venta-info">
            <div class="jm-venta-header">
                <div class="jm-venta-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <h2 class="jm-venta-titulo">Venta #<?php echo htmlspecialchars($venta['venta_id']); ?></h2>
                <p class="jm-venta-subtitle">Información detallada de la transacción</p>
            </div>

            <div class="jm-venta-detalles">
                <div class="jm-detalle-item fecha">
                    <div class="jm-detalle-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="jm-detalle-label">Fecha de Venta</div>
                    <div class="jm-detalle-value"><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($venta['fecha_venta']))); ?></div>
                </div>
                <div class="jm-detalle-item usuario">
                    <div class="jm-detalle-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="jm-detalle-label">Usuario</div>
                    <div class="jm-detalle-value"><?php echo htmlspecialchars($venta['nombre_usuario']); ?></div>
                </div>
                <div class="jm-detalle-item total">
                    <div class="jm-detalle-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="jm-detalle-label">Total Venta</div>
                    <div class="jm-detalle-value">$<?php echo htmlspecialchars(number_format($venta['total_venta'], 2)); ?></div>
                </div>
            </div>
        </div>

        <!-- Productos vendidos -->
        <div class="jm-productos-section">
            <div class="jm-productos-header">
                <div class="jm-productos-icon">
                    <i class="fas fa-shopping-basket"></i>
                </div>
                <h3 class="jm-productos-titulo">Productos Vendidos</h3>
            </div>

            <?php if (count($detalles) > 0): ?>
                <div class="jm-tabla-container">
                    <table class="jm-tabla">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Precio Unitario</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detalles as $detalle): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($detalle['nombre_producto']); ?></td>
                                    <td class="cantidad-cell"><?php echo htmlspecialchars($detalle['cantidad']); ?> unidades</td>
                                    <td class="precio-cell">$<?php echo htmlspecialchars(number_format($detalle['precio_unitario'], 2)); ?></td>
                                    <td class="subtotal-cell">$<?php echo htmlspecialchars(number_format(calcularSubtotal($detalle['cantidad'], $detalle['precio_unitario']), 2)); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="jm-resumen-total">
                    <div class="jm-resumen-label">Total de la Venta:</div>
                    <div class="jm-resumen-valor">$<?php echo htmlspecialchars(number_format($venta['total_venta'], 2)); ?></div>
                </div>
            <?php else: ?>
                <div class="jm-estado-vacio">
                    <div class="jm-estado-vacio-icon">
                        <i class="fas fa-shopping-basket"></i>
                    </div>
                    <div class="jm-estado-vacio-mensaje">No se encontraron productos en esta venta.</div>
                </div>
            <?php endif; ?>
        </div>

        <a href="listar_ventas.php" class="jm-btn-volver">
            <i class="fas fa-arrow-left"></i> Volver a Listar Ventas
        </a>

        <footer class="jm-footer">
            <i class="fas fa-heart" style="color: #e74c3c;"></i> © 2025 Variedades Juanmarc. Todos los derechos reservados.
        </footer>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
        // Animación de entrada para los elementos
        document.addEventListener('DOMContentLoaded', function() {
            // Animación para los detalles de la venta
            const detalleItems = document.querySelectorAll('.jm-detalle-item');
            detalleItems.forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    item.style.transition = 'all 0.6s ease';
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }, index * 150 + 300);
            });

            // Animación para las filas de la tabla
            const rows = document.querySelectorAll('.jm-tabla tbody tr');
            rows.forEach((row, index) => {
                row.style.opacity = '0';
                row.style.transform = 'translateX(-20px)';
                setTimeout(() => {
                    row.style.transition = 'all 0.5s ease';
                    row.style.opacity = '1';
                    row.style.transform = 'translateX(0)';
                }, (index * 100) + 800);
            });

            // Animación para el resumen total
            const resumenTotal = document.querySelector('.jm-resumen-total');
            if (resumenTotal) {
                resumenTotal.style.opacity = '0';
                resumenTotal.style.transform = 'scale(0.9)';
                setTimeout(() => {
                    resumenTotal.style.transition = 'all 0.6s ease';
                    resumenTotal.style.opacity = '1';
                    resumenTotal.style.transform = 'scale(1)';
                }, 1200);
            }

            // Efecto de conteo para el total
            const totalElement = document.querySelector('.jm-detalle-item.total .jm-detalle-value');
            if (totalElement) {
                const totalValue = parseFloat(totalElement.textContent.replace('$', '').replace(',', ''));
                let currentValue = 0;
                const increment = totalValue / 50;
                const timer = setInterval(() => {
                    currentValue += increment;
                    if (currentValue >= totalValue) {
                        currentValue = totalValue;
                        clearInterval(timer);
                    }
                    totalElement.textContent = '$' + currentValue.toFixed(2);
                }, 30);
            }
        });

        // Efecto de hover mejorado para las filas de la tabla
        document.querySelectorAll('.jm-tabla tbody tr').forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.02)';
            });
            
            row.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>
