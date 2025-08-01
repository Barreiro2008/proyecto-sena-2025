<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$usuario = $_SESSION['usuario'];

// Obtener estadísticas del sistema
try {
    // Contar productos
    $stmt_productos = $pdo->query("SELECT COUNT(*) as total FROM productos");
    $total_productos = $stmt_productos->fetch()['total'] ?? 0;
    
    // Contar lotes
    $stmt_lotes = $pdo->query("SELECT COUNT(*) as total FROM lotes");
    $total_lotes = $stmt_lotes->fetch()['total'] ?? 0;
    
    // Contar proveedores
    $stmt_proveedores = $pdo->query("SELECT COUNT(*) as total FROM proveedores");
    $total_proveedores = $stmt_proveedores->fetch()['total'] ?? 0;
    
    // Contar ventas del mes actual
    $stmt_ventas = $pdo->query("SELECT COUNT(*) as total FROM ventas WHERE MONTH(fecha_venta) = MONTH(CURDATE()) AND YEAR(fecha_venta) = YEAR(CURDATE())");
    $ventas_mes = $stmt_ventas->fetch()['total'] ?? 0;
    
    // Productos con stock bajo
    $stmt_stock_bajo = $pdo->query("SELECT COUNT(*) as total FROM productos WHERE stock < 10");
    $productos_stock_bajo = $stmt_stock_bajo->fetch()['total'] ?? 0;
    
} catch (PDOException $e) {
    $total_productos = 0;
    $total_lotes = 0;
    $total_proveedores = 0;
    $ventas_mes = 0;
    $productos_stock_bajo = 0;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | Variedades Juanmarc</title>
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
        /* Navbar mejorado */
        .jm-navbar {
            background: linear-gradient(135deg, #FF8C00 0%, #FFA500 100%);
            color: white;
            padding: 25px 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(255, 165, 0, 0.3);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .jm-navbar-left {
            display: flex;
            flex-direction: column;
        }
        .jm-navbar-left h2 {
            margin: 0;
            font-size: 26px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .jm-navbar-left p {
            margin: 5px 0 0 0;
            font-size: 15px;
            opacity: 0.9;
        }
        .jm-navbar-right {
            text-align: right;
            display: flex;
            flex-direction: column;
        }
        .jm-time {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .jm-date {
            font-size: 14px;
            opacity: 0.9;
        }
        /* Estadísticas - MÁS BONITAS */
        .jm-estadisticas {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 35px;
        }
        .jm-stat-card {
            background: linear-gradient(145deg, #ffffff, #f0f0f0);
            padding: 30px;
            border-radius: 25px;
            box-shadow:
                0 20px 40px rgba(0, 0, 0, 0.1),
               inset 0 1px 0 rgba(255, 255, 255, 0.6);
            text-align: center;
            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.8);
        }
        .jm-stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, #FF8C00, #FFA500, #FFD700);
            border-radius: 25px 25px 0 0;
        }
        .jm-stat-card::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transform: rotate(45deg);
            transition: all 0.6s;
            opacity: 0;
        }
        .jm-stat-card:hover::after {
            animation: shine 0.8s ease-in-out;
        }
        @keyframes shine {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); opacity: 0; }
            50% { opacity: 1; }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); opacity: 0; }
        }
        .jm-stat-card:hover {
            transform: translateY(-20px) scale(1.05);
            box-shadow:
                0 30px 60px rgba(0, 0, 0, 0.2),
               0 0 0 1px rgba(255, 140, 0, 0.1),
               inset 0 1px 0 rgba(255, 255, 255, 0.8);
        }
        .jm-stat-icon {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #FF8C00, #FFA500);
            border-radius: 50%;
            margin-bottom: 25px;
            color: white;
            font-size: 32px;
            box-shadow:
                0 15px 30px rgba(255, 140, 0, 0.4),
               inset 0 1px 0 rgba(255, 255, 255, 0.3);
            transition: all 0.4s ease;
        }
        .jm-stat-icon::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 3px solid rgba(255, 140, 0, 0.3);
            animation: pulse 3s infinite;
        }
        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 1;
            }
            100% {
                transform: scale(1.6);
                opacity: 0;
            }
        }
        .jm-stat-card:hover .jm-stat-icon {
            transform: scale(1.1) rotate(10deg);
            box-shadow:
                0 20px 40px rgba(255, 140, 0, 0.6),
               inset 0 1px 0 rgba(255, 255, 255, 0.4);
        }
        .jm-stat-number {
            font-size: 42px;
            font-weight: 800;
            color: #333;
            margin-bottom: 12px;
            position: relative;
            display: inline-block;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .jm-stat-label {
            font-size: 16px;
            color: #666;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            position: relative;
        }
        /* Alerta de stock bajo - MÁS LLAMATIVA */
        .jm-alert-stock::before {
            background: linear-gradient(90deg, #ff4757, #ff6b6b, #ff8a80);
        }
        .jm-alert-stock .jm-stat-icon {
            background: linear-gradient(135deg, #ff4757, #ff6b6b);
            box-shadow:
                0 15px 30px rgba(255, 71, 87, 0.5),
               inset 0 1px 0 rgba(255, 255, 255, 0.3);
            animation: alertPulse 2s infinite;
        }
        @keyframes alertPulse {
            0%, 100% { 
                transform: scale(1);
               box-shadow:
                   0 15px 30px rgba(255, 71, 87, 0.5),
                  inset 0 1px 0 rgba(255, 255, 255, 0.3);
            }
            50% { 
                transform: scale(1.1);
               box-shadow:
                   0 20px 40px rgba(255, 71, 87, 0.7),
                  inset 0 1px 0 rgba(255, 255, 255, 0.4);
            }
        }
        /* Accesos Rápidos - MÁS BONITOS */
        .jm-accesos-rapidos {
            margin-bottom: 40px;
        }
        .jm-accesos-rapidos h3 {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            margin-bottom: 30px;
            text-align: center;
            position: relative;
            padding-bottom: 20px;
        }
        .jm-accesos-rapidos h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: linear-gradient(90deg, #FF8C00, #FFA500, #FFD700);
            border-radius: 2px;
            box-shadow: 0 2px 8px rgba(255, 140, 0, 0.3);
        }
        .jm-acciones-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 30px;
        }
        .jm-accion-card {
            background: linear-gradient(145deg, #ffffff, #f0f0f0);
            border-radius: 25px;
            padding: 30px;
            text-align: center;
            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            text-decoration: none;
            color: #333;
            box-shadow:
                0 20px 40px rgba(0, 0, 0, 0.1),
               inset 0 1px 0 rgba(255, 255, 255, 0.6);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.8);
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .jm-accion-card::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #FF8C00, #FFA500);
            top: 0;
            left: 0;
            opacity: 0;
            z-index: -1;
            transition: opacity 0.5s ease;
            border-radius: 25px;
        }
        .jm-accion-card::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transform: rotate(45deg);
            transition: all 0.6s;
            opacity: 0;
        }
        .jm-accion-card:hover::after {
            animation: shine 0.8s ease-in-out;
        }
        .jm-accion-card:hover {
            transform: translateY(-20px) scale(1.05);
            box-shadow:
                0 30px 60px rgba(0, 0, 0, 0.2),
               0 0 0 1px rgba(255, 140, 0, 0.2);
            color: white;
            text-decoration: none;
        }
        .jm-accion-card:hover::before {
            opacity: 1;
        }
        .jm-accion-card:hover .jm-accion-titulo,
        .jm-accion-card:hover .jm-accion-desc {
            color: white;
        }
        .jm-accion-icon {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 90px;
            height: 90px;
            background: linear-gradient(145deg, #f8f9fa, #e9ecef);
            border-radius: 50%;
            margin-bottom: 25px;
            color: #FF8C00;
            font-size: 36px;
            transition: all 0.5s ease;
            box-shadow:
                0 10px 30px rgba(0, 0, 0, 0.1),
               inset 0 1px 0 rgba(255, 255, 255, 0.8);
        }
        .jm-accion-card:hover .jm-accion-icon {
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
            transform: scale(1.15) rotate(15deg);
            box-shadow:
                0 15px 40px rgba(0, 0, 0, 0.2),
               inset 0 1px 0 rgba(255, 255, 255, 0.9);
            color: #FF8C00;
        }
        .jm-accion-titulo {
            font-size: 19px;
            font-weight: 700;
            margin-bottom: 12px;
            transition: color 0.5s ease;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        .jm-accion-desc {
            font-size: 14px;
            color: #6c757d;
            transition: color 0.5s ease;
            line-height: 1.5;
        }
        /* Botón de cerrar sesión - SÚPER BONITO */
        .jm-logout-section {
            display: flex;
            justify-content: center;
            margin: 50px 0;
        }
        .jm-btn-logout {
            background: linear-gradient(145deg, #ffffff, #f0f0f0);
            border-radius: 25px;
            padding: 20px 40px;
            text-decoration: none;
            color: #dc3545;
            font-size: 18px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow:
                0 15px 35px rgba(220, 53, 69, 0.2),
               inset 0 1px 0 rgba(255, 255, 255, 0.6);
            border: 2px solid rgba(220, 53, 69, 0.2);
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .jm-btn-logout::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #dc3545, #c82333);
            top: 0;
            left: 0;
            opacity: 0;
            z-index: -1;
            transition: opacity 0.5s ease;
            border-radius: 25px;
        }
        .jm-btn-logout::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transform: rotate(45deg);
            transition: all 0.6s;
            opacity: 0;
        }
        .jm-btn-logout:hover::after {
            animation: shine 0.8s ease-in-out;
        }
        .jm-btn-logout:hover {
            transform: translateY(-8px) scale(1.05);
            box-shadow:
                0 25px 50px rgba(220, 53, 69, 0.4),
               0 0 0 1px rgba(220, 53, 69, 0.3);
            color: white;
            text-decoration: none;
        }
        .jm-btn-logout:hover::before {
            opacity: 1;
        }
        .jm-btn-logout i {
            font-size: 20px;
            transition: transform 0.3s ease;
        }
        .jm-btn-logout:hover i {
            transform: rotate(10deg) scale(1.1);
        }

        /* FOOTER MEJORADO - SÚPER BONITO */
        .jm-footer {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 50%, #2c3e50 100%);
            color: white;
            margin-top: 60px;
            border-radius: 25px 25px 0 0;
            box-shadow: 
                0 -10px 40px rgba(0, 0, 0, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
        }

        .jm-footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, #FF8C00, #FFA500, #FFD700, #FFA500, #FF8C00);
            background-size: 200% 100%;
            animation: gradientMove 3s ease-in-out infinite;
        }

        @keyframes gradientMove {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .jm-footer-content {
            padding: 50px 40px 30px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .jm-footer-section {
            text-align: center;
        }

        .jm-footer-section h4 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 25px;
            color: #FFD700;
            position: relative;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .jm-footer-section h4::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background: linear-gradient(90deg, #FF8C00, #FFA500);
            border-radius: 2px;
        }

        .jm-footer-info {
            line-height: 1.8;
            color: #bdc3c7;
            font-size: 15px;
        }

        .jm-footer-info p {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .jm-footer-info i {
            color: #FFA500;
            font-size: 18px;
            width: 20px;
            text-align: center;
        }

        .jm-footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .jm-footer-links li {
            margin-bottom: 12px;
        }

        .jm-footer-links a {
            color: #bdc3c7;
            text-decoration: none;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 8px 15px;
            border-radius: 8px;
        }

        .jm-footer-links a:hover {
            color: #FFD700;
            background: rgba(255, 165, 0, 0.1);
            transform: translateX(5px);
            text-decoration: none;
        }

        .jm-footer-links i {
            color: #FFA500;
            font-size: 16px;
            width: 18px;
            text-align: center;
        }

        .jm-social-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }

        .jm-social-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            background: linear-gradient(145deg, #34495e, #2c3e50);
            border-radius: 50%;
            color: #bdc3c7;
            font-size: 20px;
            text-decoration: none;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 
                0 8px 20px rgba(0, 0, 0, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
        }

        .jm-social-link::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #FF8C00, #FFA500);
            top: 0;
            left: 0;
            opacity: 0;
            z-index: -1;
            transition: opacity 0.4s ease;
            border-radius: 50%;
        }

        .jm-social-link:hover {
            transform: translateY(-8px) scale(1.1);
            color: white;
            text-decoration: none;
            box-shadow: 
                0 15px 30px rgba(255, 140, 0, 0.4),
                0 0 0 1px rgba(255, 140, 0, 0.2);
        }

        .jm-social-link:hover::before {
            opacity: 1;
        }

        .jm-footer-bottom {
            background: rgba(0, 0, 0, 0.2);
            padding: 25px 40px;
            text-align: center;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 30px;
        }

        .jm-footer-bottom p {
            margin: 0;
            color: #95a5a6;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .jm-footer-bottom i {
            color: #e74c3c;
            animation: heartbeat 2s infinite;
        }

        @keyframes heartbeat {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }

        .jm-footer-version {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 12px;
            color: #7f8c8d;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
        }

        .jm-version-badge {
            background: linear-gradient(135deg, #FF8C00, #FFA500);
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 8px rgba(255, 140, 0, 0.3);
        }

        /* Responsive */
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
            
            .jm-estadisticas {
                grid-template-columns: 1fr;
            }
            
            .jm-acciones-grid {
                grid-template-columns: 1fr;
            }
            
            .jm-navbar {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .jm-navbar-right {
                text-align: center;
            }

            .jm-footer-content {
                padding: 40px 20px 20px;
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .jm-footer-bottom {
                padding: 20px;
            }

            .jm-footer-bottom p {
                flex-direction: column;
                gap: 5px;
            }

            .jm-footer-version {
                flex-direction: column;
                gap: 10px;
            }
        }
        
        /* Animación de contadores */
        .counter {
            display: inline-block;
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="jm-body">
    <?php include 'sidebarindex.php'; ?>
    
    <div class="jm-main">
        <!-- Navbar mejorado con bienvenida y hora/fecha -->
        <div class="jm-navbar">
            <div class="jm-navbar-left">
                <h2><i class="fas fa-tachometer-alt"></i> ¡Bienvenido, <?php echo htmlspecialchars($usuario); ?>!</h2>
                <p>Panel de control - Variedades Juanmarc</p>
            </div>
            <div class="jm-navbar-right">
                <div class="jm-time" id="current-time"></div>
                <div class="jm-date" id="current-date"></div>
            </div>
        </div>

        <div class="jm-estadisticas">
            <div class="jm-stat-card">
                <div class="jm-stat-icon"><i class="fas fa-box"></i></div>
                <div class="jm-stat-number counter" data-target="<?php echo $total_productos; ?>">0</div>
                <div class="jm-stat-label">Productos Totales</div>
            </div>
            <div class="jm-stat-card">
                <div class="jm-stat-icon"><i class="fas fa-cubes"></i></div>
                <div class="jm-stat-number counter" data-target="<?php echo $total_lotes; ?>">0</div>
                <div class="jm-stat-label">Lotes Activos</div>
            </div>
            <div class="jm-stat-card">
                <div class="jm-stat-icon"><i class="fas fa-truck"></i></div>
                <div class="jm-stat-number counter" data-target="<?php echo $total_proveedores; ?>">0</div>
                <div class="jm-stat-label">Proveedores</div>
            </div>
            <div class="jm-stat-card">
                <div class="jm-stat-icon"><i class="fas fa-chart-line"></i></div>
                <div class="jm-stat-number counter" data-target="<?php echo $ventas_mes; ?>">0</div>
                <div class="jm-stat-label">Ventas del Mes</div>
            </div>
            <?php if ($productos_stock_bajo > 0): ?>
            <div class="jm-stat-card jm-alert-stock">
                <div class="jm-stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="jm-stat-number counter" data-target="<?php echo $productos_stock_bajo; ?>">0</div>
                <div class="jm-stat-label">Stock Bajo</div>
            </div>
            <?php endif; ?>
        </div>

        <div class="jm-accesos-rapidos">
            <h3>Accesos Rápidos</h3>
            <div class="jm-acciones-grid">
                <a href="gestion/gestion_producto.php" class="jm-accion-card">
                    <div class="jm-accion-icon"><i class="fas fa-box-open"></i></div>
                    <div class="jm-accion-titulo">Gestión de Productos</div>
                    <div class="jm-accion-desc">Administrar inventario y productos</div>
                </a>
                <a href="gestion/gestion_lote.php" class="jm-accion-card">
                    <div class="jm-accion-icon"><i class="fas fa-cubes"></i></div>
                    <div class="jm-accion-titulo">Gestión de Lotes</div>
                    <div class="jm-accion-desc">Controlar lotes y existencias</div>
                </a>
                <a href="gestion/listar_ventas.php" class="jm-accion-card">
                    <div class="jm-accion-icon"><i class="fas fa-shopping-cart"></i></div>
                    <div class="jm-accion-titulo">Listar Ventas</div>
                    <div class="jm-accion-desc">Ver historial de ventas</div>
                </a>
                <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
                    <a href="gestion/gestion_proveedor.php" class="jm-accion-card">
                        <div class="jm-accion-icon"><i class="fas fa-truck"></i></div>
                        <div class="jm-accion-titulo">Gestión de Proveedores</div>
                        <div class="jm-accion-desc">Administrar proveedores</div>
                    </a>
                    <a href="gestion/gestion_usuario.php" class="jm-accion-card">
                        <div class="jm-accion-icon"><i class="fas fa-users-cog"></i></div>
                        <div class="jm-accion-titulo">Gestión de Usuarios</div>
                        <div class="jm-accion-desc">Administrar usuarios del sistema</div>
                    </a>
                <?php endif; ?>
                <a href="gestion/datos_personales.php" class="jm-accion-card">
                    <div class="jm-accion-icon"><i class="fas fa-user-circle"></i></div>
                    <div class="jm-accion-titulo">Datos Personales</div>
                    <div class="jm-accion-desc">Actualizar datos personales</div>
                </a>
            </div>
        </div>

        <!-- Botón de cerrar sesión súper bonito -->
        <div class="jm-logout-section">
            <a href="logout.php" class="jm-btn-logout">
                <i class="fas fa-sign-out-alt"></i>
                Cerrar Sesión
            </a>
        </div>

        <!-- FOOTER MEJORADO -->
        <footer class="jm-footer">
            <div class="jm-footer-content">
                <!-- Información de la empresa -->
                <div class="jm-footer-section">
                    <h4><i class="fas fa-store"></i> Variedades Juanmarc</h4>
                    <div class="jm-footer-info">
                        <p><i class="fas fa-map-marker-alt"></i> Carrera 3A No 1A 40, Teruel Huila</p>
                        <p><i class="fas fa-phone"></i> +57 322 709 7033</p>
                        <p><i class="fas fa-clock"></i> Lun - Dom: 7:00 AM - 10:00 PM</p>
                    </div>
                </div>

                <!-- Enlaces útiles -->
                <div class="jm-footer-section">
                    <h4><i class="fas fa-link"></i> Enlaces Útiles</h4>
                    <ul class="jm-footer-links">
                        <li><a href="gestion/gestion_producto.php"><i class="fas fa-box"></i> Productos</a></li>
                        <li><a href="gestion/listar_ventas.php"><i class="fas fa-chart-bar"></i> Reportes</a></li>
                        <li><a href="gestion/gestion_proveedor.php"><i class="fas fa-truck"></i> Proveedores</a></li>
                        <li><a href="gestion/datos_personales.php"><i class="fas fa-user-cog"></i> Mi Perfil</a></li>
                        <li><a href="#"><i class="fas fa-question-circle"></i> Ayuda</a></li>
                    </ul>
                </div>

                <!-- Redes sociales y contacto -->
                <div class="jm-footer-section">
                    <h4><i class="fas fa-share-alt"></i>Contacto de los Desarrolladores</h4>
                    <div class="jm-footer-info">
                        <p>Mantente conectado con nosotros</p>
                    </div>
                    <div class="jm-social-links">
        
                        <a href="https://wa.me/573227097033?text=Hola%2C%20estoy%20interesado%20en%20más%20información" class="jm-social-link" title="WhatsApp">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="jm-footer-bottom">
                <p>
                    <i class="fas fa-heart"></i>
                    © 2025 Variedades Juanmarc. Todos los derechos reservados.
                </p>
                <div class="jm-footer-version">
                    <span>Sistema de Gestión de Inventario</span>
                    <span class="jm-version-badge">v1.0</span>
                </div>
            </div>
        </footer>
    </div>

    <script>
        // Actualizar hora y fecha en tiempo real
        function updateDateTime() {
            const now = new Date();
            
            // Formatear la hora (HH:MM:SS)
            const timeString = now.toLocaleTimeString('es-ES');
            
            // Formatear la fecha completa (día de la semana, día de mes de año)
            const dateString = now.toLocaleDateString('es-ES', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            
            // Capitalizar primera letra del día de la semana
            const formattedDate = dateString.charAt(0).toUpperCase() + dateString.slice(1);
            
            // Actualizar los elementos en el DOM
            document.getElementById('current-time').textContent = timeString;
            document.getElementById('current-date').textContent = formattedDate;
        }

        // Animación de contadores
        function animateCounters() {
            const counters = document.querySelectorAll('.counter');
            
            counters.forEach(counter => {
                const target = parseInt(counter.getAttribute('data-target'));
                const increment = target / 100;
                let current = 0;
                
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    counter.textContent = Math.floor(current);
                }, 20);
            });
        }

        // Inicializar cuando carga la página
        document.addEventListener('DOMContentLoaded', function() {
            updateDateTime();
            setInterval(updateDateTime, 1000);
            
            // Animar contadores después de un pequeño delay
            setTimeout(animateCounters, 500);
        });
    </script>
</body>
</html>
