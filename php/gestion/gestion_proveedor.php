<?php
session_start();
include '../conexion.php';

if (!isset($_SESSION['usuario']) || (isset($_SESSION['rol']) && $_SESSION['rol'] !== 'admin')) {
    header("Location: ../index.php");
    exit();
}

$stmt = $pdo->prepare("SELECT id, nombre, contacto, telefono FROM proveedores ORDER BY nombre");
$stmt->execute();
$proveedores = $stmt->fetchAll();

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
    <title>Gestión de Proveedores | Variedades Juanmarc</title>
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

        .jm-alert-info .jm-alert-icon {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            box-shadow: 0 10px 30px rgba(52, 152, 219, 0.4);
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

        /* ESTADÍSTICAS DE PROVEEDORES */
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

        .stat-total {
            border-left: 4px solid #3498db;
        }

        .stat-total .jm-stat-icon {
            color: #3498db;
        }

        .stat-total .jm-stat-number {
            color: #3498db;
        }

        .stat-activos {
            border-left: 4px solid #27ae60;
        }

        .stat-activos .jm-stat-icon {
            color: #27ae60;
        }

        .stat-activos .jm-stat-number {
            color: #27ae60;
        }

        .stat-contactos {
            border-left: 4px solid #f39c12;
        }

        .stat-contactos .jm-stat-icon {
            color: #f39c12;
        }

        .stat-contactos .jm-stat-number {
            color: #f39c12;
        }

        /* BOTÓN AGREGAR MODERNO */
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

        /* TARJETAS DE PROVEEDORES MODERNAS */
        .jm-proveedores-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .jm-proveedor-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid #f1f3f4;
        }

        .jm-proveedor-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 16px 48px rgba(0, 0, 0, 0.15);
        }

        .jm-proveedor-header-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 25px;
            position: relative;
            overflow: hidden;
        }

        .jm-proveedor-header-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            animation: shine 3s infinite;
        }

        .jm-proveedor-nombre {
            font-size: 20px;
            font-weight: 700;
            margin: 0;
            position: relative;
            z-index: 2;
        }

        .jm-proveedor-info {
            padding: 25px;
        }

        .jm-info-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 12px 0;
            border-bottom: 1px solid #f1f3f4;
        }

        .jm-info-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .jm-info-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 16px;
            color: white;
        }

        .jm-info-icon.contacto {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        }

        .jm-info-icon.telefono {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
        }

        .jm-info-content {
            flex: 1;
        }

        .jm-info-label {
            font-size: 12px;
            font-weight: 600;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 2px;
        }

        .jm-info-value {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
        }

        .jm-acciones-card {
            padding: 20px 25px;
            background: #f8f9fa;
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }

        .jm-btn-accion {
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: none;
            cursor: pointer;
        }

        .jm-btn-editar {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            box-shadow: 0 2px 8px rgba(52, 152, 219, 0.3);
        }

        .jm-btn-editar:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
            color: white;
            text-decoration: none;
        }

        .jm-btn-eliminar {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            box-shadow: 0 2px 8px rgba(231, 76, 60, 0.3);
        }

        .jm-btn-eliminar:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.4);
            color: white;
            text-decoration: none;
        }

        .jm-btn-whatsapp {
            background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
            color: white;
            box-shadow: 0 2px 8px rgba(37, 211, 102, 0.3);
        }

        .jm-btn-whatsapp:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 211, 102, 0.4);
            color: white;
            text-decoration: none;
        }

        /* ESTADO VACÍO */
        .jm-estado-vacio {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .jm-estado-vacio-icon {
            font-size: 80px;
            color: #bdc3c7;
            margin-bottom: 20px;
        }

        .jm-estado-vacio-titulo {
            font-size: 24px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .jm-estado-vacio-mensaje {
            font-size: 16px;
            color: #7f8c8d;
            margin-bottom: 30px;
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

        .alert-warning {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            color: #856404;
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
            
            .jm-proveedores-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .jm-estadisticas {
                grid-template-columns: 1fr;
            }

            .jm-acciones-card {
                flex-direction: column;
                gap: 8px;
            }

            .jm-btn-accion {
                justify-content: center;
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

    <?php include 'sidebar.php'; ?>

    <div class="jm-main">
        <div class="jm-navbar">
            <h2><i class="fas fa-truck mr-3"></i>Gestión de Proveedores</h2>
            
        </div>

        <?php echo $mensaje; ?>

        <?php
        // Calcular estadísticas de proveedores
        $totalProveedores = count($proveedores);
        $proveedoresActivos = $totalProveedores; // Asumimos que todos están activos
        $proveedoresConTelefono = 0;

        foreach ($proveedores as $proveedor) {
            if (!empty($proveedor['telefono'])) {
                $proveedoresConTelefono++;
            }
        }
        ?>

        <!-- Estadísticas de Proveedores -->
        <div class="jm-estadisticas">
            <div class="jm-stat-card stat-total" onclick="mostrarTotalProveedores()">
                <div class="jm-stat-icon"><i class="fas fa-building"></i></div>
                <div class="jm-stat-number"><?php echo $totalProveedores; ?></div>
                <div class="jm-stat-label">Total Proveedores</div>
            </div>
            <div class="jm-stat-card stat-activos" onclick="mostrarProveedoresActivos()">
                <div class="jm-stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="jm-stat-number"><?php echo $proveedoresActivos; ?></div>
                <div class="jm-stat-label">Proveedores Activos</div>
            </div>
            <div class="jm-stat-card stat-contactos" onclick="mostrarProveedoresConTelefono()">
                <div class="jm-stat-icon"><i class="fas fa-phone"></i></div>
                <div class="jm-stat-number"><?php echo $proveedoresConTelefono; ?></div>
                <div class="jm-stat-label">Con Teléfono</div>
            </div>
        </div>

        <div class="jm-contenedor-botones">
            <a href="../agregar/agregar_proveedor.php" class="btn-agregar">
                <i class="fas fa-plus"></i> Agregar Proveedor
            </a>
        </div>

        <?php if (empty($proveedores)): ?>
            <div class="jm-estado-vacio">
                <div class="jm-estado-vacio-icon">
                    <i class="fas fa-truck"></i>
                </div>
                <h3 class="jm-estado-vacio-titulo">No hay proveedores registrados</h3>
                <p class="jm-estado-vacio-mensaje">
                    Comienza agregando tu primer proveedor para gestionar mejor tu inventario y compras.
                </p>
                <a href="../agregar/agregar_proveedor.php" class="btn-agregar">
                    <i class="fas fa-plus"></i> Agregar Primer Proveedor
                </a>
            </div>
        <?php else: ?>
            <div class="jm-proveedores-grid">
                <?php foreach ($proveedores as $proveedor): ?>
                    <div class="jm-proveedor-card">
                        <div class="jm-proveedor-header-card">
                            <h3 class="jm-proveedor-nombre">
                                <i class="fas fa-building mr-2"></i>
                                <?php echo htmlspecialchars($proveedor['nombre']); ?>
                            </h3>
                        </div>
                        <div class="jm-proveedor-info">
                            <div class="jm-info-item">
                                <div class="jm-info-icon contacto">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="jm-info-content">
                                    <div class="jm-info-label">Contacto</div>
                                    <div class="jm-info-value"><?php echo htmlspecialchars($proveedor['contacto']); ?></div>
                                </div>
                            </div>
                            <div class="jm-info-item">
                                <div class="jm-info-icon telefono">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="jm-info-content">
                                    <div class="jm-info-label">Teléfono</div>
                                    <div class="jm-info-value">
                                        <?php echo !empty($proveedor['telefono']) ? htmlspecialchars($proveedor['telefono']) : 'No disponible'; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="jm-acciones-card">
                            <?php if (!empty($proveedor['telefono'])): ?>
                                <a href="javascript:void(0)" 
                                   class="jm-btn-accion jm-btn-whatsapp" 
                                   onclick="contactarWhatsApp('<?php echo htmlspecialchars($proveedor['telefono']); ?>', '<?php echo htmlspecialchars($proveedor['nombre']); ?>')">
                                    <i class="fab fa-whatsapp"></i> WhatsApp
                                </a>
                            <?php endif; ?>
                            <a href="editar_proveedor.php?id=<?php echo htmlspecialchars($proveedor['id']); ?>" class="jm-btn-accion jm-btn-editar">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <a href="eliminar_proveedor.php?id=<?php echo htmlspecialchars($proveedor['id']); ?>" 
                               class="jm-btn-accion jm-btn-eliminar" 
                               onclick="return confirm('¿Estás seguro de que deseas eliminar este proveedor?')">
                                <i class="fas fa-trash-alt"></i> Eliminar
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <a href="../index.php" class="btn-agregar" style="margin-top: 30px; background: linear-gradient(135deg, #6c757d 0%, #495057 100%); box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);">
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
        const totalProveedores = <?php echo $totalProveedores; ?>;
        const proveedoresActivos = <?php echo $proveedoresActivos; ?>;
        const proveedoresConTelefono = <?php echo $proveedoresConTelefono; ?>;

        // Función para mostrar alertas modales bonitas
        function mostrarAlerta(tipo, titulo, subtitulo, mensaje, detalles = null) {
            const overlay = document.getElementById('alertOverlay');
            const container = document.getElementById('alertContainer');
            
            let iconoClase = '';
            let tipoClase = '';
            
            switch(tipo) {
                case 'info':
                    iconoClase = 'fas fa-info-circle';
                    tipoClase = 'jm-alert-info';
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

        // Funciones específicas para estadísticas de proveedores
        function mostrarTotalProveedores() {
            if (totalProveedores > 0) {
                mostrarAlerta(
                    'info',
                    '🏢 TOTAL DE PROVEEDORES',
                    `Tienes ${totalProveedores} proveedor(es) registrado(s)`,
                    'Este es el resumen completo de todos los proveedores en tu sistema.',
                    [
                        { label: 'Total de proveedores', value: `${totalProveedores} proveedor(es)` },
                        { label: 'Proveedores activos', value: `${proveedoresActivos} proveedor(es)` },
                        { label: 'Con información de contacto', value: `${proveedoresConTelefono} proveedor(es)` }
                    ]
                );
            } else {
                mostrarToast('info', 'Sin proveedores', 'Aún no tienes proveedores registrados. ¡Agrega el primero!');
            }
        }

        function mostrarProveedoresActivos() {
            mostrarAlerta(
                'success',
                '✅ PROVEEDORES ACTIVOS',
                `Tienes ${proveedoresActivos} proveedor(es) activo(s)`,
                'Todos tus proveedores están disponibles para realizar pedidos y gestionar inventario.',
                [
                    { label: 'Proveedores activos', value: `${proveedoresActivos} proveedor(es)` },
                    { label: 'Total registrados', value: `${totalProveedores} proveedor(es)` },
                    { label: 'Porcentaje activo', value: `${totalProveedores > 0 ? Math.round((proveedoresActivos / totalProveedores) * 100) : 0}%` }
                ]
            );
        }

        function mostrarProveedoresConTelefono() {
            if (proveedoresConTelefono > 0) {
                mostrarAlerta(
                    'info',
                    '📞 PROVEEDORES CON TELÉFONO',
                    `${proveedoresConTelefono} proveedor(es) tienen información de contacto`,
                    'Estos proveedores pueden ser contactados directamente vía WhatsApp para pedidos urgentes.',
                    [
                        { label: 'Con teléfono', value: `${proveedoresConTelefono} proveedor(es)` },
                        { label: 'Sin teléfono', value: `${totalProveedores - proveedoresConTelefono} proveedor(es)` },
                        { label: 'Porcentaje con contacto', value: `${totalProveedores > 0 ? Math.round((proveedoresConTelefono / totalProveedores) * 100) : 0}%` }
                    ]
                );
            } else {
                mostrarToast('info', 'Sin contactos', 'Ningún proveedor tiene información de teléfono registrada.');
            }
        }

        // Función para contactar por WhatsApp
        function contactarWhatsApp(telefono, nombreProveedor) {
            // Limpiar el teléfono (quitar espacios y caracteres especiales)
            const telefonoLimpio = telefono.replace(/\s/g, '').replace(/\+57/, '');
            
            // Crear el mensaje
            const mensaje = `🏪 *VARIEDADES JUANMARC*\n\nHola, soy de Variedades Juanmarc.\n\nMe gustaría consultar sobre productos disponibles y realizar un pedido.\n\n¡Gracias por su atención!`;
            
            // Crear la URL de WhatsApp
            const url = `https://wa.me/57${telefonoLimpio}?text=${encodeURIComponent(mensaje)}`;
            
            // Abrir WhatsApp en una nueva ventana
            window.open(url, '_blank');
            
            // Mostrar toast de confirmación
            mostrarToast('success', 'WhatsApp abierto', `Contactando a ${nombreProveedor} vía WhatsApp`);
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

            // Animación de carga para las tarjetas de proveedores
            const cards = document.querySelectorAll('.jm-proveedor-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, (index * 100) + 800);
            });

            // Mostrar mensaje de bienvenida
            setTimeout(() => {
                if (totalProveedores === 0) {
                    mostrarToast('info', 'Sistema listo', 'Bienvenido al sistema de proveedores. ¡Comienza agregando tu primer proveedor!');
                } else if (totalProveedores > 0) {
                    mostrarToast('success', '¡Excelente!', `Tienes ${totalProveedores} proveedor(es) registrado(s). ¡Tu red de proveedores está creciendo!`);
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
