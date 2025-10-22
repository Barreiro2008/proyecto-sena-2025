-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 24-05-2025 a las 17:54:27
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `prueba3`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_venta`
--

CREATE TABLE `detalles_venta` (
  `id` int(11) NOT NULL,
  `venta_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalles_venta`
--

INSERT INTO `detalles_venta` (`id`, `venta_id`, `producto_id`, `cantidad`, `subtotal`, `precio_unitario`) VALUES
(3, 4, 67, 2, 0.00, 9000.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lotes`
--

CREATE TABLE `lotes` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `fecha_ingreso` date NOT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `cantidad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `proveedor_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `descripcion`, `precio`, `stock`, `proveedor_id`) VALUES
(44, 'bridge chocolate', NULL, 1000.00, 50, 9),
(45, 'bridge fresa', NULL, 1000.00, 50, 9),
(46, 'bridge vainilla', NULL, 1000.00, 50, 9),
(47, 'bridge ron con pasas', NULL, 1000.00, 50, 9),
(48, 'grissli gusanos', NULL, 200.00, 50, 9),
(49, 'menta helada', NULL, 200.00, 50, 9),
(50, 'mellows corazon', NULL, 300.00, 50, 9),
(51, 'nucita crema', NULL, 800.00, 50, 9),
(52, 'piazza girafa fresa', NULL, 500.00, 50, 9),
(53, 'piazza girafa arequipe', NULL, 500.00, 50, 9),
(54, 'piazza girafa chocolate', NULL, 500.00, 50, 9),
(55, 'piazza girafa vainilla', NULL, 500.00, 50, 9),
(56, 'snow mint', NULL, 200.00, 50, 9),
(57, 'splot tattoo', NULL, 200.00, 50, 9),
(58, 'bombones', NULL, 600.00, 50, 9),
(59, 'mermelada', NULL, 2000.00, 50, 9),
(60, 'vinagre', NULL, 2000.00, 50, 9),
(61, 'salsa para carnes', NULL, 2000.00, 50, 9),
(62, 'salsa negra', NULL, 2000.00, 50, 9),
(63, 'salsa de soya', NULL, 2500.00, 50, 9),
(64, 'mayonesa constancia 80G', NULL, 2000.00, 50, 9),
(65, 'salsa tomate constancia', NULL, 2000.00, 50, 9),
(66, 'mermelada', NULL, 2000.00, 50, 9),
(67, 'atun vancap', NULL, 9000.00, 48, 9),
(68, 'sardina vancan', NULL, 10000.00, 50, 9),
(69, 'haina pan blanca', NULL, 2600.00, 50, 9),
(70, 'galleta muuu', NULL, 500.00, 50, 9),
(71, 'ponky', NULL, 1500.00, 50, 9),
(72, 'galletas capri', NULL, 500.00, 50, 9),
(73, 'azucar', NULL, 2500.00, 50, 9),
(74, 'harina trigo americana', NULL, 2000.00, 50, 9),
(75, 'lozacrem', NULL, 2500.00, 50, 9),
(76, 'super riel', NULL, 4200.00, 50, 9),
(77, 'xtime', NULL, 200.00, 50, 9),
(78, 'avena don pancho 180g', NULL, 2500.00, 50, 9);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `contacto` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id`, `nombre`, `contacto`, `telefono`) VALUES
(2, 'Diana', 'Alex', '312 3904694'),
(3, 'Babaria', 'Empleado Babaria', '1 800 0526555'),
(4, 'Uniasociados Limitada', 'Trabajadora Univentas', '311 4972216'),
(5, 'Coca-Cola', 'Victor ', '313 6578900'),
(6, 'Postobón', 'Empleado Postobon', '318 3864356'),
(7, 'Ramo', 'Carlos Corporativo', '321 3367442'),
(8, 'Alpina', 'Miguel Alpina', '317 3662952'),
(9, 'Eqco', 'Gestor Comercial', '310 2959441'),
(10, 'Distrisol ', 'Iván', '314 2709482'),
(11, 'Yupi', 'Octavio', '312 4781414'),
(12, 'Margarita', 'Dimarwi', '321 4626926'),
(13, 'Unimax', 'German Ninco', '314 4643000'),
(14, 'Agranel', 'Augusto', '312 5892513');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','empleado') NOT NULL DEFAULT 'empleado',
  `nombre` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `usuario`, `password`, `rol`, `nombre`, `email`, `fecha_registro`) VALUES
(1, 'ADMIN', '$2y$10$2vZSeYwD5KscYqz5Q.sfk.jHxeowUzhedoU5.2iowGoVHTf2HuQ0.', 'admin', 'ADMIN', 'admin@gmail.com', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha_venta` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_venta` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id`, `usuario_id`, `fecha_venta`, `total_venta`) VALUES
(2, 1, '2025-05-17 13:35:57', 20000.00),
(3, 1, '2025-05-17 13:37:28', 34000.00),
(4, 1, '2025-05-17 16:17:21', 18000.00);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `detalles_venta`
--
ALTER TABLE `detalles_venta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `venta_id` (`venta_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `lotes`
--
ALTER TABLE `lotes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `proveedor_id` (`proveedor_id`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `detalles_venta`
--
ALTER TABLE `detalles_venta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `lotes`
--
ALTER TABLE `lotes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalles_venta`
--
ALTER TABLE `detalles_venta`
  ADD CONSTRAINT `detalles_venta_ibfk_1` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`),
  ADD CONSTRAINT `detalles_venta_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `lotes`
--
ALTER TABLE `lotes`
  ADD CONSTRAINT `lotes_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`);

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
