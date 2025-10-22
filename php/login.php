<?php
session_start();
if (isset($_SESSION['usuario'])) {
    header("Location: index.php"); 
    exit();
}
$mensaje_error = $_SESSION['error_login'] ?? null;
unset($_SESSION['error_login']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión | Variedades Juanmarc</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        /* Estilos CSS Integrados y Modificados */
        * {
            box-sizing: border-box;
        }

        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
            font-family: 'Fredoka', sans-serif;
            background: linear-gradient(to top right, #ffecd2, rgb(210, 38, 15));
            position: relative;
        }

        /* Burbujas flotantes (productos) */
        .product {
            position: absolute;
            width: 40px;
            height: 40px;
            background-size: contain;
            background-repeat: no-repeat;
            opacity: 0;
            animation: bubbleMove 10s linear infinite;
        }

        @keyframes bubbleMove {
            0% {
                transform: translateY(100vh) scale(0.5) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 0.3;
            }
            50% {
                opacity: 0.7;
                transform: translateY(50vh) scale(1) rotate(10deg);
            }
            90% {
                opacity: 0.3;
            }
            100% {
                transform: translateY(-10vh) scale(0.8) rotate(-15deg);
                opacity: 0;
            }
        }
        .product:nth-child(1)  { left: 5%;   animation-delay: 0s;   background-image: url('../imagenes/m1,1.png'); }
        .product:nth-child(2)  { left: 15%;  animation-delay: 3s;   background-image: url('../imagenes/m2,1.png'); }
        .product:nth-child(3)  { left: 25%;  animation-delay: 6s;   background-image: url('../imagenes/m3.png'); }
        .product:nth-child(4)  { left: 35%;  animation-delay: 2s;   background-image: url('../imagenes/m4.png'); }
        .product:nth-child(5)  { left: 45%;  animation-delay: 4s;   background-image: url('../imagenes/m5.png'); }
        .product:nth-child(6)  { left: 55%;  animation-delay: 7s;   background-image: url('../imagenes/m6.png'); }
        .product:nth-child(7)  { left: 65%;  animation-delay: 1s;   background-image: url('../imagenes/m7.png'); }
        .product:nth-child(8)  { left: 75%;  animation-delay: 5s;   background-image: url('../imagenes/m8.png'); }
        .product:nth-child(9)  { left: 85%;  animation-delay: 8s;   background-image: url('../imagenes/m9.png'); }
        .product:nth-child(10) { left: 95%;  animation-delay: 9s;   background-image: url('../imagenes/m10.png'); }
        .product:nth-child(11) { left: 10%;  animation-delay: 6s;   background-image: url('../imagenes/m11.png'); }
        .product:nth-child(12) { left: 50%;  animation-delay: 1.5s; background-image: url('../imagenes/m12.png'); }
        .product:nth-child(13) { left: 60%;  animation-delay: 2.5s; background-image: url('../imagenes/m13.png'); }
        .product:nth-child(14) { left: 70%;  animation-delay: 3.5s; background-image: url('../imagenes/m14.png'); }

        /* Caja de Login */
        #app {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            position: relative;
            z-index: 1;
        }

        .login-container {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 15px 25px rgba(0,0,0,0.1);
            text-align: center;
            animation: slideIn 1s ease;
            width: 300px;
            z-index: 2;
        }

        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .logo {
            width: 80px;
            height: 80px;
            margin-bottom: 1rem;
            animation: floatLogo 2s ease-in-out infinite alternate;
        }

        @keyframes floatLogo {
            from { transform: translateY(0); }
            to { transform: translateY(-10px); }
        }

        h2 {
            color: #ff7e5f;
            margin-bottom: 1rem;
            font-size: 1.5rem;
            font-family: 'Fredoka', sans-serif;
        }

        .form-group {
            margin-bottom: 0.7rem;
        }

        .form-control {
            width: 100%;
            padding: 0.7rem;
            border: 1px solid #ddd;
            border-radius: 10px;
            transition: all 0.3s ease;
            font-family: 'Fredoka', sans-serif;
        }

        .form-control:focus {
            border-color: #ff7e5f;
            outline: none;
            transform: scale(1.05);
        }

        .btn-login {
            background-color: #ff7e5f;
            color: white;
            border: none;
            padding: 0.7rem 1rem;
            width: 100%;
            border-radius: 10px;
            cursor: pointer;
            transition: background 0.3s ease;
            font-family: 'Fredoka', sans-serif;
        }

        .btn-login:hover {
            background-color: #feb47b;
        }

        .signup-text {
            margin-top: 1rem;
            font-size: 0.9rem;
            font-family: 'Fredoka', sans-serif;
        }

        .signup-text a {
            color: #ff7e5f;
            text-decoration: none;
            font-family: 'Fredoka', sans-serif;
        }

        footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            text-align: center;
            padding: 1rem;
            font-size: 0.9rem;
            color: #6c757d;
            font-family: 'Fredoka', sans-serif;
        }
    </style>
</head>
<body>
    <div class="product"></div>
    <div class="product"></div>
    <div class="product"></div>
    <div class="product"></div>
    <div class="product"></div>
    <div class="product"></div>
    <div class="product"></div>
    <div class="product"></div>
    <div class="product"></div>
    <div class="product"></div>
    <div class="product"></div>
    <div class="product"></div>
    <div class="product"></div>
    <div class="product"></div>
    <div class="product"></div>

    <div id="app">
        <div id="login-view" class="login-container">
            <img src="../imagenes/logo.jpeg" alt="Logo Juanmarc" class="logo">
            <h2>¡Bienvenido a variedades JUANMARC!</h2>
            <?php if ($mensaje_error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($mensaje_error); ?></div>
            <?php endif; ?>
            <form action="procesar_login.php" method="POST">
                <div class="form-group">
                    <input type="text" name="usuario" class="form-control" placeholder="Usuario" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" class="form-control" placeholder="Contraseña" required>
                </div>
                <button type="submit" class="btn btn-login btn-block">Entrar</button>
            </form>
        </div>
    </div>
    <footer>
        <p>&copy; 2025 Variedades Juanmarc. Todos los derechos reservados.</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>