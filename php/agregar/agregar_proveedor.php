<?php
session_start();
include '../conexion.php';

if (!isset($_SESSION['usuario']) || (isset($_SESSION['rol']) && $_SESSION['rol'] !== 'admin')) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $contacto = $_POST['contacto'];
    $telefono = $_POST['telefono'];

    if (empty($nombre) || empty($contacto) || empty($telefono)) {
        $error_message = "Por favor, completa todos los campos.";
    } else {
        try {
            $stmt_insert = $pdo->prepare("INSERT INTO proveedores (nombre, contacto, telefono) VALUES (:nombre, :contacto, :telefono)");
            $stmt_insert->bindParam(':nombre', $nombre);
            $stmt_insert->bindParam(':contacto', $contacto);
            $stmt_insert->bindParam(':telefono', $telefono);
            $stmt_insert->execute();

            $mensaje = "Proveedor agregado exitosamente.";
            header("Location: ../gestion/gestion_proveedor.php?mensaje=" . urlencode($mensaje));
            exit();

        } catch (PDOException $e) {
            $error_message = "Error al agregar el proveedor: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Proveedor | Variedades Juanmarc</title>
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
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 35px;
            color: white;
            box-shadow: 0 10px 30px rgba(40, 167, 69, 0.4);
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

        .jm-form-input {
            width: 100%;
            padding: 15px 20px 15px 50px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .jm-form-input:focus {
            outline: none;
            border-color: #28a745;
            background: white;
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
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

        .jm-form-input:focus + .jm-form-icon {
            color: #28a745;
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
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        .jm-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
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
                <li><a href="../gestion/gestion_usuario.php" class="jm-link"><i class="fas fa-cog mr-2"></i> Gestión usuario</a></li>
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
                <li><a href="../gestion/gestion_producto.php" class="jm-link"><i class="fas fa-box-open mr-2"></i> Gestión producto</a></li>
            <?php endif; ?>
            <li><a href="../gestion/gestion_lote.php" class="jm-link"><i class="fas fa-cubes mr-2"></i> Gestión lote</a></li>

            <li class="jm-menu-title">
                <img src="https://img.icons8.com/ios-filled/20/ffffff/supplier.png" alt="icono compras">
                Compras
            </li>
            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
                <li><a href="../gestion/gestion_proveedor.php" class="jm-link active"><i class="fas fa-truck mr-2"></i> Gestión proveedor</a></li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="jm-main">
        <div class="jm-navbar">
            <h2><i class="fas fa-plus mr-3"></i>Agregar Proveedor</h2>
        </div>

        <div class="jm-formulario-container">
            <div class="jm-formulario">
                <div class="jm-formulario-header">
                    <div class="jm-formulario-icon">
                        <i class="fas fa-plus"></i>
                    </div>
                    <h2>Nuevo Proveedor</h2>
                    <p class="jm-formulario-subtitle">Agrega un nuevo proveedor a tu red de contactos</p>
                </div>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle alert-icon"></i>
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST">
                    <div class="jm-form-group">
                        <label for="nombre" class="jm-form-label">
                            <i class="fas fa-building mr-2"></i>Nombre del Proveedor
                        </label>
                        <div class="jm-form-input-container">
                            <input type="text" 
                                   class="jm-form-input" 
                                   id="nombre" 
                                   name="nombre" 
                                   placeholder="Ingresa el nombre de la empresa"
                                   required>
                            <i class="fas fa-building jm-form-icon"></i>
                        </div>
                    </div>

                    <div class="jm-form-group">
                        <label for="contacto" class="jm-form-label">
                            <i class="fas fa-user mr-2"></i>Persona de Contacto
                        </label>
                        <div class="jm-form-input-container">
                            <input type="text" 
                                   class="jm-form-input" 
                                   id="contacto" 
                                   name="contacto" 
                                   placeholder="Nombre del representante"
                                   required>
                            <i class="fas fa-user jm-form-icon"></i>
                        </div>
                    </div>

                    <div class="jm-form-group">
                        <label for="telefono" class="jm-form-label">
                            <i class="fas fa-phone mr-2"></i>Teléfono de Contacto
                        </label>
                        <div class="jm-form-input-container">
                            <input type="text" 
                                   class="jm-form-input" 
                                   id="telefono" 
                                   name="telefono" 
                                   placeholder="Número de teléfono o WhatsApp"
                                   required>
                            <i class="fas fa-phone jm-form-icon"></i>
                        </div>
                    </div>

                    <div class="jm-form-actions">
                        <button type="submit" class="jm-btn jm-btn-primary">
                            <i class="fas fa-save"></i> Guardar Proveedor
                        </button>
                        <a href="../gestion/gestion_proveedor.php" class="jm-btn jm-btn-secondary">
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
            const inputs = document.querySelectorAll('.jm-form-input');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'scale(1.02)';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'scale(1)';
                });
            });
        });

        // Validación en tiempo real
        document.getElementById('telefono').addEventListener('input', function() {
            const telefono = this.value;
            const icon = this.nextElementSibling;
            
            if (telefono.length >= 10) {
                icon.className = 'fas fa-check jm-form-icon';
                icon.style.color = '#27ae60';
            } else {
                icon.className = 'fas fa-phone jm-form-icon';
                icon.style.color = '#7f8c8d';
            }
        });

        // Validación del nombre de empresa
        document.getElementById('nombre').addEventListener('input', function() {
            const nombre = this.value;
            const icon = this.nextElementSibling;
            
            if (nombre.length >= 3) {
                icon.className = 'fas fa-check jm-form-icon';
                icon.style.color = '#27ae60';
            } else {
                icon.className = 'fas fa-building jm-form-icon';
                icon.style.color = '#7f8c8d';
            }
        });

        // Validación del contacto
        document.getElementById('contacto').addEventListener('input', function() {
            const contacto = this.value;
            const icon = this.nextElementSibling;
            
            if (contacto.length >= 2) {
                icon.className = 'fas fa-check jm-form-icon';
                icon.style.color = '#27ae60';
            } else {
                icon.className = 'fas fa-user jm-form-icon';
                icon.style.color = '#7f8c8d';
            }
        });
    </script>
</body>
</html>
