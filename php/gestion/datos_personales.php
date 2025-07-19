<?php
session_start();
include '../conexion.php';
if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.php");
    exit();
}
$stmt = $pdo->prepare("SELECT usuario, email, rol, fecha_registro FROM usuarios WHERE id = :id");
$stmt->bindParam(':id', $_SESSION['usuario_id']);
$stmt->execute();
$datos_usuario = $stmt->fetch();

if (!$datos_usuario) {
    header("Location: ../logout.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Datos Personales | Variedades Juanmarc</title>
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

        /* PERFIL DE USUARIO */
        .jm-perfil-usuario {
            background: white;
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        .jm-perfil-usuario::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #FF8C00, #FFA500, #FFD700);
        }

        .jm-perfil-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .jm-avatar {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 50px;
            color: white;
            box-shadow: 0 10px 30px rgba(52, 152, 219, 0.4);
            position: relative;
            overflow: hidden;
        }

        .jm-avatar::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            animation: shine 3s infinite;
        }

        .jm-perfil-nombre {
            font-size: 28px;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
        }

        .jm-perfil-rol {
            color: #7f8c8d;
            margin-top: 8px;
            font-size: 16px;
            font-weight: 500;
        }

        .jm-rol-badge {
            display: inline-block;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 10px;
        }

        .jm-rol-admin {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
        }

        .jm-rol-usuario {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
        }

        /* INFORMACIÓN DETALLADA */
        .jm-info-detallada {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .jm-info-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            padding: 25px;
            border-left: 5px solid;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .jm-info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .jm-info-card.usuario {
            border-left-color: #3498db;
        }

        .jm-info-card.email {
            border-left-color: #e74c3c;
        }

        .jm-info-card.rol {
            border-left-color: #9b59b6;
        }

        .jm-info-card.fecha {
            border-left-color: #f39c12;
        }

        .jm-info-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 15px;
        }

        .jm-info-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: white;
        }

        .jm-info-card.usuario .jm-info-icon {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        }

        .jm-info-card.email .jm-info-icon {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        }

        .jm-info-card.rol .jm-info-icon {
            background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);
        }

        .jm-info-card.fecha .jm-info-icon {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
        }

        .jm-info-label {
            font-size: 14px;
            font-weight: 600;
            color: #2c3e50;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .jm-info-value {
            font-size: 18px;
            font-weight: 700;
            color: #2c3e50;
            margin-top: 8px;
            word-break: break-all;
        }

        /* ESTADÍSTICAS RÁPIDAS */
        .jm-estadisticas {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(44, 62, 80, 0.3);
        }

        .jm-estadisticas-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .jm-estadisticas-titulo {
            font-size: 22px;
            font-weight: 700;
            margin: 0;
        }

        .jm-estadisticas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
        }

        .jm-stat-item {
            text-align: center;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 20px;
        }

        .jm-stat-numero {
            font-size: 32px;
            font-weight: 700;
            color: #FFD700;
            margin-bottom: 5px;
        }

        .jm-stat-label {
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.8;
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

        .jm-perfil-usuario {
            animation: slideInUp 0.6s ease;
        }

        .jm-estadisticas {
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
            
            .jm-perfil-usuario {
                padding: 25px;
            }

            .jm-navbar h2 {
                font-size: 18px;
            }

            .jm-info-detallada {
                grid-template-columns: 1fr;
            }

            .jm-estadisticas-grid {
                grid-template-columns: repeat(2, 1fr);
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
            <li><a href="datos_personales.php" class="jm-link active"><i class="fas fa-user mr-2"></i> Datos personales</a></li>
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
            <h2><i class="fas fa-user-circle mr-3"></i>Datos Personales</h2>
            <div class="jm-cart">
                <img src="https://img.icons8.com/ios-filled/24/ffffff/shopping-cart.png"/>
                <span class="jm-cart-badge">0</span>
            </div>
        </div>

        <!-- Perfil del usuario -->
        <div class="jm-perfil-usuario">
            <div class="jm-perfil-header">
                <div class="jm-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <h2 class="jm-perfil-nombre"><?php echo htmlspecialchars($datos_usuario['usuario']); ?></h2>
                <p class="jm-perfil-rol">
                    Usuario del Sistema
                    <span class="jm-rol-badge <?php echo $datos_usuario['rol'] === 'admin' ? 'jm-rol-admin' : 'jm-rol-usuario'; ?>">
                        <?php echo htmlspecialchars(ucfirst($datos_usuario['rol'])); ?>
                    </span>
                </p>
            </div>

            <div class="jm-info-detallada">
                <div class="jm-info-card usuario">
                    <div class="jm-info-header">
                        <div class="jm-info-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="jm-info-label">Nombre de Usuario</div>
                    </div>
                    <div class="jm-info-value"><?php echo htmlspecialchars($datos_usuario['usuario']); ?></div>
                </div>

                <div class="jm-info-card email">
                    <div class="jm-info-header">
                        <div class="jm-info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="jm-info-label">Correo Electrónico</div>
                    </div>
                    <div class="jm-info-value"><?php echo htmlspecialchars($datos_usuario['email']); ?></div>
                </div>

                <div class="jm-info-card rol">
                    <div class="jm-info-header">
                        <div class="jm-info-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="jm-info-label">Rol en el Sistema</div>
                    </div>
                    <div class="jm-info-value"><?php echo htmlspecialchars(ucfirst($datos_usuario['rol'])); ?></div>
                </div>

                <div class="jm-info-card fecha">
                    <div class="jm-info-header">
                        <div class="jm-info-icon">
                            <i class="fas fa-calendar-plus"></i>
                        </div>
                        <div class="jm-info-label">Fecha de Registro</div>
                    </div>
                    <div class="jm-info-value"><?php echo htmlspecialchars(date('d/m/Y', strtotime($datos_usuario['fecha_registro']))); ?></div>
                </div>
            </div>
        </div>

        <!-- Estadísticas rápidas -->
        <div class="jm-estadisticas">
            <div class="jm-estadisticas-header">
                <h3 class="jm-estadisticas-titulo">Estadísticas de Cuenta</h3>
            </div>
            <div class="jm-estadisticas-grid">
                <div class="jm-stat-item">
                    <div class="jm-stat-numero" id="diasRegistrado">0</div>
                    <div class="jm-stat-label">Días Registrado</div>
                </div>
                <div class="jm-stat-item">
                    <div class="jm-stat-numero">1</div>
                    <div class="jm-stat-label">Cuenta Activa</div>
                </div>
                <div class="jm-stat-item">
                    <div class="jm-stat-numero"><?php echo $datos_usuario['rol'] === 'admin' ? '∞' : '1'; ?></div>
                    <div class="jm-stat-label">Nivel de Acceso</div>
                </div>
                <div class="jm-stat-item">
                    <div class="jm-stat-numero">100%</div>
                    <div class="jm-stat-label">Perfil Completo</div>
                </div>
            </div>
        </div>

        <a href="../index.php" class="jm-btn-volver">
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
        // Animaciones y efectos
        document.addEventListener('DOMContentLoaded', function() {
            // Calcular días desde el registro
            const fechaRegistro = new Date('<?php echo $datos_usuario['fecha_registro']; ?>');
            const fechaActual = new Date();
            const diasRegistrado = Math.floor((fechaActual - fechaRegistro) / (1000 * 60 * 60 * 24));
            
            // Animación de conteo para días registrado
            const diasElement = document.getElementById('diasRegistrado');
            let currentCount = 0;
            const increment = Math.ceil(diasRegistrado / 50);
            
            const timer = setInterval(() => {
                currentCount += increment;
                if (currentCount >= diasRegistrado) {
                    currentCount = diasRegistrado;
                    clearInterval(timer);
                }
                diasElement.textContent = currentCount;
            }, 30);

            // Animación de entrada para las tarjetas de información
            const infoCards = document.querySelectorAll('.jm-info-card');
            infoCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 150 + 300);
            });

            // Animación para las estadísticas
            const statItems = document.querySelectorAll('.jm-stat-item');
            statItems.forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'scale(0.8)';
                setTimeout(() => {
                    item.style.transition = 'all 0.5s ease';
                    item.style.opacity = '1';
                    item.style.transform = 'scale(1)';
                }, index * 100 + 800);
            });

            // Efecto de hover mejorado para las tarjetas
            infoCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px) scale(1.02)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });

            // Efecto de pulso para el avatar
            const avatar = document.querySelector('.jm-avatar');
            setInterval(() => {
                avatar.style.transform = 'scale(1.05)';
                setTimeout(() => {
                    avatar.style.transform = 'scale(1)';
                }, 200);
            }, 3000);

            // Mostrar información adicional basada en el rol
            const rolBadge = document.querySelector('.jm-rol-badge');
            if (rolBadge) {
                rolBadge.addEventListener('click', function() {
                    const rol = '<?php echo $datos_usuario['rol']; ?>';
                    let mensaje = '';
                    
                    if (rol === 'admin') {
                        mensaje = '🔑 Como administrador, tienes acceso completo a todas las funciones del sistema.';
                    } else {
                        mensaje = '👤 Como usuario estándar, tienes acceso a las funciones básicas del sistema.';
                    }
                    
                    // Crear notificación temporal
                    const notification = document.createElement('div');
                    notification.style.cssText = `
                        position: fixed;
                        top: 20px;
                        right: 20px;
                        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
                        color: white;
                        padding: 15px 20px;
                        border-radius: 10px;
                        box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
                        z-index: 10000;
                        font-size: 14px;
                        max-width: 300px;
                        animation: slideInRight 0.5s ease;
                    `;
                    notification.textContent = mensaje;
                    
                    document.body.appendChild(notification);
                    
                    setTimeout(() => {
                        notification.style.animation = 'slideOutRight 0.5s ease';
                        setTimeout(() => {
                            document.body.removeChild(notification);
                        }, 500);
                    }, 3000);
                });
            }
        });

        // Agregar estilos para las animaciones de notificación
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInRight {
                from {
                    opacity: 0;
                    transform: translateX(100%);
                }
                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }
            
            @keyframes slideOutRight {
                from {
                    opacity: 1;
                    transform: translateX(0);
                }
                to {
                    opacity: 0;
                    transform: translateX(100%);
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
