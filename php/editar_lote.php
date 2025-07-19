<?php
session_start();
include 'conexion.php';

// Verificar si el usuario ha iniciado sesión y es administrador
if (!isset($_SESSION['usuario']) || (isset($_SESSION['rol']) && $_SESSION['rol'] !== 'admin')) {
    header("Location: index.php"); // O una página de error de acceso denegado
    exit();
}

// Verificar si se recibió el ID del lote
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: gestion_lote.php?error=id_invalido");
    exit();
}

$lote_id = $_GET['id'];

// Obtener la información del lote a editar
$stmt_lote = $pdo->prepare("SELECT producto_id, cantidad, fecha_vencimiento FROM lotes WHERE id = :id");
$stmt_lote->bindParam(':id', $lote_id);
$stmt_lote->execute();
$lote = $stmt_lote->fetch(PDO::FETCH_ASSOC);

if (!$lote) {
    header("Location: gestion_lote.php?error=lote_no_encontrado");
    exit();
}

$stmt_productos = $pdo->prepare("SELECT id, nombre FROM productos ORDER BY nombre");
$stmt_productos->execute();
$productos = $stmt_productos->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $producto_id = $_POST['producto_id'];
    $cantidad = $_POST['cantidad'];
    $fecha_vencimiento = $_POST['fecha_vencimiento'];

    if (empty($producto_id) || empty($cantidad) || empty($fecha_vencimiento) || !is_numeric($cantidad) || $cantidad <= 0) {
        $error_message = "Por favor, completa todos los campos correctamente.";
    } else {
        try {
            $stmt_update = $pdo->prepare("UPDATE lotes SET producto_id = :producto_id, cantidad = :cantidad, fecha_vencimiento = :fecha_vencimiento WHERE id = :id");
            $stmt_update->bindParam(':producto_id', $producto_id);
            $stmt_update->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);
            $stmt_update->bindParam(':fecha_vencimiento', $fecha_vencimiento);
            $stmt_update->bindParam(':id', $lote_id);
            $stmt_update->execute();

            $mensaje = "Lote actualizado exitosamente.";
            header("Location: gestion_lote.php?mensaje=" . urlencode($mensaje));
            exit();

        } catch (PDOException $e) {
            $error_message = "Error al actualizar el lote: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Lote | Variedades Juanmarc</title>
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

        /* FORMULARIO MODERNO */
        .jm-formulario-container {
            max-width: 600px;
            margin: 0 auto;
        }

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
            margin-bottom: 30px;
        }

        .jm-formulario-icon {
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

        .jm-formulario h2 {
            color: #2c3e50;
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }

        .jm-formulario-subtitle {
            color: #7f8c8d;
            margin-top: 8px;
            font-size: 16px;
        }

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
            letter-spacing: 1px;
        }

        .jm-form-input-container {
            position: relative;
        }

        .jm-form-input, .jm-form-select {
            width: 100%;
            padding: 15px 20px 15px 50px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .jm-form-input:focus, .jm-form-select:focus {
            outline: none;
            border-color: #3498db;
            background: white;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
            transform: translateY(-2px);
        }

        .jm-form-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .jm-form-input:focus + .jm-form-icon,
        .jm-form-select:focus + .jm-form-icon {
            color: #3498db;
        }

        .jm-form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

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
            justify-content: center;
            min-width: 150px;
        }

        .jm-btn-primary {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }

        .jm-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
            color: white;
            text-decoration: none;
        }

        .jm-btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
        }

        .jm-btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
            color: white;
            text-decoration: none;
        }

        /* ESTADO DEL LOTE */
        .jm-lote-estado {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 4px solid #3498db;
        }

        .jm-lote-estado-titulo {
            font-size: 16px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .jm-lote-estado-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }

        .jm-lote-info-item {
            text-align: center;
        }

        .jm-lote-info-label {
            font-size: 12px;
            font-weight: 600;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .jm-lote-info-value {
            font-size: 18px;
            font-weight: 700;
            color: #2c3e50;
        }

        /* ALERTAS MEJORADAS */
        .alert {
            border-radius: 15px;
            padding: 15px 20px;
            margin-bottom: 25px;
            border: none;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-danger {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
        }

        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
        }

        .alert-icon {
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

        @keyframes shine {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }

        .jm-formulario {
            animation: slideInUp 0.6s ease;
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

            .jm-form-actions {
                flex-direction: column;
            }

            .jm-btn {
                width: 100%;
            }

            .jm-lote-estado-info {
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
            <h2><i class="fas fa-edit mr-3"></i>Editar Lote</h2>
        </div>

        <div class="jm-formulario-container">
            <!-- Estado actual del lote -->
            <div class="jm-lote-estado">
                <div class="jm-lote-estado-titulo">
                    <i class="fas fa-info-circle"></i>
                    Información Actual del Lote
                </div>
                <div class="jm-lote-estado-info">
                    <div class="jm-lote-info-item">
                        <div class="jm-lote-info-label">ID del Lote</div>
                        <div class="jm-lote-info-value">#<?php echo htmlspecialchars($lote_id); ?></div>
                    </div>
                    <div class="jm-lote-info-item">
                        <div class="jm-lote-info-label">Cantidad Actual</div>
                        <div class="jm-lote-info-value"><?php echo htmlspecialchars($lote['cantidad']); ?> unidades</div>
                    </div>
                    <div class="jm-lote-info-item">
                        <div class="jm-lote-info-label">Fecha de Vencimiento</div>
                        <div class="jm-lote-info-value"><?php echo htmlspecialchars(date('d/m/Y', strtotime($lote['fecha_vencimiento']))); ?></div>
                    </div>
                </div>
            </div>

            <div class="jm-formulario">
                <div class="jm-formulario-header">
                    <div class="jm-formulario-icon">
                        <i class="fas fa-edit"></i>
                    </div>
                    <h2>Editar Lote</h2>
                    <p class="jm-formulario-subtitle">Modifica la información del lote #<?php echo htmlspecialchars($lote_id); ?></p>
                </div>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle alert-icon"></i>
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST">
                    <div class="jm-form-group">
                        <label for="producto_id" class="jm-form-label">
                            <i class="fas fa-box mr-2"></i>Producto
                        </label>
                        <div class="jm-form-input-container">
                            <select class="jm-form-select" id="producto_id" name="producto_id" required>
                                <option value="">Seleccionar Producto</option>
                                <?php foreach ($productos as $producto): ?>
                                    <option value="<?php echo htmlspecialchars($producto['id']); ?>" <?php if ($producto['id'] == $lote['producto_id']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($producto['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fas fa-box jm-form-icon"></i>
                        </div>
                    </div>

                    <div class="jm-form-group">
                        <label for="cantidad" class="jm-form-label">
                            <i class="fas fa-sort-numeric-up mr-2"></i>Cantidad
                        </label>
                        <div class="jm-form-input-container">
                            <input type="number" 
                                   class="jm-form-input" 
                                   id="cantidad" 
                                   name="cantidad" 
                                   min="1" 
                                   value="<?php echo htmlspecialchars($lote['cantidad']); ?>" 
                                   placeholder="Cantidad de unidades"
                                   required>
                            <i class="fas fa-sort-numeric-up jm-form-icon"></i>
                        </div>
                    </div>

                    <div class="jm-form-group">
                        <label for="fecha_vencimiento" class="jm-form-label">
                            <i class="fas fa-calendar-alt mr-2"></i>Fecha de Vencimiento
                        </label>
                        <div class="jm-form-input-container">
                            <input type="date" 
                                   class="jm-form-input" 
                                   id="fecha_vencimiento" 
                                   name="fecha_vencimiento" 
                                   value="<?php echo htmlspecialchars($lote['fecha_vencimiento']); ?>" 
                                   required>
                            <i class="fas fa-calendar-alt jm-form-icon"></i>
                        </div>
                    </div>

                    <div class="jm-form-actions">
                        <button type="submit" class="jm-btn jm-btn-primary">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                        <a href="gestion_lote.php" class="jm-btn jm-btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <footer class="jm-footer">
            <i class="fas fa-heart" style="color: #e74c3c;"></i> © 2025 Variedades Juanmarc. Todos los derechos reservados.
        </footer>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
        // Animación de entrada para los campos del formulario
        document.addEventListener('DOMContentLoaded', function() {
            const formGroups = document.querySelectorAll('.jm-form-group');
            formGroups.forEach((group, index) => {
                group.style.opacity = '0';
                group.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    group.style.transition = 'all 0.5s ease';
                    group.style.opacity = '1';
                    group.style.transform = 'translateY(0)';
                }, index * 150 + 300);
            });

            // Efecto de focus mejorado
            const inputs = document.querySelectorAll('.jm-form-input, .jm-form-select');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'scale(1.02)';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'scale(1)';
                });
            });

            // Animación para el estado del lote
            const estadoLote = document.querySelector('.jm-lote-estado');
            estadoLote.style.opacity = '0';
            estadoLote.style.transform = 'translateY(-20px)';
            setTimeout(() => {
                estadoLote.style.transition = 'all 0.6s ease';
                estadoLote.style.opacity = '1';
                estadoLote.style.transform = 'translateY(0)';
            }, 100);
        });

        // Validación en tiempo real para cantidad
        document.getElementById('cantidad').addEventListener('input', function() {
            const cantidad = parseInt(this.value);
            const icon = this.nextElementSibling;
            
            if (cantidad > 0) {
                icon.className = 'fas fa-check jm-form-icon';
                icon.style.color = '#27ae60';
            } else {
                icon.className = 'fas fa-sort-numeric-up jm-form-icon';
                icon.style.color = '#7f8c8d';
            }
        });

        // Validación para fecha de vencimiento
        document.getElementById('fecha_vencimiento').addEventListener('change', function() {
            const fechaSeleccionada = new Date(this.value);
            const hoy = new Date();
            const icon = this.nextElementSibling;
            
            if (fechaSeleccionada > hoy) {
                icon.className = 'fas fa-check jm-form-icon';
                icon.style.color = '#27ae60';
            } else if (fechaSeleccionada.toDateString() === hoy.toDateString()) {
                icon.className = 'fas fa-exclamation-triangle jm-form-icon';
                icon.style.color = '#f39c12';
            } else {
                icon.className = 'fas fa-times jm-form-icon';
                icon.style.color = '#e74c3c';
            }
        });

        // Validación para producto seleccionado
        document.getElementById('producto_id').addEventListener('change', function() {
            const icon = this.nextElementSibling;
            
            if (this.value) {
                icon.className = 'fas fa-check jm-form-icon';
                icon.style.color = '#27ae60';
            } else {
                icon.className = 'fas fa-box jm-form-icon';
                icon.style.color = '#7f8c8d';
            }
        });

        // Ejecutar validaciones iniciales
        window.addEventListener('load', function() {
            document.getElementById('cantidad').dispatchEvent(new Event('input'));
            document.getElementById('fecha_vencimiento').dispatchEvent(new Event('change'));
            document.getElementById('producto_id').dispatchEvent(new Event('change'));
        });
    </script>
</body>
</html>
