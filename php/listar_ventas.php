<?php
session_start();
include 'conexion.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Obtener todas las ventas de la base de datos, incluyendo información del usuario
$stmt = $pdo->prepare("
    SELECT
        v.id AS venta_id,
        v.fecha_venta,
        v.total_venta,
        u.usuario AS nombre_usuario
    FROM ventas v
    JOIN usuarios u ON v.usuario_id = u.id
    ORDER BY v.fecha_venta DESC
");
$stmt->execute();
$ventas_raw = $stmt->fetchAll();

// Agrupar ventas por fecha (solo fecha, sin hora)
$ventas_por_fecha = [];
foreach ($ventas_raw as $venta) {
    $fecha = date('Y-m-d', strtotime($venta['fecha_venta'])); // solo fecha sin hora
    if (!isset($ventas_por_fecha[$fecha])) {
        $ventas_por_fecha[$fecha] = [];
    }
    $ventas_por_fecha[$fecha][] = $venta;
}

// Obtener las fechas disponibles ordenadas descendente
$fechas_disponibles = array_keys($ventas_por_fecha);
rsort($fechas_disponibles);

// Filtrar por fecha seleccionada
$fecha_seleccionada = $_GET['fecha'] ?? '';

if ($fecha_seleccionada && isset($ventas_por_fecha[$fecha_seleccionada])) {
    $ventas = $ventas_por_fecha[$fecha_seleccionada];
} else {
    // Mostrar todas las ventas sin filtrar
    $ventas = $ventas_raw;
}

// Mostrar mensajes si existen
if (isset($_GET['mensaje'])) {
    $mensaje = '<div class="alert alert-success">' . htmlspecialchars($_GET['mensaje']) . '</div>';
} elseif (isset($_GET['error'])) {
    $mensaje = '<div class="alert alert-danger">' . htmlspecialchars($_GET['error']) . '</div>';
} else {
    $mensaje = '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listar Ventas | Variedades Juanmarc</title>
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

        /* ALERTAS SÚPER BONITAS */
        .jm-alert-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            z-index: 10000;
            display: none;
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s ease;
        }

        .jm-alert-container {
            background: white;
            border-radius: 20px;
            padding: 0;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            transform: scale(0.8);
            animation: popIn 0.4s ease forwards;
            overflow: hidden;
        }

        .jm-alert-header {
            padding: 25px 30px 20px;
            text-align: center;
            position: relative;
        }

        .jm-alert-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 35px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .jm-alert-icon::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            animation: shine 2s infinite;
        }

        .jm-alert-success .jm-alert-icon {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            box-shadow: 0 10px 30px rgba(39, 174, 96, 0.4);
        }

        .jm-alert-info .jm-alert-icon {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            box-shadow: 0 10px 30px rgba(52, 152, 219, 0.4);
        }

        .jm-alert-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #2c3e50;
        }

        .jm-alert-subtitle {
            font-size: 16px;
            color: #7f8c8d;
            margin-bottom: 0;
        }

        .jm-alert-body {
            padding: 0 30px 25px;
            text-align: center;
        }

        .jm-alert-message {
            font-size: 16px;
            line-height: 1.6;
            color: #34495e;
            margin-bottom: 25px;
        }

        .jm-alert-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 25px;
            text-align: left;
        }

        .jm-alert-detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .jm-alert-detail-item:last-child {
            border-bottom: none;
        }

        .jm-alert-detail-label {
            font-weight: 600;
            color: #495057;
        }

        .jm-alert-detail-value {
            font-weight: 500;
            color: #6c757d;
        }

        .jm-alert-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .jm-alert-btn {
            padding: 12px 25px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-width: 120px;
            justify-content: center;
        }

        .jm-alert-btn-primary {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }

        .jm-alert-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
            color: white;
            text-decoration: none;
        }

        .jm-alert-close {
            position: absolute;
            top: 15px;
            right: 20px;
            background: none;
            border: none;
            font-size: 24px;
            color: #bdc3c7;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .jm-alert-close:hover {
            background: #ecf0f1;
            color: #7f8c8d;
            transform: rotate(90deg);
        }

        /* NOTIFICACIONES TOAST */
        .jm-toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .jm-toast {
            background: white;
            border-radius: 12px;
            padding: 16px 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 300px;
            transform: translateX(400px);
            animation: slideInRight 0.4s ease forwards;
            border-left: 4px solid;
        }

        .jm-toast.toast-success {
            border-left-color: #27ae60;
        }

        .jm-toast.toast-info {
            border-left-color: #3498db;
        }

        .jm-toast-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
        }

        .jm-toast.toast-success .jm-toast-icon {
            background: #27ae60;
        }

        .jm-toast.toast-info .jm-toast-icon {
            background: #3498db;
        }

        .jm-toast-content {
            flex: 1;
        }

        .jm-toast-title {
            font-weight: 600;
            font-size: 14px;
            color: #2c3e50;
            margin-bottom: 2px;
        }

        .jm-toast-message {
            font-size: 13px;
            color: #7f8c8d;
        }

        .jm-toast-close {
            background: none;
            border: none;
            color: #bdc3c7;
            cursor: pointer;
            font-size: 16px;
            padding: 0;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .jm-toast-close:hover {
            background: #ecf0f1;
            color: #7f8c8d;
        }

        /* FILTRO MODERNO */
        .jm-filtro-container {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .jm-filtro-container select {
            flex: 1;
            padding: 12px 18px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8f9fa;
            font-weight: 500;
        }

        .jm-filtro-container select:focus {
            outline: none;
            border-color: #FFA500;
            background: white;
            box-shadow: 0 0 0 3px rgba(255, 165, 0, 0.1);
        }

        .jm-filtro-btn {
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

        .jm-filtro-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 165, 0, 0.4);
        }

        /* ESTADÍSTICAS DE VENTAS */
        .jm-estadisticas {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .jm-stat-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .jm-stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        }

        .jm-stat-icon {
            font-size: 30px;
            margin-bottom: 10px;
        }

        .jm-stat-number {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .jm-stat-label {
            font-size: 14px;
            color: #6c757d;
            font-weight: 500;
        }

        .stat-ventas-hoy {
            border-left: 4px solid #27ae60;
        }

        .stat-ventas-hoy .jm-stat-icon {
            color: #27ae60;
        }

        .stat-ventas-hoy .jm-stat-number {
            color: #27ae60;
        }

        .stat-total-ventas {
            border-left: 4px solid #3498db;
        }

        .stat-total-ventas .jm-stat-icon {
            color: #3498db;
        }

        .stat-total-ventas .jm-stat-number {
            color: #3498db;
        }

        .stat-ingresos-hoy {
            border-left: 4px solid #f39c12;
        }

        .stat-ingresos-hoy .jm-stat-icon {
            color: #f39c12;
        }

        .stat-ingresos-hoy .jm-stat-number {
            color: #f39c12;
        }

        .stat-ingresos-total {
            border-left: 4px solid #e74c3c;
        }

        .stat-ingresos-total .jm-stat-icon {
            color: #e74c3c;
        }

        .stat-ingresos-total .jm-stat-number {
            color: #e74c3c;
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
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            transform: scale(1.01);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
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
            font-weight: 600;
            color: #2c3e50;
        }

        .total-venta {
            font-weight: 700;
            color: #27ae60;
            font-size: 16px;
        }

        .usuario-cell {
            font-weight: 600;
            color: #3498db;
        }

        /* BOTONES DE ACCIÓN */
        .jm-gestion-acciones {
            display: flex;
            gap: 8px;
            justify-content: center;
            align-items: center;
        }

        .btn-ver-detalles {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 15px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            box-shadow: 0 2px 8px rgba(52, 152, 219, 0.3);
        }

        .btn-ver-detalles:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
            color: white;
            text-decoration: none;
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
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes popIn {
            from {
                transform: scale(0.8);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        @keyframes shine {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }

        @keyframes slideInRight {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
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
            
            .jm-tabla td, .jm-tabla th {
                padding: 10px 5px;
                font-size: 12px;
            }

            .jm-estadisticas {
                grid-template-columns: 1fr;
            }

            .jm-filtro-container {
                flex-direction: column;
                gap: 10px;
            }

            .jm-filtro-container select {
                width: 100%;
            }
        }
    </style>
</head>

<body class="jm-body">
    <!-- Container para notificaciones toast -->
    <div class="jm-toast-container" id="toastContainer"></div>

    <!-- Overlay para alertas modales -->
    <div class="jm-alert-overlay" id="alertOverlay">
        <div class="jm-alert-container" id="alertContainer">
            <!-- El contenido se genera dinámicamente -->
        </div>
    </div>

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
            <li><a href="listar_ventas.php" class="jm-link active"><i class="fas fa-chart-line mr-2"></i> Listar Ventas</a></li>

            <li class="jm-menu-title">
                <img src="https://img.icons8.com/ios-filled/20/ffffff/warehouse.png" alt="icono almacen">
                Almacén
            </li>
            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
                <li><a href="gestion_producto.php" class="jm-link"><i class="fas fa-box-open mr-2"></i> Gestión producto</a></li>
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
            <h2><i class="fas fa-chart-line mr-3"></i>Listado de Ventas</h2>
            
        </div>

        <?php echo $mensaje; ?>

        <?php
        // Calcular estadísticas de ventas
        $hoy = date('Y-m-d');
        $ventasHoy = 0;
        $ingresosHoy = 0;
        $totalVentas = count($ventas_raw);
        $ingresosTotal = 0;

        foreach ($ventas_raw as $venta) {
            $fechaVenta = date('Y-m-d', strtotime($venta['fecha_venta']));
            $ingresosTotal += $venta['total_venta'];
            
            if ($fechaVenta === $hoy) {
                $ventasHoy++;
                $ingresosHoy += $venta['total_venta'];
            }
        }
        ?>

        <!-- Estadísticas de Ventas -->
        <div class="jm-estadisticas">
            <div class="jm-stat-card stat-ventas-hoy" onclick="mostrarVentasHoy()">
                <div class="jm-stat-icon"><i class="fas fa-calendar-day"></i></div>
                <div class="jm-stat-number"><?php echo $ventasHoy; ?></div>
                <div class="jm-stat-label">Ventas Hoy</div>
            </div>
            <div class="jm-stat-card stat-total-ventas" onclick="mostrarTodasVentas()">
                <div class="jm-stat-icon"><i class="fas fa-shopping-cart"></i></div>
                <div class="jm-stat-number"><?php echo $totalVentas; ?></div>
                <div class="jm-stat-label">Total Ventas</div>
            </div>
            <div class="jm-stat-card stat-ingresos-hoy" onclick="mostrarIngresosHoy()">
                <div class="jm-stat-icon"><i class="fas fa-dollar-sign"></i></div>
                <div class="jm-stat-number">$<?php echo number_format($ingresosHoy, 0); ?></div>
                <div class="jm-stat-label">Ingresos Hoy</div>
            </div>
            <div class="jm-stat-card stat-ingresos-total" onclick="mostrarIngresosTotal()">
                <div class="jm-stat-icon"><i class="fas fa-coins"></i></div>
                <div class="jm-stat-number">$<?php echo number_format($ingresosTotal, 0); ?></div>
                <div class="jm-stat-label">Ingresos Total</div>
            </div>
        </div>

        <!-- Filtro por fecha -->
        <form method="get" class="jm-filtro-container">
            <i class="fas fa-filter" style="color: #FFA500; font-size: 20px;"></i>
            <select name="fecha" onchange="this.form.submit()">
                <option value="">📅 Todas las fechas</option>
                <?php foreach ($fechas_disponibles as $fecha): ?>
                    <option value="<?php echo htmlspecialchars($fecha); ?>" <?php if ($fecha === $fecha_seleccionada) echo 'selected'; ?>>
                        <?php echo htmlspecialchars(date('d/m/Y', strtotime($fecha))); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <noscript><button type="submit" class="jm-filtro-btn">Filtrar</button></noscript>
        </form>

        <div class="jm-tabla-container">
            <table class="jm-tabla">
                <thead>
                    <tr>
                        <th>ID Venta</th>
                        <th>Fecha de Venta</th>
                        <th>Total Venta</th>
                        <th>Usuario</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($ventas) > 0): ?>
                        <?php foreach ($ventas as $venta): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($venta['venta_id']); ?></td>
                                <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($venta['fecha_venta']))); ?></td>
                                <td class="total-venta">$<?php echo htmlspecialchars(number_format($venta['total_venta'], 2)); ?></td>
                                <td class="usuario-cell"><?php echo htmlspecialchars($venta['nombre_usuario']); ?></td>
                                <td class="jm-gestion-acciones">
                                    <a href="detalles_venta.php?id=<?php echo htmlspecialchars($venta['venta_id']); ?>" class="btn-ver-detalles">
                                        <i class="fas fa-eye"></i> Ver Detalles
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center" style="padding: 40px; color: #6c757d; font-style: italic;">
                            <?php if ($fecha_seleccionada): ?>
                                No hay ventas registradas para la fecha seleccionada.
                            <?php else: ?>
                                No hay ventas registradas en el sistema.
                            <?php endif; ?>
                        </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <a href="index.php" class="btn-ver-detalles" style="margin-top: 30px; background: linear-gradient(135deg, #6c757d 0%, #495057 100%); box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);">
            <i class="fas fa-arrow-left"></i> Volver al Inicio
        </a>

        <footer class="jm-footer">
            <i class="fas fa-heart" style="color: #e74c3c;"></i> © 2025 Variedades Juanmarc. Todos los derechos reservados.
        </footer>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
        // Datos de PHP para JavaScript
        const ventasHoy = <?php echo $ventasHoy; ?>;
        const totalVentas = <?php echo $totalVentas; ?>;
        const ingresosHoy = <?php echo $ingresosHoy; ?>;
        const ingresosTotal = <?php echo $ingresosTotal; ?>;

        // Función para mostrar alertas modales bonitas
        function mostrarAlerta(tipo, titulo, subtitulo, mensaje, detalles = null) {
            const overlay = document.getElementById('alertOverlay');
            const container = document.getElementById('alertContainer');
            
            let iconoClase = '';
            let tipoClase = '';
            
            switch(tipo) {
                case 'success':
                    iconoClase = 'fas fa-check-circle';
                    tipoClase = 'jm-alert-success';
                    break;
                case 'info':
                    iconoClase = 'fas fa-info-circle';
                    tipoClase = 'jm-alert-info';
                    break;
            }
            
            let detallesHTML = '';
            if (detalles && detalles.length > 0) {
                detallesHTML = '<div class="jm-alert-details">';
                detalles.forEach(detalle => {
                    detallesHTML += `
                        <div class="jm-alert-detail-item">
                            <span class="jm-alert-detail-label">${detalle.label}</span>
                            <span class="jm-alert-detail-value">${detalle.value}</span>
                        </div>
                    `;
                });
                detallesHTML += '</div>';
            }
            
            container.innerHTML = `
                <div class="${tipoClase}">
                    <div class="jm-alert-header">
                        <button class="jm-alert-close" onclick="cerrarAlerta()">
                            <i class="fas fa-times"></i>
                        </button>
                        <div class="jm-alert-icon">
                            <i class="${iconoClase}"></i>
                        </div>
                        <h3 class="jm-alert-title">${titulo}</h3>
                        <p class="jm-alert-subtitle">${subtitulo}</p>
                    </div>
                    <div class="jm-alert-body">
                        <div class="jm-alert-message">${mensaje}</div>
                        ${detallesHTML}
                        <div class="jm-alert-actions">
                            <button class="jm-alert-btn jm-alert-btn-primary" onclick="cerrarAlerta()">
                                <i class="fas fa-check"></i> Entendido
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            overlay.style.display = 'flex';
        }

        function cerrarAlerta() {
            const overlay = document.getElementById('alertOverlay');
            overlay.style.display = 'none';
        }

        // Función para mostrar notificaciones toast
        function mostrarToast(tipo, titulo, mensaje, duracion = 4000) {
            const container = document.getElementById('toastContainer');
            
            let iconoClase = '';
            switch(tipo) {
                case 'success':
                    iconoClase = 'fas fa-check';
                    break;
                case 'info':
                    iconoClase = 'fas fa-info';
                    break;
            }
            
            const toast = document.createElement('div');
            toast.className = `jm-toast toast-${tipo}`;
            toast.innerHTML = `
                <div class="jm-toast-icon">
                    <i class="${iconoClase}"></i>
                </div>
                <div class="jm-toast-content">
                    <div class="jm-toast-title">${titulo}</div>
                    <div class="jm-toast-message">${mensaje}</div>
                </div>
                <button class="jm-toast-close" onclick="cerrarToast(this)">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            container.appendChild(toast);
            
            // Auto-cerrar después de la duración especificada
            setTimeout(() => {
                cerrarToast(toast.querySelector('.jm-toast-close'));
            }, duracion);
        }

        function cerrarToast(boton) {
            const toast = boton.closest('.jm-toast');
            toast.style.animation = 'slideOutRight 0.4s ease forwards';
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 400);
        }

        // Funciones específicas para estadísticas de ventas
        function mostrarVentasHoy() {
            if (ventasHoy > 0) {
                mostrarAlerta(
                    'success',
                    '📈 VENTAS DE HOY',
                    `Has realizado ${ventasHoy} venta(s) el día de hoy`,
                    '¡Excelente trabajo! Mantén el ritmo de ventas para alcanzar tus objetivos diarios.',
                    [
                        { label: 'Ventas realizadas', value: `${ventasHoy} venta(s)` },
                        { label: 'Ingresos generados', value: `$${ingresosHoy.toLocaleString()}` },
                        { label: 'Promedio por venta', value: `$${(ingresosHoy / ventasHoy).toLocaleString()}` }
                    ]
                );
            } else {
                mostrarToast('info', 'Sin ventas hoy', 'Aún no has realizado ventas el día de hoy. ¡Es hora de empezar!');
            }
        }

        function mostrarTodasVentas() {
            mostrarAlerta(
                'info',
                '📊 RESUMEN TOTAL DE VENTAS',
                `Tienes un total de ${totalVentas} venta(s) registradas`,
                'Este es el resumen completo de todas las ventas realizadas en tu sistema.',
                [
                    { label: 'Total de ventas', value: `${totalVentas} venta(s)` },
                    { label: 'Ingresos totales', value: `$${ingresosTotal.toLocaleString()}` },
                    { label: 'Promedio por venta', value: `$${(ingresosTotal / totalVentas).toLocaleString()}` },
                    { label: 'Ventas hoy', value: `${ventasHoy} venta(s)` }
                ]
            );
        }

        function mostrarIngresosHoy() {
            if (ingresosHoy > 0) {
                mostrarAlerta(
                    'success',
                    '💰 INGRESOS DE HOY',
                    `Has generado $${ingresosHoy.toLocaleString()} en ingresos hoy`,
                    '¡Fantástico! Tus ventas del día están generando buenos ingresos.',
                    [
                        { label: 'Ingresos del día', value: `$${ingresosHoy.toLocaleString()}` },
                        { label: 'Número de ventas', value: `${ventasHoy} venta(s)` },
                        { label: 'Ticket promedio', value: `$${ventasHoy > 0 ? (ingresosHoy / ventasHoy).toLocaleString() : '0'}` }
                    ]
                );
            } else {
                mostrarToast('info', 'Sin ingresos hoy', 'Aún no has generado ingresos el día de hoy.');
            }
        }

        function mostrarIngresosTotal() {
            mostrarAlerta(
                'info',
                '💎 INGRESOS TOTALES',
                `Has generado $${ingresosTotal.toLocaleString()} en total`,
                'Este es el resumen de todos los ingresos generados por tu negocio.',
                [
                    { label: 'Ingresos totales', value: `$${ingresosTotal.toLocaleString()}` },
                    { label: 'Total de ventas', value: `${totalVentas} venta(s)` },
                    { label: 'Ticket promedio', value: `$${totalVentas > 0 ? (ingresosTotal / totalVentas).toLocaleString() : '0'}` },
                    { label: 'Ingresos hoy', value: `$${ingresosHoy.toLocaleString()}` }
                ]
            );
        }

        // Animación de carga para las estadísticas
        document.addEventListener('DOMContentLoaded', function() {
            const statCards = document.querySelectorAll('.jm-stat-card');
            statCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 150);
            });

            // Animación de carga para las filas de la tabla
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

            // Mostrar mensaje de bienvenida
            setTimeout(() => {
                if (totalVentas === 0) {
                    mostrarToast('info', 'Sistema listo', 'Bienvenido al sistema de ventas. ¡Comienza a registrar tus primeras ventas!');
                } else if (ventasHoy > 0) {
                    mostrarToast('success', '¡Buen día!', `Llevas ${ventasHoy} venta(s) realizadas hoy. ¡Sigue así!`);
                } else {
                    mostrarToast('info', 'Nuevo día', 'Es un nuevo día para hacer ventas. ¡Mucho éxito!');
                }
            }, 1500);
        });

        // Cerrar alerta al hacer clic fuera
        document.getElementById('alertOverlay').addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarAlerta();
            }
        });
    </script>
</body>
</html>
