<?php
session_start();
include 'conexion.php';
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$stmt = $pdo->prepare("
    SELECT
        l.id AS lote_id,
        p.nombre AS producto_nombre,
        l.cantidad AS lote_cantidad,
        l.fecha_vencimiento AS lote_fecha_vencimiento
    FROM lotes l
    JOIN productos p ON l.producto_id = p.id
    ORDER BY l.fecha_vencimiento
");
$stmt->execute();
$lotes = $stmt->fetchAll();

// Mostrar mensajes si existen
if (isset($_GET['mensaje'])) {
    $mensaje = '<div class="alert alert-success">' . htmlspecialchars($_GET['mensaje']) . '</div>';
}
if (isset($_GET['error'])) {
    $mensaje = '<div class="alert alert-danger">' . htmlspecialchars($_GET['error']) . '</div>';
} else {
    $mensaje = '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Lotes | Variedades Juanmarc</title>
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

        .jm-alert-danger .jm-alert-icon {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            box-shadow: 0 10px 30px rgba(231, 76, 60, 0.4);
        }

        .jm-alert-warning .jm-alert-icon {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            box-shadow: 0 10px 30px rgba(243, 156, 18, 0.4);
        }

        .jm-alert-success .jm-alert-icon {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            box-shadow: 0 10px 30px rgba(39, 174, 96, 0.4);
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

        .jm-alert-btn-secondary {
            background: #f8f9fa;
            color: #6c757d;
            border: 2px solid #e9ecef;
        }

        .jm-alert-btn-secondary:hover {
            background: #e9ecef;
            color: #495057;
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

        .jm-toast.toast-warning {
            border-left-color: #f39c12;
        }

        .jm-toast.toast-danger {
            border-left-color: #e74c3c;
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

        .jm-toast.toast-warning .jm-toast-icon {
            background: #f39c12;
        }

        .jm-toast.toast-danger .jm-toast-icon {
            background: #e74c3c;
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

        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
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

        /* FILAS CON DIFERENTES ESTADOS DE VENCIMIENTO */
        .jm-tabla tbody tr.lote-vencido {
            background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
            border-left: 4px solid #e74c3c;
        }

        .jm-tabla tbody tr.lote-vencido:hover {
            background: linear-gradient(135deg, #ffcdd2 0%, #ef9a9a 100%);
            transform: scale(1.01);
            box-shadow: 0 4px 20px rgba(231, 76, 60, 0.3);
        }

        .jm-tabla tbody tr.lote-proximo-vencer {
            background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
            border-left: 4px solid #ff9800;
        }

        .jm-tabla tbody tr.lote-proximo-vencer:hover {
            background: linear-gradient(135deg, #ffe0b2 0%, #ffcc80 100%);
            transform: scale(1.01);
            box-shadow: 0 4px 20px rgba(255, 152, 0, 0.3);
        }

        .jm-tabla tbody tr.lote-bueno {
            background: linear-gradient(135deg, #e8f5e8 0%, #c8e6c9 100%);
            border-left: 4px solid #4caf50;
        }

        .jm-tabla tbody tr.lote-bueno:hover {
            background: linear-gradient(135deg, #c8e6c9 0%, #a5d6a7 100%);
            transform: scale(1.01);
            box-shadow: 0 4px 20px rgba(76, 175, 80, 0.3);
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

        .cantidad-cell {
            font-weight: 700;
            color: #27ae60;
            font-size: 15px;
        }

        .fecha-vencimiento {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 13px;
        }

        .fecha-vencido {
            background: #f8d7da;
            color: #721c24;
        }

        .fecha-proximo {
            background: #fff3cd;
            color: #856404;
        }

        .fecha-bueno {
            background: #d4edda;
            color: #155724;
        }

        .alerta-vencimiento {
            font-size: 14px;
            animation: pulse 2s infinite;
        }

        .alerta-vencido {
            color: #e74c3c;
        }

        .alerta-proximo {
            color: #ff9800;
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

        /* ESTADÍSTICAS DE LOTES */
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

        .stat-vencidos {
            border-left: 4px solid #e74c3c;
        }

        .stat-vencidos .jm-stat-icon {
            color: #e74c3c;
        }

        .stat-vencidos .jm-stat-number {
            color: #e74c3c;
        }

        .stat-proximos {
            border-left: 4px solid #ff9800;
        }

        .stat-proximos .jm-stat-icon {
            color: #ff9800;
        }

        .stat-proximos .jm-stat-number {
            color: #ff9800;
        }

        .stat-buenos {
            border-left: 4px solid #4caf50;
        }

        .stat-buenos .jm-stat-icon {
            color: #4caf50;
        }

        .stat-buenos .jm-stat-number {
            color: #4caf50;
        }

        .stat-total {
            border-left: 4px solid #FFA500;
        }

        .stat-total .jm-stat-icon {
            color: #FFA500;
        }

        .stat-total .jm-stat-number {
            color: #FFA500;
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

            .jm-estadisticas {
                grid-template-columns: 1fr;
            }

            .jm-alert-container {
                width: 95%;
                margin: 10px;
            }

            .jm-toast {
                min-width: 280px;
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
            <li><a href="listar_ventas.php" class="jm-link"><i class="fas fa-chart-line mr-2"></i> Listar Ventas</a></li>

            <li class="jm-menu-title">
                <img src="https://img.icons8.com/ios-filled/20/ffffff/warehouse.png" alt="icono almacen">
                Almacén
            </li>
            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
                <li><a href="gestion_producto.php" class="jm-link"><i class="fas fa-box-open mr-2"></i> Gestión producto</a></li>
            <?php endif; ?>
            <li><a href="gestion_lote.php" class="jm-link active"><i class="fas fa-cubes mr-2"></i> Gestión lote</a></li>

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
            <h2><i class="fas fa-cubes mr-3"></i>Gestión de Lotes</h2>
            <div class="jm-cart">
                <img src="https://img.icons8.com/ios-filled/24/ffffff/shopping-cart.png"/>
                <span class="jm-cart-badge">0</span>
            </div>
        </div>

        <?php echo $mensaje; ?>

        <?php
        // Calcular estadísticas de lotes
        $lotesVencidos = 0;
        $lotesProximosVencer = 0;
        $lotesBuenos = 0;
        $totalLotes = count($lotes);
        $lotesVencidosDetalle = [];
        $lotesProximosDetalle = [];

        foreach ($lotes as $lote) {
            $fechaVencimiento = strtotime($lote['lote_fecha_vencimiento']);
            $hoy = time();
            $diasRestantes = round(($fechaVencimiento - $hoy) / (60 * 60 * 24));
            
            if ($diasRestantes < 0) {
                $lotesVencidos++;
                $lotesVencidosDetalle[] = [
                    'producto' => $lote['producto_nombre'],
                    'cantidad' => $lote['lote_cantidad'],
                    'dias' => abs($diasRestantes)
                ];
            } elseif ($diasRestantes <= 30) {
                $lotesProximosVencer++;
                $lotesProximosDetalle[] = [
                    'producto' => $lote['producto_nombre'],
                    'cantidad' => $lote['lote_cantidad'],
                    'dias' => $diasRestantes
                ];
            } else {
                $lotesBuenos++;
            }
        }
        ?>

        <!-- Estadísticas de Lotes -->
        <div class="jm-estadisticas">
            <div class="jm-stat-card stat-vencidos" onclick="mostrarAlertaVencidos()">
                <div class="jm-stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="jm-stat-number"><?php echo $lotesVencidos; ?></div>
                <div class="jm-stat-label">Lotes Vencidos</div>
            </div>
            <div class="jm-stat-card stat-proximos" onclick="mostrarAlertaProximos()">
                <div class="jm-stat-icon"><i class="fas fa-clock"></i></div>
                <div class="jm-stat-number"><?php echo $lotesProximosVencer; ?></div>
                <div class="jm-stat-label">Próximos a Vencer</div>
            </div>
            <div class="jm-stat-card stat-buenos">
                <div class="jm-stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="jm-stat-number"><?php echo $lotesBuenos; ?></div>
                <div class="jm-stat-label">En Buen Estado</div>
            </div>
            <div class="jm-stat-card stat-total">
                <div class="jm-stat-icon"><i class="fas fa-boxes"></i></div>
                <div class="jm-stat-number"><?php echo $totalLotes; ?></div>
                <div class="jm-stat-label">Total de Lotes</div>
            </div>
        </div>

        <div class="jm-contenedor-botones">
            <a href="agregar_lote.php" class="btn-agregar">
                <i class="fas fa-plus"></i> Agregar Lote
            </a>
        </div>

        <div class="jm-tabla-container">
            <table class="jm-tabla">
                <thead>
                    <tr>
                        <th>ID Lote</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Fecha de Vencimiento</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($lotes) > 0): ?>
                        <?php foreach ($lotes as $lote): ?>
                            <?php
                            $fechaVencimiento = strtotime($lote['lote_fecha_vencimiento']);
                            $hoy = time();
                            $diasRestantes = round(($fechaVencimiento - $hoy) / (60 * 60 * 24));
                            
                            $claseVencimiento = '';
                            $estadoTexto = '';
                            $iconoEstado = '';
                            $claseEstado = '';
                            
                            if ($diasRestantes < 0) {
                                $claseVencimiento = 'lote-vencido';
                                $estadoTexto = 'VENCIDO';
                                $iconoEstado = 'fas fa-times-circle alerta-vencimiento alerta-vencido';
                                $claseEstado = 'fecha-vencido';
                            } elseif ($diasRestantes <= 30) {
                                $claseVencimiento = 'lote-proximo-vencer';
                                $estadoTexto = $diasRestantes . ' días restantes';
                                $iconoEstado = 'fas fa-exclamation-triangle alerta-vencimiento alerta-proximo';
                                $claseEstado = 'fecha-proximo';
                            } else {
                                $claseVencimiento = 'lote-bueno';
                                $estadoTexto = $diasRestantes . ' días restantes';
                                $iconoEstado = 'fas fa-check-circle';
                                $claseEstado = 'fecha-bueno';
                            }
                            ?>
                            <tr class="<?php echo $claseVencimiento; ?>">
                                <td><?php echo htmlspecialchars($lote['lote_id']); ?></td>
                                <td style="text-align: left;"><?php echo htmlspecialchars($lote['producto_nombre']); ?></td>
                                <td class="cantidad-cell"><?php echo htmlspecialchars($lote['lote_cantidad']); ?> unidades</td>
                                <td>
                                    <span class="fecha-vencimiento <?php echo $claseEstado; ?>">
                                        <?php echo htmlspecialchars(date('d/m/Y', strtotime($lote['lote_fecha_vencimiento']))); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="fecha-vencimiento <?php echo $claseEstado; ?>">
                                        <i class="<?php echo $iconoEstado; ?>"></i>
                                        <?php echo $estadoTexto; ?>
                                    </span>
                                </td>
                                <td class="jm-gestion-acciones">
                                    <a href="editar_lote.php?id=<?php echo htmlspecialchars($lote['lote_id']); ?>" class="btn-accion btn-editar">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <a href="eliminar_lote.php?id=<?php echo htmlspecialchars($lote['lote_id']); ?>" class="btn-accion btn-eliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar este lote?')">
                                        <i class="fas fa-trash-alt"></i> Eliminar
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center" style="padding: 40px; color: #6c757d; font-style: italic;">No hay lotes registrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <a href="index.php" class="btn-agregar" style="margin-top: 30px;">
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
        // Datos de PHP para JavaScript
        const lotesVencidos = <?php echo $lotesVencidos; ?>;
        const lotesProximos = <?php echo $lotesProximosVencer; ?>;
        const lotesVencidosDetalle = <?php echo json_encode($lotesVencidosDetalle); ?>;
        const lotesProximosDetalle = <?php echo json_encode($lotesProximosDetalle); ?>;

        // Función para mostrar alertas modales bonitas
        function mostrarAlerta(tipo, titulo, subtitulo, mensaje, detalles = null, acciones = null) {
            const overlay = document.getElementById('alertOverlay');
            const container = document.getElementById('alertContainer');
            
            let iconoClase = '';
            let tipoClase = '';
            
            switch(tipo) {
                case 'danger':
                    iconoClase = 'fas fa-exclamation-triangle';
                    tipoClase = 'jm-alert-danger';
                    break;
                case 'warning':
                    iconoClase = 'fas fa-clock';
                    tipoClase = 'jm-alert-warning';
                    break;
                case 'success':
                    iconoClase = 'fas fa-check-circle';
                    tipoClase = 'jm-alert-success';
                    break;
            }
            
            let detallesHTML = '';
            if (detalles && detalles.length > 0) {
                detallesHTML = '<div class="jm-alert-details">';
                detalles.forEach(detalle => {
                    detallesHTML += `
                        <div class="jm-alert-detail-item">
                            <span class="jm-alert-detail-label">${detalle.producto}</span>
                            <span class="jm-alert-detail-value">${detalle.cantidad} unidades - ${detalle.dias} días</span>
                        </div>
                    `;
                });
                detallesHTML += '</div>';
            }
            
            let accionesHTML = '';
            if (acciones) {
                accionesHTML = '<div class="jm-alert-actions">';
                acciones.forEach(accion => {
                    accionesHTML += `<button class="jm-alert-btn ${accion.clase}" onclick="${accion.onclick}">${accion.texto}</button>`;
                });
                accionesHTML += '</div>';
            } else {
                accionesHTML = `
                    <div class="jm-alert-actions">
                        <button class="jm-alert-btn jm-alert-btn-primary" onclick="cerrarAlerta()">
                            <i class="fas fa-check"></i> Entendido
                        </button>
                    </div>
                `;
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
                        ${accionesHTML}
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
        function mostrarToast(tipo, titulo, mensaje, duracion = 5000) {
            const container = document.getElementById('toastContainer');
            
            let iconoClase = '';
            switch(tipo) {
                case 'success':
                    iconoClase = 'fas fa-check';
                    break;
                case 'warning':
                    iconoClase = 'fas fa-exclamation-triangle';
                    break;
                case 'danger':
                    iconoClase = 'fas fa-times';
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

        // Funciones específicas para lotes
        function mostrarAlertaVencidos() {
            if (lotesVencidos > 0) {
                mostrarAlerta(
                    'danger',
                    '🚨 LOTES VENCIDOS',
                    `Tienes ${lotesVencidos} lote(s) que ya han vencido`,
                    'Estos productos pueden representar un riesgo para la salud y deben ser retirados inmediatamente del inventario.',
                    lotesVencidosDetalle.map(lote => ({
                        producto: lote.producto,
                        cantidad: lote.cantidad,
                        dias: `Vencido hace ${lote.dias} día(s)`
                    })),
                    [
                        {
                            texto: '<i class="fas fa-trash"></i> Retirar Lotes',
                            clase: 'jm-alert-btn-primary',
                            onclick: 'retirarLotesVencidos()'
                        },
                        {
                            texto: 'Cerrar',
                            clase: 'jm-alert-btn-secondary',
                            onclick: 'cerrarAlerta()'
                        }
                    ]
                );
            } else {
                mostrarToast('success', '¡Excelente!', 'No tienes lotes vencidos en este momento.');
            }
        }

        function mostrarAlertaProximos() {
            if (lotesProximos > 0) {
                mostrarAlerta(
                    'warning',
                    '⏰ LOTES PRÓXIMOS A VENCER',
                    `Tienes ${lotesProximos} lote(s) que vencen en los próximos 30 días`,
                    'Te recomendamos priorizar la venta de estos productos o contactar a los proveedores para planificar nuevos pedidos.',
                    lotesProximosDetalle.map(lote => ({
                        producto: lote.producto,
                        cantidad: lote.cantidad,
                        dias: `${lote.dias} día(s) restantes`
                    })),
                    [
                        {
                            texto: '<i class="fas fa-bullhorn"></i> Promocionar',
                            clase: 'jm-alert-btn-primary',
                            onclick: 'promocionarLotes()'
                        },
                        {
                            texto: 'Cerrar',
                            clase: 'jm-alert-btn-secondary',
                            onclick: 'cerrarAlerta()'
                        }
                    ]
                );
            } else {
                mostrarToast('success', '¡Perfecto!', 'Todos tus lotes tienen fechas de vencimiento lejanas.');
            }
        }

        function retirarLotesVencidos() {
            cerrarAlerta();
            mostrarToast('warning', 'Acción requerida', 'Procede a retirar los lotes vencidos del inventario.');
        }

        function promocionarLotes() {
            cerrarAlerta();
            mostrarToast('success', 'Buena idea', 'Considera hacer promociones para estos productos.');
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

            // Mostrar alertas automáticas después de cargar
            setTimeout(() => {
                if (lotesVencidos > 0) {
                    mostrarToast('danger', 'Atención Urgente', `Tienes ${lotesVencidos} lote(s) vencido(s). Haz clic en la tarjeta roja para ver detalles.`);
                } else if (lotesProximos > 0) {
                    mostrarToast('warning', 'Recordatorio', `${lotesProximos} lote(s) vencen pronto. Haz clic en la tarjeta naranja para ver detalles.`);
                } else if (lotesVencidos === 0 && lotesProximos === 0 && <?php echo $totalLotes; ?> > 0) {
                    mostrarToast('success', '¡Excelente gestión!', 'Todos tus lotes están en perfecto estado.');
                }
            }, 2000);
        });

        // Cerrar alerta al hacer clic fuera
        document.getElementById('alertOverlay').addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarAlerta();
            }
        });

        // Función para resaltar lotes críticos
        function resaltarLotesCriticos() {
            const lotesVencidos = document.querySelectorAll('.lote-vencido');
            
            // Efecto de parpadeo para lotes vencidos
            setInterval(() => {
                lotesVencidos.forEach(lote => {
                    lote.style.boxShadow = lote.style.boxShadow === 'none' ? 
                        '0 0 20px rgba(231, 76, 60, 0.5)' : 'none';
                });
            }, 3000);
        }

        // Ejecutar al cargar la página
        setTimeout(resaltarLotesCriticos, 3000);
    </script>
</body>
</html>
