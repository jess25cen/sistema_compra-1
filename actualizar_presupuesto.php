<?php
session_start();
require_once("conexion/db.php");

$db = new DB();
$conexion = $db->conectar();

echo "<h2>Reestructurando tabla presupuesto</h2>";

try {
    // Primero, obtener registros actuales para respaldarlos
    $sql_backup = "SELECT * FROM presupuesto";
    $stmt = $conexion->prepare($sql_backup);
    $stmt->execute();
    $backup_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>Registros encontrados en presupuesto: " . count($backup_data) . "</p>";
    
    // Obtener detalles
    $sql_backup_det = "SELECT * FROM detalle_presupuesto";
    $stmt_det = $conexion->prepare($sql_backup_det);
    $stmt_det->execute();
    $backup_detalles = $stmt_det->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>Registros encontrados en detalle_presupuesto: " . count($backup_detalles) . "</p>";
    
    // Eliminar las tablas antiguas
    $sql_drop_det = "DROP TABLE IF EXISTS detalle_presupuesto";
    $conexion->exec($sql_drop_det);
    echo "<p>✓ Tabla detalle_presupuesto eliminada</p>";
    
    $sql_drop = "DROP TABLE IF EXISTS presupuesto";
    $conexion->exec($sql_drop);
    echo "<p>✓ Tabla presupuesto eliminada</p>";
    
    // Crear tabla presupuesto nueva (estructura simplificada como pedido compra)
    $sql_create = "CREATE TABLE `presupuesto` (
      `id_presupuesto` int(11) NOT NULL AUTO_INCREMENT,
      `fecha_presupuesto` date NOT NULL,
      `estado` varchar(10) NOT NULL,
      `id_usuario` int(11) NOT NULL,
      PRIMARY KEY (`id_presupuesto`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $conexion->exec($sql_create);
    echo "<p>✓ Tabla presupuesto creada con estructura simplificada</p>";
    
    // Crear tabla detalle_presupuesto nueva
    $sql_create_det = "CREATE TABLE `detalle_presupuesto` (
      `id_detalle_presupuesto` int(11) NOT NULL AUTO_INCREMENT,
      `cantidad` int(11) NOT NULL,
      `id_presupuesto` int(11) NOT NULL,
      `id_productos` int(11) NOT NULL,
      PRIMARY KEY (`id_detalle_presupuesto`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $conexion->exec($sql_create_det);
    echo "<p>✓ Tabla detalle_presupuesto creada con estructura simplificada</p>";
    
    // Re-insertar datos si los hay (adaptando a la nueva estructura)
    if (count($backup_data) > 0) {
        $sql_insert = "INSERT INTO presupuesto (id_presupuesto, fecha_presupuesto, estado, id_usuario) 
                      VALUES (:id_presupuesto, :fecha_presupuesto, :estado, :id_usuario)";
        $stmt_insert = $conexion->prepare($sql_insert);
        
        foreach ($backup_data as $row) {
            $stmt_insert->execute([
                ':id_presupuesto' => $row['id_presupuesto'],
                ':fecha_presupuesto' => $row['fecha'],
                ':estado' => $row['estado'],
                ':id_usuario' => $row['id_usuario']
            ]);
        }
        echo "<p>✓ Datos de presupuesto restaurados: " . count($backup_data) . " registros</p>";
    }
    
    // Re-insertar detalles
    if (count($backup_detalles) > 0) {
        $sql_insert_det = "INSERT INTO detalle_presupuesto (id_detalle_presupuesto, cantidad, id_presupuesto, id_productos) 
                          VALUES (:id_detalle_presupuesto, :cantidad, :id_presupuesto, :id_productos)";
        $stmt_insert_det = $conexion->prepare($sql_insert_det);
        
        foreach ($backup_detalles as $row) {
            $stmt_insert_det->execute([
                ':id_detalle_presupuesto' => $row['id_detalle_presupuesto'],
                ':cantidad' => $row['cantidad'],
                ':id_presupuesto' => $row['id_presupuesto'],
                ':id_productos' => $row['id_productos']
            ]);
        }
        echo "<p>✓ Datos de detalle_presupuesto restaurados: " . count($backup_detalles) . " registros</p>";
    }
    
    echo "<p style='color: green; font-weight: bold;'>¡Reestructuración completada exitosamente!</p>";
    echo "<p>La tabla presupuesto ahora tiene la misma estructura que pedido_compra</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
