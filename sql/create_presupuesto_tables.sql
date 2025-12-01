-- Crear tabla presupuesto (con relaciones a pedido_compra y proveedor)
CREATE TABLE IF NOT EXISTS `presupuesto` (
  `id_presupuesto` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_presupuesto` date NOT NULL,
  `estado` varchar(10) NOT NULL DEFAULT 'ACTIVO',
  `id_usuario` int(11) NOT NULL,
  `pedido_compra` int(11),
  `id_proveedor` int(11),
  PRIMARY KEY (`id_presupuesto`),
  KEY `id_usuario` (`id_usuario`),
  KEY `pedido_compra` (`pedido_compra`),
  KEY `id_proveedor` (`id_proveedor`),
  CONSTRAINT `fk_presupuesto_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  CONSTRAINT `fk_presupuesto_pedido` FOREIGN KEY (`pedido_compra`) REFERENCES `pedido_compra` (`pedido_compra`),
  CONSTRAINT `fk_presupuesto_proveedor` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedor` (`id_proveedor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Crear tabla detalle_presupuesto
CREATE TABLE IF NOT EXISTS `detalle_presupuesto` (
  `id_detalle_presupuesto` int(11) NOT NULL AUTO_INCREMENT,
  `cantidad` int(11) NOT NULL,
  `id_presupuesto` int(11) NOT NULL,
  `id_productos` int(11) NOT NULL,
  PRIMARY KEY (`id_detalle_presupuesto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;