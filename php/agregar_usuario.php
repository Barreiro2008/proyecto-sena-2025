<?php
session_start();
include 'conexion.php';
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
        header("Location: index.php");
        exit();
}

$mensaje = '';
$usuario = '';
$email = '';
$rol = 'empleado'; 
$clave = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
     $email = $_POST['email'] ?? '';
        $rol = $_POST['rol'] ?? 'empleado'; 
        $clave = $_POST['password'] ?? '';
        $confirmar_clave = $_POST['confirmar_clave'] ?? '';

    if (empty($usuario) || empty($email) || empty($clave) || empty($confirmar_clave)) {
            $mensaje = '<div class="alert alert-danger">Todos los campos son obligatorios.</div>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $mensaje = '<div class="alert alert-danger">El formato del email no es válido.</div>';
    } elseif ($clave !== $confirmar_clave) {
            $mensaje = '<div class="alert alert-danger">Las contraseñas no coinciden.</div>';
    } else {
            $stmt_check = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = :usuario OR email = :email");
            $stmt_check->bindParam(':usuario', $usuario);
            $stmt_check->bindParam(':email', $email);
            $stmt_check->execute();
        if ($stmt_check->fetch()) {
            $mensaje = '<div class="alert alert-danger">El usuario o el email ya existen en el sistema.</div>';
        } else {
            $clave_hash = password_hash($clave, PASSWORD_DEFAULT);

        try {
            $stmt_insert = $pdo->prepare("INSERT INTO usuarios (usuario, email, password, rol, fecha_registro) VALUES (:usuario, :email, :password, :rol, NOW())");
            $stmt_insert->bindParam(':usuario', $usuario);
            $stmt_insert->bindParam(':email', $email);
            $stmt_insert->bindParam(':password', $clave_hash);
            $stmt_insert->bindParam(':rol', $rol);
            $stmt_insert->execute();
            $mensaje = '<div class="alert alert-success">Usuario agregado correctamente.</div>';
            $usuario = '';
            $email = '';
            $clave = '';
            $confirmar_clave = '';
            } catch (PDOException $e) {
                $mensaje = '<div class="alert alert-danger">Error al agregar el usuario: ' . $e->getMessage() . '</div>';
                }
            }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Usuario | Variedades Juanmarc</title>
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

        /* CONTENEDOR PRINCIPAL */
        .jm-contenedor-principal {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 30px;
            align-items: start;
        }

        /* FORMULARIO MODERNO */
        .jm-formulario-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        .jm-formulario-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(135deg, #FF8C00 0%, #FFA500 100%);
        }

        .jm-formulario-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .jm-formulario-titulo {
            font-size: 28px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .jm-formulario-subtitulo {
            font-size: 16px;
            color: #7f8c8d;
            font-weight: 500;
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
            letter-spacing: 0.5px;
        }

        .jm-form-input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .jm-form-input:focus {
            outline: none;
            border-color: #FFA500;
            background: white;
            box-shadow: 0 0 0 3px rgba(255, 165, 0, 0.1);
            transform: translateY(-2px);
        }

        .jm-form-input:valid {
            border-color: #28a745;
        }

        .jm-form-select {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
            background: #f8f9fa;
            cursor: pointer;
        }

        .jm-form-select:focus {
            outline: none;
            border-color: #FFA500;
            background: white;
            box-shadow: 0 0 0 3px rgba(255, 165, 0, 0.1);
        }

        .jm-rol-selector {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 10px;
        }

        .jm-rol-option {
            position: relative;
        }

        .jm-rol-radio {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }

        .jm-rol-label {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px 20px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f8f9fa;
            font-weight: 600;
        }

        .jm-rol-radio:checked + .jm-rol-label {
            border-color: #FFA500;
            background: rgba(255, 165, 0, 0.1);
            color: #FF8C00;
        }

        .jm-rol-icon {
            font-size: 20px;
        }

        .jm-password-strength {
            margin-top: 8px;
            height: 4px;
            background: #e9ecef;
            border-radius: 2px;
            overflow: hidden;
        }

        .jm-password-strength-bar {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .strength-weak { background: #e74c3c; width: 25%; }
        .strength-fair { background: #f39c12; width: 50%; }
        .strength-good { background: #f1c40f; width: 75%; }
        .strength-strong { background: #27ae60; width: 100%; }

        .jm-password-requirements {
            margin-top: 10px;
            font-size: 12px;
            color: #7f8c8d;
        }

        .jm-requirement {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 4px;
        }

        .jm-requirement.met {
            color: #27ae60;
        }

        .jm-requirement.met i {
            color: #27ae60;
        }

        /* BOTONES */
        .jm-botones-container {
            display: flex;
            gap: 15px;
            margin-top: 35px;
        }

        .jm-btn {
            padding: 15px 30px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
            flex: 1;
            justify-content: center;
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

        /* PANEL DE AYUDA */
        .jm-panel-ayuda {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 30px;
        }

        .jm-panel-titulo {
            font-size: 20px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .jm-ayuda-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 12px;
            border-left: 4px solid #FFA500;
        }

        .jm-ayuda-icon {
            color: #FFA500;
            font-size: 18px;
            margin-top: 2px;
        }

        .jm-ayuda-texto {
            font-size: 14px;
            color: #2c3e50;
            font-weight: 500;
            line-height: 1.4;
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

        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
        }

        .alert-danger {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
        }

        .alert i {
            font-size: 18px;
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
        @media (max-width: 1200px) {
            .jm-contenedor-principal {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .jm-panel-ayuda {
                position: relative;
                top: 0;
            }
        }

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
            
            .jm-formulario-container {
                padding: 25px;
            }
            
            .jm-botones-container {
                flex-direction: column;
            }

            .jm-rol-selector {
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
                <li><a href="gestion_usuario.php" class="jm-link active"><i class="fas fa-cog mr-2"></i> Gestión usuario</a></li>
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
            <h2><i class="fas fa-user-plus mr-3"></i>Agregar Usuario</h2>
            <div class="jm-cart">
                <img src="https://img.icons8.com/ios-filled/24/ffffff/shopping-cart.png"/>
                <span class="jm-cart-badge">0</span>
            </div>
        </div>

        <div class="jm-contenedor-principal">
            <div class="jm-formulario-container">
                <div class="jm-formulario-header">
                    <h2 class="jm-formulario-titulo">
                        <i class="fas fa-user-plus"></i>
                        Nuevo Usuario
                    </h2>
                    <p class="jm-formulario-subtitulo">Complete todos los campos para crear un nuevo usuario en el sistema</p>
                </div>

                <?php if ($mensaje): ?>
                    <?php 
                    $alertClass = strpos($mensaje, 'alert-success') !== false ? 'alert-success' : 'alert-danger';
                    $alertIcon = strpos($mensaje, 'alert-success') !== false ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle';
                    ?>
                    <div class="alert <?php echo $alertClass; ?>">
                        <i class="<?php echo $alertIcon; ?>"></i>
                        <?php echo strip_tags($mensaje); ?>
                    </div>
                <?php endif; ?>

                <form method="post" id="userForm">
                    <div class="jm-form-group">
                        <label for="usuario" class="jm-form-label">
                            <i class="fas fa-user mr-2"></i>Nombre de Usuario
                        </label>
                        <input type="text" 
                               class="jm-form-input" 
                               id="usuario" 
                               name="usuario" 
                               value="<?php echo htmlspecialchars($usuario); ?>" 
                               placeholder="Ingrese el nombre de usuario"
                               required>
                    </div>

                    <div class="jm-form-group">
                        <label for="email" class="jm-form-label">
                            <i class="fas fa-envelope mr-2"></i>Correo Electrónico
                        </label>
                        <input type="email" 
                               class="jm-form-input" 
                               id="email" 
                               name="email" 
                               value="<?php echo htmlspecialchars($email); ?>" 
                               placeholder="usuario@ejemplo.com"
                               required>
                    </div>

                    <div class="jm-form-group">
                        <label class="jm-form-label">
                            <i class="fas fa-user-tag mr-2"></i>Rol del Usuario
                        </label>
                        <div class="jm-rol-selector">
                            <div class="jm-rol-option">
                                <input type="radio" 
                                       class="jm-rol-radio" 
                                       id="rol_empleado" 
                                       name="rol" 
                                       value="empleado" 
                                       <?php if ($rol === 'empleado') echo 'checked'; ?>>
                                <label for="rol_empleado" class="jm-rol-label">
                                    <i class="fas fa-user jm-rol-icon"></i>
                                    <div>
                                        <div style="font-weight: 700;">Empleado</div>
                                        <div style="font-size: 12px; opacity: 0.7;">Acceso básico</div>
                                    </div>
                                </label>
                            </div>
                            <div class="jm-rol-option">
                                <input type="radio" 
                                       class="jm-rol-radio" 
                                       id="rol_admin" 
                                       name="rol" 
                                       value="admin" 
                                       <?php if ($rol === 'admin') echo 'checked'; ?>>
                                <label for="rol_admin" class="jm-rol-label">
                                    <i class="fas fa-user-shield jm-rol-icon"></i>
                                    <div>
                                        <div style="font-weight: 700;">Administrador</div>
                                        <div style="font-size: 12px; opacity: 0.7;">Acceso completo</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="jm-form-group">
                        <label for="clave" class="jm-form-label">
                            <i class="fas fa-lock mr-2"></i>Contraseña
                        </label>
                        <input type="password" 
                               class="jm-form-input" 
                               id="clave" 
                               name="password" 
                               placeholder="Ingrese una contraseña segura"
                               required>
                        <div class="jm-password-strength">
                            <div class="jm-password-strength-bar" id="strengthBar"></div>
                        </div>
                        <div class="jm-password-requirements" id="requirements">
                            <div class="jm-requirement" id="req-length">
                                <i class="fas fa-times"></i> Mínimo 8 caracteres
                            </div>
                            <div class="jm-requirement" id="req-upper">
                                <i class="fas fa-times"></i> Una letra mayúscula
                            </div>
                            <div class="jm-requirement" id="req-lower">
                                <i class="fas fa-times"></i> Una letra minúscula
                            </div>
                            <div class="jm-requirement" id="req-number">
                                <i class="fas fa-times"></i> Un número
                            </div>
                        </div>
                    </div>

                    <div class="jm-form-group">
                        <label for="confirmar_clave" class="jm-form-label">
                            <i class="fas fa-lock mr-2"></i>Confirmar Contraseña
                        </label>
                        <input type="password" 
                               class="jm-form-input" 
                               id="confirmar_clave" 
                               name="confirmar_clave" 
                               placeholder="Confirme la contraseña"
                               required>
                        <div id="passwordMatch" style="margin-top: 8px; font-size: 12px;"></div>
                    </div>

                    <div class="jm-botones-container">
                        <button type="submit" class="jm-btn jm-btn-primary">
                            <i class="fas fa-user-plus"></i> Crear Usuario
                        </button>
                        <a href="gestion_usuario.php" class="jm-btn jm-btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </form>
            </div>

            <div class="jm-panel-ayuda">
                <h3 class="jm-panel-titulo">
                    <i class="fas fa-info-circle"></i>
                    Guía de Usuario
                </h3>
                
                <div class="jm-ayuda-item">
                    <i class="fas fa-user jm-ayuda-icon"></i>
                    <div class="jm-ayuda-texto">
                        <strong>Nombre de Usuario:</strong> Debe ser único en el sistema. Se recomienda usar nombres descriptivos.
                    </div>
                </div>

                <div class="jm-ayuda-item">
                    <i class="fas fa-envelope jm-ayuda-icon"></i>
                    <div class="jm-ayuda-texto">
                        <strong>Email:</strong> Debe ser una dirección válida. Se usará para notificaciones del sistema.
                    </div>
                </div>

                <div class="jm-ayuda-item">
                    <i class="fas fa-user-tag jm-ayuda-icon"></i>
                    <div class="jm-ayuda-texto">
                        <strong>Roles:</strong> Los empleados tienen acceso básico, los administradores tienen control total.
                    </div>
                </div>

                <div class="jm-ayuda-item">
                    <i class="fas fa-shield-alt jm-ayuda-icon"></i>
                    <div class="jm-ayuda-texto">
                        <strong>Contraseña:</strong> Debe cumplir con los requisitos de seguridad mostrados en tiempo real.
                    </div>
                </div>

                <div class="jm-ayuda-item">
                    <i class="fas fa-lightbulb jm-ayuda-icon"></i>
                    <div class="jm-ayuda-texto">
                        <strong>Consejo:</strong> Use contraseñas únicas y seguras para proteger el sistema.
                    </div>
                </div>
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
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('clave');
            const confirmPasswordInput = document.getElementById('confirmar_clave');
            const strengthBar = document.getElementById('strengthBar');
            const requirements = {
                length: document.getElementById('req-length'),
                upper: document.getElementById('req-upper'),
                lower: document.getElementById('req-lower'),
                number: document.getElementById('req-number')
            };
            const passwordMatch = document.getElementById('passwordMatch');

            // Validación de fortaleza de contraseña
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                
                // Verificar longitud
                if (password.length >= 8) {
                    requirements.length.classList.add('met');
                    requirements.length.querySelector('i').className = 'fas fa-check';
                    strength++;
                } else {
                    requirements.length.classList.remove('met');
                    requirements.length.querySelector('i').className = 'fas fa-times';
                }
                
                // Verificar mayúscula
                if (/[A-Z]/.test(password)) {
                    requirements.upper.classList.add('met');
                    requirements.upper.querySelector('i').className = 'fas fa-check';
                    strength++;
                } else {
                    requirements.upper.classList.remove('met');
                    requirements.upper.querySelector('i').className = 'fas fa-times';
                }
                
                // Verificar minúscula
                if (/[a-z]/.test(password)) {
                    requirements.lower.classList.add('met');
                    requirements.lower.querySelector('i').className = 'fas fa-check';
                    strength++;
                } else {
                    requirements.lower.classList.remove('met');
                    requirements.lower.querySelector('i').className = 'fas fa-times';
                }
                
                // Verificar número
                if (/[0-9]/.test(password)) {
                    requirements.number.classList.add('met');
                    requirements.number.querySelector('i').className = 'fas fa-check';
                    strength++;
                } else {
                    requirements.number.classList.remove('met');
                    requirements.number.querySelector('i').className = 'fas fa-times';
                }
                
                // Actualizar barra de fortaleza
                strengthBar.className = 'jm-password-strength-bar';
                if (strength === 1) strengthBar.classList.add('strength-weak');
                else if (strength === 2) strengthBar.classList.add('strength-fair');
                else if (strength === 3) strengthBar.classList.add('strength-good');
                else if (strength === 4) strengthBar.classList.add('strength-strong');
                
                checkPasswordMatch();
            });

            // Verificar coincidencia de contraseñas
            function checkPasswordMatch() {
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                
                if (confirmPassword === '') {
                    passwordMatch.innerHTML = '';
                    return;
                }
                
                if (password === confirmPassword) {
                    passwordMatch.innerHTML = '<i class="fas fa-check" style="color: #27ae60;"></i> <span style="color: #27ae60;">Las contraseñas coinciden</span>';
                } else {
                    passwordMatch.innerHTML = '<i class="fas fa-times" style="color: #e74c3c;"></i> <span style="color: #e74c3c;">Las contraseñas no coinciden</span>';
                }
            }

            confirmPasswordInput.addEventListener('input', checkPasswordMatch);

            // Animaciones de entrada
            const formGroups = document.querySelectorAll('.jm-form-group');
            formGroups.forEach((group, index) => {
                group.style.opacity = '0';
                group.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    group.style.transition = 'all 0.5s ease';
                    group.style.opacity = '1';
                    group.style.transform = 'translateY(0)';
                }, index * 100);
            });

            // Efectos de focus en inputs
            const inputs = document.querySelectorAll('.jm-form-input');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'scale(1.02)';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'scale(1)';
                });
            });

            // Validación del formulario
            document.getElementById('userForm').addEventListener('submit', function(e) {
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                
                if (password !== confirmPassword) {
                    e.preventDefault();
                    alert('Las contraseñas no coinciden. Por favor, verifique e intente nuevamente.');
                    confirmPasswordInput.focus();
                    return false;
                }
                
                // Verificar fortaleza mínima
                const hasLength = password.length >= 8;
                const hasUpper = /[A-Z]/.test(password);
                const hasLower = /[a-z]/.test(password);
                const hasNumber = /[0-9]/.test(password);
                
                if (!hasLength || !hasUpper || !hasLower || !hasNumber) {
                    e.preventDefault();
                    alert('La contraseña debe cumplir con todos los requisitos de seguridad.');
                    passwordInput.focus();
                    return false;
                }
            });
        });
    </script>
</body>
</html>
