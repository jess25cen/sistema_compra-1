<?php
session_start();
require_once("conexion/db.php");

$db = new DB();
$conexion = $db->conectar();

echo "<h2>Actualizando estructura de tabla detalle_pedido</h2>";

try {
    // Primero, obtener registros actuales para respaldarlos
    $sql_backup = "SELECT * FROM detalle_pedido";
    $stmt = $conexion->prepare($sql_backup);
    $stmt->execute();
    $backup_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>Registros encontrados: " . count($backup_data) . "</p>";
    
    // Eliminar la tabla antigua
    $sql_drop = "DROP TABLE IF EXISTS detalle_pedido";
    $conexion->exec($sql_drop);
    echo "<p>✓ Tabla antigua eliminada</p>";
    
    // Crear la tabla nueva con PRIMARY KEY y AUTO_INCREMENT
    $sql_create = "CREATE TABLE `detalle_pedido` (
      `id_detalle_pedido` int(11) NOT NULL AUTO_INCREMENT,
      `cantidad` int(11) NOT NULL,
      `pedido_compra` int(11) NOT NULL,
      `id_productos` int(11) NOT NULL,
      PRIMARY KEY (`id_detalle_pedido`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $conexion->exec($sql_create);
    echo "<p>✓ Tabla nueva creada con AUTO_INCREMENT</p>";
    
    // Re-insertar datos si los hay
    if (count($backup_data) > 0) {
        $sql_insert = "INSERT INTO detalle_pedido (id_detalle_pedido, cantidad, pedido_compra, id_productos) 
                      VALUES (:id_detalle_pedido, :cantidad, :pedido_compra, :id_productos)";
        $stmt_insert = $conexion->prepare($sql_insert);
        
        foreach ($backup_data as $row) {
            $stmt_insert->execute([
                ':id_detalle_pedido' => $row['id_detalle_pedido'],
                ':cantidad' => $row['cantidad'],
                ':pedido_compra' => $row['pedido_compra'],
                ':id_productos' => $row['id_productos']
            ]);
        }
        echo "<p>✓ Datos restaurados: " . count($backup_data) . " registros</p>";
    }
    
    echo "<p style='color: green; font-weight: bold;'>¡Actualización completada exitosamente!</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
