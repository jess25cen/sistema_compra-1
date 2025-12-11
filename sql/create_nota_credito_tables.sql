-- Crear tablas para Nota de Crédito Compra

CREATE TABLE IF NOT EXISTS `nota_credito` (
  `id_nota_credito` int(11) NOT NULL AUTO_INCREMENT,
  `numero_nota` varchar(50) NOT NULL,
  `fecha_nota` date NOT NULL,
  `id_factura_compra` int(11),
  `id_proveedor` int(11),
  `motivo` varchar(100),
  `observaciones` text,
  `monto_total` decimal(10, 2) DEFAULT 0.00,
  `estado` varchar(20) DEFAULT 'ACTIVO',
  `id_usuario` int(11),
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_nota_credito`),
  KEY `id_factura_compra` (`id_factura_compra`),
  KEY `id_proveedor` (`id_proveedor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `detalle_nota_credito` (
  `id_detalle_nota` int(11) NOT NULL AUTO_INCREMENT,
  `id_nota_credito` int(11) NOT NULL,
  `id_productos` int(11),
  `cantidad` decimal(10, 2) DEFAULT 0.00,
  `precio_unitario` decimal(10, 2) DEFAULT 0.00,
  `total` decimal(10, 2) DEFAULT 0.00,
  PRIMARY KEY (`id_detalle_nota`),
  KEY `id_nota_credito` (`id_nota_credito`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
