<?php
// Datos de conexión a la base de datos
$servername = "localhost"; // O la dirección de tu servidor de base de datos
$username = "tu_usuario_db"; // Tu nombre de usuario de la base de datos
$password = "tu_contraseña_db"; // Tu contraseña de la base de datos
$dbname = "variedades_juanmarc"; // El nombre de tu base de datos

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    // Configurar PDO para que lance excepciones en caso de error
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Opcional: Configurar el modo de obtención por defecto a asociativo
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

// **Base de datos (para copiar y pegar en tu gestor SQL como phpMyAdmin, Dbeaver, etc.)**
/*
-- Crear la base de datos si no existe
CREATE DATABASE IF NOT EXISTS variedades_juanmarc;

-- Usar la base de datos
USE variedades_juanmarc;

-- Crear la tabla de usuarios (si aún no existe)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    -- Puedes agregar más campos como 'nombre_completo', 'email', etc.
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Crear la tabla de proveedores (si aún no existe)
CREATE TABLE IF NOT EXISTS proveedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_proveedor VARCHAR(100) NOT NULL,
    -- Puedes agregar más campos como 'direccion', 'telefono', 'email', etc.
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Crear la tabla de productos (si aún no existe)
CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    nombre_producto VARCHAR(255) NOT NULL,
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    laboratorio VARCHAR(100),
    presentacion VARCHAR(100),
    proveedor_id INT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (proveedor_id) REFERENCES proveedores(id)
);

-- Crear la tabla de lotes (si aún no existe)
CREATE TABLE IF NOT EXISTS lotes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    fecha_vencimiento DATE,
    cantidad INT UNSIGNED NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (producto_id) REFERENCES productos(id)
);

-- Insertar datos de ejemplo en la tabla de proveedores
INSERT INTO proveedores (nombre_proveedor) VALUES
('Proveedor A'),
('Proveedor B');

-- Insertar datos de ejemplo en la tabla de productos
INSERT INTO productos (codigo, nombre_producto, stock, laboratorio, presentacion, proveedor_id) VALUES
('36', 'A FOLIC', 3, 'IQ FARMA', 'TABLETA', 1),
('41', 'AB AMBROMOX', 685, 'FARMINDUSTRIA', 'INYECTABLE', 2);

-- Insertar datos de ejemplo en la tabla de lotes (para los productos de ejemplo)
-- Asumiendo que la fecha actual es 2025-04-12
INSERT INTO lotes (producto_id, fecha_vencimiento, cantidad) VALUES
(1, '2025-03-14', 3), -- Vencido hace 29 días
(2, '2025-04-13', 685); -- Vence en 1 día

-- Puedes crear más tablas relacionadas como 'ventas', 'detalles_venta', etc.,
-- dependiendo de la funcionalidad completa de tu sistema.
*/
?>