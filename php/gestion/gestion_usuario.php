<?php
session_start();
include '../conexion.php';
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php"); 
    exit();
}
$stmt = $pdo->prepare("SELECT id, usuario, email, rol, fecha_registro FROM usuarios WHERE id != :id_admin");
$stmt->bindParam(':id_admin', $_SESSION['usuario_id']);
$stmt->execute();
$usuarios = $stmt->fetchAll();
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
    <title>Gestión de Usuarios | Variedades Juanmarc</title>
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

        /* ESTADÍSTICAS DE USUARIOS */
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

        .stat-admins {
            border-left: 4px solid #e74c3c;
        }

        .stat-admins .jm-stat-icon {
            color: #e74c3c;
        }

        .stat-admins .jm-stat-number {
            color: #e74c3c;
        }

        .stat-usuarios {
            border-left: 4px solid #27ae60;
        }

        .stat-usuarios .jm-stat-icon {
            color: #27ae60;
        }

        .stat-usuarios .jm-stat-number {
            color: #27ae60;
        }

        .stat-recientes {
            border-left: 4px solid #f39c12;
        }

        .stat-recientes .jm-stat-icon {
            color: #f39c12;
        }

        .stat-recientes .jm-stat-number {
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
            text-align: left;
            font-weight: 600;
            color: #2c3e50;
        }

        .email-cell {
            font-weight: 500;
            color: #3498db;
            font-size: 13px;
        }

        .rol-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .rol-admin {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            box-shadow: 0 2px 8px rgba(231, 76, 60, 0.3);
        }

        .rol-usuario {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            box-shadow: 0 2px 8px rgba(39, 174, 96, 0.3);
        }

        .fecha-cell {
            font-size: 13px;
            color: #7f8c8d;
            font-weight: 500;
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
        }
    </style>
</head>

<body class="jm-body">
    <?php include 'sidebar.php'; ?>
    <div class="jm-main">
        <div class="jm-navbar">
            <h2><i class="fas fa-users-cog mr-3"></i>Gestión de Usuarios</h2>
            <div class="jm-cart">
                <img src="https://img.icons8.com/ios-filled/24/ffffff/shopping-cart.png"/>
                <span class="jm-cart-badge">0</span>
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

        <?php
        // Calcular estadísticas de usuarios
        $totalUsuarios = count($usuarios);
        $adminCount = 0;
        $usuarioCount = 0;
        $recientesCount = 0;
        $fechaLimite = date('Y-m-d', strtotime('-30 days'));

        foreach ($usuarios as $usuario) {
            if ($usuario['rol'] === 'admin') {
                $adminCount++;
            } else {
                $usuarioCount++;
            }
            
            if ($usuario['fecha_registro'] >= $fechaLimite) {
                $recientesCount++;
            }
        }
        ?>

        <!-- Estadísticas de Usuarios -->
        <div class="jm-estadisticas">
            <div class="jm-stat-card stat-total">
                <div class="jm-stat-icon"><i class="fas fa-users"></i></div>
                <div class="jm-stat-number"><?php echo $totalUsuarios; ?></div>
                <div class="jm-stat-label">Total Usuarios</div>
            </div>
            <div class="jm-stat-card stat-admins">
                <div class="jm-stat-icon"><i class="fas fa-user-shield"></i></div>
                <div class="jm-stat-number"><?php echo $adminCount; ?></div>
                <div class="jm-stat-label">Administradores</div>
            </div>
            <div class="jm-stat-card stat-usuarios">
                <div class="jm-stat-icon"><i class="fas fa-user"></i></div>
                <div class="jm-stat-number"><?php echo $usuarioCount; ?></div>
                <div class="jm-stat-label">Usuarios Estándar</div>
            </div>
            <div class="jm-stat-card stat-recientes">
                <div class="jm-stat-icon"><i class="fas fa-user-plus"></i></div>
                <div class="jm-stat-number"><?php echo $recientesCount; ?></div>
                <div class="jm-stat-label">Registros Recientes</div>
            </div>
        </div>

        <div class="jm-contenedor-botones">
            <a href="../agregar/agregar_usuario.php" class="btn-agregar">
                <i class="fas fa-user-plus"></i> Agregar Usuario
            </a>
        </div>

        <?php if (empty($usuarios)): ?>
            <div class="jm-estado-vacio">
                <div class="jm-estado-vacio-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3 class="jm-estado-vacio-titulo">No hay usuarios registrados</h3>
                <p class="jm-estado-vacio-mensaje">
                    Comienza agregando el primer usuario al sistema para gestionar los accesos.
                </p>
                <a href="agregar_usuario.php" class="btn-agregar">
                    <i class="fas fa-user-plus"></i> Agregar Primer Usuario
                </a>
            </div>
        <?php else: ?>
            <div class="jm-tabla-container">
                <table class="jm-tabla">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Fecha de Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($usuario['id']); ?></td>
                                <td style="text-align: left;">
                                    <i class="fas fa-user mr-2" style="color: #3498db;"></i>
                                    <?php echo htmlspecialchars($usuario['usuario']); ?>
                                </td>
                                <td class="email-cell"><?php echo htmlspecialchars($usuario['email']); ?></td>
                                <td>
                                    <span class="rol-badge <?php echo $usuario['rol'] === 'admin' ? 'rol-admin' : 'rol-usuario'; ?>">
                                        <i class="fas <?php echo $usuario['rol'] === 'admin' ? 'fa-shield-alt' : 'fa-user'; ?>"></i>
                                        <?php echo htmlspecialchars(ucfirst($usuario['rol'])); ?>
                                    </span>
                                </td>
                                <td class="fecha-cell"><?php echo htmlspecialchars(date('d/m/Y', strtotime($usuario['fecha_registro']))); ?></td>
                                <td class="jm-gestion-acciones">
                                    <a href="editar_usuario.php?id=$usuario <?php echo htmlspecialchars($usuario['id']); ?>" class="btn-accion btn-editar">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <a href="eliminar_usuario.php?id=<?php echo htmlspecialchars($usuario['id']); ?>" 
                                       class="btn-accion btn-eliminar" 
                                       onclick="return confirm('¿Estás seguro de que deseas eliminar este usuario?')">
                                        <i class="fas fa-trash-alt"></i> Eliminar
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

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

            // Efecto de hover mejorado para las filas de la tabla
            rows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.02) translateX(5px)';
                });
                
                row.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1) translateX(0)';
                });
            });

            // Animación de conteo para las estadísticas
            const numbers = document.querySelectorAll('.jm-stat-number');
            numbers.forEach(number => {
                const finalValue = parseInt(number.textContent);
                if (!isNaN(finalValue) && finalValue > 0) {
                    let currentValue = 0;
                    const increment = Math.ceil(finalValue / 20);
                    const timer = setInterval(() => {
                        currentValue += increment;
                        if (currentValue >= finalValue) {
                            currentValue = finalValue;
                            clearInterval(timer);
                        }
                        number.textContent = currentValue;
                    }, 50);
                }
            });

            // Efecto de click en las estadísticas
            statCards.forEach(card => {
                card.addEventListener('click', function() {
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = 'scale(1)';
                    }, 150);
                });
            });
        });

        // Confirmación mejorada para eliminar usuarios
        document.querySelectorAll('.btn-eliminar').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const usuario = this.closest('tr').querySelector('td:nth-child(2)').textContent.trim();
                
                if (confirm(`⚠️ ¿Estás seguro de que deseas eliminar al usuario "${usuario}"?\n\nEsta acción no se puede deshacer.`)) {
                    window.location.href = this.href;
                }
            });
        });
    </script>
</body>
</html>
