<?php
session_start();
include '../conexion.php';

// Verificar si el usuario ha iniciado sesión y es administrador
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php"); // Redirigir si no es administrador
    exit();
}

// Verificar si se recibió el ID del usuario a editar
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../gestion/gestion_usuario.php?error=id_invalido");
    exit();
}

$usuario_id = $_GET['id'];

// Obtener la información del usuario a editar
$stmt_select = $pdo->prepare("SELECT id, usuario, email, rol, fecha_registro FROM usuarios WHERE id = :id");
$stmt_select->bindParam(':id', $usuario_id);
$stmt_select->execute();
$usuario_editar = $stmt_select->fetch();

if (!$usuario_editar) {
    header("Location: ../gestion/gestion_usuario.php?error=usuario_no_encontrado");
    exit();
}

// Variables para los mensajes y los datos del formulario
$mensaje = '';
$usuario = $usuario_editar['usuario'];
$email = $usuario_editar['email'];
$rol = $usuario_editar['rol'];

// Procesar el formulario cuando se envíe
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $email = $_POST['email'] ?? '';
    $rol = $_POST['rol'] ?? 'empleado';

    // Validaciones
    if (empty($usuario) || empty($email)) {
        $mensaje = '<div class="alert alert-danger">El usuario y el email son obligatorios.</div>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = '<div class="alert alert-danger">El formato del email no es válido.</div>';
    } else {
        // Verificar si el nuevo usuario o email ya existen para otro usuario
        $stmt_check = $pdo->prepare("SELECT id FROM usuarios WHERE (usuario = :usuario AND id != :id) OR (email = :email AND id != :id)");
        $stmt_check->bindParam(':usuario', $usuario);
        $stmt_check->bindParam(':email', $email);
        $stmt_check->bindParam(':id', $usuario_id);
        $stmt_check->execute();
        if ($stmt_check->fetch()) {
            $mensaje = '<div class="alert alert-danger">El usuario o el email ya están en uso por otro usuario.</div>';
        } else {
            try {
                $stmt_update = $pdo->prepare("UPDATE usuarios SET usuario = :usuario, email = :email, rol = :rol WHERE id = :id");
                $stmt_update->bindParam(':usuario', $usuario);
                $stmt_update->bindParam(':email', $email);
                $stmt_update->bindParam(':rol', $rol);
                $stmt_update->bindParam(':id', $usuario_id);
                $stmt_update->execute();
                $mensaje = '<div class="alert alert-success">Usuario actualizado correctamente.</div>';
                // Actualizar los datos locales después de la actualización
                $usuario_editar['usuario'] = $usuario;
                $usuario_editar['email'] = $email;
                $usuario_editar['rol'] = $rol;
            } catch (PDOException $e) {
                $mensaje = '<div class="alert alert-danger">Error al actualizar el usuario: ' . $e->getMessage() . '</div>';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario | Variedades Juanmarc</title>
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
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
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

        /* INFORMACIÓN DEL USUARIO */
        .jm-info-usuario {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            border-left: 5px solid #007bff;
        }

        .jm-info-titulo {
            font-size: 18px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .jm-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .jm-info-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .jm-info-label {
            font-weight: 600;
            color: #6c757d;
            font-size: 14px;
        }

        .jm-info-value {
            font-weight: 700;
            color: #2c3e50;
            font-size: 14px;
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
            border-color: #007bff;
            background: white;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
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
            border-color: #007bff;
            background: white;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
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
            border-color: #007bff;
            background: rgba(0, 123, 255, 0.1);
            color: #007bff;
        }

        .jm-rol-icon {
            font-size: 20px;
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
        }

        .jm-btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
            color: white;
            text-decoration: none;
        }

        /* PANEL DE INFORMACIÓN */
        .jm-panel-info {
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

        .jm-info-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }

        .jm-info-card-titulo {
            font-size: 16px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .jm-info-card-contenido {
            font-size: 14px;
            color: #6c757d;
            line-height: 1.5;
        }

        .jm-cambios-recientes {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border-radius: 12px;
            padding: 15px;
            border-left: 4px solid #f39c12;
        }

        .jm-cambios-titulo {
            font-size: 14px;
            font-weight: 700;
            color: #856404;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .jm-cambios-texto {
            font-size: 12px;
            color: #856404;
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
            
            .jm-panel-info {
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

            .jm-info-grid {
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
                <li><a href="../gestion/gestion_usuario.php" class="jm-link active"><i class="fas fa-cog mr-2"></i> Gestión usuario</a></li>
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
                <li><a href="../gestion/gestion_proveedor.php" class="jm-link"><i class="fas fa-truck mr-2"></i> Gestión proveedor</a></li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="jm-main">
        <div class="jm-navbar">
            <h2><i class="fas fa-user-edit mr-3"></i>Editar Usuario</h2>
            <div class="jm-cart">
                <img src="https://img.icons8.com/ios-filled/24/ffffff/shopping-cart.png"/>
                <span class="jm-cart-badge">0</span>
            </div>
        </div>

        <div class="jm-contenedor-principal">
            <div class="jm-formulario-container">
                <div class="jm-formulario-header">
                    <h2 class="jm-formulario-titulo">
                        <i class="fas fa-user-edit"></i>
                        Editar Usuario
                    </h2>
                    <p class="jm-formulario-subtitulo">Modifique la información del usuario seleccionado</p>
                </div>

                <!-- Información actual del usuario -->
                <div class="jm-info-usuario">
                    <h3 class="jm-info-titulo">
                        <i class="fas fa-info-circle"></i>
                        Información Actual
                    </h3>
                    <div class="jm-info-grid">
                        <div class="jm-info-item">
                            <span class="jm-info-label">ID:</span>
                            <span class="jm-info-value">#<?php echo htmlspecialchars($usuario_editar['id']); ?></span>
                        </div>
                        <div class="jm-info-item">
                            <span class="jm-info-label">Registro:</span>
                            <span class="jm-info-value"><?php echo htmlspecialchars(date('d/m/Y', strtotime($usuario_editar['fecha_registro']))); ?></span>
                        </div>
                        <div class="jm-info-item">
                            <span class="jm-info-label">Rol Actual:</span>
                            <span class="jm-info-value">
                                <?php if ($usuario_editar['rol'] === 'admin'): ?>
                                    <i class="fas fa-shield-alt" style="color: #e74c3c;"></i> Administrador
                                <?php else: ?>
                                    <i class="fas fa-user" style="color: #27ae60;"></i> Empleado
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
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

                <form method="post" id="editUserForm">
                    <div class="jm-form-group">
                        <label for="usuario" class="jm-form-label">
                            <i class="fas fa-user mr-2"></i>Nombre de Usuario
                        </label>
                        <input type="text" 
                               class="jm-form-input" 
                               id="usuario" 
                               name="usuario" 
                               value="<?php echo htmlspecialchars($usuario_editar['usuario']); ?>" 
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
                               value="<?php echo htmlspecialchars($usuario_editar['email']); ?>" 
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
                                       <?php if ($usuario_editar['rol'] === 'empleado') echo 'checked'; ?>>
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
                                       <?php if ($usuario_editar['rol'] === 'admin') echo 'checked'; ?>>
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

                    <div class="jm-botones-container">
                        <button type="submit" class="jm-btn jm-btn-primary">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                        <a href="../gestion/gestion_usuario.php" class="jm-btn jm-btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </form>
            </div>

            <div class="jm-panel-info">
                <h3 class="jm-panel-titulo">
                    <i class="fas fa-info-circle"></i>
                    Información de Edición
                </h3>
                
                <div class="jm-info-card">
                    <h4 class="jm-info-card-titulo">
                        <i class="fas fa-user-edit"></i>
                        Datos Modificables
                    </h4>
                    <div class="jm-info-card-contenido">
                        Puede cambiar el nombre de usuario, email y rol. Los cambios se aplicarán inmediatamente.
                    </div>
                </div>

                <div class="jm-info-card">
                    <h4 class="jm-info-card-titulo">
                        <i class="fas fa-shield-alt"></i>
                        Roles Disponibles
                    </h4>
                    <div class="jm-info-card-contenido">
                        <strong>Empleado:</strong> Acceso básico al sistema.<br>
                        <strong>Administrador:</strong> Control total del sistema.
                    </div>
                </div>

                <div class="jm-info-card">
                    <h4 class="jm-info-card-titulo">
                        <i class="fas fa-exclamation-triangle"></i>
                        Validaciones
                    </h4>
                    <div class="jm-info-card-contenido">
                        El sistema verifica que el usuario y email no estén en uso por otros usuarios.
                    </div>
                </div>

                <div class="jm-cambios-recientes">
                    <h4 class="jm-cambios-titulo">
                        <i class="fas fa-clock"></i>
                        Último Cambio
                    </h4>
                    <div class="jm-cambios-texto">
                        Los cambios se guardan automáticamente y son efectivos de inmediato.
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
            document.getElementById('editUserForm').addEventListener('submit', function(e) {
                const usuario = document.getElementById('usuario').value.trim();
                const email = document.getElementById('email').value.trim();
                
                if (!usuario || !email) {
                    e.preventDefault();
                    alert('Por favor, complete todos los campos obligatorios.');
                    return false;
                }
                
                // Validar formato de email
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    e.preventDefault();
                    alert('Por favor, ingrese un email válido.');
                    document.getElementById('email').focus();
                    return false;
                }
            });

            // Detectar cambios en el formulario
            const originalValues = {
                usuario: document.getElementById('usuario').value,
                email: document.getElementById('email').value,
                rol: document.querySelector('input[name="rol"]:checked').value
            };

            function detectChanges() {
                const currentValues = {
                    usuario: document.getElementById('usuario').value,
                    email: document.getElementById('email').value,
                    rol: document.querySelector('input[name="rol"]:checked')?.value
                };

                const hasChanges = Object.keys(originalValues).some(key => 
                    originalValues[key] !== currentValues[key]
                );

                const submitBtn = document.querySelector('.jm-btn-primary');
                if (hasChanges) {
                    submitBtn.style.background = 'linear-gradient(135deg, #28a745 0%, #20c997 100%)';
                    submitBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Cambios';
                } else {
                    submitBtn.style.background = 'linear-gradient(135deg, #007bff 0%, #0056b3 100%)';
                    submitBtn.innerHTML = '<i class="fas fa-save"></i> Sin Cambios';
                }
            }

            // Escuchar cambios en todos los campos
            document.getElementById('usuario').addEventListener('input', detectChanges);
            document.getElementById('email').addEventListener('input', detectChanges);
            document.querySelectorAll('input[name="rol"]').forEach(radio => {
                radio.addEventListener('change', detectChanges);
            });

            // Animación de la información del usuario
            const infoUsuario = document.querySelector('.jm-info-usuario');
            infoUsuario.style.opacity = '0';
            infoUsuario.style.transform = 'translateY(-20px)';
            setTimeout(() => {
                infoUsuario.style.transition = 'all 0.6s ease';
                infoUsuario.style.opacity = '1';
                infoUsuario.style.transform = 'translateY(0)';
            }, 200);
        });
    </script>
</body>
</html>
