<?php
session_start();
require_once("conexion/db.php");

$db = new DB();
$conexion = $db->conectar();

echo "<h2>Actualizando tabla presupuesto con relaciones</h2>";

try {
    // Primero, obtener registros actuales para respaldarlos
    $sql_backup = "SELECT * FROM presupuesto";
    $stmt = $conexion->prepare($sql_backup);
    $stmt->execute();
    $backup_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>Registros encontrados en presupuesto: " . count($backup_data) . "</p>";
    
    // Eliminar la tabla antigua
    $sql_drop = "DROP TABLE IF EXISTS presupuesto";
    $conexion->exec($sql_drop);
    echo "<p>✓ Tabla presupuesto eliminada</p>";
    
    // Crear la tabla presupuesto nueva con relaciones
    $sql_create = "CREATE TABLE `presupuesto` (
      `id_presupuesto` int(11) NOT NULL AUTO_INCREMENT,
      `fecha_presupuesto` date NOT NULL,
      `estado` varchar(10) NOT NULL,
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $conexion->exec($sql_create);
    echo "<p>✓ Tabla presupuesto creada con relaciones a pedido_compra, proveedor y usuarios</p>";
    
    // Re-insertar datos si los hay
    if (count($backup_data) > 0) {
        $sql_insert = "INSERT INTO presupuesto (id_presupuesto, fecha_presupuesto, estado, id_usuario, pedido_compra, id_proveedor) 
                      VALUES (:id_presupuesto, :fecha_presupuesto, :estado, :id_usuario, :pedido_compra, :id_proveedor)";
        $stmt_insert = $conexion->prepare($sql_insert);
        
        foreach ($backup_data as $row) {
            $stmt_insert->execute([
                ':id_presupuesto' => $row['id_presupuesto'],
                ':fecha_presupuesto' => $row['fecha_presupuesto'],
                ':estado' => $row['estado'],
                ':id_usuario' => $row['id_usuario'],
                ':pedido_compra' => null,
                ':id_proveedor' => null
            ]);
        }
        echo "<p>✓ Datos de presupuesto restaurados: " . count($backup_data) . " registros</p>";
    }
    
    echo "<p style='color: green; font-weight: bold;'>¡Actualización completada exitosamente!</p>";
    echo "<p>La tabla presupuesto ahora tiene relaciones con pedido_compra y proveedor</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
