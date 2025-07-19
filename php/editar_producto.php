<?php
session_start();
include 'conexion.php';

// Verificar si el usuario ha iniciado sesión y es administrador
if (!isset($_SESSION['usuario']) || (isset($_SESSION['rol']) && $_SESSION['rol'] !== 'admin')) {
    header("Location: index.php"); // O una página de error de acceso denegado
    exit();
}

// Verificar si se recibió el ID del producto
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: gestion_producto.php?error=id_invalido");
    exit();
}

$producto_id = $_GET['id'];

// Obtener la información del producto a editar
$stmt_producto = $pdo->prepare("SELECT nombre, precio, stock FROM productos WHERE id = :id");
$stmt_producto->bindParam(':id', $producto_id);
$stmt_producto->execute();
$producto = $stmt_producto->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    header("Location: gestion_producto.php?error=producto_no_encontrado");
    exit();
}

// Procesar el formulario cuando se envíe
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];

    // Validaciones básicas
    if (empty($nombre) || empty($precio) || empty($stock) || !is_numeric($precio) || !is_numeric($stock) || $precio <= 0 || $stock < 0) {
        $error_message = "Por favor, completa todos los campos correctamente.";
    } else {
        try {
            $stmt_update = $pdo->prepare("UPDATE productos SET nombre = :nombre, precio = :precio, stock = :stock WHERE id = :id");
            $stmt_update->bindParam(':nombre', $nombre);
            $stmt_update->bindParam(':precio', $precio);
            $stmt_update->bindParam(':stock', $stock, PDO::PARAM_INT);
            $stmt_update->bindParam(':id', $producto_id);
            $stmt_update->execute();

            $mensaje = "Producto actualizado exitosamente.";
            header("Location: gestion_producto.php?mensaje=" . urlencode($mensaje));
            exit();

        } catch (PDOException $e) {
            $error_message = "Error al actualizar el producto: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Producto | Variedades Juanmarc</title>
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

        /* PANEL DE INFORMACIÓN DEL PRODUCTO */
        .jm-panel-producto {
            background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);
            color: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(155, 89, 182, 0.3);
            position: relative;
            overflow: hidden;
        }

        .jm-panel-producto::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            animation: shine 4s infinite;
        }

        .jm-producto-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .jm-producto-icon {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .jm-producto-titulo {
            font-size: 20px;
            font-weight: 700;
            margin: 0;
        }

        .jm-producto-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .jm-info-item {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 15px;
            text-align: center;
        }

        .jm-info-label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
            opacity: 0.8;
        }

        .jm-info-value {
            font-size: 18px;
            font-weight: 700;
        }

        /* FORMULARIO MODERNO */
        .jm-formulario {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        .jm-formulario::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #FF8C00, #FFA500, #FFD700);
        }

        .jm-formulario-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .jm-formulario-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 35px;
            color: white;
            box-shadow: 0 10px 30px rgba(243, 156, 18, 0.4);
            position: relative;
            overflow: hidden;
        }

        .jm-formulario-icon::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            animation: shine 3s infinite;
        }

        .jm-formulario-titulo {
            font-size: 28px;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
        }

        .jm-formulario-subtitle {
            color: #7f8c8d;
            margin-top: 8px;
            font-size: 16px;
        }

        /* CAMPOS DEL FORMULARIO */
        .jm-form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .jm-form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .jm-form-control {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8f9fa;
            font-weight: 500;
        }

        .jm-form-control:focus {
            outline: none;
            border-color: #FFA500;
            background: white;
            box-shadow: 0 0 0 3px rgba(255, 165, 0, 0.1);
            transform: translateY(-2px);
        }

        .jm-form-control:hover {
            border-color: #dee2e6;
            background: white;
        }

        /* ICONOS EN CAMPOS */
        .jm-input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .jm-form-control.with-icon {
            padding-left: 45px;
        }

        .jm-form-group:focus-within .jm-input-icon {
            color: #FFA500;
        }

        /* BOTONES MODERNOS */
        .jm-btn {
            padding: 15px 30px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            position: relative;
            overflow: hidden;
        }

        .jm-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .jm-btn:hover::before {
            left: 100%;
        }

        .jm-btn-primary {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
        }

        .jm-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
            color: white;
            text-decoration: none;
        }

        .jm-btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
            margin-top: 15px;
        }

        .jm-btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
            color: white;
            text-decoration: none;
        }

        /* ALERTAS MODERNAS */
        .jm-alert {
            padding: 20px 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            border: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideInDown 0.5s ease;
        }

        .jm-alert-danger {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
        }

        .jm-alert-success {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
        }

        .jm-alert i {
            font-size: 20px;
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

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
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

        .jm-panel-producto {
            animation: slideInUp 0.6s ease;
        }

        .jm-formulario {
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
            
            .jm-formulario {
                padding: 25px;
            }

            .jm-navbar h2 {
                font-size: 18px;
            }

            .jm-btn {
                width: 100%;
                justify-content: center;
                margin-bottom: 10px;
            }

            .jm-producto-info {
                grid-template-columns: 1fr;
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
            <h2><i class="fas fa-edit mr-3"></i>Editar Producto</h2>
            <div class="jm-cart">
                <img src="https://img.icons8.com/ios-filled/24/ffffff/shopping-cart.png"/>
                <span class="jm-cart-badge">0</span>
            </div>
        </div>

        <!-- Panel de información del producto actual -->
        <div class="jm-panel-producto">
            <div class="jm-producto-header">
                <div class="jm-producto-icon">
                    <i class="fas fa-box-open"></i>
                </div>
                <h3 class="jm-producto-titulo">Producto #<?php echo htmlspecialchars($producto_id); ?></h3>
            </div>
            <div class="jm-producto-info">
                <div class="jm-info-item">
                    <div class="jm-info-label">Nombre Actual</div>
                    <div class="jm-info-value"><?php echo htmlspecialchars($producto['nombre']); ?></div>
                </div>
                <div class="jm-info-item">
                    <div class="jm-info-label">Precio Actual</div>
                    <div class="jm-info-value">$<?php echo htmlspecialchars(number_format($producto['precio'], 2)); ?></div>
                </div>
                <div class="jm-info-item">
                    <div class="jm-info-label">Stock Actual</div>
                    <div class="jm-info-value"><?php echo htmlspecialchars($producto['stock']); ?> unidades</div>
                </div>
            </div>
        </div>

        <!-- Formulario -->
        <div class="jm-formulario">
            <div class="jm-formulario-header">
                <div class="jm-formulario-icon">
                    <i class="fas fa-edit"></i>
                </div>
                <h2 class="jm-formulario-titulo">Editar Información</h2>
                <p class="jm-formulario-subtitle">Modifica los datos del producto según sea necesario</p>
            </div>

            <?php if (isset($error_message)): ?>
                <div class="jm-alert jm-alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" id="productoForm">
                <div class="jm-form-group">
                    <label for="nombre" class="jm-form-label">
                        <i class="fas fa-tag mr-2"></i>Nombre del Producto
                    </label>
                    <div style="position: relative;">
                        <i class="jm-input-icon fas fa-box"></i>
                        <input type="text" class="jm-form-control with-icon" id="nombre" name="nombre" 
                               value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>
                    </div>
                </div>

                <div class="jm-form-group">
                    <label for="precio" class="jm-form-label">
                        <i class="fas fa-dollar-sign mr-2"></i>Precio
                    </label>
                    <div style="position: relative;">
                        <i class="jm-input-icon fas fa-money-bill-wave"></i>
                        <input type="number" class="jm-form-control with-icon" id="precio" name="precio" 
                               min="0.01" step="0.01" value="<?php echo htmlspecialchars($producto['precio']); ?>" required>
                    </div>
                </div>

                <div class="jm-form-group">
                    <label for="stock" class="jm-form-label">
                        <i class="fas fa-cubes mr-2"></i>Stock
                    </label>
                    <div style="position: relative;">
                        <i class="jm-input-icon fas fa-warehouse"></i>
                        <input type="number" class="jm-form-control with-icon" id="stock" name="stock" 
                               min="0" value="<?php echo htmlspecialchars($producto['stock']); ?>" required>
                    </div>
                </div>

                <div style="text-align: center; margin-top: 40px;">
                    <button type="submit" class="jm-btn jm-btn-primary">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                    <br>
                    <a href="gestion_producto.php" class="jm-btn jm-btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver a Gestión de Productos
                    </a>
                </div>
            </form>
        </div>

        <footer class="jm-footer">
            <i class="fas fa-heart" style="color: #e74c3c;"></i> © 2025 Variedades Juanmarc. Todos los derechos reservados.
        </footer>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
        // Animaciones y validaciones
        document.addEventListener('DOMContentLoaded', function() {
            // Animación de entrada para los campos del formulario
            const formGroups = document.querySelectorAll('.jm-form-group');
            formGroups.forEach((group, index) => {
                group.style.opacity = '0';
                group.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    group.style.transition = 'all 0.6s ease';
                    group.style.opacity = '1';
                    group.style.transform = 'translateY(0)';
                }, index * 150 + 500);
            });

            // Validación en tiempo real
            const nombreInput = document.getElementById('nombre');
            const precioInput = document.getElementById('precio');
            const stockInput = document.getElementById('stock');

            // Validación del nombre
            nombreInput.addEventListener('input', function() {
                if (this.value.length < 3) {
                    this.style.borderColor = '#e74c3c';
                } else {
                    this.style.borderColor = '#27ae60';
                }
            });

            // Validación del precio
            precioInput.addEventListener('input', function() {
                const precio = parseFloat(this.value);
                if (precio <= 0 || isNaN(precio)) {
                    this.style.borderColor = '#e74c3c';
                } else {
                    this.style.borderColor = '#27ae60';
                }
            });

            // Validación del stock
            stockInput.addEventListener('input', function() {
                const stock = parseInt(this.value);
                if (stock < 0 || isNaN(stock)) {
                    this.style.borderColor = '#e74c3c';
                } else {
                    this.style.borderColor = '#27ae60';
                }
            });

            // Formateo automático del precio
            precioInput.addEventListener('blur', function() {
                if (this.value && !isNaN(this.value)) {
                    this.value = parseFloat(this.value).toFixed(2);
                }
            });

            // Efecto de focus mejorado
            const inputs = document.querySelectorAll('.jm-form-control');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'scale(1.02)';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'scale(1)';
                });
            });

            // Validación del formulario antes del envío
            document.getElementById('productoForm').addEventListener('submit', function(e) {
                const nombre = nombreInput.value.trim();
                const precio = parseFloat(precioInput.value);
                const stock = parseInt(stockInput.value);

                let errores = [];

                if (nombre.length < 3) {
                    errores.push('El nombre del producto debe tener al menos 3 caracteres');
                }

                if (precio <= 0 || isNaN(precio)) {
                    errores.push('El precio debe ser mayor a $0.01');
                }

                if (stock < 0 || isNaN(stock)) {
                    errores.push('El stock no puede ser negativo');
                }

                if (errores.length > 0) {
                    e.preventDefault();
                    
                    // Mostrar errores
                    let errorHtml = '<div class="jm-alert jm-alert-danger"><i class="fas fa-exclamation-triangle"></i><ul style="margin: 0; padding-left: 20px;">';
                    errores.forEach(error => {
                        errorHtml += '<li>' + error + '</li>';
                    });
                    errorHtml += '</ul></div>';
                    
                    // Insertar antes del formulario
                    const form = document.getElementById('productoForm');
                    const existingAlert = form.previousElementSibling;
                    if (existingAlert && existingAlert.classList.contains('jm-alert')) {
                        existingAlert.remove();
                    }
                    form.insertAdjacentHTML('beforebegin', errorHtml);
                    
                    // Scroll al error
                    document.querySelector('.jm-alert').scrollIntoView({ behavior: 'smooth' });
                }
            });

            // Detectar cambios en los campos
            const originalValues = {
                nombre: nombreInput.value,
                precio: precioInput.value,
                stock: stockInput.value
            };

            function detectarCambios() {
                const hasChanges = 
                    nombreInput.value !== originalValues.nombre ||
                    precioInput.value !== originalValues.precio ||
                    stockInput.value !== originalValues.stock;

                const submitBtn = document.querySelector('button[type="submit"]');
                if (hasChanges) {
                    submitBtn.style.background = 'linear-gradient(135deg, #e74c3c 0%, #c0392b 100%)';
                    submitBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Cambios';
                } else {
                    submitBtn.style.background = 'linear-gradient(135deg, #007bff 0%, #0056b3 100%)';
                    submitBtn.innerHTML = '<i class="fas fa-check"></i> Sin Cambios';
                }
            }

            nombreInput.addEventListener('input', detectarCambios);
            precioInput.addEventListener('input', detectarCambios);
            stockInput.addEventListener('input', detectarCambios);
        });

        // Efecto de carga en el botón de envío
        document.getElementById('productoForm').addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Actualizando...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>
